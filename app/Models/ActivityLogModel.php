<?php

namespace App\Models;

use App\Models\BaseModel;

class ActivityLogModel extends BaseModel
{
    protected $table      = 'activity_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

	public function add($message, $user_id = null, $ip_address = false)
	{
		$loggedUserId = logged('id');
		$userId = (!empty($user_id) && $user_id !== 0) ? (int) $user_id : (!empty($loggedUserId) ? (int) $loggedUserId : 0);

		// Extract asset_id from message if present (e.g., "Asset #123 updated...")
		$assetId = $this->extractAssetId($message);

		return $this->create([
			'title' => $message,
			'user' => $userId,
			'asset_id' => $assetId,
			'ip_address' => !empty($ip_address) ? $ip_address : ip_address(),
		]);
	}

	/**
	 * Extract asset ID from activity message
	 * Looks for pattern "Asset #<number>"
	 */
	private function extractAssetId($message)
	{
		if (empty($message)) {
			return null;
		}

		// Match "Asset #NNN" pattern
		if (preg_match('/Asset #(\d+)/i', $message, $matches)) {
			return (int) $matches[1];
		}

		return null;
	}
}