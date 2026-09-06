<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Guarantees a session id exists right away, even for a guest/null account.
if (!isset($_SESSION['visitor_id'])) {
    $_SESSION['visitor_id'] = session_id();
}

$user_id      = $_SESSION['user_id'] ?? null;
$account_name = $_SESSION['account_name'] ?? null;
$is_logged_in = $user_id !== null && $account_name !== null;

if (!function_exists('guarded_href')) {
    function guarded_href(bool $is_logged_in, string $when_logged_in = '#'): string {
        return $is_logged_in ? $when_logged_in : 'signin_signup.php';
    }
}
