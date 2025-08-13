<?php
namespace Core;

interface MiddlewareInterface {
  public function handle(Request $req, Response $res, callable $next);
}
