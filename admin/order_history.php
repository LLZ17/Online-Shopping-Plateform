<?php
// TEE DE YONG's Part: Luxury Order History (Ultra-Optimized UI)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 💡 1. 状态更新逻辑 (修正跳转文件名为 order_history.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_all_status') {
    $order_id = $_POST['order_id'];
    $p_status = $_POST['payment_status'];
    $o_status = $_POST['order_status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, order_status = ? WHERE order_id = ?");
    if ($stmt->execute([$p_status, $o_status, $order_id])) {
        header("Location: order_history.php?updated=1");
        exit;
    }
}

// 💡 2. 获取数据 (按日期最新排序)
$search = $_GET['search'] ?? '';
$whereSQL = $search ? "WHERE order_id LIKE ? OR shipping_address LIKE ?" : "";
$params = $search ? ["%$search%", "%$search%"] : [];

try {
    $sql = "SELECT * FROM orders $whereSQL ORDER BY order_date DESC, order_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $orders = []; }

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<template id="order_premium_layout">
    <div class="order-container">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
            
            .order-container { padding: 35px; font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; min-height: 100vh; animation: pageFadeIn 0.5s ease-out; }
            @keyframes pageFadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

            /* --- Header Section --- */
            .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
            .page-header h1 { font-size: 34px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -1.5px; }
            .btn-sync { background: #ffffff; color: #64748b; padding: 12px 24px; border-radius: 14px; text-decoration: none; font-weight: 700; border: 1px solid #e2e8f0; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
            .btn-sync:hover { color: #0f172a; border-color: #cbd5e1; transform: translateY(-2px); }

            /* --- Search Bar --- */
            .filter-card { background: #ffffff; padding: 20px; border-radius: 24px; border: 1px solid rgba(226, 232, 240, 0.8); margin-bottom: 30px; display: flex; gap: 15px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.04); }
            .search-input { flex-grow: 1; padding: 14px 22px; border-radius: 14px; border: 1px solid #e2e8f0; font-family: inherit; font-weight: 600; outline: none; transition: all 0.3s; background: #fcfdfe; }
            .search-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
            .btn-search { background: #0f172a; color: #fff; padding: 14px 35px; border-radius: 14px; border: none; font-weight: 800; cursor: pointer; transition: all 0.3s; }
            .btn-search:hover { transform: scale(1.02); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2); }

            /* --- Order List --- */
            .order-item { background: #ffffff; border-radius: 30px; border: 1px solid rgba(226, 232, 240, 0.7); margin-bottom: 25px; overflow: hidden; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
            .order-item:hover { transform: translateY(-8px); border-color: #3b82f6; box-shadow: 0 30px 60px -15px rgba(15, 23, 42, 0.1); }

            .order-top { padding: 18px 30px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
            .order-id-tag { font-size: 16px; font-weight: 800; color: #0f172a; }
            
            /* Status Coloring */
            .badge-p { padding: 6px 14px; border-radius: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; margin-right: 8px; }
            .p-paid { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
            .p-pending { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
            
            .badge-o { padding: 6px 14px; border-radius: 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
            .o-deliv { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
            .o-proc { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

            .order-body { padding: 30px; display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 30px; }
            .info-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
            .info-value { font-size: 14px; font-weight: 700; color: #1e293b; line-height: 1.5; }

            /* Update Form */
            .action-box { background: #f8fafc; padding: 20px; border-radius: 20px; border: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 12px; margin-top: 10px; }
            .select-control { padding: 10px; border-radius: 10px; border: 1px solid #cbd5e1; font-family: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
            .btn-save { background: #0f172a; color: #fff; padding: 12px; border-radius: 10px; border: none; font-weight: 800; cursor: pointer; transition: 0.3s; }
            .btn-save:hover { background: #2563eb; }

            .price-display { font-size: 26px; font-weight: 900; color: #059669; letter-spacing: -1px; }
        </style>

        <div class="page-header">
            <h1>Order History</h1>
            <a href="order_history.php" class="btn-sync">🔄 Refresh Sync</a>
        </div>

        <form method="GET" action="order_history.php" class="filter-card">
            <input type="text" name="search" class="search-input" placeholder="Search by ID, Customer or Address..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-search">FILTER</button>
        </form>

        <div class="order-list">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $o): 
                    $pStatusClass = (strtolower($o['payment_status']) == 'paid') ? 'p-paid' : 'p-pending';
                    $oStatusClass = (strtolower($o['order_status']) == 'delivered') ? 'o-deliv' : 'o-proc';
                ?>
                <div class="order-item">
                    <div class="order-top">
                        <div class="order-id-tag">ORDER #<?= $o['order_id'] ?> <span style="font-weight:400; color:#94a3b8; margin-left:15px; font-size:13px;">📅 <?= $o['order_date'] ?></span></div>
                        <div style="display:flex;">
                            <span class="badge-p <?= $pStatusClass ?>">💳 <?= $o['payment_status'] ?></span>
                            <span class="badge-o <?= $oStatusClass ?>">📦 <?= $o['order_status'] ?></span>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div>
                            <span class="info-label">Customer Details & Destination</span>
                            <div class="info-value" style="color:#3b82f6; margin-bottom:5px;">User ID: <?= $o['user_id'] ?></div>
                            <div class="info-value"><?= htmlspecialchars($o['shipping_address'] ?: 'N/A') ?></div>
                        </div>

                        <div>
                            <span class="info-label">Items Purchased</span>
                            <div class="info-value">PID: <?= $o['product_id'] ?> (x<?= $o['quantity'] ?>)</div>
                            <div class="info-value" style="color:#64748b; font-size:12px; margin-top:5px;">
                                Color: <?= $o['colour'] ?> | Size: <?= $o['selected_size'] ?>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <span class="info-label">Total Amount</span>
                            <div class="price-display">RM <?= number_format($o['total_price'], 2) ?></div>
                            
                            <form method="POST" class="action-box">
                                <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                <input type="hidden" name="action" value="update_all_status">
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                                    <select name="payment_status" class="select-control">
                                        <option value="Pending" <?= $o['payment_status']=='Pending'?'selected':'' ?>>Pending</option>
                                        <option value="Paid" <?= $o['payment_status']=='Paid'?'selected':'' ?>>Paid</option>
                                    </select>
                                    <select name="order_status" class="select-control">
                                        <option value="Processing" <?= $o['order_status']=='Processing'?'selected':'' ?>>Processing</option>
                                        <option value="Shipped" <?= $o['order_status']=='Shipped'?'selected':'' ?>>Shipped</option>
                                        <option value="Delivered" <?= $o['order_status']=='Delivered'?'selected':'' ?>>Delivered</option>
                                        <option value="Cancelled" <?= $o['order_status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn-save">SAVE STATUS</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:120px; background:white; border-radius:30px; border:1px solid #e2e8f0;">
                    <div style="font-size:80px; margin-bottom:20px; opacity:0.5;">📋</div>
                    <h2 style="font-weight:800; color:#1e293b;">No matching orders</h2>
                    <p style="color:#94a3b8;">Try clearing filters or check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</template>

<script>
(function() {
    function runInjection() {
        const template = document.getElementById('order_premium_layout');
        const viewport = document.querySelector('.admin-content-viewport') || 
                         document.querySelector('.main-content') || 
                         document.querySelector('main');
        if (viewport && template && !viewport.querySelector('.order-container')) {
            viewport.innerHTML = ''; 
            viewport.appendChild(template.content.cloneNode(true));
        }
    }
    const observer = new MutationObserver(runInjection);
    observer.observe(document.body, { childList: true, subtree: true });
    window.addEventListener('load', runInjection);
    setInterval(runInjection, 1000);
})();
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
