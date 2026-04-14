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

<template id="final_product_layout">
    <div class="inventory-container" style="padding: 30px; font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; min-height: 100vh;">
        <style>
            .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
            .page-header h1 { font-size: 32px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -1.5px; }
            
            /* 💡 过滤栏样式 */
            .filter-card {
                background: #ffffff; border-radius: 20px; padding: 20px; margin-bottom: 25px;
                box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); border: 1px solid rgba(226, 232, 240, 0.8);
                display: flex; flex-wrap: wrap; gap: 15px; align-items: center;
            }
            .filter-input {
                padding: 12px 18px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;
                font-family: inherit; font-weight: 600; color: #334155; outline: none; transition: border-color 0.3s;
            }
            .filter-input:focus { border-color: #3b82f6; }
            .input-search { flex-grow: 1; min-width: 250px; }
            .input-price { width: 120px; }
            
            .btn-filter {
                background: #0f172a; color: #ffffff; padding: 12px 30px; border-radius: 12px;
                font-weight: 800; font-size: 14px; cursor: pointer; border: none; font-family: inherit;
            }
            .btn-reset {
                background: #f1f5f9; color: #64748b; padding: 12px 20px; border-radius: 12px;
                font-weight: 800; font-size: 14px; text-decoration: none; border: 1px solid #e2e8f0;
            }

            .btn-refresh { background: #ffffff; color: #64748b; padding: 12px 20px; border-radius: 14px; text-decoration: none; font-weight: 700; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 8px; }
            .btn-add { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 12px 24px; border-radius: 14px; text-decoration: none; font-weight: 700; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3); }
            
            .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; }
            .product-card { background: #ffffff; border-radius: 28px; border: 1px solid rgba(226, 232, 240, 0.8); overflow: hidden; display: flex; flex-direction: column; transition: all 0.4s ease; height: 100%; position: relative; }
            .product-card:hover { transform: translateY(-12px); box-shadow: 0 40px 80px -12px rgba(15, 23, 42, 0.15); border-color: #3b82f6; }
            .card-img-box { width: 100%; height: 200px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; }
            .card-img { max-width: 85%; max-height: 85%; object-fit: contain; }
            .card-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
            .p-title { font-size: 16px; font-weight: 700; color: #1e293b; height: 48px; overflow: hidden; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
            .price-tag { font-size: 22px; font-weight: 800; color: #0f172a; }
            .card-actions { display: grid; grid-template-columns: 1fr 1fr; background: #f8fafc; border-top: 1px solid #f1f5f9; }
            .btn-action { padding: 16px; text-align: center; text-decoration: none; font-size: 13px; font-weight: 700; }
            .edit-link { color: #3b82f6; border-right: 1px solid #f1f5f9; }
            .del-link { color: #94a3b8; }
            .del-link:hover { color: #dc2626; }
        </style>

        <div class="page-header">
            <h1>Inventory Control</h1>
            <div class="header-actions">
                <a href="product_crud.php" class="btn-refresh">🔄 Refresh</a>
                <a href="admin_product_add.php" class="btn-add">➕ Add Item</a>
            </div>
        </div>

        <form method="GET" action="product_crud.php" class="filter-card">
            <input type="text" name="search" class="filter-input input-search" placeholder="Search product name or ID..." value="<?= htmlspecialchars($search) ?>">
            <input type="number" name="min_price" class="filter-input input-price" placeholder="Min RM" step="0.01" value="<?= htmlspecialchars($min_price) ?>">
            <input type="number" name="max_price" class="filter-input input-price" placeholder="Max RM" step="0.01" value="<?= htmlspecialchars($max_price) ?>">
            <button type="submit" class="btn-filter">FILTER</button>
            
            <?php if (!empty($search) || $min_price !== '' || $max_price !== ''): ?>
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
                        <div style="font-size: 11px; color: #94a3b8; margin-bottom: 5px;">#<?= $p['product_id'] ?></div>
                        <div class="p-title"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="price-tag">RM <?= number_format($p['price'], 2) ?></div>
                    </div>
                    <div class="card-actions">
                        <a href="admin_product_edit.php?id=<?= urlencode($p['product_id']) ?>" class="btn-action edit-link">Edit</a>
                        <a href="admin_product_delete.php?id=<?= urlencode($p['product_id']) ?>" class="btn-action del-link" onclick="return confirm('Delete?')">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: span 4; text-align: center; padding: 100px; color: #94a3b8; background: white; border-radius: 20px;">
                    <div style="font-size: 50px;">🛍️</div>
                    <p>No products found matching your search.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</template>

<script>
(function() {
    const templateId = 'final_product_layout';
    
    function performInjection() {
        const template = document.getElementById(templateId);
        if (!template) return;

        const viewport = document.querySelector('.admin-content-viewport') || 
                         document.querySelector('.main-content') || 
                         document.querySelector('#layout-content') ||
                         document.querySelector('main');

        if (viewport) {
            if (!viewport.querySelector('.inventory-container')) {
                viewport.innerHTML = ''; 
                viewport.appendChild(template.content.cloneNode(true));
                
                // 💡 关键：重新同步搜索框的值（防止注入后输入框被清空）
                const searchInputs = viewport.querySelectorAll('input[name="search"]');
                if (searchInputs.length > 0) {
                    // 这里可以添加逻辑确保输入框状态正常
                }
            }
        }
    }

    const heartBeat = setInterval(performInjection, 500);
    const observer = new MutationObserver((mutations) => { performInjection(); });
    observer.observe(document.body, { childList: true, subtree: true });
    window.addEventListener('load', performInjection);
    performInjection();
})();
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
