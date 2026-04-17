<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($product_id)) { 
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
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1,
        SUM(CASE WHEN comment != '' THEN 1 ELSE 0 END) as with_comments
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
    /* ============================================
       商品详情页样式 - 无 inline CSS
    ============================================ */
    .detail-page {
        max-width: 1200px;
        margin: 40px auto;
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
        padding: 10px 22px;
        border-radius: 40px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .back-btn:hover {
        background: #e60012;
        color: white;
    }
    
    /* 主容器 */
    .product-container {
        display: flex;
        gap: 50px;
        flex-wrap: wrap;
        background: white;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    
    .product-gallery {
        flex: 1;
        min-width: 300px;
    }
    
    .main-image {
        width: 100%;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
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
    
    .sales-count {
        color: #666;
        font-size: 14px;
        border-left: 1px solid #ddd;
        padding-left: 15px;
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
    
    .stock-available {
        color: #666;
        font-size: 14px;
        margin: 10px 0;
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
    
    /* 选项按钮组 */
    .option-group {
        margin: 25px 0;
    }
    
    .option-label {
        display: block;
        font-weight: 600;
        margin-bottom: 12px;
        color: #333;
    }
    
    .option-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .option-btn {
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
    
    .option-btn:hover {
        border-color: #e60012;
        color: #e60012;
    }
    
    .option-btn.active {
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
    
    /* ============================================
       评论区域样式
    ============================================ */
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
    
    /* 筛选按钮 */
    .filter-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin: 15px 0;
    }
    
    .filter-btn {
        padding: 6px 15px;
        background: #f0f0f0;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-size: 13px;
        text-decoration: none;
        color: #333;
        transition: 0.2s;
    }
    
    .filter-btn:hover {
        background: #e0e0e0;
    }
    
    .filter-btn.active {
        background: #e60012;
        color: white;
    }
    
    /* 评论卡片 */
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
    
    /* 写评论表单 */
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
        display: inline-block;
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
    
    /* 响应式 */
    @media (max-width: 768px) {
        .product-container {
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
        .review-summary {
            flex-direction: column;
            align-items: center;
        }
        .filter-buttons {
            justify-content: center;
        }
    }
</style>

<div class="detail-page">
    <!-- 返回按钮 -->
    <div class="back-nav">
        <a href="javascript:history.back()" class="back-btn" id="backBtn">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    
    <div class="product-container">
        <!-- 左侧图片 -->
        <div class="product-gallery">
            <?php
            $product_image = 'default.jpg';
            if (!empty($product['product_image'])) {
                $images = explode(',', $product['product_image']);
                $product_image = trim($images[0]);
            }
            ?>
            <img src="../images/<?php echo htmlspecialchars($product_image); ?>" 
                 class="main-image" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 id="mainImage">
        </div>
        
        <!-- 右侧信息 -->
        <div class="product-info">
            <span class="product-category">
                <i class="fas fa-tag"></i> 
                <?php echo (strpos($product['product_id'], 'M') === 0) ? 'Men\'s Collection' : 'Women\'s Collection'; ?>
            </span>
            
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="rating-section">
                <div class="stars" id="starRating">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php echo $i <= $avg_rating ? '★' : '☆'; ?>
                    <?php endfor; ?>
                </div>
                <a href="#reviews" class="review-link" id="reviewLink">
                    <?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?>
                </a>
                <div class="sales-count">
                    <i class="fas fa-chart-line"></i> <?php echo number_format($product['sales_count'] ?? 0); ?> Sold
                </div>
            </div>
            
            <div class="price-section">
                <span class="current-price">RM <?php echo number_format($product['price'], 2); ?></span>
            </div>
            
            <div>
                <span class="stock-status <?php echo $product['stock'] > 10 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-stock'); ?>">
                    <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <?php echo $product['stock'] > 0 ? 'In stock' : 'Out of stock'; ?>
                </span>
            </div>
            
            <div class="stock-available">
                <i class="fas fa-cubes"></i> Stock: <?php echo $product['stock']; ?> pieces available
            </div>
            
            <div class="points-badge">
                <i class="fas fa-gift"></i> Earn <?php echo floor($product['price']); ?> reward points with this purchase
            </div>
            
            <!-- 颜色选择 -->
            <?php if(!empty($colours)): ?>
            <div class="option-group">
                <span class="option-label"><i class="fas fa-palette"></i> Select Colour:</span>
                <div class="option-buttons" id="colourButtons">
                    <?php foreach($colours as $c): ?>
                        <button class="option-btn colour-btn" data-value="<?php echo trim($c); ?>"><?php echo trim($c); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected_colour">
            </div>
            <?php endif; ?>
            
            <!-- 尺码选择 -->
            <?php if(!empty($sizes)): ?>
            <div class="option-group">
                <span class="option-label"><i class="fas fa-ruler"></i> Select Size:</span>
                <div class="option-buttons" id="sizeButtons">
                    <?php foreach($sizes as $s): ?>
                        <button class="option-btn size-btn" data-value="<?php echo trim($s); ?>"><?php echo trim($s); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected_size">
            </div>
            <?php endif; ?>
            
            <!-- 数量选择 -->
            <div class="quantity-section">
                <span class="quantity-label"><i class="fas fa-sort-amount-up"></i> Quantity:</span>
                <div class="quantity-selector">
                    <button class="qty-btn" id="qtyMinus">-</button>
                    <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
                    <button class="qty-btn" id="qtyPlus">+</button>
                </div>
            </div>
            
            <!-- 加购按钮 -->
            <button class="add-to-cart-btn" id="addToCartBtn" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
            
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
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php echo $i <= round($avg_rating) ? '★' : '☆'; ?>
                    <?php endfor; ?>
                </div>
                <div><?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?></div>
            </div>
            <div class="rating-bars">
                <?php for($r = 5; $r >= 1; $r--): 
                    $count = $stats['rating_' . $r] ?? 0;
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
        
        <!-- 筛选按钮 -->
        <div class="filter-buttons">
            <a href="?id=<?php echo $product_id; ?>" class="filter-btn <?php echo !isset($_GET['rating_filter']) || $_GET['rating_filter'] == 0 ? 'active' : ''; ?>">All (<?php echo $total_reviews; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=5" class="filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 5 ? 'active' : ''; ?>">5 ★ (<?php echo $stats['rating_5'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=4" class="filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 4 ? 'active' : ''; ?>">4 ★ (<?php echo $stats['rating_4'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=3" class="filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 3 ? 'active' : ''; ?>">3 ★ (<?php echo $stats['rating_3'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=2" class="filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 2 ? 'active' : ''; ?>">2 ★ (<?php echo $stats['rating_2'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=1" class="filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 1 ? 'active' : ''; ?>">1 ★ (<?php echo $stats['rating_1'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=with_comments" class="filter-btn">With Comments (<?php echo $stats['with_comments'] ?? 0; ?>)</a>
        </div>
        
        <!-- 写评论表单 -->
        <?php if(isset($_SESSION['user_id'])):
            $check_stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
            $check_stmt->execute([$product_id, $_SESSION['user_id']]);
            $hasReviewed = $check_stmt->fetch();
            
            $order_stmt = $pdo->prepare("SELECT 1 FROM orders WHERE user_id = ? AND product_id = ? AND order_status = 'Delivered' LIMIT 1");
            $order_stmt->execute([$_SESSION['user_id'], $product_id]);
            $canReview = $order_stmt->fetch();
            
            if(!$hasReviewed && $canReview): ?>
                <div class="review-form">
                    <h4>✍️ Write Your Review</h4>
                    <form method="POST" action="../member/Reviewsubmit.php" id="reviewForm">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="rating-select" id="ratingStars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="rating-star" data-rating="<?php echo $i; ?>">☆</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating_value" required>
                        <textarea name="comment" class="review-textarea" rows="4" placeholder="Share your experience with this product..." id="reviewComment"></textarea>
                        <button type="submit" class="submit-btn" id="submitReviewBtn">Submit Review</button>
                    </form>
                </div>
            <?php elseif($hasReviewed): ?>
                <div class="review-card" style="background:#e8f5e9;">
                    <i class="fas fa-check-circle" style="color:#4caf50;"></i> You have already reviewed this product. Thank you for your feedback!
                </div>
            <?php elseif(!$canReview): ?>
                <div class="review-card" style="background:#fff3e0;">
                    <i class="fas fa-lock"></i> You can only review products you have purchased and received. 
                    <a href="../member/orders_history.php" style="color:#e60012;">View your orders →</a>
                </div>
            <?php endif; 
        else: ?>
            <div class="review-card" style="background:#f0f0f0;">
                <i class="fas fa-sign-in-alt"></i> <a href="../login.php" style="color:#e60012;">Login</a> to write a review.
            </div>
        <?php endif; ?>
        
        <!-- 评论列表 -->
        <?php if(empty($reviews)): ?>
            <div class="empty-reviews">
                <i class="fas fa-comment-dots"></i>
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        <?php else: ?>
            <?php foreach($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <span class="reviewer"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($review['username']); ?></span>
                    <div class="review-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="review-date"><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                </div>
                <?php if(!empty($review['comment'])): ?>
                    <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 返回按钮
    $('#backBtn').on('click', function(e) {
        e.preventDefault();
        window.history.back();
    });
    
    // 颜色选择
    $('.colour-btn').on('click', function() {
        $('.colour-btn').removeClass('active');
        $(this).addClass('active');
        $('#selected_colour').val($(this).data('value'));
    });
    
    // 尺码选择
    $('.size-btn').on('click', function() {
        $('.size-btn').removeClass('active');
        $(this).addClass('active');
        $('#selected_size').val($(this).data('value'));
    });
    
    // 数量减少
    $('#qtyMinus').on('click', function() {
        var input = $('#quantity');
        var current = parseInt(input.val()) || 1;
        var min = 1;
        if (current > min) {
            input.val(current - 1);
        }
    });
    
    // 数量增加
    $('#qtyPlus').on('click', function() {
        var input = $('#quantity');
        var current = parseInt(input.val()) || 1;
        var max = <?php echo $product['stock']; ?>;
        if (current < max) {
            input.val(current + 1);
        }
    });
    
    // 数量输入框变化
    $('#quantity').on('change', function() {
        var val = parseInt($(this).val()) || 1;
        var min = 1;
        var max = <?php echo $product['stock']; ?>;
        if (val < min) val = min;
        if (val > max) val = max;
        $(this).val(val);
    });
    
    // 评分星星
    $('.rating-star').on('click', function() {
        var rating = $(this).data('rating');
        $('#rating_value').val(rating);
        $('.rating-star').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).html('★').addClass('selected');
            } else {
                $(this).html('☆').removeClass('selected');
            }
        });
    });
    
    // 加购按钮
    $('#addToCartBtn').on('click', function() {
        var qty = $('#quantity').val();
        var size = $('#selected_size').val();
        var colour = $('#selected_colour').val();
        
        <?php if(!empty($sizes)): ?>
            if (size === '') {
                alert('Please select a size');
                return;
            }
        <?php endif; ?>
        
        $.ajax({
            url: 'add.php',
            method: 'POST',
            data: { 
                product_id: '<?php echo $product_id; ?>', 
                quantity: qty, 
                selected_size: size,
                colour: colour
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (confirm('✓ Added to cart! Go to cart now?')) {
                        window.location.href = 'unified_cart.php';
                    }
                } else {
                    alert(response.error);
                    if (response.error === 'Please login first') {
                        window.location.href = '../login.php';
                    }
                }
            },
            error: function() {
                alert('Error adding to cart');
            }
        });
    });
});
</script>

<style>
    .empty-reviews {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }
    .empty-reviews i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
    }
    .empty-reviews p {
        font-size: 16px;
    }
</style>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>
