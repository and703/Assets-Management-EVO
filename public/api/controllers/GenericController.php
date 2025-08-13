<?php
namespace Controllers;
use Models\GenericModel;
use Core\{Request, Response};

class GenericController extends BaseController {
  private GenericModel $model;
  private array $allowed;

  public function __construct(Request $req, Response $res){
    parent::__construct($req,$res);
    $this->model = new GenericModel($GLOBALS['pdo']);
    $this->allowed = $GLOBALS['api_config']['ALLOWED_TABLES'];
  }

  private function guard(string $table){
    if (!in_array($table, $this->allowed, true))
      return $this->res->error('Table not allowed', 400);
    return null;
  }

  public function list(string $table){
    if ($e=$this->guard($table)) return $e;
    $filters = array_diff_key($this->req->query, array_flip(['page','size','sort','dir','q']));
    $out = $this->model->list($table, [
      'page'=>$this->req->q('page',1),
      'size'=>$this->req->q('size',500),
      'sort'=>$this->req->q('sort','id'),
      'dir' =>$this->req->q('dir','DESC'),
      'q'   =>$this->req->q('q',''),
      'filters'=>$filters
    ]);
    return $this->res->ok(['status'=>'ok','table'=>$table]+$out);
  }

  public function get(string $table, int $id){
    if ($e=$this->guard($table)) return $e;
    $row = $this->model->find($table, $id);
    if (!$row) return $this->res->error('Not found', 404);
    return $this->res->ok(['status'=>'ok','table'=>$table,'data'=>$row]);
  }

  public function create(string $table){
    if ($e=$this->guard($table)) return $e;
    $data = $this->req->body ?: [];
    // JSON columns auto-encode
    foreach (($GLOBALS['api_config']['JSON_COLUMNS'][$table] ?? []) as $col) {
      if (isset($data[$col]) && (is_array($data[$col]) || is_object($col))) {
        $data[$col] = json_encode($data[$col], JSON_UNESCAPED_UNICODE);
      }
    }
    $ret = $this->model->insert($table, $data);
    $row = $ret['id'] ? $this->model->find($table, $ret['id']) : null;
    return $this->res->created(['status'=>'ok','table'=>$table,'id'=>$ret['id'],'data'=>$row]);
  }

  public function update(string $table, int $id){
    if ($e=$this->guard($table)) return $e;
    $data = $this->req->body ?: [];
    foreach (($GLOBALS['api_config']['JSON_COLUMNS'][$table] ?? []) as $col) {
      if (isset($data[$col]) && (is_array($data[$col]) || is_object($data[$col]))) {
        $data[$col] = json_encode($data[$col], JSON_UNESCAPED_UNICODE);
      }
    }
    $this->model->update($table, $id, $data);
    $row = $this->model->find($table, $id);
    return $this->res->ok(['status'=>'ok','table'=>$table,'id'=>$id,'data'=>$row]);
  }

  public function delete(string $table, int $id){
    if ($e=$this->guard($table)) return $e;
    $n = $this->model->delete($table, $id);
    return $this->res->ok(['status'=>'ok','table'=>$table,'id'=>$id,'deleted'=>$n]);
  }
}
