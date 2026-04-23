<?php
session_start();

// 1. 使用 DIRECTORY_SEPARATOR 定义绝对路径
$pxx_root = dirname(__DIR__); 
$pxx_db_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php';
$pxx_head_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_head.php';
$pxx_foot_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_foot.php';

require_once $pxx_db_file;

// 2. 检查 Session：如果未登录则跳转
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../login.php'); 
    exit(); 
}
$user_id = $_SESSION['user_id'];

// 3. 获取订单数据：每个产品占据一行（不合并卡片）
$stmt = $pdo->prepare("
    SELECT 
        o.order_id, o.order_date, o.order_status, o.total_price, o.quantity, 
        o.selected_size, o.colour, p.name as product_name, p.product_image, p.product_id
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.user_id = ? 
    ORDER BY o.order_date DESC, o.order_id DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// 🌟 修复：先定义好所有路径变量，再 require
require_once $pxx_head_file;
?>

<style>
    .history-container { max-width: 900px; margin: 40px auto; padding: 0 20px; font-family: Arial, sans-serif; }
    .history-title { font-size: 24px; font-weight: bold; margin-bottom: 25px; color: #333; }
    
    .order-row-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid #eaeaea;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .order-meta-info { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 13px; color: #888; }
    .status-label { color: #ef6c00; font-weight: bold; background: #fff3e0; padding: 2px 10px; border-radius: 4px; }

    .product-box { display: flex; gap: 15px; align-items: center; }
    
    .prod-img { 
        width: 80px; 
        height: 100px; 
        object-fit: cover; 
        border-radius: 8px; 
        border: 1px solid #eee; 
        background-color: #f0f0f0;
        display: block;
        flex-shrink: 0;
    }
    
    .prod-desc { flex: 1; }
    .prod-title { font-size: 16px; font-weight: bold; color: #222; text-decoration: none; display: block; margin-bottom: 5px; }
    .attribute-tags { display: flex; gap: 8px; }
    .tag { background: #f5f5f5; color: #666; padding: 2px 8px; border-radius: 4px; font-size: 12px; }

    .price-box { text-align: right; }
    .price-amount { color: #e60012; font-size: 18px; font-weight: 800; }
    
    .action-bar { display: flex; justify-content: flex-end; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #f9f9f9; }
    .btn { padding: 8px 18px; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; cursor: pointer; }
    .btn-review { background: #e60012; color: #fff; border: none; }
    .btn-detail { border: 1px solid #ddd; color: #666; background: #fff; }
</style>

<div class="history-container">
    <h1 class="history-title">📦 My Orders</h1>

    <?php if (empty($orders)): ?>
        <div style="text-align:center; padding:100px 0; color:#ccc;">No orders found.</div>
    <?php else: ?>
        <?php foreach ($orders as $o): 
            $img_data = !empty($o['product_image']) ? explode(',', $o['product_image']) : [];
            $main_img = !empty($img_data[0]) ? trim($img_data[0]) : 'default.jpg';
        ?>
            <div class="order-row-card">
                <div class="order-meta-info">
                    <span>Date: <?php echo date('d M Y', strtotime($o['order_date'])); ?></span>
                    <span class="status-label"><?php echo htmlspecialchars($o['order_status']); ?></span>
                </div>

                <div class="product-box">
                    <img src="../images/<?php echo htmlspecialchars($main_img); ?>" 
                         class="prod-img" 
                         alt="<?php echo htmlspecialchars($o['product_name']); ?>"
                         onerror="this.onerror=null; this.src='../images/default.jpg';">
                    
                    <div class="prod-desc">
                        <a href="../detail.php?id=<?php echo $o['product_id']; ?>" class="prod-title">
                            <?php echo htmlspecialchars($o['product_name']); ?>
                        </a>
                        <div class="attribute-tags">
                            <span class="tag">Size: <?php echo htmlspecialchars($o['selected_size'] ?: 'Free'); ?></span>
                            <span class="tag">Color: <?php echo htmlspecialchars($o['colour'] ?: 'N/A'); ?></span>
                            <span class="tag">Qty: <?php echo $o['quantity']; ?></span>
                        </div>
                    </div>

                    <div class="price-box">
                        <div class="price-amount">RM <?php echo number_format($o['total_price'], 2); ?></div>
                        <div style="font-size:12px; color:#999;">1 item(s)</div>
                    </div>
                </div>

                <div class="action-bar">
                    <a href="order_details.php?id=<?php echo $o['order_id']; ?>" class="btn btn-detail">Details</a>
                    <a href="MemberReview/Reviewsubmit.php?order_id=<?php echo $o['order_id']; ?>&product_id=<?php echo $o['product_id']; ?>" class="btn btn-review">Write Review</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once $pxx_foot_file; ?>