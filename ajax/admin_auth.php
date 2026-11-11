<?php
/**
 * Amethystclothing.lk — Admin Authentication Endpoint
 * POST /ajax/admin_auth.php
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please provide both username and password.']);
    exit;
}

try {
    $stmt = db()->prepare("SELECT id, name, username, email, password_hash, role, is_active FROM admins WHERE (username = :u1 OR email = :u2) LIMIT 1");
    $stmt->execute(['u1' => $username, 'u2' => $username]);
    $admin = $stmt->fetch();

    if (!$admin || (int)$admin['is_active'] !== 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials or inactive account.']);
        exit;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        exit;
    }

    // Success: Establish admin session
    $_SESSION['admin_id'] = (int)$admin['id'];

    // Update last login
    $upd = db()->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = ?");
    $upd->execute([$admin['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Authentication successful.',
        'redirect' => base_url('admin/dashboard')
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal authentication error: ' . $e->getMessage()]);
    exit;
}
