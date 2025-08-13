<?php
// Env-first, fallback defaults
$GLOBALS['api_config'] = [
  'DB_HOST'   => getenv('DB_HOST') ?: '127.0.0.1',
  'DB_PORT'   => getenv('DB_PORT') ?: '3306',
  'DB_NAME'   => getenv('DB_NAME') ?: 'asset_db',
  'DB_USER'   => getenv('DB_USER') ?: 'febrian001',
  'DB_PASS'   => getenv('DB_PASS') ?: 'Pirelli889900--',
  'CORS_ORIG' => getenv('CORS_ORIG') ?: '*',
  'API_TOKEN' => getenv('API_TOKEN') ?: '',   // empty = auth disabled
  'RATE_PER_MIN' => (int)(getenv('RATE_PER_MIN') ?: 120),
  'ALLOWED_TABLES' => [ // allow-list (add/remove here)
    'assets','asset_audits','api_logs','api_tokens','users','roles','permissions',
    'role_permissions','settings','tag_data','email_templates','activity_logs'
  ],
  'JSON_COLUMNS' => [ 'asset_audits' => ['changes'] ],
];
