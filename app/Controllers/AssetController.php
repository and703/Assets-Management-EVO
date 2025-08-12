<?php
/**
 * ---------------------------------------------------------------------------
 * AssetController – full CRUD + Excel/CSV import + EPC generation
 * ---------------------------------------------------------------------------
 * Location: app/Controllers/AssetController.php
 *
 * Route suggestions (add to app/Config/Routes.php):
 *   $routes->get ('assets',            'AssetController::index');       // list
 *   $routes->get ('assets/create',     'AssetController::create');      // form
 *   $routes->post('assets',            'AssetController::store');       // save new
 *   $routes->get ('assets/(:num)',     'AssetController::show/$1');     // detail
 *   $routes->get ('assets/(:num)/edit','AssetController::edit/$1');     // edit form
 *   $routes->put ('assets/(:num)',     'AssetController::update/$1');   // update
 *   $routes->delete('assets/(:num)',   'AssetController::destroy/$1');  // delete
 *   $routes->get ('assets/import',     'AssetController::showImportForm');
 *   $routes->post('assets/import',     'AssetController::importExcel');
 * ---------------------------------------------------------------------------
 */

namespace App\Controllers;

use App\Controllers\AdminBaseController;
use App\Models\AssetModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpDate;

use mysqli;

class AssetController extends AdminBaseController
{
    
    public $title = 'Assets Management';
    public $menu = 'assets_import';

    protected AssetModel $assets;
    protected array $validationRules = [
        'location'          => 'permit_empty|string',
    ];

    public function __construct()
    {
        $this->assets = new AssetModel();
        helper(['form', 'url', 'epc', 'text']);
    }

    /**
     * Return JSON for DataTables auto-refresh.
     * Route: GET assets/data
     */
    public function data()
    {
        $this->permissionCheck('page_assets');
        $assetModel = new AssetModel();
        return $this->response->setJSON($assetModel->findAll());
    }

