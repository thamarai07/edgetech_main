<?php
// Run this ONCE in the browser after importing database/schema.sql to create
// the default admin login. Delete or rename this file afterwards.
require_once __DIR__ . '/config/database.php';

$username = 'admin';
$password = 'Admin@123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$db = get_db();
$stmt = $db->prepare(
    'INSERT INTO admin_users (username, password_hash, full_name) VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
);
$fullName = 'Edge Tech Admin';
$stmt->bind_param('sss', $username, $hash, $fullName);
$stmt->execute();

echo "Admin user ready.<br>Username: <b>$username</b><br>Password: <b>$password</b>";
echo "<br><br>Please delete backend/setup_admin.php now for security, and change the password after logging in.";
