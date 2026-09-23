<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();
$error = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM placements WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: placements.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim($_POST['student_name']);
    $company = trim($_POST['company']);
    $role = trim($_POST['role']);
    $course = trim($_POST['course']);
    $package = (float) $_POST['package_lpa'];
    $photo = trim($_POST['photo']);
    $placedOn = $_POST['placed_on'] ?: date('Y-m-d');

    if ($studentName === '' || $company === '' || $role === '') {
        $error = 'Student name, company, and role are required.';
    } else {
        $stmt = $db->prepare('INSERT INTO placements (student_name, company, role, course, package_lpa, photo, placed_on) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssdsss', $studentName, $company, $role, $course, $package, $photo, $placedOn);
        $stmt->execute();
        header('Location: placements.php?saved=1');
        exit;
    }
}

$placements = $db->query('SELECT * FROM placements ORDER BY placed_on DESC');

$activePage = 'placements';
require __DIR__ . '/includes/header.php';
?>
<h1>Placements</h1>
<?php if (isset($_GET['saved'])): ?><div class="success-box">Placement added.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Placement removed.</div><?php endif; ?>
<?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><h2>Add Placement Record</h2></div>
  <form method="post">
    <div class="form-grid">
      <div class="field"><label>Student Name *</label><input type="text" name="student_name" required></div>
      <div class="field"><label>Company *</label><input type="text" name="company" required></div>
      <div class="field"><label>Role *</label><input type="text" name="role" required></div>
      <div class="field"><label>Course</label><input type="text" name="course"></div>
      <div class="field"><label>Package (LPA)</label><input type="number" step="0.1" name="package_lpa"></div>
      <div class="field"><label>Placed On</label><input type="date" name="placed_on"></div>
      <div class="field full"><label>Photo Path</label><input type="text" name="photo" placeholder="/images/students/example.jpg"></div>
    </div>
    <button class="btn" type="submit" style="margin-top:1rem">Add Placement</button>
  </form>
</div>

<div class="card">
  <div class="card-head"><h2>All Placements</h2></div>
  <table>
    <thead><tr><th>Student</th><th>Company</th><th>Role</th><th>Course</th><th>Package</th><th>Date</th><th></th></tr></thead>
    <tbody>
      <?php if ($placements->num_rows === 0): ?>
        <tr><td colspan="7" class="empty-state">No placements added yet.</td></tr>
      <?php else: while ($p = $placements->fetch_assoc()): ?>
        <tr>
          <td><?= e($p['student_name']) ?></td>
          <td><?= e($p['company']) ?></td>
          <td><?= e($p['role']) ?></td>
          <td><?= e($p['course']) ?></td>
          <td><?= $p['package_lpa'] ? number_format((float)$p['package_lpa'],1).' LPA' : '-' ?></td>
          <td><?= e($p['placed_on']) ?></td>
          <td><a class="btn btn-sm btn-danger" href="placements.php?delete=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this record?')">Delete</a></td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
