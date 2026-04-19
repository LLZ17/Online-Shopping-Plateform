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

<link rel="stylesheet" href="../css/app.css">

<div class="product-detail-page">
    <!-- 返回按钮 -->
    <div class="product-detail-back-nav">
        <a href="javascript:history.back()" class="product-detail-back-btn" id="productDetailBackBtn">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    
    <div class="product-detail-container">
        <!-- 左侧图片 -->
        <div class="product-detail-gallery">
            <?php
            $product_image = 'default.jpg';
            if (!empty($product['product_image'])) {
                $images = explode(',', $product['product_image']);
                $product_image = trim($images[0]);
            }
            ?>
            <img src="../images/<?php echo htmlspecialchars($product_image); ?>" 
                 class="product-detail-main-image" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 id="productDetailMainImage">
        </div>
        
        <!-- 右侧信息 -->
        <div class="product-detail-info">
            <span class="product-detail-category">
                <i class="fas fa-tag"></i> 
                <?php echo (strpos($product['product_id'], 'M') === 0) ? 'Men\'s Collection' : 'Women\'s Collection'; ?>
            </span>
            
            <h1 class="product-detail-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="product-detail-rating-section">
                <div class="product-detail-stars" id="productDetailStarRating">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php echo $i <= $avg_rating ? '★' : '☆'; ?>
                    <?php endfor; ?>
                </div>
                <a href="#reviews" class="product-detail-review-link" id="productDetailReviewLink">
                    <?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?>
                </a>
                <div class="product-detail-sales-count">
                    <i class="fas fa-chart-line"></i> <?php echo number_format($product['sales_count'] ?? 0); ?> Sold
                </div>
            </div>
            
            <div class="product-detail-price-section">
                <span class="product-detail-current-price">RM <?php echo number_format($product['price'], 2); ?></span>
            </div>
            
<div class="product-detail-stock-detail">
    <div class="stock-left-detail">
        <i class="fas fa-boxes"></i> Available Stock: <strong><?php echo $product['stock']; ?></strong> pieces
    </div>
    <div class="sales-count-detail">
        <i class="fas fa-chart-simple"></i> Total Sold: <strong><?php echo number_format($product['sales_count'] ?? 0); ?></strong> units
    </div>
