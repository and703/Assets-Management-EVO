<?php
namespace Core;

class Router {
  private array $routes = [];
  private array $middleware = [];

  public function use(MiddlewareInterface $mw){ $this->middleware[] = $mw; }

  public function add(string $method, string $pattern, callable $handler){
    $regex = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#','(?P<$1>[^/]+)', rtrim($pattern,'/')) . '/?$#';
    $this->routes[] = [$method, $regex, $handler];
  }

  public function dispatch(){
    $req = new Request();
    $res = new Response();

    $pipeline = array_reduce(
      array_reverse($this->middleware),
      fn($next, $mw) => fn($req,$res) => $mw->handle($req,$res,$next),
      function($req,$res){ return $this->match($req,$res); }
    );

    return $pipeline($req,$res);
  }

  private function match(Request $req, Response $res){
    foreach ($this->routes as [$m,$rx,$h]) {
      if ($req->method !== strtoupper($m)) continue;
      if (preg_match($rx, $req->path, $mch)) {
        foreach ($mch as $k=>$v) if (!is_int($k)) $req->params[$k]=$v;
        return $h($req,$res);
      }
    }
    return $res->error('Not Found', 404);
  }
}
