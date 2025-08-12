<?php
declare(strict_types=1);
/**
 * Front-controller & ultra-simple router.
 */
require_once __DIR__ . '/../src/Db.php';
require_once __DIR__ . '/../src/Response.php';
require_once __DIR__ . '/../src/Controllers/ConnectionController.php';
require_once __DIR__ . '/../src/Controllers/DownloadDataController.php';
require_once __DIR__ . '/../src/Controllers/UploadController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

use App\Response;
use App\Controllers\ConnectionController;
use App\Controllers\DownloadDataController;
use App\Controllers\UploadController;
use App\Controllers\AuthController;

$method = $_SERVER['REQUEST_METHOD'];
$path   = preg_replace('#^/+#', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (!str_starts_with($path, 'api/')) {
    Response::json(['error' => 'Unknown route'], 404);
}
$path = substr($path, 4);                     // trim "api/"

/* ---- dispatch table --------------------------------------------------- */
$routes = [
    // Connection
    'GET Connection/TestConnection'         => [ConnectionController::class, 'test'],
    'GET Connection/GetAllUsers'            => [ConnectionController::class, 'getAllUsers'],

    // DownloadData
    'GET DownloadData/GetAllItems'          => [DownloadDataController::class, 'getAllItems'],
    'GET DownloadData/GetCategories'        => [DownloadDataController::class, 'getCategories'],
    'GET DownloadData/GetLocation'          => [DownloadDataController::class, 'getLocation'],
    'GET DownloadData/GetStatusList'        => [DownloadDataController::class, 'getStatusList'],
    'GET DownloadData/GetAll_Inventory_H'   => [DownloadDataController::class, 'getAllInventoryH'],

    // Upload
    'POST Upload/UploadAssignedAssetsTag'   => [UploadController::class, 'uploadAssignedAssetsTag'],
    'POST Upload/UploadData_Test'           => [UploadController::class, 'uploadDataTest'],
    // After the other routes
    'POST Auth/Register'                    => [AuthController::class, 'register'],

];

$key = $method . ' ' . $path;
if (!isset($routes[$key])) {
    Response::json(['error' => 'Route not found'], 404);
}

[$class, $func] = $routes[$key];
(new $class)->$func();
