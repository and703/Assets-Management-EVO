<?php
/* Database connection parameters */
return [
    'db' => [
        'dsn'  => 'mysql:host=localhost;dbname=smartscan;charset=utf8mb4',
        'user' => 'root',
        'pass' => 'root',
        'opt'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
];
