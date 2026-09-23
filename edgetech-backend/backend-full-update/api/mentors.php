<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $db->query("SELECT * FROM mentors WHERE status = 'published' ORDER BY sort_order ASC, created_at ASC");
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'role' => $row['role'],
            'company' => $row['company'],
            'image' => $row['image'],
            'linkedinUrl' => $row['linkedin_url'],
        ];
    }
    respond(['success' => true, 'mentors' => $items]);
}

require_admin_session();

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['name', 'role']);
    $company = $data['company'] ?? '';
    $image = $data['image'] ?? '';
    $linkedinUrl = $data['linkedinUrl'] ?? '';
    $sortOrder = (int) ($data['sortOrder'] ?? 0);
    $status = $data['status'] ?? 'published';

    $stmt = $db->prepare('INSERT INTO mentors (name, role, company, image, linkedin_url, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssis', $data['name'], $data['role'], $company, $image, $linkedinUrl, $sortOrder, $status);
    $stmt->execute();
    respond(['success' => true, 'id' => $stmt->insert_id], 201);
}

if ($method === 'PUT') {
    $data = json_input();
    $id = (int) ($data['id'] ?? 0);
    if (!$id) {
        respond_error('Mentor id is required.', 422);
    }
    require_fields($data, ['name', 'role']);
    $company = $data['company'] ?? '';
    $image = $data['image'] ?? '';
    $linkedinUrl = $data['linkedinUrl'] ?? '';
    $sortOrder = (int) ($data['sortOrder'] ?? 0);
    $status = $data['status'] ?? 'published';

    $stmt = $db->prepare('UPDATE mentors SET name=?, role=?, company=?, image=?, linkedin_url=?, sort_order=?, status=? WHERE id=?');
    $stmt->bind_param('sssssisi', $data['name'], $data['role'], $company, $image, $linkedinUrl, $sortOrder, $status, $id);
    $stmt->execute();
    respond(['success' => true]);
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        respond_error('id is required.', 422);
    }
    $stmt = $db->prepare('DELETE FROM mentors WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true]);
}

respond_error('Method not allowed.', 405);
