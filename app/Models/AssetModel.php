<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetModel extends Model
{
    protected $table            = 'assets';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'tagID',
        'asset',
        'subnumber',
        'joint_assets_number',
        'capitalized_on',
        'asset_class',
        'asset_class_desc',
        'category',
        'asset_description',
        'quantity',
        'perpcs_id',
        'sn',
        'uom',
        'po',
        'location',
        'bar_kar',
        'created_at',
        'last_scan'
    ];

    protected $useTimestamps    = false; // we set created_at manually

    // Simple validation (optional, adjust as needed)
    protected $validationRules  = [
        'tagID'          => 'permit_empty|min_length[1]|max_length[255]',
        'asset'          => 'permit_empty|integer',
        'sn'             => 'permit_empty|min_length[1]|max_length[255]',
        'capitalized_on' => 'permit_empty|valid_date[Y-m-d]',
    ];
    protected $beforeUpdate = ['logAudit'];
    
    /**
     * Logs any field changes to asset_audits.
     */
    protected function logAudit(array $data): array
    {
        // $data['id'] can be an array (batch) or scalar
        $ids = is_array($data['id']) ? $data['id'] : [$data['id']];

        $auditModel = new \App\Models\AssetAuditModel();
        $userId     = session('user_id') ?? null;           // adjust to your auth lib
        $now        = date('Y-m-d H:i:s');

        foreach ($ids as $id) {
            $before = $this->find($id);                     // original DB row
            if (! $before) { continue; }

            $after   = $data['data'];                      // new values being saved
            $changes = [];

            foreach ($after as $field => $new) {
                // Cast to string to avoid strict-type false positives with null/ints
                $old = array_key_exists($field, $before) ? $before[$field] : null;
                if ((string) $old !== (string) $new) {
                    $changes[$field] = ['old' => $old, 'new' => $new];
                }
            }

            if ($changes) {
                $auditModel->insert([
                    'asset_id'   => $id,
                    'user_id'    => $userId,
                    'changed_at' => $now,
                    'changes'    => json_encode($changes, JSON_UNESCAPED_UNICODE),
                ]);
            }
        }

        // Special rule: if location changed, nuke last_scan
        if (isset($changes['location'])) {
            $data['data']['last_scan'] = null;
        }

        return $data;   // let CI continue updating the row
    }
}
