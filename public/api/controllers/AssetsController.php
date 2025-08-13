<?php
namespace Controllers;
use Models\GenericModel;

class AssetsController extends BaseController {
  private GenericModel $model;
  public function __construct($req,$res){ parent::__construct($req,$res); $this->model=new GenericModel($GLOBALS['pdo']); }

  // GET /api/v1/assets/barcode/{barcode}
  public function byBarcode(string $barcode){
    $st = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE bar_kar = :b ORDER BY id ASC");
    $st->execute([':b'=>$barcode]); $rows=$st->fetchAll();
    if (!$rows) return $this->res->error('No asset for this barcode', 404);
    return $this->res->ok(['status'=>'ok','barcode'=>$barcode,'count'=>count($rows),'data'=>$rows]);
  }
}
