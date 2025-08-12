<?php
namespace App\Controllers;

use App\Response;
use App\Db;

final class ConnectionController
{
    public function test(): never
    {
        Response::json('OK');
    }

    public function getAllUsers(): never
    {
        $rows = Db::get()
            ->query('SELECT UserID, UserName, Password FROM users ORDER BY UserID')
            ->fetchAll();
        Response::json($rows);
    }
}
