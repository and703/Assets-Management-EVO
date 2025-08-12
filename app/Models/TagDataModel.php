<?php
namespace App\Models;

use CodeIgniter\Model;

class TagDataModel extends Model
{
    protected $table      = 'tag_data';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'log_id', 'tagID', 'count', 'memoryBank', 'memoryBankData',
        'RSSI', 'PC', 'phase', 'channelIndex', 'isVisible',
        'tagDetails', 'tagStatus', 'brandIDfound',
    ];
    public    $useTimestamps = false;
}
