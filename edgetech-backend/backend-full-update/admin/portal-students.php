<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM portal_students WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: portal-students.php?deleted=1');
    exit;
}

$filter = $_GET['status'] ?? '';
if ($filter) {
    $stmt = $db->prepare('SELECT * FROM portal_students WHERE status = ? ORDER BY created_at DESC');
    $stmt->bind_param('s', $filter);
    $stmt->execute();
    $students = $stmt->get_result();
} else {
    $students = $db->query('SELECT * FROM portal_students ORDER BY created_at DESC');
}

$statuses = ['active', 'completed', 'dropped'];

$activePage = 'portal-students';
require __DIR__ . '/includes/header.php';
?>
<h1>Portal Students</h1>
<?php if (isset($_GET['saved'])): ?><div class="success-box">Student saved. Share the Student ID and Register Number with them to log in.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Student deleted.</div><?php endif; ?>

<div class="card">
  <div class="card-head">
    <h2>Enrolled Students</h2>
    <div class="row-actions">
      <a class="btn btn-sm <?= $filter === '' ? '' : 'btn-outline' ?>" href="portal-students.php">All</a>
      <?php foreach ($statuses as $s): ?>
        <a class="btn btn-sm <?= $filter === $s ? '' : 'btn-outline' ?>" href="portal-students.php?status=<?= $s ?>"><?= ucfirst($s) ?></a>
      <?php endforeach; ?>
      <a class="btn btn-sm" href="portal-student-form.php">+ Add Student</a>
    </div>
  </div>
  <table>
    <thead><tr><th>Student ID</th><th>Register No.</th><th>Name</th><th>Course</th><th>Batch</th><th>Status</th><th>Enrolled</th><th></th></tr></thead>
    <tbody>
      <?php if ($students->num_rows === 0): ?>
        <tr><td colspan="8" class="empty-state">No students yet. Click "Add Student" to create one.</td></tr>
      <?php else: while ($st = $students->fetch_assoc()): ?>
        <tr>
          <td><strong><?= e($st['student_id']) ?></strong></td>
          <td><?= e($st['register_number']) ?></td>
          <td><?= e($st['full_name']) ?><br><small style="color:#9ca3af"><?= e($st['email']) ?></small></td>
          <td><?= e($st['course_title']) ?></td>
          <td><?= e($st['batch']) ?></td>
          <td><span class="badge <?= e($st['status']) ?>"><?= e($st['status']) ?></span></td>
          <td><?= e($st['enrolled_on']) ?></td>
          <td>
            <div class="row-actions">
              <a class="btn btn-sm btn-outline" href="portal-student-form.php?id=<?= (int)$st['id'] ?>">Edit</a>
              <a class="btn btn-sm btn-danger" href="portal-students.php?delete=<?= (int)$st['id'] ?>" onclick="return confirm('Delete this student and all their certificates?')">Delete</a>
            </div>
          </td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
