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
    
    $is_men = false;
    $is_women = false;
    $product_type = '';
    $product_category = '';
    
    $men_keywords = ['men', 'male', 'boy', 'guy', 'gentleman', 'mens'];
    $women_keywords = ['women', 'female', 'girl', 'lady', 'womens', 'woman'];
    
    foreach ($men_keywords as $kw) {
        if (strpos($search_lower, $kw) !== false) { $is_men = true; break; }
    }
    foreach ($women_keywords as $kw) {
        if (strpos($search_lower, $kw) !== false) { $is_women = true; break; }
    }
    
    $shirt_keywords = ['shirt', 'tee', 't-shirt', 'tshirt', 'top', 'blouse', 'polo', 'jersey', 'hoodie', 'sweater', 'pullover'];
    $pants_keywords = ['pants', 'pant', 'trousers', 'jeans', 'denim', 'jogger', 'cargo', 'chino', 'legging', 'shorts', 'short', 'bermuda'];
    $skirt_keywords = ['skirt', 'dress', 'maxi', 'midi', 'mini', 'pleated', 'a-line', 'bodycon'];
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
    
    $brand_keywords = ['hype', 'tntco', 'ric', 'lovito', 'jeep', 'valunee', 'hitty'];
    foreach ($brand_keywords as $kw) {
        if (strpos($search_lower, $kw) !== false) { $product_category = $kw; break; }
    }
    
    $sql .= " AND (";
    $conditions = [];
    
    if ($is_men && !$is_women) {
        $conditions[] = "product_id LIKE 'M%'";
    } elseif ($is_women && !$is_men) {
        $conditions[] = "product_id LIKE 'W%'";
    }
    
    if (!empty($product_category)) {
        $conditions[] = "(name LIKE ? OR keywords LIKE ?)";
        $params[] = "%$product_category%";
        $params[] = "%$product_category%";
    }
    
    if (!empty($product_type)) {
        $conditions[] = "(name LIKE ? OR keywords LIKE ?)";
        $params[] = "%$product_type%";
        $params[] = "%$product_type%";
    }
    
    $conditions[] = "(name LIKE ? OR keywords LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    
    $sql .= implode(" OR ", $conditions);
    $sql .= ")";
}

// 分类筛选
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

$search_suggestion = '';
if (!empty($search) && empty($products)) {
    $search_suggestion = 'No products found for "' . htmlspecialchars($search) . '". Try: shirt, pants, dress, hoodie, jeans, jacket';
}
?>

<link rel="stylesheet" href="css/app.css">

<div class="viewall-products-container">
    <a href="index.php" class="viewall-back-link">← Back to Home</a>
    <h1>All Products</h1>
    
    <?php if (!empty($search)): ?>
    <div class="viewall-search-info">
        🔍 Showing results for: <strong><?php echo htmlspecialchars($search); ?></strong>
        <?php if (!empty($products)): ?>
            (<?php echo count($products); ?> products found)
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="viewall-filter-bar">
        <div class="viewall-category-buttons">
            <a href="ViewAllProduct.php<?php echo $search ? '?query='.urlencode($search) : ''; ?>" class="viewall-category-btn <?php echo empty($category) ? 'active' : ''; ?>">All</a>
            <a href="ViewAllProduct.php<?php echo $search ? '?query='.urlencode($search).'&' : '?'; ?>category=Men" class="viewall-category-btn <?php echo $category == 'Men' ? 'active' : ''; ?>">👔 Men</a>
            <a href="ViewAllProduct.php<?php echo $search ? '?query='.urlencode($search).'&' : '?'; ?>category=Women" class="viewall-category-btn <?php echo $category == 'Women' ? 'active' : ''; ?>">👗 Women</a>
        </div>
        
        <?php if ($search || $category): ?>
            <a href="ViewAllProduct.php" class="viewall-clear-btn">✖ Clear Filters</a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="viewall-no-results">
            <p>No products found for "<?php echo htmlspecialchars($search); ?>"</p>
            <?php if ($search_suggestion): ?>
                <p class="viewall-search-suggestion">💡 <?php echo $search_suggestion; ?></p>
            <?php endif; ?>
            <a href="ViewAllProduct.php" class="viewall-view-all">View all products</a>
        </div>
    <?php else: ?>
        <div class="viewall-products-grid">
            <?php foreach ($products as $product): ?>
                <div class="viewall-product-card">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo !empty($product['product_image']) ? htmlspecialchars($product['product_image']) : 'default.jpg'; ?>" 
                             class="viewall-product-img" 
                             onerror="this.src='images/default.jpg'">
                    </a>
                    <div class="viewall-product-info">
                        <div class="viewall-product-name">
                            <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </div>
                        <div class="viewall-product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                        <div class="viewall-product-stock-info">
        <span class="stock-left">📦 Stock: <?php echo $product['stock']; ?> left</span>
        <span class="sales-count">📈 Sold: <?php echo number_format($product['sales_count'] ?? 0); ?></span>
    </div>
                        <div class="viewall-product-points">🎁 Earn <?php echo floor($product['price']); ?> points</div>
                        <div class="viewall-product-stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ')' : '✗ Out of Stock'; ?>
                        </div>
                        <?php if ($product['stock'] > 0): ?>
                            <button class="viewall-add-to-cart" data-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
                        <?php else: ?>
                            <button class="viewall-add-to-cart" disabled style="background: #ccc;">Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/app.js"></script>

<?php require_once 'includes/_foot.php'; ?>
