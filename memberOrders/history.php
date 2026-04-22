<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // ✅ 按 order_id 分组（关键修复）
    $stmt = $pdo->prepare("
        SELECT 
            order_id,
            MIN(order_date) as order_date,
            SUM(total_price) as total_price,
            SUM(points_used) as points_used,
            SUM(points_earned) as points_earned,
            MAX(order_status) as order_status,
            MAX(payment_status) as payment_status
        FROM orders
        WHERE user_id = ?
        GROUP BY order_id
        ORDER BY order_date DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="../css/app.css">

<div class="member-history-container">
    <h1>📦 My Orders</h1>

```
<?php if (empty($orders)): ?>
    <div class="member-history-empty">
        <p>You haven't placed any orders yet.</p>
        <a href="../ViewAllProduct.php" class="member-history-view-btn">Start Shopping</a>
    </div>
<?php else: ?>

    <?php foreach ($orders as $order): ?>

        <div class="order-card" style="margin-bottom:20px; padding:20px; border-radius:12px; background:#fff; box-shadow:0 5px 15px rgba(0,0,0,0.05);">

            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                
                <div>
                    <strong>Order #<?php echo $order['order_id']; ?></strong><br>
                    <small><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></small>
                </div>

                <div>
                    <strong style="color:#e60012;">
                        RM <?php echo number_format($order['total_price'], 2); ?>
                    </strong>
                </div>

            </div>

            <div style="margin-top:10px; display:flex; justify-content:space-between; flex-wrap:wrap;">
                
                <div>
                    Points: 
                    <span style="color:#e60012;">-<?php echo $order['points_used']; ?></span> /
                    <span style="color:#4caf50;">+<?php echo $order['points_earned']; ?></span>
                </div>

                <div>
                    <span class="status-<?php echo strtolower($order['order_status']); ?>" style="font-weight:600;">
                        <?php echo $order['order_status']; ?>
                    </span>
                </div>

            </div>

            <div style="margin-top:15px;">
                <a href="detail.php?order_id=<?php echo $order['order_id']; ?>" 
                   class="member-history-view-btn">
                    View Details →
                </a>
            </div>

        </div>

    <?php endforeach; ?>

<?php endif; ?>

<div style="margin-top:30px;">
    <a href="../index.php" class="member-history-back-btn">🏠 Home</a>
    <a href="../ViewAllProduct.php" class="member-history-back-btn">🛍️ Shop More</a>
</div>
```

</div>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>
