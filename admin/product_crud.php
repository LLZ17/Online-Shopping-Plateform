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

// 💡 核心修复：确保 SQL 能够区分 Man 和 Women 商品
if (!empty($category)) {
    // 强制匹配数据库中的 category 字段
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

<template id="final_product_layout">
    <div class="inventory-container" style="padding: 30px; font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; min-height: 100vh;">
        <style>
            .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
            .page-header h1 { font-size: 32px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -1.5px; }
            
            .filter-card {
                background: #ffffff; border-radius: 20px; padding: 20px; margin-bottom: 25px;
                box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); border: 1px solid rgba(226, 232, 240, 0.8);
                display: flex; flex-wrap: wrap; gap: 15px; align-items: center;
            }
            .filter-input {
                padding: 12px 18px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;
                font-family: inherit; font-weight: 600; color: #334155; outline: none; transition: border-color 0.3s;
                background: white;
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

            .btn-refresh { 
                background: #ffffff; color: #475569; padding: 10px 22px; border-radius: 14px; 
                text-decoration: none; font-weight: 800; font-size: 13px; border: 1px solid #e2e8f0; 
                display: inline-flex; align-items: center; gap: 10px; cursor: pointer; 
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            }
            .btn-refresh:hover { background: #0f172a; color: #ffffff; border-color: #0f172a; transform: translateY(-3px) scale(1.02); }
            .icon-spin { display: inline-block; transition: transform 0.8s ease; }
            .btn-refresh:hover .icon-spin { transform: rotate(360deg); color: #3b82f6; }

            .btn-add { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 12px 24px; border-radius: 14px; text-decoration: none; font-weight: 700; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3); transition: 0.3s; }
            .btn-add:hover { transform: translateY(-2px); box-shadow: 0 15px 25px -5px rgba(239, 68, 68, 0.4); color: white; }
            
            .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 25px; }
            .product-card { background: #ffffff; border-radius: 28px; border: 1px solid rgba(226, 232, 240, 0.8); overflow: hidden; display: flex; flex-direction: column; transition: all 0.4s ease; height: 100%; position: relative; }
            .product-card:hover { transform: translateY(-12px); box-shadow: 0 40px 80px -12px rgba(15, 23, 42, 0.15); border-color: #3b82f6; }
            .card-img-box { width: 100%; height: 200px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; }
            .card-img { max-width: 85%; max-height: 85%; object-fit: contain; }
            .card-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
            .p-title { font-size: 16px; font-weight: 700; color: #1e293b; height: 48px; overflow: hidden; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
            .price-tag { font-size: 22px; font-weight: 800; color: #0f172a; }
            
            /* 三等分布局 & 颜色设置 */
            .card-actions { display: grid; grid-template-columns: 1fr 1fr 1fr; background: #f8fafc; border-top: 1px solid #f1f5f9; }
            .btn-action { padding: 16px; text-align: center; text-decoration: none; font-size: 13px; font-weight: 700; }
            .detail-link { color: #eab308; border-right: 1px solid #f1f5f9; } 
            .edit-link { color: #3b82f6; border-right: 1px solid #f1f5f9; }
            .del-link { color: #ef4444; } 
            .del-link:hover { color: #dc2626; }
        </style>

        <div class="page-header">
            <h1>Product CRUD</h1>
            <div class="header-actions" style="display: flex; gap: 15px;">
                <a href="javascript:void(0);" onclick="triggerSyncRefresh();" class="btn-refresh">
                    <span class="icon-spin">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                        </svg>
                    </span>
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
                        <div style="font-size: 11px; color: #94a3b8; margin-bottom: 5px;">#<?= $p['product_id'] ?></div>
                        <div class="p-title"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="price-tag">RM <?= number_format($p['price'], 2) ?></div>
                    </div>
                    <div class="card-actions">
                        <a href="admin_product_detail.php?id=<?= urlencode($p['product_id']) ?>" class="btn-action detail-link">Detail</a>
                        <a href="admin_product_edit.php?id=<?= urlencode($p['product_id']) ?>" class="btn-action edit-link">Edit</a>
                        
                        <a href="admin_product_delete.php?id=<?= urlencode($p['product_id']) ?>" class="btn-action del-link" onclick="return confirm('Are you sure you want to permanently delete this product (#<?= htmlspecialchars($p['product_id']) ?>)?\n\nThis action cannot be undone.');">Delete</a>
                        
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: span 4; text-align: center; padding: 100px; color: #94a3b8; background: white; border-radius: 20px;">
                    <p style="margin-bottom: 20px;"><img src="https://cdn-icons-png.flaticon.com/512/3501/3501061.png" width="80" style="opacity: 0.5;"></p>
                    <p style="font-size: 18px; font-weight: 700;">No products found matching your search.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</template>

<script>
function triggerSyncRefresh() {
    alert("✅ Admin records synchronized and refreshed!");
    window.location.href = 'product_crud.php';
}

(function() {
    const templateId = 'final_product_layout';
    
    function performInjection() {
        const template = document.getElementById(templateId);
        if (!template) return;

        const viewport = document.querySelector('.admin-content-viewport') || 
                         document.querySelector('.main-content') || 
                         document.querySelector('#layout-content') ||
                         document.querySelector('.content-wrapper') ||
                         document.querySelector('main');

        if (viewport) {
            if (!viewport.querySelector('.inventory-container')) {
                viewport.innerHTML = ''; 
                viewport.appendChild(template.content.cloneNode(true));
            }
        }
    }

    const heartBeat = setInterval(performInjection, 500);
    
    const observer = new MutationObserver((mutations) => { 
        performInjection(); 
    });

    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });

    window.addEventListener('load', performInjection);
    performInjection();
})();
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
