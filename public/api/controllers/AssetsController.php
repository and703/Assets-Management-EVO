<?php
namespace Controllers;
use Models\GenericModel;

class AssetsController extends BaseController {
  private GenericModel $model;
  public function __construct($req,$res){
    parent::__construct($req,$res);
    $this->model = new GenericModel($GLOBALS['pdo']);
  }

  // GET /api/v1/assets/barcode/{barcode}  -> read-only, returns ALL rows
  public function byBarcode(string $barcode){
    $st = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE bar_kar = :b ORDER BY id ASC");
    $st->execute([':b'=>$barcode]); $rows=$st->fetchAll();
    if (!$rows) return $this->res->error('No asset for this barcode', 404);
    return $this->res->ok(['status'=>'ok','barcode'=>$barcode,'count'=>count($rows),'data'=>$rows]);
  }

  // PATCH /api/v1/assets/barcode/{barcode}  -> partial update of ALL rows with bar_kar
  public function updateByBarcodePartial(string $barcode){
    $data = $this->req->body ?: [];
    return $this->doUpdateByBarcode($barcode, $data, 'PATCH');
  }

  // PUT /api/v1/assets/barcode/{barcode}    -> full update semantics (updates only provided fields)
  public function updateByBarcodeFull(string $barcode){
    $data = $this->req->body ?: [];
    return $this->doUpdateByBarcode($barcode, $data, 'PUT');
  }

  /** Shared updater */
  private function doUpdateByBarcode(string $barcode, array $payload, string $method){
    if (!is_array($payload) || !$payload) {
      return $this->res->error('Body must be a non-empty JSON object', 400);
    }

    // Allow only real columns (except id)
    $colsInfo = $this->model->columns('assets');
    $colNames = array_column($colsInfo, 'COLUMN_NAME');
    $writable = array_values(array_diff($colNames, ['id']));

    // Keep only known columns
    $data = array_intersect_key($payload, array_flip($writable));
    if (!$data) return $this->res->error('No writable fields in body', 400);

    // Optional JSON columns support for assets (none by default, but kept for consistency)
    foreach (($GLOBALS['api_config']['JSON_COLUMNS']['assets'] ?? []) as $col) {
      if (isset($data[$col]) && (is_array($data[$col]) || is_object($data[$col]))) {
        $data[$col] = json_encode($data[$col], JSON_UNESCAPED_UNICODE);
      }
    }

    // Ensure target exists
    $check = $GLOBALS['pdo']->prepare("SELECT id, bar_kar FROM assets WHERE bar_kar = :b");
    $check->execute([':b'=>$barcode]);
    $existing = $check->fetchAll();
    if (!$existing) return $this->res->error('No asset for this barcode', 404);

    // Build UPDATE ... WHERE bar_kar = :__b
    $sets = [];
    $params = [];
    foreach ($data as $k => $v) {
      $sets[] = "`$k` = :$k";
      $params[":$k"] = $v;
    }
    $params[':__b'] = $barcode;

    $sql = "UPDATE assets SET ".implode(', ', $sets)." WHERE bar_kar = :__b";
    $upd = $GLOBALS['pdo']->prepare($sql);
    $upd->execute($params);
    $affected = $upd->rowCount();

    // If they changed the barcode itself, fetch by new barcode; otherwise by original
    $newBarcode = array_key_exists('bar_kar', $data) ? (string)$data['bar_kar'] : $barcode;

    $sel = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE bar_kar = :b ORDER BY id ASC");
    $sel->execute([':b'=>$newBarcode]);
    $rows = $sel->fetchAll();

    return $this->res->ok([
      'status'     => 'ok',
      'method'     => $method,
      'barcode_in' => $barcode,
      'barcode_out'=> $newBarcode,
      'updated'    => $affected,
      'count'      => count($rows),
      'data'       => $rows,
    ]);
  }
}
