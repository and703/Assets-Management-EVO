<?php
namespace Models;
use PDO;

abstract class BaseModel {
  protected PDO $db;
  public function __construct(PDO $db){ $this->db=$db; }
}
