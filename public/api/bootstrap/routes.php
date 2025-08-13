<?php
use Core\Router;
use Core\Request;
use Core\Response;
use Controllers\GenericController;
use Controllers\AssetsController;

/** @var Router $router */

// Health check
$router->add('GET', '/api/v1/health', function(Request $req, Response $res){
  return $res->ok(['status'=>'ok','time'=>date('c')]);
});

// --- Generic CRUD for ANY allowed table ---
// List / Create
$router->add('GET',  '/api/v1/{table}',        function($req,$res){ return (new GenericController($req,$res))->list($req->param('table')); });
// Detail 
$router->add('GET',    '/api/v1/{table}/{id}', function($req,$res){ return (new GenericController($req,$res))->get($req->param('table'), (int)$req->param('id')); });

// --- Assets specials ---
$router->add('GET', '/api/v1/assets/barcode/{barcode}', function($req,$res){
  return (new AssetsController($req,$res))->byBarcode($req->param('barcode'));
});
