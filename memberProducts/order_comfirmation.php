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

<style>
    .success-page {
        max-width: 1000px;
        margin: 50px auto;
        padding: 0 20px;
    }
    
    /* 成功图标 */
    .success-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .success-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #4caf50, #45a049);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
        animation: pulse 0.5s ease;
    }
    
    .success-icon i {
        font-size: 50px;
        color: white;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.8); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .success-header h1 {
        font-size: 32px;
        color: #333;
        margin-bottom: 10px;
    }
    
    .success-header p {
        color: #666;
        font-size: 16px;
    }
    
    /* 订单卡片 */
    .order-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    
    .order-header {
        background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
        color: white;
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .order-number {
        font-size: 20px;
        font-weight: 700;
    }
    
    .order-number span {
        font-size: 14px;
        font-weight: normal;
        opacity: 0.8;
    }
    
    .order-date {
        font-size: 14px;
        opacity: 0.8;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #4caf50;
        padding: 6px 15px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
    }
    
    /* 订单内容 */
    .order-body {
        padding: 30px;
    }
    
    /* 订单信息网格 */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 1px solid #eee;
    }
    
    .info-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
    }
    
    .info-label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
    }
    
    .info-value {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        word-break: break-word;
    }
    
    /* 商品列表 */
    .products-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e60012;
        display: inline-block;
    }
    
    .product-list {
        margin: 20px 0;
    }
    
    .product-item {
        display: flex;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px solid #f0f0f0;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .product-item:last-child {
        border-bottom: none;
    }
    
    .product-img {
        width: 80px;
        height: 100px;
        object-fit: cover;
        border-radius: 12px;
        background: #f5f5f5;
    }
    
    .product-details {
        flex: 2;
        min-width: 180px;
    }
    
    .product-name {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin-bottom: 6px;
    }
    
    .product-meta {
        font-size: 13px;
        color: #888;
    }
    
    .product-quantity {
        min-width: 80px;
        text-align: center;
    }
    
    .quantity-badge {
        background: #f0f0f0;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }
    
    .product-price {
        min-width: 100px;
        text-align: right;
    }
    
    .unit-price {
        font-size: 12px;
        color: #888;
    }
    
    .subtotal {
        font-size: 16px;
        font-weight: 700;
        color: #e60012;
    }
    
    /* 价格汇总 */
    .summary-box {
        background: #f8f9fa;
        border-radius: 16px;
        padding: 25px;
        margin-top: 25px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        font-size: 15px;
    }
    
    .summary-row.total {
        border-top: 2px solid #e0e0e0;
        margin-top: 10px;
        padding-top: 20px;
        font-size: 22px;
        font-weight: 800;
        color: #e60012;
    }
    
    .points-box {
        background: linear-gradient(135deg, #fff8e1, #ffecb3);
        border-radius: 12px;
        padding: 15px 20px;
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .points-earned {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #ff9800;
        font-weight: 700;
    }
    
    .points-used {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #e60012;
        font-weight: 700;
    }
    
    /* 按钮区域 */
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 30px;
    }
    
    .btn {
        padding: 14px 32px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: #e60012;
        color: white;
    }
    
    .btn-primary:hover {
        background: #cc0010;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(230,0,18,0.3);
    }
    
    .btn-secondary {
        background: #f0f0f0;
        color: #333;
    }
    
    .btn-secondary:hover {
        background: #e0e0e0;
        transform: translateY(-2px);
    }
    
    .btn-outline {
        background: transparent;
        border: 2px solid #e60012;
        color: #e60012;
    }
    
    .btn-outline:hover {
        background: #e60012;
        color: white;
    }
    
    /* 送货信息 */
    .delivery-note {
        background: #e3f2fd;
        border-radius: 12px;
        padding: 15px 20px;
        margin-top: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        color: #1565c0;
    }
    
    @media (max-width: 768px) {
        .order-header {
            flex-direction: column;
            text-align: center;
        }
        .product-item {
            flex-direction: column;
            text-align: center;
        }
        .product-price {
            text-align: center;
        }
        .action-buttons {
            flex-direction: column;
        }
        .btn {
            justify-content: center;
        }
    }
</style>

<div class="success-page">
    <!-- 成功头部 -->
    <div class="success-header">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>🎉 Payment Successful!</h1>
        <p>Your order has been confirmed and will be shipped soon.</p>
    </div>
    
    <!-- 订单卡片 -->
    <div class="order-card">
        <div class="order-header">
            <div class="order-number">
                <span>Order Number</span><br>
                <?php echo htmlspecialchars($order_number); ?>
            </div>
            <div class="order-date">
                <i class="far fa-calendar-alt"></i> <?php echo date('d M Y, h:i A', strtotime($order_info['order_date'])); ?>
            </div>
            <div class="status-badge">
                <i class="fas fa-check-circle"></i> Paid & Confirmed
            </div>
        </div>
        
        <div class="order-body">
            <!-- 订单信息 -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-truck"></i> Shipping Address</div>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($order_info['shipping_address'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-phone"></i> Contact Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($order_info['contact_phone'] ?: 'Not provided'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-credit-card"></i> Payment Method</div>
                    <div class="info-value">ToyyibPay (Online Banking)</div>
                </div>
            </div>
            
            <!-- 商品列表标题 -->
            <div class="products-title">
                <i class="fas fa-box"></i> Items Ordered (<?php echo count($items); ?>)
            </div>
            
            <!-- 商品列表 -->
            <div class="product-list">
                <?php foreach ($items as $item): ?>
                <div class="product-item">
                    <img src="../images/<?php echo htmlspecialchars($item['product_image'] ?? 'default.jpg'); ?>" 
                         class="product-img" 
                         onerror="this.src='../images/default.jpg'"
                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    
                    <div class="product-details">
                        <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="product-meta">
                            <span>📏 Size: <?php echo $item['selected_size'] ?: 'One Size'; ?></span>
                            <?php if($item['colour']): ?>
                            <span> | 🎨 Color: <?php echo $item['colour']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="product-quantity">
                        <span class="quantity-badge">Qty: <?php echo $item['quantity']; ?></span>
                    </div>
                    
                    <div class="product-price">
                        <div class="unit-price">RM <?php echo number_format($item['unit_price'], 2); ?> each</div>
                        <div class="subtotal">RM <?php echo number_format($item['total_price'], 2); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- 价格汇总 -->
            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>RM <?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee</span>
                    <span>FREE</span>
                </div>
                <?php if ($points_used > 0): ?>
                <div class="summary-row">
                    <span>Points Discount (<?php echo number_format($points_used); ?> points)</span>
                    <span>- RM <?php echo number_format($points_used / 100, 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Total Paid</span>
                    <span>RM <?php echo number_format($total, 2); ?></span>
                </div>
            </div>
            
            <!-- 积分信息 -->
            <div class="points-box">
                <div class="points-earned">
                    <i class="fas fa-star"></i> Points Earned from this order
                </div>
                <div style="font-weight: 800; font-size: 20px; color: #ff9800;">+<?php echo number_format($points_earned); ?></div>
            </div>
            
            <?php if ($points_used > 0): ?>
            <div class="points-box" style="background: #ffebee;">
                <div class="points-used">
                    <i class="fas fa-coins"></i> Points Used for discount
                </div>
                <div style="font-weight: 800; font-size: 20px; color: #e60012;">-<?php echo number_format($points_used); ?></div>
            </div>
            <?php endif; ?>
            
            <!-- 送货提示 -->
            <div class="delivery-note">
                <i class="fas fa-truck-fast" style="font-size: 24px;"></i>
                <div>
                    <strong>Estimated Delivery:</strong> 3-5 business days<br>
                    You will receive a shipping confirmation email once your order is on its way.
                </div>
            </div>
        </div>
    </div>
    
    <!-- 按钮区域 -->
    <div class="action-buttons">
        <a href="orders_history.php" class="btn btn-primary">
            <i class="fas fa-list"></i> View My Orders
        </a>
        <a href="../ViewAllProduct.php" class="btn btn-secondary">
            <i class="fas fa-shopping-bag"></i> Continue Shopping
        </a>
        <a href="../index.php" class="btn btn-outline">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>
</div>

<?php require_once '../includes/_foot.php'; ?>