<?php
// TEE DE YONG's Part: Product Management (ULTIMATE FIXED VERSION)
ini_set('display_errors', 1); 
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 💡 调试用：查看浏览器到底传了什么过来
// echo "<pre style='background:#000;color:#fff;padding:10px;'>Debug URL Params: "; var_dump($_GET); echo "</pre>";

$whereConditions = ["1=1"]; 
$params = [];

$search   = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$category  = $_GET['category'] ?? ''; // 这个必须对应 select 标签的 name

// 搜索
if (!empty($search)) {
    $whereConditions[] = "(name LIKE ? OR product_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// 价格
if ($min_price !== '') { $whereConditions[] = "price >= ?"; $params[] = $min_price; }
if ($max_price !== '') { $whereConditions[] = "price <= ?"; $params[] = $max_price; }

// ✨ 终极修复：直接根据 product_id 首字母强制过滤
if ($category === 'Men') {
    $whereConditions[] = "SUBSTRING(product_id, 1, 1) = 'M'";
} elseif ($category === 'Women') {
    $whereConditions[] = "SUBSTRING(product_id, 1, 1) = 'W'";
}

$whereSQL = implode(" AND ", $whereConditions);

try {
    $sql = "SELECT * FROM products WHERE $whereSQL ORDER BY created_at DESC"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $products = []; }

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.21">

<template id="final_product_layout">
    <div class="inventory-container">
        <div class="page-header">
            <h1>Product CRUD</h1>
            <div class="header-actions">
                <a href="javascript:void(0);" onclick="triggerSyncRefresh();" class="btn-refresh">
                    <span class="icon-spin">🔄</span><span>SYNC RECORDS</span>
                </a>
                <a href="admin_product_add.php" class="btn-add">➕ Add Item</a>
            </div>
        </div>

        <form method="GET" action="product_crud.php" class="filter-card">
            <input type="text" name="search" class="filter-input input-search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="category" class="filter-input">
                <option value="">All Genders</option>
                <option value="Men" <?= $category === 'Men' ? 'selected' : '' ?>>Men</option>
                <option value="Women" <?= $category === 'Women' ? 'selected' : '' ?>>Women</option>
            </select>

            <input type="number" name="min_price" class="filter-input input-price" placeholder="Min RM" value="<?= htmlspecialchars($min_price) ?>">
            <input type="number" name="max_price" class="filter-input input-price" placeholder="Max RM" value="<?= htmlspecialchars($max_price) ?>">
            
            <button type="submit" class="btn-filter">FILTER</button>
            
            <?php if ($search || $min_price || $max_price || $category): ?>
                <a href="product_crud.php" class="btn-reset">✖ Reset</a>
            <?php endif; ?>
        </form>

        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $p): 
                    $imgData = $p['product_image'] ?? ''; 
                    $imgArr = explode(',', $imgData);
                    
                    // --- 只有这里做了微调以支持空格文件名 ---
                    if (!empty($imgArr[0])) {
                        $firstImg = trim($imgArr[0]);
                        // 使用 rawurlencode 处理文件名中的空格，但保留路径斜杠 /
                        $encodedImg = str_replace('%2F', '/', rawurlencode($firstImg));
                        $path = "../images/" . $encodedImg;
                    } else {
                        $path = "../images/default.png";
                    }
                    // ---------------------------------------
                ?>
                <div class="product-card">
                    <div class="card-img-box"><img src="<?= $path ?>" class="card-img" onerror="this.src='../images/default.png'"></div>
                    <div class="card-body">
                        <div class="id-row">
                            <span style="font-size:11px;color:#94a3b8;">#<?= $p['product_id'] ?></span>
                            <span class="cat-badge"><?= htmlspecialchars($p['category_id'] ?? 'N/A') ?></span>
                        </div>
                        <div class="p-title"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="price-tag">RM <?= number_format($p['price'], 2) ?></div>
                    </div>
                    <div class="card-actions">
                        <a href="admin_product_detail.php?id=<?= $p['product_id'] ?>" class="detail-link btn-action">Detail</a>
                        <a href="admin_product_edit.php?id=<?= $p['product_id'] ?>" class="edit-link btn-action">Edit</a>
                        <a href="admin_product_delete.php?id=<?= $p['product_id'] ?>" class="del-link btn-action" onclick="return confirm('Delete?');">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column:1/-1;text-align:center;padding:100px;color:#94a3b8;">No results.</div>
            <?php endif; ?>
        </div>
    </div>
</template>

<script src="../js/app.js?v=1.21"></script> 

<script>
// 🚀 强制覆盖任何同名函数，确保只跳回当前页面
function triggerSyncRefresh() {
    alert("✅ Product records synchronized!");
    window.location.href = 'product_crud.php';
}
</script>
