<?php
// TEE DE YONG's Part: Luxury Order History (Ultra-Optimized UI)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

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

<link rel="stylesheet" href="../css/app.css?v=1.15">

<template id="order_premium_layout">
    <div class="order-container">
        <div class="page-header">
            <h1>Order History</h1>
            <a href="javascript:void(0);" onclick="triggerSyncRefresh();" class="btn-sync">🔄 Refresh Sync</a>
        </div>

        <form method="GET" action="order_history.php" class="filter-card">
            <input type="text" name="search" class="search-input" placeholder="Search by ID, Customer or Address..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-search">FILTER</button>
        </form>

        <div class="order-list">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $o): 
                    $pStatusClass = (strtolower($o['payment_status'] ?? '') == 'paid') ? 'p-paid' : 'p-pending';
                    $oStatusClass = (strtolower($o['order_status'] ?? '') == 'delivered') ? 'o-deliv' : 'o-proc';
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

<script src="../js/app.js?v=1.15"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
