<?php
/**
 * order_confirm.php — Order confirmation page.
 *
 * Replaces / works alongside final.html.
 * Reads the order from the database using ?ref=RK-XXXXXX.
 * Preserves the exact visual design of final.css / final.html.
 */

session_start();
require_once __DIR__ . '/db.php';

/* ── Fetch order from database ── */
$orderRef = htmlspecialchars(trim($_GET['ref'] ?? ''), ENT_QUOTES, 'UTF-8');
$order    = null;

if ($orderRef !== '') {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_ref = ? LIMIT 1');
    $stmt->execute([$orderRef]);
    $order = $stmt->fetch();
}

/* If no valid order is found, redirect to homepage */
if (!$order) {
    header('Location: j.html');
    exit;
}

$customerName = htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8');
$orderDate    = date('d M Y, h:i A', strtotime($order['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful | R.K Fresh Flowers</title>
    <link rel="stylesheet" href="final.css">
    <style>
        /* Extra info block — sits neatly below the existing final.css layout */
        .order-info {
            margin: 18px auto 0;
            max-width: 420px;
            background: #f9fdf9;
            border: 1px solid #c8dfc9;
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 13px;
            color: #3a5e3b;
            text-align: left;
            line-height: 1.9;
        }
        .order-info strong {
            color: #2b4d2c;
        }
        .order-ref-badge {
            display: inline-block;
            background: #3e6044;
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

    <div class="success-container">

        <div class="success-icon">
            <span>✓</span>
        </div>

        <h1>Order Successful!</h1>

        <div class="line"></div>

        <p class="greeting">Dear <?= $customerName ?>,</p>

        <p class="message">
            Your order has been submitted successfully!
        </p>

        <div class="order-ref-badge"><?= $orderRef ?></div>

        <div class="order-info">
            <strong>Order Reference:</strong> <?= $orderRef ?><br>
            <strong>Order Date:</strong> <?= $orderDate ?><br>
            <strong>Status:</strong> Pending — we will confirm your order shortly.
        </div>

        <p class="thanks">
            Thank you for ordering with us.
            <br>
            We look forward to serving you again!
        </p>

        <p class="goodbye">Have a Wonderful Day! 🌸</p>

        <a href="j.html" class="home-btn">← Back to Home</a>

    </div>

</body>
</html>
