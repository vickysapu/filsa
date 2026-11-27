<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAccessRequest;
use App\Models\DataAccessGrant;
use App\Models\ApiAccessToken;
use App\Models\Branch;
use App\Services\Security\DataAccessSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataAccessApprovalController extends Controller
{
    protected DataAccessSecurityService $securityService;

    public function __construct(DataAccessSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    protected function checkAdminAccess(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('administrator') || !$user->is_active) {
            abort(403, 'Akses ditolak. Hanya administrator yang diizinkan.');
        }
    }

    public function index()
    {
        $this->checkAdminAccess();

        $requests = DataAccessRequest::with(['approver', 'grants', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.data-access.requests', compact('requests'));
    }

    public function show(DataAccessRequest $dataRequest)
    {
        $this->checkAdminAccess();

        $allTables = $this->getAllDatabaseTables();

        $branches = Branch::where('is_active', true)->select('id', 'name', 'code')->orderBy('name')->get();

        // Debug: Log requested_tables
        Log::info('Review page accessed', [
            'request_id' => $dataRequest->id,
            'requested_tables' => $dataRequest->requested_tables,
            'allTables_keys' => array_keys($allTables),
        ]);

        return view('admin.data-access.review', [
            'request' => $dataRequest,
            'allTables' => $allTables,
            'branches' => $branches,
        ]);
    }

    protected function getAllDatabaseTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $tableList = [];

        $tableNameKey = 'Tables_in_' . env('DB_DATABASE');

        foreach ($tables as $table) {
            $tableName = $table->$tableNameKey;

            // Skip system tables
            if (in_array($tableName, ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens', 'sessions', 'personal_access_tokens'])) {
                continue;
            }

            // Check if table exists and get columns
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $allColumns = Schema::getColumnListing($tableName);

            // Filter sensitive columns
            $sensitivePatterns = ['/password/i', '/secret/i', '/token/i', '/key/i', '/credential/i', '/remember_token/i'];
            $allowedColumns = array_filter($allColumns, function ($col) use ($sensitivePatterns) {
                foreach ($sensitivePatterns as $pattern) {
                    if (preg_match($pattern, $col)) {
                        return false;
                    }
                }
                return true;
            });

            $tableList[$tableName] = [
                'name' => $tableName,
                'columns' => array_values($allowedColumns),
                'all_columns' => $allColumns,
                
            ];
        }

        return $tableList;
    }

    public function approve(Request $request, DataAccessRequest $dataRequest)
    {
        $this->checkAdminAccess();

        Log::info('=== APPROVE DEBUG START ===', [
            'request_id' => $dataRequest->id,
            'method' => $request->method(),
            'route' => $request->route()->uri,
        ]);

        try {
            $validated = $request->validate([
                'branch_id' => ['required', 'uuid', Rule::exists('branches', 'id')->where(fn($q) => $q->where('is_active', true))],
                'grants' => 'required|array',
                'grants.*.table_name' => 'required|string',
                'grants.*.allowed_columns' => 'required|array|min:1',
                'grants.*.filters' => 'nullable|array',
                'grants.*.filters.column' => 'nullable|string',
                'grants.*.filters.value' => 'nullable|string',
                'grants.*.rate_limit_per_minute' => 'integer|min:1|max:1000',
                'expires_at' => 'nullable|date|after:now',
                'allowed_ips' => 'nullable|array',
                'allowed_ips.*' => 'ip',
            ]);

            Log::info('Validation passed', ['validated' => $validated]);

            return DB::transaction(function () use ($dataRequest, $validated) {
                Log::info('Transaction started');

                // Step 1: Update request status
                Log::info('Step 1: Updating request status');
                $dataRequest->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'reviewed_at' => now(),
                    'branch_id' => $validated['branch_id'],
                ]);
                Log::info('Step 1 complete', ['status' => $dataRequest->status, 'branch_id' => $dataRequest->branch_id]);

                $grants = [];
                $tokens = [];

                // Step 2: Create grants & tokens
                Log::info('Step 2: Creating grants', ['count' => count($validated['grants'])]);

                foreach ($validated['grants'] as $index => $grantData) {
                    Log::info('Processing grant ' . $index, [
                        'table' => $grantData['table_name'],
                        'allowed_columns' => $grantData['allowed_columns'],
                        'allowed_columns_count' => count($grantData['allowed_columns']),
                    ]);

                    $tableName = $grantData['table_name'];
                    $autoRestricted = $this->getSensitiveColumns()[$tableName] ?? [];
                    $restricted = array_unique(array_merge($grantData['restricted_columns'] ?? [], $autoRestricted));

                    $grant = DataAccessGrant::create([
                        'id' => (string) Str::uuid(),
                        'request_id' => $dataRequest->id,
                        'table_name' => $tableName,
                        'allowed_columns' => $grantData['allowed_columns'],
                        'restricted_columns' => $restricted,
                        'filters' => $grantData['filters'] ?? null,
                        'allow_create' => false,
                        'allow_update' => false,
                        'allow_delete' => false,
                        'rate_limit_per_minute' => $grantData['rate_limit_per_minute'] ?? 60,
                        'expires_at' => $validated['expires_at'] ?? null,
                    ]);

                    Log::info('Grant created', [
                        'grant_id' => $grant->id,
                        'table' => $tableName,
                        'allowed_columns_saved' => $grant->allowed_columns,
                    ]);

                    $grants[] = $grant;

                    // Step 3: Generate token
                    Log::info('Step 3: Generating token for grant ' . $grant->id);
                    $tokenData = $this->securityService->generateSecureToken();

                    Log::info('Token data generated', ['prefix' => $tokenData['token_prefix']]);

                    $apiToken = ApiAccessToken::create([
                        'id' => (string) Str::uuid(),
                        'grant_id' => $grant->id,
                        'token_hash' => $tokenData['token_hash'],
                        'token_prefix' => $tokenData['token_prefix'],
                        'encryption_key' => $tokenData['encryption_key'],
                        'allowed_ips' => $validated['allowed_ips'] ?? null,
                    ]);

                    Log::info('API Token created', ['token_id' => $apiToken->id]);

                    $tokens[] = [
                        'table' => $tableName,
                        'token' => $tokenData['raw_token'],
                        'prefix' => $tokenData['token_prefix'],
                        'expires_at' => $validated['expires_at'],
                    ];
                }

                Log::info('Step 4: Sending email', ['to' => $dataRequest->requester_email, 'tokens_count' => count($tokens)]);

                try {
                    Mail::to($dataRequest->requester_email)->queue(new \App\Mail\DataAccessApproved($dataRequest, $tokens));
                    Log::info('Email queued successfully');
                } catch (\Exception $mailException) {
                    Log::warning('Email queue failed, continuing anyway', [
                        'error' => $mailException->getMessage(),
                    ]);
                }

                $this->logApprovalActivity(Auth::user(), $dataRequest, $grants, count($tokens));

                Log::info('=== APPROVE DEBUG COMPLETE ===');

                return redirect()->route('admin.data-access.requests')->with('success', 'Permintaan disetujui! Token akses telah dikirim via email.');
            });
        } catch (\Exception $e) {
            Log::error('=== APPROVE DEBUG FAILED ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function reject(Request $request, DataAccessRequest $dataRequest)
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
        ]);

        $dataRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $this->logRejectionActivity(Auth::user(), $dataRequest, $validated['rejection_reason']);

        Mail::to($dataRequest->requester_email)->queue(new \App\Mail\DataAccessRejected($dataRequest));

        return back()->with('success', 'Permintaan ditolak. Notifikasi telah dikirim.');
    }

    protected function getSensitiveColumns(): array
    {
        $sensitive = [
            'users' => ['password', 'remember_token', 'email_verified_at', 'two_factor_secret', 'two_factor_recovery_codes'],
            'branches' => ['api_secret', 'internal_notes', 'encryption_key'],
            'api_access_tokens' => ['token_hash', 'encryption_key', 'raw_token'],
        ];

        foreach ($sensitive as $table => $columns) {
            if (Schema::hasTable($table)) {
                $allColumns = Schema::getColumnListing($table);
                $patterns = ['/password/i', '/secret/i', '/token/i', '/key/i', '/credential/i'];
                foreach ($allColumns as $column) {
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $column) && !in_array($column, $sensitive[$table])) {
                            $sensitive[$table][] = $column;
                        }
                    }
                }
            }
        }

        return $sensitive;
    }

    protected function logApprovalActivity($user, DataAccessRequest $request, array $grants, int $tokenCount): void
    {
        Log::channel('audit')->info('Data access approved', [
            'user_id' => $user?->id,
            'request_id' => $request->id,
            'grant_ids' => array_map(fn($g) => $g->id, $grants),
            'tokens_generated' => $tokenCount,
            'branch_id' => $request->branch_id,
        ]);
    }

    protected function logRejectionActivity($user, DataAccessRequest $request, string $reason): void
    {
        Log::channel('audit')->info('Data access rejected', [
            'user_id' => $user?->id,
            'request_id' => $request->id,
            'reason' => $reason,
            'branch_id' => $request->branch_id,
        ]);
    }
}
