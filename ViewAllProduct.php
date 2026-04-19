<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

// --- 1. 获取所有参数 ---
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : ''; // 兼容旧按钮
$gender_type = isset($_GET['gender_type']) ? trim($_GET['gender_type']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// --- 2. 智能搜索功能 (保留你原有的逻辑) ---
if (!empty($search)) {
    $search_lower = strtolower($search);
    $is_men = false;
    $is_women = false;
    $product_type_search = '';
    $product_category_search = '';
    
    // 性别检测
    $men_keywords = ['men', 'male', 'boy', 'guy', 'gentleman', 'mens'];
    $women_keywords = ['women', 'female', 'girl', 'lady', 'womens', 'woman'];
    foreach ($men_keywords as $kw) { if (strpos($search_lower, $kw) !== false) { $is_men = true; break; } }
    foreach ($women_keywords as $kw) { if (strpos($search_lower, $kw) !== false) { $is_women = true; break; } }
    
    // 产品类型检测
    $shirt_keywords = ['shirt', 'tee', 't-shirt', 'tshirt', 'top', 'blouse', 'polo', 'jersey', 'hoodie', 'sweater', 'pullover'];
    $pants_keywords = ['pants', 'pant', 'trousers', 'jeans', 'denim', 'jogger', 'cargo', 'chino', 'legging', 'shorts', 'short', 'bermuda'];
    $skirt_keywords = ['skirt', 'dress', 'maxi', 'midi', 'mini', 'pleated', 'a-line', 'bodycon'];
    $jacket_keywords = ['jacket', 'coat', 'blazer', 'windbreaker', 'bomber', 'vest'];
    
    foreach ($shirt_keywords as $kw) { if (strpos($search_lower, $kw) !== false) { $product_type_search = 'shirt'; break; } }
    if (empty($product_type_search)) { foreach ($pants_keywords as $kw) { if (strpos($search_lower, $kw) !== false) { $product_type_search = 'pants'; break; } } }
    if (empty($product_type_search)) { foreach ($skirt_keywords as $kw) { if (strpos($search_lower, $kw) !== false) { $product_type_search = 'skirt'; break; } } }
    if (empty($product_type_search)) { foreach ($jacket_keywords as $kw) { if (strpos($search_lower, $kw) !== false) { $product_type_search = 'jacket'; break; } } }

    // 品牌检测
    $brand_keywords = ['hype', 'tntco', 'ric', 'lovito', 'jeep', 'valunee', 'hitty'];
    foreach ($brand_keywords as $kw) { if (strpos($search_lower, $kw) !== false) { $product_category_search = $kw; break; } }

    $sql .= " AND (";
    $search_conditions = [];
    if ($is_men && !$is_women) { $search_conditions[] = "product_id LIKE 'M%'"; }
    elseif ($is_women && !$is_men) { $search_conditions[] = "product_id LIKE 'W%'"; }
    if (!empty($product_category_search)) { $search_conditions[] = "(name LIKE ? OR keywords LIKE ?)"; $params[] = "%$product_category_search%"; $params[] = "%$product_category_search%"; }
    if (!empty($product_type_search)) { $search_conditions[] = "(name LIKE ? OR keywords LIKE ?)"; $params[] = "%$product_type_search%"; $params[] = "%$product_type_search%"; }
    $search_conditions[] = "(name LIKE ? OR keywords LIKE ? OR description LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    $sql .= implode(" OR ", $search_conditions) . ")";
}

