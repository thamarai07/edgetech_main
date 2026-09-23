<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/../config/uploads.php';

$db = get_db();

/** True if a value already exists in the given unique column of portal_students. */
function portal_value_taken(mysqli $db, string $column, string $value, int $exceptId = 0): bool
{
    $sql = "SELECT id FROM portal_students WHERE $column = ? AND id <> ? LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('si', $value, $exceptId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() !== null;
}

/** CLZ + yymmdd, with -2, -3... suffixes if that day already has students. */
function generate_student_id(mysqli $db): string
{
    $base = 'CLZ' . date('ymd');
    $candidate = $base;
    $n = 1;
    while (portal_value_taken($db, 'student_id', $candidate)) {
        $n++;
        $candidate = $base . '-' . $n;
    }
    return $candidate;
}

function suggest_register_number(mysqli $db): string
{
    do {
        $candidate = 'REG' . date('ymd') . random_int(1000, 9999);
    } while (portal_value_taken($db, 'register_number', $candidate));
    return $candidate;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$student = [
    'student_id' => '', 'register_number' => '', 'full_name' => '', 'email' => '', 'phone' => '',
    'course_id' => '', 'course_title' => '', 'batch' => '', 'duration' => '', 'dob' => '',
    'address' => '', 'photo' => '', 'enrolled_on' => date('Y-m-d'), 'completed_on' => '',
    'status' => 'active', 'notes' => '',
];
$error = '';

if ($id) {
    $stmt = $db->prepare('SELECT * FROM portal_students WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    if ($found) {
        $student = $found;
    }
} else {
    $student['student_id'] = generate_student_id($db);
    $student['register_number'] = suggest_register_number($db);
}

$courses = $db->query('SELECT id, title FROM courses ORDER BY title');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['student_id']);
    $registerNumber = trim($_POST['register_number']);
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $courseId = (int) ($_POST['course_id'] ?? 0) ?: null;
    $courseTitle = trim($_POST['course_title'] ?? '');
    $batch = trim($_POST['batch']);
    $duration = trim($_POST['duration']);
    $dob = $_POST['dob'] ?: null;
    $address = trim($_POST['address']);
    $photo = trim($_POST['photo']);
    $enrolledOn = $_POST['enrolled_on'] ?: null;
    $completedOn = $_POST['completed_on'] ?: null;
    $status = in_array($_POST['status'], ['active', 'completed', 'dropped'], true) ? $_POST['status'] : 'active';
    $notes = trim($_POST['notes']);

    // Resolve course title from the chosen course id.
    if ($courseId) {
        $cs = $db->prepare('SELECT title FROM courses WHERE id = ?');
        $cs->bind_param('i', $courseId);
        $cs->execute();
        $crow = $cs->get_result()->fetch_assoc();
        if ($crow) {
            $courseTitle = $crow['title'];
        }
    }

    try {
        $uploadedUrl = handle_image_upload('photo_file', 'students');
        if ($uploadedUrl) {
            $photo = $uploadedUrl;
        }
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    if ($error === '' && ($studentId === '' || $registerNumber === '' || $fullName === '')) {
        $error = 'Student ID, Register Number and Full Name are required.';
    }
    if ($error === '' && portal_value_taken($db, 'student_id', $studentId, $id)) {
        $error = 'That Student ID is already in use. Pick another.';
    }
    if ($error === '' && portal_value_taken($db, 'register_number', $registerNumber, $id)) {
        $error = 'That Register Number is already in use. Pick another.';
    }

    if ($error === '') {
        if ($id) {
            $stmt = $db->prepare(
                'UPDATE portal_students SET student_id=?, register_number=?, full_name=?, email=?, phone=?, course_id=?, course_title=?, batch=?, duration=?, dob=?, address=?, photo=?, enrolled_on=?, completed_on=?, status=?, notes=? WHERE id=?'
            );
            $stmt->bind_param(
                'sssssissssssssssi',
                $studentId, $registerNumber, $fullName, $email, $phone, $courseId, $courseTitle,
                $batch, $duration, $dob, $address, $photo, $enrolledOn, $completedOn, $status, $notes, $id
            );
        } else {
            $stmt = $db->prepare(
                'INSERT INTO portal_students (student_id, register_number, full_name, email, phone, course_id, course_title, batch, duration, dob, address, photo, enrolled_on, completed_on, status, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'sssssissssssssss',
                $studentId, $registerNumber, $fullName, $email, $phone, $courseId, $courseTitle,
                $batch, $duration, $dob, $address, $photo, $enrolledOn, $completedOn, $status, $notes
            );
        }

        if ($stmt->execute()) {
            header('Location: portal-students.php?saved=1');
            exit;
        }
        $error = 'Could not save the student. Please try again.';
    }

    // Keep entered values on error.
    $student = array_merge($student, $_POST);
}

