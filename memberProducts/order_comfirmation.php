<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

if (empty($order_number)) {
    header('Location: unified_cart.php');
    exit();
}

// 获取订单详情
$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.product_image, p.price as unit_price
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.order_number = ? AND o.user_id = ?
");
$stmt->execute([$order_number, $_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: unified_cart.php');
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

<div class="order-confirmation-success-page">
    <!-- 成功头部 -->
    <div class="order-confirmation-success-header">
        <div class="order-confirmation-success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>🎉 Payment Successful!</h1>
        <p>Your order has been confirmed and will be shipped soon.</p>
    </div>
    
    <!-- 订单卡片 -->
    <div class="order-confirmation-order-card">
        <div class="order-confirmation-order-header">
            <div class="order-confirmation-order-number">
                <span>Order Number</span><br>
                <?php echo htmlspecialchars($order_number); ?>
            </div>
            <div class="order-confirmation-order-date">
                <i class="far fa-calendar-alt"></i> <?php echo date('d M Y, h:i A', strtotime($order_info['order_date'])); ?>
            </div>
            <div class="order-confirmation-status-badge">
                <i class="fas fa-check-circle"></i> Paid & Confirmed
            </div>
        </div>
        
        <div class="order-confirmation-order-body">
            <!-- 订单信息 -->
            <div class="order-confirmation-info-grid">
                <div class="order-confirmation-info-item">
                    <div class="order-confirmation-info-label"><i class="fas fa-truck"></i> Shipping Address</div>
                    <div class="order-confirmation-info-value"><?php echo nl2br(htmlspecialchars($order_info['shipping_address'])); ?></div>
                </div>
                <div class="order-confirmation-info-item">
                    <div class="order-confirmation-info-label"><i class="fas fa-phone"></i> Contact Number</div>
                    <div class="order-confirmation-info-value"><?php echo htmlspecialchars($order_info['contact_phone'] ?: 'Not provided'); ?></div>
                </div>
                <div class="order-confirmation-info-item">
                    <div class="order-confirmation-info-label"><i class="fas fa-credit-card"></i> Payment Method</div>
                    <div class="order-confirmation-info-value">ToyyibPay (Online Banking)</div>
                </div>
            </div>
            
            <!-- 商品列表标题 -->
            <div class="order-confirmation-products-title">
                <i class="fas fa-box"></i> Items Ordered (<?php echo count($items); ?>)
            </div>
            
            <!-- 商品列表 -->
            <div class="order-confirmation-product-list">
                <?php foreach ($items as $item): ?>
                <div class="order-confirmation-product-item">
                    <img src="../images/<?php echo htmlspecialchars($item['product_image'] ?? 'default.jpg'); ?>" 
                         class="order-confirmation-product-img" 
                         onerror="this.src='../images/default.jpg'"
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    
                    <div class="order-confirmation-product-details">
                        <div class="order-confirmation-product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="order-confirmation-product-meta">
                            <span>📏 Size: <?php echo $item['selected_size'] ?: 'One Size'; ?></span>
                            <?php if($item['colour']): ?>
                            <span> | 🎨 Color: <?php echo $item['colour']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="order-confirmation-product-quantity">
                        <span class="order-confirmation-quantity-badge">Qty: <?php echo $item['quantity']; ?></span>
                    </div>
                    
                    <div class="order-confirmation-product-price">
                        <div class="order-confirmation-unit-price">RM <?php echo number_format($item['unit_price'], 2); ?> each</div>
                        <div class="order-confirmation-subtotal">RM <?php echo number_format($item['total_price'], 2); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- 价格汇总 -->
            <div class="order-confirmation-summary-box">
                <div class="order-confirmation-summary-row">
                    <span>Subtotal</span>
                    <span>RM <?php echo number_format($total, 2); ?></span>
                </div>
                <div class="order-confirmation-summary-row">
                    <span>Shipping Fee</span>
                    <span>FREE</span>
                </div>
                <?php if ($points_used > 0): ?>
                <div class="order-confirmation-summary-row">
                    <span>Points Discount (<?php echo number_format($points_used); ?> points)</span>
                    <span>- RM <?php echo number_format($points_used / 100, 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="order-confirmation-summary-row order-confirmation-total">
                    <span>Total Paid</span>
                    <span>RM <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <!-- 积分信息 -->
            <div class="order-confirmation-points-box">
                <div class="order-confirmation-points-earned">
                    <i class="fas fa-star"></i> Points Earned from this order
                </div>
                <div class="order-confirmation-points-value">+<?php echo number_format($points_earned); ?></div>
            </div>
            
            <?php if ($points_used > 0): ?>
            <div class="order-confirmation-points-box order-confirmation-points-used">
                <div class="order-confirmation-points-used-label">
                    <i class="fas fa-coins"></i> Points Used for discount
                </div>
                <div class="order-confirmation-points-used-value">-<?php echo number_format($points_used); ?></div>
            </div>
            <?php endif; ?>
            
            <!-- 送货提示 -->
            <div class="order-confirmation-delivery-note">
                <i class="fas fa-truck-fast" style="font-size: 24px;"></i>
                <div>
                    <strong>Estimated Delivery:</strong> 3-5 business days<br>
                    You will receive a shipping confirmation email once your order is on its way.
                </div>
            </div>
        </div>
    </div>
    
    <!-- 按钮区域 -->
    <div class="order-confirmation-action-buttons">
        <a href="orders_history.php" class="order-confirmation-btn order-confirmation-btn-primary">
            <i class="fas fa-list"></i> View My Orders
        </a>
        <a href="../ViewAllProduct.php" class="order-confirmation-btn order-confirmation-btn-secondary">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
        </a>
        <a href="../index.php" class="order-confirmation-btn order-confirmation-btn-outline">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>
</div>

<script src="../js/app.js"></script>

<?php require_once '../includes/_foot.php'; ?>
