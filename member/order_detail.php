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
    header('Location: orders_history.php');
    exit();
}

// 获取订单详情（包含商品图片）
$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.product_image, p.price as original_price
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.order_number = ? AND o.user_id = ?
");
$stmt->execute([$order_number, $_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    header('Location: orders_history.php');
    exit();
}

// 计算总额
$total = 0;
$points_used = 0;
$points_earned = 0;
foreach ($items as $item) {
    $total += $item['total_price'];
    $points_used = $item['points_used'];
    $points_earned = $item['points_earned'];
}
?>

<style>
    .detail-container {
        max-width: 1000px;
        margin: 40px auto;
        background: white;
        border-radius: 24px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    /* 订单头部 */
    .order-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        color: white;
        padding: 35px 40px;
    }
    
    .order-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 700;
    }
    
    .order-header p {
        margin: 0;
        opacity: 0.8;
        font-size: 14px;
    }
    
    .order-badge {
        display: inline-block;
        padding: 6px 15px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 15px;
    }
    
    .badge-processing {
        background: #ff9800;
        color: #fff;
    }
    
    .badge-shipped {
        background: #2196f3;
        color: #fff;
    }
    
    .badge-delivered {
        background: #4caf50;
        color: #fff;
    }
    
    .badge-cancelled {
        background: #f44336;
        color: #fff;
    }
    
    .badge-paid {
        background: #4caf50;
        color: #fff;
    }
    
    .badge-pending {
        background: #ff9800;
        color: #fff;
    }
    
    /* 订单信息卡片 */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
        padding: 30px 40px;
        background: #f9f9f9;
        border-bottom: 1px solid #eee;
    }
    
    .info-card {
        background: white;
        padding: 20px;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    
    .info-card h4 {
        margin: 0 0 12px 0;
        font-size: 14px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .info-card p {
        margin: 8px 0;
        font-size: 15px;
        color: #333;
    }
    
    .info-card strong {
        color: #1a1a1a;
    }
    
    /* 商品列表 */
    .products-section {
        padding: 30px 40px;
    }
    
    .products-section h3 {
        margin: 0 0 20px 0;
        font-size: 20px;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: 10px;
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
    
    .product-image {
        width: 100px;
        height: 120px;
        background: #f5f5f5;
        border-radius: 12px;
        object-fit: cover;
    }
    
    .product-details {
        flex: 2;
        min-width: 200px;
    }
    
    .product-name {
        font-size: 16px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 8px;
    }
    
    .product-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 13px;
        color: #888;
    }
    
    .product-price {
        text-align: right;
        min-width: 120px;
    }
    
    .product-price .unit-price {
        font-size: 13px;
        color: #888;
    }
    
    .product-price .subtotal {
        font-size: 18px;
        font-weight: 700;
        color: #e60012;
    }
    
    .product-quantity {
        text-align: center;
        min-width: 80px;
    }
    
    .quantity-badge {
        background: #f0f0f0;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }
    
    /* 订单汇总 */
    .summary-section {
        background: #f9f9f9;
        padding: 25px 40px;
        border-top: 1px solid #eee;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
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
    
    .points-row {
        background: #fff8e1;
        padding: 15px 20px;
        border-radius: 12px;
        margin-top: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .points-earned {
        color: #ff9800;
        font-weight: 700;
    }
    
    .points-used {
        color: #e60012;
        font-weight: 700;
    }
    
    /* 按钮区域 */
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-top: 25px;
    }
    
    .btn-back {
        background: #666;
        color: white;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 40px;
        font-weight: 600;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-back:hover {
        background: #555;
        transform: translateY(-2px);
    }
    
    .btn-cancel {
        background: #f44336;
        color: white;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 40px;
        font-weight: 600;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-cancel:hover {
        background: #d32f2f;
        transform: translateY(-2px);
    }
    
    .btn-reorder {
        background: #1a1a1a;
        color: white;
        padding: 12px 30px;
        text-decoration: none;
        border-radius: 40px;
        font-weight: 600;
        transition: 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-reorder:hover {
        background: #e60012;
        transform: translateY(-2px);
    }
    
    .tracking-info {
        background: #e3f2fd;
        padding: 15px 20px;
        border-radius: 12px;
        margin-top: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    @media (max-width: 768px) {
        .order-header {
            padding: 25px 20px;
        }
        .info-grid {
            padding: 20px;
            gap: 15px;
        }
        .products-section {
            padding: 20px;
        }
        .summary-section {
            padding: 20px;
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
        .btn-back, .btn-cancel, .btn-reorder {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="detail-container">
    <!-- 头部 -->
    <div class="order-header">
        <h1>📋 Order Details</h1>
        <p>Review your order information below</p>
        <div>
            <span class="order-badge badge-<?php echo strtolower($items[0]['order_status']); ?>">
                <?php echo $items[0]['order_status']; ?>
            </span>
            <span class="order-badge badge-<?php echo strtolower($items[0]['payment_status']); ?>" style="margin-left: 10px;">
                <?php echo $items[0]['payment_status']; ?>
            </span>
        </div>
    </div>
    
    <!-- 订单信息网格 -->
    <div class="info-grid">
        <div class="info-card">
            <h4>📄 Order Information</h4>
            <p><strong>Order Number:</strong><br><?php echo htmlspecialchars($order_number); ?></p>
            <p><strong>Order Date:</strong><br><?php echo date('d M Y, h:i A', strtotime($items[0]['order_date'])); ?></p>
        </div>
        
        <div class="info-card">
            <h4>🚚 Shipping Information</h4>
            <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($items[0]['shipping_address'] ?: 'No address provided')); ?></p>
            <p><strong>Phone:</strong><br><?php echo htmlspecialchars($items[0]['contact_phone'] ?: 'No phone number'); ?></p>
        </div>
        
        <div class="info-card">
            <h4>💳 Payment Information</h4>
            <p><strong>Payment Method:</strong><br><?php echo htmlspecialchars($items[0]['payment_method'] ?: 'ToyyibPay'); ?></p>
            <p><strong>Payment Status:</strong><br>
                <span style="color: <?php echo $items[0]['payment_status'] == 'Paid' ? '#4caf50' : '#ff9800'; ?>">
                    <?php echo $items[0]['payment_status']; ?>
                </span>
            </p>
        </div>
    </div>
    
    <!-- 商品列表 -->
    <div class="products-section">
        <h3>🛍️ Items Ordered (<?php echo count($items); ?> item<?php echo count($items) > 1 ? 's' : ''; ?>)</h3>
        
        <?php foreach ($items as $item): ?>
        <div class="product-item">
            <img src="../images/<?php echo htmlspecialchars($item['product_image'] ?? 'default.jpg'); ?>" 
                 class="product-image" 
                 onerror="this.src='../images/default.jpg'"
                 alt="<?php echo htmlspecialchars($item['product_name']); ?>">
            
            <div class="product-details">
                <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                <div class="product-meta">
                    <span>🎨 Color: <?php echo $item['colour'] ?: 'Standard'; ?></span>
                    <span>📏 Size: <?php echo $item['selected_size'] ?: 'One Size'; ?></span>
                </div>
            </div>
            
            <div class="product-quantity">
                <span class="quantity-badge">Qty: <?php echo $item['quantity']; ?></span>
            </div>
            
            <div class="product-price">
                <div class="unit-price">RM <?php echo number_format($item['total_price'] / $item['quantity'], 2); ?> each</div>
                <div class="subtotal">RM <?php echo number_format($item['total_price'], 2); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- 订单汇总 -->
    <div class="summary-section">
        <div class="summary-row">
            <span>Subtotal</span>
            <span>RM <?php echo number_format($total, 2); ?></span>
        </div>
        
        <div class="summary-row">
            <span>Shipping Fee</span>
            <span>RM 0.00</span>
        </div>
        
        <?php if ($points_used > 0): ?>
        <div class="summary-row">
            <span>Points Discount (<?php echo number_format($points_used); ?> points)</span>
            <span>- RM <?php echo number_format($points_used / 100, 2); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="summary-row total">
            <span>Total Amount</span>
            <span>RM <?php echo number_format($total, 2); ?></span>
        </div>
        
        <!-- 积分信息 -->
        <div class="points-row">
            <div>
                <span>🏆 Points Earned from this order</span>
            </div>
            <div>
                <span class="points-earned">+<?php echo number_format($points_earned); ?> points</span>
            </div>
        </div>
        
        <?php if ($points_used > 0): ?>
        <div class="points-row" style="background: #ffebee;">
            <div>
                <span>💎 Points Used for discount</span>
            </div>
            <div>
                <span class="points-used">-<?php echo number_format($points_used); ?> points</span>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 物流跟踪信息（如果是已发货状态） -->
        <?php if ($items[0]['order_status'] == 'Shipped' || $items[0]['order_status'] == 'Delivered'): ?>
        <div class="tracking-info">
            <span>📦 Tracking Number: <strong>PXX-<?php echo substr($order_number, -8); ?>-MY</strong></span>
            <span>🚚 Courier: <strong>PXX Express</strong></span>
        </div>
        <?php endif; ?>
        
        <!-- 按钮区域 -->
        <div class="action-buttons">
            <a href="orders_history.php" class="btn-back">
                ← Back to Orders
            </a>
            
            <div style="display: flex; gap: 12px;">
                <?php if ($items[0]['order_status'] == 'Processing'): ?>
                <a href="cancel_order.php?order_number=<?php echo urlencode($order_number); ?>" 
                   class="btn-cancel" 
                   onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.')">
                    ❌ Cancel Order
                </a>
                <?php endif; ?>
                <a href="../ViewAllProduct.php" class="btn-reorder">
                    🛒 Reorder Items
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/_foot.php'; ?>