    // Detailed single asset view
    public function show(int $id)
    {
        $asset = $this->assets->find($id);
        if (!$asset) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Asset not found');
        }
        return view('user/assets/show', ['asset' => $asset]);
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE – show form
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        $this->permissionCheck('page_assets');
        return view('user/assets/create');
    }

    // Store new asset row
    public function store()
    {
        $input = $this->request->getPost();
        if (! $this->validate($this->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // auto EPC per-piece logic even for manual create (qty default 1)
        $qty = (int) ($input['quantity'] ?? 1);
        $batch = [];
        for ($i = 1; $i <= $qty; $i++) {
            $batch[] = [
                'tagID'             => encode_giai96($input['asset'], $i),
                'asset'             => $input['asset'],
                'subnumber'         => $input['subnumber'] ?? 0,
                'joint_assets_number'=> $input['joint_assets_number'] ?? 0,
                'capitalized_on'    => $input['capitalized_on'] ?? null,
                'asset_class'       => $input['asset_class'] ?? null,
                'asset_class_desc'  => $input['asset_class_desc'] ?? null,
                'category'          => $input['category'] ?? null,
                'asset_description' => $input['asset_description'],
                'quantity'          => 1,
                'uom'               => $input['uom'] ?? null,
                'po'                => $input['po'] ?? null,
                'location'          => $input['location'] ?? null,
                'bar_kar'           => $input['bar_kar'] ?? null,
                'perpcs_id'         => "$qty/$i",
                'created_at'        => date('Y-m-d H:i:s'),
            ];
        }
        $this->assets->insertBatch($batch);
        return redirect()->to('/assets')->with('message', 'Asset(s) created');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT – show edit form
    // ──────────────────────────────────────────────────────────────
    public function edit(int $id)
    {
        $asset = $this->assets->find($id);
        if (!$asset) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Asset not found');
        }
        return view('user/assets/edit', ['asset' => $asset]);
    }

    // Update asset (only single row – qty remains 1 here)

    public function update(int $id)
    {
        $assetModel = new AssetModel();

        // ---- original row (to detect location move) ----
        $original = $assetModel->find($id);
        if (! $original) {
            return redirect()->to('')->with('error', 'Asset not found');
        }

        // Validation rules live in the model for reuse
        if (! $this->validate($assetModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        unset($data['_method']);          // from hidden spoof field
        // ------------------------------------------------------------
        //  NEW: if location changed → reset last_scan to NULL
        // ------------------------------------------------------------
        if (array_key_exists('location', $data) && $data['location'] !== $original['location']) {
            $data['last_scan'] = null;
        }

        // Allow quantity edits to propagate per‑piece rows? For now, just update.
        $assetModel->update($id, $data);

        return redirect()->to('')->with('message', 'Asset updated');
    }

    // ──────────────────────────────────────────────────────────────
    // DELETE
    // ──────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        if ($this->assets->delete($id)) {
            return redirect()->to('/assets')->with('message', 'Asset deleted');
        }
        return redirect()->to('/assets')->with('error', 'Unable to delete asset');
    }

    //------------------------------------------------------------------
    // XLSX/CSV IMPORT with TRUNCATE BEFORE INSERT
    //------------------------------------------------------------------
    public function showImportForm()
    {
        $this->permissionCheck('page_assets');
        return view('user/assets/import_form');
    }
    //------------------------------------------------------------------
    //  IMPORT – no-truncate  •  skip if Asset Number already exists
    //------------------------------------------------------------------
    public function importExcel()
    {
        helper('text');

        $file = $this->request->getFile('asset_file');
        if (! $file || ! $file->isValid())
            return redirect()->back()->with('error', 'No file selected or upload error');

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'csv']))
            return redirect()->back()->with('error', 'File must be XLSX or CSV');

        /* save to tmp -------------------------------------------------- */
        $tmp = WRITEPATH . 'uploads/' . bin2hex(random_bytes(8)) . '.' . $ext;
        $file->move(dirname($tmp), basename($tmp));

        $reader = $ext === 'csv' ? new Csv() : new Xlsx();
        if ($reader instanceof Csv) {
            $reader->setDelimiter(',');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            /* 0) build a set of existing asset numbers ------------------ */
            $assetModel      = new AssetModel();
            $existingNumbers = array_flip(
                $assetModel->select('joint_assets_number')->findColumn('joint_assets_number') ?? []
            );
            // keep track of numbers we insert in *this* batch
            $seenInThisRun = [];

            /* 1) parse sheet & build batch ----------------------------- */
            $rows  = $reader->load($tmp)->getActiveSheet()->toArray(null, true, true, true);
            $T     = static fn ($v) => trim((string) $v);
            $batch = [];

            foreach ($rows as $idx => $row) {
                if ($idx === 1) continue; // header

                $assetNum = $T($row['C'] ?? '');
                if ($assetNum === '') continue;

                // qty only used for inserts (per-piece expansion)
                $qty = max(1, (int) $T($row['I'] ?? 1));

                // Already exists: try update (per-field, skip empty cells)
                if (isset($existingNumbers[$assetNum])) {
                    $existing = $assetModel->where('joint_assets_number', $assetNum)->first();
                    if (! $existing) continue;

                    $fields = [
                        'asset'               => $T($row['A'] ?? null),
                        'subnumber'           => $T($row['B'] ?? null),
                        'joint_assets_number' => $assetNum,
                        'capitalized_on'      => $this->excelDate($row['D'] ?? null), // may be null
                        'asset_class'         => $T($row['F'] ?? null),
                        'asset_class_desc'    => $T($row['G'] ?? null),
                        'category'            => $T($row['G'] ?? null),
                        'asset_description'   => $T($row['H'] ?? null),
                        'quantity'            => $T($row['I'] ?? null),
                        'uom'                 => $T($row['J'] ?? null),
                        'po'                  => $T($row['K'] ?? null),
                        'location'            => $T($row['L'] ?? null),
                        'bar_kar'             => $T($row['M'] ?? null),
                    ];

                    $update = [];
                    foreach ($fields as $key => $value) {
                        // only update if provided (not '' and not null) and actually different
                        if ($value !== '' && $value !== null && (!array_key_exists($key, $existing) || $value != $existing[$key])) {
                            $update[$key] = $value;
                        }
                    }

                    if (! empty($update)) {
                        $update['updated_at'] = date('Y-m-d H:i:s');
                        $assetModel->update($existing['id'], $update);
                    }

                    continue; // skip insert logic
                }

                // Not existing: insert per-piece
                if (isset($seenInThisRun[$assetNum])) continue;
                $seenInThisRun[$assetNum] = true;

                for ($p = 1; $p <= $qty; $p++) {
                    $batch[] = [
                        'tagID'               => encode_giai96($assetNum, $p),
                        'asset'               => $T($row['A'] ?? ''),
                        'subnumber'           => $T($row['B'] ?? ''),
                        'joint_assets_number' => $assetNum,
                        'capitalized_on'      => $this->excelDate($row['D'] ?? null),
                        'asset_class'         => $T($row['F'] ?? ''),
                        'asset_class_desc'    => $T($row['G'] ?? ''),
                        'category'            => $T($row['G'] ?? ''),
                        'asset_description'   => $T($row['H'] ?? ''),
                        'quantity'            => $T($row['I'] ?? ''),
                        'uom'                 => $T($row['J'] ?? ''),
                        'po'                  => $T($row['K'] ?? ''),
                        'location'            => $T($row['L'] ?? ''),
                        'bar_kar'             => $T($row['M'] ?? ''),
                        'perpcs_id'           => "$p/$qty",
                        'created_at'          => date('Y-m-d H:i:s'),
                        'last_scan'           => null,
                    ];
                }
            }

            /* 2) insert -------------------------------------------------- */
            if ($batch) {
                $assetModel->insertBatch($batch);
            }

            unlink($tmp);
            $db->transComplete();

            $msg = count($batch)
                ? 'Import finished. Added '.count($batch).' new rows; existing assets updated if changed.'
                : 'No new assets – all rows matched existing records.';

            return redirect()->to('import')->with('message', $msg);

        } catch (\Throwable $e) {
            unlink($tmp);
            $db->transRollback();
            return redirect()->back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    /*------------------------------------------------------------------*/
    private function excelDate($value)
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value))   // Excel serial
            return PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        if ($dt = date_create($value))
            return $dt->format('Y-m-d');
        return null;
    }


}
