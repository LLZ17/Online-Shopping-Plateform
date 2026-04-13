<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT order_number, MIN(order_date) as order_date, SUM(total_price) as total_price, 
           SUM(points_used) as points_used, SUM(points_earned) as points_earned, order_status
    FROM orders WHERE user_id = ? GROUP BY order_number ORDER BY order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<style>
    .orders-container { max-width: 1000px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    h1 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 15px; margin-bottom: 25px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f5f5f5; }
    .status-Processing { color: #ff9800; }
    .status-Shipped { color: #2196f3; }
    .status-Delivered { color: #4caf50; }
    .status-Cancelled { color: #f44336; }
    .btn-view { background: #e60012; color: white; padding: 6px 15px; text-decoration: none; border-radius: 5px; font-size: 12px; }
    .empty-orders { text-align: center; padding: 50px; color: #999; }
    .actions { margin-top: 20px; text-align: center; }
    .btn-back { background: #666; color: white; padding: 10px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
</style>

<div class="orders-container">
    <h1>📦 My Orders</h1>
    <?php if (empty($orders)): ?>
        <div class="empty-orders"><p>You haven't placed any orders yet.</p><a href="../memberProducts/listing.php" class="btn-view">Start Shopping</a></div>
    <?php else: ?>
        <table>
            <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Points Used</th><th>Points Earned</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong><br><small><?php echo date('d M Y', strtotime($order['order_date'])); ?></small></td>
                        <td>RM <?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo $order['points_used']; ?></td>
                        <td><?php echo $order['points_earned']; ?></td>
                        <td class="status-<?php echo $order['order_status']; ?>"><?php echo $order['order_status']; ?></td>
                        <td><a href="detail.php?order_number=<?php echo $order['order_number']; ?>" class="btn-view">View →</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="actions">
            <a href="../index.php" class="btn-back">🏠 Home</a>
            <a href="../memberProducts/listing.php" class="btn-back" style="background:#e60012;">🛍️ Shop</a>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/_foot.php'; ?>