<?php
namespace Core;

class Response {
  public function json($data, int $code=200) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    return null;
  }
  public function ok($data=[])     { return $this->json($data, 200); }
  public function created($data=[]) { return $this->json($data, 201); }
  public function error(string $msg, int $code){ 
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode(['status'=>'error','message'=>$msg]);
    return null;
  }
  public function stream(string $mime, string $filename, callable $writer) {
    header('Content-Type: '.$mime);
    if ($filename !== '') {
      header('Content-Disposition: attachment; filename="'.$filename.'"');
    }
    http_response_code(200);
    $writer(); // echo/print inside
    return null;
  }
}
