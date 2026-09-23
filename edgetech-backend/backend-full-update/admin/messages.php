<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM contact_messages WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: messages.php?deleted=1');
    exit;
}

$statuses = ['new', 'contacted', 'replied'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int) $_POST['id'];
    $status = in_array($_POST['status'], $statuses, true) ? $_POST['status'] : 'new';
    $stmt = $db->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    header('Location: messages.php?updated=1' . (isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''));
    exit;
}

$filter = $_GET['status'] ?? '';
if ($filter && in_array($filter, $statuses, true)) {
    $stmt = $db->prepare('SELECT * FROM contact_messages WHERE status = ? ORDER BY created_at DESC');
    $stmt->bind_param('s', $filter);
    $stmt->execute();
    $messages = $stmt->get_result();
} else {
    $filter = '';
    $messages = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
}

$activePage = 'messages';
require __DIR__ . '/includes/header.php';
?>
<h1>Contact Messages</h1>
<?php if (isset($_GET['updated'])): ?><div class="success-box">Status updated.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Message deleted.</div><?php endif; ?>

<div class="card">
  <div class="card-head">
    <h2>All Messages</h2>
    <div class="row-actions">
      <a class="btn btn-sm <?= $filter === '' ? '' : 'btn-outline' ?>" href="messages.php">All</a>
      <?php foreach ($statuses as $s): ?>
        <a class="btn btn-sm <?= $filter === $s ? '' : 'btn-outline' ?>" href="messages.php?status=<?= $s ?>"><?= ucfirst($s) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <table>
    <thead><tr><th>Name</th><th>Contact</th><th>Course Interest</th><th>Message</th><th>Status</th><th>Received</th><th></th></tr></thead>
    <tbody>
      <?php if ($messages->num_rows === 0): ?>
        <tr><td colspan="7" class="empty-state">No messages yet.</td></tr>
      <?php else: while ($m = $messages->fetch_assoc()): ?>
        <tr>
          <td><?= e($m['name']) ?></td>
          <td><?= e($m['phone']) ?><br><small style="color:#9ca3af"><?= e($m['email']) ?></small></td>
          <td><?= e($m['course_interest']) ?></td>
          <td style="max-width:280px"><?= e($m['message']) ?></td>
          <td>
            <form method="post" style="display:flex; gap:.3rem; align-items:center;">
              <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <select name="status" onchange="this.form.submit()">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= $s ?>" <?= ($m['status'] ?? 'new') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="update_status" value="1">
            </form>
          </td>
          <td><?= e($m['created_at']) ?></td>
          <td><a class="btn btn-sm btn-danger" href="messages.php?delete=<?= (int)$m['id'] ?>" onclick="return confirm('Delete this message?')">Delete</a></td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
