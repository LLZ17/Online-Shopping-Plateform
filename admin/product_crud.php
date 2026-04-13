<?php
// TEE DE YONG's Part: Product Inventory (Final Premium Version)
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * 💡 核心修复：
 * 既然 _base.php 丢了，我们只引用还在的 db.php
 */
require_once __DIR__ . '/../includes/db.php'; 

// 获取所有商品数据
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY product_id DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div style='color:red; padding:20px; border:1px solid;'>数据库错误: " . $e->getMessage() . "</div>");
}

// 头部引用：确保布局不崩溃
$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) {
    include $admin_head;
}
?>

<style>
    /* 强力清场：保持最漂亮、最清爽的界面 */
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid,
    .admin-content-viewport > div[style*="background:white"] { 
        display: none !important; 
    }
    
    html, body { 
        background: #f8fafc !important; 
        font-family: 'Inter', -apple-system, sans-serif;
    }

    .inventory-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 0 20px;
    }

    /* 顶部高级感标题栏 */
    .inventory-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        background: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .inventory-header h1 { font-weight: 800; color: #1e293b; margin: 0; font-size: 28px; }
    
    .btn-new {
        background: #e60012; color: white; padding: 14px 30px;
        border-radius: 50px; text-decoration: none; font-weight: 700;
        box-shadow: 0 4px 15px rgba(230, 0, 18, 0.2); transition: 0.3s;
    }
    .btn-new:hover { background: #cc0010; transform: translateY(-2px); }

    /* 商品网格卡片布局 */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }

    .p-card {
        background: white; border-radius: 20px; overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03); border: 1px solid #edf2f7;
        transition: 0.3s;
    }
    .p-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.08); }

    .p-image {
        height: 200px; background: #f1f5f9;
        display: flex; align-items: center; justify-content: center;
        color: #94a3b8; font-size: 50px;
    }

    .p-info { padding: 25px; }
    .p-tag { color: #e60012; font-size: 11px; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; }
    .p-name { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 15px 0; height: 45px; overflow: hidden; }
    .p-price { font-size: 24px; font-weight: 900; color: #0f172a; }

    /* 底部操作区 */
    .p-actions {
        display: flex; gap: 12px; margin-top: 25px; padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }
    .btn-edit {
        flex: 1; text-align: center; padding: 12px;
        background: #f8fafc; color: #475569; border-radius: 12px;
        text-decoration: none; font-weight: 700; font-size: 14px;
    }
    
    .btn-del {
        width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;
        background: #fff1f0; color: #e60012; border-radius: 12px;
        text-decoration: none;
    }
</style>

<div id="premium-product-app" class="inventory-container">
    <div class="inventory-header">
        <div>
            <h1>Product Inventory</h1>
            <p style="color: #64748b; margin: 5px 0 0;">Managing <b><?= count($products) ?></b> items in catalog</p>
        </div>
        <a href="admin_product_add.php" class="btn-new">+ New Product</a>
    </div>

    <div class="product-grid">
        <?php foreach ($products as $p): ?>
        <div class="p-card">
            <div class="p-image">🛍️</div>
            <div class="p-info">
                <div class="p-tag">ID #<?= $p['product_id'] ?></div>
                <h3 class="p-name"><?= htmlspecialchars($p['name']) ?></h3>
                <div class="p-price">RM <?= number_format($p['price'], 2) ?></div>
                
                <div class="p-actions">
                    <a href="admin_product_edit.php?id=<?= $p['product_id'] ?>" class="btn-edit">Edit details</a>
                    <a href="admin_product_delete.php?id=<?= $p['product_id'] ?>" 
                       class="btn-del" 
                       onclick="return confirm('Confirm permanent deletion?')">🗑️</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    /* 注入代码到主视口 */
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('premium-product-app');
        if (viewport && content) {
            viewport.innerHTML = ''; 
            viewport.appendChild(content);
        }
    });
</script>

<?php 
$foot_file = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot_file)) {
    include $foot_file; 
}
?>