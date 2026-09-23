<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $db->query("SELECT * FROM testimonials WHERE status = 'published' ORDER BY created_at DESC");
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'role' => $row['role'],
            'course' => $row['course'],
            'rating' => (int) $row['rating'],
            'image' => $row['image'],
            'quote' => $row['quote'],
        ];
    }
    respond(['success' => true, 'testimonials' => $items]);
}

require_admin_session();

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['name', 'quote']);
    $role = $data['role'] ?? '';
    $course = $data['course'] ?? '';
    $rating = (int) ($data['rating'] ?? 5);
    $image = $data['image'] ?? '';

    $stmt = $db->prepare('INSERT INTO testimonials (name, role, course, rating, image, quote) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssiss', $data['name'], $role, $course, $rating, $image, $data['quote']);
    $stmt->execute();
    respond(['success' => true, 'id' => $stmt->insert_id], 201);
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        respond_error('id is required.', 422);
    }
    $stmt = $db->prepare('DELETE FROM testimonials WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true]);
}

respond_error('Method not allowed.', 405);
