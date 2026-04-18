<?php
// TEE DE YONG's Part: Sales Data Analytics (Ultimate Premium UI + Kinetic Sync Button)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

try {
    // 获取总概览数据 (Total Revenue & Total Sold)
    $sqlSummary = "SELECT COUNT(order_id) as total_orders, SUM(price * quantity) as total_revenue FROM order_items";
    $summary = $pdo->query($sqlSummary)->fetch();

    // 统计销量前五的商品 (Top 5 Sales)
    $sqlTop5 = "SELECT p.name, p.image_path, SUM(od.quantity) as total_sold, SUM(od.price * od.quantity) as total_revenue
                FROM products p
                JOIN order_items od ON p.product_id = od.product_id
                GROUP BY p.product_id
                ORDER BY total_sold DESC
                LIMIT 5";
    $topProducts = $pdo->query($sqlTop5)->fetchAll();
} catch (PDOException $e) {
    $topProducts = [];
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.14">

<div id="sales-app" class="sales-container" style="display:none;">
    <div class="page-header">
        <div>
            <h1>Sales Analytics</h1>
            <p>Overview of your store's performance and top-selling items.</p>
        </div>
        <a href="javascript:void(0);" onclick="triggerSyncRefresh();" class="btn-refresh">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 4v6h-6"></path>
                <path d="M1 20v-6h6"></path>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
            <span>SYNC DATA</span>
        </a>
    </div>

    <div class="summary-grid">
        <div class="stat-card card-revenue">
            <div class="icon-box icon-revenue">💵</div>
            <div class="stat-info">
                <span class="stat-label">Total Revenue</span>
                <div class="stat-value">RM <?= number_format($summary['total_revenue'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="stat-card card-units">
            <div class="icon-box icon-units">📦</div>
            <div class="stat-info">
                <span class="stat-label">Units Sold</span>
                <div class="stat-value"><?= number_format($summary['total_orders'] ?? 0) ?><small style="font-size:18px; color:#94a3b8; margin-left:5px;">items</small></div>
            </div>
        </div>
    </div>

    <div class="top5-card">
        <div class="card-title">🏆 Top 5 Selling Products</div>
        
        <?php if (!empty($topProducts)): ?>
            <?php foreach ($topProducts as $index => $p): 
                $rank = $index + 1;
                $rankClass = ($rank <= 3) ? "rank-$rank" : "";
                $maxSold = $topProducts[0]['total_sold'];
                $percent = ($maxSold > 0) ? ($p['total_sold'] / $maxSold * 100) : 0;
            ?>
            <div class="product-list-item">
                <div class="rank-badge <?= $rankClass ?>"><?= $rank ?></div>
                
                <img src="../<?= htmlspecialchars($p['image_path'] ?: 'images/default.png') ?>" class="p-thumb" onerror="this.src='../images/default.png'">

                <div class="p-details">
                    <h4><?= htmlspecialchars($p['name']) ?></h4>
                    <div class="p-progress-bg">
                        <div class="p-progress-fill" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
                <div class="p-stats">
                    <span class="sold-count"><?= $p['total_sold'] ?> <small style="font-size:14px; color:#64748b;">Sold</small></span>
                    <span class="revenue">RM <?= number_format($p['total_revenue'], 2) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:70px 20px; color:#94a3b8; background: #f8fafc; border-radius: 24px; border: 2px dashed #e2e8f0;">
                <p style="margin-bottom: 20px;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3501/3501061.png" width="80" style="opacity: 0.8;">
                </p>
                <p style="font-size: 20px; font-weight: 700; color: #334155; margin-bottom: 8px;">No sales data recorded yet.</p>
                <p style="font-size: 15px;">Once customers start ordering, your top products will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../js/app.js?v=1.14"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
