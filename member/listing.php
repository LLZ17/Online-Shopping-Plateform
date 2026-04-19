<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

// 获取搜索关键词和分类筛选
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// 搜索功能
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR keywords LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// 分类筛选（男装/女装）
if (!empty($category)) {
    $sql .= " AND category_type = ?";
    $params[] = $category;
}

$sql .= " ORDER BY product_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// 获取分类列表
$categories = ['Men', 'Women'];
?>

<style>
    .products-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .search-bar { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; justify-content: center; }
    .search-box { display: flex; gap: 10px; flex: 2; min-width: 250px; }
    .search-box input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
    .search-box button { padding: 12px 30px; background: #e60012; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
    .category-filter { display: flex; gap: 10px; align-items: center; }
    .category-filter select { padding: 12px 20px; border: 1px solid #ddd; border-radius: 8px; background: white; cursor: pointer; }
    .clear-btn { padding: 12px 20px; background: #666; color: white; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; }
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
</style>

<div class="products-container">
    <h1>Our Products</h1>
    
    <form method="GET" class="search-bar">
        <div class="search-box">
            <input type="text" name="search" placeholder="Search products by name, keyword..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">🔍 Search</button>
        </div>
        
        <div class="category-filter">
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="Men" <?php echo $category == 'Men' ? 'selected' : ''; ?>>👔 Men</option>
                <option value="Women" <?php echo $category == 'Women' ? 'selected' : ''; ?>>👗 Women</option>
            </select>
        </div>
        
        <?php if ($search || $category): ?>
            <a href="listing.php" class="clear-btn">✖ Clear Filters</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($products)): ?>
        <div class="no-results">
            <p>No products found matching your criteria.</p>
            <a href="listing.php" style="color: #e60012;">View all products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="../images/<?php echo !empty($product['product_image']) ? $product['product_image'] : 'default.jpg'; ?>" onerror="this.src='../images/default.jpg'">
                    </a>
                    <div class="product-info">
                        <div class="product-name"><a href="detail.php?id=<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></div>
                        <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
                        <div class="points">🎁 Earn <?php echo floor($product['price']); ?> points</div>
                        <div class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                             <div class="member-product-stock-info">
        <span class="stock-left">📦 Stock: <?php echo $product['stock']; ?> left</span>
        <span class="sales-count">📈 Sold: <?php echo number_format($product['sales_count'] ?? 0); ?></span>
    </div>
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
        $.ajax({
            url: '../member_cart/add.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (confirm('✓ Added to cart! Go to cart now?')) {
                        window.location.href = '../member_cart/unified_cart.php';
                    } else {
                        $btn.text('Added!');
                        setTimeout(function() { $btn.text('Add to Cart'); }, 1500);
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
    });
});
</script>
<?php require_once __DIR__ . '/../includes/_foot.php'; ?>
