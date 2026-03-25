<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiAccessToken;
use App\Services\Security\DataAccessSecurityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DataAccessController extends Controller
{
    protected DataAccessSecurityService $securityService;

    public function __construct(DataAccessSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function index(Request $request, string $table): JsonResponse
    {
        $authResult = $this->authenticateToken($request);
        if (!$authResult['success']) {
            return response()->json(['error' => $authResult['message']], $authResult['code']);
        }

        $apiToken = $authResult['token'];
        $fullToken = $authResult['full_token'];
        $grant = $apiToken->grant;
        $branchId = $grant->request->branch_id;

        if ($grant->table_name !== $table) {
            return response()->json(
                [
                    'error' => 'Table not allowed for this token',
                    'allowed_table' => $grant->table_name,
                ],
                403,
            );
        }

        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $allowedColumns = $grant->allowed_columns;
        $query = DB::table($table)->select($allowedColumns);

        if ($grant->filters && isset($grant->filters['column']) && isset($grant->filters['value'])) {
            $query->where($grant->filters['column'], $grant->filters['value']);
        }

        $columns = Schema::getColumnListing($table);
        if (in_array('branch_id', $columns)) {
            $query->where('branch_id', $branchId);
        }

        $data = $query->get();

        $this->securityService->logAccess([
            'token_id' => $apiToken->id,
            'endpoint' => $request->fullUrl(),
            'method' => 'GET',
            'table_accessed' => $table,
            'columns_accessed' => $allowedColumns,
            'rows_returned' => $data->count(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(
            [
                'success' => true,
                'data' => $data,
                'meta' => [
                    'table' => $table,
                    'columns_returned' => $allowedColumns,
                    'branch_id' => $branchId,
                    'total_records' => $data->count(),
                    'timestamp' => now()->toIso8601String(),
                    'token' => [
                        'prefix' => $apiToken->token_prefix,
                        'full_token' => $fullToken,
                        'grant_id' => $apiToken->grant_id,
                        'expires_at' => $grant->expires_at?->toIso8601String(),
                        'is_revoked' => $apiToken->isRevoked(),
                    ],
                ],
            ],
            200,
        );
    }

    public function show(Request $request, string $table, string $id): JsonResponse
    {
        $authResult = $this->authenticateToken($request);
        if (!$authResult['success']) {
            return response()->json(['error' => $authResult['message']], $authResult['code']);
        }

        $apiToken = $authResult['token'];
        $fullToken = $authResult['full_token'];
        $grant = $apiToken->grant;
        $branchId = $grant->request->branch_id;

        if ($grant->table_name !== $table) {
            return response()->json(['error' => 'Table not allowed'], 403);
        }

        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $query = DB::table($table)->select($grant->allowed_columns)->where('id', $id);

        $columns = Schema::getColumnListing($table);
        if (in_array('branch_id', $columns)) {
            $query->where('branch_id', $branchId);
        }

        $record = $query->first();

        if (!$record) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        return response()->json(
            [
                'success' => true,
                'data' => $record,
                'meta' => [
                    'table' => $table,
                    'token_prefix' => $apiToken->token_prefix,
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
            200,
        );
    }

    protected function authenticateToken(Request $request): array
    {
        $bearerToken = $request->query('token') ?? ($request->query('bearer_token') ?? $request->bearerToken());

        if (!$bearerToken) {
            return [
                'success' => false,
                'message' => 'Missing token. Add ?token=YOUR_TOKEN to URL',
                'code' => 401,
            ];
        }

        $prefix = substr($bearerToken, 0, 10);

        $apiToken = ApiAccessToken::with(['grant.request.branch', 'grant.request.grants'])
            ->where('token_prefix', $prefix)
            ->first();

        if (!$apiToken || !$this->securityService->validateToken($bearerToken, $apiToken->token_hash)) {
            return [
                'success' => false,
                'message' => 'Invalid token',
                'code' => 401,
            ];
        }

        if ($apiToken->isRevoked()) {
            return [
                'success' => false,
                'message' => 'Token has been revoked',
                'code' => 401,
            ];
        }

        if ($apiToken->grant->isExpired()) {
            return [
                'success' => false,
                'message' => 'Token has expired',
                'code' => 401,
            ];
        }

        if ($apiToken->allowed_ips) {
            $requestIp = $request->ip();
            if (!$this->securityService->isIpAllowed($apiToken->allowed_ips, $requestIp)) {
                return [
                    'success' => false,
                    'message' => 'IP address not allowed',
                    'code' => 403,
                ];
            }
        }

        return [
            'success' => true,
            'token' => $apiToken,
            'full_token' => $bearerToken, 
            'code' => 200,
        ];
    }
}
