<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// 1. 获取产品ID并验证
$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';
if (empty($product_id)) { 
    header('Location: listing.php'); 
    exit(); 
}

// 2. 获取产品详细数据 (包含 stock 和 low_stock_threshold)
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { 
    header('Location: listing.php'); 
    exit(); 
}

// 3. 处理图片逻辑
$all_images = [];
if (!empty($product['product_image'])) {
    $all_images = array_map('trim', explode(',', $product['product_image']));
} else {
    $all_images = ['default.jpg'];
}

// 4. 库存逻辑
$stock = (int)$product['stock'];
$threshold = (int)($product['low_stock_threshold'] ?? 5);
$is_out_of_stock = ($stock <= 0);

// 5. 获取评价统计
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
    FROM reviews WHERE product_id = ?
");
$stmt->execute([$product_id]);
$stats = $stmt->fetch();
$avg_rating = round($stats['avg_rating'] ?? 0, 1);
$total_reviews = $stats['total'] ?? 0;

$sizes = !empty($product['size']) ? explode(',', $product['size']) : [];
$colours = !empty($product['colour']) ? explode(',', $product['colour']) : [];

require_once __DIR__ . '/../includes/_head.php';
?>

<style>
    .detail-page { max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }
    
    /* 图片展示区 */
    .product-container { display: flex; gap: 50px; flex-wrap: wrap; background: white; border-radius: 24px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
    .product-gallery { flex: 1; min-width: 350px; }
    
    .main-image-container { position: relative; width: 100%; aspect-ratio: 1/1; background: #f9f9f9; border-radius: 20px; overflow: hidden; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(0,0,0,0.05); }
    .main-image { max-width: 100%; max-height: 100%; transition: 0.3s; }
    
    /* 左右按钮 */
    .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.8); border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; z-index: 10; transition: 0.3s; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .nav-btn:hover { background: #e60012; color: white; }
    .prev-btn { left: 15px; }
    .next-btn { right: 15px; }

    /* 缩略图 */
    .thumbnail-list { display: flex; gap: 12px; margin-top: 15px; overflow-x: auto; padding-bottom: 5px; }
    .thumb-item { width: 75px; height: 75px; border: 2px solid transparent; border-radius: 10px; cursor: pointer; overflow: hidden; flex-shrink: 0; transition: 0.2s; }
    .thumb-item img { width: 100%; height: 100%; object-fit: cover; }
    .thumb-item.active { border-color: #e60012; }

    /* 产品信息区 */
    .product-info { flex: 1; min-width: 350px; }
    .product-title { font-size: 30px; font-weight: 800; color: #1a1a1a; margin-bottom: 15px; }
    .current-price { font-size: 38px; font-weight: 800; color: #e60012; margin: 20px 0; display: block; }
    
    /* 库存与按钮 */
    .stock-tag { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 50px; font-weight: 600; font-size: 14px; margin-bottom: 10px; }
    .in-stock-tag { background: #e8f5e9; color: #2e7d32; }
    .low-stock-tag { background: #fff3e0; color: #ef6c00; border: 1px solid #ffcc80; }
    .out-stock-tag { background: #ffebee; color: #c62828; }

    .quantity-selector { display: flex; align-items: center; gap: 15px; margin-top: 10px; }
    .qty-btn { width: 40px; height: 40px; border: 1px solid #ddd; background: white; border-radius: 10px; cursor: pointer; font-size: 20px; transition: 0.2s; }
    .qty-btn:hover:not(:disabled) { background: #f0f0f0; border-color: #999; }
    .qty-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .quantity-input { width: 60px; height: 40px; text-align: center; border: 1px solid #ddd; border-radius: 10px; font-weight: 700; font-size: 16px; -moz-appearance: textfield; }
    .quantity-input::-webkit-outer-spin-button, .quantity-input::-webkit-inner-spin-button { -webkit-appearance: none; }

    .option-btn { padding: 10px 20px; border: 1.5px solid #e0e0e0; background: white; border-radius: 12px; cursor: pointer; transition: 0.2s; font-weight: 500; }
    .option-btn.active { border-color: #e60012; color: #e60012; background: #fff5f5; }

    .add-to-cart-btn { width: 100%; background: #e60012; color: white; padding: 18px; border: none; border-radius: 15px; font-size: 20px; font-weight: 700; cursor: pointer; margin-top: 30px; transition: 0.3s; }
    .add-to-cart-btn:hover:not(:disabled) { background: #cc0010; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(230,0,18,0.2); }
    .add-to-cart-btn:disabled { background: #ccc; cursor: not-allowed; }
</style>

<div class="detail-page">
    <div class="product-container">
        <div class="product-gallery">
            <div class="main-image-container">
                <img src="../images/<?php echo htmlspecialchars($all_images[0]); ?>" class="main-image" id="mainDisplay">
                <?php if(count($all_images) > 1): ?>
                    <button class="nav-btn prev-btn" id="prevImg"><i class="fas fa-chevron-left"></i></button>
                    <button class="nav-btn next-btn" id="nextImg"><i class="fas fa-chevron-right"></i></button>
                <?php endif; ?>
            </div>
            <div class="thumbnail-list">
                <?php foreach($all_images as $idx => $img): ?>
                    <div class="thumb-item <?php echo $idx === 0 ? 'active' : ''; ?>" data-index="<?php echo $idx; ?>">
                        <img src="../images/<?php echo htmlspecialchars($img); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="rating-info">
                <span style="color:#ffc107">★ <?php echo $avg_rating; ?></span> | 
                <span style="color:#666"><?php echo $total_reviews; ?> Reviews</span> | 
                <span style="font-weight: 700; color:#333"><?php echo number_format($product['sales_count']); ?> Sold</span>
            </div>

            <span class="current-price">RM <?php echo number_format($product['price'], 2); ?></span>

            <?php if($is_out_of_stock): ?>
                <span class="stock-tag out-stock-tag"><i class="fas fa-times-circle"></i> Out of Stock</span>
            <?php elseif($stock <= $threshold): ?>
                <span class="stock-tag low-stock-tag"><i class="fas fa-exclamation-triangle"></i> Low Stock: Only <?php echo $stock; ?> left</span>
            <?php else: ?>
                <span class="stock-tag in-stock-tag"><i class="fas fa-check-circle"></i> In Stock (<?php echo $stock; ?> available)</span>
            <?php endif; ?>

            <?php if(!empty($colours)): ?>
                <div style="margin: 20px 0;">
                    <p style="font-weight: 700; margin-bottom: 10px;">Colour:</p>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <?php foreach($colours as $idx => $c): ?>
                            <button class="option-btn colour-btn" data-color="<?php echo trim($c); ?>" data-target-index="<?php echo $idx; ?>">
                                <?php echo trim($c); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selected_colour">
                </div>
            <?php endif; ?>

            <div style="margin: 25px 0;">
                <p style="font-weight: 700; margin-bottom: 10px;">Quantity:</p>
                <div class="quantity-selector">
                    <button class="qty-btn" id="minus">-</button>
                    <input type="number" id="qtyInput" value="<?php echo $is_out_of_stock ? 0 : 1; ?>" readonly class="quantity-input">
                    <button class="qty-btn" id="plus">+</button>
                </div>
            </div>

            <button class="add-to-cart-btn" id="buyBtn" <?php echo $is_out_of_stock ? 'disabled' : ''; ?>>
                <?php echo $is_out_of_stock ? 'Sold Out' : '<i class="fas fa-shopping-cart"></i> Add to Cart'; ?>
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 数据初始化
    const images = <?php echo json_encode($all_images); ?>;
    const maxStock = <?php echo $stock; ?>;
    let currentIdx = 0;

    // --- 图片切换逻辑 ---
    function updateGallery(index) {
        currentIdx = index;
        const newSrc = "../images/" + images[currentIdx];
        $('#mainDisplay').fadeOut(100, function() {
            $(this).attr('src', newSrc).fadeIn(100);
        });
        $('.thumb-item').removeClass('active');
        $('.thumb-item[data-index="'+index+'"]').addClass('active');
    }

    $('#nextImg').click(function() {
        let next = (currentIdx + 1) % images.length;
        updateGallery(next);
    });

    $('#prevImg').click(function() {
        let prev = (currentIdx - 1 + images.length) % images.length;
        updateGallery(prev);
    });

    $('.thumb-item').click(function() {
        updateGallery($(this).data('index'));
    });

    // --- 颜色联动图片 ---
    $('.colour-btn').click(function() {
        $('.colour-btn').removeClass('active');
        $(this).addClass('active');
        $('#selected_colour').val($(this).data('color'));
        
        // 假设颜色顺序和图片顺序一致 (Shopee 逻辑)
        let targetIdx = $(this).data('target-index');
        if(images[targetIdx]) {
            updateGallery(targetIdx);
        }
    });

    // --- 数量增减逻辑 (严格) ---
    $('#plus').click(function() {
        let currentVal = parseInt($('#qtyInput').val());
        if (currentVal < maxStock) {
            $('#qtyInput').val(currentVal + 1);
        } else {
            alert("Maximum available stock reached!");
        }
    });

    $('#minus').click(function() {
        let currentVal = parseInt($('#qtyInput').val());
        if (currentVal > 1) {
            $('#qtyInput').val(currentVal - 1);
        }
    });

    // --- 加购逻辑 ---
    $('#buyBtn').click(function() {
        const qty = $('#qtyInput').val();
        const colour = $('#selected_colour').val();

        if ($('.colour-btn').length > 0 && !colour) {
            alert("Please select a colour first!");
            return;
        }

        $.ajax({
            url: 'add_to_cart_process.php', // 确保你有这个处理程序
            method: 'POST',
            data: {
                product_id: '<?php echo $product_id; ?>',
                quantity: qty,
                colour: colour
            },
            success: function(res) {
                // 处理返回结果 (例如提示跳转到购物车)
                alert("Item added to cart successfully!");
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>