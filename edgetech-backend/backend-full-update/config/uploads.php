<?php
// Public base URL this backend is served from (used to build absolute image URLs,
// since the Next.js frontend runs on a different domain/host).
define('BACKEND_BASE_URL', 'https://snow-elk-902268.hostingersite.com');

/**
 * Handles an optional uploaded image file for a $_FILES field.
 * Returns the public URL of the saved file, or null if no file was uploaded.
 * Throws a RuntimeException with a user-friendly message on validation failure.
 */
function handle_image_upload(string $fieldName, string $subfolder): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    $maxBytes = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('Image is too large. Maximum size is 5MB.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, or GIF images are allowed.');
    }

    $ext = $allowed[$mime];
    $filename = bin2hex(random_bytes(8)) . '.' . $ext;

    $uploadDir = __DIR__ . "/../uploads/$subfolder";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = "$uploadDir/$filename";
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }

    return BACKEND_BASE_URL . "/uploads/$subfolder/$filename";
}

/**
 * Like handle_image_upload() but also accepts PDF files. Used for certificate
 * uploads, which may be an image or a PDF. Returns the public URL or null.
 */
function handle_document_upload(string $fieldName, string $subfolder): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed. Please try again.');
    }

    $maxBytes = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File is too large. Maximum size is 10MB.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, or PDF files are allowed.');
    }

    $ext = $allowed[$mime];
    $filename = bin2hex(random_bytes(8)) . '.' . $ext;

    $uploadDir = __DIR__ . "/../uploads/$subfolder";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = "$uploadDir/$filename";
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return BACKEND_BASE_URL . "/uploads/$subfolder/$filename";
}
