<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';

// The auth endpoint always works with the session directly.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];
$db = get_db();

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['username', 'password']);

    $stmt = $db->prepare('SELECT id, username, password_hash, full_name FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $data['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin || !password_verify($data['password'], $admin['password_hash'])) {
        respond_error('Invalid username or password.', 401);
    }

    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];

    respond(['success' => true, 'admin' => ['id' => $admin['id'], 'username' => $admin['username'], 'fullName' => $admin['full_name']]]);
}

if ($method === 'GET') {
    if (empty($_SESSION['admin_id'])) {
        respond(['success' => true, 'authenticated' => false]);
    }
    respond(['success' => true, 'authenticated' => true, 'username' => $_SESSION['admin_username']]);
}

if ($method === 'DELETE') {
    session_destroy();
    respond(['success' => true]);
}

respond_error('Method not allowed.', 405);
