<?php
/**
 * order_submit.php — Handles the order form (order.html → POST here)
 *
 * Fields expected (matching order.html input names):
 *   name, email, phone, flower
 *
 * On success  → redirects to order_confirm.php?ref=RK-XXXXXX
 * On failure  → redirects back to order.html?error=<message>
 */

session_start();
require_once __DIR__ . '/db.php';

/* ── Only accept POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: order.html');
    exit;
}

/* ── Collect and sanitize inputs ── */
$name   = trim($_POST['name']   ?? '');
$email  = trim($_POST['email']  ?? '');
$phone  = trim($_POST['phone']  ?? '');
$flower = trim($_POST['flower'] ?? '');

/* ── Server-side validation ── */
$errors = [];

if ($name === '') {
    $errors[] = 'Full name is required.';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if ($phone === '') {
    $errors[] = 'Contact number is required.';
}
if ($flower === '') {
    $errors[] = 'Please describe the flowers you would like to order.';
}

if (!empty($errors)) {
    $msg = urlencode(implode(' ', $errors));
    header("Location: order.html?error=$msg");
    exit;
}

/* ── Attach logged-in user if present ── */
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

/* ── Generate human-readable order reference: RK-000001 ── */
$countStmt = $pdo->query('SELECT COUNT(*) FROM orders');
$orderCount = (int)$countStmt->fetchColumn();
$orderRef   = 'RK-' . str_pad($orderCount + 1, 6, '0', STR_PAD_LEFT);

/* ── Insert order into database ── */
try {
    $stmt = $pdo->prepare(
        'INSERT INTO orders
             (order_ref, user_id, customer_name, email, phone, flower_details, order_status)
         VALUES
             (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $orderRef,
        $userId,
        $name,
        $email,
        $phone,
        $flower,
        'pending'
    ]);
} catch (PDOException $e) {
    header('Location: order.html?error=' . urlencode('Sorry, we could not save your order. Please try again.'));
    exit;
}

/* ── Redirect to confirmation page ── */
header('Location: order_confirm.php?ref=' . urlencode($orderRef));
exit;
