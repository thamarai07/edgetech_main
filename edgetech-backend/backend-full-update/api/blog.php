<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['slug'])) {
        $stmt = $db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1");
        $stmt->bind_param('s', $_GET['slug']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            respond_error('Post not found.', 404);
        }
        respond(['success' => true, 'post' => $row]);
    }

    $result = $db->query("SELECT id, slug, title, excerpt, cover_image, author, category, published_at FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC");
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    respond(['success' => true, 'posts' => $posts]);
}

require_admin_session();

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['title', 'slug', 'content']);
    $excerpt = $data['excerpt'] ?? '';
    $coverImage = $data['coverImage'] ?? '';
    $author = $data['author'] ?? 'Edge Tech Solution';
    $category = $data['category'] ?? '';
    $status = $data['status'] ?? 'published';

    $stmt = $db->prepare('INSERT INTO blog_posts (slug, title, excerpt, content, cover_image, author, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssssss', $data['slug'], $data['title'], $excerpt, $data['content'], $coverImage, $author, $category, $status);
    if (!$stmt->execute()) {
        respond_error('Could not create post. Slug may already be in use.', 409);
    }
    respond(['success' => true, 'id' => $stmt->insert_id], 201);
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) {
        respond_error('id is required.', 422);
    }
    $stmt = $db->prepare('DELETE FROM blog_posts WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true]);
}

respond_error('Method not allowed.', 405);
