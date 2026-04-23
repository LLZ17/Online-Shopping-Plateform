<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_group = isset($_GET['order_group']) ? $_GET['order_group'] : '';

if (empty($order_group)) {
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE user_id = ? AND payment_status = 'Paid'
        ORDER BY order_date DESC 
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $latest = $stmt->fetch();
    
    if (!$latest) {
        header('Location: unified_cart.php');
        exit();
    }
    
    $order_date = $latest['order_date'];
} else {
    $date_str = substr($order_group, 3, 4) . '-' . substr($order_group, 7, 2) . '-' . substr($order_group, 9, 2);
    $order_date = $date_str;
}

$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.product_image, p.price as unit_price
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.user_id = ? AND DATE(o.order_date) = DATE(?)
    ORDER BY o.order_id DESC
");
$stmt->execute([$_SESSION['user_id'], $order_date]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: unified_cart.php');
    exit();
}

$total = 0;
$points_used = 0;
$points_earned = 0;
$shipping_address = '';
foreach ($items as $item) {
    $total += $item['total_price'];
    $points_used = max($points_used, $item['points_used']);
    $points_earned = max($points_earned, $item['points_earned']);
    if (empty($shipping_address)) {
        $shipping_address = $item['shipping_address'];
    }
}
$order_info = $items[0];
?>

