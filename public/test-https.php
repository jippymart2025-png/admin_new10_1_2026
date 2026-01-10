<?php
/**
 * HTTPS Debugging Test File
 * Access this file directly to test server configuration
 * URL: https://yourdomain.com/test-https.php
 */

header('Content-Type: application/json');

$serverInfo = [
    'status' => 'SUCCESS - PHP is working!',
    'timestamp' => date('Y-m-d H:i:s'),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'php_version' => PHP_VERSION,
    'https_detection' => [
        'HTTPS' => $_SERVER['HTTPS'] ?? 'Not set',
        'REQUEST_SCHEME' => $_SERVER['REQUEST_SCHEME'] ?? 'Not set',
        'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'Not set',
        'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'Not set',
        'HTTP_X_FORWARDED_SSL' => $_SERVER['HTTP_X_FORWARDED_SSL'] ?? 'Not set',
    ],
    'request_info' => [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'Not set',
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Not set',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Not set',
        'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'Not set',
        'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? 'Not set',
    ],
    'laravel_test' => 'Now test: /api/stories?zone_id=BmSTwRFzmP13PnVNFJZJ',
];

echo json_encode($serverInfo, JSON_PRETTY_PRINT);
exit;

