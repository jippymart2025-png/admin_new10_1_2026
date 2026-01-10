<?php

return [
    // Local development overrides
    'security_alerts_enabled' => false,
    'rate_limits' => [
        'admin_per_hour' => 50, // More lenient for testing
        'ip_per_hour' => 100,
        'global_per_hour' => 500,
    ],
    'token' => [
        'max_expiration_minutes' => 60, // Longer for testing
        'default_expiration_minutes' => 10,
    ],
    'allowed_origins' => [
        'localhost:8000',
        '127.0.0.1:8000',
        'localhost:3000', // For frontend testing
    ],
];
