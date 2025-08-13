<?php
namespace Models;
use PDO;

class GenericModel extends BaseModel {
  public function columns(string $table): array {
    $st = $this->db->prepare("
      SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t
      ORDER BY ORDINAL_POSITION
    ");
    $st->execute([':t'=>$table]);
    return $st->fetchAll() ?: [];
  }

  public function list(string $table, array $opts): array {
    $cols = $this->columns($table);
    if (!$cols) return ['rows'=>[], 'total'=>0];
    $names = array_column($cols,'COLUMN_NAME');
    $sort = in_array(($opts['sort']??'id'), $names, true) ? $opts['sort'] : 'id';
    $dir  = strtoupper($opts['dir']??'DESC'); $dir = in_array($dir,['ASC','DESC'])?$dir:'DESC';

    $bind = [];
    $where=[];

    // equality filters from $opts['filters'] (key=>value)
    foreach ($opts['filters']??[] as $k=>$v) {
      if (in_array($k,$names,true)) { $where[]="`$k` = :eq_$k"; $bind[":eq_$k"]=$v; }
    }

    // q search across text columns
    $q = trim((string)($opts['q']??''));
    if ($q!=='') {
      $texts=[];
      foreach ($cols as $c) {
        if (in_array(strtolower($c['DATA_TYPE']), ['char','varchar','text','tinytext','mediumtext','longtext'], true))
          $texts[]="`{$c['COLUMN_NAME']}` LIKE :q";
      }
      if ($texts){ $where[]='('.implode(' OR ',$texts).')'; $bind[':q']='%'.$q.'%'; }
    }

    $whereSql = $where ? ' WHERE '.implode(' AND ',$where) : '';
    $sql = "SELECT * FROM `$table` $whereSql ORDER BY `$sort` $dir ";
    $st = $this->db->prepare($sql);
    foreach($bind as $k=>$v) $st->bindValue($k,$v);
    $st->execute();
    $rows=$st->fetchAll();

    $ct = $this->db->prepare("SELECT COUNT(*) c FROM `$table` $whereSql");
    foreach($bind as $k=>$v) $ct->bindValue($k,$v);
    $ct->execute();
    $total=(int)($ct->fetch()['c']??0);

    return ['rows'=>$rows, 'total'=>$total, 'sort'=>$sort, 'dir'=>$dir];
  }

  public function find(string $table, int $id): ?array {
    $st=$this->db->prepare("SELECT * FROM `$table` WHERE id=:id");
    $st->execute([':id'=>$id]); $r=$st->fetch();
    return $r?:null;
  }

  public function insert(string $table, array $data): array {
    unset($data['id']);
    if (!$data) return ['id'=>null];
    $cols='`'.implode('`,`', array_keys($data)).'`';
    $qs = implode(',', array_fill(0,count($data),'?'));
    $st=$this->db->prepare("INSERT INTO `$table` ($cols) VALUES ($qs)");
    $st->execute(array_values($data));
    return ['id'=>(int)$this->db->lastInsertId()];
  }

  public function update(string $table, int $id, array $data): int {
    unset($data['id']);
    if (!$data) return 0;
    $sets=[]; foreach($data as $k=>$v){ $sets[]="`$k`=?"; }
    $st=$this->db->prepare("UPDATE `$table` SET ".implode(',',$sets)." WHERE id=?");
    $vals=array_values($data); $vals[]=$id;
    $st->execute($vals); return $st->rowCount();
  }

  public function delete(string $table, int $id): int {
    $st=$this->db->prepare("DELETE FROM `$table` WHERE id=:id");
    $st->execute([':id'=>$id]); return $st->rowCount();
  }
}
