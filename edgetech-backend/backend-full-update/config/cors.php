<?php
// Allow the Next.js dev server (and same-origin) to call this API.
$allowedOrigins = [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
    'https://claryzentech.com',
    'https://www.claryzentech.com',
    'https://claryzentech.thamaraik69.workers.dev',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
