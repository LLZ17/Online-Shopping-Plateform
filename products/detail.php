<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: listing.php');
    exit();
}

// Get product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: listing.php');
    exit();
}

// Get product reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll();

// Calculate average rating
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = ?");
$stmt->execute([$product_id]);
$rating_stats = $stmt->fetch();
$avg_rating = round($rating_stats['avg_rating'] ?? 0, 1);
$total_reviews = $rating_stats['total'] ?? 0;

// Parse sizes
$sizes = [];
if (!empty($product['sizes'])) {
    $sizes = explode(',', $product['sizes']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <style>
        body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; }
        .product-detail { display: flex; gap: 40px; padding: 30px; flex-wrap: wrap; }
        .product-image { flex: 1; min-width: 250px; }
        .product-image img { width: 100%; border-radius: 10px; }
        .product-info { flex: 1; }
        .product-name { font-size: 28px; margin: 0 0 10px 0; }
        .price { font-size: 32px; color: #e60012; font-weight: bold; margin: 15px 0; }
        .stock { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 14px; margin: 10px 0; }
        .in-stock { background: #e8f5e9; color: #2e7d32; }
        .out-stock { background: #ffebee; color: #c62828; }
        .points { color: #ff9800; margin: 10px 0; }
        .description { margin: 20px 0; line-height: 1.6; color: #666; }
        .quantity { width: 80px; padding: 10px; margin: 15px 0; border: 1px solid #ddd; border-radius: 5px; }
        .btn-add { background: #e60012; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-add:hover { background: #cc0010; }
        .stars { color: #ffc107; font-size: 20px; }
        .sizes { margin: 15px 0; }
        .size-btn { padding: 8px 18px; margin: 5px; border: 1px solid #ddd; background: white; border-radius: 5px; cursor: pointer; }
        .size-btn:hover, .size-btn.active { background: #e60012; color: white; border-color: #e60012; }
        .back-link { display: inline-block; margin: 20px; color: #666; text-decoration: none; }
        .reviews-section { padding: 30px; border-top: 1px solid #eee; background: #f9f9f9; }
        .review { border-bottom: 1px solid #eee; padding: 15px 0; }
        .review-stars { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <a href="listing.php" class="back-link">← Back to Products</a>
        
        <div class="product-detail">
            <div class="product-image">
                <img src="../images/<?php echo !empty($product['product_image']) ? $product['product_image'] : 'default.jpg'; ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <div class="product-info">
                <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="stars">
                    <?php for($i = 1; $i <= 5; $i++) echo $i <= $avg_rating ? '★' : '☆'; ?>
                    <span style="color: #666; font-size: 14px;">(<?php echo $total_reviews; ?> reviews)</span>
                </div>
                
                <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
                
                <div class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                    <?php echo $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ' available)' : '✗ Out of Stock'; ?>
                </div>
                
                <div class="points">🎁 Earn <?php echo floor($product['price']); ?> reward points</div>
                
                <?php if (!empty($sizes)): ?>
                <div class="sizes">
                    <strong>Size:</strong><br>
                    <?php foreach ($sizes as $size): ?>
                        <button type="button" class="size-btn" data-size="<?php echo trim($size); ?>"><?php echo trim($size); ?></button>
                    <?php endforeach; ?>
                    <input type="hidden" id="selected_size" value="">
                </div>
                <?php endif; ?>
                
                <div class="description">
                    <strong>Description:</strong><br>
                    <?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?>
                </div>
                
                <?php if ($product['stock'] > 0): ?>
                    <input type="number" id="quantity" class="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    <br>
                    <button class="btn-add" onclick="addToCart()">Add to Cart</button>
                <?php else: ?>
                    <button class="btn-add" disabled style="background: #ccc;">Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="reviews-section">
            <h3>Customer Reviews</h3>
            <?php if (isset($_SESSION['user_id'])): 
    // 检查是否已评价
    $stmt_check = $pdo->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt_check->execute([$product_id, $_SESSION['user_id']]);
    $hasReviewed = $stmt_check->fetch();
    
    // 检查是否已购买并送达
    $stmt_purchase = $pdo->prepare("
        SELECT 1 FROM orders 
        WHERE user_id = ? AND product_id = ? AND order_status = 'Delivered'
        LIMIT 1
    ");
    $stmt_purchase->execute([$_SESSION['user_id'], $product_id]);
    $canReview = $stmt_purchase->fetch();
    
    if (!$hasReviewed && $canReview):
?>
    <div class="review-form">
        <h4>Write a Review</h4>
        <form method="POST" action="../reviews/submit.php">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <div class="rating-select">
                <span class="star" data-rating="1">☆</span>
                <span class="star" data-rating="2">☆</span>
                <span class="star" data-rating="3">☆</span>
                <span class="star" data-rating="4">☆</span>
                <span class="star" data-rating="5">☆</span>
            </div>
            <input type="hidden" name="rating" id="rating_value" required>
            <textarea name="comment" rows="4" placeholder="Share your experience..." required></textarea>
            <button type="submit">Submit Review</button>
        </form>
    </div>
<?php elseif ($hasReviewed): ?>
    <p>✅ You have already reviewed this product.</p>
<?php elseif (!$canReview): ?>
    <p>📝 You can only review products you have purchased and received.</p>
<?php endif; endif; ?>
            <?php if (empty($reviews)): ?>
                <p>No reviews yet.</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review">
                        <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                        <span class="review-stars">
                            <?php for($i = 1; $i <= 5; $i++) echo $i <= $review['rating'] ? '★' : '☆'; ?>
                        </span>
                        <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Size selection
    $('.size-btn').click(function() {
        $('.size-btn').removeClass('active').css({'background': 'white', 'color': '#333', 'border-color': '#ddd'});
        $(this).addClass('active').css({'background': '#e60012', 'color': 'white', 'border-color': '#e60012'});
        $('#selected_size').val($(this).data('size'));
    });
    
    function addToCart() {
        var quantity = $('#quantity').val();
        var size = $('#selected_size').val();
        
        <?php if (!empty($sizes)): ?>
        if (size === '') {
            alert('Please select a size');
            return;
        }
        <?php endif; ?>
        
        $.ajax({
            url: '../cart/add.php',
            method: 'POST',
            data: { 
                product_id: <?php echo $product_id; ?>, 
                quantity: quantity,
                selected_size: size 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (confirm('✓ Added to cart! Go to cart now?')) {
                        window.location.href = '../cart/index.php';
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
    }
    </script>
</body>
</html>