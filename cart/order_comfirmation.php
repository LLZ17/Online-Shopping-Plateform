<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

if (empty($order_number)) {
    header('Location: index.php');
    exit();
}

// 获取该订单的所有商品
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$order_number, $_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: index.php');
    exit();
}

$total = 0;
foreach ($items as $item) {
    $total += $item['total_price'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; text-align: center; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .success { color: green; font-size: 50px; }
        h1 { color: #333; }
        .order-details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: left; }
        .btn { background: #e60012; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">✓</div>
        <h1>Order Confirmed!</h1>
        <p>Thank you for your purchase.</p>
        
        <div class="order-details">
            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order_number); ?></p>
            <p><strong>Total Amount:</strong> RM <?php echo number_format($total, 2); ?></p>
            <?php if (!empty($items[0]['points_earned'])): ?>
                <p><strong>Points Earned:</strong> <?php echo $items[0]['points_earned']; ?> points</p>
                <p><strong>Points Used:</strong> <?php echo $items[0]['points_used']; ?> points</p>
            <?php endif; ?>
        </div>
        
        <a href="../products/listing.php" class="btn">Continue Shopping</a>
        <a href="../orders/history.php" class="btn" style="background: #666;">View Orders</a>
    </div>
</body>
</html>