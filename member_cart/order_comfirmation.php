<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

if (empty($order_number)) {
    header('Location: unified_cart.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.product_image 
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.order_number = ? AND o.user_id = ?
");
$stmt->execute([$order_number, $_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: unified_cart.php');
    exit();
}

$total = 0;
foreach ($items as $item) {
    $total += $item['total_price'];
}
$order_info = $items[0];
?>

<style>
    .container { max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 12px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .success { color: #28a745; font-size: 60px; }
    h1 { color: #333; }
    .order-number { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 20px 0; }
    .btn { background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px; }
    .btn-gray { background: #666; }
</style>

<div class="container">
    <div class="success">✓</div>
    <h1>Order Confirmed!</h1>
    <p>Thank you for your purchase.</p>
    <div class="order-number">
        <strong>Order Number:</strong> <?php echo htmlspecialchars($order_number); ?><br>
        <strong>Total Paid:</strong> RM <?php echo number_format($total, 2); ?><br>
        <strong>Points Earned:</strong> <?php echo $order_info['points_earned']; ?> points<br>
        <strong>Points Used:</strong> <?php echo $order_info['points_used']; ?> points
    </div>
    <a href="../memberProducts/listing.php" class="btn btn-gray">Continue Shopping</a>
    <a href="../memberOrders/history.php" class="btn">View Orders</a>
</div>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>