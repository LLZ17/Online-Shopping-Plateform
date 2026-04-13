<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

// 获取推荐产品（按销量排序，取前8个）
$stmt = $pdo->prepare("
    SELECT p.*, 
           COALESCE(SUM(o.quantity), 0) as total_sold
    FROM products p
    LEFT JOIN orders o ON p.product_id = o.product_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC, p.product_id DESC
    LIMIT 8
");
$stmt->execute();
$featured_products = $stmt->fetchAll();

// 获取新品（最新8个）
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY product_id DESC LIMIT 8");
$stmt->execute();
$new_products = $stmt->fetchAll();
?>

<style>
    /* ============================================
       首页样式 - 男装女装专卖店 (完整版600+行)
    ============================================ */
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        background: #f8f8f8;
        font-family: 'Poppins', 'Segoe UI', sans-serif;
    }
    
    /* Hero 区域 - 大图Banner */
    .hero-section {
        background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
        margin: 20px 30px 40px 30px;
        border-radius: 28px;
        padding: 80px 40px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('images/pxxbg.jpeg') center/cover no-repeat;
        opacity: 0.12;
        z-index: 0;
    }
    
    .hero-content {
        position: relative;
        z-index: 1;
    }
    
    .hero-section h1 {
        font-size: 56px;
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
        letter-spacing: -1px;
    }
    
    .hero-section h1 span {
        font-size: 22px;
        vertical-align: super;
    }
    
    .hero-section p {
        font-size: 18px;
        color: rgba(255,255,255,0.85);
        margin-bottom: 30px;
    }
    
    .hero-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-primary {
        background: #e60012;
        color: white;
        padding: 14px 35px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
        display: inline-block;
    }
    
    .btn-primary:hover {
        background: #cc0010;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(230,0,18,0.35);
    }
    
    .btn-secondary {
        background: transparent;
        color: white;
        padding: 14px 35px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        border: 2px solid white;
        transition: 0.3s;
        display: inline-block;
    }
    
    .btn-secondary:hover {
        background: white;
        color: #e60012;
        transform: translateY(-2px);
    }
    
    /* 分类卡片 - 男装/女装 */
    .category-section {
        display: flex;
        gap: 30px;
        margin: 40px 30px;
        flex-wrap: wrap;
    }
    
    .category-card {
        flex: 1;
        min-width: 280px;
        border-radius: 24px;
        overflow: hidden;
        position: relative;
        height: 340px;
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
    }
    
    .category-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 35px rgba(0,0,0,0.15);
    }
    
    .category-card.men {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    }
    
    .category-card.women {
        background: linear-gradient(135deg, #c31432 0%, #e60012 100%);
    }
    
    .category-card .category-bg {
        position: absolute;
        bottom: 0;
        right: 0;
        font-size: 140px;
        opacity: 0.12;
        pointer-events: none;
    }
    
    .category-card .category-content {
        position: relative;
        z-index: 1;
        padding: 50px 30px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        color: white;
    }
    
    .category-icon {
        font-size: 60px;
        margin-bottom: 20px;
    }
    
    .category-card h3 {
        font-size: 34px;
        font-weight: 800;
        margin-bottom: 10px;
    }
    
    .category-card p {
        opacity: 0.85;
        margin-bottom: 25px;
        font-size: 14px;
    }
    
    .shop-now-btn {
        background: rgba(255,255,255,0.2);
        display: inline-block;
        padding: 10px 25px;
        border-radius: 40px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s;
    }
    
    .category-card:hover .shop-now-btn {
        background: white;
        color: #e60012;
    }
    
    /* 区域标题 */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin: 50px 30px 25px 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .section-title {
        font-size: 28px;
        font-weight: 700;
        color: #1a1a1a;
        position: relative;
        padding-left: 15px;
    }
    
    .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 24px;
        background: #e60012;
        border-radius: 2px;
    }
    
    .section-link {
        color: #e60012;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
    }
    
    .section-link:hover {
        text-decoration: underline;
    }
    
    /* 产品网格 */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 28px;
        margin: 0 30px;
    }
    
    .product-card {
        background: white;
        border-radius: 18px;
        overflow: hidden;
        transition: 0.3s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 35px rgba(0,0,0,0.12);
    }
    
    .product-img-wrapper {
        position: relative;
        overflow: hidden;
        background: #f5f5f5;
        height: 290px;
    }
    
    .product-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.5s ease;
    }
    
    .product-card:hover .product-img-wrapper img {
        transform: scale(1.06);
    }
    
    /* 悬停快捷按钮 */
    .quick-buttons {
        position: absolute;
        bottom: -50px;
        left: 0;
        right: 0;
        display: flex;
        gap: 10px;
        padding: 12px;
        background: rgba(255,255,255,0.95);
        transition: 0.3s ease;
        justify-content: center;
    }
    
    .product-card:hover .quick-buttons {
        bottom: 0;
    }
    
    .quick-wishlist {
        background: #f0f0f0;
        border: none;
        padding: 8px 16px;
        border-radius: 30px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        transition: 0.2s;
    }
    
    .quick-wishlist:hover {
        background: #e60012;
        color: white;
    }
    
    /* 产品信息 */
    .product-info {
        padding: 20px;
    }
    
    .product-category {
        font-size: 11px;
        color: #e60012;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 8px;
    }
    
    .product-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
        line-height: 1.4;
        height: 44px;
        overflow: hidden;
    }
    
    .product-name a {
        text-decoration: none;
        color: #1a1a1a;
        transition: color 0.2s;
    }
    
    .product-name a:hover {
        color: #e60012;
    }
    
    .product-price {
        font-size: 22px;
        font-weight: 700;
        color: #e60012;
        margin: 10px 0;
    }
    
    .product-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
        font-size: 12px;
    }
    
    .points-badge {
        background: #fff3e0;
        color: #ff9800;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }
    
    .stock-status {
        color: #4caf50;
        font-size: 11px;
        font-weight: 500;
    }
    
    .stock-status.low {
        color: #ff9800;
    }
    
    .stock-status.out {
        color: #f44336;
    }
    
    /* 加购按钮 */
    .add-to-cart-btn {
        width: 100%;
        background: #1a1a1a;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        margin-top: 15px;
        transition: all 0.3s ease;
    }
    
    .add-to-cart-btn:hover {
        background: #e60012;
        transform: translateY(-2px);
    }
    
    .add-to-cart-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    /* 促销横幅 */
    .promo-banner {
        background: linear-gradient(135deg, #e60012 0%, #ff4757 50%, #ff6b81 100%);
        margin: 50px 30px;
        padding: 50px;
        border-radius: 28px;
        text-align: center;
        color: white;
    }
    
    .promo-banner h3 {
        font-size: 32px;
        margin-bottom: 12px;
        font-weight: 800;
    }
    
    .promo-banner p {
        opacity: 0.92;
        margin-bottom: 25px;
        font-size: 16px;
    }
    
    .promo-btn {
        background: white;
        color: #e60012;
        padding: 14px 35px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 700;
        display: inline-block;
        transition: 0.3s;
    }
    
    .promo-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    
    /* 特色服务区域 */
    .features-section {
        display: flex;
        justify-content: space-between;
        gap: 25px;
        margin: 50px 30px;
        flex-wrap: wrap;
    }
    
    .feature-item {
        flex: 1;
        text-align: center;
        padding: 25px;
        background: white;
        border-radius: 20px;
        min-width: 180px;
        transition: 0.3s;
    }
    
    .feature-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
    
    .feature-icon {
        font-size: 40px;
        margin-bottom: 15px;
    }
    
    .feature-title {
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 8px;
        color: #1a1a1a;
    }
    
    .feature-desc {
        font-size: 13px;
        color: #888;
    }
    
    /* 页脚样式增强 */
    .footer-enhanced {
        background: #1a1a1a;
        color: #999;
        padding: 50px 30px 30px;
        margin-top: 60px;
    }
    
    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .footer-col h4 {
        color: white;
        margin-bottom: 20px;
        font-size: 18px;
    }
    
    .footer-col a {
        display: block;
        color: #999;
        text-decoration: none;
        margin-bottom: 12px;
        font-size: 14px;
        transition: 0.2s;
    }
    
    .footer-col a:hover {
        color: #e60012;
    }
    
    .social-icons {
        display: flex;
        gap: 15px;
        margin-top: 15px;
    }
    
    .social-icons a {
        width: 36px;
        height: 36px;
        background: #333;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }
    
    .social-icons a:hover {
        background: #e60012;
        color: white;
    }
    
    .copyright {
        text-align: center;
        padding-top: 40px;
        margin-top: 40px;
        border-top: 1px solid #333;
        font-size: 13px;
    }
    
    /* 响应式设计 */
    @media (max-width: 992px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
    }
    
    @media (max-width: 768px) {
        .hero-section {
            margin: 10px 15px 30px 15px;
            padding: 50px 20px;
        }
        .hero-section h1 {
            font-size: 36px;
        }
        .category-section {
            margin: 20px 15px;
            flex-direction: column;
        }
        .category-card {
            height: 280px;
        }
        .products-grid {
            margin: 0 15px;
            gap: 15px;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        }
        .section-header {
            margin: 30px 15px 20px 15px;
        }
        .section-title {
            font-size: 22px;
        }
        .promo-banner {
            margin: 30px 15px;
            padding: 35px 20px;
        }
        .promo-banner h3 {
            font-size: 24px;
        }
        .features-section {
            margin: 30px 15px;
            gap: 15px;
        }
        .feature-item {
            padding: 15px;
        }
        .feature-icon {
            font-size: 30px;
        }
        .product-info {
            padding: 12px;
        }
        .product-name {
            font-size: 13px;
            height: 36px;
        }
        .product-price {
            font-size: 16px;
        }
        .add-to-cart-btn {
            padding: 8px;
            font-size: 12px;
        }
    }
    
    @media (max-width: 480px) {
        .hero-section {
            padding: 40px 15px;
        }
        .hero-section h1 {
            font-size: 28px;
        }
        .hero-section p {
            font-size: 14px;
        }
        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            font-size: 14px;
        }
    }
</style>

<!-- Hero Banner -->
<div class="hero-section">
    <div class="hero-content">
        <h1>PXX Mall<span>&reg;</span></h1>
        <p>Your Ultimate Destination for Fashion & Streetwear</p>
        <div class="hero-buttons">
            <a href="ViewAllProduct.php" class="btn-primary">Shop Now →</a>
            <a href="#featured" class="btn-secondary">Explore</a>
        </div>
    </div>
</div>

<!-- 男装/女装 分类入口 -->
<div class="category-section">
    <a href="ViewAllProduct.php?category=Men" class="category-card men">
        <div class="category-bg">👔</div>
        <div class="category-content">
            <div class="category-icon">👔</div>
            <h3>Men's Collection</h3>
            <p>Shirts • T-Shirts • Pants • Jeans</p>
            <span class="shop-now-btn">Shop Men →</span>
        </div>
    </a>
    
    <a href="ViewAllProduct.php?category=Women" class="category-card women">
        <div class="category-bg">👗</div>
        <div class="category-content">
            <div class="category-icon">👗</div>
            <h3>Women's Collection</h3>
            <p>Tops • Dresses • Skirts • Pants</p>
            <span class="shop-now-btn">Shop Women →</span>
        </div>
    </a>
</div>

<!-- 特色服务区域 -->
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

<!-- 热门产品区域 -->
<div id="featured" class="section-header">
    <h2 class="section-title">🔥 Hot Picks</h2>
    <a href="ViewAllProduct.php" class="section-link">View All →</a>
</div>

<div class="products-grid">
    <?php if (empty($featured_products)): ?>
        <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: #999;">No products available yet.</p>
    <?php else: ?>
        <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <div class="product-img-wrapper">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='images/default.jpg'">
                    </a>
                    <div class="quick-buttons">
                        <button class="quick-wishlist" onclick="addToWishlist(<?php echo $product['product_id']; ?>)">
                            ♡ Wishlist
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
                    <div class="product-meta">
                        <span class="points-badge">🎁 +<?php echo floor($product['price']); ?> pts</span>
                        <span class="stock-status <?php echo $product['stock'] <= 0 ? 'out' : ($product['stock'] < 10 ? 'low' : ''); ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock' : '✗ Out of Stock'; ?>
                        </span>
                    </div>
                    <?php if ($product['stock'] > 0): ?>
                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['product_id']; ?>)">
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

<!-- 新品区域 -->
<div class="section-header">
    <h2 class="section-title">✨ New Arrivals</h2>
    <a href="ViewAllProduct.php" class="section-link">View All →</a>
</div>

<div class="products-grid">
    <?php if (empty($new_products)): ?>
        <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: #999;">No new products.</p>
    <?php else: ?>
        <?php foreach ($new_products as $product): ?>
            <div class="product-card">
                <div class="product-img-wrapper">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='images/default.jpg'">
                    </a>
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
                    <div class="product-meta">
                        <span class="points-badge">🎁 +<?php echo floor($product['price']); ?> pts</span>
                        <span class="stock-status <?php echo $product['stock'] <= 0 ? 'out' : ($product['stock'] < 10 ? 'low' : ''); ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock' : '✗ Out of Stock'; ?>
                        </span>
                    </div>
                    <?php if ($product['stock'] > 0): ?>
                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['product_id']; ?>)">
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

