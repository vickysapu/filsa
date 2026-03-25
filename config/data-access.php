<?php
return [
    'security' => [
        'token_hash_algorithm' => 'sha512',
        'signature_algorithm' => 'sha256',
        'require_ip_whitelist' => env('DATA_API_REQUIRE_IP_WHITELIST', false),
        'max_rate_limit' => 1000,
        'default_rate_limit' => 60,
    ],
    'encryption' => [
        'enabled' => env('DATA_API_ENCRYPTION_ENABLED', true),
        'cipher' => 'aes-256-gcm',
    ],
    'audit' => [
        'log_all_requests' => env('DATA_API_LOG_ALL_REQUESTS', true),
        'retention_days' => 365,
    ],
    'approval' => [
        'admin_role_only' => env('DATA_API_ADMIN_ONLY_APPROVAL', true),
        'require_email_verification' => true,
    ],
];