<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['slug'])) {
        $slug = $_GET['slug'];
        $stmt = $db->prepare("SELECT * FROM courses WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) {
            respond_error('Course not found.', 404);
        }
        respond(['success' => true, 'course' => course_row_to_array($row)]);
    }

    $result = $db->query("SELECT * FROM courses WHERE status = 'active' ORDER BY created_at DESC");
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = course_row_to_array($row);
    }
    respond(['success' => true, 'courses' => $courses]);
}

// All write operations require an authenticated admin (used by the CRM panel)
require_admin_session();

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['title', 'slug', 'category', 'level', 'duration', 'price']);

    $stmt = $db->prepare(
        'INSERT INTO courses (slug, title, category, level, duration, projects, mentor, mentor_role, rating, reviews_count, price, original_price, image, tools, description, certificate, internship, placement_support, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $tools = isset($data['tools']) ? json_encode(array_values((array) $data['tools'])) : json_encode([]);
    $projects = (int) ($data['projects'] ?? 0);
    $mentor = $data['mentor'] ?? '';
    $mentorRole = $data['mentorRole'] ?? '';
    $rating = (float) ($data['rating'] ?? 4.5);
    $reviewsCount = (int) ($data['reviews'] ?? 0);
    $price = (int) $data['price'];
    $originalPrice = (int) ($data['originalPrice'] ?? $price);
    $image = $data['image'] ?? '';
    $description = $data['description'] ?? '';
    $certificate = !empty($data['certificate']) ? 1 : 0;
    $internship = !empty($data['internship']) ? 1 : 0;
    $placementSupport = !empty($data['placementSupport']) ? 1 : 0;
    $status = $data['status'] ?? 'active';

    $stmt->bind_param(
        'sssssissdiiisssiiis',
        $data['slug'], $data['title'], $data['category'], $data['level'], $data['duration'],
        $projects, $mentor, $mentorRole, $rating, $reviewsCount, $price, $originalPrice, $image,
        $tools, $description, $certificate, $internship, $placementSupport, $status
    );

    if (!$stmt->execute()) {
        respond_error('Could not create course. Slug may already be in use.', 409);
    }

    respond(['success' => true, 'id' => $stmt->insert_id], 201);
}

if ($method === 'PUT') {
    $data = json_input();
    $id = (int) ($data['id'] ?? 0);
    if (!$id) {
        respond_error('Course id is required.', 422);
    }
    require_fields($data, ['title', 'slug', 'category', 'level', 'duration', 'price']);

    $tools = isset($data['tools']) ? json_encode(array_values((array) $data['tools'])) : json_encode([]);
    $projects = (int) ($data['projects'] ?? 0);
    $mentor = $data['mentor'] ?? '';
    $mentorRole = $data['mentorRole'] ?? '';
    $rating = (float) ($data['rating'] ?? 4.5);
    $reviewsCount = (int) ($data['reviews'] ?? 0);
    $price = (int) $data['price'];
    $originalPrice = (int) ($data['originalPrice'] ?? $price);
    $image = $data['image'] ?? '';
    $description = $data['description'] ?? '';
    $certificate = !empty($data['certificate']) ? 1 : 0;
    $internship = !empty($data['internship']) ? 1 : 0;
    $placementSupport = !empty($data['placementSupport']) ? 1 : 0;
    $status = $data['status'] ?? 'active';

    $stmt = $db->prepare(
        'UPDATE courses SET slug=?, title=?, category=?, level=?, duration=?, projects=?, mentor=?, mentor_role=?, rating=?, reviews_count=?, price=?, original_price=?, image=?, tools=?, description=?, certificate=?, internship=?, placement_support=?, status=? WHERE id=?'
    );
    $stmt->bind_param(
        'sssssissdiiisssiiisi',
        $data['slug'], $data['title'], $data['category'], $data['level'], $data['duration'],
        $projects, $mentor, $mentorRole, $rating, $reviewsCount, $price, $originalPrice, $image,
        $tools, $description, $certificate, $internship, $placementSupport, $status, $id
    );

    if (!$stmt->execute()) {
        respond_error('Could not update course.', 409);
    }

    respond(['success' => true]);
}

if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $deleteData);
    $id = (int) ($_GET['id'] ?? $deleteData['id'] ?? 0);
    if (!$id) {
        respond_error('Course id is required.', 422);
    }
    $stmt = $db->prepare('DELETE FROM courses WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true]);
}

respond_error('Method not allowed.', 405);
