<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

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

<style>
    .detail-container {
        max-width: 1000px;
        margin: 50px auto;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .order-header {
        background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
        color: white;
        padding: 30px;
    }
    
    .order-header h1 {
        margin: 0 0 10px 0;
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .badge-group {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .order-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-delivered { background: #4caf50; color: white; }
    .badge-paid { background: #4caf50; color: white; }
    .badge-processing { background: #ff9800; color: white; }
    .badge-pending { background: #ff9800; color: white; }
    .badge-shipped { background: #2196f3; color: white; }
    .badge-cancelled { background: #f44336; color: white; }
    
    .order-body {
        padding: 30px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .info-card {
        background: #f8f9fa;
        padding: 18px;
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
        font-size: 15px;
        font-weight: 600;
        color: #333;
        word-break: break-word;
    }
    
    .products-title {
        font-size: 18px;
        font-weight: 700;
        margin: 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e60012;
        display: inline-block;
    }
    
    .product-item {
        display: flex;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px solid #eee;
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
        border-radius: 10px;
        background: #f5f5f5;
    }
    
    .product-details {
        flex: 2;
        min-width: 180px;
    }
    
    .product-name {
        font-weight: 700;
        margin-bottom: 5px;
        color: #1a1a1a;
    }
    
    .product-meta {
        font-size: 13px;
        color: #888;
    }
    
    .product-quantity {
        min-width: 80px;
        text-align: center;
        font-weight: 600;
    }
    
    .product-price {
        min-width: 120px;
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
    
    .summary-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
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
        padding-top: 15px;
        font-size: 20px;
        font-weight: 800;
        color: #e60012;
    }
    
    .points-row {
        background: #fff8e1;
        padding: 15px;
        border-radius: 10px;
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
        <h1><i class="fas fa-receipt"></i> Order Details</h1>
        <p style="margin: 0; opacity: 0.8; font-size: 14px;">Review your order information below</p>
        <div class="badge-group">
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
    
    <!-- 订单信息网格 -->
    <div class="order-body">
        <div class="info-grid">
            <div class="info-card">
                <div class="info-label"><i class="fas fa-hashtag"></i> Order ID</div>
                <div class="info-value"><?php echo htmlspecialchars($order_id); ?></div>
            </div>
            <div class="info-card">
                <div class="info-label"><i class="far fa-calendar-alt"></i> Order Date</div>
                <div class="info-value"><?php echo date('d M Y, h:i A', strtotime($order_info['order_date'])); ?></div>
            </div>
            <div class="info-card">
                <div class="info-label"><i class="fas fa-truck"></i> Shipping Address</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($order_info['shipping_address'] ?: 'No address provided')); ?></div>
            </div>
        </div>
        
        <!-- 商品列表 -->
        <div class="products-title">
            <i class="fas fa-box"></i> Items Ordered (<?php echo count($items); ?> item<?php echo count($items) > 1 ? 's' : ''; ?>)
        </div>
        
        <?php foreach ($items as $item): ?>
        <div class="product-item">
            <img src="../images/<?php echo htmlspecialchars($item['product_image'] ?? 'default.jpg'); ?>" 
                 class="product-img" 
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
                <div class="unit-price">RM <?php echo number_format($item['unit_price'], 2); ?> each</div>
                <div class="subtotal">RM <?php echo number_format($item['total_price'], 2); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- 订单汇总 -->
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
                <span>Total Amount</span>
                <span>RM <?php echo number_format($total, 2); ?></span>
            </div>
        </div>
        
        <!-- 积分信息 -->
        <div class="points-row">
            <div>
                <span><i class="fas fa-star" style="color: #ff9800;"></i> Points Earned from this order</span>
            </div>
            <div>
                <span class="points-earned">+<?php echo number_format($points_earned); ?> points</span>
            </div>
        </div>
        
        <?php if ($points_used > 0): ?>
        <div class="points-row" style="background: #ffebee;">
            <div>
                <span><i class="fas fa-coins" style="color: #e60012;"></i> Points Used for discount</span>
            </div>
            <div>
                <span class="points-used">-<?php echo number_format($points_used); ?> points</span>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 物流跟踪信息（如果是已发货状态） -->
        <?php if ($order_info['order_status'] == 'Shipped' || $order_info['order_status'] == 'Delivered'): ?>
        <div class="tracking-info">
            <span><i class="fas fa-tracking"></i> Tracking Number: <strong>PXX-<?php echo substr($order_id, -8); ?>-MY</strong></span>
            <span><i class="fas fa-truck"></i> Courier: <strong>PXX Express</strong></span>
        </div>
        <?php endif; ?>
        
        <!-- 按钮区域 -->
        <div class="action-buttons">
            <a href="orders_history.php" class="btn-back" id="backToOrdersBtn">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            
            <?php if ($order_info['order_status'] == 'Processing'): ?>
            <a href="cancel_order.php?order_id=<?php echo urlencode($order_id); ?>" 
               class="btn-cancel" 
               id="cancelOrderBtn">
                <i class="fas fa-times-circle"></i> Cancel Order
            </a>
            <?php endif; ?>
            
            <a href="../ViewAllProduct.php" class="btn-reorder" id="reorderBtn">
                <i class="fas fa-shopping-cart"></i> Reorder Items
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 取消订单按钮 - 确认框（无 inline onclick）
    $('#cancelOrderBtn').on('click', function(e) {
        if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});
</script>

<?php require_once '../includes/_foot.php'; ?>
