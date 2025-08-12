<?php
// Minimal env-based config
return [
    'DB_HOST' => getenv('DB_HOST') ?: '127.0.0.1',
    'DB_NAME' => getenv('DB_NAME') ?: 'assets_db',
    'DB_USER' => getenv('DB_USER') ?: 'root',
    'DB_PASS' => getenv('DB_PASS') ?: '',
    'DB_PORT' => getenv('DB_PORT') ?: '3306',
    // Optional simple auth (leave empty to disable)
    'API_TOKEN' => getenv('API_TOKEN') ?: '',
];
