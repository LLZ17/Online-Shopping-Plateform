<?php
// TEE DE YONG's Part: Professional Product CRUD (Optimization)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/_base.php'; 
require_once '../includes/db.php';     

// 引用头文件
include '../includes/_admin_head.php'; 

// 1. 处理搜索逻辑
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM products"; 
if ($search) {
    $sql .= " WHERE name LIKE " . $pdo->quote("%$search%") . " OR description LIKE " . $pdo->quote("%$search%");
}
$sql .= " ORDER BY product_id DESC";
$products = $pdo->query($sql)->fetchAll();
?>

<style>
    /* ============================================================
       🚀 布局优化：清场并美化容器
       ============================================================ */
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid,
    .admin-content-viewport > div[style*="background:white"] {
        display: none !important;
    }

    html, body { background-color: #f0f2f5 !important; overflow: auto !important; height: auto !important; }

    .crud-container { padding: 30px; max-width: 1400px; margin: 0 auto; }

    /* ============================================================
       🎨 顶栏与搜索框美化
       ============================================================ */
    .action-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .search-wrapper {
        position: relative;
        width: 350px;
    }
    .search-wrapper input {
        width: 100%;
        padding: 12px 20px 12px 45px;
        border-radius: 30px;
        border: 1px solid #ddd;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        outline: none;
        transition: 0.3s;
    }
    .search-wrapper input:focus { border-color: #e60012; box-shadow: 0 4px 15px rgba(230,0,18,0.1); }
    .search-wrapper i { position: absolute; left: 18px; top: 15px; color: #999; }

    /* ============================================================
       📦 产品卡片美化
       ============================================================ */
    .product-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
        gap: 25px; 
    }

    .p-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #edf2f7;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .p-card:hover { transform: translateY(-10px); box-shadow: 0 20px 30px rgba(0,0,0,0.08); }

    .p-img-box {
        height: 220px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .p-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
    
    .stock-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .in-stock { background: #e8f5e9; color: #2e7d32; }
    .out-stock { background: #ffebee; color: #c62828; }

    .p-info { padding: 20px; }
    .p-category { font-size: 11px; color: #e60012; font-weight: 600; text-transform: uppercase; }
    .p-name { font-size: 18px; font-weight: 700; color: #2d3748; margin: 8px 0; height: 48px; overflow: hidden; }
    .p-price { font-size: 22px; font-weight: 800; color: #1a202c; }

    .p-footer {
        display: flex;
        gap: 10px;
        padding-top: 15px;
        border-top: 1px solid #f1f1f1;
        margin-top: 15px;
    }
    .btn-icon {
        flex: 1;
        padding: 10px;
        border-radius: 10px;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        transition: 0.2s;
        text-decoration: none;
    }
    .btn-edit { background: #f1f5f9; color: #475569; }
    .btn-edit:hover { background: #e2e8f0; }
    .btn-del { background: #fff1f0; color: #e60012; }
    .btn-del:hover { background: #ffccc7; }
</style>

<div id="optimized-product-content" class="crud-container">
    <div class="action-header">
        <div>
            <h1 style="font-size: 30px; font-weight: 800; color: #1a202c; margin: 0;">Product Inventory</h1>
            <p style="color: #718096; margin-top: 5px;">Total active listings: <b><?= count($products) ?></b></p>
        </div>
        
        <div style="display: flex; gap: 15px;">
            <form method="get" class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Quick find product..." value="<?= htmlspecialchars($search) ?>">
            </form>
            <a href="admin_product_add.php" style="background: #e60012; color: white; padding: 12px 25px; border-radius: 30px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 15px rgba(230,0,18,0.2);">
                <i class="fa-solid fa-plus"></i> New Product
            </a>
        </div>
    </div>

    <div class="product-grid">
        <?php foreach ($products as $p): ?>
        <div class="p-card">
            <div class="p-img-box">
                <?php if(!empty($p['image'])): ?>
                    <img src="../images/<?= $p['image'] ?>" alt="product">
                <?php else: ?>
                    <i class="fa-solid fa-image" style="font-size: 50px; color: #e2e8f0;"></i>
                <?php endif; ?>
                
                <?php $stock = $p['stock'] ?? 10; ?>
                <span class="stock-badge <?= $stock > 0 ? 'in-stock' : 'out-stock' ?>">
                    <?= $stock > 0 ? 'In Stock' : 'Out of Stock' ?>
                </span>
            </div>

            <div class="p-info">
                <div class="p-category">Catalog ID #<?= $p['product_id'] ?></div>
                <div class="p-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="p-price">RM <?= number_format($p['price'], 2) ?></div>

                <div class="p-footer">
                    <a href="admin_product_edit.php?id=<?= $p['product_id'] ?>" class="btn-icon btn-edit">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>
                    <a href="admin_product_delete.php?id=<?= $p['product_id'] ?>" class="btn-icon btn-del" onclick="return confirm('Archive this product?')">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // 注入内容
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('optimized-product-content');
        if (viewport && content) {
            viewport.innerHTML = ''; // 强行清场朋友的内容
            viewport.appendChild(content);
        }
    });
</script>

</main>
<?php include '../includes/_foot.php'; ?>