<?php
/**
 * POST /api/rfid/index.php
 * Native PHP endpoint with scalability improvements.
 * Requires PHP ≥ 8.0, PDO-MySQL, and optionally APCu for caching.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');  // Security enhancement

// 1. Helper Functions
function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function fail(int $status, string $msg): void {
    respond($status, ['status' => 'error', 'message' => $msg]);
}

function cleanValue(mixed $v): mixed {
    if (is_bool($v)) return $v ? 1 : 0;
    if ($v === 'null' || $v === '') return null;
    if (is_string($v) && is_numeric($v)) return +$v;
    if (is_string($v) && strlen($v) > 255) throw new \InvalidArgumentException('Value exceeds maximum length');
    return $v;
}

// 2. Load DB Settings (as in original)
$projectRoot = dirname(__DIR__, 3);
try {
    $db = (function (string $rootPath): array {
        if (!defined('APPPATH')) define('APPPATH', $rootPath . '/app/');
        require_once $rootPath . '/app/Config/Database.php';
        $cfg = new \Config\Database();
        $group = $cfg->defaultGroup ?? 'default';
        return (array) $cfg->$group;
    })($projectRoot);
} catch (\Throwable $e) { fail(500, $e->getMessage()); }

// 3. PDO Connection
try {
    $pdo = new \PDO(
        "mysql:host={$db['hostname']};dbname={$db['database']};charset=utf8mb4",
        $db['username'], $db['password'],
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
    );
} catch (\PDOException $e) { fail(500, 'DB connection failed: ' . $e->getMessage()); }

// 4. Simple Caching for Token (Using APCu if available, otherwise file-based)
if (function_exists('apcu_fetch')) {
    // Use APCu for in-memory caching
    function getCachedToken(string $key) {
        $cached = apcu_fetch($key);
        return $cached !== false ? $cached : null;
    }
    
    function setCachedToken(string $key, array $value, int $ttl = 300) {
        apcu_store($key, $value, $ttl);
    }
} else {
    // Fallback to file-based caching (simple and native)
    function getCachedToken(string $key) {
        $file = __DIR__ . '/cache/' . md5($key) . '.json';  // Store in a cache directory
        if (file_exists($file) && (time() - filemtime($file) < 300)) {  // 5-minute TTL
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }
    
    function setCachedToken(string $key, array $value) {
        $file = __DIR__ . '/cache/' . md5($key) . '.json';
        file_put_contents($file, json_encode($value));
    }
}

// 5. Main Logic
try {
    // Authentication
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^\s*Bearer\s+([A-Za-z0-9._~+-]+)\s*$/', $authHeader, $matches)) {
        fail(401, 'Invalid Authorization header');
    }
    
    $token = $matches[1];
    $hash = hash('sha256', $token);
    $nowUtc = gmdate('Y-m-d H:i:s');
    
    $cachedUser = getCachedToken('token:' . $hash);
    if ($cachedUser) {
        $apiUser = $cachedUser;
    } else {
        $stmt = $pdo->prepare("SELECT id, owner FROM api_tokens WHERE token_hash = :h AND revoked = 0 AND (expires_at IS NULL OR expires_at >= :now) LIMIT 1");
        $stmt->execute([':h' => $hash, ':now' => $nowUtc]);
        $apiUser = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($apiUser) {
            setCachedToken('token:' . $hash, $apiUser);  // Cache it
        }
    }
    
    if (!$apiUser) fail(401, 'Invalid or expired token');
    $authenticatedAs = $apiUser['owner'];
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'Only POST is allowed');
    
    $bodyJson = file_get_contents('php://input');
    $bodyArr = json_decode($bodyJson, true);
    if (!is_array($bodyArr)) fail(422, 'Invalid JSON body');
    
    $payload = isset($bodyArr[0]) ? $bodyArr : [$bodyArr];  // Array-ify
    $now = date('Y-m-d H:i:s');
    
    $pdo->beginTransaction();
    
    // Insert into api_logs (as before)
    $stmtLog = $pdo->prepare(
            'INSERT INTO api_logs
             (timestamp, method, uri, ip_address, scanner_location, user_agent,
              auth_owner, headers, query_params, post_data, body, files)
             VALUES
             (:ts,:method,:uri,:ip,:loc,:ua,
              :owner,:hdrs,:qry,:post,:body,:files)');
    $stmtLog->execute([
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
        ]);  // Replace with your values
    $logId = (int)$pdo->lastInsertId();
    
    // Batched Insert for tag_data
    if (!empty($payload)) {
        $insertValues = [];
        $params = [];
        foreach ($payload as $index => $tag) {
            $tagID = trim($tag['tagID'] ?? '');
            if ($tagID === '') throw new \InvalidArgumentException('Missing tagID');
            
            $insertValues[] = "( :logId, :tagID_{$index}, :count_{$index}, :memBank_{$index}, ... )";  // List your fields
            $params[":tagID_{$index}"] = $tagID;
            $params[":count_{$index}"] = cleanValue($tag['count'] ?? null);
            // Add other fields as needed
        }
        
        $sql = "INSERT INTO tag_data (log_id, tagID, count, ...) VALUES " . implode(', ', $insertValues);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    // Bulk update assets.last_scan (as before)
    
    $pdo->commit();
    respond(201, ['status' => 'ok', 'log_id' => $logId, 'tags_in' => count($payload)]);
} catch (\Throwable $e) {
    $pdo->rollBack();
    fail(500, $e->getMessage());
}