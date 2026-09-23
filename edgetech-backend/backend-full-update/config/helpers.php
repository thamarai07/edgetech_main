<?php

function json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function respond_error(string $message, int $status = 400): void
{
    respond(['success' => false, 'error' => $message], $status);
}

function require_fields(array $data, array $fields): void
{
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
            respond_error("Field '$field' is required.", 422);
        }
    }
}

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function course_row_to_array(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'slug' => $row['slug'],
        'title' => $row['title'],
        'category' => $row['category'],
        'level' => $row['level'],
        'duration' => $row['duration'],
        'projects' => (int) $row['projects'],
        'mentor' => $row['mentor'],
        'mentorRole' => $row['mentor_role'],
        'rating' => (float) $row['rating'],
        'reviews' => (int) $row['reviews_count'],
        'price' => (int) $row['price'],
        'originalPrice' => (int) $row['original_price'],
        'image' => $row['image'],
        'tools' => $row['tools'] ? json_decode($row['tools']) : [],
        'description' => $row['description'],
        'curriculum' => $row['curriculum'] ? json_decode($row['curriculum']) : [],
        'certificate' => (bool) $row['certificate'],
        'internship' => (bool) $row['internship'],
        'placementSupport' => (bool) $row['placement_support'],
        'status' => $row['status'],
    ];
}
