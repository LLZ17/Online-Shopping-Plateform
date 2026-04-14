<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

// 获取推荐产品（按库存量排序，优先展示库存充足的热门商品）
$stmt = $pdo->prepare("
    SELECT * FROM products 
    ORDER BY stock DESC, product_id DESC 
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
        首页样式 - PXX Mall 极简全身模特版
    ============================================ */
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        background: #FFFFFF; /* 整体背景改为纯白 */
        font-family: 'Poppins', 'Segoe UI', sans-serif;
    }
    
    /* ---------------------------------
       Hero 区域 - 轮播图 Slider
    --------------------------------- */
    .hero-slider-container {
        position: relative;
        margin: 20px 30px 40px 30px;
        border-radius: 8px;
        overflow: hidden;
        height: 75vh; 
        min-height: 600px;
        background: #FFFFFF;
    }

    .slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.8s ease-in-out;
        background-size: contain;
        background-position: center center;
        background-repeat: no-repeat;
        background-color: #FFFFFF;
        z-index: 1;
    }

    .slide.active {
        opacity: 1;
        z-index: 2;
    }

    /* 统一的红色按钮样式 */
    .btn-red-action {
        background: #e60012;
        color: white;
        padding: 14px 45px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 16px;
        transition: 0.3s;
        display: inline-block;
        box-shadow: 0 4px 15px rgba(230,0,18,0.3);
        border: none;
        cursor: pointer;
    }
    
    .btn-red-action:hover {
        background: #cc0010;
        box-shadow: 0 8px 20px rgba(230,0,18,0.4);
    }

    /* 轮播图中间底部的按钮 */
    .shop-now-bottom {
        position: absolute;
        bottom: 60px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 3;
    }

    .shop-now-bottom:hover {
        transform: translate(-50%, -2px);
    }

    /* 新增：左右切换翻页按钮 */
    .slider-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.15); /* 淡淡的黑底，在白背景上清晰可见 */
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
        z-index: 10;
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }

    .slider-nav:hover {
        background: rgba(0, 0, 0, 0.4);
        color: #e60012;
    }

    .slider-prev {
        left: 30px;
    }

    .slider-next {
        right: 30px;
    }

    /* 白色底色导航圆点 */
    .slider-dots {
        position: absolute;
        bottom: 25px;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: center;
        gap: 12px;
        z-index: 10;
    }

    .dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.4); /* 半透明白底 */
        border: 2px solid #FFFFFF; /* 纯白边框 */
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }

    .dot.active {
        background-color: #FFFFFF; /* 激活时全白 */
        transform: scale(1.3);
    }
    
    /* ---------------------------------
       分类卡片 - 加高拉直全身模特版
    --------------------------------- */
    .category-section {
        display: flex;
        gap: 30px;
        margin: 40px 30px;
        flex-wrap: wrap;
    }
    
    .category-card {
        flex: 1;
        min-width: 280px;
        /* 框拉直：去掉圆角 */
        border-radius: 0px; 
        overflow: hidden;
        position: relative;
        /* 框上下拉大：高度调大至 1100px，确保容纳全身 */
        height: 1100px; 
        cursor: pointer;
        transition: 0.3s;
        text-decoration: none;
        background-color: #FFFFFF;
    }

    /* 底部渐变黑影，保护红色的 Shop 按钮，调低高度避免遮挡模特 */
    .category-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 15%;
        background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%);
        z-index: 1;
        transition: 0.3s;
    }
    
    .category-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 35px rgba(0,0,0,0.15);
    }
    
    /* 确保以头部为基准，不切头 */
    .category-card.men {
        background: url('images/men.jpeg') center top / cover no-repeat;
    }
    
    .category-card.women {
        background: url('images/women.jpeg') center top / cover no-repeat;
    }
    
    .category-btn-container {
        position: absolute;
        bottom: 40px;
        left: 0;
        width: 100%;
        text-align: center;
        z-index: 2;
    }
    
    .category-card:hover .btn-red-action {
        transform: scale(1.05);
    }
    
    /* ---------------------------------
       商品排版与其他原版区域
    --------------------------------- */
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
    
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 28px;
        margin: 0 30px;
    }
    
    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        transition: 0.3s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        border: 1px solid #f0f0f0;
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
    
    .stock-status.low { color: #ff9800; }
    .stock-status.out { color: #f44336; }
    
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
        border-radius: 12px;
        min-width: 180px;
        transition: 0.3s;
        border: 1px solid #f0f0f0;
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
    
    /* 响应式设计 */
    @media (max-width: 992px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
    }
    
    @media (max-width: 768px) {
        .hero-slider-container {
            margin: 10px 15px 30px 15px;
            height: 65vh;
            min-height: 450px;
        }
        .slider-nav {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }
        .slider-prev { left: 15px; }
        .slider-next { right: 15px; }
        .category-section {
            margin: 20px 15px;
            flex-direction: column;
        }
        .category-card {
            height: 800px;
        }
        .products-grid {
            margin: 0 15px;
            gap: 15px;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        }
        .section-header { margin: 30px 15px 20px 15px; }
        .section-title { font-size: 22px; }
        .promo-banner { margin: 30px 15px; padding: 35px 20px; }
        .promo-banner h3 { font-size: 24px; }
        .features-section { margin: 30px 15px; gap: 15px; }
        .feature-item { padding: 15px; }
        .product-info { padding: 12px; }
        .product-name { font-size: 13px; height: 36px; }
        .product-price { font-size: 16px; }
    }
</style>

<div class="hero-slider-container">
    
    <div class="slide active" style="background-image: url('images/pxx.jpeg');">
        <a href="ViewAllProduct.php" class="btn-red-action shop-now-bottom">Shop Now →</a>
    </div>
    <div class="slide" style="background-image: url('images/pxx2.jpeg');">
        <a href="ViewAllProduct.php" class="btn-red-action shop-now-bottom">Shop Now →</a>
    </div>
    <div class="slide" style="background-image: url('images/pxx3.jpeg');">
        <a href="ViewAllProduct.php" class="btn-red-action shop-now-bottom">Shop Now →</a>
    </div>

    <button class="slider-nav slider-prev">❮</button>
    <button class="slider-nav slider-next">❯</button>

    <div class="slider-dots">
        <div class="dot active" data-index="0"></div>
        <div class="dot" data-index="1"></div>
        <div class="dot" data-index="2"></div>
    </div>
</div>

<div class="category-section">
    <a href="ViewAllProduct.php?category=Men" class="category-card men">
        <div class="category-btn-container">
            <span class="btn-red-action">Shop Men →</span>
        </div>
    </a>
    
    <a href="ViewAllProduct.php?category=Women" class="category-card women">
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

<div class="promo-banner">
    <h3>🎉 Free Shipping on Orders RM100+</h3>
    <p>Use code: <strong style="background: rgba(255,255,255,0.25); padding: 6px 15px; border-radius: 30px;">PXXSHIP</strong> at checkout</p>
    <a href="ViewAllProduct.php" class="promo-btn">Shop Now →</a>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
/* 轮播图逻辑 - 左右按键控制、小圆点控制 & 自动播放 */
document.addEventListener("DOMContentLoaded", function() {
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        currentSlide = index;
        
        slides[currentSlide].classList.add('active');
        dots[currentSlide].classList.add('active');
    }

    function nextSlide() {
        let nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }

    function prevSlide() {
        let prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }

    // 绑定左右按键事件
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetInterval();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetInterval();
        });
    }

    // 点击圆点切换图片
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            resetInterval();
        });
    });

    // 自动播放 (10000ms = 10秒)
    function startInterval() {
        slideInterval = setInterval(nextSlide, 10000);
    }

    function resetInterval() {
        clearInterval(slideInterval);
        startInterval();
    }

    // 启动自动播放
    startInterval();
});

/* 购物车与收藏逻辑 */
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

</div>

<?php require_once 'includes/_foot.php'; ?>