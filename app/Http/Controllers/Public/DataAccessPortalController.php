<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ApiAccessToken;
use App\Services\Security\DataAccessSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

    public function verifyToken(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|min:50',
        ]);

        $rawToken = trim($validated['token']);

        $prefix = substr($rawToken, 0, 10);
        $apiToken = ApiAccessToken::with(['grant.request.branch', 'grant.request.grants'])
            ->where('token_prefix', $prefix)
            ->first();
            
        if (!$apiToken || !$this->securityService->validateToken($rawToken, $apiToken->token_hash)) {
            return back()->with('error', 'Token tidak valid. Silakan periksa kembali.');
        }

        if ($apiToken->isRevoked()) {
            return back()->with('error', 'Token ini telah dicabut oleh administrator.');
        }

        if ($apiToken->grant->isExpired()) {
            return back()->with('error', 'Token ini telah kedaluwarsa.');
        }

        session([
            'portal_token' => $rawToken,
            'portal_api_token_id' => $apiToken->id,
            'portal_branch_id' => $apiToken->grant->request->branch_id,
        ]);

        return redirect()->route('data-portal.dashboard')->with('success', 'Token terverifikasi! Silakan akses data Anda.');
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

        $availableTables = $dataRequest->grants->map(function ($g) {
            return [
                'table_name' => $g->table_name,
                'allowed_columns' => $g->allowed_columns,
                'filters' => $g->filters,
                'rate_limit' => $g->rate_limit_per_minute,
                'expires_at' => $g->expires_at,
                'permissions' => [
                    'read' => true,
                    'create' => $g->allow_create,
                    'update' => $g->allow_update,
                    'delete' => $g->allow_delete,
                ],
            ];
        });

        return view('public.data-access.portal', [
            'branch' => $branch,
            'tables' => $availableTables,
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

        $grant = $apiToken->grant;
        $branchId = $request->attributes->get('portal_branch_id');

        if ($grant->table_name !== $table) {
            return response()->json(['error' => 'Table not allowed'], 403);
        }

        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

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

        $grant = $apiToken->grant;
        $branchId = $request->attributes->get('portal_branch_id');

        if ($grant->table_name !== $table || !Schema::hasTable($table)) {
            abort(403);
        }

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
            ->map(fn($row) => implode(',', array_map(fn($cell) => '"' . str_replace('"', '""', $cell) . '"', $row)))
            ->implode("\n");

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$table}_export_" . date('Ymd') . ".csv\"");
    }

    public function logout()
    {
        session()->forget(['portal_token', 'portal_api_token_id', 'portal_branch_id']);
        return redirect()->route('data-portal.verify')->with('success', 'Anda telah keluar dari portal data.');
    }
}
