<?php
namespace Middleware;
use Core\{MiddlewareInterface, Request, Response};

class RateLimitMiddleware implements MiddlewareInterface {
  private string $bucket;
  public function __construct(string $bucket){ $this->bucket=$bucket; }

  public function handle(Request $req, Response $res, callable $next) {
    $limit = max(1,(int)$GLOBALS['api_config']['RATE_PER_MIN']);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = preg_replace('/[^a-z0-9_\-\.]/i','_', $this->bucket.'_'.$ip);
    $dir = sys_get_temp_dir().'/api_rate';
    if (!is_dir($dir)) @mkdir($dir,0777,true);
    $file = $dir.'/'.$key.'.json';

    $now=time(); $win=60; $data=['t'=>$now,'c'=>0];
    if (is_file($file)) {
      $data = json_decode(file_get_contents($file)?:'{}', true) ?: $data;
      if ($now-($data['t']??0) >= $win) $data=['t'=>$now,'c'=>0];
    }
    $data['c']++; file_put_contents($file,json_encode($data));
    if ($data['c'] > $limit) {
      header('Retry-After: 60');
      return $res->error('Rate limit exceeded', 429);
    }
    return $next($req,$res);
  }
}
