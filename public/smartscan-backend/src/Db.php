<?php
// src/Db.php
namespace App;

final class Db
{
    private static ?\PDO $pdo = null;

    public static function get(): \PDO
    {
        if (self::$pdo === null) {
            $cfg = require __DIR__ . '/../config/config.php';
            $db  = $cfg['db'];
            self::$pdo = new \PDO($db['dsn'], $db['user'], $db['pass'], $db['opt']);
        }
        return self::$pdo;
    }
}
