<?php
namespace Controllers;
use Core\{Request, Response};
use Models\GenericModel;

class BulkController extends BaseController {
  private GenericModel $model;
  private $allowed;

  public function __construct(Request $req, Response $res){
    parent::__construct($req,$res);
    $this->model = new GenericModel($GLOBALS['pdo']);
    $this->allowed = $GLOBALS['api_config']['ALLOWED_TABLES'];
  }
  private function guard(string $table){
    if ($this->allowed !== '*' && is_array($this->allowed) && !in_array($table, $this->allowed, true))
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

  // PATCH /api/v1/{table}/bulk-query?q=&<col>=&limit=&dry_run=1
  public function updateByQuery(string $table){
    if ($e=$this->guard($table)) return $e;

    $payload = $this->req->body ?: [];
    if (!is_array($payload) || !$payload) {
      return $this->res->error('Body must be a non-empty JSON object', 400);
    }

    // Columns & text columns
    $cols = $this->model->columns($table);
    if (!$cols) return $this->res->error('Table not found', 404);
    $colNames = array_column($cols, 'COLUMN_NAME');
    $textCols = [];
    foreach ($cols as $c) {
      $dt = strtolower((string)$c['DATA_TYPE']);
      if (in_array($dt, ['char','varchar','text','tinytext','mediumtext','longtext'], true)) {
        $textCols[] = $c['COLUMN_NAME'];
      }
    }

    // Keep only valid writable fields
    $writable = array_values(array_diff($colNames, ['id']));
    $data = array_intersect_key($payload, array_flip($writable));
    if (!$data) return $this->res->error('No writable fields in body', 400);

    // JSON columns auto-encode
    foreach (($GLOBALS['api_config']['JSON_COLUMNS'][$table] ?? []) as $col) {
      if (isset($data[$col]) && (is_array($data[$col]) || is_object($data[$col]))) {
        $data[$col] = json_encode($data[$col], JSON_UNESCAPED_UNICODE);
      }
    }

    // Build WHERE from query params and q
    $reserved = ['page','size','sort','dir','q','limit','dry_run'];
    $filters = array_diff_key($this->req->query, array_flip($reserved));

    $where = [];
    $bind = [];
    foreach ($filters as $k=>$v) {
      if (in_array($k, $colNames, true)) {
        $where[] = "`$k` = :eq_$k";
        $bind[":eq_$k"] = $v;
      }
    }

    $q = trim((string)($this->req->q('q','')));
    if ($q !== '' and $textCols) {
      $likes = [];
      foreach ($textCols as $tc) { $likes[] = "`$tc` LIKE :q"; }
      $where[] = '(' . implode(' OR ', $likes) . ')';
      $bind[':q'] = '%'+$q+'%';
    }
    // Correct concatenation for PHP
    if (isset($bind[':q']) && is_string($q)) { $bind[':q'] = '%'.$q.'%'; }

    $whereSql = $where ? (' WHERE '.implode(' AND ', $where)) : '';

    // Determine target IDs (respect optional limit)
    $maxCap = 10000;
    $limit = (int)($this->req->q('limit', 0));
    if ($limit < 0) $limit = 0;
    if ($limit > $maxCap) $limit = $maxCap;
    $limitSql = $limit > 0 ? " LIMIT $limit" : '';

    $idSql = "SELECT id FROM `$table`{$whereSql} ORDER BY id ASC{$limitSql}";
    $st = $GLOBALS['pdo']->prepare($idSql);
    foreach ($bind as $k=>$v) $st->bindValue($k, $v);
    $st->execute();
    $ids = array_map('intval', array_column($st->fetchAll(), 'id'));
    $matched = count($ids);
    if ($matched === 0) return $this->res->error('No rows match the query', 404);

    $dryRun = in_array(strtolower((string)$this->req->q('dry_run','0')), ['1','true','yes','y'], true);
    if ($dryRun) {
      return $this->res->ok([
        'status'=>'ok','table'=>$table,'dry_run'=>true,
        'matched'=>$matched,'limit'=>$limit if $limit>0 else null,
        'ids_sample'=>array_slice($ids,0,200)
      ]);
    }

    // Build UPDATE using IN (to avoid WHERE changing after update)
    $setParts=[]; $setBind=[];
    foreach ($data as $k=>$v) { $setParts[]="`$k` = :set_$k"; $setBind[":set_$k"]=$v; }

    // Prepare id placeholders
    $idParams = [];
    foreach ($ids as $i=>$id) { $idParams[":id$i"] = $id; }
    $inClause = implode(',', array_keys($idParams));

    $sql = "UPDATE `$table` SET ".implode(', ', $setParts)." WHERE id IN ($inClause)";
    $upd = $GLOBALS['pdo']->prepare($sql);
    foreach ($setBind as $k=>$v) $upd->bindValue($k,$v);
    foreach ($idParams as $k=>$v) $upd->bindValue($k,$v, \PDO::PARAM_INT);
    $upd->execute();
    $affected = $upd->rowCount();

    return $this->res->ok([
      'status'=>'ok','table'=>$table,'dry_run'=>false,
      'matched'=>$matched,'updated'=>$affected,
      'ids_sample'=>array_slice($ids,0,200)
    ]);
  }
}
