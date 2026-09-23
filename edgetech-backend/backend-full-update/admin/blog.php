<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();
$error = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM blog_posts WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: blog.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $excerpt = trim($_POST['excerpt']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);
    $coverImage = trim($_POST['cover_image']);
    $status = $_POST['status'] === 'draft' ? 'draft' : 'published';

    if ($title === '' || $slug === '' || $content === '') {
        $error = 'Title, slug, and content are required.';
    } else {
        $stmt = $db->prepare('INSERT INTO blog_posts (slug, title, excerpt, content, cover_image, author, category, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $author = 'Edge Tech Solution';
        $stmt->bind_param('ssssssss', $slug, $title, $excerpt, $content, $coverImage, $author, $category, $status);
        if ($stmt->execute()) {
            header('Location: blog.php?saved=1');
            exit;
        }
        $error = 'Could not save post. The slug might already be in use.';
    }
}

$posts = $db->query('SELECT * FROM blog_posts ORDER BY created_at DESC');

$activePage = 'blog';
require __DIR__ . '/includes/header.php';
?>
<h1>Blog Posts</h1>
<?php if (isset($_GET['saved'])): ?><div class="success-box">Post published.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Post deleted.</div><?php endif; ?>
<?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><h2>Add New Post</h2></div>
  <form method="post">
    <div class="form-grid">
      <div class="field"><label>Title *</label><input type="text" name="title" required></div>
      <div class="field"><label>Slug (URL) *</label><input type="text" name="slug" required></div>
      <div class="field"><label>Category</label><input type="text" name="category" placeholder="Career Guidance"></div>
      <div class="field"><label>Cover Image Path</label><input type="text" name="cover_image" placeholder="/images/blog/example.jpg"></div>
      <div class="field full"><label>Excerpt (short summary)</label><textarea name="excerpt" rows="2"></textarea></div>
      <div class="field full"><label>Content (HTML allowed) *</label><textarea name="content" rows="6" required></textarea></div>
      <div class="field">
        <label>Status</label>
        <select name="status">
          <option value="published">Published</option>
          <option value="draft">Draft</option>
        </select>
      </div>
    </div>
    <button class="btn" type="submit" style="margin-top:1rem">Publish Post</button>
  </form>
</div>

<div class="card">
  <div class="card-head"><h2>All Posts</h2></div>
  <table>
    <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Published</th><th></th></tr></thead>
    <tbody>
      <?php if ($posts->num_rows === 0): ?>
        <tr><td colspan="5" class="empty-state">No posts yet.</td></tr>
      <?php else: while ($p = $posts->fetch_assoc()): ?>
        <tr>
          <td><?= e($p['title']) ?><br><small style="color:#9ca3af">/<?= e($p['slug']) ?></small></td>
          <td><?= e($p['category']) ?></td>
          <td><span class="badge <?= $p['status'] === 'published' ? 'active' : 'draft' ?>"><?= e($p['status']) ?></span></td>
          <td><?= e($p['published_at']) ?></td>
          <td><a class="btn btn-sm btn-danger" href="blog.php?delete=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this post?')">Delete</a></td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
