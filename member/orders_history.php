<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// 修复：使用 order_id 而不是 order_number
$stmt = $pdo->prepare("
    SELECT 
        order_id, 
        MIN(order_date) as order_date, 
        SUM(total_price) as total_price, 
        SUM(points_used) as points_used, 
        SUM(points_earned) as points_earned, 
        order_status,
        payment_status
    FROM orders 
    WHERE user_id = ? 
    GROUP BY order_id 
    ORDER BY order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<link rel="stylesheet" href="../css/app.css">

<div class="orders-history-container">
    <div class="orders-history-title">
        <i class="fas fa-box"></i> My Orders
    </div>
    <div class="orders-history-subtitle">
        Track and manage your orders
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="orders-history-empty">
            <div class="orders-history-empty-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h3>No orders yet</h3>
            <p>Looks like you haven't placed any orders yet.</p>
            <a href="../ViewAllProduct.php" class="orders-history-shop-btn">Start Shopping →</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="orders-history-card">
                <div class="orders-history-card-header">
                    <div>
                        <div class="orders-history-order-number">
                            <span>Order #</span> <?php echo htmlspecialchars($order['order_id']); ?>
                        </div>
                        <div class="orders-history-order-date">
                            <i class="far fa-calendar-alt"></i> <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?>
                        </div>
                    </div>
                    <div class="orders-history-status-group">
                        <span class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                            <i class="fas <?php echo $order['order_status'] == 'Delivered' ? 'fa-check-circle' : ($order['order_status'] == 'Processing' ? 'fa-clock' : 'fa-truck'); ?>"></i>
                            <?php echo $order['order_status']; ?>
                        </span>
                        <span class="order-status payment-<?php echo strtolower($order['payment_status']); ?>">
                            <i class="fas fa-credit-card"></i>
                            <?php echo $order['payment_status']; ?>
                        </span>
                    </div>
                </div>
                
                <div class="orders-history-card-body">
                    <div class="orders-history-summary">
                        <div class="orders-history-summary-item">
                            <div class="orders-history-summary-label">Total Amount</div>
                            <div class="orders-history-summary-value">RM <?php echo number_format($order['total_price'], 2); ?></div>
                        </div>
                        <div class="orders-history-summary-item">
                            <div class="orders-history-summary-label">Points Earned</div>
                            <div class="orders-history-summary-value points-earned">+<?php echo number_format($order['points_earned']); ?></div>
                        </div>
                        <?php if ($order['points_used'] > 0): ?>
                        <div class="orders-history-summary-item">
                            <div class="orders-history-summary-label">Points Used</div>
                            <div class="orders-history-summary-value points-used">-<?php echo number_format($order['points_used']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 修复：使用 order_id 而不是 order_number -->
                    <a href="order_detail.php?order_id=<?php echo urlencode($order['order_id']); ?>" class="orders-history-view-btn">
                        View Details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="orders-history-actions">
            <a href="../index.php" class="orders-history-back-btn">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="../ViewAllProduct.php" class="orders-history-back-btn orders-history-shop-btn">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="../js/app.js"></script>

<?php require_once '../includes/_foot.php'; ?>
