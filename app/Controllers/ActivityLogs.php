<?php
namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Controllers\AdminBaseController;

class ActivityLogs extends AdminBaseController
{
    
    public $title = 'Activity Logs';
    public $menu = 'activity_logs';

	public function index()
	{
        $this->permissionCheck('activity_log_list');
		$ip = !empty(get('ip')) ? urldecode(get('ip')) : false;
		$user = !empty(get('user')) ? (int) get('user') : false;
		$assetId = !empty(get('asset')) ? (int) get('asset') : false;

		$activityModel = new ActivityLogModel();
		$query = $activityModel;

		if ($ip) {
			$query = $query->where('ip_address', $ip);
		}

		if ($user) {
			$query = $query->where('user', $user);
		}

		if ($assetId) {
			$query = $query->where('asset_id', $assetId);
		}

		$activity_logs = $query->orderBy('id', 'desc')->findAll();

		$filter_ip = $ip;
		$filter_user = $user;
		$filter_asset = $assetId;
		return  view('admin/activity_logs/list', compact('activity_logs', 'filter_ip', 'filter_user', 'filter_asset'));

	}

	public function view($id)
	{
        $this->permissionCheck('activity_log_view');
		$activity = (new ActivityLogModel)->getById($id);
		return  view('admin/activity_logs/view', compact('activity'));

	}
    
}
