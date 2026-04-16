<?php
session_start();

// 1. 获取 ID：支持字符串 ID (如 M1001)
$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($product_id)) { 
    header('Location: listing.php'); 
    exit(); 
}

require_once '../includes/db.php';
require_once '../includes/_head.php';

// 2. 获取产品基本信息 (包含你要求的所有字段)
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { 
    header('Location: listing.php'); 
    exit(); 
}

// --- 🔥 新增功能：检测当前购物车中指定商品的数量 ---
$existing_cart_qty = 0;
if (isset($_SESSION['user_id'])) {
    $stmt_cart = $pdo->prepare("SELECT SUM(quantity) as total_qty FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt_cart->execute([$_SESSION['user_id'], $product_id]);
    $cart_res = $stmt_cart->fetch();
    $existing_cart_qty = (int)($cart_res['total_qty'] ?? 0);
}
// --- 结束新增 ---

// 3. 获取评价详情
$stmt = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll();

// 4. 统计评分
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total, AVG(rating) as avg_rating 
    FROM reviews WHERE product_id = ?
");
$stmt->execute([$product_id]);
$stats = $stmt->fetch();
$avg_rating = round($stats['avg_rating'] ?? 0, 1);
$total_reviews = $stats['total'] ?? 0;

// 解析尺寸和颜色 (对应你的 size 和 colour 字段)
$sizes = !empty($product['size']) ? explode(',', $product['size']) : [];
$colours = !empty($product['colour']) ? explode(',', $product['colour']) : [];
?>

<style>
    .detail-container { max-width: 1200px; margin: 40px auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .product-detail { display: flex; gap: 40px; padding: 40px; flex-wrap: wrap; }
    .product-gallery { flex: 1; min-width: 350px; }
    .main-image { width: 100%; border-radius: 15px; border: 1px solid #eee; }
    
    .product-info { flex: 1.2; min-width: 350px; }
    .badge-cat { color: #e60012; font-weight: 800; font-size: 13px; text-transform: uppercase; margin-bottom: 10px; display: block; }
    .product-name { font-size: 32px; font-weight: 800; color: #1a1a1a; margin-bottom: 10px; }
    
    /* 新增：销量和评价样式 */
    .meta-row { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
    .sales-count { color: #666; font-size: 14px; border-left: 1px solid #ddd; padding-left: 20px; }
    
    .price-tag { font-size: 36px; color: #e60012; font-weight: 800; margin: 20px 0; }
    
    /* 新增：Status 标签样式 (对应你的 enum) */
    .status-pill { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 15px; }
    .status-In-stock { background: #e8f5e9; color: #2e7d32; }
    .status-Low-in-stock { background: #fff3e0; color: #ef6c00; }
    .status-Out-of-stock { background: #ffebee; color: #c62828; }

    .option-group { margin: 20px 0; }
    .option-label { font-weight: 700; margin-bottom: 10px; display: block; color: #444; }
    .btn-opt { padding: 10px 18px; border: 1px solid #ddd; background: white; border-radius: 8px; cursor: pointer; transition: 0.2s; font-weight: 600; }
    .btn-opt.active { background: #1a1a1a; color: white; border-color: #1a1a1a; }
    
    .btn-add { width: 100%; background: #e60012; color: white; padding: 18px; border: none; border-radius: 12px; font-size: 20px; font-weight: 800; cursor: pointer; margin: 25px 0; transition: 0.3s; }
    .btn-add:hover { background: #cc0010; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(230,0,18,0.3); }
    
    .desc-box { background: #fdfdfd; padding: 20px; border-radius: 10px; border: 1px solid #f0f0f0; margin-top: 30px; }
    .desc-box h4 { margin-bottom: 10px; color: #333; }
</style>

<div class="detail-container">
    <div class="product-detail">
        <div class="product-gallery">
            <img src="../images/<?php echo htmlspecialchars($product['product_image'] ?: 'default.jpg'); ?>" 
                 class="main-image" onerror="this.src='../images/default.jpg'">
        </div>

        <div class="product-info">
            <span class="badge-cat">Men's Collection</span>
            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="meta-row">
                <div style="color: #ff9800;">
                    ★ <?php echo $avg_rating; ?> <span style="color:#888; font-size: 13px;">(<?php echo $total_reviews; ?> Reviews)</span>
                </div>
                <div class="sales-count">
                    <strong><?php echo number_format($product['sales_count'] ?? 0); ?></strong> Sold
                </div>
            </div>

            <div class="price-tag">RM <?php echo number_format($product['price'], 2); ?></div>

            <?php if ($product['status']): ?>
                <div class="status-pill status-<?php echo str_replace(' ', '-', $product['status']); ?>">
                    ● <?php echo $product['status']; ?>
                </div>
            <?php endif; ?>

            <div style="color: #666; font-size: 14px; margin-bottom: 20px;">
                <i class="fas fa-cubes"></i> Stock: <?php echo $product['stock']; ?> pieces available
            </div>

            <?php if(!empty($colours)): ?>
            <div class="option-group">
                <span class="option-label">Select Colour:</span>
                <div style="display: flex; gap: 10px;">
                    <?php foreach($colours as $c): ?>
                        <button class="btn-opt col-btn" data-val="<?php echo trim($c); ?>"><?php echo trim($c); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected_colour">
            </div>
            <?php endif; ?>

            <?php if(!empty($sizes)): ?>
            <div class="option-group">
                <span class="option-label">Select Size:</span>
                <div style="display: flex; gap: 10px;">
                    <?php foreach($sizes as $s): ?>
                        <button class="btn-opt size-btn" data-val="<?php echo trim($s); ?>"><?php echo trim($s); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected_size">
            </div>
            <?php endif; ?>

            <div class="option-group">
                <span class="option-label">Quantity:</span>
                <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" 
                       style="width: 100px; padding: 12px; border-radius: 8px; border: 1px solid #ddd;">
            </div>

            <button class="btn-add-cart" id="addToCartBtn">🛒 Add to Cart</button>
                <?php echo $product['stock'] > 0 ? 'ADD TO CART' : 'OUT OF STOCK'; ?>
            </button>

            <div class="desc-box">
                <h4>Product Description</h4>
                <p style="line-height: 1.6; color: #555;">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// 选项点击逻辑
$('.size-btn').click(function(){
    $('.size-btn').removeClass('active');
    $(this).addClass('active');
    $('#selected_size').val($(this).data('val'));
});

$('.col-btn').click(function(){
    $('.col-btn').removeClass('active');
    $(this).addClass('active');
    $('#selected_colour').val($(this).data('val'));
});

function addToCart(){
    // --- 🔥 购买数量验证功能 ---
    let enteredQty = parseInt($('#quantity').val()); // 当前页面拟购买数量
    let cartQty = <?php echo $existing_cart_qty; ?>; // 购物车已存数量
    let totalStock = <?php echo (int)$product['stock']; ?>; // 数据库库存总量

    if ((enteredQty + cartQty) > totalStock) {
        alert("Action Failed: You already have " + cartQty + " items in your cart. Adding " + enteredQty + " more would exceed the total available stock of " + totalStock + ".");
        return; // 拦截请求
    }
    // --- 结束验证 ---

    let size = $('#selected_size').val();
    let color = $('#selected_colour').val();
    
    // 简单验证
    <?php if(!empty($sizes)): ?>
    if(!size) { alert('Please select a size'); return; }
    <?php endif; ?>

    $.ajax({
        url: 'add.php',
        method: 'POST',
        data: { 
            product_id: '<?php echo $product_id; ?>', 
            quantity: enteredQty, 
            selected_size: size,
            colour: color
        },
        dataType: 'json',
        success: function(res){
            if(res.success) {
                if(confirm('Added! View cart?')) window.location.href = 'unified_cart.php';
            } else {
                alert(res.error);
            }
        }
    });
}
</script>

<?php require_once '../includes/_foot.php'; ?>
