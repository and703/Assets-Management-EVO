<?php
namespace Controllers;
use Core\{Request, Response};
use Models\GenericModel;

class UtilityController extends BaseController {
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

  // GET /api/v1/{table}/meta
  public function meta(string $table){
    if ($e=$this->guard($table)) return $e;
    $cols = $this->model->columns($table);
    return $this->res->ok(['status'=>'ok','table'=>$table,'columns'=>$cols]);
  }

  // GET /api/v1/{table}/export.csv
  public function exportCsv(string $table){
    if ($e=$this->guard($table)) return $e;

    $q    = trim((string)($this->req->q('q','')));
    $page = 1;
    $size = 5000; // big chunks
    $filters = array_diff_key($this->req->query, array_flip(['q','page','size','sort','dir']));

    $firstChunk = $this->model->list($table, ['q'=>$q,'page'=>$page,'size'=>$size,'filters'=>$filters,'sort'=>'id','dir'=>'ASC']);
    $cols = array_keys($firstChunk['rows'][0] ?? []);
    return $this->res->stream('text/csv', $table.'.csv', function() use ($table,$q,$filters,$size,$cols){
      $out = fopen('php://output', 'w');
      if ($cols) fputcsv($out, $cols);
      $page = 1;
      while (true) {
        $chunk = $GLOBALS['__csv_chunk'] ?? null;
        $result = (new GenericModel($GLOBALS['pdo']))->list($table, ['q'=>$q,'page'=>$page,'size'=>$size,'filters'=>$filters,'sort'=>'id','dir'=>'ASC']);
        foreach ($result['rows'] as $r) {
          // enforce same column order per row
          $ordered = [];
          foreach ($cols as $c) $ordered[] = $r[$c] ?? '';
          fputcsv($out, $ordered);
        }
        if (count($result['rows']) < $size) break;
        $page++;
      }
      fclose($out);
    });
  }

  // GET /api/v1/{table}/export.jsonl
  public function exportJsonl(string $table){
    if ($e=$this->guard($table)) return $e;

    $q    = trim((string)($this->req->q('q','')));
    $page = 1;
    $size = 5000;
    $filters = array_diff_key($this->req->query, array_flip(['q','page','size','sort','dir']));

    return $this->res->stream('application/x-ndjson', $table.'.jsonl', function() use ($table,$q,$filters,$size){
      while (true) {
        $result = (new GenericModel($GLOBALS['pdo']))->list($table, ['q'=>$q,'page'=>$page,'size'=>$size,'filters'=>$filters,'sort'=>'id','dir'=>'ASC']);
        foreach ($result['rows'] as $r) {
          echo json_encode($r, JSON_UNESCAPED_UNICODE)."\n";
        }
        if (count($result['rows']) < $size) break;
        $page++;
      }
    });
  }
}
