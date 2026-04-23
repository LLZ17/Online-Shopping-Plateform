<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

$order_date = $_GET['date'] ?? '';
$user_id = $_SESSION['user_id'];

if (!$order_date) { header('Location: history.php'); exit(); }

// 查询该日期所有订单 + 用户资料
$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.product_image, p.product_id,
           u.real_name, u.phone_num, u.email
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    JOIN users u ON o.user_id = u.user_id
    WHERE DATE(o.order_date) = ? AND o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt->execute([$order_date, $user_id]);
$items = $stmt->fetchAll();

if (empty($items)) { header('Location: history.php'); exit(); }

// 计算汇总
$grand_total = 0;
$total_items = 0;
$points_used = 0;
$points_earned = 0;
$shipping_address = '';
$first_order = $items[0];

foreach ($items as $item) {
    $grand_total += $item['total_price'];
    $total_items += $item['quantity'];
    $points_used = max($points_used, $item['points_used']);
    $points_earned = max($points_earned, $item['points_earned']);
    if (empty($shipping_address) && !empty($item['shipping_address'])) {
        $shipping_address = $item['shipping_address'];
    }
}

// 计算运费和折扣
$voucher_discount = $first_order['voucher_discount_amount'] ?? 0;
$points_discount_amount = $first_order['points_discount_amount'] ?? 0;
$subtotal = $grand_total + $points_discount_amount + $voucher_discount;
$shipping_fee = ($subtotal - $points_discount_amount - $voucher_discount >= 100) ? 0 : 8.00;
?>

