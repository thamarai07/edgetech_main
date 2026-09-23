<?php
// Database connection settings for local XAMPP (MySQL default: root / no password)
define('DB_HOST', 'localhost');
define('DB_NAME', 'edgetech_crm');
define('DB_USER', 'root');
define('DB_PASS', '');

function get_db(): mysqli
{
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }

    // PHP 8.1+ defaults mysqli to throwing exceptions on every DB error, which turns
    // ordinary, expected failures (like a duplicate slug) into an uncaught fatal error
    // (HTTP 500) instead of the friendly `if (!$stmt->execute())` messages the admin
    // pages are written to show. Restore the classic boolean-return behavior.
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    return $conn;
}
