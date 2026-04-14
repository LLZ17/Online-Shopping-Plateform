<?php
// TEE DE YONG's Part: Product CRUD (Max RM Restored + Enlarged UI)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$search    = $_GET['search'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? ''; // 💡 确保接收 Max 参数
$page      = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit     = 6; 

$where = "1=1";
$params = [];
if ($search !== '') { $where .= " AND name LIKE ?"; $params[] = "%$search%"; }
if ($min_price !== '') { $where .= " AND price >= ?"; $params[] = (float)$min_price; }
if ($max_price !== '') { $where .= " AND price <= ?"; $params[] = (float)$max_price; }

$offset = ($page - 1) * $limit;

try {
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $where");
    $stmtTotal->execute($params);
    $totalProducts = $stmtTotal->fetchColumn();
    $totalPages = ceil($totalProducts / $limit);

    $sql = "SELECT * FROM products WHERE $where ORDER BY product_id DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div style='padding:20px; color:red;'>Error: " . $e->getMessage() . "</div>");
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    .page-title-box { display: none !important; }
    html, body { background: #f0f2f5 !important; }
    
    .main-container { max-width: 1400px; margin: 40px auto; padding: 0 30px; }
    
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
    .top-bar h1 { font-size: 40px; font-weight: 900; color: #111; margin: 0; letter-spacing: -2px; }
    .btn-add-pro { background: #e60012; color: white; padding: 16px 32px; border-radius: 16px; font-weight: 800; text-decoration: none; box-shadow: 0 10px 20px rgba(230,0,18,0.2); font-size: 16px; }

    /* 💡 重新优化的过滤栏布局 */
    .filter-card { background: white; padding: 25px; border-radius: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 40px; display: flex; gap: 15px; align-items: center; }
    .input-pro { padding: 16px 20px; border: 2px solid #edf2f7; border-radius: 14px; font-weight: 600; font-size: 15px; outline: none; transition: 0.3s; }
    .input-pro:focus { border-color: #e60012; background: #fff; }
    
    /* 搜索框占大头，Min/Max 占小头 */
    .search-field { flex: 3; }
    .price-field { flex: 1; min-width: 120px; }
    
    .btn-search { background: #111; color: white; padding: 16px 40px; border-radius: 14px; font-weight: 800; border: none; cursor: pointer; font-size: 16px; transition: 0.3s; }
    .btn-search:hover { background: #333; }

    .product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 35px; margin-bottom: 50px; }
    .product-card { background: white; border-radius: 30px; overflow: hidden; border: 1px solid #f1f5f9; transition: 0.4s; display: flex; flex-direction: column; }
    .product-card:hover { transform: translateY(-8px); box-shadow: 0 25px 50px rgba(0,0,0,0.08); }
    
    .img-area { height: 280px; background: #f8fafc; overflow: hidden; }
    .img-area img { width: 100%; height: 100%; object-fit: cover; }
    
    .info-area { padding: 35px; flex: 1; display: flex; flex-direction: column; }
    .item-id { font-size: 12px; font-weight: 900; color: #e60012; margin-bottom: 12px; }
    .item-title { font-size: 26px; font-weight: 800; color: #1e293b; margin: 0 0 15px 0; line-height: 1.3; }
    .item-price { font-size: 30px; font-weight: 900; color: #0f172a; margin-bottom: 25px; }

    .action-row { display: flex; gap: 15px; margin-top: auto; padding-top: 25px; border-top: 2px solid #f8fafc; }
    .btn-edit { flex: 1; text-align: center; padding: 18px; background: #f8fafc; color: #334155; border-radius: 16px; text-decoration: none; font-weight: 800; font-size: 15px; border: 2px solid #e2e8f0; }
    .btn-edit:hover { background: #e60012; color: #fff; border-color: #e60012; }
    .btn-del { width: 60px; display: flex; align-items: center; justify-content: center; background: #fff1f0; color: #ef4444; border-radius: 16px; text-decoration: none; font-size: 20px; }

    .pagination { display: flex; justify-content: center; align-items: center; gap: 12px; padding-bottom: 60px; }
    .page-btn { min-width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; background: white; border-radius: 16px; text-decoration: none; font-weight: 800; font-size: 18px; border: 2px solid #edf2f7; transition: 0.3s; }
    .page-btn.active { background: #e60012; color: white; border-color: #e60012; box-shadow: 0 10px 20px rgba(230,0,18,0.2); }
</style>

<div id="product-app" class="main-container" style="display:none;">
    <div class="top-bar">
        <h1>Inventory Control</h1>
        <a href="admin_product_add.php" class="btn-add-pro">+ NEW PRODUCT</a>
    </div>

    <form method="GET" class="filter-card">
        <input type="text" name="search" class="input-pro search-field" placeholder="Search product name..." value="<?= htmlspecialchars($search) ?>">
        <input type="number" step="0.01" name="min_price" class="input-pro price-field" placeholder="Min RM" value="<?= htmlspecialchars($min_price) ?>">
        <input type="number" step="0.01" name="max_price" class="input-pro price-field" placeholder="Max RM" value="<?= htmlspecialchars($max_price) ?>">
        <button type="submit" class="btn-search">FILTER</button>
    </form>

    <div class="product-grid">
        <?php foreach ($products as $p): ?>
        <div class="product-card">
            <div class="img-area">
                <?php if(!empty($p['image_path'])): ?>
                    <img src="../<?= htmlspecialchars($p['image_path']) ?>">
                <?php else: ?><div style="height:100%; display:flex; align-items:center; justify-content:center; font-size:80px; color:#cbd5e1;">🛍️</div><?php endif; ?>
            </div>
            <div class="info-area">
                <div class="item-id">SKU #<?= $p['product_id'] ?></div>
                <h3 class="item-title"><?= htmlspecialchars($p['name']) ?></h3>
                <div class="item-price">RM <?= number_format($p['price'], 2) ?></div>
                <div class="action-row">
                    <a href="admin_product_edit.php?id=<?= $p['product_id'] ?>" class="btn-edit">EDIT SPECS</a>
                    <a href="admin_product_delete.php?id=<?= $p['product_id'] ?>" class="btn-del" onclick="return confirm('Delete item?')">🗑️</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&min_price=<?= urlencode($min_price) ?>&max_price=<?= urlencode($max_price) ?>" class="page-btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const app = document.getElementById('product-app');
        app.style.display = 'block';
        let v = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
        if (v) { v.innerHTML = ''; v.appendChild(app); }
    });
</script>

<?php $foot = __DIR__ . '/../includes/_foot.php'; if (file_exists($foot)) { include $foot; } ?>