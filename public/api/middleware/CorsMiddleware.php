<?php
namespace Middleware;
use Core\{MiddlewareInterface, Request, Response};

class CorsMiddleware implements MiddlewareInterface {
  public function handle(Request $req, Response $res, callable $next) {
    $orig = $GLOBALS['api_config']['CORS_ORIG'];
    header('Access-Control-Allow-Origin: '.$orig);
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Allow-Methods: GET,POST,PATCH,PUT,DELETE,OPTIONS');
    if ($req->method === 'OPTIONS') { http_response_code(204); return null; }
    return $next($req,$res);
  }
}
