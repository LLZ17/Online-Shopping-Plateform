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

// --- 2. SQL 逻辑 ---
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM favourites f WHERE f.product_id = p.product_id AND f.user_id = ?) as is_favourite 
        FROM products p WHERE 1=1";
$params = [$current_user_id];

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

if ($sort == 'price_low') { $sql .= " ORDER BY p.price ASC"; } 
elseif ($sort == 'price_high') { $sql .= " ORDER BY p.price DESC"; } 
else { $sql .= " ORDER BY p.product_id DESC"; }

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<style>
    .view-all-container { max-width: 1300px; margin: 30px auto; padding: 0 20px; font-family: 'Segoe UI', sans-serif; }
    .search-result-bar { background: #f8f9fa; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #e60012; }
    .search-result-bar h2 { margin: 0; font-size: 18px; color: #333; }
    .search-result-bar .search-term { color: #e60012; font-weight: 700; }
    .no-results { text-align: center; padding: 60px 20px; color: #666; font-size: 16px; }
    
    .toolbar { display: flex; gap: 15px; margin-bottom: 35px; align-items: center; justify-content: center; background: #f9f9f9; padding: 20px; border-radius: 12px; border: 1px solid #eee; }
    .btn-back { text-decoration: none; background: #333; color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: bold; }
    .btn-reset { text-decoration: none; background: #fff; color: #ff4757; padding: 10px 15px; border-radius: 6px; border: 1px solid #ff4757; font-weight: bold; }
    .filter-select { padding: 12px; border-radius: 8px; border: 1px solid #ccc; min-width: 200px; font-size: 14px; }

    /* 网格与卡片 */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
    .product-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; position: relative; border: 1px solid #f0f0f0; display: flex; flex-direction: column; height: 100%; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    
    .product-img-wrapper { height: 320px; overflow: hidden; background: #f0f0f0; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    
    /* 收藏按钮 */
    .wishlist-btn { 
        position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.9); 
        border: 1px solid #eee; width: 36px; height: 36px; border-radius: 50%; 
        cursor: pointer; z-index: 10; font-size: 18px; display: flex; 
        align-items: center; justify-content: center; transition: 0.2s; color: #ccc; 
    }
    .wishlist-btn.active { color: #ff4757; border-color: #ff4757; }

    /* 🌟 核心修复：产品信息部分 */
    .product-info { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
    
    .product-name { 
        margin-bottom: 8px; font-weight: 600; font-size: 15px; line-height: 1.4;
        height: 2.8em; overflow: hidden; display: -webkit-box;
        -webkit-line-clamp: 2; -webkit-box-orient: vertical; 
    }
    .product-name a { color: #333; text-decoration: none; }
    .product-name a:hover { color: #e60012; }

    .product-price { font-size: 20px; color: #e60012; font-weight: 800; margin-bottom: 5px; }
    
    /* 🌟 新增评分样式 */
    .rating-row { margin-bottom: 10px; color: #ffbc00; font-size: 12px; }
    .rating-count { color: #999; margin-left: 5px; }

    .stats-row { display: flex; align-items: center; gap: 10px; font-size: 12px; margin-bottom: 15px; }
    .sales-badge { color: #ff4757; font-weight: 600; background: #fff0f0; padding: 2px 8px; border-radius: 4px; }
    .stock-badge { font-weight: 600; }
    .stock-in { color: #2ecc71; }
    .stock-out { color: #95a5a6; }

    .add-btn { width: 100%; padding: 12px; background: #000; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: auto; transition: 0.2s; }
    .add-btn:hover:not(:disabled) { background: #333; }
    .add-btn:disabled { background: #ccc; cursor: not-allowed; }
</style>

<div class="view-all-container">
    <?php if (!empty($search)): ?>
    <div class="search-result-bar">
        <h2>Search results for: <span class="search-term">"<?php echo htmlspecialchars($search); ?>"</span> 
        (<?php echo count($products); ?> items found)</h2>
    </div>
    <?php endif; ?>

    <div class="toolbar">
        <a href="index.php" class="btn-back">⬅ Back</a>
        <form method="GET" action="ViewAllProduct.php" style="display: flex; gap: 10px; align-items: center;">
            <?php if(!empty($search)): ?>
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($search); ?>">
            <?php endif; ?>
            <?php if(!empty($category_main)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_main); ?>">
            <?php endif; ?>
            <select name="cat_key" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php if (strtolower($category_main) !== 'women'): ?>
                <optgroup label="MEN">
                    <option value="MS" <?php echo $cat_key == 'MS' ? 'selected' : ''; ?>>Men's Shirt </option>
                    <option value="MP" <?php echo $cat_key == 'MP' ? 'selected' : ''; ?>>Men's Pants </option>
                    <option value="MO" <?php echo $cat_key == 'MO' ? 'selected' : ''; ?>>Men's Others </option>
                </optgroup>
                <?php endif; ?>
                <?php if (strtolower($category_main) !== 'men'): ?>
                <optgroup label="WOMEN">
                    <option value="WS" <?php echo $cat_key == 'WS' ? 'selected' : ''; ?>>Women's Shirt </option>
                    <option value="WP" <?php echo $cat_key == 'WP' ? 'selected' : ''; ?>>Women's Pants </option>
                    <option value="WO" <?php echo $cat_key == 'WO' ? 'selected' : ''; ?>>Women's Others </option>
                </optgroup>
                <?php endif; ?>
            </select>
            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="">Sort By Price</option>
                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
            <a href="ViewAllProduct.php" class="btn-reset">Reset</a>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <div class="no-results">
            <i class="fa-solid fa-magnifying-glass" style="font-size: 40px; margin-bottom: 20px;"></i>
            <h3>No products found</h3>
            <p>Try different keywords or browse all categories</p>
        </div>
    <?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $product): 
            $img_path = !empty($product['product_image']) ? trim(explode(',', $product['product_image'])[0]) : 'default.jpg';
            $stock = (int)$product['stock'];
        ?>
            <div class="product-card">
                <button type="button" 
                        class="wishlist-btn <?php echo ($product['is_favourite'] > 0) ? 'active' : ''; ?>" 
                        data-id="<?php echo htmlspecialchars($product['product_id']); ?>"
                        onclick="toggleWishlist(this)">
                    <?php echo ($product['is_favourite'] > 0) ? '♥' : '♡'; ?>
                </button>

                <div class="product-img-wrapper">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo htmlspecialchars($img_path); ?>" onerror="this.src='images/default.jpg'">
                    </a>
                </div>

                <div class="product-info">
                    <div class="product-name">
                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </div>
                    
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>

                    <div class="rating-row">
                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                        <span class="rating-count">(<?php echo rand(10, 50); ?>)</span>
                    </div>

                    <div class="stats-row">
                        <span class="sales-badge"><?php echo $product['sales_count'] ?? 0; ?> Sold</span>
                        <span class="stock-badge <?php echo ($stock > 0) ? 'stock-in' : 'stock-out'; ?>">
                            <?php echo ($stock > 0) ? $stock . " In Stock" : "Out of Stock"; ?>
                        </span>
                    </div>
                    
                    <button type="button" class="add-btn" 
                            data-id="<?php echo $product['product_id']; ?>" 
                            <?php echo ($stock <= 0) ? 'disabled' : ''; ?>
                            onclick="addToCart('<?php echo $product['product_id']; ?>')">
                        <?php echo ($stock > 0) ? '🛒 Add to Cart' : 'Sold Out'; ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function toggleWishlist(btn) {
    <?php if (!isset($_SESSION['user_id'])): ?>
        alert('Please login first!');
        window.location.href = 'login.php';
        return;
    <?php endif; ?>
    
    var $btn = $(btn);
    var pid = $btn.data('id');
    
    if ($btn.prop('disabled')) return;
    $btn.prop('disabled', true);
    
    $.ajax({
        url: 'member/wishlist_toggle.php', 
        type: 'POST',
        data: { p_id: pid }, 
        dataType: 'json',
        success: function(res) {
            if(res.status === 'added') {
                $btn.addClass('active').html('♥');
                if(typeof updateHeaderWishlist === "function") updateHeaderWishlist(1);
            } else if(res.status === 'removed') {
                $btn.removeClass('active').html('♡');
                if(typeof updateHeaderWishlist === "function") updateHeaderWishlist(-1);
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr.responseText);
        },
        complete: function() {
            $btn.prop('disabled', false);
        }
    });
}

function addToCart(pid) {
    $.post('memberProducts/add.php', { 
        product_id: pid, 
        quantity: 1 
    }, function(res) {
        alert('Added to cart!');
        // 这里可以加上更新导航栏购物车数量的逻辑
    });
}
</script>

<?php require_once 'includes/_foot.php'; ?>