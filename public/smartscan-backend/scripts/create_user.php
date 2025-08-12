#!/usr/bin/env php
<?php
require __DIR__ . '/../src/Db.php';

[$script, $user, $pass, $role] = $argv + [null, null, null, 'admin'];

if (!$user || !$pass) {
    fwrite(STDERR, "Usage: create_user.php <username> <password> [role]\n");
    exit(1);
}

$hash = password_hash($pass, PASSWORD_BCRYPT);
$pdo  = App\Db::get();
$pdo->prepare(
    'INSERT INTO users (UserName, password, Role)
     VALUES (?,?,?) ON DUPLICATE KEY UPDATE Password=VALUES(Password), Role=VALUES(Role)'
)->execute([$user, $hash, $role]);

echo "User '{$user}' created/updated with role '{$role}'.\n";
