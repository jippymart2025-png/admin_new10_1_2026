<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Impersonation Security Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for impersonation security features
    |
    */

    'security_alerts_enabled' => env('IMPERSONATION_SECURITY_ALERTS', true),
    'security_email' => env('IMPERSONATION_SECURITY_EMAIL', 'security@jippymart.in'),
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for impersonation requests
    |
    */
    
    'rate_limits' => [
        'admin_per_hour' => env('IMPERSONATION_ADMIN_LIMIT', 10),
        'ip_per_hour' => env('IMPERSONATION_IP_LIMIT', 20),
        'global_per_hour' => env('IMPERSONATION_GLOBAL_LIMIT', 100),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Token Settings
    |--------------------------------------------------------------------------
    |
    | Token generation and validation settings
    |
    */
    
    'token' => [
        'max_expiration_minutes' => env('IMPERSONATION_MAX_EXPIRATION', 30),
        'default_expiration_minutes' => env('IMPERSONATION_DEFAULT_EXPIRATION', 5),
        'cache_duration_minutes' => env('IMPERSONATION_CACHE_DURATION', 10),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Allowed origins for impersonation requests
    |
    */
    
    'allowed_origins' => [
        'admin.jippymart.in',
        'localhost:8000',
        '127.0.0.1:8000',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Business Hours
    |--------------------------------------------------------------------------
    |
    | Business hours for impersonation monitoring
    |
    */
    
    'business_hours' => [
        'start' => env('IMPERSONATION_BUSINESS_START', 6), // 6 AM
        'end' => env('IMPERSONATION_BUSINESS_END', 22),    // 10 PM
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    |
    | Security monitoring configuration
    |
    */
    
    'monitoring' => [
        'max_failed_attempts' => env('IMPERSONATION_MAX_FAILED_ATTEMPTS', 5),
        'max_ips_per_admin' => env('IMPERSONATION_MAX_IPS_PER_ADMIN', 3),
        'max_user_agents_per_admin' => env('IMPERSONATION_MAX_USER_AGENTS_PER_ADMIN', 2),
        'max_restaurant_impersonations' => env('IMPERSONATION_MAX_RESTAURANT_IMPERSONATIONS', 10),
    ],
];
