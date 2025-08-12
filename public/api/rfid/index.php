<?php
/**
 * POST /api/rfid/index.php
 * Native PHP endpoint with Bearer-token auth.
 * Requires PHP ≥ 8.0, PDO-MySQL.
 */
declare(strict_types=1);

/* ── 0. Tiny CI4 stub classes (MUST stay at top level) ───────────────────── */
namespace CodeIgniter\Config {
    if (!class_exists(BaseConfig::class)) {
        class BaseConfig { public function __construct() {} }
    }
}
namespace CodeIgniter\Database {
    if (!class_exists(Config::class)) {
        class Config extends \CodeIgniter\Config\BaseConfig { public function __construct() { parent::__construct(); } }
    }
}
/* ----------------------------- real logic --------------------------------- */
namespace {

    header('Content-Type: application/json; charset=utf-8');

    /* ── 1. Simple helpers ────────────────────────────────────────────────── */
    function respond(int $status, array $payload): never {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }
    function fail(int $status, string $msg): never { respond($status, ['status'=>'error','message'=>$msg]); }

    /** Sanitize incoming scalar: booleans→0/1, "null"→NULL, numeric strings→int/float */
    function cleanValue($v) {
        if (is_bool($v))                      return $v ? 1 : 0;   // bool → 0/1
        if ($v === 'null' || $v === '')       return null;         // literal "null" or empty → NULL
        if (is_string($v) && is_numeric($v))  return +$v;          // numeric string → int/float
        return $v;                                                // otherwise unchanged
    }

