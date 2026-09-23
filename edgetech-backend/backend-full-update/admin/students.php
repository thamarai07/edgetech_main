<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM course_enquiries WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: students.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int) $_POST['id'];
    $status = $_POST['status'];
    $stmt = $db->prepare('UPDATE course_enquiries SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    header('Location: students.php?updated=1');
    exit;
}

$filter = $_GET['status'] ?? '';
if ($filter) {
    $stmt = $db->prepare('SELECT * FROM course_enquiries WHERE status = ? ORDER BY created_at DESC');
    $stmt->bind_param('s', $filter);
    $stmt->execute();
    $leads = $stmt->get_result();
} else {
    $leads = $db->query('SELECT * FROM course_enquiries ORDER BY created_at DESC');
}

$statuses = ['new', 'contacted', 'converted', 'not_interested'];

$activePage = 'students';
require __DIR__ . '/includes/header.php';
?>
<h1>Student Leads</h1>
<?php if (isset($_GET['updated'])): ?><div class="success-box">Status updated.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Lead deleted.</div><?php endif; ?>

<div class="card">
  <div class="card-head">
    <h2>All Enquiries (from course enrollment forms)</h2>
    <div class="row-actions">
      <a class="btn btn-sm <?= $filter === '' ? '' : 'btn-outline' ?>" href="students.php">All</a>
      <?php foreach ($statuses as $s): ?>
        <a class="btn btn-sm <?= $filter === $s ? '' : 'btn-outline' ?>" href="students.php?status=<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <table>
    <thead><tr><th>Name</th><th>Course</th><th>Contact</th><th>City</th><th>Message</th><th>Status</th><th>Received</th><th></th></tr></thead>
    <tbody>
      <?php if ($leads->num_rows === 0): ?>
        <tr><td colspan="8" class="empty-state">No leads found.</td></tr>
      <?php else: while ($lead = $leads->fetch_assoc()): ?>
        <tr>
          <td><?= e($lead['name']) ?></td>
          <td><?= e($lead['course_title']) ?></td>
          <td><?= e($lead['phone']) ?><br><small style="color:#9ca3af"><?= e($lead['email']) ?></small></td>
          <td><?= e($lead['city']) ?></td>
          <td style="max-width:220px"><?= e($lead['message']) ?></td>
          <td>
            <form method="post" style="display:flex; gap:.3rem; align-items:center;">
              <input type="hidden" name="id" value="<?= (int)$lead['id'] ?>">
              <select name="status" onchange="this.form.submit()">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= $s ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="update_status" value="1">
            </form>
          </td>
          <td><?= e($lead['created_at']) ?></td>
          <td><a class="btn btn-sm btn-danger" href="students.php?delete=<?= (int)$lead['id'] ?>" onclick="return confirm('Delete this lead?')">Delete</a></td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
