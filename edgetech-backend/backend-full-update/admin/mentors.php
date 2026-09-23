<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();
$error = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM mentors WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: mentors.php?deleted=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    $role = trim($_POST['role']);
    $company = trim($_POST['company']);
    $image = trim($_POST['image']);
    $linkedinUrl = trim($_POST['linkedin_url']);
    $sortOrder = (int) $_POST['sort_order'];
    $status = $_POST['status'] === 'hidden' ? 'hidden' : 'published';

    if ($name === '' || $role === '') {
        $error = 'Name and role are required.';
    } elseif ($id) {
        $stmt = $db->prepare('UPDATE mentors SET name=?, role=?, company=?, image=?, linkedin_url=?, sort_order=?, status=? WHERE id=?');
        $stmt->bind_param('sssssisi', $name, $role, $company, $image, $linkedinUrl, $sortOrder, $status, $id);
        $stmt->execute();
        header('Location: mentors.php?saved=1');
        exit;
    } else {
        $stmt = $db->prepare('INSERT INTO mentors (name, role, company, image, linkedin_url, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssis', $name, $role, $company, $image, $linkedinUrl, $sortOrder, $status);
        $stmt->execute();
        header('Location: mentors.php?saved=1');
        exit;
    }
}

$editMentor = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $db->prepare('SELECT * FROM mentors WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editMentor = $stmt->get_result()->fetch_assoc();
}

$mentors = $db->query('SELECT * FROM mentors ORDER BY sort_order ASC, created_at ASC');

$activePage = 'mentors';
require __DIR__ . '/includes/header.php';
?>
<h1>Mentors</h1>
<?php if (isset($_GET['saved'])): ?><div class="success-box">Mentor saved.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Mentor deleted.</div><?php endif; ?>
<?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><h2><?= $editMentor ? 'Edit Mentor' : 'Add New Mentor' ?></h2></div>
  <form method="post">
    <?php if ($editMentor): ?><input type="hidden" name="id" value="<?= (int) $editMentor['id'] ?>"><?php endif; ?>
    <div class="form-grid">
      <div class="field"><label>Name *</label><input type="text" name="name" value="<?= e($editMentor['name'] ?? '') ?>" required></div>
      <div class="field"><label>Role *</label><input type="text" name="role" placeholder="Full Stack Mentor" value="<?= e($editMentor['role'] ?? '') ?>" required></div>
      <div class="field"><label>Company</label><input type="text" name="company" placeholder="Ex-Zoho" value="<?= e($editMentor['company'] ?? '') ?>"></div>
      <div class="field"><label>Sort Order</label><input type="number" name="sort_order" value="<?= (int) ($editMentor['sort_order'] ?? 0) ?>"></div>
      <div class="field full"><label>Photo Path</label><input type="text" name="image" placeholder="/images/mentors/example.jpg" value="<?= e($editMentor['image'] ?? '') ?>"></div>
      <div class="field full"><label>LinkedIn URL</label><input type="text" name="linkedin_url" placeholder="https://linkedin.com/in/..." value="<?= e($editMentor['linkedin_url'] ?? '') ?>"></div>
      <div class="field">
        <label>Status</label>
        <select name="status">
          <option value="published" <?= ($editMentor['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published</option>
          <option value="hidden" <?= ($editMentor['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Hidden</option>
        </select>
      </div>
    </div>
    <button class="btn" type="submit" style="margin-top:1rem"><?= $editMentor ? 'Update Mentor' : 'Add Mentor' ?></button>
    <?php if ($editMentor): ?><a class="btn btn-outline" href="mentors.php" style="margin-top:1rem">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="card">
  <div class="card-head"><h2>All Mentors</h2></div>
  <table>
    <thead><tr><th>Order</th><th>Name</th><th>Role</th><th>Company</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php if ($mentors->num_rows === 0): ?>
        <tr><td colspan="6" class="empty-state">No mentors yet. Add your first mentor.</td></tr>
      <?php else: while ($m = $mentors->fetch_assoc()): ?>
        <tr>
          <td><?= (int) $m['sort_order'] ?></td>
          <td><?= e($m['name']) ?></td>
          <td><?= e($m['role']) ?></td>
          <td><?= e($m['company']) ?></td>
          <td><span class="badge <?= e($m['status']) ?>"><?= e($m['status']) ?></span></td>
          <td>
            <div class="row-actions">
              <a class="btn btn-sm btn-outline" href="mentors.php?edit=<?= (int) $m['id'] ?>">Edit</a>
              <a class="btn btn-sm btn-danger" href="mentors.php?delete=<?= (int) $m['id'] ?>" onclick="return confirm('Delete this mentor?')">Delete</a>
            </div>
          </td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
