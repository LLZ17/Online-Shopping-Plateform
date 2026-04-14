<?php
// TEE DE YONG's Part: Product Management (The "Immortal" UI Edition)
ini_set('display_errors', 0); 
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

try {
    $sql = "SELECT * FROM products ORDER BY created_at DESC"; 
    $stmt = $pdo->query($sql);
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
            .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
            .page-header h1 { font-size: 32px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -1.5px; }
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

        <div class="product-grid">
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
        </div>
    </div>
</template>

<script>
(function() {
    const templateId = 'final_product_layout';
    
    function performInjection() {
        const template = document.getElementById(templateId);
        if (!template) return;

        // 锁定模板的动态内容区域
        const viewport = document.querySelector('.admin-content-viewport') || 
                         document.querySelector('.main-content') || 
                         document.querySelector('#layout-content') ||
                         document.querySelector('main');

        if (viewport) {
            // 如果当前内容不是我们的商品列表，立刻强制替换
            if (!viewport.querySelector('.inventory-container')) {
                viewport.innerHTML = ''; // 杀掉公文包欢迎页
                viewport.appendChild(template.content.cloneNode(true));
                console.log("🛡️ Anti-Blank Guard: Content Restored.");
            }
        }
    }

    // 1. 每一秒检查一次 (防止 Ajax 切换后导致的变空)
    const heartBeat = setInterval(performInjection, 500);

    // 2. 监听 DOM 树变化 (毫秒级响应)
    const observer = new MutationObserver((mutations) => {
        performInjection();
    });
    observer.observe(document.body, { childList: true, subtree: true });

    // 3. 初始加载触发
    window.addEventListener('load', performInjection);
    performInjection();
})();
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
