<?php
// app/Controllers/AssetAuditController.php
namespace App\Controllers;

use App\Controllers\AdminBaseController;
use App\Models\AssetAuditModel;

class AssetAuditController extends AdminBaseController
{
    public $title = 'Assets Audit Logs';
    public $menu = 'assets_audits';
    
    public function index($assetId)
    {
        $audits = (new AssetAuditModel())
                    ->where('asset_id', $assetId)
                    ->orderBy('changed_at', 'desc')
                    ->findAll();
        //var_dump($audits); // Debugging line, remove in production
        if (empty($audits)) {
            return redirect()->to(base_url(''))
                             ->with('error', 'No audit logs found for this asset.');
        }else{
            return view('user/asset_audits/index', ['audits' => $audits]);
        }
    }
    // GET /assets/{id}/audits/data   → returns fresh audits as JSON
    public function data($assetId)
    {
        $db = \Config\Database::connect();
        
        $audits = $db->table('asset_audits')
                    ->select('asset_audits.*, users.name as user_name')
                    ->join('users', 'users.id = asset_audits.user_id', 'left')
                    ->where('asset_audits.asset_id', $assetId)
                    ->orderBy('asset_audits.changed_at', 'desc')
                    ->get()
                    ->getResultArray();

        // Add user_display field for easier viewing
        foreach ($audits as &$audit) {
            $audit['user_display'] = $audit['user_id'] > 0 && $audit['user_name'] 
                ? ($audit['user_name'] . ' #' . $audit['user_id'])
                : 'System';
        }

        return $this->response->setJSON($audits);
    }
}