</div>
            
            <div>
                <span class="product-detail-stock-status <?php echo $product['stock'] > 10 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-stock'); ?>">
                    <i class="fas <?php echo $product['stock'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <?php echo $product['stock'] > 0 ? 'In stock' : 'Out of stock'; ?>
                </span>
            </div>
            
            <div class="product-detail-stock-available">
                <i class="fas fa-cubes"></i> Stock: <?php echo $product['stock']; ?> pieces available
            </div>
            
            <div class="product-detail-points-badge">
                <i class="fas fa-gift"></i> Earn <?php echo floor($product['price']); ?> reward points with this purchase
            </div>
            
            <!-- 颜色选择 -->
            <?php if(!empty($colours)): ?>
            <div class="product-detail-option-group">
                <span class="product-detail-option-label"><i class="fas fa-palette"></i> Select Colour:</span>
                <div class="product-detail-option-buttons" id="productDetailColourButtons">
                    <?php foreach($colours as $c): ?>
                        <button class="product-detail-option-btn colour-btn" data-value="<?php echo trim($c); ?>"><?php echo trim($c); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="productDetailSelectedColour">
            </div>
            <?php endif; ?>
            
            <!-- 尺码选择 -->
            <?php if(!empty($sizes)): ?>
            <div class="product-detail-option-group">
                <span class="product-detail-option-label"><i class="fas fa-ruler"></i> Select Size:</span>
                <div class="product-detail-option-buttons" id="productDetailSizeButtons">
                    <?php foreach($sizes as $s): ?>
                        <button class="product-detail-option-btn size-btn" data-value="<?php echo trim($s); ?>"><?php echo trim($s); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="productDetailSelectedSize">
            </div>
            <?php endif; ?>
            
            <!-- 数量选择 -->
            <div class="product-detail-quantity-section">
                <span class="product-detail-quantity-label"><i class="fas fa-sort-amount-up"></i> Quantity:</span>
                <div class="product-detail-quantity-selector">
                    <button class="product-detail-qty-btn" id="productDetailQtyMinus">-</button>
                    <input type="number" id="productDetailQuantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="product-detail-quantity-input">
                    <button class="product-detail-qty-btn" id="productDetailQtyPlus">+</button>
                </div>
            </div>
            
            <!-- 加购按钮 -->
            <button class="product-detail-add-to-cart-btn" id="productDetailAddToCartBtn" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
            
            <!-- 商品描述 -->
            <div class="product-detail-description-section">
                <div class="product-detail-description-title">
                    <i class="fas fa-file-alt"></i> Product Description
                </div>
                <div class="product-detail-description-text">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available for this product.')); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 评论区域 -->
    <div id="reviews" class="product-detail-reviews-section">
        <div class="product-detail-reviews-title">
            <i class="fas fa-star"></i> Customer Reviews
        </div>
        
        <div class="product-detail-review-summary">
            <div class="product-detail-avg-rating-box">
                <div class="product-detail-avg-number"><?php echo $avg_rating; ?></div>
                <div class="product-detail-avg-stars">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php echo $i <= round($avg_rating) ? '★' : '☆'; ?>
                    <?php endfor; ?>
                </div>
                <div><?php echo $total_reviews; ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?></div>
            </div>
            <div class="product-detail-rating-bars">
                <?php for($r = 5; $r >= 1; $r--): 
                    $count = $stats['rating_' . $r] ?? 0;
                    $percent = $total_reviews > 0 ? ($count / $total_reviews * 100) : 0;
                ?>
                <div class="product-detail-rating-bar-item">
                    <span class="product-detail-rating-bar-label"><?php echo $r; ?> ★</span>
                    <div class="product-detail-rating-bar-bg">
                        <div class="product-detail-rating-bar-fill" style="width: <?php echo $percent; ?>%;"></div>
                    </div>
                    <span class="product-detail-rating-bar-count"><?php echo $count; ?></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- 筛选按钮 -->
        <div class="product-detail-filter-buttons">
            <a href="?id=<?php echo $product_id; ?>" class="product-detail-filter-btn <?php echo !isset($_GET['rating_filter']) || $_GET['rating_filter'] == 0 ? 'active' : ''; ?>">All (<?php echo $total_reviews; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=5" class="product-detail-filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 5 ? 'active' : ''; ?>">5 ★ (<?php echo $stats['rating_5'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=4" class="product-detail-filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 4 ? 'active' : ''; ?>">4 ★ (<?php echo $stats['rating_4'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=3" class="product-detail-filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 3 ? 'active' : ''; ?>">3 ★ (<?php echo $stats['rating_3'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=2" class="product-detail-filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 2 ? 'active' : ''; ?>">2 ★ (<?php echo $stats['rating_2'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=1" class="product-detail-filter-btn <?php echo isset($_GET['rating_filter']) && $_GET['rating_filter'] == 1 ? 'active' : ''; ?>">1 ★ (<?php echo $stats['rating_1'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=with_comments" class="product-detail-filter-btn">With Comments (<?php echo $stats['with_comments'] ?? 0; ?>)</a>
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
                <div class="product-detail-review-form">
                    <h4>✍️ Write Your Review</h4>
                    <form method="POST" action="../member/Reviewsubmit.php" id="productDetailReviewForm">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="product-detail-rating-select" id="productDetailRatingStars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="product-detail-rating-star" data-rating="<?php echo $i; ?>">☆</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="productDetailRatingValue" required>
                        <textarea name="comment" class="product-detail-review-textarea" rows="4" placeholder="Share your experience with this product..." id="productDetailReviewComment"></textarea>
                        <button type="submit" class="product-detail-submit-btn" id="productDetailSubmitReviewBtn">Submit Review</button>
                    </form>
                </div>
            <?php elseif($hasReviewed): ?>
                <div class="product-detail-review-card product-detail-already-reviewed">
                    <i class="fas fa-check-circle" style="color:#4caf50;"></i> You have already reviewed this product. Thank you for your feedback!
                </div>
            <?php elseif(!$canReview): ?>
                <div class="product-detail-review-card product-detail-cannot-review">
                    <i class="fas fa-lock"></i> You can only review products you have purchased and received. 
                    <a href="../member/orders_history.php" style="color:#e60012;">View your orders →</a>
                </div>
            <?php endif; 
        else: ?>
            <div class="product-detail-review-card product-detail-login-prompt">
                <i class="fas fa-sign-in-alt"></i> <a href="../login.php" style="color:#e60012;">Login</a> to write a review.
            </div>
        <?php endif; ?>
        
        <!-- 评论列表 -->
        <?php if(empty($reviews)): ?>
            <div class="product-detail-empty-reviews">
                <i class="fas fa-comment-dots"></i>
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        <?php else: ?>
            <?php foreach($reviews as $review): ?>
            <div class="product-detail-review-card">
                <div class="product-detail-review-header">
                    <span class="product-detail-reviewer"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($review['username']); ?></span>
                    <div class="product-detail-review-stars">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="product-detail-review-date"><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                </div>
                <?php if(!empty($review['comment'])): ?>
                    <div class="product-detail-review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var productStock = <?php echo $product['stock']; ?>;
    var productId = '<?php echo $product_id; ?>';
    var hasSizes = <?php echo !empty($sizes) ? 'true' : 'false'; ?>;
</script>
<script src="../js/app.js"></script>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>
