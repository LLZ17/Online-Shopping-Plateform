<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR keywords LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category) && $category != 'All') {
    $sql .= " AND category_type = ?";
    $params[] = $category;
}

$sql .= " ORDER BY product_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
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
    .back-link { display: inline-block; margin-bottom: 20px; color: #666; text-decoration: none; }
    .back-link:hover { color: #e60012; }
</style>

<div class="products-container">
    <a href="../index.php" class="back-link">← Back to Home</a>
    <h1>All Products</h1>
    
    <div class="filter-bar">
        <div class="category-buttons">
            <a href="listing.php" class="category-btn <?php echo empty($category) ? 'active' : ''; ?>">All</a>
            <a href="listing.php?category=Men" class="category-btn <?php echo $category == 'Men' ? 'active' : ''; ?>">👔 Men</a>
            <a href="listing.php?category=Women" class="category-btn <?php echo $category == 'Women' ? 'active' : ''; ?>">👗 Women</a>
        </div>
        
        <?php if ($search || $category): ?>
            <a href="listing.php" class="clear-btn">✖ Clear Filters</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="no-results">
            <p>No products found.</p>
            <a href="listing.php" style="color: #e60012;">View all products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="../images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" onerror="this.src='../images/default.jpg'">
                    </a>
                    <div class="product-info">
                        <div class="product-name">
                            <a href="detail.php?id=<?php echo $product['product_id']; ?>">
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
        $.ajax({
            url: 'add.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (confirm('✓ Added to cart! Go to cart now?')) {
                        window.location.href = 'unified_cart.php';
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

<?php require_once '../includes/_foot.php'; ?>
