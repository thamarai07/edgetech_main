<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();
$error = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM testimonials WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: reviews.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $course = trim($_POST['course']);
    $rating = (int) $_POST['rating'];
    $image = trim($_POST['image']);
    $quote = trim($_POST['quote']);

    if ($name === '' || $quote === '') {
        $error = 'Name and review text are required.';
    } else {
        $stmt = $db->prepare('INSERT INTO testimonials (name, role, course, rating, image, quote) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssiss', $name, $role, $course, $rating, $image, $quote);
        $stmt->execute();
        header('Location: reviews.php?saved=1');
        exit;
    }
}

$reviews = $db->query('SELECT * FROM testimonials ORDER BY created_at DESC');

$activePage = 'reviews';
require __DIR__ . '/includes/header.php';
?>
<h1>Student Reviews</h1>
<?php if (isset($_GET['saved'])): ?><div class="success-box">Review added.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Review deleted.</div><?php endif; ?>
<?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><h2>Add New Review</h2></div>
  <form method="post">
    <div class="form-grid">
      <div class="field"><label>Student Name *</label><input type="text" name="name" required></div>
      <div class="field"><label>Role / Company</label><input type="text" name="role" placeholder="Software Engineer at Infosys"></div>
      <div class="field"><label>Course</label><input type="text" name="course"></div>
      <div class="field"><label>Rating (1-5)</label><input type="number" name="rating" min="1" max="5" value="5"></div>
      <div class="field full"><label>Photo Path</label><input type="text" name="image" placeholder="/images/students/example.jpg"></div>
      <div class="field full"><label>Review Text *</label><textarea name="quote" rows="3" required></textarea></div>
    </div>
    <button class="btn" type="submit" style="margin-top:1rem">Add Review</button>
  </form>
</div>

<div class="card">
  <div class="card-head"><h2>All Reviews</h2></div>
  <table>
    <thead><tr><th>Name</th><th>Role</th><th>Course</th><th>Rating</th><th>Review</th><th></th></tr></thead>
    <tbody>
      <?php if ($reviews->num_rows === 0): ?>
        <tr><td colspan="6" class="empty-state">No reviews yet.</td></tr>
      <?php else: while ($r = $reviews->fetch_assoc()): ?>
        <tr>
          <td><?= e($r['name']) ?></td>
          <td><?= e($r['role']) ?></td>
          <td><?= e($r['course']) ?></td>
          <td><?= (int)$r['rating'] ?> / 5</td>
          <td style="max-width:280px"><?= e($r['quote']) ?></td>
          <td><a class="btn btn-sm btn-danger" href="reviews.php?delete=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this review?')">Delete</a></td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
