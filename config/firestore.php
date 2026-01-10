<?php
return [
    'project_id' => env('FIREBASE_PROJECT_ID', 'jippymart-27c08'),
    'credentials' => storage_path('app/firebase/credentials.json'),
    'database_id' => env('FIRESTORE_DATABASE_ID', '(default)'),
    'collection' => env('FIRESTORE_COLLECTION', 'activity_logs'),
    'timeout' => env('FIRESTORE_TIMEOUT', 15), // Reduced timeout for shared hosting
    'max_connections' => env('FIRESTORE_MAX_CONNECTIONS', 5), // Limit connections
    'connection_pool_size' => env('FIRESTORE_POOL_SIZE', 3), // Connection pool size
    'retry' => [
        'initial_delay' => env('FIRESTORE_RETRY_INITIAL_DELAY', 1.0),
        'max_delay' => env('FIRESTORE_RETRY_MAX_DELAY', 30.0), // Reduced max delay
        'multiplier' => env('FIRESTORE_RETRY_MULTIPLIER', 2.0),
        'retry_codes' => ['UNAVAILABLE', 'RESOURCE_EXHAUSTED', 'INTERNAL', 'DEADLINE_EXCEEDED'],
        'max_attempts' => env('FIRESTORE_MAX_RETRY_ATTEMPTS', 3), // Limit retry attempts
    ],
];
