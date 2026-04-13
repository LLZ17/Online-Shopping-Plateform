<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) { 
    header('Location: listing.php'); 
    exit(); 
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { 
    header('Location: listing.php'); 
    exit(); 
}

// 获取评价
$stmt = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll();

// 评分统计
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

// 解析尺码（支持 S,M,L,XL 或 28,30,32,34 格式）
$sizes = [];
if (!empty($product['size'])) {
    $sizes = explode(',', $product['size']);
}
?>

<style>
    /* 全局样式 */
    .product-detail-page {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px;
    }
    
    /* 返回按钮 */
    .back-nav {
        margin-bottom: 25px;
    }
    
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f5f5f5;
        color: #333;
        padding: 10px 20px;
        border-radius: 40px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .back-btn:hover {
        background: #e60012;
        color: white;
        transform: translateX(-3px);
    }
    
    /* 主容器 */
    .product-main {
        display: flex;
        gap: 50px;
        flex-wrap: wrap;
        background: white;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    
    /* 左侧图片区 */
    .product-gallery {
        flex: 1;
        min-width: 300px;
    }
    
    .main-image {
        width: 100%;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        background: #f8f8f8;
    }
    
    /* 右侧信息区 */
    .product-info {
        flex: 1;
        min-width: 300px;
    }
    
    .product-category {
        display: inline-block;
        background: #f0f0f0;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #e60012;
        margin-bottom: 15px;
    }
    
    .product-title {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0 0 10px 0;
        line-height: 1.3;
    }
    
    /* 评分区域 */
    .rating-section {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .stars {
        color: #ffc107;
        font-size: 18px;
        letter-spacing: 2px;
    }
    
    .review-link {
        color: #888;
        font-size: 14px;
        text-decoration: none;
    }
    
    .review-link:hover {
        color: #e60012;
    }
    
    /* 价格 */
    .price-section {
        margin: 20px 0;
    }
    
    .current-price {
        font-size: 36px;
        font-weight: 800;
        color: #e60012;
    }
    
    .original-price {
        font-size: 18px;
        color: #999;
        text-decoration: line-through;
        margin-left: 10px;
    }
    
    /* 库存状态 */
    .stock-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 500;
        margin: 15px 0;
    }
    
    .in-stock {
        background: #e8f5e9;
        color: #2e7d32;
    }
    
    .low-stock {
        background: #fff3e0;
        color: #ff9800;
    }
    
    .out-stock {
        background: #ffebee;
        color: #f44336;
    }
    
    /* 积分 */
    .points-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #fff8e1;
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 14px;
        color: #ff9800;
        margin: 10px 0;
    }
    
    /* 尺码选择 */
    .size-section {
        margin: 25px 0;
    }
    
    .size-label {
        display: block;
        font-weight: 600;
        margin-bottom: 12px;
        color: #333;
    }
    
    .size-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .size-btn {
        min-width: 55px;
        padding: 10px 18px;
        border: 1.5px solid #e0e0e0;
        background: white;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .size-btn:hover {
        border-color: #e60012;
        color: #e60012;
    }
    
    .size-btn.active {
        background: #e60012;
        border-color: #e60012;
        color: white;
    }
    
    /* 数量选择 */
    .quantity-section {
        margin: 25px 0;
    }
    
    .quantity-label {
        display: block;
        font-weight: 600;
        margin-bottom: 12px;
        color: #333;
    }
    
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .qty-btn {
        width: 36px;
        height: 36px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
        transition: 0.2s;
    }
    
    .qty-btn:hover {
        background: #f0f0f0;
    }
    
    .quantity-input {
        width: 70px;
        height: 40px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 16px;
    }
    
    /* 加购按钮 */
    .add-to-cart-btn {
        width: 100%;
        background: #e60012;
        color: white;
        padding: 16px;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        margin: 20px 0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .add-to-cart-btn:hover {
        background: #cc0010;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(230,0,18,0.3);
    }
    
    .add-to-cart-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    /* 商品描述 */
    .description-section {
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #eee;
    }
    
    .description-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 15px;
        color: #333;
    }
    
    .description-text {
        color: #666;
        line-height: 1.8;
    }
    
    /* 评论区域 */
    .reviews-section {
        margin-top: 40px;
        background: white;
        border-radius: 24px;
        padding: 35px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    
    .reviews-title {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e60012;
    }
    
    .review-summary {
        display: flex;
        gap: 40px;
        padding-bottom: 25px;
        border-bottom: 1px solid #eee;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    
    .avg-rating-box {
        text-align: center;
        min-width: 120px;
    }
    
    .avg-number {
        font-size: 48px;
        font-weight: 800;
        color: #e60012;
    }
    
    .avg-stars {
        color: #ffc107;
        font-size: 18px;
        margin: 8px 0;
    }
    
    .rating-bars {
        flex: 1;
        min-width: 200px;
    }
    
    .rating-bar-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 6px 0;
    }
    
    .rating-bar-label {
        width: 45px;
        font-size: 13px;
    }
    
    .rating-bar-bg {
        flex: 1;
        height: 8px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .rating-bar-fill {
        height: 100%;
        background: #ffc107;
        border-radius: 4px;
    }
    
    .rating-bar-count {
        width: 40px;
        font-size: 12px;
        color: #888;
    }
    
    .review-card {
        background: #fafafa;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 15px;
        transition: 0.2s;
    }
    
    .review-card:hover {
        background: #f5f5f5;
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .reviewer {
        font-weight: 700;
        color: #333;
    }
    
    .review-stars {
        color: #ffc107;
        font-size: 14px;
    }
    
    .review-date {
        color: #999;
        font-size: 12px;
    }
    
    .review-comment {
        color: #555;
        line-height: 1.6;
        margin-top: 10px;
    }
    
    .review-form {
        background: #fff8e1;
        padding: 25px;
        border-radius: 16px;
        margin: 25px 0;
    }
    
    .review-form h4 {
        margin: 0 0 15px 0;
        font-size: 18px;
    }
    
    .rating-star {
        font-size: 32px;
        cursor: pointer;
        color: #ddd;
        transition: 0.2s;
    }
    
    .rating-star:hover,
    .rating-star.selected {
        color: #ffc107;
    }
    
    .review-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 10px;
        margin: 15px 0;
        resize: vertical;
        font-family: inherit;
    }
    
    .submit-btn {
        background: #e60012;
        color: white;
        padding: 12px 28px;
        border: none;
        border-radius: 40px;
        cursor: pointer;
        font-weight: 600;
        transition: 0.2s;
    }
    
    .submit-btn:hover {
        background: #cc0010;
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .product-main {
            padding: 20px;
        }
        .product-title {
            font-size: 22px;
        }
        .current-price {
            font-size: 28px;
        }
        .reviews-section {
            padding: 20px;
        }
    }
</style>

<div class="product-detail-page">
    <!-- 返回按钮 -->
    <div class="back-nav">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    
    <div class="product-main">
        <!-- 左侧图片 -->
        <div class="product-gallery">
            <img src="../images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" 
                 class="main-image" 
                 onerror="this.src='../images/default.jpg'"
                 alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        
        <!-- 右侧信息 -->
        <div class="product-info">
            <span class="product-category">
                <i class="fas fa-tag"></i> 
                <?php echo isset($product['category_type']) ? ($product['category_type'] == 'Men' ? 'Men\'s Fashion' : 'Women\'s Fashion') : 'Fashion'; ?>
            </span>
            
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="rating-section">
                <div class="stars">
                    <?php for($i=1;$i<=5;$i++) echo $i<=$avg_rating?'★':'☆'; ?>
                </div>
                <a href="#reviews" class="review-link">
                    <?php echo $total_reviews; ?> customer review<?php echo $total_reviews != 1 ? 's' : ''; ?>
                </a>
            </div>
            
            <div class="price-section">
                <span class="current-price">RM <?php echo number_format($product['price'], 2); ?></span>
            </div>
            
            <div>
                <span class="stock-status <?php echo $product['stock'] > 10 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-stock'); ?>">
                    <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <?php echo $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ' available)' : 'Out of Stock'; ?>
                </span>
            </div>
            
            <div class="points-badge">
                <i class="fas fa-gift"></i> Earn <?php echo floor($product['price']); ?> reward points with this purchase
            </div>
            
            <!-- 尺码选择 -->
            <?php if(!empty($sizes)): ?>
            <div class="size-section">
                <span class="size-label"><i class="fas fa-ruler"></i> Select Size:</span>
                <div class="size-buttons">
                    <?php foreach($sizes as $size): ?>
                        <button class="size-btn" data-size="<?php echo trim($size); ?>"><?php echo trim($size); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected_size">
            </div>
            <?php endif; ?>
            
            <!-- 数量选择 -->
            <div class="quantity-section">
                <span class="quantity-label"><i class="fas fa-sort-amount-up"></i> Quantity:</span>
                <div class="quantity-selector">
                    <button class="qty-btn" onclick="changeQty(-1)">-</button>
                    <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                    <button class="qty-btn" onclick="changeQty(1)">+</button>
                </div>
            </div>
            
            <!-- 加购按钮 -->
            <?php if ($product['stock'] > 0): ?>
                <button class="add-to-cart-btn" onclick="addToCart()">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            <?php else: ?>
                <button class="add-to-cart-btn" disabled>
                    <i class="fas fa-ban"></i> Out of Stock
                </button>
            <?php endif; ?>
            
            <!-- 商品描述 -->
            <div class="description-section">
                <div class="description-title">
                    <i class="fas fa-file-alt"></i> Product Description
                </div>
                <div class="description-text">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available for this product.')); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 评论区域 -->
    <div id="reviews" class="reviews-section">
        <div class="reviews-title">
            <i class="fas fa-star"></i> Customer Reviews
        </div>
        
        <div class="review-summary">
            <div class="avg-rating-box">
                <div class="avg-number"><?php echo $avg_rating; ?></div>
                <div class="avg-stars">
                    <?php for($i=1;$i<=5;$i++) echo $i<=round($avg_rating)?'★':'☆'; ?>
                </div>
                <div><?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?></div>
            </div>
            <div class="rating-bars">
                <?php for($r=5; $r>=1; $r--): 
                    $count = $stats['rating_'.$r] ?? 0;
                    $percent = $total_reviews > 0 ? ($count / $total_reviews * 100) : 0;
                ?>
                <div class="rating-bar-item">
                    <span class="rating-bar-label"><?php echo $r; ?> ★</span>
                    <div class="rating-bar-bg">
                        <div class="rating-bar-fill" style="width: <?php echo $percent; ?>%;"></div>
                    </div>
                    <span class="rating-bar-count"><?php echo $count; ?></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- 写评论表单 -->
        <?php if(isset($_SESSION['user_id'])):
            $stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
            $stmt->execute([$product_id, $_SESSION['user_id']]);
            $hasReviewed = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT 1 FROM orders WHERE user_id = ? AND product_id = ? AND order_status = 'Delivered' LIMIT 1");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            $canReview = $stmt->fetch();
            
            if(!$hasReviewed && $canReview): ?>
                <div class="review-form">
                    <h4>✍️ Write Your Review</h4>
                    <form method="POST" action="../member/Reviewsubmit.php">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="rating-select">
                            <?php for($i=1;$i<=5;$i++): ?>
                                <span class="rating-star" data-rating="<?php echo $i; ?>">☆</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating_value" required>
                        <textarea name="comment" class="review-textarea" rows="4" placeholder="Share your experience with this product..."></textarea>
                        <button type="submit" class="submit-btn">Submit Review</button>
                    </form>
                </div>
            <?php elseif($hasReviewed): ?>
                <div style="background:#e8f5e9; padding:15px; border-radius:12px;">
                    <i class="fas fa-check-circle" style="color:#4caf50;"></i> You have already reviewed this product. Thank you for your feedback!
                </div>
            <?php elseif(!$canReview): ?>
                <div style="background:#fff3e0; padding:15px; border-radius:12px;">
                    <i class="fas fa-lock"></i> You can only review products you have purchased and received. 
                    <a href="../member/orders_history.php" style="color:#e60012;">View your orders →</a>
                </div>
            <?php endif; 
        else: ?>
            <div style="background:#f0f0f0; padding:15px; border-radius:12px;">
                <i class="fas fa-sign-in-alt"></i> <a href="../login.php" style="color:#e60012;">Login</a> to write a review.
            </div>
        <?php endif; ?>
        
        <!-- 评论列表 -->
        <?php if(empty($reviews)): ?>
            <div style="text-align:center; padding:40px; color:#999;">
                <i class="fas fa-comment-dots" style="font-size:40px; margin-bottom:15px; display:block;"></i>
                No reviews yet. Be the first to review this product!
            </div>
        <?php else: foreach($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <span class="reviewer"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($review['username']); ?></span>
                    <div class="review-stars"><?php for($i=1;$i<=5;$i++) echo $i<=$review['rating']?'★':'☆'; ?></div>
                    <span class="review-date"><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                </div>
                <?php if(!empty($review['comment'])): ?>
                    <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// 尺码选择
$('.size-btn').click(function(){
    $('.size-btn').removeClass('active');
    $(this).addClass('active');
    $('#selected_size').val($(this).data('size'));
});

// 评分星星
$('.rating-star').click(function(){
    let rating = $(this).data('rating');
    $('#rating_value').val(rating);
    $('.rating-star').each(function(){
        if($(this).data('rating') <= rating) {
            $(this).html('★').addClass('selected');
        } else {
            $(this).html('☆').removeClass('selected');
        }
    });
});

// 数量加减
function changeQty(delta) {
    let input = $('#quantity');
    let current = parseInt(input.val()) || 1;
    let min = 1;
    let max = <?php echo $product['stock']; ?>;
    let newVal = current + delta;
    if (newVal < min) newVal = min;
    if (newVal > max) newVal = max;
    input.val(newVal);
}

// 限制输入范围
$('#quantity').on('change', function() {
    let val = parseInt($(this).val()) || 1;
    let min = 1;
    let max = <?php echo $product['stock']; ?>;
    if (val < min) val = min;
    if (val > max) val = max;
    $(this).val(val);
});

// 加入购物车
function addToCart(){
    let qty = $('#quantity').val();
    let size = $('#selected_size').val();
    
    <?php if(!empty($sizes)): ?>
        if(size === ''){
            alert('Please select a size');
            return;
        }
    <?php endif; ?>
    
    $.ajax({
        url: 'add.php',
        method: 'POST',
        data: { 
            product_id: <?php echo $product_id; ?>, 
            quantity: qty, 
            selected_size: size 
        },
        dataType: 'json',
        success: function(response){
            if(response.success){
                if(confirm('✓ Added to cart! Go to cart now?')){
                    window.location.href = 'unified_cart.php';
                }
            } else {
                alert(response.error);
                if(response.error === 'Please login first'){
                    window.location.href = '../login.php';
                }
            }
        },
        error: function(){
            alert('Error adding to cart');
        }
    });
}
</script>

<?php require_once '../includes/_foot.php'; ?>