<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM courses WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: courses.php?deleted=1');
    exit;
}

$courses = $db->query('SELECT * FROM courses ORDER BY created_at DESC');

$activePage = 'courses';
require __DIR__ . '/includes/header.php';
?>
<h1>Courses</h1>

<?php if (isset($_GET['saved'])): ?><div class="success-box">Course saved successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Course deleted.</div><?php endif; ?>

<div class="card">
  <div class="card-head">
    <h2>All Courses</h2>
    <a class="btn btn-sm" href="course-form.php">+ Add New Course</a>
  </div>
  <table>
    <thead><tr><th>Title</th><th>Category</th><th>Duration</th><th>Price</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php if ($courses->num_rows === 0): ?>
        <tr><td colspan="6" class="empty-state">No courses yet. Add your first course.</td></tr>
      <?php else: while ($c = $courses->fetch_assoc()): ?>
        <tr>
          <td><?= e($c['title']) ?><br><small style="color:#9ca3af">/<?= e($c['slug']) ?></small></td>
          <td><?= e($c['category']) ?></td>
          <td><?= e($c['duration']) ?></td>
          <td>&#8377;<?= number_format((int)$c['price']) ?></td>
          <td><span class="badge <?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
          <td>
            <div class="row-actions">
              <a class="btn btn-sm btn-outline" href="course-form.php?id=<?= (int)$c['id'] ?>">Edit</a>
              <a class="btn btn-sm btn-danger" href="courses.php?delete=<?= (int)$c['id'] ?>" onclick="return confirm('Delete this course? This cannot be undone.')">Delete</a>
            </div>
          </td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
