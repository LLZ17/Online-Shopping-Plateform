<?php
// TEE DE YONG's Part: Admin Portal Dashboard (Top 5 Leaderboard)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// ==========================================
// 🏆 获取 Top 5 商品逻辑
// 提示: 如果你的数据库有 'sales' (销量) 字段，请把 'price DESC' 改成 'sales DESC'
// ==========================================
try {
    $sql = "SELECT * FROM products ORDER BY price DESC LIMIT 5";
    $stmt = $pdo->query($sql);
    $topProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div style='padding:20px; color:red;'>Database Error: " . $e->getMessage() . "</div>");
}

// 引入头部
$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    /* 清理干扰 */
    .page-title-box { display: none !important; }
    html, body { background: #f8fafc !important; }

    /* 仪表盘主容器 */
    .dashboard-container { 
        max-width: 1100px; 
        margin: 30px auto; 
        font-family: 'Inter', sans-serif; 
    }

    /* 欢迎标题 */
    .dash-header { margin-bottom: 30px; }
    .dash-header h1 { font-size: 32px; font-weight: 900; color: #111; margin: 0; letter-spacing: -1px; }
    .dash-header p { color: #64748b; font-weight: 600; margin-top: 5px; }

    /* 排行榜卡片 */
    .top5-card { 
        background: #fff; 
        border-radius: 20px; 
        box-shadow: 0 15px 35px rgba(0,0,0,0.04); 
        border: 1px solid rgba(0,0,0,0.05); 
        overflow: hidden;
        max-width: 600px; /* 适合作为 Dashboard 的一个 Widget */
    }

    .top5-header { 
        background: linear-gradient(135deg, #111 0%, #222 100%); 
        padding: 20px 30px; 
        display: flex; 
        align-items: center; 
        gap: 15px;
    }
    .top5-header h2 { color: #fff; margin: 0; font-size: 20px; font-weight: 800; }
    .top5-icon { font-size: 24px; }

    /* 排行榜列表 */
    .top5-list { padding: 0; margin: 0; list-style: none; }
    .top5-item { 
        display: flex; 
        align-items: center; 
        padding: 20px 30px; 
        border-bottom: 1px solid #f1f5f9; 
        transition: 0.3s;
    }
    .top5-item:last-child { border-bottom: none; }
    .top5-item:hover { background: #f8fafc; }

    /* 排名数字设计 */
    .rank-badge { 
        width: 36px; height: 36px; 
        border-radius: 12px; 
        display: flex; align-items: center; justify-content: center; 
        font-weight: 900; font-size: 16px; 
        margin-right: 20px;
        background: #f1f5f9; color: #94a3b8;
    }
    /* 前三名专属高亮颜色 (金银铜) */
    .rank-1 { background: #fef08a; color: #854d0e; box-shadow: 0 4px 10px rgba(254,240,138,0.5); }
    .rank-2 { background: #e2e8f0; color: #475569; box-shadow: 0 4px 10px rgba(226,232,240,0.5); }
    .rank-3 { background: #fed7aa; color: #9a3412; box-shadow: 0 4px 10px rgba(254,215,170,0.5); }

    .item-details { flex: 1; }
    .item-name { margin: 0; font-size: 16px; font-weight: 800; color: #1e293b; }
    .item-id { margin: 4px 0 0; font-size: 12px; font-weight: 700; color: #94a3b8; }
    .item-price { font-size: 18px; font-weight: 900; color: #e60012; }
</style>

<div id="dashboard-app" class="dashboard-container" style="display:none;">
    <div class="dash-header">
        <h1>System Dashboard</h1>
        <p>Welcome back! Here is the overview of your PXX Mall data.</p>
    </div>

    <div class="top5-card">
        <div class="top5-header">
            <div class="top5-icon">👑</div>
            <h2>Top 5 Selling Products</h2>
        </div>
        <ul class="top5-list">
            <?php if (count($topProducts) > 0): ?>
                <?php foreach ($topProducts as $index => $p): ?>
                    <?php 
                        $rank = $index + 1; 
                        // 给前三名分配特殊颜色 class
                        $rankClass = ($rank <= 3) ? "rank-{$rank}" : "";
                    ?>
                    <li class="top5-item">
                        <div class="rank-badge <?= $rankClass ?>"><?= $rank ?></div>
                        <div class="item-details">
                            <h3 class="item-name"><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="item-id">Product ID: #<?= $p['product_id'] ?></p>
                        </div>
                        <div class="item-price">RM <?= number_format($p['price'], 2) ?></div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="top5-item" style="justify-content: center; color: #94a3b8; font-weight: 600;">
                    No product data available.
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script>
    // ⚔️ 仪表盘专属追踪脚本，确保你的 Widget 能在主页显示
    document.addEventListener("DOMContentLoaded", function() {
        const myContent = document.getElementById('dashboard-app');
        if (!myContent) return;
        myContent.style.display = 'block';

        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
        
        if (!viewport) {
            const allElements = document.body.getElementsByTagName('*');
            for (let el of allElements) {
                if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                    viewport = el.parentElement;
                    break;
                }
            }
        }

        if (viewport) {
            viewport.innerHTML = ''; 
            viewport.style.setProperty('overflow-y', 'auto', 'important');
            viewport.style.setProperty('height', '100vh', 'important');
            viewport.style.setProperty('padding-bottom', '50px', 'important');
            viewport.appendChild(myContent);
        } else {
            myContent.style.position = 'fixed';
            myContent.style.top = '60px'; 
            myContent.style.right = '0';
            myContent.style.width = 'calc(100vw - 250px)'; 
            myContent.style.height = '100vh';
            myContent.style.overflowY = 'auto';
            myContent.style.background = '#f8fafc';
            myContent.style.zIndex = '9999';
            myContent.style.padding = '30px';
            document.body.appendChild(myContent);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>