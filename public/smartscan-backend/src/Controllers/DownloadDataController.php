<?php
namespace App\Controllers;

use App\Response;
use App\Db;

final class DownloadDataController
{
    public function getAllItems(): never
    {
        $rows = Db::get()->query('SELECT * FROM items ORDER BY ItemID')->fetchAll();
        Response::json($rows);
    }

    public function getCategories(): never
    {
        $rows = Db::get()->query('SELECT * FROM categories ORDER BY CategoryID')->fetchAll();
        Response::json($rows);
    }

    public function getLocation(): never
    {
        $rows = Db::get()->query('SELECT * FROM locations ORDER BY LocationID')->fetchAll();
        Response::json($rows);
    }

    public function getStatusList(): never
    {
        $rows = Db::get()->query('SELECT * FROM status_list ORDER BY StatusID')->fetchAll();
        Response::json($rows);
    }

    public function getAllInventoryH(): never
    {
        $rows = Db::get()->query(
            'SELECT * FROM inventory_header ORDER BY InventoryDate DESC'
        )->fetchAll();
        Response::json($rows);
    }
}
