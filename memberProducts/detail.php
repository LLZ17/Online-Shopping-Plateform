<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) { header('Location: listing.php'); exit(); }

$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) { header('Location: listing.php'); exit(); }

// 获取评价 - 支持星级筛选
$rating_filter = isset($_GET['rating_filter']) ? (int)$_GET['rating_filter'] : 0;
$sql_reviews = "SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ?";
$params_reviews = [$product_id];
if ($rating_filter >= 1 && $rating_filter <= 5) {
    $sql_reviews .= " AND r.rating = ?";
    $params_reviews[] = $rating_filter;
}
$sql_reviews .= " ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql_reviews);
$stmt->execute($params_reviews);
$reviews = $stmt->fetchAll();

// 获取评分统计
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

$sizes = !empty($product['sizes']) ? explode(',', $product['sizes']) : [];
?>

<style>
    .detail-container { max-width: 1200px; margin: 40px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .product-detail { display: flex; gap: 40px; padding: 30px; flex-wrap: wrap; }
    .product-image { flex: 1; min-width: 250px; }
    .product-image img { width: 100%; border-radius: 10px; }
    .product-info { flex: 1; }
    .product-name { font-size: 28px; margin: 0 0 10px 0; }
    .price { font-size: 32px; color: #e60012; font-weight: bold; margin: 15px 0; }
    .stock { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 14px; margin: 10px 0; }
    .in-stock { background: #e8f5e9; color: #2e7d32; }
    .points { color: #ff9800; margin: 10px 0; }
    .quantity { width: 80px; padding: 10px; margin: 15px 0; border: 1px solid #ddd; border-radius: 5px; }
    .btn-add { background: #e60012; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
    .size-btn { padding: 8px 18px; margin: 5px; border: 1px solid #ddd; background: white; border-radius: 5px; cursor: pointer; }
    .size-btn.active { background: #e60012; color: white; }
    
    /* 评价区域样式 */
    .reviews-section { padding: 30px; border-top: 1px solid #eee; background: #f9f9f9; }
    .review-summary { display: flex; gap: 40px; padding-bottom: 20px; border-bottom: 1px solid #eee; margin-bottom: 20px; flex-wrap: wrap; }
    .avg-rating-box { text-align: center; }
    .avg-number { font-size: 48px; font-weight: bold; color: #e60012; }
    .avg-stars { color: #ffc107; font-size: 20px; }
    .rating-bars { flex: 1; min-width: 200px; }
    .rating-bar-item { display: flex; align-items: center; gap: 10px; margin: 5px 0; }
    .rating-bar-label { width: 40px; font-size: 14px; }
    .rating-bar-bg { flex: 1; height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden; }
    .rating-bar-fill { height: 100%; background: #ffc107; border-radius: 4px; }
    .rating-bar-count { width: 40px; font-size: 12px; color: #666; }
    .filter-buttons { display: flex; gap: 10px; flex-wrap: wrap; margin: 15px 0; }
    .filter-btn { padding: 6px 15px; background: #f0f0f0; border: none; border-radius: 20px; cursor: pointer; font-size: 13px; }
    .filter-btn.active { background: #e60012; color: white; }
    .review-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; }
    .reviewer { font-weight: bold; }
    .review-stars { color: #ffc107; }
    .review-date { color: #999; font-size: 12px; }
    .review-comment { margin-top: 10px; line-height: 1.6; color: #555; }
    .review-form { background: #fff3e0; padding: 20px; border-radius: 10px; margin: 20px 0; }
    .rating-star { font-size: 30px; cursor: pointer; color: #ddd; transition: 0.2s; }
    .rating-star:hover, .rating-star.selected { color: #ffc107; }
</style>

<div class="detail-container">
    <div style="margin-bottom: 20px;">
        <a href="javascript:history.back()" style="display: inline-block; background: #666; color: white; padding: 8px 20px; text-decoration: none; border-radius: 5px;">
            ← Back
        </a>
        <a href="../index.php" style="display: inline-block; background: #333; color: white; padding: 8px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;">
            🏠 Home
        </a>
    </div>
    <div class="product-detail">
        <div class="product-image">
            <img src="../images/<?php echo $product['product_image'] ?: 'default.jpg'; ?>" onerror="this.src='../images/default.jpg'">
        </div>
        <div class="product-info">
            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="stars"><?php for($i=1;$i<=5;$i++) echo $i<=$avg_rating?'★':'☆'; ?> <span>(<?php echo $total_reviews; ?> reviews)</span></div>
            <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
            <div class="stock in-stock">✓ In Stock (<?php echo $product['stock']; ?> available)</div>
            <div class="points">🎁 Earn <?php echo floor($product['price']); ?> reward points</div>
            <?php if(!empty($sizes)): ?>
                <div><strong>Size:</strong><br><?php foreach($sizes as $size): ?><button class="size-btn" data-size="<?php echo trim($size); ?>"><?php echo trim($size); ?></button><?php endforeach; ?><input type="hidden" id="selected_size"></div>
            <?php endif; ?>
            <div class="description"><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($product['description']?:'No description.')); ?></div>
            <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" style="width:80px; padding:10px; margin:15px 0;">
            <button class="btn-add" onclick="addToCart()">Add to Cart</button>
        </div>
    </div>

    <div class="reviews-section">
        <h3>Customer Reviews</h3>
        
        <!-- 评分摘要 -->
        <div class="review-summary">
            <div class="avg-rating-box">
                <div class="avg-number"><?php echo $avg_rating; ?></div>
                <div class="avg-stars"><?php for($i=1;$i<=5;$i++) echo $i<=round($avg_rating)?'★':'☆'; ?></div>
                <div><?php echo $total_reviews; ?> reviews</div>
            </div>
            <div class="rating-bars">
                <?php for($r=5; $r>=1; $r--): 
                    $count = $stats['rating_'.$r] ?? 0;
                    $percent = $total_reviews > 0 ? ($count / $total_reviews * 100) : 0;
                ?>
                <div class="rating-bar-item">
                    <span class="rating-bar-label"><?php echo $r; ?> ★</span>
                    <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $percent; ?>%;"></div></div>
                    <span class="rating-bar-count"><?php echo $count; ?></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- 筛选按钮 -->
        <div class="filter-buttons">
            <a href="?id=<?php echo $product_id; ?>" class="filter-btn <?php echo $rating_filter == 0 ? 'active' : ''; ?>">All (<?php echo $total_reviews; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=5" class="filter-btn <?php echo $rating_filter == 5 ? 'active' : ''; ?>">5 ★ (<?php echo $stats['rating_5'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=4" class="filter-btn <?php echo $rating_filter == 4 ? 'active' : ''; ?>">4 ★ (<?php echo $stats['rating_4'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=3" class="filter-btn <?php echo $rating_filter == 3 ? 'active' : ''; ?>">3 ★ (<?php echo $stats['rating_3'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=2" class="filter-btn <?php echo $rating_filter == 2 ? 'active' : ''; ?>">2 ★ (<?php echo $stats['rating_2'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=1" class="filter-btn <?php echo $rating_filter == 1 ? 'active' : ''; ?>">1 ★ (<?php echo $stats['rating_1'] ?? 0; ?>)</a>
            <a href="?id=<?php echo $product_id; ?>&rating_filter=with_comments" class="filter-btn">With Comments (<?php echo $stats['with_comments'] ?? 0; ?>)</a>
        </div>
        
        <!-- 写评价表单 -->
        <?php if(isset($_SESSION['user_id'])):
            $stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE product_id=? AND user_id=?");
            $stmt->execute([$product_id,$_SESSION['user_id']]);
            $hasReviewed = $stmt->fetch();
            $stmt = $pdo->prepare("SELECT 1 FROM orders WHERE user_id=? AND product_id=? AND order_status='Delivered' LIMIT 1");
            $stmt->execute([$_SESSION['user_id'],$product_id]);
            $canReview = $stmt->fetch();
            if(!$hasReviewed && $canReview): ?>
                <div class="review-form">
                    <h4>Write a Review</h4>
                    <form method="POST" action="../MemberReviews/Reviewsubmit.php">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="rating-select">
                            <?php for($i=1;$i<=5;$i++): ?>
                                <span class="rating-star" data-rating="<?php echo $i; ?>">☆</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating_value" required>
                        <textarea name="comment" rows="4" style="width:100%; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:8px;" placeholder="Share your experience..."></textarea>
                        <button type="submit" style="background:#e60012; color:white; padding:10px 25px; border:none; border-radius:5px; cursor:pointer;">Submit Review</button>
                    </form>
                </div>
            <?php elseif($hasReviewed): ?>
                <p>✅ You have already reviewed this product.</p>
            <?php elseif(!$canReview): ?>
                <p>📝 You can only review products you have purchased.</p>
            <?php endif; else: ?>
            <p><a href="../login.php">Login</a> to write a review.</p>
        <?php endif; ?>
        
        <!-- 评价列表 -->
        <?php if(empty($reviews)): ?>
            <p style="text-align:center; padding:30px;">No reviews yet. Be the first to review!</p>
        <?php else: foreach($reviews as $review): ?>
            <div class="review-card">
                <div class="review-header">
                    <div><span class="reviewer"><?php echo htmlspecialchars($review['username']); ?></span></div>
                    <div class="review-stars"><?php for($i=1;$i<=5;$i++) echo $i<=$review['rating']?'★':'☆'; ?></div>
                    <div class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
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
$('.size-btn').click(function(){
    $('.size-btn').removeClass('active');
    $(this).addClass('active');
    $('#selected_size').val($(this).data('size'));
});

$('.rating-star').click(function(){
    let rating = $(this).data('rating');
    $('#rating_value').val(rating);
    $('.rating-star').each(function(){
        $(this).data('rating') <= rating ? $(this).html('★').css('color','#ffc107') : $(this).html('☆').css('color','#ddd');
    });
});

function addToCart(){
    let q=$('#quantity').val(), s=$('#selected_size').val();
    <?php if(!empty($sizes)): ?>if(s===''){ alert('Select size'); return; }<?php endif; ?>
    $.ajax({
        url:'../member_cart/add.php',
        method:'POST',
        data:{ product_id:<?php echo $product_id; ?>, quantity:q, selected_size:s },
        dataType:'json',
        success:function(r){
            if(r.success){
                if(confirm('✓ Added to cart! Go to cart now?')) window.location.href='../member_cart/unified_cart.php';
            } else { alert(r.error); if(r.error==='Please login first') window.location.href='../login.php'; }
        }
    });
}
</script>
<?php require_once __DIR__ . '/../includes/_foot.php'; ?>