<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

$current_user_id = $_SESSION['user_id'] ?? null;

/**
 * 优化后的 SQL：通过子查询检测收藏状态
 */
$featured_sql = "
    SELECT p.*, 
    (SELECT COUNT(*) FROM favourites f WHERE f.product_id = p.product_id AND f.user_id = ?) as is_favourite
    FROM products p 
    ORDER BY p.product_id DESC 
    LIMIT 12";
$stmt = $pdo->prepare($featured_sql);
$stmt->execute([$current_user_id]);
$featured_products = $stmt->fetchAll();

$new_products_sql = "
    SELECT p.*, 
    (SELECT COUNT(*) FROM favourites f WHERE f.product_id = p.product_id AND f.user_id = ?) as is_favourite
    FROM products p 
    ORDER BY product_id DESC 
    LIMIT 8";
$stmt = $pdo->prepare($new_products_sql);
$stmt->execute([$current_user_id]);
$new_products = $stmt->fetchAll();
?>

<link rel="stylesheet" href="css/app.css">

<div class="hero-slider-container">
    <div class="slide active" style="background-image: url('images/pxx.jpeg'); background-size: cover; background-position: center;">
        <a href="ViewAllProduct.php" class="btn-red-action shop-now-bottom">Shop Now →</a>
    </div>
    <div class="slide" style="background-image: url('images/pxx2.jpeg'); background-size: cover; background-position: center;">
        <a href="ViewAllProduct.php" class="btn-red-action shop-now-bottom">Shop Now →</a>
    </div>
    <div class="slide" style="background-image: url('images/pxx3.jpeg'); background-size: cover; background-position: center;">
        <a href="ViewAllProduct.php" class="btn-red-action shop-now-bottom">Shop Now →</a>
    </div>

    <button class="slider-nav slider-prev" id="sliderPrev">❮</button>
    <button class="slider-nav slider-next" id="sliderNext">❯</button>

    <div class="slider-dots" id="sliderDots">
        <div class="dot active" data-index="0"></div>
        <div class="dot" data-index="1"></div>
        <div class="dot" data-index="2"></div>
    </div>
</div>

<div class="category-section">
    <a href="ViewAllProduct.php?category=Men" class="category-card men" style="background-image: url('images/men.jpeg'); background-size: cover; background-position: center top;">
        <div class="category-btn-container">
            <span class="btn-red-action">Shop Men →</span>
        </div>
    </a>
    
    <a href="ViewAllProduct.php?category=Women" class="category-card women" style="background-image: url('images/women.jpeg'); background-size: cover; background-position: center top;">
        <div class="category-btn-container">
            <span class="btn-red-action">Shop Women →</span>
        </div>
    </a>
</div>

<div class="features-section">
    <div class="feature-item">
        <div class="feature-icon">🚚</div>
        <div class="feature-title">Free Shipping</div>
        <div class="feature-desc">On orders RM100+</div>
    </div>
    <div class="feature-item">
        <div class="feature-icon">🔄</div>
        <div class="feature-title">Easy Returns</div>
        <div class="feature-desc">30 days return policy</div>
    </div>
    <div class="feature-item">
        <div class="feature-icon">⭐</div>
        <div class="feature-title">Reward Points</div>
        <div class="feature-desc">Earn points on every purchase</div>
    </div>
    <div class="feature-item">
        <div class="feature-icon">💳</div>
        <div class="feature-title">Secure Payment</div>
        <div class="feature-desc">Multiple payment options</div>
    </div>
</div>

<div id="featured" class="section-header">
    <h2 class="section-title">🔥 Hot Picks</h2>
    <a href="ViewAllProduct.php" class="section-link">View All →</a>
</div>

