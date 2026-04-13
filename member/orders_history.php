<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// 获取用户的所有订单（按订单号分组）
$stmt = $pdo->prepare("
    SELECT 
        order_number, 
        MIN(order_date) as order_date, 
        SUM(total_price) as total_price, 
        SUM(points_used) as points_used, 
        SUM(points_earned) as points_earned, 
        order_status,
        payment_status
    FROM orders 
    WHERE user_id = ? 
    GROUP BY order_number 
    ORDER BY order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<style>
    .orders-container {
        max-width: 1200px;
        margin: 50px auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        overflow: hidden;
        padding: 30px;
    }
    
    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .page-title i {
        color: #e60012;
    }
    
    .page-subtitle {
        color: #888;
        font-size: 14px;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    /* 订单卡片 */
    .order-card {
        background: white;
        border: 1px solid #eee;
        border-radius: 16px;
        margin-bottom: 25px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .order-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .order-header {
        background: #fafafa;
        padding: 18px 25px;
        display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    border-bottom: 1px solid #eee;
}

.order-number {
    font-weight: 700;
    font-size: 16px;
    color: #1a1a1a;
}

.order-number span {
    color: #e60012;
    font-size: 14px;
}

.order-date {
    font-size: 13px;
    color: #888;
}

.order-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
}

.status-processing {
    background: #fff3e0;
    color: #ff9800;
}

.status-shipped {
    background: #e3f2fd;
    color: #2196f3;
}

.status-delivered {
    background: #e8f5e9;
    color: #4caf50;
}

.status-cancelled {
    background: #ffebee;
    color: #f44336;
}

.payment-paid {
    background: #e8f5e9;
    color: #4caf50;
}

.payment-pending {
    background: #fff3e0;
    color: #ff9800;
}

.order-body {
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.order-summary {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.summary-item {
    text-align: center;
    min-width: 80px;
}

.summary-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.summary-value {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
}

.summary-value.points-earned {
    color: #ff9800;
}

.summary-value.points-used {
    color: #e60012;
}

.btn-view {
    background: #e60012;
    color: white;
    padding: 10px 25px;
    border-radius: 40px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-view:hover {
    background: #cc0010;
    transform: translateY(-2px);
}

.empty-orders {
    text-align: center;
    padding: 80px 20px;
}

.empty-icon {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-orders h3 {
    font-size: 20px;
    color: #666;
    margin-bottom: 10px;
}

.empty-orders p {
    color: #999;
    margin-bottom: 25px;
}

.btn-shop {
    background: #e60012;
    color: white;
    padding: 12px 35px;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    transition: 0.3s;
}

.btn-shop:hover {
    background: #cc0010;
    transform: translateY(-2px);
}

.actions {
    margin-top: 30px;
    text-align: center;
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-back {
    background: #666;
    color: white;
    padding: 10px 25px;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}

.btn-back:hover {
    background: #555;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .orders-container {
        margin: 20px;
        padding: 20px;
    }
    .order-header {
        flex-direction: column;
        text-align: center;
    }
    .order-body {
        flex-direction: column;
        text-align: center;
    }
    .btn-view {
        margin-top: 10px;
    }
}
</style>

<div class="orders-container">
    <div class="page-title">
        <i class="fas fa-box"></i> My Orders
    </div>
    <div class="page-subtitle">
        Track and manage your orders
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <div class="empty-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h3>No orders yet</h3>
            <p>Looks like you haven't placed any orders yet.</p>
            <a href="../ViewAllProduct.php" class="btn-shop">Start Shopping →</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-number">
                            <span>Order #</span> <?php echo htmlspecialchars($order['order_number']); ?>
                        </div>
                        <div class="order-date">
                            <i class="far fa-calendar-alt"></i> <?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
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
                
                <div class="order-body">
                    <div class="order-summary">
                        <div class="summary-item">
                            <div class="summary-label">Total Amount</div>
                            <div class="summary-value">RM <?php echo number_format($order['total_price'], 2); ?></div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Points Earned</div>
                            <div class="summary-value points-earned">+<?php echo number_format($order['points_earned']); ?></div>
                        </div>
                        <?php if ($order['points_used'] > 0): ?>
                        <div class="summary-item">
                            <div class="summary-label">Points Used</div>
                            <div class="summary-value points-used">-<?php echo number_format($order['points_used']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <a href="order_detail.php?order_number=<?php echo urlencode($order['order_number']); ?>" class="btn-view">
                        View Details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="actions">
            <a href="../index.php" class="btn-back">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="../ViewAllProduct.php" class="btn-back" style="background: #e60012;">
                <i class="fas fa-shopping-bag"></i> Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/_foot.php'; ?>
