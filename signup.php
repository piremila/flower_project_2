<?php
/**
 * signup.php — Handles the Create Account form (accound.html)
 *
 * Expected POST fields (matching accound.html input names):
 *   fullname, phone, email, password, confirmPassword
 *
 * On success  → redirects to login.php with ?registered=1
 * On failure  → redirects back to accound.html with ?error=<code>
 */

session_start();
require_once __DIR__ . '/db.php';

/* ── Only accept POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: accound.html');
    exit;
}

/* ── Collect and sanitize inputs ── */
$fullname        = trim($_POST['fullname']        ?? '');
$phone           = trim($_POST['phone']           ?? '');
$email           = trim($_POST['email']           ?? '');
$password        = $_POST['password']             ?? '';
$confirmPassword = $_POST['confirmPassword']      ?? '';

/* ── Server-side validation ── */
$errors = [];

if ($fullname === '') {
    $errors[] = 'Full name is required.';
}

if ($phone === '') {
    $errors[] = 'Phone number is required.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}

if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    $msg = urlencode(implode(' ', $errors));
    header("Location: accound.html?error=" . $msg);
    exit;
}

/* ── Check for duplicate email ── */
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    header('Location: accound.html?error=' . urlencode('An account with that email already exists. Please sign in instead.'));
    exit;
}

/* ── Hash password and insert ── */
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$insert = $pdo->prepare(
    'INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)'
);
$insert->execute([$fullname, $email, $phone, $hashedPassword]);

/* ── Redirect to login with success flag ── */
header('Location: login.php?registered=1');
exit;
