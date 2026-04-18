<?php
// TEE DE YONG's Part: Product Management (Ultimate Guard + Full Search & Filter)
ini_set('display_errors', 0); 
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 💡 1. 核心过滤逻辑处理
$whereConditions = ["1=1"]; 
$params = [];

$search = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$category = $_GET['category'] ?? ''; 

if (!empty($search)) {
    $whereConditions[] = "(name LIKE ? OR product_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($min_price !== '') {
    $whereConditions[] = "price >= ?";
    $params[] = $min_price;
}
if ($max_price !== '') {
    $whereConditions[] = "price <= ?";
    $params[] = $max_price;
}

if (!empty($category)) {
    $whereConditions[] = "category = ?";
    $params[] = $category;
}

$whereSQL = implode(" AND ", $whereConditions);

try {
    $sql = "SELECT * FROM products WHERE $whereSQL ORDER BY created_at DESC"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.0">

<template id="final_product_layout">
    <div class="inventory-container" style="background-color: #f8fafc; min-height: 100vh;">
        <div class="page-header">
            <h1>Product CRUD</h1>
            <div class="header-actions" style="display: flex; gap: 15px;">
                <a href="javascript:void(0);" onclick="triggerSyncRefresh();" class="btn-refresh">
                    <span class="icon-spin">🔄</span>
                    <span>SYNC RECORDS</span>
                </a>
                <a href="admin_product_add.php" class="btn-add">➕ Add Item</a>
            </div>
        </div>

        <form method="GET" action="product_crud.php" class="filter-card">
            <input type="text" name="search" class="filter-input input-search" placeholder="Search product name or ID..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="category" class="filter-input" style="width: 140px; cursor: pointer;">
                <option value="">All Genders</option>
                <option value="Man" <?= $category === 'Man' ? 'selected' : '' ?>>Man</option>
                <option value="Women" <?= $category === 'Women' ? 'selected' : '' ?>>Women</option>
            </select>

            <input type="number" name="min_price" class="filter-input input-price" placeholder="Min RM" step="0.01" value="<?= htmlspecialchars($min_price) ?>">
            <input type="number" name="max_price" class="filter-input input-price" placeholder="Max RM" step="0.01" value="<?= htmlspecialchars($max_price) ?>">
            <button type="submit" class="btn-filter">FILTER</button>
            
            <?php if (!empty($search) || $min_price !== '' || $max_price !== '' || !empty($category)): ?>
                <a href="product_crud.php" class="btn-reset">✖ Reset</a>
            <?php endif; ?>
        </form>

        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): 
                    $imgData = $p['product_image'] ?? ''; 
                    $path = !empty($imgData) ? "../images/" . trim(explode(',', $imgData)[0]) : "../images/default.png";
                ?>
                <div class="product-card">
                    <div class="card-img-box">
                        <img src="<?= $path ?>" class="card-img" onerror="this.src='../images/default.png'">
                    </div>
                    <div class="card-body">
                        <div class="id-row">
                            <div style="font-size: 11px; color: #94a3b8;">#<?= $p['product_id'] ?></div>
                            <div class="cat-badge"><?= htmlspecialchars($p['category_id'] ?? 'N/A') ?></div>
                        </div>
                        <div class="p-title"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="price-tag">RM <?= number_format($p['price'], 2) ?></div>
                    </div>
                    <div class="card-actions">
                        <a href="admin_product_detail.php?id=<?= urlencode($p['product_id']) ?>" class="btn-action detail-link">Detail</a>
                        <a href="admin_product_edit.php?id=<?= urlencode($p['product_id']) ?>" class="btn-action edit-link">Edit</a>
                        <a href="admin_product_delete.php?id=<?= urlencode($p['product_id']) ?>" class="btn-action del-link" onclick="return confirm('Delete this product?');">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: span 4; text-align: center; padding: 100px; color: #94a3b8;">
                    <p>No products found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</template>

<script src="../js/app.js?v=1.0"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
