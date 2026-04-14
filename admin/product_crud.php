<?php
// TEE DE YONG's Part: Product Management (Grid Card Edition + Refresh Button)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 强大的搜索与价格过滤逻辑
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
    $errorMsg = "Database Error: " . $e->getMessage();
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    .page-title-box { display: none !important; }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .inventory-container { 
        width: 100%; box-sizing: border-box; padding: 20px; 
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif; 
        animation: fadeInUp 0.5s ease-out forwards;
    }

    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .page-header h1 { font-size: 32px; font-weight: 900; color: #0f172a; letter-spacing: -1px; margin: 0; }
    
    /* 💡 新增的 Refresh 按钮样式 */
    .btn-refresh {
        background: #ffffff; color: #334155;
        padding: 10px 16px; border-radius: 12px; text-decoration: none; font-weight: 800;
        font-size: 13px; transition: all 0.3s ease; border: 1px solid #cbd5e1;
        display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        white-space: nowrap; cursor: pointer;
    }
    .btn-refresh:hover {
        background: #f1f5f9; color: #0f172a; border-color: #94a3b8;
        transform: translateY(-2px); box-shadow: 0 8px 15px -3px rgba(0,0,0,0.08);
    }
    .icon-spin { transition: transform 0.5s ease; display: inline-block; }
    .btn-refresh:hover .icon-spin { transform: rotate(180deg); }

    /* 红色的 New Product 按钮 */
    .btn-add-product {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #ffffff;
        padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 800;
        font-size: 13px; transition: all 0.3s ease; border: 1px solid #ef4444;
        display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 15px -4px rgba(239, 68, 68, 0.4);
        white-space: nowrap;
    }
    .btn-add-product:hover { transform: translateY(-2px); box-shadow: 0 12px 20px -4px rgba(239, 68, 68, 0.5); color: #ffffff; }

    .filter-card {
        background: #ffffff; border-radius: 20px; padding: 20px; margin-bottom: 25px;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); border: 1px solid rgba(226, 232, 240, 0.8);
        display: flex; flex-wrap: wrap; gap: 15px; align-items: center;
    }
    .filter-input {
        padding: 12px 18px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 14px;
        font-family: inherit; font-weight: 600; color: #334155; outline: none; transition: border-color 0.3s;
    }
    .filter-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .input-search { flex-grow: 1; min-width: 250px; }
    .input-price { width: 120px; }
    
    .btn-filter {
        background: #0f172a; color: #ffffff; padding: 12px 30px; border-radius: 12px;
        font-weight: 800; font-size: 14px; cursor: pointer; border: none; font-family: inherit;
        transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .btn-filter:hover { background: #334155; transform: translateY(-1px); box-shadow: 0 5px 15px rgba(15, 23, 42, 0.3); }
    .btn-reset {
        background: #f1f5f9; color: #64748b; padding: 12px 20px; border-radius: 12px;
        font-weight: 800; font-size: 14px; cursor: pointer; border: 1px solid #e2e8f0; 
        text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s;
    }
    .btn-reset:hover { background: #e2e8f0; color: #0f172a; }

    /* 商品网格布局 (4 列) */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr); 
        gap: 20px; 
        width: 100%;
    }

    @media (max-width: 1400px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 1100px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .product-grid { grid-template-columns: 1fr; } }

    .product-card {
        background: #ffffff; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.03);
        border: 1px solid rgba(226, 232, 240, 0.8); overflow: hidden;
        display: flex; flex-direction: column; transition: all 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-5px); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .card-image-wrapper {
        width: 100%; padding-top: 100%; position: relative;
        background: #f8fafc; border-bottom: 1px solid #f1f5f9;
    }
    .card-img {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        object-fit: contain; padding: 15px; box-sizing: border-box;
    }

    .card-body { padding: 15px; display: flex; flex-direction: column; flex-grow: 1; }

    .card-category-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .p-category { font-size: 11px; color: #64748b; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .cat-badge { background: #f1f5f9; padding: 2px 6px; border-radius: 6px; font-size: 10px; color: #475569; }

    .p-title {
        font-size: 14px; font-weight: 800; color: #0f172a; line-height: 1.4;
        margin-bottom: 8px; height: 40px; 
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }

    .card-price-row {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: auto; padding-top: 10px; border-top: 1px solid #f1f5f9;
    }
    .price-tag { font-size: 18px; font-weight: 900; color: #059669; }
    .rating-star { color: #f59e0b; font-size: 12px; font-weight: 700; }

    .card-stock-status {
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
        margin-bottom: 8px; display: flex; align-items: center; gap: 5px;
    }
    .stock-good { color: #059669; }
    .stock-low { color: #d97706; }
    .stock-out { color: #dc2626; }

    .card-actions {
        display: grid; grid-template-columns: 1fr 1fr; 
        border-top: 1px solid #f1f5f9; background: #f8fafc;
    }
    .btn-card-action {
        padding: 12px; text-align: center; text-decoration: none; font-size: 12px;
        font-weight: 700; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .btn-edit { color: #3b82f6; border-right: 1px solid #f1f5f9; }
    .btn-edit:hover { background: #e0f7fa; color: #01579b; }

    .btn-delete { color: #ef4444; }
    .btn-delete:hover { background: #fee2e2; color: #b91c1c; }
</style>

<div class="inventory-container">
    <div class="page-header">
        <h1>Inventory Control</h1>
        
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <a href="product_crud.php" class="btn-refresh">
                <span class="icon-spin">🔄</span> Refresh
            </a>
            <a href="product_add.php" class="btn-add-product">
                <span>➕</span> NEW PRODUCT
            </a>
        </div>
    </div>

    <?php if (isset($errorMsg)): ?>
        <div style="background:#fef2f2; color:#dc2626; padding:15px; border-radius:12px; margin-bottom:20px; border: 1px solid #fecaca; font-weight:600;">
            ⚠️ <?= $errorMsg ?>
        </div>
    <?php endif; ?>

    <form method="GET" class="filter-card">
        <input type="text" name="search" class="filter-input input-search" placeholder="Search product name or ID..." value="<?= htmlspecialchars($search) ?>">
        <input type="number" name="min_price" class="filter-input input-price" placeholder="Min RM" step="0.01" value="<?= htmlspecialchars($min_price) ?>">
        <input type="number" name="max_price" class="filter-input input-price" placeholder="Max RM" step="0.01" value="<?= htmlspecialchars($max_price) ?>">
        <button type="submit" class="btn-filter">FILTER</button>
        
        <?php if (!empty($search) || $min_price !== '' || $max_price !== ''): ?>
            <a href="product_crud.php" class="btn-reset">✖ Reset</a>
        <?php endif; ?>
    </form>

    <?php if (!empty($products)): ?>
        <div class="product-grid">
            <?php foreach ($products as $p): 
                $images = explode(',', $p['product_image']);
                $firstImage = trim($images[0]);
                $photoPath = !empty($firstImage) ? "../images/" . htmlspecialchars($firstImage) : '../images/default_product.png';

                $stock = (int)$p['stock'];
                $threshold = (int)($p['low_stock_threshold'] ?? 5);
                
                if ($stock <= 0) {
                    $stockClass = 'stock-out';
                    $stockLabel = '🛑 Out of stock';
                } elseif ($stock <= $threshold) {
                    $stockClass = 'stock-low';
                    $stockLabel = '⚠️ Low Stock (' . $stock . ')';
                } else {
                    $stockClass = 'stock-good';
                    $stockLabel = '✅ In stock (' . $stock . ')';
                }
            ?>
            <div class="product-card">
                <div class="card-image-wrapper">
                    <img src="<?= $photoPath ?>" class="card-img" onerror="this.src='../images/default_product.png'" alt="Product">
                </div>

                <div class="card-body">
                    <div class="card-category-row">
                        <span class="p-category">📁 <span class="cat-badge"><?= htmlspecialchars($p['category_id']) ?></span></span>
                        <span style="font-size: 10px; color: #94a3b8; font-weight: 700;">#<?= htmlspecialchars($p['product_id']) ?></span>
                    </div>

                    <div class="p-title"><?= htmlspecialchars($p['name']) ?></div>

                    <div class="card-stock-status <?= $stockClass ?>">
                        <?= $stockLabel ?>
                    </div>

                    <div class="card-price-row">
                        <span class="price-tag">RM <?= number_format($p['price'], 2) ?></span>
                        <span class="rating-star">⭐ <?= number_format($p['rating'], 1) ?></span>
                    </div>
                </div>

                <div class="card-actions">
                    <a href="product_edit.php?id=<?= urlencode($p['product_id']) ?>" class="btn-card-action btn-edit">
                        <span>✏️</span> Edit
                    </a>
                    <a href="product_delete.php?id=<?= urlencode($p['product_id']) ?>" class="btn-card-action btn-delete" onclick="return confirm('Confirm DELETE [<?= htmlspecialchars($p['product_id']) ?>]?');">
                        <span>🗑️</span> Delete
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="background:#ffffff; border-radius:20px; border:1px solid #e2e8f0; text-align:center; padding:80px; color:#94a3b8;">
            <div style="font-size:50px; margin-bottom:15px;">🛍️</div>
            <div style="font-size:18px; font-weight:700;">No products found.</div>
            <?php if (!empty($search) || $min_price !== '' || $max_price !== ''): ?>
                <div style="font-size:14px; margin-top:10px;">Try adjusting your filters.</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
        let container = document.querySelector('.inventory-container');
        if (viewport && container && viewport !== container.parentNode) {
            viewport.innerHTML = '';
            viewport.appendChild(container);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
