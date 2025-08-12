<?php
// app/Models/AssetAuditModel.php
namespace App\Models;

use CodeIgniter\Model;

class AssetAuditModel extends Model
{
    protected $table         = 'asset_audits';
    protected $returnType    = 'array';
    protected $allowedFields = ['asset_id', 'user_id', 'changed_at', 'changes'];
    public    $useTimestamps = false;
}
