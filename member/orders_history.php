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
        order_status
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
        margin: 40px auto;
        background: white;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    h1 {
        color: #1a1a1a;
        border-bottom: 3px solid #e60012;
        padding-bottom: 15px;
        margin-bottom: 25px;
        font-size: 28px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th, td {
        padding: 15px 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    th {
        background: #f8f8f8;
        font-weight: 600;
        color: #555;
    }
    
    tr:hover {
        background: #fafafa;
    }
    
    .status-Processing {
        color: #ff9800;
        font-weight: 600;
    }
    
    .status-Shipped {
        color: #2196f3;
        font-weight: 600;
    }
    
    .status-Delivered {
        color: #4caf50;
        font-weight: 600;
    }
    
    .status-Cancelled {
        color: #f44336;
        font-weight: 600;
    }
    
    .btn-view {
        background: #e60012;
        color: white;
        padding: 6px 18px;
        text-decoration: none;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        transition: 0.3s;
        display: inline-block;
    }
    
    .btn-view:hover {
        background: #cc0010;
        transform: translateY(-2px);
    }
    
    .empty-orders {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    
    .empty-orders p {
        margin-bottom: 20px;
        font-size: 16px;
    }
    
    .btn-shop {
        background: #e60012;
        color: white;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 30px;
        display: inline-block;
        font-weight: 600;
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
        text-decoration: none;
        border-radius: 30px;
        display: inline-block;
        font-weight: 500;
        transition: 0.3s;
    }
    
    .btn-back:hover {
        background: #555;
        transform: translateY(-2px);
    }
    
    .order-number {
        font-weight: 700;
        color: #1a1a1a;
    }
    
    .order-date {
        font-size: 12px;
        color: #888;
        margin-top: 4px;
    }
    
    @media (max-width: 768px) {
        .orders-container {
            margin: 20px;
            padding: 15px;
        }
        th, td {
            padding: 10px 8px;
            font-size: 12px;
        }
        .btn-view {
            padding: 4px 12px;
            font-size: 10px;
        }
    }
</style>

<div class="orders-container">
    <h1>📦 My Orders</h1>
    
    <?php if (empty($orders)): ?>
        <div class="empty-orders">
            <p>You haven't placed any orders yet.</p>
            <a href="../ViewAllProduct.php" class="btn-shop">Start Shopping →</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Points Used</th>
                        <th>Points Earned</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div class="order-number"><?php echo htmlspecialchars($order['order_number']); ?></div>
                            </td>
                            <td>
                                <?php echo date('d M Y', strtotime($order['order_date'])); ?>
                                <div class="order-date"><?php echo date('h:i A', strtotime($order['order_date'])); ?></div>
                            </td>
                            <td class="price">RM <?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo number_format($order['points_used']); ?></td>
                            <td><?php echo number_format($order['points_earned']); ?></td>
                            <td class="status-<?php echo $order['order_status']; ?>">
                                <?php 
                                    $status_icon = '';
                                    if($order['order_status'] == 'Processing') $status_icon = '⏳ ';
                                    if($order['order_status'] == 'Shipped') $status_icon = '🚚 ';
                                    if($order['order_status'] == 'Delivered') $status_icon = '✅ ';
                                    if($order['order_status'] == 'Cancelled') $status_icon = '❌ ';
                                    echo $status_icon . $order['order_status'];
                                ?>
                            </td>
                            <td>
                                <a href="order_detail.php?order_number=<?php echo urlencode($order['order_number']); ?>" class="btn-view">View →</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="actions">
            <a href="../index.php" class="btn-back">🏠 Back to Home</a>
            <a href="../ViewAllProduct.php" class="btn-back" style="background: #e60012;">🛍️ Continue Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/_foot.php'; ?>