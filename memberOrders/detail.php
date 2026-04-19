<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';
$user_id = $_SESSION['user_id'];

if (empty($order_number)) {
    header('Location: history.php');
    exit();
}

// 获取订单信息
$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.product_image
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.order_number = ? AND o.user_id = ?
");
$stmt->execute([$order_number, $user_id]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: history.php');
    exit();
}

$order_info = $items[0];
$total = 0;
foreach ($items as $item) {
    $total += $item['total_price'];
}
?>

<link rel="stylesheet" href="../css/app.css">

<div class="product-order-detail-container">
    <h1>📄 Order Details</h1>
    <div class="product-order-detail-grid">
        <div>
            <div class="product-order-detail-item"><span class="product-order-detail-label">Order Number:</span><span class="product-order-detail-value"><?php echo htmlspecialchars($order_number); ?></span></div>
            <div class="product-order-detail-item"><span class="product-order-detail-label">Order Date:</span><span class="product-order-detail-value"><?php echo date('d M Y, h:i A', strtotime($order_info['order_date'])); ?></span></div>
            <div class="product-order-detail-item"><span class="product-order-detail-label">Status:</span><span class="product-order-detail-status status-<?php echo $order_info['order_status']; ?>"><?php echo $order_info['order_status']; ?></span></div>
        </div>
        <div>
            <div class="product-order-detail-item"><span class="product-order-detail-label">Shipping Address:</span><span class="product-order-detail-value"><?php echo nl2br(htmlspecialchars($order_info['shipping_address'])); ?></span></div>
            <div class="product-order-detail-item"><span class="product-order-detail-label">Contact Phone:</span><span class="product-order-detail-value"><?php echo htmlspecialchars($order_info['contact_phone']); ?></span></div>
            <div class="product-order-detail-item"><span class="product-order-detail-label">Payment Status:</span><span class="product-order-detail-value"><?php echo $order_info['payment_status'] ?? 'Pending'; ?></span></div>
        </div>
    </div>
    
    <h3>🛍️ Items</h3>
    <table class="product-order-detail-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): 
                $unit_price = $item['total_price'] / $item['quantity'];
            ?>
                <tr>
                    <td>
                        <div class="product-order-detail-product">
                            <img src="../images/<?php echo $item['product_image'] ?: 'default.jpg'; ?>" class="product-order-detail-img" onerror="this.src='../images/default.jpg'">
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                        </div>
                    </div>
                    <td><?php echo !empty($item['selected_size']) ? $item['selected_size'] : 'N/A'; ?></div>
                    <td><?php echo $item['quantity']; ?></div>
                    <td class="product-order-detail-price">RM <?php echo number_format($unit_price, 2); ?></div>
                    <td class="product-order-detail-price">RM <?php echo number_format($item['total_price'], 2); ?></div>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="product-order-detail-points">
        <span>🎁 Points Earned: <strong><?php echo $order_info['points_earned']; ?></strong></span>
        <span>💎 Points Used: <strong><?php echo $order_info['points_used']; ?></strong> (RM <?php echo number_format($order_info['points_used'] / 100, 2); ?> discount)</span>
    </div>
    
    <div class="product-order-detail-total">
        <span style="font-size: 18px;">Total Paid:</span>
        <span class="product-order-detail-total-amount">RM <?php echo number_format($total, 2); ?></span>
    </div>
    
    <a href="history.php" class="product-order-detail-back-btn">← Back to Orders</a>
</div>

<script src="../js/app.js"></script>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>
