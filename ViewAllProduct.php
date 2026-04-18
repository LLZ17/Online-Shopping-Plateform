<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

// 获取搜索参数
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// 智能搜索功能
if (!empty($search)) {
    $search_lower = strtolower($search);
    
    // 性别检测关键词
    $is_men = false;
    $is_women = false;
    $product_type = '';
    $product_category = '';
    
    // === 1. 性别检测 ===
    $men_keywords = ['men', 'male', 'boy', 'guy', 'gentleman', 'mens'];
    $women_keywords = ['women', 'female', 'girl', 'lady', 'womens', 'woman'];
    
    foreach ($men_keywords as $kw) {
        if (strpos($search_lower, $kw) !== false) { $is_men = true; break; }
    }
    foreach ($women_keywords as $kw) {
        if (strpos($search_lower, $kw) !== false) { $is_women = true; break; }
    }
    
    // === 2. 产品类型检测 ===
    // 上衣类
    $shirt_keywords = ['shirt', 'tee', 't-shirt', 'tshirt', 'top', 'blouse', 'polo', 'jersey', 'hoodie', 'sweater', 'pullover'];
    // 裤装类
    $pants_keywords = ['pants', 'pant', 'trousers', 'jeans', 'denim', 'jogger', 'cargo', 'chino', 'legging', 'shorts', 'short', 'bermuda'];
    // 裙装类
    $skirt_keywords = ['skirt', 'dress', 'maxi', 'midi', 'mini', 'pleated', 'a-line', 'bodycon'];
    // 外套类
    $jacket_keywords = ['jacket', 'coat', 'blazer', 'windbreaker', 'bomber', 'vest'];
    
    foreach ($shirt_keywords as $kw) {
        if (strpos($search_lower, $kw) !== false) { $product_type = 'shirt'; break; }
    }
    if (empty($product_type)) {
        foreach ($pants_keywords as $kw) {
            if (strpos($search_lower, $kw) !== false) { $product_type = 'pants'; break; }
        }
    }
    if (empty($product_type)) {
        foreach ($skirt_keywords as $kw) {
            if (strpos($search_lower, $kw) !== false) { $product_type = 'skirt'; break; }
        }
    }
    if (empty($product_type)) {
        foreach ($jacket_keywords as $kw) {
            if (strpos($search_lower, $kw) !== false) { $product_type = 'jacket'; break; }
        }
    }
    
    // === 3. 品牌检测 ===
    $brand_keywords = ['hype', 'tntco', 'ric', 'lovito', 'jeep', 'valunee', 'hitty'];
    foreach ($brand_keywords as $kw) {
        if (strpos($search_lower, $kw) !== false) { $product_category = $kw; break; }
    }
    
    // === 4. 构建搜索条件 ===
    $sql .= " AND (";
    $conditions = [];
    
    // 按性别搜索（通过 product_id 前缀）
    if ($is_men && !$is_women) {
        $conditions[] = "product_id LIKE 'M%'";
    } elseif ($is_women && !$is_men) {
        $conditions[] = "product_id LIKE 'W%'";
    }
    
    // 按品牌搜索
    if (!empty($product_category)) {
        $conditions[] = "(name LIKE ? OR keywords LIKE ?)";
        $params[] = "%$product_category%";
        $params[] = "%$product_category%";
    }
    
    // 按产品类型搜索
    if (!empty($product_type)) {
        $conditions[] = "(name LIKE ? OR keywords LIKE ?)";
        $params[] = "%$product_type%";
        $params[] = "%$product_type%";
    }
    
    // 通用搜索（名称、关键词、描述）
    $conditions[] = "(name LIKE ? OR keywords LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    
    $sql .= implode(" OR ", $conditions);
    $sql .= ")";
}

// 分类筛选（用户手动点击按钮）
if (!empty($category) && $category != 'All') {
    if ($category == 'Men') {
        $sql .= " AND product_id LIKE 'M%'";
    } elseif ($category == 'Women') {
        $sql .= " AND product_id LIKE 'W%'";
    }
}

$sql .= " ORDER BY product_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// 搜索建议
$search_suggestion = '';
if (!empty($search) && empty($products)) {
    $search_suggestion = 'No products found for "' . htmlspecialchars($search) . '". Try: shirt, pants, dress, hoodie, jeans, jacket';
}
?>

