<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/../config/uploads.php';

$db = get_db();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$course = [
    'slug' => '', 'title' => '', 'category' => '', 'level' => '', 'duration' => '',
    'projects' => 0, 'mentor' => '', 'mentor_role' => '', 'rating' => 4.5, 'reviews_count' => 0,
    'price' => 0, 'original_price' => 0, 'image' => '', 'tools' => '', 'description' => '',
    'certificate' => 1, 'internship' => 1, 'placement_support' => 1, 'status' => 'active',
];
$error = '';

if ($id) {
    $stmt = $db->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    if ($found) {
        $course = $found;
        $course['tools'] = $found['tools'] ? implode(', ', json_decode($found['tools'])) : '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = trim($_POST['slug']);
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $level = trim($_POST['level']);
    $duration = trim($_POST['duration']);
    $projects = (int) $_POST['projects'];
    $mentor = trim($_POST['mentor']);
    $mentorRole = trim($_POST['mentor_role']);
    $rating = (float) $_POST['rating'];
    $reviewsCount = (int) $_POST['reviews_count'];
    $price = (int) $_POST['price'];
    $originalPrice = (int) $_POST['original_price'];
    $image = trim($_POST['image']);
    $description = trim($_POST['description']);
    $toolsArr = array_filter(array_map('trim', explode(',', $_POST['tools'] ?? '')));
    $tools = json_encode(array_values($toolsArr));
    $certificate = isset($_POST['certificate']) ? 1 : 0;
    $internship = isset($_POST['internship']) ? 1 : 0;
    $placementSupport = isset($_POST['placement_support']) ? 1 : 0;
    $status = $_POST['status'] === 'draft' ? 'draft' : 'active';

    try {
        $uploadedUrl = handle_image_upload('image_file', 'courses');
        if ($uploadedUrl) {
            $image = $uploadedUrl;
        }
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }

    if ($error === '' && ($slug === '' || $title === '' || $category === '' || $level === '' || $duration === '')) {
        $error = 'Please fill in all required fields (title, slug, category, level, duration).';
    }

    if ($error === '') {
        if ($id) {
            $stmt = $db->prepare(
                'UPDATE courses SET slug=?, title=?, category=?, level=?, duration=?, projects=?, mentor=?, mentor_role=?, rating=?, reviews_count=?, price=?, original_price=?, image=?, tools=?, description=?, certificate=?, internship=?, placement_support=?, status=? WHERE id=?'
            );
            $stmt->bind_param(
                'sssssissdiiisssiiisi',
                $slug, $title, $category, $level, $duration, $projects, $mentor, $mentorRole,
                $rating, $reviewsCount, $price, $originalPrice, $image, $tools, $description,
                $certificate, $internship, $placementSupport, $status, $id
            );
        } else {
            $stmt = $db->prepare(
                'INSERT INTO courses (slug, title, category, level, duration, projects, mentor, mentor_role, rating, reviews_count, price, original_price, image, tools, description, certificate, internship, placement_support, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'sssssissdiiisssiiis',
                $slug, $title, $category, $level, $duration, $projects, $mentor, $mentorRole,
                $rating, $reviewsCount, $price, $originalPrice, $image, $tools, $description,
                $certificate, $internship, $placementSupport, $status
            );
        }

        if ($stmt->execute()) {
            header('Location: courses.php?saved=1');
            exit;
        }
        $error = 'Could not save course. The slug might already be in use by another course.';
        $course = array_merge($course, $_POST, [
            'certificate' => $certificate,
            'internship' => $internship,
            'placement_support' => $placementSupport,
        ]);
    }
}

$activePage = 'courses';
require __DIR__ . '/includes/header.php';
?>
<h1><?= $id ? 'Edit Course' : 'Add New Course' ?></h1>

<?php if ($error): ?><div class="error-box"><?= e($error) ?></div><?php endif; ?>

<div class="card">
  <form method="post" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="field"><label>Course Title *</label><input type="text" name="title" value="<?= e($course['title']) ?>" required></div>
      <div class="field"><label>Slug (URL, e.g. full-stack-web-development) *</label><input type="text" name="slug" value="<?= e($course['slug']) ?>" required></div>

      <div class="field"><label>Category *</label><input type="text" name="category" value="<?= e($course['category']) ?>" required></div>
      <div class="field"><label>Level *</label><input type="text" name="level" value="<?= e($course['level']) ?>" required></div>

      <div class="field"><label>Duration *</label><input type="text" name="duration" value="<?= e($course['duration']) ?>" placeholder="e.g. 6 Months" required></div>
      <div class="field"><label>No. of Projects</label><input type="number" name="projects" value="<?= (int)$course['projects'] ?>"></div>

      <div class="field"><label>Mentor Name</label><input type="text" name="mentor" value="<?= e($course['mentor']) ?>"></div>
      <div class="field"><label>Mentor Role</label><input type="text" name="mentor_role" value="<?= e($course['mentor_role']) ?>"></div>

      <div class="field"><label>Rating (0-5)</label><input type="number" step="0.1" min="0" max="5" name="rating" value="<?= e((string)$course['rating']) ?>"></div>
      <div class="field"><label>Reviews Count</label><input type="number" name="reviews_count" value="<?= (int)$course['reviews_count'] ?>"></div>

      <div class="field"><label>Price (&#8377;) *</label><input type="number" name="price" value="<?= (int)$course['price'] ?>" required></div>
      <div class="field"><label>Original Price (&#8377;)</label><input type="number" name="original_price" value="<?= (int)$course['original_price'] ?>"></div>

      <div class="field full">
        <label>Course Image</label>
        <?php if (!empty($course['image'])): ?>
          <img src="<?= e($course['image']) ?>" alt="Current course image" style="max-height:120px;border-radius:8px;margin-bottom:.5rem;display:block;">
        <?php endif; ?>
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
        <p style="font-size:.78rem;color:#6b7280;margin-top:.4rem;">Upload a JPG, PNG, WEBP, or GIF (max 5MB). Uploading a new image replaces the current one. Or paste an image URL below instead.</p>
        <input type="text" name="image" value="<?= e($course['image']) ?>" placeholder="https://... or leave blank if uploading a file" style="margin-top:.5rem;">
      </div>
      <div class="field full"><label>Tools / Technologies (comma separated)</label><input type="text" name="tools" value="<?= e($course['tools']) ?>" placeholder="HTML, CSS, JavaScript, React"></div>
      <div class="field full"><label>Description</label><textarea name="description" rows="4"><?= e($course['description']) ?></textarea></div>

      <div class="field full">
        <label>Status</label>
        <select name="status">
          <option value="active" <?= $course['status'] === 'active' ? 'selected' : '' ?>>Active (visible on site)</option>
          <option value="draft" <?= $course['status'] === 'draft' ? 'selected' : '' ?>>Draft (hidden)</option>
        </select>
      </div>

      <div class="field full checkbox-row">
        <label><input type="checkbox" name="certificate" <?= $course['certificate'] ? 'checked' : '' ?>> Certificate</label>
        <label><input type="checkbox" name="internship" <?= $course['internship'] ? 'checked' : '' ?>> Internship</label>
        <label><input type="checkbox" name="placement_support" <?= $course['placement_support'] ? 'checked' : '' ?>> Placement Support</label>
      </div>
    </div>

    <div style="margin-top:1.5rem; display:flex; gap:.6rem;">
      <button class="btn" type="submit"><?= $id ? 'Save Changes' : 'Create Course' ?></button>
      <a class="btn btn-outline" href="courses.php">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
