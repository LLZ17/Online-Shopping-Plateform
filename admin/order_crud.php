<?php
// TEE DE YONG's Part: Sales Data Analytics Page (Ultimate Premium Edition - Fixed Header)
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
    
    .page-header { margin-bottom: 45px; }
    
    /* 💡 修复：删除了会导致某些浏览器隐形的渐变色代码，换回最稳妥的高级深色 */
    .page-header h1 { 
        font-size: 42px; 
        font-weight: 900; 
        color: #0f172a; 
        letter-spacing: -1.5px; 
        margin: 0; 
    }
    .page-header p { color: #64748b; margin-top: 10px; font-size: 16px; font-weight: 500; }

    /* --- 顶部概览卡片基础样式 --- */
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
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.02); 
        display: flex;
        align-items: center;
        gap: 25px;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
    }
    
    /* 🟦 1. 营业额 (Total Revenue) - 青色 (Cyan) */
    .card-revenue {
        border: 1px solid rgba(6, 182, 212, 0.15);       
        border-left: 6px solid #06b6d4;  
    }
    .card-revenue:hover { 
        border-color: rgba(6, 182, 212, 0.4); 
        transform: translateY(-6px) scale(1.01); 
        box-shadow: 0 25px 50px -12px rgba(6, 182, 212, 0.15), 0 4px 10px rgba(6, 182, 212, 0.05); 
    }
    .icon-revenue { background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%); color: #0891b2; box-shadow: 0 8px 16px -4px rgba(6, 182, 212, 0.2); }

    /* 🟦 2. 销量 (Units Sold) - 蓝色 (Blue) */
    .card-units {
        border: 1px solid rgba(59, 130, 246, 0.15);       
        border-left: 6px solid #3b82f6;  
    }
    .card-units:hover { 
        border-color: rgba(59, 130, 246, 0.4); 
        transform: translateY(-6px) scale(1.01); 
        box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.15), 0 4px 10px rgba(59, 130, 246, 0.05); 
    }
    .icon-units { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1d4ed8; box-shadow: 0 8px 16px -4px rgba(59, 130, 246, 0.2); }

    .icon-box {
        width: 76px; height: 76px;
        border-radius: 22px;
        display: flex; align-items: center; justify-content: center;
        font-size: 34px;
    }

    .stat-info { flex: 1; }
    .stat-label { color: #64748b; font-size: 14px; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 1.5px; }
    .stat-value { font-size: 46px; font-weight: 900; color: #0f172a; letter-spacing: -1.5px; }
    .currency-symbol { font-size: 24px; color: #94a3b8; font-weight: 700; margin-right: 4px; }
    .unit-text { font-size: 20px; color: #94a3b8; font-weight: 700; margin-left: 4px; }

    /* 🟥 3. Top 5 排行榜 - 红色 (Red) */
    .top5-card { 
        background: #ffffff; 
        border-radius: 32px; 
        padding: 50px; 
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05); 
        border: 1px solid rgba(239, 68, 68, 0.15); 
        border-top: 6px solid #ef4444; 
        width: 100%;
        transition: all 0.4s ease;
    }
    .top5-card:hover {
        box-shadow: 0 25px 50px -12px rgba(239, 68, 68, 0.1);
    }
    .card-title { 
        font-size: 30px; 
        font-weight: 900; 
        color: #0f172a; 
        margin-bottom: 40px; 
        display: flex; 
        align-items: center; 
        gap: 15px; 
        letter-spacing: -0.5px;
    }
    
    .product-list-item { 
        display: flex; 
        align-items: center; 
        gap: 25px; 
        padding: 22px 28px; 
        border-radius: 24px; 
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); 
        margin-bottom: 16px; 
        border: 1px solid transparent; 
        background: #f8fafc; 
    }
    .product-list-item:hover { 
        background: #ffffff; 
        border-color: rgba(239, 68, 68, 0.2); 
        transform: translateY(-4px) scale(1.005); 
        box-shadow: 0 15px 30px -8px rgba(239, 68, 68, 0.08); 
    }

    /* 名次徽章 */
    .rank-badge { width: 54px; height: 54px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 22px; }
    .rank-1 { background: linear-gradient(135deg, #fde047 0%, #f59e0b 100%); color: #fff; box-shadow: 0 6px 12px rgba(245, 158, 11, 0.2); }
    .rank-2 { background: linear-gradient(135deg, #e2e8f0 0%, #94a3b8 100%); color: #fff; box-shadow: 0 6px 12px rgba(148, 163, 184, 0.2); }
    .rank-3 { background: linear-gradient(135deg, #fed7aa 0%, #ea580c 100%); color: #fff; box-shadow: 0 6px 12px rgba(234, 88, 12, 0.2); }
    .rank-other { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    /* 商品图片 */
    .p-thumb { width: 84px; height: 84px; border-radius: 20px; object-fit: cover; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);}
    
    .p-details { flex: 1; }
    .p-details h4 { margin: 0 0 12px 0; font-size: 20px; font-weight: 800; color: #1e293b; letter-spacing: -0.3px; }
    
    /* 进度条 */
    .p-progress-bg { height: 10px; background: #f1f5f9; border-radius: 10px; width: 100%; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .p-progress-fill { 
        height: 100%; 
        background: linear-gradient(90deg, #ef4444 0%, #f43f5e 50%, #fb7185 100%); 
        border-radius: 10px; 
        transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1); 
        position: relative;
    }
    .p-progress-fill::after {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(180deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 100%);
        border-radius: 10px;
    }

    .p-stats { text-align: right; min-width: 150px; }
    .sold-count { display: block; font-size: 24px; font-weight: 900; color: #0f172a; margin-bottom: 4px; letter-spacing: -0.5px; }
    .sold-count span { font-size: 16px; color: #64748b; font-weight: 700; }
    .revenue { font-size: 16px; font-weight: 800; color: #e11d48; background: #ffe4e6; padding: 6px 12px; border-radius: 10px; display: inline-block; box-shadow: 0 2px 4px rgba(225, 29, 72, 0.1);} 
</style>

<div id="sales-app" class="sales-container" style="display:none;">
    <div class="page-header">
        <h1>Sales Data Analytics</h1>
        <p>Overview of your store's performance and top-selling items.</p>
    </div>

    <div class="summary-grid">
        <div class="stat-card card-revenue">
            <div class="icon-box icon-revenue">💵</div>
            <div class="stat-info">
                <span class="stat-label">Total Revenue</span>
                <div class="stat-value"><span class="currency-symbol">RM</span><?= number_format($summary['total_revenue'] ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="stat-card card-units">
            <div class="icon-box icon-units">📦</div>
            <div class="stat-info">
                <span class="stat-label">Units Sold</span>
                <div class="stat-value"><?= number_format($summary['total_orders'] ?? 0) ?><span class="unit-text">items</span></div>
            </div>
        </div>
    </div>

    <div class="top5-card">
        <div class="card-title">🏆 Top 5 Selling Products</div>
        
        <?php if (!empty($topProducts)): ?>
            <?php foreach ($topProducts as $index => $p): 
                $rank = $index + 1;
                $rankClass = ($rank <= 3) ? "rank-$rank" : "rank-other";
                $maxSold = $topProducts[0]['total_sold'];
                // 确保宽度至少有一点点
                $percent = ($maxSold > 0) ? max(($p['total_sold'] / $maxSold * 100), 2) : 0;
            ?>
            <div class="product-list-item">
                <div class="rank-badge <?= $rankClass ?>"><?= $rank ?></div>
                
                <?php if(!empty($p['image_path'])): ?>
                    <img src="../<?= htmlspecialchars($p['image_path']) ?>" class="p-thumb" alt="Product Image">
                <?php else: ?>
                    <div class="p-thumb" style="display:flex; align-items:center; justify-content:center; font-size:26px;">🛍️</div>
                <?php endif; ?>

                <div class="p-details">
                    <h4><?= htmlspecialchars($p['name']) ?></h4>
                    <div class="p-progress-bg">
                        <div class="p-progress-fill" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
                <div class="p-stats">
                    <span class="sold-count"><?= $p['total_sold'] ?> <span>Sold</span></span>
                    <span class="revenue">RM <?= number_format($p['total_revenue'], 2) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:70px 20px; color:#94a3b8; background: #f8fafc; border-radius: 24px; border: 2px dashed #e2e8f0;">
                <p style="font-size:54px; margin-bottom: 20px;">📊</p>
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
        
        // 自动注入到管理后台的 viewport
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
