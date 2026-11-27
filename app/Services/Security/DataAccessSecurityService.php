<?php

namespace App\Services\Security;

use App\Models\DataAccessLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

class DataAccessSecurityService
{
    // Kuota login portal tidak lagi dibatasi (unlimited / bisa dipakai selamanya)
    public const PORTAL_LOGIN_LIMIT = null;

    private const KEY_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

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

    public function issueOneTimeKey(string $requestId, int $ttlHours = 48): string
    {
        $raw = '';
        $max = strlen(self::KEY_ALPHABET) - 1;

        for ($i = 0; $i < 12; $i++) {
            $raw .= self::KEY_ALPHABET[random_int(0, $max)];
        }

        Cache::put(
            $this->oneTimeCacheKey($requestId),
            hash('sha256', $raw . config('app.key')),
            now()->addHours($ttlHours)
        );

        return implode('-', str_split($raw, 4));
    }

    public function consumeOneTimeKey(string $requestId, string $providedKey): bool
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $providedKey));
        $stored = Cache::get($this->oneTimeCacheKey($requestId));

        if (!$stored || !hash_equals($stored, hash('sha256', $normalized . config('app.key')))) {
            return false;
        }

        Cache::forget($this->oneTimeCacheKey($requestId));

        return true;
    }

    public function portalLoginsUsed(string $tokenId): int
    {
        return DataAccessLog::where('token_id', $tokenId)
            ->where('endpoint', 'portal.login')
            ->count();
    }

    public function portalLoginsLeft(string $tokenId): int
    {
        // Token dapat digunakan selamanya tanpa batas kuota
        return PHP_INT_MAX;
    }

    public function recordPortalLogin($apiToken, ?string $ip, ?string $userAgent): void
    {
        $this->logAccess([
            'token_id' => $apiToken->id,
            'endpoint' => 'portal.login',
            'method' => 'POST',
            'table_accessed' => $apiToken->grant->table_name,
            'ip_address' => $ip,
            'user_agent' => $userAgent ? Str::limit($userAgent, 255) : null,
        ]);
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

    private function oneTimeCacheKey(string $requestId): string
    {
        return 'data_access_once_' . $requestId;
    }
}