<style>
    .detail-container { max-width: 900px; margin: 40px auto; padding: 20px; }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #666;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 24px;
        font-size: 15px;
    }
    .back-link:hover { color: #e60012; }
    
    .detail-title { font-size: 24px; font-weight: 800; margin-bottom: 24px; color: #111; }
    
    .info-section {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
    }
    
    .section-title {
        font-size: 16px;
        font-weight: 800;
        color: #333;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f5f5f5;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .info-label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    .info-value {
        font-size: 15px;
        color: #333;
        font-weight: 600;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
    }
    .status-processing { background: #fff3e0; color: #ef6c00; }
    .status-shipped { background: #e3f2fd; color: #1565c0; }
    .status-delivered { background: #e8f5e9; color: #2e7d32; }
    
    .product-list { margin-top: 8px; }
    
    .product-item {
        display: flex;
        gap: 16px;
        padding: 16px;
        background: #fafafa;
        border-radius: 12px;
        margin-bottom: 12px;
        align-items: center;
    }
    
    .product-img {
        width: 80px;
        height: 100px;
        object-fit: cover;
        border-radius: 10px;
        background: #f0f0f0;
        flex-shrink: 0;
    }
    
    .product-details { flex: 1; }
    .product-name {
        font-weight: 700;
        font-size: 15px;
        color: #333;
        margin-bottom: 8px;
        text-decoration: none;
        display: block;
    }
    .product-name:hover { color: #e60012; }
    .product-meta {
        font-size: 13px;
        color: #666;
        line-height: 1.6;
    }
    .product-meta span {
        display: inline-block;
        background: white;
        padding: 3px 10px;
        border-radius: 6px;
        margin-right: 6px;
        margin-bottom: 4px;
        border: 1px solid #eee;
    }
    
    .product-price {
        text-align: right;
        min-width: 100px;
    }
    .price-amount {
        font-size: 18px;
        font-weight: 800;
        color: #e60012;
    }
    
    .summary-box {
        background: #fafafa;
        border-radius: 12px;
        padding: 20px;
        margin-top: 16px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 15px;
        color: #666;
    }
    .summary-row.discount { color: #2e7d32; }
    .summary-row.total {
        border-top: 2px solid #e0e0e0;
        margin-top: 8px;
        padding-top: 16px;
        font-size: 20px;
        font-weight: 800;
        color: #111;
    }
    .summary-row.total .amount { color: #e60012; font-size: 24px; }
    
    .points-box {
        display: flex;
        gap: 16px;
        margin-top: 16px;
    }
    .points-item {
        flex: 1;
        background: white;
        padding: 16px;
        border-radius: 12px;
        text-align: center;
        border: 1px solid #eee;
    }
    .points-item i {
        font-size: 24px;
        margin-bottom: 8px;
        display: block;
    }
    .points-item.earned i { color: #ff9800; }
    .points-item.used i { color: #e60012; }
    .points-label { font-size: 12px; color: #888; font-weight: 700; }
    .points-value { font-size: 20px; font-weight: 800; margin-top: 4px; }
    .points-item.earned .points-value { color: #ff9800; }
    .points-item.used .points-value { color: #e60012; }
    
    @media (max-width: 768px) {
        .info-grid { grid-template-columns: 1fr; }
        .product-item { flex-wrap: wrap; }
        .product-price { width: 100%; text-align: left; margin-top: 10px; }
        .points-box { flex-direction: column; }
    }
</style>

<div class="detail-container">
    <a href="history.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>
    
    <h1 class="detail-title">📋 Order Details</h1>
    
    <!-- 订单信息 -->
    <div class="info-section">
        <div class="section-title">
            <i class="fas fa-info-circle"></i> Order Information
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Order Date</span>
                <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($first_order['order_date'])); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Order Status</span>
                <span class="status-badge status-<?php echo strtolower($first_order['order_status']); ?>">
                    <i class="fas fa-circle" style="font-size: 8px;"></i>
                    <?php echo $first_order['order_status']; ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Payment Status</span>
                <span class="info-value" style="color: #2e7d32; font-weight: 700;">
                    <i class="fas fa-check-circle"></i> <?php echo $first_order['payment_status']; ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Payment Method</span>
                <span class="info-value"><?php echo htmlspecialchars($first_order['bank_name'] ?: 'ToyyibPay'); ?></span>
            </div>
        </div>
    </div>
    
    <!-- 收货信息 -->
    <div class="info-section">
        <div class="section-title">
            <i class="fas fa-shipping-fast"></i> Shipping Information
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Recipient</span>
                <span class="info-value"><?php echo htmlspecialchars($first_order['real_name']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Contact Number</span>
                <span class="info-value"><?php echo htmlspecialchars($first_order['phone_num']); ?></span>
            </div>
            <div class="info-item" style="grid-column: 1 / -1;">
                <span class="info-label">Shipping Address</span>
                <span class="info-value"><?php echo htmlspecialchars($shipping_address); ?></span>
            </div>
        </div>
    </div>
    
    <!-- 商品列表 -->
    <div class="info-section">
        <div class="section-title">
            <i class="fas fa-box-open"></i> Ordered Items (<?php echo count($items); ?> products, <?php echo $total_items; ?> items)
        </div>
        
        <div class="product-list">
            <?php foreach ($items as $item): 
                $img_array = explode(',', $item['product_image']);
                $img_to_show = trim($img_array[0]);
            ?>
                <div class="product-item">
                    <img src="../images/<?php echo htmlspecialchars($img_to_show); ?>" 
                         class="product-img" 
                         onerror="this.src='../images/default.jpg'">
                    
                    <div class="product-details">
                        <a href="../detail.php?id=<?php echo $item['product_id']; ?>" class="product-name">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                        </a>
                        <div class="product-meta">
                            <?php if ($item['selected_size']): ?>
                                <span>Size: <?php echo htmlspecialchars($item['selected_size']); ?></span>
                            <?php endif; ?>
                            <?php if ($item['colour']): ?>
                                <span>Color: <?php echo htmlspecialchars($item['colour']); ?></span>
                            <?php endif; ?>
                            <span>Quantity: <?php echo $item['quantity']; ?></span>
                        </div>
                    </div>
                    
                    <div class="product-price">
                        <div class="price-amount">RM <?php echo number_format($item['total_price'], 2); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- 价格汇总 -->
        <div class="summary-box">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>RM <?php echo number_format($subtotal, 2); ?></span>
            </div>
            <?php if ($points_discount_amount > 0): ?>
            <div class="summary-row discount">
                <span>Points Discount</span>
                <span>- RM <?php echo number_format($points_discount_amount, 2); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($voucher_discount > 0): ?>
            <div class="summary-row discount">
                <span>Voucher Discount</span>
                <span>- RM <?php echo number_format($voucher_discount, 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-row">
                <span>Shipping Fee</span>
                <span>
                    <?php if ($shipping_fee > 0): ?>
                        RM <?php echo number_format($shipping_fee, 2); ?>
                    <?php else: ?>
                        <span style="color: #4caf50; font-weight: 700;">FREE</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="summary-row total">
                <span>Total Paid</span>
                <span class="amount">RM <?php echo number_format($grand_total + $shipping_fee, 2); ?></span>
            </div>
        </div>
        
        <!-- 积分信息 -->
        <?php if ($points_earned > 0 || $points_used > 0): ?>
        <div class="points-box">
            <?php if ($points_earned > 0): ?>
            <div class="points-item earned">
                <i class="fas fa-star"></i>
                <div class="points-label">Points Earned</div>
                <div class="points-value">+<?php echo number_format($points_earned); ?></div>
            </div>
            <?php endif; ?>
            <?php if ($points_used > 0): ?>
            <div class="points-item used">
                <i class="fas fa-coins"></i>
                <div class="points-label">Points Used</div>
                <div class="points-value">-<?php echo number_format($points_used); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>