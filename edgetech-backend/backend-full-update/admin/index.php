<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';

$db = get_db();
$totalCourses = $db->query("SELECT COUNT(*) c FROM courses WHERE status='active'")->fetch_assoc()['c'];
$totalLeads = $db->query('SELECT COUNT(*) c FROM course_enquiries')->fetch_assoc()['c'];
$newLeads = $db->query("SELECT COUNT(*) c FROM course_enquiries WHERE status='new'")->fetch_assoc()['c'];
$totalMessages = $db->query('SELECT COUNT(*) c FROM contact_messages')->fetch_assoc()['c'];
$portalStudents = $db->query('SELECT COUNT(*) c FROM portal_students')->fetch_assoc()['c'];

$recentLeads = $db->query('SELECT * FROM course_enquiries ORDER BY created_at DESC LIMIT 6');

$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<h1>Dashboard</h1>

<div class="stat-grid">
  <div class="stat-card"><div class="num"><?= (int)$totalCourses ?></div><div class="label">Active Courses</div></div>
  <div class="stat-card"><div class="num"><?= (int)$totalLeads ?></div><div class="label">Total Student Leads</div></div>
  <div class="stat-card"><div class="num"><?= (int)$newLeads ?></div><div class="label">New / Unattended Leads</div></div>
  <div class="stat-card"><div class="num"><?= (int)$totalMessages ?></div><div class="label">Contact Messages</div></div>
  <div class="stat-card"><div class="num"><?= (int)$portalStudents ?></div><div class="label">Portal Students</div></div>
</div>

<div class="card">
  <div class="card-head">
    <h2>Recent Student Leads</h2>
    <a class="btn btn-sm btn-outline" href="students.php">View All</a>
  </div>
  <table>
    <thead><tr><th>Name</th><th>Course</th><th>Phone</th><th>Email</th><th>Status</th><th>Received</th></tr></thead>
    <tbody>
      <?php if ($recentLeads->num_rows === 0): ?>
        <tr><td colspan="6" class="empty-state">No leads yet. They will show up here as students submit course enrollment forms.</td></tr>
      <?php else: while ($lead = $recentLeads->fetch_assoc()): ?>
        <tr>
          <td><?= e($lead['name']) ?></td>
          <td><?= e($lead['course_title']) ?></td>
          <td><?= e($lead['phone']) ?></td>
          <td><?= e($lead['email']) ?></td>
          <td><span class="badge <?= e($lead['status']) ?>"><?= e($lead['status']) ?></span></td>
          <td><?= e($lead['created_at']) ?></td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
