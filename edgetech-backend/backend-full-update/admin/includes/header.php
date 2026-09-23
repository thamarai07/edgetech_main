<?php
// Expects $activePage to be set by the including page (e.g. 'dashboard', 'courses', 'students', 'messages', 'reviews', 'blog', 'placements')
$activePage = $activePage ?? '';
function navlink(string $page, string $href, string $label, string $active): void
{
    $cls = $page === $active ? 'active' : '';
    echo "<a class=\"$cls\" href=\"$href\">$label</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edge Tech CRM</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <h2>Edge Tech CRM</h2>
    <nav>
      <?php navlink('dashboard', 'index.php', 'Dashboard', $activePage); ?>
      <?php navlink('courses', 'courses.php', 'Courses', $activePage); ?>
      <?php navlink('students', 'students.php', 'Student Leads', $activePage); ?>
      <?php navlink('portal-students', 'portal-students.php', 'Portal Students', $activePage); ?>
      <?php navlink('certificates', 'student-certificates.php', 'Certificates', $activePage); ?>
      <?php navlink('messages', 'messages.php', 'Contact Messages', $activePage); ?>
      <?php navlink('reviews', 'reviews.php', 'Reviews', $activePage); ?>
      <?php navlink('mentors', 'mentors.php', 'Mentors', $activePage); ?>
      <?php navlink('blog', 'blog.php', 'Blog Posts', $activePage); ?>
      <?php navlink('placements', 'placements.php', 'Placements', $activePage); ?>
    </nav>
    <a class="logout" href="logout.php">Log Out</a>
  </aside>
  <main class="main">
