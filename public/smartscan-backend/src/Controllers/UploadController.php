<?php
namespace App\Controllers;

use App\Response;
use App\Db;

final class UploadController
{
    /** /api/Upload/UploadAssignedAssetsTag  (POST) */
    public function uploadAssignedAssetsTag(): never
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!\is_array($payload)) {
            Response::json(['error' => 'Invalid JSON array'], 400);
        }

        $pdo  = Db::get();
        $stmt = $pdo->prepare(
            'INSERT INTO assigned_assets (ItemID, ItemBarcode, TagID, AssignedAt)
             VALUES (?,?,?,NOW())
             ON DUPLICATE KEY UPDATE TagID=VALUES(TagID), AssignedAt=NOW()'
        );

        $pdo->beginTransaction();
        foreach ($payload as $row) {
            $stmt->execute([
                $row['ItemID']      ?? null,
                $row['ItemBarcode'] ?? null,
                $row['TagID']       ?? null,
            ]);
        }
        $pdo->commit();

        Response::json(['status' => 'success', 'processed' => \count($payload)]);
    }

    /** /api/Upload/UploadData_Test  (POST) – inventory lines */
    public function uploadDataTest(): never
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!\is_array($payload)) {
            Response::json(['error' => 'Invalid JSON array'], 400);
        }

        $pdo       = Db::get();
        $stmtHead  = $pdo->prepare(
            'INSERT INTO inventory_header (InventoryID, InventoryDate, UserID)
             VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE InventoryDate=VALUES(InventoryDate)'
        );
        $stmtItems = $pdo->prepare(
            'INSERT INTO inventory_items
               (InventoryID, ItemID, ItemBarcode, LocationID, StatusID,
                Scanned, StatusUpdated, ReallocatedApplied, TagID, CreatedAt)
             VALUES (?,?,?,?,?,?,?,?,?,NOW())
             ON DUPLICATE KEY UPDATE
                LocationID          = VALUES(LocationID),
                StatusID            = VALUES(StatusID),
                Scanned             = VALUES(Scanned),
                StatusUpdated       = VALUES(StatusUpdated),
                ReallocatedApplied  = VALUES(ReallocatedApplied),
                TagID               = VALUES(TagID)'
        );

        $pdo->beginTransaction();
        foreach ($payload as $row) {
            $invId = $row['InventoryID'] ?? null;
            if (!$invId) {
                continue;
            }

            $stmtHead->execute([
                $invId,
                $row['InventoryDate'] ?? date('Y-m-d H:i:s'),
                $row['UserID']        ?? null,
            ]);

            $stmtItems->execute([
                $invId,
                $row['ItemID']               ?? null,
                $row['ItemBarcode']          ?? null,
                $row['LocationID']           ?? null,
                $row['StatusID']             ?? null,
                (int) ($row['Scanned']            ?? 0),
                (int) ($row['StatusUpdated']      ?? 0),
                (int) ($row['ReallocatedApplied'] ?? 0),
                $row['TagID']               ?? null,
            ]);
        }
        $pdo->commit();

        Response::json(['status' => 'success', 'processed' => \count($payload)]);
    }
}
