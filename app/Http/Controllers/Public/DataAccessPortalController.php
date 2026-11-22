<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ApiAccessToken;
use App\Services\Security\DataAccessSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataAccessPortalController extends Controller
{
    protected DataAccessSecurityService $securityService;

    public function __construct(DataAccessSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function showVerification()
    {
        return view('public.data-access.verify');
    }

    /**
     * Validasi raw token. Return [ApiAccessToken|null, pesanError|null].
     */
    protected function findValidToken(string $rawToken): array
    {
        $rawToken = trim($rawToken);
        $prefix = substr($rawToken, 0, 10);

        $apiToken = ApiAccessToken::with(['grant.request.branch', 'grant.request.grants'])
            ->where('token_prefix', $prefix)
            ->first();

        if (!$apiToken || !$this->securityService->validateToken($rawToken, $apiToken->token_hash)) {
            return [null, 'Token tidak valid. Silakan periksa kembali.'];
        }

        if ($apiToken->isRevoked()) {
            return [null, 'Token ini telah dicabut oleh administrator.'];
        }

        if ($apiToken->grant->isExpired()) {
            return [null, 'Token ini telah kedaluwarsa.'];
        }

        return [$apiToken, null];
    }

    /**
     * Ambil grant yang valid untuk tabel tertentu dari token yang tersimpan di session.
     */
    protected function grantForTable(string $table)
    {
        $raw = session('portal_tokens', [])[$table] ?? null;
        if (!$raw) {
            return null;
        }

        [$apiToken] = $this->findValidToken($raw);

        if (!$apiToken || $apiToken->grant->table_name !== $table) {
            return null;
        }

        return $apiToken->grant;
    }

    public function verifyToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|min:50',
        ]);

        $rawToken = trim($validated['token']);

        [$apiToken, $error] = $this->findValidToken($rawToken);

        if (!$apiToken) {
            return back()->with('error', $error);
        }

        $request->session()->regenerate();

        session([
            'portal_token' => $rawToken,
            'portal_api_token_id' => $apiToken->id,
            'portal_branch_id' => $apiToken->grant->request->branch_id,
            'portal_request_id' => $apiToken->grant->request_id,
            // Satu token per tabel: token pertama langsung terdaftar untuk tabelnya
            'portal_tokens' => [$apiToken->grant->table_name => $rawToken],
        ]);

        return redirect()->route('data-portal.dashboard')
            ->with('success', 'Token terverifikasi! Silakan akses data Anda.');
    }

    /**
     * Tambah token untuk tabel lain (dipanggil via fetch dari dashboard).
     */
    public function addToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|min:50',
        ]);

        $requestId = session('portal_request_id');

        if (!$requestId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid. Silakan verifikasi ulang.',
            ], 401);
        }

        $rawToken = trim($validated['token']);
        [$apiToken, $error] = $this->findValidToken($rawToken);

        if (!$apiToken) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        // Token harus berasal dari permintaan akses yang sama
        if ((string) $apiToken->grant->request_id !== (string) $requestId) {
            return response()->json([
                'success' => false,
                'message' => 'Token ini milik permintaan akses yang berbeda.',
            ], 403);
        }

        $table = $apiToken->grant->table_name;

        $tokens = session('portal_tokens', []);
        $tokens[$table] = $rawToken;
        session(['portal_tokens' => $tokens]);

        return response()->json([
            'success' => true,
            'table' => $table,
            'token' => $rawToken,
            'message' => "Token untuk tabel {$table} berhasil ditambahkan.",
        ]);
    }

    public function showDashboard(Request $request)
    {
        $apiToken = $request->attributes->get('portal_api_token');

        if (!$apiToken || !$apiToken->grant) {
            return redirect()->route('data-portal.verify')->with('error', 'Sesi token tidak valid.');
        }

        $grant = $apiToken->grant;
        $dataRequest = $grant->request;

        if (!$dataRequest) {
            return redirect()->route('data-portal.verify')->with('error', 'Data request tidak ditemukan.');
        }

        $branch = $dataRequest->branch;
        $tableTokens = session('portal_tokens', []);

        $availableTables = $dataRequest->grants->map(function ($g) use ($tableTokens) {
            return [
                'table_name' => $g->table_name,
                'allowed_columns' => $g->allowed_columns,
                'filters' => $g->filters,
                'rate_limit' => $g->rate_limit_per_minute,
                'expires_at' => $g->expires_at,
                'has_token' => isset($tableTokens[$g->table_name]),
                'permissions' => [
                    'read' => true,
                    'create' => $g->allow_create,
                    'update' => $g->allow_update,
                    'delete' => $g->allow_delete,
                ],
            ];
        })->values();

        return view('public.data-access.portal', [
            'branch' => $branch,
            'tables' => $availableTables,
            'table_tokens' => $tableTokens,
            'token_prefix' => $apiToken->token_prefix,
            'expires_at' => $grant->expires_at,
        ]);
    }

    public function fetchTableData(Request $request, string $table)
    {
        $apiToken = $request->attributes->get('portal_api_token');

        if (!$apiToken || !$apiToken->grant) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $grant = $this->grantForTable($table);

        if (!$grant) {
            return response()->json([
                'error' => 'Table not allowed for this token',
                'needs_token' => true,
            ], 403);
        }

        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $branchId = $request->attributes->get('portal_branch_id') ?? session('portal_branch_id');
        $allowedColumns = $grant->allowed_columns;

        $query = DB::table($table)->select($allowedColumns);

        if ($grant->filters && isset($grant->filters['column']) && isset($grant->filters['value'])) {
            $query->where($grant->filters['column'], $grant->filters['value']);
        } else {
            $columns = Schema::getColumnListing($table);
            if (in_array('branch_id', $columns)) {
                $query->where('branch_id', $branchId);
            }
        }

        $perPage = min((int) request('per_page', 20), 100);
        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'table' => $table,
                'columns' => $allowedColumns,
                'branch_id' => $branchId,
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'rate_limit_remaining' => 59,
            ],
        ]);
    }

    public function exportCsv(Request $request, string $table)
    {
        $apiToken = $request->attributes->get('portal_api_token');

        if (!$apiToken || !$apiToken->grant) {
            abort(403);
        }

        $grant = $this->grantForTable($table);

        if (!$grant || !Schema::hasTable($table)) {
            abort(403);
        }

        $branchId = $request->attributes->get('portal_branch_id') ?? session('portal_branch_id');
        $allowedColumns = $grant->allowed_columns;

        $query = DB::table($table)->select($allowedColumns);

        if ($grant->filters && isset($grant->filters['column']) && isset($grant->filters['value'])) {
            $query->where($grant->filters['column'], $grant->filters['value']);
        } else {
            $columns = Schema::getColumnListing($table);
            if (in_array('branch_id', $columns)) {
                $query->where('branch_id', $branchId);
            }
        }

        $data = $query->get();

        $csv = collect([$allowedColumns])
            ->merge($data->map(fn($row) => array_values((array) $row)))
            ->map(fn($row) => implode(',', array_map(fn($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $row)))
            ->implode("\n");

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$table}_export_" . date('Ymd') . ".csv\"");
    }

    public function logout()
    {
        session()->forget([
            'portal_token',
            'portal_tokens',
            'portal_api_token_id',
            'portal_branch_id',
            'portal_request_id',
        ]);

        return redirect()->route('data-portal.verify')->with('success', 'Anda telah keluar dari portal data.');
    }
}