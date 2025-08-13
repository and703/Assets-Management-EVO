<?php
namespace Middleware;
use Core\{MiddlewareInterface, Request, Response};

class AuthMiddleware implements MiddlewareInterface {
  public function handle(Request $req, Response $res, callable $next) {
    $token = $GLOBALS['api_config']['API_TOKEN'];
    if ($token === '' ) return $next($req,$res); // auth disabled
    $auth = $req->headers['Authorization'] ?? $req->headers['authorization'] ?? '';
    if (stripos($auth,'Bearer ') !== 0 || substr($auth,7) !== $token) {
      return $res->error('Unauthorized', 401);
    }
    return $next($req,$res);
  }
}
