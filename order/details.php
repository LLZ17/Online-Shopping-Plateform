<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = $_SESSION['user_id'];

// 获取订单信息（队友的表结构）
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: history.php');
    exit();
}

// 获取订单商品详情
$stmt = $pdo->prepare("
    SELECT o.*, p.product_image 
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['total_price'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        h1 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 15px; margin-bottom: 25px; }
        h3 { color: #555; margin: 20px 0 15px 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .info-item { margin-bottom: 10px; }
        .info-label { font-weight: bold; color: #666; width: 120px; display: inline-block; }
        .info-value { color: #333; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-Processing { background: #fff3e0; color: #ff9800; }
        .status-Shipped { background: #e3f2fd; color: #2196f3; }
        .status-Delivered { background: #e8f5e9; color: #4caf50; }
        .status-Cancelled { background: #ffebee; color: #f44336; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f5f5f5; color: #666; }
        td { padding: 15px 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        .product-img { width: 60px; height: 80px; object-fit: cover; border-radius: 6px; }
        .product-name { font-weight: bold; color: #333; }
        .product-size { font-size: 12px; color: #999; margin-top: 5px; }
        .points-summary { background: #fff3e0; padding: 15px 20px; border-radius: 10px; margin: 20px 0; display: flex; justify-content: space-between; }
        .total-box { text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee; }
        .total-amount { font-size: 24px; font-weight: bold; color: #e60012; }
        .btn-back { background: #666; color: white; padding: 10px 25px; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 20px; }
        .btn-back:hover { background: #555; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Order Details</h1>
        
        <div class="info-grid">
            <div>
                <div class="info-item"><span class="info-label">Order Number:</span><span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span></div>
                <div class="info-item"><span class="info-label">Order Date:</span><span class="info-value"><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></span></div>
                <div class="info-item"><span class="info-label">Status:</span><span class="status status-<?php echo $order['order_status']; ?>"><?php echo $order['order_status']; ?></span></div>
            </div>
            <div>
                <div class="info-item"><span class="info-label">Shipping Address:</span><span class="info-value"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span></div>
                <div class="info-item"><span class="info-label">Contact Phone:</span><span class="info-value"><?php echo htmlspecialchars($order['contact_phone']); ?></span></div>
                <div class="info-item"><span class="info-label">Payment:</span><span class="info-value"><?php echo $order['payment_status'] ?? 'Pending'; ?></span></div>
            </div>
        </div>
        
        <h3>🛍️ Items</h3>
        <table>
            <thead>
                <tr><th>Product</th><th>Size</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <img src="../images/<?php echo $item['product_image'] ?: 'default.jpg'; ?>" class="product-img" onerror="this.src='../images/default.jpg'">
                            <div>
                                <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo !empty($item['selected_size']) ? $item['selected_size'] : 'N/A'; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>RM <?php echo number_format($item['price'], 2); ?></td>
                    <td class="price">RM <?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="points-summary">
            <span>🎁 Points Earned: <strong><?php echo $order['points_earned']; ?></strong></span>
            <span>💎 Points Used: <strong><?php echo $order['points_used']; ?></strong> (RM <?php echo number_format($order['points_used'] / 100, 2); ?> discount)</span>
        </div>
        
        <div class="total-box">
            <span style="font-size: 18px;">Total Paid:</span>
            <span class="total-amount">RM <?php echo number_format($total, 2); ?></span>
        </div>
        
        <a href="history.php" class="btn-back">← Back to Orders</a>
    </div>
</body>
</html>