<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/uploads.php';

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

/** Turn a stored "/uploads/..." path into an absolute URL the frontend can load. */
function absolute_asset_url(?string $path): ?string
{
    if (!$path) {
        return null;
    }
    if (str_starts_with($path, '/uploads')) {
        return BACKEND_BASE_URL . $path;
    }
    return $path;
}

if ($method === 'POST') {
    $data = json_input();
    require_fields($data, ['studentId', 'registerNumber']);

    $studentId = trim($data['studentId']);
    $registerNumber = trim($data['registerNumber']);

    $stmt = $db->prepare('SELECT * FROM portal_students WHERE student_id = ? AND register_number = ? LIMIT 1');
    $stmt->bind_param('ss', $studentId, $registerNumber);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        respond_error('Invalid Student ID or Register Number.', 401);
    }

    $certStmt = $db->prepare(
        "SELECT * FROM student_certificates WHERE portal_student_id = ? AND status = 'issued' ORDER BY issue_date DESC, id DESC"
    );
    $certStmt->bind_param('i', $row['id']);
    $certStmt->execute();
    $certResult = $certStmt->get_result();

    $certificates = [];
    while ($c = $certResult->fetch_assoc()) {
        $certificates[] = [
            'id' => (int) $c['id'],
            'certificateNumber' => $c['certificate_number'],
            'title' => $c['title'],
            'type' => $c['type'],
            'issueDate' => $c['issue_date'],
            'fileUrl' => absolute_asset_url($c['file_url'] ?: null),
            'grade' => $c['grade'] ?: null,
        ];
    }

    respond([
        'success' => true,
        'student' => [
            'studentId' => $row['student_id'],
            'registerNumber' => $row['register_number'],
            'fullName' => $row['full_name'],
            'email' => $row['email'] ?: null,
            'phone' => $row['phone'] ?: null,
            'courseTitle' => $row['course_title'] ?: null,
            'batch' => $row['batch'] ?: null,
            'duration' => $row['duration'] ?: null,
            'dob' => $row['dob'] ?: null,
            'address' => $row['address'] ?: null,
            'photo' => absolute_asset_url($row['photo'] ?: null),
            'enrolledOn' => $row['enrolled_on'] ?: null,
            'completedOn' => $row['completed_on'] ?: null,
            'status' => $row['status'],
            'certificates' => $certificates,
        ],
    ]);
}

respond_error('Method not allowed.', 405);
