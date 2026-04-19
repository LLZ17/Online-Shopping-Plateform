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

// 分类筛选（男装/女装）
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
?>

<link rel="stylesheet" href="../css/app.css">

<div class="products-listing-container">
    <a href="../index.php" class="products-listing-back-link">← Back to Home</a>
    <h1>All Products</h1>
    
    <div class="products-listing-filter-bar">
        <div class="products-listing-category-buttons">
            <a href="listing.php" class="products-listing-category-btn <?php echo empty($category) ? 'active' : ''; ?>">All</a>
            <a href="listing.php?category=Men" class="products-listing-category-btn <?php echo $category == 'Men' ? 'active' : ''; ?>">👔 Men</a>
            <a href="listing.php?category=Women" class="products-listing-category-btn <?php echo $category == 'Women' ? 'active' : ''; ?>">👗 Women</a>
        </div>
        
        <?php if ($search || $category): ?>
            <a href="listing.php" class="products-listing-clear-btn">✖ Clear Filters</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="products-listing-no-results">
            <p>No products found.</p>
            <a href="listing.php" class="products-listing-view-all">View all products</a>
        </div>
    <?php else: ?>
        <div class="products-listing-grid">
            <?php foreach ($products as $product): ?>
                <div class="products-listing-card">
                    <a href="detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="../images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" 
                             class="products-listing-card-img" 
                             onerror="this.src='../images/default.jpg'">
                    </a>
                    <div class="products-listing-card-info">
                        <div class="products-listing-card-name">
                            <a href="detail.php?id=<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </div>
                        <div class="products-listing-card-price">RM <?php echo number_format($product['price'], 2); ?></div>
                        <div class="products-listing-card-points">🎁 Earn <?php echo floor($product['price']); ?> points</div>
                        <div class="products-listing-card-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ')' : '✗ Out of Stock'; ?>
                        </div>
                        <?php if ($product['stock'] > 0): ?>
                            <button class="products-listing-add-to-cart" data-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
                        <?php else: ?>
                            <button class="products-listing-add-to-cart" disabled style="background: #ccc;">Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../js/app.js"></script>

<?php require_once '../includes/_foot.php'; ?>
