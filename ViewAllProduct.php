<?php
session_start();
// 1. 严格路径检查：确保根据 index.php 风格引用 db.php
if (file_exists('includes/db.php')) {
    require_once 'includes/db.php';
} else {
    die("Database connection file missing at includes/db.php");
}
require_once 'includes/_head.php';

$current_user_id = $_SESSION['user_id'] ?? null;

// --- 2. 获取并清理参数 ---
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
// 兼容 category 和 gender_type 两个参数名
$gender_filter = $_GET['gender_type'] ?? $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? '';

// --- 3. 构建核心 SQL ---
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM favourites f WHERE f.product_id = p.product_id AND f.user_id = ?) as is_favourite 
        FROM products p WHERE 1=1";
$params = [$current_user_id];

// --- 4. 精准分类逻辑：防止 Men/Women 混淆 ---
if (!empty($gender_filter)) {
    if (stripos($gender_filter, 'Women') !== false) {
        $sql .= " AND p.product_id LIKE 'W%'";
    } elseif (stripos($gender_filter, 'Men') !== false) {
        $sql .= " AND p.product_id LIKE 'M%'";
    }

    // 具体子类过滤
    if (stripos($gender_filter, 'Shirt') !== false) {
        $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ?)";
        $params[] = "%shirt%"; $params[] = "%shirt%";
    } elseif (stripos($gender_filter, 'Pants') !== false) {
        $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ? OR p.name LIKE ?)";
        $params[] = "%pants%"; $params[] = "%pants%"; $params[] = "%skirt%";
    }
}

// 搜索框处理
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

// 排序处理
if ($sort == 'price_low') { $sql .= " ORDER BY p.price ASC"; }
elseif ($sort == 'price_high') { $sql .= " ORDER BY p.price DESC"; }
else { $sql .= " ORDER BY p.product_id DESC"; }

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<link rel="stylesheet" href="css/app.css">
<style>
    /* 修复字体截断的核心 CSS */
    .product-name { 
        min-height: 58px; /* 给足两行文字空间 */
        line-height: 1.5; /* 增加行高，防止字母底部被切断 */
        margin: 12px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-name a { color: #333; text-decoration: none; font-weight: bold; font-size: 18px; }

    /* 布局优化 */
    .view-all-container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
    .filter-section { 
        display: flex; gap: 15px; margin-bottom: 40px; justify-content: center; 
        background: #f9f9f9; padding: 20px; border-radius: 12px; border: 1px solid #eee;
    }
    .filter-select { padding: 12px; border-radius: 8px; border: 1px solid #ccc; min-width: 250px; font-size: 16px; }

    /* 图片点击区域 */
    .product-img-wrapper { position: relative; height: 350px; border-radius: 12px; overflow: hidden; background: #f0f0f0; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
    .product-card:hover img { transform: scale(1.05); }

    /* 价格与徽章 */
    .product-price { font-size: 24px; color: #e60012; font-weight: 800; margin: 5px 0; }
    .points-badge { background: #fff4e5; color: #ff9800; padding: 4px 10px; border-radius: 5px; font-weight: bold; }
</style>

<div class="view-all-container">
    <form method="GET" action="ViewAllProduct.php" class="filter-section" id="filterForm">
        <select name="gender_type" class="filter-select" onchange="this.form.submit()">
            <option value="">All Products</option>
            <optgroup label="Men">
                <option value="Men - Shirt" <?php echo ($gender_filter == 'Men' || $gender_filter == 'Men - Shirt') ? 'selected' : ''; ?>>Men's Shirt</option>
                <option value="Men - Pants" <?php echo $gender_filter == 'Men - Pants' ? 'selected' : ''; ?>>Men's Pants</option>
            </optgroup>
            <optgroup label="Women">
                <option value="Women - Shirt" <?php echo ($gender_filter == 'Women' || $gender_filter == 'Women - Shirt') ? 'selected' : ''; ?>>Women's Shirt</option>
                <option value="Women - Pants" <?php echo $gender_filter == 'Women - Pants' ? 'selected' : ''; ?>>Women's Pants</option>
            </optgroup>
        </select>

        <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="">Sort By Price</option>
            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Low to High</option>
            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>High to Low</option>
        </select>
    </form>

    <div class="products-grid">
        <?php foreach ($products as $product): 
            $images = preg_split('/[\s,]+/', $product['product_image']);
            $main_img = !empty($images[0]) ? $images[0] : 'default.jpg';
        ?>
            <div class="product-card">
                <div class="product-img-wrapper">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo htmlspecialchars($main_img); ?>" onerror="this.src='images/default.jpg'">
                    </a>
                    <div class="quick-buttons">
                        <button class="quick-wishlist <?php echo ($product['is_favourite'] > 0) ? 'active' : ''; ?>" 
                                data-id="<?php echo $product['product_id']; ?>">
                            <?php echo ($product['is_favourite'] > 0) ? '♥ Wishlisted' : '♡ Wishlist'; ?>
                        </button>
                    </div>
                </div>

                <div class="product-info">
                    <div class="product-name">
                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </div>
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                    <div class="product-meta">
                        <span class="points-badge">🎁 +<?php echo floor($product['price']); ?> pts</span>
                        <span style="font-size: 14px; color: #666;">Sold: <?php echo $product['sales_count'] ?? 0; ?></span>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <button type="button" class="add-to-cart-btn" data-id="<?php echo $product['product_id']; ?>">
                            🛒 Add to Cart
                        </button>
                    <?php else: ?>
                        <button type="button" class="add-to-cart-btn" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 1. 购物车异步逻辑：明确指定只监听 .add-to-cart-btn
    $('.add-to-cart-btn').on('click', function(e) {
        e.preventDefault();
        var pid = $(this).data('id');
        if(!pid) return;

        $.post('memberProducts/add.php', { product_id: pid, quantity: 1 }, function(response) {
            alert('Item added to cart!');
        }).fail(function() {
            alert('Error adding to cart.');
        });
    });

    // 2. 收藏逻辑：防止事件冒泡导致跳转
    $('.quick-wishlist').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var pid = $(this).data('id');
        var $btn = $(this);
        
        $.post('wishlist_add.php', { product_id: pid }, function() {
            $btn.toggleClass('active').text($btn.hasClass('active') ? '♥ Wishlisted' : '♡ Wishlist');
        });
    });
});
</script>

<?php require_once 'includes/_foot.php'; ?>