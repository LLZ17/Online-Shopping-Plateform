<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// 获取所有订单（按 order_number 分组）
$stmt = $pdo->prepare("
    SELECT order_number, MIN(order_date) as order_date, SUM(total_price) as total_price, 
           SUM(points_used) as points_used, SUM(points_earned) as points_earned,
           order_status
    FROM orders 
    WHERE user_id = ? 
    GROUP BY order_number 
    ORDER BY order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        h1 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .btn-view { background: #e60012; color: white; padding: 5px 15px; text-decoration: none; border-radius: 5px; font-size: 12px; }
        .empty-orders { text-align: center; padding: 50px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Orders</h1>
        
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <p>You haven't placed any orders yet.</p>
                <a href="../products/listing.php">Start Shopping →</a>
            </div>
        <?php else: ?>
            </table>
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
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                            <td>RM <?php echo number_format($order['total_price'], 2); ?></td>
                            <td><?php echo $order['points_used']; ?></td>
                            <td><?php echo $order['points_earned']; ?></td>
                            <td><span><?php echo $order['order_status']; ?></span></td>
                            <td><a href="detail.php?order_number=<?php echo $order['order_number']; ?>" class="btn-view">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>