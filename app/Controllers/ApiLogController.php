<?php

/* -------------------------------------------------------------------------
 * ApiLogController – list + detail
 * app/Controllers/ApiLogController.php
 * -------------------------------------------------------------------------*/

namespace App\Controllers;

use App\Controllers\AdminBaseController;
use App\Models\ApiLogModel;
use App\Models\TagDataModel;
use CodeIgniter\Controller;

class ApiLogController extends AdminBaseController
{
    
    public $title = 'API Logs';
    public $menu = 'assets_api_logs';
    protected ApiLogModel $logs;
    protected TagDataModel $tags;

    public function __construct()
    {
        $this->logs = new ApiLogModel();
        $this->tags = new TagDataModel();
    }

    // --------------------------- LIST
    public function index()
    {
        $pager          = service('pager');
        $page           = (int) ($this->request->getGet('page') ?? 1);
        $perPage        = 9999;
        $data['logs']   = $this->logs->orderBy('id', 'DESC')->paginate($perPage, 'default', $page);
        $data['pager']  = $this->logs->pager;
        return view('user/api_logs/list', $data);
    }

    /**
     * Return JSON for DataTables auto-refresh.
     * Route: GET assets/data
     */
    public function data()
    {
        $logModel = new ApiLogModel();
        return $this->response->setJSON($logModel->findAll());
    }

    // --------------------------- DETAIL (the piece you asked)
    public function detail(int $id)
    {
        $log = $this->logs->find($id);
        if (! $log) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Log $id not found");
        }
        $tags = $this->tags->where('log_id', $id)->findAll();
        return view('user/api_logs/detail', ['log' => $log, 'tags' => $tags]);
    }
}
