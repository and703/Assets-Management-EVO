<?php
use Core\Router;
use Core\Request;
use Core\Response;
use Controllers\GenericController;
use Controllers\AssetsController;
use Controllers\AssetsDataController;
use Controllers\BulkController;
use Controllers\UtilityController;
use Middleware\ApiKeyMiddleware;

/** @var Router $router */

// Health
$router->add('GET', '/api/v1/health', function(Request $req, Response $res){
  return $res->ok(['status'=>'ok','time'=>date('c')]);
});

$auth = [new ApiKeyMiddleware()];

// Assets CRUD — API key required
$router->add('GET',    '/api/v1/assets/data',       fn($req,$res)=> (new AssetsDataController($req,$res))->list(),                    $auth);
$router->add('POST',   '/api/v1/assets/data',       fn($req,$res)=> (new AssetsDataController($req,$res))->create(),                  $auth);
$router->add('GET',    '/api/v1/assets/data/{id}',  fn($req,$res)=> (new AssetsDataController($req,$res))->show((int)$req->param('id')),   $auth);
$router->add('PUT',    '/api/v1/assets/data/{id}',  fn($req,$res)=> (new AssetsDataController($req,$res))->replace((int)$req->param('id')), $auth);
$router->add('PATCH',  '/api/v1/assets/data/{id}',  fn($req,$res)=> (new AssetsDataController($req,$res))->update((int)$req->param('id')),  $auth);
$router->add('DELETE', '/api/v1/assets/data/{id}',  fn($req,$res)=> (new AssetsDataController($req,$res))->delete((int)$req->param('id')),  $auth);

// Generic CRUD
$router->add('GET',  '/api/v1/{table}',        fn($req,$res)=> (new GenericController($req,$res))->list($req->param('table')));
$router->add('POST', '/api/v1/{table}',        fn($req,$res)=> (new GenericController($req,$res))->create($req->param('table')));
$router->add('GET',    '/api/v1/{table}/{id}', fn($req,$res)=> (new GenericController($req,$res))->get($req->param('table'), (int)$req->param('id')));
$router->add('PATCH',  '/api/v1/{table}/{id}', fn($req,$res)=> (new GenericController($req,$res))->update($req->param('table'), (int)$req->param('id')));
$router->add('PUT',    '/api/v1/{table}/{id}', fn($req,$res)=> (new GenericController($req,$res))->update($req->param('table'), (int)$req->param('id')));
$router->add('DELETE', '/api/v1/{table}/{id}', fn($req,$res)=> (new GenericController($req,$res))->delete($req->param('table'), (int)$req->param('id')));

// Bulk ops
$router->add('POST',  '/api/v1/{table}/bulk',  fn($req,$res)=> (new BulkController($req,$res))->createMany($req->param('table')));
$router->add('PATCH', '/api/v1/{table}/bulk',  fn($req,$res)=> (new BulkController($req,$res))->updateMany($req->param('table')));
$router->add('DELETE','/api/v1/{table}/bulk',  fn($req,$res)=> (new BulkController($req,$res))->deleteMany($req->param('table')));

// Utility
$router->add('GET', '/api/v1/{table}/meta',         fn($req,$res)=> (new UtilityController($req,$res))->meta($req->param('table')));
$router->add('GET', '/api/v1/{table}/export.csv',   fn($req,$res)=> (new UtilityController($req,$res))->exportCsv($req->param('table')));
$router->add('GET', '/api/v1/{table}/export.jsonl', fn($req,$res)=> (new UtilityController($req,$res))->exportJsonl($req->param('table')));

// Assets specials (READ)
$router->add('GET', '/api/v1/assets/barcode/{barcode}', fn($req,$res)=> (new AssetsController($req,$res))->byBarcode($req->param('barcode')));

// Assets update by barcode (WRITE)
$router->add('PATCH', '/api/v1/assets/barcode/{barcode}', fn($req,$res)=> (new AssetsController($req,$res))->updateByBarcodePartial($req->param('barcode')));
$router->add('PUT',   '/api/v1/assets/barcode/{barcode}', fn($req,$res)=> (new AssetsController($req,$res))->updateByBarcodeFull($req->param('barcode')));

// Bulk by query (PATCH)
$router->add('PATCH', '/api/v1/{table}/bulk-query', fn($req,$res)=> (new BulkController($req,$res))->updateByQuery($req->param('table')));