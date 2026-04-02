<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssetIdToActivityLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('activity_logs', [
            'asset_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Reference to asset ID for audit trail filtering',
            ],
        ]);

        // Add index for faster queries
        $this->db->query('ALTER TABLE activity_logs ADD INDEX idx_asset_id (asset_id)');
    }

    public function down()
    {
        if ($this->db->fieldExists('asset_id', 'activity_logs')) {
            $this->forge->dropColumn('activity_logs', 'asset_id');
        }
    }
}
