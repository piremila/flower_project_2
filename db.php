<?php
/**
 * db.php — Reusable database connection for R.K Fresh Flowers
 *
 * Uses PDO with prepared statements.
 * Include this file in every PHP backend script:
 *   require_once __DIR__ . '/db.php';
 *
 * HOW TO CONFIGURE:
 *   - DB_HOST : Leave as 'localhost' for standard XAMPP.
 *   - DB_NAME : Must match the database you imported (flower).
 *   - DB_USER : Default XAMPP root user is 'root'.
 *   - DB_PASS : Default XAMPP root password is '' (empty string).
 *               If you set a password in phpMyAdmin, change it here.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'flower');
define('DB_USER', 'root');
define('DB_PASS', '');          // <-- change if you added a MySQL password
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST
     . ';dbname=' . DB_NAME
     . ';charset=' . DB_CHARSET;

$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdo_options);
} catch (PDOException $e) {
    // Show a friendly error; never expose raw exception messages publicly.
    http_response_code(500);
    exit('<p style="font-family:sans-serif;color:#c0392b;padding:20px;">'
        . '⚠ Database connection failed. Please check that XAMPP MySQL is running '
        . 'and that <strong>db.php</strong> has the correct credentials.</p>');
}
