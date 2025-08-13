<?php
namespace Core;

class Request {
  public string $method;
  public string $path;
  public array $query;
  public array $headers;
  public array $params = [];
  public array $body;

  public function __construct() {
    $this->method  = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $this->path    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $this->query   = $_GET ?? [];
    $this->headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
    $raw = file_get_contents('php://input') ?: '';
    $this->body = $raw ? (json_decode($raw, true) ?: []) : [];
  }

  public function param(string $key, $default=null){ return $this->params[$key] ?? $default; }
  public function q(string $key, $default=null){ return $this->query[$key] ?? $default; }
}