// --- 3. Gender & Type 下拉筛选逻辑 (重点修改) ---
if (!empty($gender_type)) {
    // 处理性别前缀
    if (strpos($gender_type, 'Men') !== false) {
        $sql .= " AND product_id LIKE 'M%'";
    } elseif (strpos($gender_type, 'Women') !== false) {
        $sql .= " AND product_id LIKE 'W%'";
    }
    // 处理具体类型关键词
    if (strpos($gender_type, 'Shirt') !== false) {
        $sql .= " AND (name LIKE ? OR keywords LIKE ?)";
        $params[] = "%shirt%"; $params[] = "%shirt%";
    } elseif (strpos($gender_type, 'Pants') !== false) {
        $sql .= " AND (name LIKE ? OR keywords LIKE ? OR name LIKE ?)";
        $params[] = "%pants%"; $params[] = "%pants%"; $params[] = "%skirt%"; // 包含裙子逻辑
    }
} elseif (!empty($category) && $category != 'All') { // 兼容旧分类按钮
    if ($category == 'Men') { $sql .= " AND product_id LIKE 'M%'"; }
    elseif ($category == 'Women') { $sql .= " AND product_id LIKE 'W%'"; }
}

// --- 4. 排序逻辑 (Price Range) ---
if ($sort == 'price_low') {
    $sql .= " ORDER BY price ASC";
} elseif ($sort == 'price_high') {
    $sql .= " ORDER BY price DESC";
} else {
    $sql .= " ORDER BY product_id DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// 搜索建议
$search_suggestion = '';
if (!empty($search) && empty($products)) {
    $search_suggestion = 'No products found. Try: shirt, pants, dress, hoodie';
}
?>

<style>
    .products-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    h1 { color: #333; margin-bottom: 20px; }
    .filter-bar { display: flex; gap: 15px; margin-bottom: 30px; justify-content: center; flex-wrap: wrap; align-items: center; background: #fdfdfd; padding: 20px; border-radius: 12px; border: 1px solid #eee; }
    .filter-select { padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; min-width: 200px; outline: none; background: white; }
    .category-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    .category-btn { padding: 10px 25px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; text-decoration: none; color: #333; transition: 0.3s; }
    .category-btn.active { background: #e60012; color: white; border-color: #e60012; }
    .clear-btn { padding: 10px 25px; background: #666; color: white; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; }
    
    /* Grid & Card */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-top: 20px; }
    .product-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: 0.3s; display: flex; flex-direction: column; }
    .product-card:hover { transform: translateY(-5px); }
    .product-card img { width: 100%; height: 280px; object-fit: cover; }
    
    /* Info Section */
    .product-info { padding: 15px; text-align: center; flex-grow: 1; display: flex; flex-direction: column; }
    .product-name { font-size: 16px; font-weight: bold; margin-bottom: 8px; height: 40px; overflow: hidden; }
    .product-name a { text-decoration: none; color: #333; }
    
    /* Rating & Sold */
    .product-stats { display: flex; justify-content: space-between; font-size: 13px; color: #666; margin-bottom: 10px; padding: 0 5px; }
    .rating { color: #ff9800; font-weight: bold; }
    .sold { color: #888; }
    
    .price { color: #e60012; font-size: 22px; font-weight: bold; margin-bottom: 5px; }
    .points { color: #ff9800; font-size: 12px; margin-bottom: 5px; }
    .stock { font-size: 12px; margin-bottom: 10px; }
    .in-stock { color: green; }
    .out-stock { color: red; }
    
    .add-to-cart { width: 100%; background: #e60012; color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: 0.3s; }
    .add-to-cart:hover { background: #cc0010; }
    .no-results { text-align: center; padding: 50px; color: #999; }
</style>

<div class="products-container">
    <a href="index.php" class="back-link">← Back to Home</a>
    <h1>All Products</h1>
    
    <?php if (!empty($search)): ?>
    <div class="search-info" style="text-align:center; margin-bottom:15px;">
        🔍 Results for: <strong><?php echo htmlspecialchars($search); ?></strong> (<?php echo count($products); ?>)
    </div>
    <?php endif; ?>

    <form method="GET" action="ViewAllProduct.php" class="filter-bar">
        <?php if($search): ?> <input type="hidden" name="query" value="<?php echo htmlspecialchars($search); ?>"> <?php endif; ?>

        <select name="gender_type" class="filter-select" onchange="this.form.submit()">
            <option value="" disabled <?php echo empty($gender_type) ? 'selected' : ''; ?>>Select Gender & Type...</option>
            <option value="">All Categories</option>
            <optgroup label="Men's Section">
                <option value="Men - Shirt" <?php echo $gender_type == 'Men - Shirt' ? 'selected' : ''; ?>>Men - Shirt</option>
                <option value="Men - Pants" <?php echo $gender_type == 'Men - Pants' ? 'selected' : ''; ?>>Men - Pants</option>
                <option value="Men - Others" <?php echo $gender_type == 'Men - Others' ? 'selected' : ''; ?>>Men - Others</option>
            </optgroup>
            <optgroup label="Women's Section">
                <option value="Women - Shirt" <?php echo $gender_type == 'Women - Shirt' ? 'selected' : ''; ?>>Women - Shirt</option>
                <option value="Women - Pants and Skirt" <?php echo $gender_type == 'Women - Pants and Skirt' ? 'selected' : ''; ?>>Women - Pants and Skirt</option>
                <option value="Women - Others" <?php echo $gender_type == 'Women - Others' ? 'selected' : ''; ?>>Women - Others</option>
            </optgroup>
        </select>

        <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="">Default Sorting</option>
            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High ↑</option>
            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low ↓</option>
        </select>

        <?php if ($search || $gender_type || $sort || $category): ?>
            <a href="ViewAllProduct.php" class="clear-btn">✖ Clear</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($products)): ?>
        <div class="no-results">
            <p>No products found.</p>
            <?php if ($search_suggestion): ?> <p style="color:#e60012;">💡 <?php echo $search_suggestion; ?></p> <?php endif; ?>
            <a href="ViewAllProduct.php" style="color: #e60012;">View all products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): 
                // --- 图片处理逻辑 ---
                // 自动识别 main 图片，或者拿第一张
                $images = preg_split('/[\s,]+/', $product['product_image']);
                $main_img = !empty($images[0]) ? $images[0] : 'default.jpg';
                foreach($images as $img) {
                    if (stripos($img, 'main') !== false) {
                        $main_img = $img;
                        break;
                    }
                }
            ?>
                <div class="product-card">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo htmlspecialchars($main_img); ?>" onerror="this.src='images/default.jpg'">
                    </a>
                    <div class="product-info">
                        <div class="product-name">
                            <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </div>

                        <div class="product-stats">
                            <span class="rating">⭐ <?php echo number_format($product['rating'], 1); ?></span>
                            <span class="sold"><?php echo number_format($product['sales_count']); ?> sold</span>
                        </div>

                        <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
                        <div class="points">🎁 Earn <?php echo floor($product['price']); ?> pts</div>
                        <div class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ')' : '✗ Out of Stock'; ?>
                        </div>
                        
                        <button class="add-to-cart" data-id="<?php echo $product['product_id']; ?>" <?php echo $product['stock'] <= 0 ? 'disabled style="background:#ccc"' : ''; ?>>
                            <?php echo $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.add-to-cart').click(function() {
        var productId = $(this).data('id');
        var $btn = $(this);
        
        $btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: 'memberProducts/add.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $btn.text('✓ Added!').css('background', '#28a745');
                    if (confirm('Item added! Go to shopping cart?')) {
                        window.location.href = 'unified_cart.php'; // 调去你的购物车页面
                    } else {
                        setTimeout(function() { 
                            $btn.prop('disabled', false).text('Add to Cart').css('background', '#e60012'); 
                        }, 2000);
                    }
                } else {
                    alert(response.error);
                    $btn.prop('disabled', false).text('Add to Cart');
                    if (response.error === 'Please login first') {
                        window.location.href = 'login.php';
                    }
                }
            },
            error: function() {
                alert('Error adding to cart');
                $btn.prop('disabled', false).text('Add to Cart');
            }
        });
    });
});
</script>

<?php require_once 'includes/_foot.php'; ?>