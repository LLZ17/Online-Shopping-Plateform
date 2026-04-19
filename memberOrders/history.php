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

<link rel="stylesheet" href="../css/app.css">

<div class="member-history-container">
    <h1>📦 My Orders</h1>
    <?php if (empty($orders)): ?>
        <div class="member-history-empty">
            <p>You haven't placed any orders yet.</p>
            <a href="../memberProducts/listing.php" class="member-history-view-btn">Start Shopping</a>
        </div>
    <?php else: ?>
        <table class="member-history-table">
            <thead>
                <tr><th>Order #</th><th>Date</th><th>Total</th><th>Points Used</th><th>Points Earned</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong><br><small><?php echo date('d M Y', strtotime($order['order_date'])); ?></small></td>
                        <td>RM <?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo $order['points_used']; ?></td>
                        <td><?php echo $order['points_earned']; ?></td>
                        <td class="member-history-status status-<?php echo $order['order_status']; ?>"><?php echo $order['order_status']; ?></td>
                        <td><a href="detail.php?order_number=<?php echo $order['order_number']; ?>" class="member-history-view-btn">View →</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="member-history-actions">
            <a href="../index.php" class="member-history-back-btn">🏠 Home</a>
            <a href="../memberProducts/listing.php" class="member-history-back-btn member-history-shop-btn">🛍️ Shop</a>
        </div>
    <?php endif; ?>
</div>

<script src="../js/app.js"></script>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>
