<?php
// Include this in any API endpoint. The session is only started when an endpoint
// actually needs to check the admin login (write operations) - public GET
// endpoints never touch the session, which avoids PHP session-file lock
// contention that was intermittently 500ing the read API under load.

function require_admin_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['admin_id'])) {
        respond_error('Unauthorized. Please log in.', 401);
    }
}
