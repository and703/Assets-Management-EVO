<?php
namespace Controllers;
use Core\{Request, Response};
use Models\GenericModel;

class BulkController extends BaseController {
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

  // POST /api/v1/{table}/bulk
  public function createMany(string $table){
    if ($e=$this->guard($table)) return $e;
    $items = $this->req->body;
    if (!is_array($items) || !$items) return $this->res->error('Body must be a non-empty array', 400);

    $pdo = $GLOBALS['pdo'];
    $pdo->beginTransaction();
    try {
      $ids=[];
      foreach ($items as $data) {
        if (!is_array($data)) continue;
        foreach (($GLOBALS['api_config']['JSON_COLUMNS'][$table] ?? []) as $col) {
          if (isset($data[$col]) && (is_array($data[$col]) || is_object($data[$col]))) {
            $data[$col] = json_encode($data[$col], JSON_UNESCAPED_UNICODE);
          }
        }
        $ret = $this->model->insert($table, $data);
        if ($ret['id']) $ids[] = $ret['id'];
      }
      $pdo->commit();
      return $this->res->created(['status'=>'ok','table'=>$table,'inserted'=>count($ids),'ids'=>$ids]);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      return $this->res->error('Bulk insert failed: '.$e->getMessage(), 500);
    }
  }

  // PATCH /api/v1/{table}/bulk
  // body: [{id:1, ...fields}, {id:2, ...}]
  public function updateMany(string $table){
    if ($e=$this->guard($table)) return $e;
    $items = $this->req->body;
    if (!is_array($items) || !$items) return $this->res->error('Body must be a non-empty array', 400);

    $pdo = $GLOBALS['pdo'];
    $pdo->beginTransaction();
    $count = 0;
    try {
      foreach ($items as $data) {
        if (!is_array($data) || !isset($data['id']) || !ctype_digit((string)$data['id'])) continue;
        $id = (int)$data['id'];
        unset($data['id']);
        foreach (($GLOBALS['api_config']['JSON_COLUMNS'][$table] ?? []) as $col) {
          if (isset($data[$col]) && (is_array($data[$col]) || is_object($data[$col]))) {
            $data[$col] = json_encode($data[$col], JSON_UNESCAPED_UNICODE);
          }
        }
        $count += $this->model->update($table, $id, $data);
      }
      $pdo->commit();
      return $this->res->ok(['status'=>'ok','table'=>$table,'updated'=>$count]);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      return $this->res->error('Bulk update failed: '.$e->getMessage(), 500);
    }
  }

  // DELETE /api/v1/{table}/bulk
  // body: { ids: [1,2,3] }
  public function deleteMany(string $table){
    if ($e=$this->guard($table)) return $e;
    $ids = $this->req->body['ids'] ?? null;
    if (!is_array($ids) || !$ids) return $this->res->error('Body must contain "ids" array', 400);

    $pdo = $GLOBALS['pdo'];
    $pdo->beginTransaction();
    $count = 0;
    try {
      foreach ($ids as $id) {
        if (!ctype_digit((string)$id)) continue;
        $count += $this->model->delete($table, (int)$id);
      }
      $pdo->commit();
      return $this->res->ok(['status'=>'ok','table'=>$table,'deleted'=>$count]);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      return $this->res->error('Bulk delete failed: '.$e->getMessage(), 500);
    }
  }
}
