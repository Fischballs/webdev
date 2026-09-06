<?php
/**
 * config/database.php
 *
 * Opens a single shared PDO connection as $pdo. Required by every page
 * that talks to the database (home.php, signin_signup.php, cart.php,
 * checkout.php).
 *
 * XAMPP defaults: host 127.0.0.1, user "root", empty password. If you
 * set a MySQL root password or use a different account, update
 * DB_USER / DB_PASS below to match.
 */

const DB_HOST = '127.0.0.1';
const DB_NAME = 'miguel_gutib';
const DB_USER = 'root';
const DB_PASS = '';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
        ]
    );
} catch (PDOException $e) {
    // In production, log $e->getMessage() instead of showing it to visitors.
    die('Database connection failed. Make sure MySQL is running in XAMPP '
        . 'and that the "miguel_gutib" database has been imported '
        . '(see database/schema.sql). Details: ' . $e->getMessage());
}