$activePage = 'portal-students';
require __DIR__ . '/includes/header.php';
?>
<h1><?= $id ? 'Edit Student' : 'Add New Student' ?></h1>

<?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <form method="post" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="field"><label>Student ID *</label><input type="text" name="student_id" value="<?= e($student['student_id']) ?>" required>
        <p style="font-size:.78rem;color:#6b7280;margin-top:.4rem;">Auto-generated. Share this with the student for login.</p></div>
      <div class="field"><label>Register Number *</label><input type="text" name="register_number" value="<?= e($student['register_number']) ?>" required>
        <p style="font-size:.78rem;color:#6b7280;margin-top:.4rem;">The student's login secret. Share it privately.</p></div>

      <div class="field"><label>Full Name *</label><input type="text" name="full_name" value="<?= e($student['full_name']) ?>" required></div>
      <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($student['email']) ?>"></div>

      <div class="field"><label>Phone</label><input type="text" name="phone" value="<?= e($student['phone']) ?>"></div>
      <div class="field"><label>Date of Birth</label><input type="date" name="dob" value="<?= e($student['dob']) ?>"></div>

      <div class="field"><label>Course</label>
        <select name="course_id">
          <option value="">— Select a course —</option>
          <?php while ($c = $courses->fetch_assoc()): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$student['course_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="field"><label>Batch</label><input type="text" name="batch" value="<?= e($student['batch']) ?>" placeholder="e.g. Sep 2026 - Weekend"></div>

      <div class="field"><label>Duration</label><input type="text" name="duration" value="<?= e($student['duration']) ?>" placeholder="e.g. 6 Months"></div>
      <div class="field"><label>Status</label>
        <select name="status">
          <?php foreach (['active' => 'Active', 'completed' => 'Completed', 'dropped' => 'Dropped'] as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= $student['status'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field"><label>Enrolled On</label><input type="date" name="enrolled_on" value="<?= e($student['enrolled_on']) ?>"></div>
      <div class="field"><label>Completed On</label><input type="date" name="completed_on" value="<?= e($student['completed_on']) ?>"></div>

      <div class="field full"><label>Address</label><input type="text" name="address" value="<?= e($student['address']) ?>"></div>

      <div class="field full">
        <label>Photo</label>
        <?php if (!empty($student['photo'])): ?>
          <img src="<?= e($student['photo']) ?>" alt="Current photo" style="max-height:110px;border-radius:8px;margin-bottom:.5rem;display:block;">
        <?php endif; ?>
        <input type="file" name="photo_file" accept="image/jpeg,image/png,image/webp">
        <input type="text" name="photo" value="<?= e($student['photo']) ?>" placeholder="https://... or leave blank" style="margin-top:.5rem;">
      </div>

      <div class="field full"><label>Internal Notes</label><textarea name="notes" rows="3"><?= e($student['notes']) ?></textarea></div>
    </div>

    <input type="hidden" name="course_title" value="<?= e($student['course_title']) ?>">

    <div style="margin-top:1.5rem; display:flex; gap:.6rem;">
      <button class="btn" type="submit"><?= $id ? 'Save Changes' : 'Create Student' ?></button>
      <a class="btn btn-outline" href="portal-students.php">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
