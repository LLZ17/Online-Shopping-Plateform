<?php
// TEE DE YONG's Part: Sales Data Analytics (Optimized UI & Font Sizes)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

try {
    // 💡 1. 汇总概览
    $sqlSummary = "SELECT 
                    SUM(sales_count) as total_units, 
                    SUM(sales_count * price) as total_revenue 
                   FROM products";
    $summary = $pdo->query($sqlSummary)->fetch();

    // 💡 2. 销量排行榜
    $sqlTop5 = "SELECT 
                    name, 
                    product_image, 
                    sales_count as total_sold, 
                    (sales_count * price) as total_revenue
                FROM products 
                WHERE sales_count > 0
                ORDER BY sales_count DESC 
                LIMIT 5";
    $topProducts = $pdo->query($sqlTop5)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $topProducts = [];
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.41">

<div id="sales-app" class="sales-container" style="display:none;">
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <div>
            <h1>Sales Analytics</h1>
            <p style="color: #64748b; margin-top: 8px;">Inventory Performance Based on Lifetime Sales.</p>
        </div>
        <a href="javascript:void(0);" onclick="triggerSyncRefresh();" class="btn-refresh" style="display: flex; align-items: center; gap: 10px; padding: 12px 24px; background: white; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; color: #475569; font-weight: 800; font-size: 13px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
            <span>SYNC DATA</span>
        </a>
    </div>

    <div class="summary-grid">
        <div class="stat-card card-revenue">
            <div class="icon-box icon-revenue">💵</div>
            <div class="stat-info">
                <span class="stat-label">Estimated Revenue</span>
                <div class="stat-value">
                    <span class="currency-symbol">RM</span>
                    <?= number_format($summary['total_revenue'] ?? 0, 2) ?>
                </div>
            </div>
        </div>
        <div class="stat-card card-units">
            <div class="icon-box icon-units">📦</div>
            <div class="stat-info">
                <span class="stat-label">Total Lifetime Sold</span>
                <div class="stat-value">
                    <?= number_format($summary['total_units'] ?? 0) ?>
                    <small class="unit-text">items</small>
                </div>
            </div>
        </div>
    </div>

    <div class="top5-card">
        <div class="card-title">🏆 Top 5 Selling </div>
        
        <?php if (!empty($topProducts)): ?>
            <?php foreach ($topProducts as $index => $p): 
                $rank = $index + 1;
                $maxSold = $topProducts[0]['total_sold'] ?: 1;
                $percent = ($p['total_sold'] / $maxSold) * 100;
                
                $imgArray = explode(',', $p['product_image']);
                $displayImg = trim($imgArray[0]);
                $imgPath = "../images/" . $displayImg;
            ?>
            <div class="product-list-item">
                <div class="rank-badge rank-<?= $rank ?>"><?= $rank ?></div>
                <img src="<?= $imgPath ?>" class="p-thumb" onerror="this.src='../images/default.png'">
                
                <div class="p-details" style="flex: 1;">
                    <h4 style="margin: 0 0 8px 0; font-size: 18px; color: #1e293b;"><?= htmlspecialchars($p['name']) ?></h4>
                    <div class="p-progress-bg">
                        <div class="p-progress-fill" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>

                <div class="p-stats" style="text-align: right; min-width: 140px;">
                    <span class="sold-count"><?= number_format($p['total_sold']) ?> <small>Sold</small></span>
                    <span class="revenue">RM <?= number_format($p['total_revenue'], 2) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:100px; color:#94a3b8; border: 2px dashed #e2e8f0; border-radius: 20px;">
                <p>No products with sales record yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../js/app.js?v=1.41"></script>

<script>
// 💡 强行锁定跳转逻辑：
// 为了防止 app.js 里的同名函数把页面带去 Order History，我们在这里重写它。
function triggerSyncRefresh() {
    alert("📊 Sales data synchronized and refreshed!");
    // 获取当前文件名，如果是 sales_analytics.php 就跳回这里
    window.location.reload(); 
}
</script>

<?php include __DIR__ . '/../includes/_foot.php'; ?>
