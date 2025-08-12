<?php
namespace App\Models;

use CodeIgniter\Model;

class ApiLogModel extends Model
{
    protected $table      = 'api_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'timestamp', 'method', 'uri', 'ip_address', 'scanner_location',
        'user_agent', 'headers', 'query_params', 'post_data', 'body', 'files',
    ];
    public    $useTimestamps = false;
}
