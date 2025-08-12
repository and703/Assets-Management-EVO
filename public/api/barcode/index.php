<?php
declare(strict_types=1);
header('Content-Type: application/json');

// CORS (relax as needed)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    exit;
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

$config = require __DIR__ . '/_config.php';

// Optional Bearer token
if ($config['API_TOKEN'] !== '') {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
    if (stripos($auth, 'Bearer ') !== 0 || substr($auth, 7) !== $config['API_TOKEN']) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}

// Input (?barcode=... ; allow JSON body too)
$barcode = $_GET['barcode'] ?? null;
if ($barcode === null) {
    $in = json_decode(file_get_contents('php://input') ?: 'null', true);
    if (is_array($in) && isset($in['barcode'])) $barcode = $in['barcode'];
}
$barcode = is_string($barcode) ? trim($barcode) : '';
if ($barcode === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'barcode is required']);
    exit;
}

$touch = isset($_GET['touch']) && $_GET['touch'] !== '0'; // optional: update last_scan

require __DIR__ . '/_db.php';

// Table/columns per your schema
$sql = "SELECT
            id, asset, tagID, subnumber, joint_assets_number, capitalized_on,
            asset_class, asset_class_desc, category, asset_description,
            quantity, perpcs_id, sn, uom, po, location, bar_kar,
            created_at, updated_at, last_scan
        FROM assets
        WHERE bar_kar = :barcode
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':barcode' => $barcode]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    echo json_encode(['status' => 'not_found', 'message' => 'No asset for this barcode']);
    exit;
}

// Optional: mark last_scan = NOW() if touch=1
if ($touch) {
    $upd = $pdo->prepare("UPDATE assets SET last_scan = NOW() WHERE id = :id");
    $upd->execute([':id' => $row['id']]);
    $row['last_scan'] = date('Y-m-d H:i:s');
}

echo json_encode([
    'status'  => 'ok',
    'barcode' => $barcode,
    'data'    => $row,
]);