<style>
    .order-confirmation-success-page { max-width: 900px; margin: 40px auto; padding: 20px; font-family: 'Poppins', sans-serif; }
    .order-confirmation-success-header {
        background: linear-gradient(135deg, #4caf50, #45a049);
        padding: 50px;
        text-align: center;
        color: white;
        border-radius: 24px;
        margin-bottom: 30px;
    }
    .order-confirmation-success-icon {
        width: 100px; height: 100px; background: rgba(255,255,255,0.2);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
    }
    .order-confirmation-success-icon i { font-size: 50px; }
    .order-confirmation-order-card {
        background: white; border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        overflow: hidden; margin-bottom: 30px;
    }
    .order-confirmation-order-header {
        background: #fafafa; padding: 24px;
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #eee;
    }
    .order-confirmation-order-number { font-size: 18px; font-weight: 800; color: #333; }
    .order-confirmation-order-number span { font-size: 12px; color: #888; font-weight: 400; }
    .order-confirmation-status-badge {
        background: #e8f5e9; color: #2e7d32;
        padding: 8px 16px; border-radius: 20px;
        font-size: 14px; font-weight: 700;
    }
    .order-confirmation-order-body { padding: 30px; }
    .order-confirmation-info-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px; margin-bottom: 30px;
    }
    .order-confirmation-info-item {
        background: #f8f9fa; padding: 20px;
        border-radius: 12px;
    }
    .order-confirmation-info-label {
        font-size: 12px; color: #888;
        text-transform: uppercase; letter-spacing: 1px;
        font-weight: 700; margin-bottom: 8px;
    }
    .order-confirmation-info-value { font-size: 15px; color: #333; line-height: 1.5; }
    .order-confirmation-products-title {
        font-size: 18px; font-weight: 800;
        margin-bottom: 20px; padding-bottom: 10px;
        border-bottom: 2px solid #e60012; display: inline-block;
    }
    .order-confirmation-product-item {
        display: flex; gap: 20px; padding: 20px 0;
        border-bottom: 1px solid #f0f0f0; align-items: center;
    }
    .order-confirmation-product-img {
        width: 80px; height: 100px; object-fit: cover;
        border-radius: 10px; background: #f5f5f5;
    }
    .order-confirmation-product-details { flex: 1; }
    .order-confirmation-product-name { font-weight: 700; font-size: 15px; margin-bottom: 8px; }
    .order-confirmation-product-meta { font-size: 13px; color: #888; }
    .order-confirmation-product-meta span {
        display: inline-block; background: #f5f5f5;
        padding: 3px 10px; border-radius: 6px; margin-right: 6px;
    }
    .order-confirmation-product-price { text-align: right; min-width: 120px; }
    .order-confirmation-unit-price { font-size: 13px; color: #888; }
    .order-confirmation-subtotal { font-size: 18px; font-weight: 800; color: #e60012; }
    .order-confirmation-summary-box {
        background: #f8f9fa; border-radius: 16px;
        padding: 25px; margin-top: 25px;
    }
    .order-confirmation-summary-row {
        display: flex; justify-content: space-between;
        padding: 12px 0; font-size: 16px;
    }
    .order-confirmation-summary-row.order-confirmation-total {
        border-top: 2px solid #e0e0e0; margin-top: 10px;
        padding-top: 15px; font-size: 22px; font-weight: 800; color: #e60012;
    }
    .order-confirmation-points-box {
        background: #fff8e1; padding: 20px;
        border-radius: 12px; margin-top: 20px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .order-confirmation-points-earned { font-weight: 700; color: #ff9800; }
    .order-confirmation-points-value { font-size: 24px; font-weight: 800; color: #ff9800; }
    .order-confirmation-action-buttons {
        display: flex; gap: 15px; justify-content: center;
        flex-wrap: wrap; margin-top: 30px;
    }
    .order-confirmation-btn {
        padding: 14px 32px; border-radius: 40px;
        text-decoration: none; font-weight: 700;
        display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.3s;
    }
    .order-confirmation-btn-primary { background: #e60012; color: white; }
    .order-confirmation-btn-secondary { background: #f0f0f0; color: #333; }
    .order-confirmation-btn-outline { border: 2px solid #e60012; color: #e60012; }
</style>

<div class="order-confirmation-success-page">
    <div class="order-confirmation-success-header">
        <div class="order-confirmation-success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>🎉 Payment Successful!</h1>
        <p>Your order has been confirmed and will be shipped soon.</p>
    </div>
    
    <div class="order-confirmation-order-card">
        <div class="order-confirmation-order-header">
            <div class="order-confirmation-order-number">
                <span>Order Date</span><br>
                <?php echo date('d M Y, h:i A', strtotime($order_info['order_date'])); ?>
            </div>
            <div class="order-confirmation-status-badge">
                <i class="fas fa-check-circle"></i> Paid & Confirmed
            </div>
        </div>
        
        <div class="order-confirmation-order-body">
            <div class="order-confirmation-info-grid">
                <div class="order-confirmation-info-item">
                    <div class="order-confirmation-info-label">
                        <i class="fas fa-truck"></i> Shipping Address
                    </div>
                    <div class="order-confirmation-info-value">
                        <?php echo nl2br(htmlspecialchars($shipping_address)); ?>
                    </div>
                </div>
                <div class="order-confirmation-info-item">
                    <div class="order-confirmation-info-label">
                        <i class="fas fa-credit-card"></i> Payment Method
                    </div>
                    <div class="order-confirmation-info-value">ToyyibPay (Online Banking)</div>
                </div>
            </div>
            
            <div class="order-confirmation-products-title">
                <i class="fas fa-box"></i> Items Ordered (<?php echo count($items); ?>)
            </div>
            
            <div class="order-confirmation-product-list">
                <?php foreach ($items as $item): 
                    $img_array = explode(',', $item['product_image']);
                    $img_to_show = trim($img_array[0]);
                ?>
                <div class="order-confirmation-product-item">
                    <img src="../images/<?php echo htmlspecialchars($img_to_show); ?>" 
                         class="order-confirmation-product-img" 
                         onerror="this.src='../images/default.jpg'"
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    
                    <div class="order-confirmation-product-details">
                        <div class="order-confirmation-product-name">
                            <?php echo htmlspecialchars($item['product_name']); ?>
                        </div>
                        <div class="order-confirmation-product-meta">
                            <?php if($item['selected_size']): ?>
                            <span>Size: <?php echo htmlspecialchars($item['selected_size']); ?></span>
                            <?php endif; ?>
                            <?php if($item['colour']): ?>
                            <span>Color: <?php echo htmlspecialchars($item['colour']); ?></span>
                            <?php endif; ?>
                            <span>Qty: <?php echo $item['quantity']; ?></span>
                        </div>
                    </div>
                    
                    <div class="order-confirmation-product-price">
                        <div class="order-confirmation-unit-price">
                            RM <?php echo number_format($item['unit_price'], 2); ?> each
                        </div>
                        <div class="order-confirmation-subtotal">
                            RM <?php echo number_format($item['total_price'], 2); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-confirmation-summary-box">
                <div class="order-confirmation-summary-row">
                    <span>Subtotal</span>
                    <span>RM <?php echo number_format($total, 2); ?></span>
                </div>
                <?php if ($points_used > 0): ?>
                <?php $points_discount_amount = $points_used / 100; ?>
                <div class="order-confirmation-summary-row">
                    <span>Points Discount (<?php echo number_format($points_used); ?> points)</span>
                    <span>- RM <?php echo number_format($points_discount_amount, 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="order-confirmation-summary-row">
                    <span>Shipping Fee</span>
                    <span>FREE</span>
                </div>
                <div class="order-confirmation-summary-row order-confirmation-total">
                    <span>Total Paid</span>
                    <span>RM <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <?php if ($points_earned > 0): ?>
            <div class="order-confirmation-points-box">
                <div class="order-confirmation-points-earned">
                    <i class="fas fa-star"></i> Points Earned
                </div>
                <div class="order-confirmation-points-value">+<?php echo number_format($points_earned); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
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

<?php require_once '../includes/_foot.php'; ?>