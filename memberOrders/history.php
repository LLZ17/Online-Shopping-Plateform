<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$user_id = $_SESSION['user_id'];

// 按照 order_date 分组，确保一个订单显示成一个卡片
$stmt = $pdo->prepare("
    SELECT 
        order_date, 
        MAX(total_price) as total_price, 
        MAX(order_status) as order_status,
        COUNT(*) as items_count 
    FROM orders 
    WHERE user_id = ? 
    GROUP BY order_date 
    ORDER BY order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<div class="member-history-container" style="max-width:800px; margin:40px auto; padding:20px;">
    <h1>📦 My Orders</h1>
    <?php if (empty($orders)): ?>
        <p>No orders found. <a href="../ViewAllProduct.php">Shop Now</a></p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div style="background:#fff; border:1px solid #ddd; padding:20px; border-radius:12px; margin-bottom:15px;">
                <div style="display:flex; justify-content:space-between;">
                    <div>
                        <strong>Date:</strong> <?php echo $order['order_date']; ?>
                    </div>
                    <div style="color:#e60012; font-weight:bold;">
                        RM <?php echo number_format($order['total_price'], 2); ?>
                    </div>
                </div>
                <div style="margin-top:10px; display:flex; justify-content:space-between;">
                    <span>Status: <?php echo $order['order_status']; ?> (<?php echo $order['items_count']; ?> items)</span>
                    <a href="detail.php?date=<?php echo urlencode($order['order_date']); ?>">View Details →</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/_foot.php'; ?>