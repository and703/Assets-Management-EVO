<?php
namespace Middleware;
use Core\{MiddlewareInterface, Request, Response};

class ApiKeyMiddleware implements MiddlewareInterface {
  public function handle(Request $req, Response $res, callable $next) {
    $configured = $GLOBALS['api_config']['API_KEY'] ?? '';
    if ($configured === '') return $next($req, $res);

    $provided = $req->query['api_key'] ?? '';
    if ($provided === '') {
      return $res->error('Unauthorized: missing api_key', 401);
    }

    if (!hash_equals(hash('sha256', $configured), $provided)) {
      return $res->error('Unauthorized: invalid api_key', 401);
    }

    return $next($req, $res);
  }
}