<div class="products-grid" id="featuredGrid">
    <?php if (empty($featured_products)): ?>
        <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: #999;">No products available yet.</p>
    <?php else: ?>
        <?php foreach ($featured_products as $product): ?>
            <div class="product-card <?php echo ($product['is_favourite'] > 0) ? 'is-fav-module' : ''; ?>">
                <div class="product-img-wrapper">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='images/default.jpg'">
                    </a>
                    <div class="quick-buttons">
                        <button class="quick-wishlist <?php echo ($product['is_favourite'] > 0) ? 'active' : ''; ?>" 
                                data-id="<?php echo $product['product_id']; ?>">
                            <?php echo ($product['is_favourite'] > 0) ? '♥ Wishlisted' : '♡ Wishlist'; ?>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <?php echo isset($product['category_type']) ? ($product['category_type'] == 'Men' ? '👔 MEN' : '👗 WOMEN') : 'FASHION'; ?>
                    </div>
                    <div class="product-name">
                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </div>
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                    
                    <!-- 库存和销量显示 -->
                    <div class="product-stock-info">
                        <div class="stock-left">
                            <i class="fas fa-box"></i> Stock: <strong><?php echo $product['stock']; ?></strong> left
                        </div>
                        <div class="sales-count">
                            <i class="fas fa-chart-line"></i> Sold: <strong><?php echo number_format($product['sales_count'] ?? 0); ?></strong>
                        </div>
                    </div>
                    
                    <div class="product-meta">
                        <span class="points-badge">🎁 +<?php echo floor($product['price']); ?> pts</span>
                        <span class="stock-status <?php echo $product['stock'] <= 0 ? 'out' : ($product['stock'] < 10 ? 'low' : ''); ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock' : '✗ Out of Stock'; ?>
                        </span>
                    </div>
                    <?php if ($product['stock'] > 0): ?>
                        <button class="add-to-cart-btn" data-id="<?php echo $product['product_id']; ?>">
                            🛒 Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="add-to-cart-btn" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="section-header">
    <h2 class="section-title">✨ New Arrivals</h2>
    <a href="ViewAllProduct.php" class="section-link">View All →</a>
</div>

<div class="products-grid" id="newGrid">
    <?php if (empty($new_products)): ?>
        <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: #999;">No new products.</p>
    <?php else: ?>
        <?php foreach ($new_products as $product): ?>
            <div class="product-card <?php echo ($product['is_favourite'] > 0) ? 'is-fav-module' : ''; ?>">
                <div class="product-img-wrapper">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='images/default.jpg'">
                    </a>
                    <div class="quick-buttons">
                        <button class="quick-wishlist <?php echo ($product['is_favourite'] > 0) ? 'active' : ''; ?>" 
                                data-id="<?php echo $product['product_id']; ?>">
                            <?php echo ($product['is_favourite'] > 0) ? '♥ Wishlisted' : '♡ Wishlist'; ?>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <div class="product-category">
                        <?php echo isset($product['category_type']) ? ($product['category_type'] == 'Men' ? '👔 MEN' : '👗 WOMEN') : 'FASHION'; ?>
                    </div>
                    <div class="product-name">
                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </div>
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                    
                    <!-- 库存和销量显示 -->
                    <div class="product-stock-info">
                        <div class="stock-left">
                            <i class="fas fa-box"></i> Stock: <strong><?php echo $product['stock']; ?></strong> left
                        </div>
                        <div class="sales-count">
                            <i class="fas fa-chart-line"></i> Sold: <strong><?php echo number_format($product['sales_count'] ?? 0); ?></strong>
                        </div>
                    </div>
                    
                    <div class="product-meta">
                        <span class="points-badge">🎁 +<?php echo floor($product['price']); ?> pts</span>
                        <span class="stock-status <?php echo $product['stock'] <= 0 ? 'out' : ($product['stock'] < 10 ? 'low' : ''); ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock' : '✗ Out of Stock'; ?>
                        </span>
                    </div>
                    <?php if ($product['stock'] > 0): ?>
                        <button class="add-to-cart-btn" data-id="<?php echo $product['product_id']; ?>">
                            🛒 Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="add-to-cart-btn" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="promo-banner">
    <h3>🎉 Free Shipping on Orders RM100+</h3>
    <p>Use code: <strong style="background: rgba(255,255,255,0.25); padding: 6px 15px; border-radius: 30px;">PXXSHIP</strong> at checkout</p>
    <a href="ViewAllProduct.php" class="promo-btn">Shop Now →</a>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    //  PHP to JavaScript
    var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<script src="js/app.js"></script>

<?php require_once 'includes/_foot.php'; ?>
