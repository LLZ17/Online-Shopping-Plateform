<?php
// TEE DE YONG's Part: Sales Data Analytics (Ultimate Premium UI + Kinetic Sync Button)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 引入数据库
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

// 加载后台头部
$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800;900&display=swap');

    /* 强制去掉可能存在的背景冲突 */
    .page-title-box { display: none !important; }
    
    /* 页面载入动画 */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .sales-container { 
        max-width: 1400px; 
        margin: 40px auto; 
        padding: 0 30px; 
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; 
        width: 100%; 
        animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    .page-header { 
        margin-bottom: 45px; 
        display: flex; 
        justify-content: space-between; 
        align-items: flex-end; 
    }
    
    .page-header h1 { 
        font-size: 42px; 
        font-weight: 900; 
        color: #0f172a; 
        letter-spacing: -1.5px; 
        margin: 0; 
    }
    .page-header p { color: #64748b; margin-top: 10px; font-size: 16px; font-weight: 500; }

    /* 🔄 极致优化的 Refresh 按钮 */
    .btn-refresh {
        background: #ffffff;
        color: #64748b;
        padding: 12px 26px;
        border-radius: 16px;
        text-decoration: none;
        font-weight: 800;
        font-size: 13px;
        letter-spacing: 0.5px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        cursor: pointer;
    }

    .btn-refresh:hover {
        background: #0f172a; /* 悬浮变成高级黑 */
        color: #ffffff;
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 15px 30px -5px rgba(15, 23, 42, 0.2);
        border-color: #0f172a;
    }

    .btn-refresh svg { 
        transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1); 
    }

    .btn-refresh:hover svg { 
        transform: rotate(360deg); /* 旋转 360 度 */
        color: #3b82f6; /* 图标变亮蓝 */
    }

    /* --- 统计卡片 --- */
    .summary-grid { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 35px; 
        margin-bottom: 45px; 
    }
    .stat-card { 
        background: #ffffff; 
        padding: 35px 45px; 
        border-radius: 28px; 
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05); 
        display: flex;
        align-items: center;
        gap: 25px;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
    }
    
    .card-revenue { border-left: 6px solid #06b6d4; }
    .card-units { border-left: 6px solid #3b82f6; }

    .icon-box {
        width: 76px; height: 76px;
        border-radius: 22px;
        display: flex; align-items: center; justify-content: center;
        font-size: 34px;
    }

    .icon-revenue { background: #cffafe; color: #0891b2; }
    .icon-units { background: #dbeafe; color: #1d4ed8; }

    .stat-info { flex: 1; }
    .stat-label { color: #64748b; font-size: 14px; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .stat-value { font-size: 46px; font-weight: 900; color: #0f172a; letter-spacing: -1.5px; }

    /* --- 排行榜 --- */
    .top5-card { 
        background: #ffffff; 
        border-radius: 32px; 
        padding: 50px; 
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05); 
        border-top: 6px solid #ef4444; 
    }
    .card-title { font-size: 30px; font-weight: 900; color: #0f172a; margin-bottom: 40px; display: flex; align-items: center; gap: 15px; }
    
    .product-list-item { 
        display: flex; 
        align-items: center; 
        gap: 25px; 
        padding: 22px 28px; 
        border-radius: 24px; 
        background: #f8fafc; 
        margin-bottom: 16px; 
        transition: all 0.3s ease;
    }
    .product-list-item:hover { 
        background: #ffffff; 
        transform: translateY(-4px); 
        box-shadow: 0 15px 30px rgba(239, 68, 68, 0.08); 
    }

    .rank-badge { width: 54px; height: 54px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 22px; }
    .rank-1 { background: linear-gradient(135deg, #fde047 0%, #f59e0b 100%); color: #fff; }
    .rank-2 { background: #e2e8f0; color: #64748b; }
    .rank-3 { background: #fed7aa; color: #ea580c; }

    .p-thumb { width: 84px; height: 84px; border-radius: 20px; object-fit: cover; border: 1px solid #e2e8f0; }
    .p-details { flex: 1; }
    .p-details h4 { margin: 0 0 12px 0; font-size: 20px; font-weight: 800; color: #1e293b; }
    
    .p-progress-bg { height: 10px; background: #f1f5f9; border-radius: 10px; width: 100%; overflow: hidden; }
    .p-progress-fill { height: 100%; background: #ef4444; border-radius: 10px; transition: width 1.2s ease; }

    .p-stats { text-align: right; min-width: 150px; }
    .sold-count { display: block; font-size: 24px; font-weight: 900; color: #0f172a; }
    .revenue { font-size: 16px; font-weight: 800; color: #e11d48; background: #ffe4e6; padding: 6px 12px; border-radius: 10px; display: inline-block; }
</style>

<div id="sales-app" class="sales-container" style="display:none;">
    <div class="page-header">
        <div>
            <h1>Sales Analytics</h1>
            <p>Overview of your store's performance and top-selling items.</p>
        </div>
        <a href="javascript:location.reload();" class="btn-refresh">
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const salesApp = document.getElementById('sales-app');
        salesApp.style.display = 'block';
        
        // 自动注入逻辑
        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
        if (viewport) {
            viewport.innerHTML = '';
            viewport.appendChild(salesApp);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
