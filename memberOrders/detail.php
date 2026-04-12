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

<style>
    .detail-container { max-width: 900px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    h1 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 15px; margin-bottom: 25px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
    .info-item { margin-bottom: 10px; }
    .info-label { font-weight: bold; color: #666; width: 130px; display: inline-block; }
    .info-value { color: #333; }
    .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    .status-Processing { background: #fff3e0; color: #ff9800; }
    .status-Shipped { background: #e3f2fd; color: #2196f3; }
    .status-Delivered { background: #e8f5e9; color: #4caf50; }
    .product-img { width: 60px; height: 80px; object-fit: cover; border-radius: 6px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
    th { background: #f5f5f5; }
    .price { font-weight: bold; color: #e60012; }
    .total-box { text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee; }
    .total-amount { font-size: 24px; font-weight: bold; color: #e60012; }
    .points-box { background: #fff3e0; padding: 15px; border-radius: 10px; margin: 20px 0; display: flex; justify-content: space-between; }
    .btn-back { background: #666; color: white; padding: 10px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
</style>

<div class="detail-container">
    <h1>📄 Order Details</h1>
    <div class="info-grid">
        <div>
            <div class="info-item"><span class="info-label">Order Number:</span><span class="info-value"><?php echo htmlspecialchars($order_number); ?></span></div>
            <div class="info-item"><span class="info-label">Order Date:</span><span class="info-value"><?php echo date('d M Y, h:i A', strtotime($order_info['order_date'])); ?></span></div>
            <div class="info-item"><span class="info-label">Status:</span><span class="status status-<?php echo $order_info['order_status']; ?>"><?php echo $order_info['order_status']; ?></span></div>
        </div>
        <div>
            <div class="info-item"><span class="info-label">Shipping Address:</span><span class="info-value"><?php echo nl2br(htmlspecialchars($order_info['shipping_address'])); ?></span></div>
            <div class="info-item"><span class="info-label">Contact Phone:</span><span class="info-value"><?php echo htmlspecialchars($order_info['contact_phone']); ?></span></div>
            <div class="info-item"><span class="info-label">Payment Status:</span><span class="info-value"><?php echo $order_info['payment_status'] ?? 'Pending'; ?></span></div>
        </div>
    </div>
    
    <h3>🛍️ Items</h3>
    <table>
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
                // 计算单价 = total_price / quantity
                $unit_price = $item['total_price'] / $item['quantity'];
            ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="../images/<?php echo $item['product_image'] ?: 'default.jpg'; ?>" class="product-img" onerror="this.src='../images/default.jpg'">
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                        </div>
                    </div>
                    <td><?php echo !empty($item['selected_size']) ? $item['selected_size'] : 'N/A'; ?></div>
                    <td><?php echo $item['quantity']; ?></div>
                    <td class="price">RM <?php echo number_format($unit_price, 2); ?></div>
                    <td class="price">RM <?php echo number_format($item['total_price'], 2); ?></div>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="points-box">
        <span>🎁 Points Earned: <strong><?php echo $order_info['points_earned']; ?></strong></span>
        <span>💎 Points Used: <strong><?php echo $order_info['points_used']; ?></strong> (RM <?php echo number_format($order_info['points_used'] / 100, 2); ?> discount)</span>
    </div>
    
    <div class="total-box">
        <span style="font-size: 18px;">Total Paid:</span>
        <span class="total-amount">RM <?php echo number_format($total, 2); ?></span>
    </div>
    
    <a href="history.php" class="btn-back">← Back to Orders</a>
</div>
<?php require_once __DIR__ . '/../includes/_foot.php'; ?>