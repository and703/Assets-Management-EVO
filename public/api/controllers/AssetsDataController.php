<?php
namespace Controllers;

class AssetsDataController extends BaseController {

  private array $writable = [
    'asset','tagID','subnumber','joint_assets_number','capitalized_on',
    'asset_class','asset_class_desc','category','asset_description','quantity',
    'perpcs_id','sn','uom','po','location','bar_kar','updated_at','last_scan'
  ];

  private function pdo(){ return $GLOBALS['pdo']; }

  // GET /api/v1/assets/data
  public function list(){
    $pdo = $this->pdo();

    $pageRaw = $this->req->q('page', '1');
    $allPages = (strtolower((string)$pageRaw) === 'all');
    $page   = $allPages ? 1 : max(1, (int)$pageRaw);
    $size   = $allPages ? null : min(1000, max(1, (int)($this->req->q('size', 100))));
    $sort   = $this->req->q('sort', 'id');
    $dir    = strtoupper($this->req->q('dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
    $q      = trim((string)$this->req->q('q', ''));
    $offset = $allPages ? 0 : ($page - 1) * $size;

    $allowed_sorts = [
      'id','asset','tagID','subnumber','joint_assets_number','capitalized_on',
      'asset_class','asset_class_desc','category','asset_description','quantity',
      'perpcs_id','sn','uom','po','location','bar_kar','created_at','updated_at','last_scan'
    ];
    if (!in_array($sort, $allowed_sorts, true)) $sort = 'id';

    $where  = [];
    $params = [];

    if ($q !== '') {
      $where[]      = "(asset LIKE :q OR asset_description LIKE :q OR tagID LIKE :q OR location LIKE :q OR bar_kar LIKE :q OR sn LIKE :q OR po LIKE :q)";
      $params[':q'] = '%'.$q.'%';
    }

    $filterable = [
      'asset','tagID','subnumber','joint_assets_number','capitalized_on',
      'asset_class','asset_class_desc','category','asset_description',
      'quantity','perpcs_id','sn','uom','po','location','bar_kar'
    ];
    foreach ($filterable as $col) {
      $val = $this->req->q($col);
      if ($val !== null && $val !== '') {
        $key          = ':f_'.$col;
        $where[]      = "`$col` = $key";
        $params[$key] = $val;
      }
    }

    $whereSQL = $where ? 'WHERE '.implode(' AND ', $where) : '';

    $countSt = $pdo->prepare("SELECT COUNT(*) FROM assets $whereSQL");
    $countSt->execute($params);
    $total = (int)$countSt->fetchColumn();

    if ($allPages) {
      $st = $pdo->prepare("SELECT * FROM assets $whereSQL ORDER BY `$sort` $dir");
      $st->execute($params);
    } else {
      $params[':limit']  = $size;
      $params[':offset'] = $offset;
      $st = $pdo->prepare("SELECT * FROM assets $whereSQL ORDER BY `$sort` $dir LIMIT :limit OFFSET :offset");
      foreach ($params as $key => $val) {
        $st->bindValue($key, $val, ($key === ':limit' || $key === ':offset') ? \PDO::PARAM_INT : \PDO::PARAM_STR);
      }
    }

    $st->execute();
    $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

    return $this->res->ok([
      'status' => 'ok',
      'total'  => $total,
      'page'   => $allPages ? 'all' : $page,
      'size'   => $allPages ? $total : $size,
      'pages'  => $allPages ? 1 : (int)ceil($total / $size),
      'data'   => $rows,
    ]);
  }

  // GET /api/v1/assets/data/{id}
  public function show(int $id){
    $st = $this->pdo()->prepare("SELECT * FROM assets WHERE id = :id");
    $st->execute([':id' => $id]);
    $row = $st->fetch(\PDO::FETCH_ASSOC);
    if (!$row) return $this->res->error('Asset not found', 404);
    return $this->res->ok(['status' => 'ok', 'data' => $row]);
  }

  // POST /api/v1/assets/data
  public function create(){
    $body = $this->req->body ?: [];
    $data = array_intersect_key($body, array_flip($this->writable));
    if (!$data) return $this->res->error('No valid fields provided', 400);

    $data['created_at'] = date('Y-m-d H:i:s');

    $cols = '`'.implode('`,`', array_keys($data)).'`';
    $qs   = implode(',', array_fill(0, count($data), '?'));
    $st   = $this->pdo()->prepare("INSERT INTO assets ($cols) VALUES ($qs)");
    $st->execute(array_values($data));
    $newId = (int)$this->pdo()->lastInsertId();

    $st2 = $this->pdo()->prepare("SELECT * FROM assets WHERE id = :id");
    $st2->execute([':id' => $newId]);
    $row = $st2->fetch(\PDO::FETCH_ASSOC);

    return $this->res->created(['status' => 'ok', 'id' => $newId, 'data' => $row]);
  }

  // PUT /api/v1/assets/data/{id}  — full replace
  public function replace(int $id){
    return $this->doUpdate($id, true);
  }

  // PATCH /api/v1/assets/data/{id}  — partial update
  public function update(int $id){
    return $this->doUpdate($id, false);
  }

  private function doUpdate(int $id, bool $full){
    $check = $this->pdo()->prepare("SELECT id FROM assets WHERE id = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) return $this->res->error('Asset not found', 404);

    $body = $this->req->body ?: [];
    $data = array_intersect_key($body, array_flip($this->writable));
    if (!$data) return $this->res->error('No valid fields provided', 400);

    $data['updated_at'] = date('Y-m-d H:i:s');

    $sets = [];
    $vals = [];
    foreach ($data as $k => $v) { $sets[] = "`$k` = ?"; $vals[] = $v; }
    $vals[] = $id;

    $st = $this->pdo()->prepare("UPDATE assets SET ".implode(', ', $sets)." WHERE id = ?");
    $st->execute($vals);

    $st2 = $this->pdo()->prepare("SELECT * FROM assets WHERE id = :id");
    $st2->execute([':id' => $id]);
    $row = $st2->fetch(\PDO::FETCH_ASSOC);

    return $this->res->ok(['status' => 'ok', 'updated' => $st->rowCount(), 'data' => $row]);
  }

  // DELETE /api/v1/assets/data/{id}
  public function delete(int $id){
    $check = $this->pdo()->prepare("SELECT id FROM assets WHERE id = :id");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) return $this->res->error('Asset not found', 404);

    $st = $this->pdo()->prepare("DELETE FROM assets WHERE id = :id");
    $st->execute([':id' => $id]);

    return $this->res->ok(['status' => 'ok', 'deleted' => $st->rowCount(), 'id' => $id]);
  }
}
