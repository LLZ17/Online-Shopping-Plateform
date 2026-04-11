<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// 获取所有订单（按 order_number 分组）
$stmt = $pdo->prepare("
    SELECT order_number, 
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
    .orders-container { max-width: 1000px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    h1 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f5f5f5; font-weight: 600; color: #555; }
    .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    .status-Processing { background: #fff3e0; color: #ff9800; }
    .status-Shipped { background: #e3f2fd; color: #2196f3; }
    .status-Delivered { background: #e8f5e9; color: #4caf50; }
    .status-Cancelled { background: #ffebee; color: #f44336; }
    .payment-Paid { background: #e8f5e9; color: #4caf50; }
    .payment-Pending { background: #fff3e0; color: #ff9800; }
    .btn-view { background: #e60012; color: white; padding: 6px 15px; text-decoration: none; border-radius: 5px; font-size: 12px; display: inline-block; }
    .btn-view:hover { background: #cc0010; }
    .btn-back { background: #666; color: white; padding: 10px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; margin-right: 10px; }
    .btn-shop { background: #e60012; color: white; padding: 10px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
    .empty-orders { text-align: center; padding: 50px; color: #999; }
    .actions { margin-top: 30px; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
    .order-status { font-size: 13px; margin-top: 5px; }
    .tracking { font-size: 12px; color: #666; margin-top: 5px; }
</style>

<div class="orders-container">
    <h1><i class="fa-solid fa-receipt"></i> My Orders</h1>
    
    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <i class="fa-solid fa-box-open" style="font-size: 50px; color: #ccc; margin-bottom: 15px; display: block;"></i>
            <p>You haven't placed any orders yet.</p>
            <a href="../products/listing.php" class="btn-view">Start Shopping</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Points</th>
                    <th>Order Status</th>
                    <th>Payment</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong><br>
                        <small style="color:#999;">ID: <?php echo substr($order['order_number'], -6); ?></small>
                    </div>
                    <td><?php echo date('d M Y', strtotime($order['order_date'])); ?><br>
                        <small style="color:#999;"><?php echo date('h:i A', strtotime($order['order_date'])); ?></small>
                    </div>
                    <td><strong style="color:#e60012;">RM <?php echo number_format($order['total_price'], 2); ?></strong></div>
                    <td>
                        <div>🎁 Used: <?php echo $order['points_used']; ?></div>
                        <div>⭐ Earned: <?php echo $order['points_earned']; ?></div>
                    </div>
                    <td>
                        <span class="status status-<?php echo $order['order_status']; ?>">
                            <?php echo $order['order_status']; ?>
                        </span>
                        <div class="order-status">
                            <?php if ($order['order_status'] == 'Processing'): ?>
                                ⏳ Preparing your order
                            <?php elseif ($order['order_status'] == 'Shipped'): ?>
                                🚚 On the way
                            <?php elseif ($order['order_status'] == 'Delivered'): ?>
                                ✅ Delivered
                            <?php elseif ($order['order_status'] == 'Cancelled'): ?>
                                ❌ Cancelled
                            <?php endif; ?>
                        </div>
                    </div>
                    <td>
                        <span class="status payment-<?php echo $order['payment_status'] ?? 'Pending'; ?>">
                            <?php echo $order['payment_status'] ?? 'Pending'; ?>
                        </span>
                    </div>
                    <td><a href="detail.php?order_number=<?php echo $order['order_number']; ?>" class="btn-view">View Details →</a></div>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="actions">
            <a href="../index.php" class="btn-back"><i class="fa-solid fa-home"></i> Back to Home</a>
            <a href="../products/listing.php" class="btn-shop"><i class="fa-solid fa-shop"></i> Continue Shopping</a>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; text-align: center;">
            <small>💡 Need help? Contact us at <strong>support@pxxmall.com</strong></small>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>