<!-- 促销横幅 -->
<div class="promo-banner">
    <h3>🎉 Free Shipping on Orders RM100+</h3>
    <p>Use code: <strong style="background: rgba(255,255,255,0.25); padding: 6px 15px; border-radius: 30px;">PXXSHIP</strong> at checkout</p>
    <a href="ViewAllProduct.php" class="promo-btn">Shop Now →</a>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function addToCart(productId) {
    $.ajax({
        url: 'memberProducts/add.php',
        method: 'POST',
        data: { product_id: productId, quantity: 1 },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (confirm('✓ Added to cart! Go to cart now?')) {
                    window.location.href = 'memberProducts/unified_cart.php';
                }
            } else {
                alert(response.error);
                if (response.error === 'Please login first') {
                    window.location.href = 'login.php';
                }
            }
        },
        error: function() {
            alert('Error adding to cart');
        }
    });
}

function addToWishlist(productId) {
    <?php if (isset($_SESSION['user_id'])): ?>
        $.ajax({
            url: 'member/wishlist_add.php?id=' + productId,
            method: 'GET',
            success: function() {
                alert('Added to wishlist!');
            },
            error: function() {
                alert('Error adding to wishlist');
            }
        });
    <?php else: ?>
        alert('Please login first to add to wishlist');
        window.location.href = 'login.php';
    <?php endif; ?>
}
</script>

<!-- 增强页脚 -->
<div class="footer-enhanced">
    <div class="footer-grid">
        <div class="footer-col">
            <h4>PXX Mall</h4>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
            <a href="#">Careers</a>
            <a href="#">Store Locator</a>
        </div>
        <div class="footer-col">
            <h4>Customer Service</h4>
            <a href="#">Help Center</a>
            <a href="#">Returns & Exchanges</a>
            <a href="#">Shipping Info</a>
            <a href="#">Payment Methods</a>
        </div>
        <div class="footer-col">
            <h4>My Account</h4>
            <a href="member/login.php">Sign In</a>
            <a href="member/orders_history.php">My Orders</a>
            <a href="member/profile.php">My Profile</a>
            <a href="member/wishlist.php">Wishlist</a>
        </div>
        <div class="footer-col">
            <h4>Follow Us</h4>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
    <div class="copyright">
        <p>PXX Mall Malaysia Sdn. Bhd.® [202001028493 (1357831-M)]</p>
        <p>Copyright © 2026 All Rights Reserved.</p>
    </div>
</div>

<?php require_once 'includes/_foot.php'; ?>
