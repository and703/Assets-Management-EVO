<?php
namespace Core;

class Response {
  public function __construct() {
    header('Content-Type: application/json');
  }
  public function json($data, int $code=200) {
    http_response_code($code);
    echo json_encode($data);
    return null;
  }
  public function ok($data=[])     { return $this->json($data, 200); }
  public function created($data=[]) { return $this->json($data, 201); }
  public function error(string $msg, int $code){ return $this->json(['status'=>'error','message'=>$msg], $code); }
}
