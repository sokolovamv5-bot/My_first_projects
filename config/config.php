<?php
/**
 * Main Configuration File
 */

return [
    'app' => [
        'name' => 'Дом сказочных узоров',
        'slogan' => 'Там, где узоры шепчут сказку',
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'debug' => getenv('APP_DEBUG') === 'true',
    ],
    
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'house_of_patterns',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    
    'upload' => [
        'max_size' => (int)(getenv('MAX_UPLOAD_SIZE') ?: 10485760),
        'products_path' => __DIR__ . '/../storage/uploads/products/',
        'master_classes_path' => __DIR__ . '/../storage/uploads/master_classes/',
    ],
    
    'session' => [
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 120),
    ],
];
