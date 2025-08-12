<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\AssetModel;

class AssetImportService
{
    protected AssetModel $assetModel;

    public function __construct()
    {
        $this->assetModel = new AssetModel();
    }

    /**
     * Import an Excel/CSV file to the assets table.
     *
     * @param string $filePath
     * @return int Number of rows inserted
     */
    public function import(string $filePath): int
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $reader = ($ext === 'csv')
                    ? new Csv()
                    : new Xlsx();

        $spreadsheet = $reader->load($filePath);
        $rows        = $spreadsheet->getActiveSheet()->toArray();
        $records     = [];

        foreach ($rows as $idx => $row) {
            if ($idx === 0) { // Skip header
                continue;
            }
            // Skip empty row
            if (trim($row[0] ?? '') === '' && trim($row[1] ?? '') === '') {
                continue;
            }

            $records[] = [
                'tagID'              => trim((string)($row[0] ?? '')),
                'asset'              => (int) trim((string)($row[1] ?? 0)),
                'subnumber'          => (int) trim((string)($row[2] ?? 0)),
                'joint_assets_number'=> (int) trim((string)($row[3] ?? 0)),
                'capitalized_on'     => $this->formatExcelDate($row[4] ?? null),
                'bal_sh_acct_apc'    => trim((string)($row[5] ?? '')),
                'asset_class'        => trim((string)($row[6] ?? '')),
                'category'           => trim((string)($row[7] ?? '')),
                'asset_description'  => trim((string)($row[8] ?? '')),
                'quantity'           => (int) trim((string)($row[9] ?? 0)),
                'uom'                => trim((string)($row[10] ?? '')),
                'po'                 => trim((string)($row[11] ?? '')),
                'location'           => trim((string)($row[12] ?? '')),
                'created_at'         => date('Y-m-d H:i:s'),
                'last_scan'          => null
            ];
        }

        if (!empty($records)) {
            $this->assetModel->insertBatch($records);
        }

        return count($records);
    }

    /**
     * Convert Excel serialized date or string date to Y-m-d format.
     *
     * @param mixed $value
     * @return string|null
     */
    protected function formatExcelDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // If numeric, treat as Excel serial date
        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject($value);
                return $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Otherwise attempt parse
        $dt = date_create($value);
        return $dt ? $dt->format('Y-m-d') : null;
    }
}
