<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['name', 'email', 'phone', 'courseTitle']);

    if (!is_valid_email($data['email'])) {
        respond_error('Enter a valid email address.', 422);
    }

    $courseId = null;
    if (!empty($data['courseSlug'])) {
        $stmt = $db->prepare('SELECT id FROM courses WHERE slug = ? LIMIT 1');
        $stmt->bind_param('s', $data['courseSlug']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            $courseId = (int) $row['id'];
        }
    }

    $city = $data['city'] ?? null;
    $qualification = $data['qualification'] ?? null;
    $message = $data['message'] ?? null;

    $stmt = $db->prepare(
        'INSERT INTO course_enquiries (course_id, course_title, name, email, phone, city, qualification, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'isssssss',
        $courseId, $data['courseTitle'], $data['name'], $data['email'], $data['phone'], $city, $qualification, $message
    );
    $stmt->execute();

    respond(['success' => true, 'message' => 'Thanks! Our team will contact you shortly.'], 201);
}

if ($method === 'GET') {
    require_admin_session();

    $status = $_GET['status'] ?? null;
    if ($status) {
        $stmt = $db->prepare('SELECT * FROM course_enquiries WHERE status = ? ORDER BY created_at DESC');
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $db->query('SELECT * FROM course_enquiries ORDER BY created_at DESC');
    }

    $leads = [];
    while ($row = $result->fetch_assoc()) {
        $leads[] = $row;
    }
    respond(['success' => true, 'leads' => $leads]);
}

if ($method === 'PUT') {
    require_admin_session();
    $data = json_input();
    $id = (int) ($data['id'] ?? 0);
    if (!$id || empty($data['status'])) {
        respond_error('id and status are required.', 422);
    }
    $stmt = $db->prepare('UPDATE course_enquiries SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $data['status'], $id);
    $stmt->execute();
    respond(['success' => true]);
}

if ($method === 'DELETE') {
    require_admin_session();
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        respond_error('id is required.', 422);
    }
    $stmt = $db->prepare('DELETE FROM course_enquiries WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true]);
}

respond_error('Method not allowed.', 405);
