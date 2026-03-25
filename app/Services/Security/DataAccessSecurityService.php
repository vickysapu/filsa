<?php

namespace App\Services\Security;

use App\Models\DataAccessLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

class DataAccessSecurityService
{
    public function generateSecureToken(): array
    {
        $prefix = 'dap_' . Str::random(6);
        $rawToken = $prefix . '_' . Str::random(32) . '_' . Str::uuid();
        $tokenHash = hash('sha512', $rawToken . config('app.key'));
        $encryptionKey = bin2hex(random_bytes(32));

        return [
            'raw_token' => $rawToken,
            'token_hash' => $tokenHash,
            'token_prefix' => $prefix,
            'encryption_key' => $encryptionKey,
        ];
    }

    public function validateToken(string $providedToken, string $storedHash): bool
    {
        $computedHash = hash('sha512', $providedToken . config('app.key'));
        return hash_equals($storedHash, $computedHash);
    }

    public function encryptResponse(array $data, string $encryptionKey): string
    {
        return Crypt::encryptString(json_encode($data), $encryptionKey);
    }

    public function generateRequestSignature(string $payload, string $secret): string
    {
        return hash_hmac('sha512', $payload, $secret);
    }

    public function verifyRequestSignature(string $payload, string $signature, string $secret): bool
    {
        return hash_equals($this->generateRequestSignature($payload, $secret), $signature);
    }

    public function isIpAllowed(?array $allowedIps, string $requestIp): bool
    {
        if (empty($allowedIps)) {
            return true;
        }
        return IpUtils::checkIp($requestIp, $allowedIps);
    }

    public function filterColumns(array $data, array $allowedColumns): array
    {
        return array_intersect_key($data, array_flip($allowedColumns));
    }

    public function logAccess(array $accessData): void
    {
        DataAccessLog::create([
            'id' => (string) Str::uuid(),

            'token_id' => $accessData['token_id'],
            'endpoint' => $accessData['endpoint'],
            'method' => $accessData['method'],
            'table_accessed' => $accessData['table_accessed'],
            'columns_accessed' => $accessData['columns_accessed'] ?? null,
            'rows_returned' => $accessData['rows_returned'] ?? 0,
            'ip_address' => $accessData['ip_address'],
            'user_agent' => $accessData['user_agent'] ?? null,
        ]);
    }
}