<style>
    .products-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    h1 { color: #333; margin-bottom: 20px; }
    .filter-bar { display: flex; gap: 15px; margin-bottom: 30px; justify-content: center; flex-wrap: wrap; }
    .category-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
    .category-btn { padding: 10px 25px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; text-decoration: none; color: #333; transition: 0.3s; }
    .category-btn.active { background: #e60012; color: white; border-color: #e60012; }
    .category-btn:hover { background: #e60012; color: white; }
    .clear-btn { padding: 10px 25px; background: #666; color: white; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; }
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-top: 20px; }
    .product-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
    .product-card:hover { transform: translateY(-5px); }
    .product-card img { width: 100%; height: 250px; object-fit: cover; }
    .product-info { padding: 15px; text-align: center; }
    .product-name { font-size: 16px; font-weight: bold; margin: 0 0 10px 0; }
    .product-name a { text-decoration: none; color: #333; }
    .price { color: #e60012; font-size: 22px; font-weight: bold; }
    .points { color: #ff9800; font-size: 12px; margin: 5px 0; }
    .stock { font-size: 12px; margin: 5px 0; }
    .in-stock { color: green; }
    .out-stock { color: red; }
    .add-to-cart { width: 100%; background: #e60012; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; margin-top: 10px; font-weight: bold; }
    .add-to-cart:hover { background: #cc0010; }
    .no-results { text-align: center; padding: 50px; color: #999; }
    .search-suggestion { text-align: center; color: #e60012; margin-top: 10px; }
    .back-link { display: inline-block; margin-bottom: 20px; color: #666; text-decoration: none; }
    .back-link:hover { color: #e60012; }
    .search-info { background: #f0f0f0; padding: 10px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
</style>

<div class="products-container">
    <a href="index.php" class="back-link">← Back to Home</a>
    <h1>All Products</h1>
    
    <?php if (!empty($search)): ?>
    <div class="search-info">
        🔍 Showing results for: <strong><?php echo htmlspecialchars($search); ?></strong>
        <?php if (!empty($products)): ?>
            (<?php echo count($products); ?> products found)
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="filter-bar">
        <div class="category-buttons">
            <a href="ViewAllProduct.php<?php echo $search ? '?query='.urlencode($search) : ''; ?>" class="category-btn <?php echo empty($category) ? 'active' : ''; ?>">All</a>
            <a href="ViewAllProduct.php<?php echo $search ? '?query='.urlencode($search).'&' : '?'; ?>category=Men" class="category-btn <?php echo $category == 'Men' ? 'active' : ''; ?>">👔 Men</a>
            <a href="ViewAllProduct.php<?php echo $search ? '?query='.urlencode($search).'&' : '?'; ?>category=Women" class="category-btn <?php echo $category == 'Women' ? 'active' : ''; ?>">👗 Women</a>
        </div>
        
        <?php if ($search || $category): ?>
            <a href="ViewAllProduct.php" class="clear-btn">✖ Clear Filters</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="no-results">
            <p>No products found for "<?php echo htmlspecialchars($search); ?>"</p>
            <?php if ($search_suggestion): ?>
                <p class="search-suggestion">💡 <?php echo $search_suggestion; ?></p>
            <?php endif; ?>
            <a href="ViewAllProduct.php" style="color: #e60012;">View all products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" onerror="this.src='images/default.jpg'">
                    </a>
                    <div class="product-info">
                        <div class="product-name">
                            <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </div>
                        <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
                        <div class="points">🎁 Earn <?php echo floor($product['price']); ?> points</div>
                        <div class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ')' : '✗ Out of Stock'; ?>
                        </div>
                        <?php if ($product['stock'] > 0): ?>
                            <button class="add-to-cart" data-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
                        <?php else: ?>
                            <button class="add-to-cart" disabled style="background: #ccc;">Out of Stock</button>
                        <?php endif; ?>
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
        
        // 禁用按钮防止重复点击
        $btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: 'memberProducts/add.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // 修改这里的逻辑：
                    // 我们直接提示已添加，并让按钮变成“Added!”
                    $btn.text('✓ Added!').css('background', '#28a745'); // 变成绿色
                    
                    // 询问用户是否要去购物车查看，或者留在原地
                    if (confirm('Item added! Go to shopping cart?')) {
                        window.location.href = 'ViewAllProduct.php'; // 如果点“确定”，去购物车
                    } else {
                        // 如果点“取消”，留在原地继续购物
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
            error: function() {
                alert('Error adding to cart');
            }
        });
    });
});
</script>

<?php require_once 'includes/_foot.php'; ?>
