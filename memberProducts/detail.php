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

$sizes = !empty($product['size']) ? explode(',', $product['size']) : [];
?>

<style>
    .detail-container { max-width: 1200px; margin: 40px auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .product-detail { display: flex; gap: 50px; padding: 40px; flex-wrap: wrap; }
    .product-gallery { flex: 1; min-width: 300px; }
    .main-image { width: 100%; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .product-info { flex: 1; min-width: 300px; }
    .product-category { font-size: 12px; color: #e60012; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
    .product-name { font-size: 28px; margin: 0 0 15px 0; color: #1a1a1a; }
    .rating { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
    .stars { color: #ffc107; font-size: 16px; }
    .review-count { color: #888; font-size: 13px; }
    .price { font-size: 32px; color: #e60012; font-weight: bold; margin: 15px 0; }
    .stock-status { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 13px; margin: 10px 0; }
    .in-stock { background: #e8f5e9; color: #2e7d32; }
    .out-stock { background: #ffebee; color: #f44336; }
    .points { background: #fff3e0; color: #ff9800; padding: 8px 15px; border-radius: 25px; display: inline-block; margin: 10px 0; font-size: 14px; }
    .size-section { margin: 20px 0; }
    .size-label { font-weight: 600; margin-bottom: 10px; display: block; }
    .size-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    .size-btn { padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 8px; cursor: pointer; transition: 0.2s; }
    .size-btn:hover, .size-btn.active { background: #e60012; color: white; border-color: #e60012; }
    .quantity-section { margin: 20px 0; }
    .quantity-input { width: 80px; padding: 12px; border: 1px solid #ddd; border-radius: 8px; text-align: center; font-size: 16px; }
    .btn-add-cart { width: 100%; background: #e60012; color: white; padding: 15px; border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer; margin: 20px 0; transition: 0.3s; }
    .btn-add-cart:hover { background: #cc0010; transform: translateY(-2px); }
    .btn-add-cart:disabled { background: #ccc; cursor: not-allowed; }
    .description { border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px; }
    .description h4 { margin-bottom: 10px; }
    .description p { color: #666; line-height: 1.6; }
    .reviews-section { padding: 30px 40px; background: #f9f9f9; border-top: 1px solid #eee; }
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
    .review-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .review-header { display: flex; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; }
    .reviewer { font-weight: bold; }
    .review-stars { color: #ffc107; }
    .review-date { color: #999; font-size: 12px; }
    .review-comment { margin-top: 10px; line-height: 1.6; color: #555; }
    .review-form { background: #fff8e1; padding: 20px; border-radius: 10px; margin: 20px 0; }
    .rating-star { font-size: 30px; cursor: pointer; color: #ddd; transition: 0.2s; }
    .rating-star:hover, .rating-star.selected { color: #ffc107; }
    .back-link { display: inline-block; margin: 20px 40px; color: #666; text-decoration: none; }
    .back-link:hover { color: #e60012; }
    .submit-review-btn { background: #e60012; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin-top: 10px; }
    .submit-review-btn:hover { background: #cc0010; }
</style>

<a href="javascript:history.back()" class="back-link">← Back</a>

<div class="detail-container">
    <div class="product-detail">
        <div class="product-gallery">
            <img src="../images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" 
                 class="main-image" 
                 onerror="this.src='../images/default.jpg'">
        </div>
        <div class="product-info">
            <div class="product-category"><?php echo isset($product['category_type']) ? ($product['category_type'] == 'Men' ? '👔 MEN' : '👗 WOMEN') : 'FASHION'; ?></div>
            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="rating">
                <div class="stars"><?php for($i=1;$i<=5;$i++) echo $i<=$avg_rating?'★':'☆'; ?></div>
                <span class="review-count">(<?php echo $total_reviews; ?> reviews)</span>
            </div>
            <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
            <div class="stock-status <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                <?php echo $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ' available)' : '✗ Out of Stock'; ?>
            </div>
            <div class="points">🎁 Earn <?php echo floor($product['price']); ?> reward points with this purchase</div>
            
            <?php if(!empty($sizes)): ?>
            <div class="size-section">
                <span class="size-label">Select Size:</span>
                <div class="size-buttons">
                    <?php foreach($sizes as $size): ?>
                        <button class="size-btn" data-size="<?php echo trim($size); ?>"><?php echo trim($size); ?></button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" id="selected_size">
            </div>
            <?php endif; ?>
            
            <div class="quantity-section">
                <span class="size-label">Quantity:</span>
                <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="quantity-input">
            </div>
            
            <?php if ($product['stock'] > 0): ?>
                <button class="btn-add-cart" onclick="addToCart()">🛒 Add to Cart</button>
            <?php else: ?>
                <button class="btn-add-cart" disabled>Out of Stock</button>
            <?php endif; ?>
            
            <div class="description">
                <h4>Product Description</h4>
                <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available for this product.')); ?></p>
            </div>
        </div>
    </div>

    <div class="reviews-section">
        <h3>Customer Reviews</h3>
        
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
        
        <?php if(isset($_SESSION['user_id'])):
            $stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
            $stmt->execute([$product_id, $_SESSION['user_id']]);
            $hasReviewed = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT 1 FROM orders WHERE user_id = ? AND product_id = ? AND order_status = 'Delivered' LIMIT 1");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            $canReview = $stmt->fetch();
            
            if(!$hasReviewed && $canReview): ?>
                <div class="review-form">
                    <h4>Write a Review</h4>
                    <form method="POST" action="../member/Reviewsubmit.php">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="rating-select">
                            <?php for($i=1;$i<=5;$i++): ?>
                                <span class="rating-star" data-rating="<?php echo $i; ?>">☆</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating_value" required>
                        <textarea name="comment" rows="4" style="width:100%; margin:15px 0; padding:12px; border:1px solid #ddd; border-radius:8px;" placeholder="Share your experience with this product..."></textarea>
                        <button type="submit" class="submit-review-btn">Submit Review</button>
                    </form>
                </div>
            <?php elseif($hasReviewed): ?>
                <p style="background: #e8f5e9; padding: 15px; border-radius: 8px;">✅ You have already reviewed this product. Thank you for your feedback!</p>
            <?php elseif(!$canReview): ?>
                <p style="background: #fff3e0; padding: 15px; border-radius: 8px;">📝 You can only review products you have purchased and received. <a href="../member/orders_history.php">View your orders</a></p>
            <?php endif; 
        else: ?>
            <p style="background: #f0f0f0; padding: 15px; border-radius: 8px;">🔐 <a href="../login.php" style="color:#e60012;">Login</a> to write a review.</p>
        <?php endif; ?>
        
        <?php if(empty($reviews)): ?>
            <p style="text-align:center; padding:30px;">No reviews yet. Be the first to review this product!</p>
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
        if($(this).data('rating') <= rating) {
            $(this).html('★').addClass('selected');
        } else {
            $(this).html('☆').removeClass('selected');
        }
    });
});

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
