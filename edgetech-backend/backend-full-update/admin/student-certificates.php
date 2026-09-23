<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/../config/uploads.php';

$db = get_db();
$error = '';

if (isset($_GET['delete'])) {
    $cid = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM student_certificates WHERE id = ?');
    $stmt->bind_param('i', $cid);
    $stmt->execute();
    header('Location: student-certificates.php?deleted=1');
    exit;
}

function generate_certificate_number(mysqli $db): string
{
    $base = 'CLZ-CERT-' . date('ymd');
    $n = 0;
    do {
        $n++;
        $candidate = $base . '-' . str_pad((string) $n, 2, '0', STR_PAD_LEFT);
        $stmt = $db->prepare('SELECT id FROM student_certificates WHERE certificate_number = ? LIMIT 1');
        $stmt->bind_param('s', $candidate);
        $stmt->execute();
    } while ($stmt->get_result()->fetch_assoc() !== null);
    return $candidate;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentPk = (int) ($_POST['portal_student_id'] ?? 0);
    $certNumber = trim($_POST['certificate_number']) ?: generate_certificate_number($db);
    $title = trim($_POST['title']);
    $type = in_array($_POST['type'], ['completion', 'internship', 'achievement'], true) ? $_POST['type'] : 'completion';
    $issueDate = $_POST['issue_date'] ?: date('Y-m-d');
    $grade = trim($_POST['grade']);
    $status = $_POST['status'] === 'draft' ? 'draft' : 'issued';
    $fileUrl = '';

    if (!$studentPk || $title === '') {
        $error = 'Please choose a student and enter a certificate title.';
    }

    if ($error === '') {
        try {
            $uploaded = handle_document_upload('cert_file', 'certificates');
            if ($uploaded) {
                $fileUrl = $uploaded;
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }

    if ($error === '') {
        $stmt = $db->prepare(
            'INSERT INTO student_certificates (portal_student_id, certificate_number, title, type, issue_date, file_url, grade, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('isssssss', $studentPk, $certNumber, $title, $type, $issueDate, $fileUrl, $grade, $status);
        if ($stmt->execute()) {
            header('Location: student-certificates.php?saved=1');
            exit;
        }
        $error = 'Could not save the certificate. Please try again.';
    }
}

$students = $db->query('SELECT id, student_id, full_name, course_title FROM portal_students ORDER BY full_name');
$certs = $db->query(
    'SELECT sc.*, ps.full_name, ps.student_id AS student_code
     FROM student_certificates sc
     JOIN portal_students ps ON ps.id = sc.portal_student_id
     ORDER BY sc.created_at DESC'
);

$activePage = 'certificates';
require __DIR__ . '/includes/header.php';
?>
<h1>Student Certificates</h1>
<?php if (isset($_GET['saved'])): ?><div class="success-box">Certificate saved.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="success-box">Certificate deleted.</div><?php endif; ?>
<?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <div class="card-head"><h2>Issue a Certificate</h2></div>
  <form method="post" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="field"><label>Student *</label>
        <select name="portal_student_id" required>
          <option value="">— Select a student —</option>
          <?php while ($s = $students->fetch_assoc()): ?>
            <option value="<?= (int)$s['id'] ?>"><?= e($s['full_name']) ?> (<?= e($s['student_id']) ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="field"><label>Certificate Number</label><input type="text" name="certificate_number" placeholder="Auto-generated if left blank"></div>

      <div class="field"><label>Title *</label><input type="text" name="title" placeholder="Full Stack Web Development — Course Completion" required></div>
      <div class="field"><label>Type</label>
        <select name="type">
          <option value="completion">Course Completion</option>
          <option value="internship">Internship</option>
          <option value="achievement">Achievement</option>
        </select>
      </div>

      <div class="field"><label>Issue Date</label><input type="date" name="issue_date" value="<?= date('Y-m-d') ?>"></div>
      <div class="field"><label>Grade / Score</label><input type="text" name="grade" placeholder="e.g. A+ / 92%"></div>

      <div class="field full">
        <label>Certificate File (PDF or image, optional)</label>
        <input type="file" name="cert_file" accept="image/jpeg,image/png,image/webp,application/pdf">
        <p style="font-size:.78rem;color:#6b7280;margin-top:.4rem;">Leave blank for now — the website shows a placeholder certificate until a file is uploaded. Max 10MB.</p>
      </div>

      <div class="field full"><label>Status</label>
        <select name="status">
          <option value="issued">Issued (visible to student)</option>
          <option value="draft">Draft (hidden)</option>
        </select>
      </div>
    </div>
    <button class="btn" type="submit" style="margin-top:1rem">Save Certificate</button>
  </form>
</div>

<div class="card">
  <div class="card-head"><h2>All Certificates</h2></div>
  <table>
    <thead><tr><th>Number</th><th>Student</th><th>Title</th><th>Type</th><th>Issued</th><th>File</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php if ($certs->num_rows === 0): ?>
        <tr><td colspan="8" class="empty-state">No certificates issued yet.</td></tr>
      <?php else: while ($c = $certs->fetch_assoc()): ?>
        <tr>
          <td><?= e($c['certificate_number']) ?></td>
          <td><?= e($c['full_name']) ?><br><small style="color:#9ca3af"><?= e($c['student_code']) ?></small></td>
          <td><?= e($c['title']) ?></td>
          <td><?= e($c['type']) ?></td>
          <td><?= e($c['issue_date']) ?></td>
          <td><?= $c['file_url'] ? '<a href="' . e($c['file_url']) . '" target="_blank" rel="noopener">View</a>' : '<small style="color:#9ca3af">placeholder</small>' ?></td>
          <td><span class="badge <?= e($c['status']) ?>"><?= e($c['status']) ?></span></td>
          <td><a class="btn btn-sm btn-danger" href="student-certificates.php?delete=<?= (int)$c['id'] ?>" onclick="return confirm('Delete this certificate?')">Delete</a></td>
        </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
