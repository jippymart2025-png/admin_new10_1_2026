<?php

return [
    // Production overrides
    'security_alerts_enabled' => true,
    'security_email' => 'security@jippymart.in',
    'rate_limits' => [
        'admin_per_hour' => 5, // Stricter for production
        'ip_per_hour' => 10,
        'global_per_hour' => 50,
    ],
    'token' => [
        'max_expiration_minutes' => 15, // Shorter for security
        'default_expiration_minutes' => 3,
    ],
    'allowed_origins' => [
        'admin.jippymart.in',
    ],
    'monitoring' => [
        'max_failed_attempts' => 3, // Stricter monitoring
        'max_ips_per_admin' => 2,
        'max_user_agents_per_admin' => 1,
    ],
];