    /* ── 2. Load DB settings from CI4 -------------------------------------- */
    $projectRoot = dirname(__DIR__, 3);
    try {
        $db = (function (string $rootPath): array {
            if (!defined('APPPATH'))     define('APPPATH', $rootPath.'/app/');
            if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'production');
            require_once $rootPath . '/app/Config/Database.php';
            $cfg   = new \Config\Database();
            $group = $cfg->defaultGroup ?? 'default';
            $set   = $cfg->$group ?? null;
            if (!is_array($set)) throw new \RuntimeException('Invalid DB settings');
            return $set;
        })($projectRoot);
    } catch (\Throwable $e) { fail(500, $e->getMessage()); }

    /* ── 3. PDO connect (shared by auth + main logic) ----------------------- */
    try {
        $pdo = new \PDO(
            "mysql:host={$db['hostname']};dbname={$db['database']};charset=utf8mb4",
            $db['username'], $db['password'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    } catch (\PDOException $e) { fail(500, 'DB connection failed: '.$e->getMessage()); }

    /* ── 4. Bearer-token authentication ------------------------------------ */
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^\s*Bearer\s+([A-Za-z0-9._~+-]+)\s*$/', $authHeader, $m)) {
        fail(401, 'Missing or malformed Authorization: Bearer <token> header');
    }
    $token   = $m[1];
    $hash    = hash('sha256', $token);
    $nowUtc  = gmdate('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        "SELECT id, owner
           FROM api_tokens
          WHERE token_hash = :h
            AND revoked     = 0
            AND (expires_at IS NULL OR expires_at >= :now)
          LIMIT 1"
    );
    $stmt->execute([':h' => $hash, ':now' => $nowUtc]);
    $apiUser = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$apiUser) fail(401, 'Invalid or expired API token');
    $authenticatedAs = $apiUser['owner'];

    /* ── 5. Only POST accepted --------------------------------------------- */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        fail(405, 'Only POST is allowed');
    }

    /* ── 6. Parse JSON body ------------------------------------------------- */
    $bodyJson = file_get_contents('php://input');
    $bodyArr  = json_decode($bodyJson, true);
    if (!is_array($bodyArr)) {
        fail(422, 'Body must be a valid JSON object or array');
    }

    /* ── 7. Snapshot request meta ------------------------------------------ */
    $now     = date('Y-m-d H:i:s');
    $uri     = explode('?', $_SERVER['REQUEST_URI'])[0];
    $ip      = $_SERVER['REMOTE_ADDR']     ?? '';
    $agent   = $_SERVER['HTTP_USER_AGENT'] ?? '';

    /* ── 8. Main transactional work ---------------------------------------- */
    try {
        $pdo->beginTransaction();

        /* 8a. api_logs */
        $stmt = $pdo->prepare(
            'INSERT INTO api_logs
             (timestamp, method, uri, ip_address, scanner_location, user_agent,
              auth_owner, headers, query_params, post_data, body, files)
             VALUES
             (:ts,:method,:uri,:ip,:loc,:ua,
              :owner,:hdrs,:qry,:post,:body,:files)'
        );
        $stmt->execute([
            ':ts'    => $now,
            ':method'=> 'POST',
            ':uri'   => $uri,
            ':ip'    => $ip,
            ':loc'   => $_SERVER['HTTP_X_SCANNER_LOCATION'] ?? null,
            ':ua'    => $agent,
            ':owner' => $authenticatedAs,
            ':hdrs'  => json_encode(getallheaders(), JSON_UNESCAPED_SLASHES),
            ':qry'   => json_encode($_GET,  JSON_UNESCAPED_SLASHES),
            ':post'  => json_encode($_POST, JSON_UNESCAPED_SLASHES),
            ':body'  => $bodyJson,
            ':files' => json_encode($_FILES, JSON_UNESCAPED_SLASHES),
        ]);
        $logId = (int)$pdo->lastInsertId();

        /* 8b. tag_data rows */
        $tagIns = $pdo->prepare(
            'INSERT INTO tag_data
             (log_id, tagID, count, memoryBank, memoryBankData, RSSI, PC,
              phase, channelIndex, isVisible, tagDetails, tagStatus, brandIDfound)
             VALUES
             (:log_id,:tagID,:count,:memBank,:memData,:rssi,:pc,
              :phase,:chIdx,:visible,:details,:status,:brand)'
        );

        $payload     = isset($bodyArr[0]) ? $bodyArr : [$bodyArr]; // array-ify one-object payload
        $epcsToStamp = [];

        foreach ($payload as $tag) {
            $tagID = trim($tag['tagID'] ?? '');
            if ($tagID === '') throw new \InvalidArgumentException('Missing tagID');

            $tagIns->execute([
                ':log_id'  => $logId,
                ':tagID'   => $tagID,
                ':count'   => cleanValue($tag['count']          ?? null),
                ':memBank' => cleanValue($tag['memoryBank']     ?? null),
                ':memData' => cleanValue($tag['memoryBankData'] ?? null),
                ':rssi'    => cleanValue($tag['RSSI']           ?? null),
                ':pc'      => cleanValue($tag['PC']             ?? null),
                ':phase'   => cleanValue($tag['phase']          ?? null),
                ':chIdx'   => cleanValue($tag['channelIndex']   ?? null),
                ':visible' => cleanValue($tag['isVisible']      ?? null),  // bool → 0/1
                ':details' => cleanValue($tag['tagDetails']     ?? null),
                ':status'  => cleanValue($tag['tagStatus']      ?? null),
                ':brand'   => cleanValue($tag['brandIDfound']   ?? null),  // bool → 0/1
            ]);
            $epcsToStamp[] = $tagID;
        }

        /* 8c. Bulk-update assets.last_scan */
        if ($epcsToStamp) {
            $placeholders = rtrim(str_repeat('?,', count($epcsToStamp)), ',');
            $pdo->prepare("UPDATE assets SET last_scan = ? WHERE tagID IN ($placeholders)")
                ->execute(array_merge([$now], $epcsToStamp));
        }

        $pdo->commit();
        respond(201, ['status'=>'ok','log_id'=>$logId,'tags_in'=>count($payload)]);
    } catch (\Throwable $e) {
        $pdo->rollBack();
        fail(500, $e->getMessage());
    }
}
