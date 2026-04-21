<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

$current_user_id = $_SESSION['user_id'] ?? null;

// --- 1. 获取参数 ---
$cat_key = $_GET['cat_key'] ?? '';         
$category_main = $_GET['category'] ?? ''; 
$sort = $_GET['sort'] ?? '';               
$search = isset($_GET['query']) ? trim($_GET['query']) : '';

// --- 2. 构建 SQL (确保包含 rating, stock, sales_count) ---
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM favourites f WHERE f.product_id = p.product_id AND f.user_id = ?) as is_favourite 
        FROM products p WHERE 1=1";
$params = [$current_user_id];

// --- 3. 核心过滤逻辑 (ID 编码逻辑) ---
if (!empty($cat_key)) {
    $sql .= " AND LEFT(p.product_id, 2) = ?";
    $params[] = strtoupper($cat_key);
} 
elseif (!empty($category_main)) {
    if (strtolower($category_main) == 'men') {
        $sql .= " AND p.product_id LIKE 'M%'";
    } elseif (strtolower($category_main) == 'women') {
        $sql .= " AND p.product_id LIKE 'W%'";
    }
}

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ?)";
    $params[] = "%$search%"; 
    $params[] = "%$search%";
}

// --- 4. 排序 ---
if ($sort == 'price_low') { 
    $sql .= " ORDER BY p.price ASC"; 
} elseif ($sort == 'price_high') { 
    $sql .= " ORDER BY p.price DESC"; 
} else { 
    $sql .= " ORDER BY p.product_id DESC"; 
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<style>
    .view-all-container { max-width: 1300px; margin: 30px auto; padding: 0 20px; font-family: 'Segoe UI', sans-serif; background: #fff; }
    
    /* 工具栏 */
    .toolbar { 
        display: flex; gap: 15px; margin-bottom: 35px; align-items: center; justify-content: center; 
        background: #f9f9f9; padding: 20px; border-radius: 12px; border: 1px solid #eee; 
    }
    .btn-back { text-decoration: none; background: #333; color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: bold; }
    .btn-reset { text-decoration: none; background: #fff; color: #ff4757; padding: 10px 15px; border-radius: 6px; border: 1px solid #ff4757; font-weight: bold; }

    .filter-select { padding: 12px; border-radius: 8px; border: 1px solid #ccc; min-width: 200px; font-size: 14px; }

    /* 商品网格 */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
    
    .product-card { 
        background: #fff; border-radius: 12px; overflow: hidden; 
        box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; position: relative; border: 1px solid #f0f0f0; 
    }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    
    .product-img-wrapper { height: 320px; overflow: hidden; background: #f0f0f0; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }

    /* Wishlist 样式 */
    .wishlist-btn { 
        position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.9); border: none; 
        width: 35px; height: 35px; border-radius: 50%; cursor: pointer; z-index: 10; font-size: 18px;
        display: flex; align-items: center; justify-content: center; transition: 0.3s;
    }
    .wishlist-btn.active { color: #ff4757; background: #fff; box-shadow: 0 0 10px rgba(255,71,87,0.3); }

    /* 商品信息区 */
    .product-info { padding: 15px; }
    
    /* 解决标题截断 */
    .product-name { 
        height: 44px; line-height: 1.4; margin-bottom: 8px; 
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; 
        font-weight: 600; font-size: 16px;
    }
    .product-name a { color: #333; text-decoration: none; }

    /* Rating 星级样式 */
    .rating-box { color: #ffbc00; font-size: 13px; margin-bottom: 8px; }
    .rating-count { color: #888; font-size: 12px; margin-left: 5px; }

    .product-price { font-size: 20px; color: #e60012; font-weight: 800; margin-bottom: 10px; }

    /* Stock & Sales 展示 */
    .stats-row { display: flex; justify-content: space-between; font-size: 12px; color: #666; margin-bottom: 12px; }
    .stock-badge { color: #2ecc71; font-weight: bold; }
    .sales-badge { background: #fff0f0; color: #ff4757; padding: 2px 6px; border-radius: 4px; font-weight: bold; }

    .add-btn { 
        width: 100%; padding: 12px; background: #000; color: #fff; border: none; 
        border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s; 
    }
    .add-btn:hover { background: #333; }
</style>

<div class="view-all-container">
    <div class="toolbar">
        <a href="index.php" class="btn-back">⬅ Back</a>
        
        <form method="GET" action="ViewAllProduct.php" style="display: flex; gap: 10px; align-items: center;">
            <?php if(!empty($search)): ?>
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($search); ?>">
            <?php endif; ?>
            <?php if(!empty($category_main) && empty($cat_key)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_main); ?>">
            <?php endif; ?>

            <select name="cat_key" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <optgroup label="MEN">
                    <option value="MS" <?php echo $cat_key == 'MS' ? 'selected' : ''; ?>>Men's Shirt (MS)</option>
                    <option value="MP" <?php echo $cat_key == 'MP' ? 'selected' : ''; ?>>Men's Pants (MP)</option>
                    <option value="MO" <?php echo $cat_key == 'MO' ? 'selected' : ''; ?>>Men's Others (MO)</option>
                </optgroup>
                <optgroup label="WOMEN">
                    <option value="WS" <?php echo $cat_key == 'WS' ? 'selected' : ''; ?>>Women's Shirt (WS)</option>
                    <option value="WP" <?php echo $cat_key == 'WP' ? 'selected' : ''; ?>>Women's Pants (WP)</option>
                    <option value="WO" <?php echo $cat_key == 'WO' ? 'selected' : ''; ?>>Women's Others (WO)</option>
                </optgroup>
            </select>

            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="">Sort By Price</option>
                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>

            <?php if(!empty($cat_key) || !empty($sort) || !empty($category_main)): ?>
                <a href="ViewAllProduct.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="products-grid">
        <?php foreach ($products as $product): 
            $display_img = trim(explode(',', $product['product_image'])[0]);
            // 处理评分，如果没有该字段则默认为 5.0
            $rating = $product['rating'] ?? 5.0;
            $sales = $product['sales_count'] ?? 0;
            $stock = $product['stock'] ?? 0;
        ?>
            <div class="product-card">
                <button type="button" class="wishlist-btn <?php echo ($product['is_favourite'] > 0) ? 'active' : ''; ?>" 
                        data-id="<?php echo $product['product_id']; ?>">
                    <?php echo ($product['is_favourite'] > 0) ? '♥' : '♡'; ?>
                </button>

                <div class="product-img-wrapper">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo htmlspecialchars($display_img); ?>" onerror="this.src='images/default.jpg'">
                    </a>
                </div>

                <div class="product-info">
                    <div class="rating-box">
                        <?php 
                        for($i=1; $i<=5; $i++) {
                            echo $i <= $rating ? '★' : '☆';
                        }
                        ?>
                        <span class="rating-count">(<?php echo $rating; ?>)</span>
                    </div>

                    <div class="product-name">
                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </div>
                    
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                    
                    <div class="stats-row">
                        <span class="sales-badge"><?php echo $sales; ?> Sold</span>
                        <span class="stock-badge"><?php echo $stock > 0 ? "In Stock ($stock)" : "Out of Stock"; ?></span>
                    </div>

                    <button type="button" class="add-btn" data-id="<?php echo $product['product_id']; ?>">🛒 Add to Cart</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    $('.wishlist-btn').on('click', function() {
        if(!isLoggedIn) { alert('Please login first!'); return; }
        var $btn = $(this);
        $.post('wishlist_add.php', { product_id: $btn.data('id') }, function() {
            $btn.toggleClass('active');
            $btn.html($btn.hasClass('active') ? '♥' : '♡');
        });
    });

    $('.add-btn').on('click', function() {
        $.post('memberProducts/add.php', { product_id: $(this).data('id'), quantity: 1 }, function() {
            alert('Added to cart!');
        });
    });
});
</script>

<?php require_once 'includes/_foot.php'; ?>