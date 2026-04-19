<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// 修复：使用 order_id 而不是 order_number
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

if (empty($order_id)) {
    header('Location: orders_history.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.product_image, p.price as unit_price
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: orders_history.php');
    exit();
}

$total = 0;
$points_used = 0;
$points_earned = 0;
foreach ($items as $item) {
    $total += $item['total_price'];
    $points_used = $item['points_used'];
    $points_earned = $item['points_earned'];
}
$order_info = $items[0];
?>

<link rel="stylesheet" href="../css/app.css">

<div class="order-detail-container">
    <div class="order-detail-header">
        <h1><i class="fas fa-receipt"></i> Order Details</h1>
        <p class="order-detail-subtitle">Review your order information below</p>
        <div class="order-detail-badge-group">
            <span class="order-badge badge-<?php echo strtolower($order_info['order_status']); ?>">
                <i class="fas <?php echo $order_info['order_status'] == 'Delivered' ? 'fa-check-circle' : ($order_info['order_status'] == 'Processing' ? 'fa-clock' : 'fa-truck'); ?>"></i>
                <?php echo $order_info['order_status']; ?>
            </span>
            <span class="order-badge badge-<?php echo strtolower($order_info['payment_status']); ?>">
                <i class="fas fa-credit-card"></i>
                <?php echo $order_info['payment_status']; ?>
            </span>
        </div>
    </div>
    
    <div class="order-detail-body">
        <div class="order-detail-info-grid">
            <div class="order-detail-info-card">
                <div class="order-detail-info-label"><i class="fas fa-hashtag"></i> Order ID</div>
                <div class="order-detail-info-value"><?php echo htmlspecialchars($order_id); ?></div>
            </div>
            <div class="order-detail-info-card">
                <div class="order-detail-info-label"><i class="far fa-calendar-alt"></i> Order Date</div>
                <div class="order-detail-info-value"><?php echo date('d M Y, h:i A', strtotime($order_info['order_date'])); ?></div>
            </div>
            <div class="order-detail-info-card">
                <div class="order-detail-info-label"><i class="fas fa-truck"></i> Shipping Address</div>
                <div class="order-detail-info-value"><?php echo nl2br(htmlspecialchars($order_info['shipping_address'] ?: 'No address provided')); ?></div>
            </div>
        </div>
        
        <div class="order-detail-products-title">
            <i class="fas fa-box"></i> Items Ordered (<?php echo count($items); ?> item<?php echo count($items) > 1 ? 's' : ''; ?>)
        </div>
        
        <?php foreach ($items as $item): ?>
        <div class="order-detail-product-item">
            <img src="../images/<?php echo htmlspecialchars($item['product_image'] ?? 'default.jpg'); ?>" 
                 class="order-detail-product-img" 
                 onerror="this.src='../images/default.jpg'"
                 alt="<?php echo htmlspecialchars($item['product_name']); ?>">
            
            <div class="order-detail-product-details">
                <div class="order-detail-product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                <div class="order-detail-product-meta">
                    <span>🎨 Color: <?php echo $item['colour'] ?: 'Standard'; ?></span>
                    <span>📏 Size: <?php echo $item['selected_size'] ?: 'One Size'; ?></span>
                </div>
            </div>
            
            <div class="order-detail-product-quantity">
                <span class="order-detail-quantity-badge">Qty: <?php echo $item['quantity']; ?></span>
            </div>
            
            <div class="order-detail-product-price">
                <div class="order-detail-unit-price">RM <?php echo number_format($item['unit_price'], 2); ?> each</div>
                <div class="order-detail-subtotal">RM <?php echo number_format($item['total_price'], 2); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <div class="order-detail-summary-box">
            <div class="order-detail-summary-row">
                <span>Subtotal</span>
                <span>RM <?php echo number_format($total, 2); ?></span>
            </div>
            
            <div class="order-detail-summary-row">
                <span>Shipping Fee</span>
                <span>FREE</span>
            </div>
            
            <?php if ($points_used > 0): ?>
            <div class="order-detail-summary-row">
                <span>Points Discount (<?php echo number_format($points_used); ?> points)</span>
                <span>- RM <?php echo number_format($points_used / 100, 2); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="order-detail-summary-row total">
                <span>Total Amount</span>
                <span>RM <?php echo number_format($total, 2); ?></span>
            </div>
        </div>
        
        <div class="order-detail-points-row">
            <div>
                <span><i class="fas fa-star" style="color: #ff9800;"></i> Points Earned from this order</span>
            </div>
            <div>
                <span class="order-detail-points-earned">+<?php echo number_format($points_earned); ?> points</span>
            </div>
        </div>
        
        <?php if ($points_used > 0): ?>
        <div class="order-detail-points-row order-detail-points-used">
            <div>
                <span><i class="fas fa-coins" style="color: #e60012;"></i> Points Used for discount</span>
            </div>
            <div>
                <span class="order-detail-points-used-value">-<?php echo number_format($points_used); ?> points</span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($order_info['order_status'] == 'Shipped' || $order_info['order_status'] == 'Delivered'): ?>
        <div class="order-detail-tracking-info">
            <span><i class="fas fa-tracking"></i> Tracking Number: <strong>PXX-<?php echo substr($order_id, -8); ?>-MY</strong></span>
            <span><i class="fas fa-truck"></i> Courier: <strong>PXX Express</strong></span>
        </div>
        <?php endif; ?>
        
        <div class="order-detail-action-buttons">
            <a href="orders_history.php" class="order-detail-btn-back">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            
            <?php if ($order_info['order_status'] == 'Processing'): ?>
            <a href="cancel_order.php?order_id=<?php echo urlencode($order_id); ?>" 
               class="order-detail-btn-cancel" 
               id="orderDetailCancelBtn">
                <i class="fas fa-times-circle"></i> Cancel Order
            </a>
            <?php endif; ?>
            
            <a href="../ViewAllProduct.php" class="order-detail-btn-reorder">
                <i class="fas fa-shopping-cart"></i> Reorder Items
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/app.js"></script>

<?php require_once '../includes/_foot.php'; ?>
