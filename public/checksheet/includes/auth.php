<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ensure_default_admin()
{
    try {
        $pdo = getPDO();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = (int)$stmt->fetchColumn();
        if ($count === 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
            $stmt->execute(['admin', $hash]);
        }
    } catch (Exception $e) {
        // ignore
    }
}
ensure_default_admin();

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $cached = $user ?: null;
    return $cached;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . 'index.php?page=login');
        exit;
    }
}

function logout()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function attempt_login(string $username, string $password, ?string &$error = null): bool
{
    $error = null;
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'Invalid username or password.';
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        $error = 'Invalid username or password.';
        return false;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];

    return true;
}
