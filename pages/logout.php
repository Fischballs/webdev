<?php
/**
 * logout.php
 * Clears the logged-in account and returns the visitor to the "null
 * account" state, then sends them back to the homepage.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['user_id']);
unset($_SESSION['account_name']);
// visitor_id is left in place — it's just the guest session marker, not the account.

header('Location: home.php');
exit;
