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
    $params[] = "%$search%"; $params[] = "%$search%";
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
    .toolbar { display: flex; gap: 15px; margin-bottom: 35px; align-items: center; justify-content: center; background: #f9f9f9; padding: 20px; border-radius: 12px; border: 1px solid #eee; }
    .btn-back { text-decoration: none; background: #333; color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: bold; }
    .btn-reset { text-decoration: none; background: #fff; color: #ff4757; padding: 10px 15px; border-radius: 6px; border: 1px solid #ff4757; font-weight: bold; }
    .filter-select { padding: 12px; border-radius: 8px; border: 1px solid #ccc; min-width: 200px; font-size: 14px; }

    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
    .product-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; position: relative; border: 1px solid #f0f0f0; display: flex; flex-direction: column; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    
    .product-img-wrapper { height: 320px; overflow: hidden; background: #f0f0f0; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }

    /* Wishlist 样式 */
    .wishlist-btn { position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.9); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; z-index: 10; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: 0.2s; color: #ccc; }
    .wishlist-btn.active { color: #ff4757; background: #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1); }

    .product-info { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
    .product-name { margin-bottom: 10px; font-weight: 600; font-size: 16px; word-wrap: break-word; line-height: 1.4; }
    .product-name a { color: #333; text-decoration: none; }

    .product-price { font-size: 20px; color: #e60012; font-weight: 800; margin-bottom: 10px; }
    .stats-row { display: flex; align-items: center; gap: 10px; font-size: 12px; margin-bottom: 15px; }
    .sales-badge { color: #ff4757; font-weight: 600; background: #fff0f0; padding: 2px 8px; border-radius: 4px; }
    .stock-badge { font-weight: 600; }
    .stock-in { color: #2ecc71; }
    .stock-out { color: #95a5a6; }

    .add-btn { width: 100%; padding: 12px; background: #000; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: auto; }
    .add-btn:disabled { background: #ccc; cursor: not-allowed; }
</style>

<div class="view-all-container">
    <div class="toolbar">
        <a href="index.php" class="btn-back">⬅ Back</a>
        <form method="GET" action="ViewAllProduct.php" style="display: flex; gap: 10px; align-items: center;">
            <?php if(!empty($category_main)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_main); ?>">
            <?php endif; ?>
            <select name="cat_key" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php if (strtolower($category_main) !== 'women'): ?>
                <optgroup label="MEN">
                    <option value="MS" <?php echo $cat_key == 'MS' ? 'selected' : ''; ?>>Men's Shirt (MS)</option>
                    <option value="MP" <?php echo $cat_key == 'MP' ? 'selected' : ''; ?>>Men's Pants (MP)</option>
                    <option value="MO" <?php echo $cat_key == 'MO' ? 'selected' : ''; ?>>Men's Others (MO)</option>
                </optgroup>
                <?php endif; ?>
                <?php if (strtolower($category_main) !== 'men'): ?>
                <optgroup label="WOMEN">
                    <option value="WS" <?php echo $cat_key == 'WS' ? 'selected' : ''; ?>>Women's Shirt (WS)</option>
                    <option value="WP" <?php echo $cat_key == 'WP' ? 'selected' : ''; ?>>Women's Pants (WP)</option>
                    <option value="WO" <?php echo $cat_key == 'WO' ? 'selected' : ''; ?>>Women's Others (WO)</option>
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

    <div class="products-grid">
        <?php foreach ($products as $product): 
            $display_img = trim(explode(',', $product['product_image'])[0]);
            $stock = (int)$product['stock'];
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
                    <div class="product-name">
                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </div>
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                    <div class="stats-row">
                        <span class="sales-badge"><?php echo $product['sales_count'] ?? 0; ?> Sold</span>
                        <span class="stock-badge <?php echo ($stock > 0) ? 'stock-in' : 'stock-out'; ?>">
                            <?php echo ($stock > 0) ? $stock . " In Stock" : "Out of Stock"; ?>
                        </span>
                    </div>
                    <button type="button" class="add-btn" data-id="<?php echo $product['product_id']; ?>" <?php echo ($stock <= 0) ? 'disabled' : ''; ?>>
                        <?php echo ($stock > 0) ? '🛒 Add to Cart' : 'Sold Out'; ?>
                    </button>
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
        var pid = $btn.data('id'); // 获取商品 ID

        // 发送 AJAX，参数名统一为 product_id
        $.ajax({
            url: 'wishlist_add.php',
            type: 'POST',
            data: { product_id: pid },
            dataType: 'json',
            success: function(res) {
                if(res.status === 'added') {
                    $btn.addClass('active').text('♥');
                } else if(res.status === 'removed') {
                    $btn.removeClass('active').text('♡');
                } else if(res.status === 'not_logged_in') {
                    alert('Please login first!');
                }
            },
            error: function() {
                alert('Error connecting to server.');
            }
        });
    });

    $('.add-btn').on('click', function() {
        $.post('memberProducts/add.php', { product_id: $(this).data('id'), quantity: 1 }, function() {
            alert('Added to cart!');
        });
    });
});
</script>