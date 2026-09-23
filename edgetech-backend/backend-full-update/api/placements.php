<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $db->query('SELECT * FROM placements ORDER BY placed_on DESC, created_at DESC');
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    $stats = $db->query('SELECT COUNT(*) as total, AVG(package_lpa) as avg_package, MAX(package_lpa) as top_package FROM placements')->fetch_assoc();

    respond(['success' => true, 'placements' => $items, 'stats' => [
        'total' => (int) $stats['total'],
        'avgPackage' => $stats['avg_package'] ? round((float) $stats['avg_package'], 1) : 0,
        'topPackage' => $stats['top_package'] ? (float) $stats['top_package'] : 0,
    ]]);
}

require_admin_session();

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['studentName', 'company', 'role']);
    $course = $data['course'] ?? '';
    $package = (float) ($data['packageLpa'] ?? 0);
    $photo = $data['photo'] ?? '';
    $placedOn = $data['placedOn'] ?? date('Y-m-d');

    $stmt = $db->prepare('INSERT INTO placements (student_name, company, role, course, package_lpa, photo, placed_on) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssdsss', $data['studentName'], $data['company'], $data['role'], $course, $package, $photo, $placedOn);
    $stmt->execute();
    respond(['success' => true, 'id' => $stmt->insert_id], 201);
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        respond_error('id is required.', 422);
    }
    $stmt = $db->prepare('DELETE FROM placements WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true]);
}

respond_error('Method not allowed.', 405);
