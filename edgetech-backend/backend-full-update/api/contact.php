<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['name', 'email', 'phone', 'message']);

    if (!is_valid_email($data['email'])) {
        respond_error('Enter a valid email address.', 422);
    }

    $courseInterest = $data['course'] ?? null;

    $stmt = $db->prepare(
        'INSERT INTO contact_messages (name, email, phone, course_interest, message) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssss', $data['name'], $data['email'], $data['phone'], $courseInterest, $data['message']);
    $stmt->execute();

    respond(['success' => true, 'message' => 'Thanks for reaching out — our team will contact you within 24 hours.'], 201);
}

if ($method === 'GET') {
    require_admin_session();
    $result = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    respond(['success' => true, 'messages' => $messages]);
}

if ($method === 'PUT') {
    require_admin_session();
    $data = json_input();
    $id = (int) ($data['id'] ?? 0);
    if (!$id || empty($data['status'])) {
        respond_error('id and status are required.', 422);
    }
    $stmt = $db->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $data['status'], $id);
    $stmt->execute();
    respond(['success' => true]);
}

respond_error('Method not allowed.', 405);
