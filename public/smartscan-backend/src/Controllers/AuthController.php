<?php
namespace App\Controllers;

use App\Response;
use App\Db;

final class AuthController
{
    public function register(): never
    {
        $in = json_decode(file_get_contents('php://input'), true);
        if (!$in['username'] || !$in['password']) {
            Response::json(['error' => 'username & password required'], 400);
        }
        $hash = password_hash($in['password'], PASSWORD_BCRYPT);
        Db::get()->prepare(
            'INSERT INTO users (UserName, Password, Role) VALUES (?,?,?)'
        )->execute([$in['username'], $hash, $in['role'] ?? 'user']);

        Response::json('registered');
    }
}
