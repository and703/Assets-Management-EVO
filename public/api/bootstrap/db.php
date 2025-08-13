<?php

$config = $GLOBALS['api_config'];

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
  $config['DB_HOST'],$config['DB_PORT'],$config['DB_NAME']
);

$GLOBALS['pdo'] = new PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES=>false,
]);
