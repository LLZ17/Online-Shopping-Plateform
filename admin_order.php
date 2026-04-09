<?php
// TEE DE YONG's Part: Order Management - FINAL COMPLETED VERSION
require_once 'includes/_base.php';
require_once 'includes/db.php';

$_title = 'Admin | Order Management';
include 'includes/_admin_head.php';

// 1. 处理状态更新逻辑 (更新 order_status 和 payment_status)
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE order_id = ?");
    $stmt->execute([$_POST['new_order_status'], $_POST['new_payment_status'], $_POST['order_id']]);
    echo "<script>alert('Order fulfillment updated successfully!'); window.location='admin_order.php';</script>";
}

// 2. 获取订单数据 (包含所有隐藏字段，按日期排序)
$orders = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC")->fetchAll();
?>

<style>
    /* 全局现代化样式：Inter 字体与柔和背景 */
    body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
    .content-wrapper { padding: 40px; }
    
    /* 极大化卡片设计：20px 圆角与软阴影 */
    .order-card { background: white; border-radius: 20px; padding: 35px; box-shadow: 0 10px 40px rgba(0,0,0,0.03); }
    
    /* 表格布局优化：极大化间距 */
    table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
    th { color: #888; font-size: 13px; text-transform: uppercase; padding: 0 15px; text-align: left; }
    td { padding: 25px 15px; background: white; vertical-align: middle; border: none; }
    td:first-child { border-radius: 15px 0 0 15px; }
    td:last-child { border-radius: 0 15px 15px 0; }
    
    /* 状态标签 (Badges) 样式 */
    .badge { padding: 8px 16px; border-radius: 30px; font-size: 11px; font-weight: 700; text-transform: uppercase; display: inline-block; }
    .status-processing { background: #fff4e5; color: #ff9800; }
    .status-shipped { background: #e3f2fd; color: #2196f3; }
    .status-delivered { background: #e8f5e9; color: #4caf50; }
    .status-canceled { background: #ffebee; color: #f44336; }
    
    .payment-paid { background: #e8f5e9; color: #4caf50; border: 1px solid; }
    .payment-pending { background: #f1f2f6; color: #7f8c8d; border: 1px solid; }

    /* MANAGEMENT 垂直组合样式 (对应你的截图) */
    .mgmt-select { 
        width: 100%; 
        height: 48px; 
        border-radius: 14px; 
        padding: 0 15px; 
        font-size: 15px; 
        font-weight: 500;
        background-color: #fff;
        margin-bottom: 10px;
        cursor: pointer;
        outline: none;
    }
    
    .save-btn { 
        width: 100%; 
        height: 52px; 
        background: #1a1a1a; 
        color: #ffffff; 
        border: none; 
        border-radius: 14px; 
        font-size: 16px; 
        font-weight: 700; 
        cursor: pointer; 
        transition: 0.3s;
    }
    .save-btn:hover { background: #000; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>

<div class="content-wrapper">
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 36px; font-weight: 800; color: #1a1a1a;">Sales & Order Management</h1>
        <p style="color: #666; font-size: 16px;">Detailed tracking of customer purchases and fulfillment status.</p>
    </div>

    <div class="order-card">
        <table>
            <thead>
                <tr>
                    <th>Order Details</th>
                    <th>Product Info (图3)</th>
                    <th>Total Price</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                    <th style="width: 220px;">Management</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>
                        <div style="font-weight: 800; font-size: 16px; color: #1a1a1a;">#<?= $o['order_id'] ?></div>
                        <div style="font-size: 12px; color: #999; margin-top:5px;">
                            Customer: <?= $o['user_id'] ?><br>
                            <?= $o['order_date'] ?>
                        </div>
                    </td>

                    <td>
                        <div style="font-weight: 600; color: #333; font-size: 15px;">Product ID: <?= $o['product_id'] ?></div>
                        <div style="font-size: 12px; color: #888; margin-top: 6px; line-height: 1.6;">
                            Size: <b style="color:#000"><?= $o['selected_size'] ?? 'N/A' ?></b> | 
                            Colour: <b style="color:#000"><?= $o['colour'] ?? 'N/A' ?></b><br>
                            Quantity: <b style="color:#000"><?= $o['quantity'] ?></b>
                        </div>
                    </td>

                    <td>
                        <div style="font-weight: 800; font-size: 18px; color: #1a1a1a;">
                            RM <?= number_format($o['total_price'], 2) ?>
                        </div>
                        <?php if($o['points_discount_amount'] > 0): ?>
                            <div style="font-size: 11px; color: #e74c3c; font-weight: 600;">- RM <?= $o['points_discount_amount'] ?> (Points Discount)</div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <span class="badge status-<?= strtolower($o['order_status']) ?>">
                            <?= $o['order_status'] ?>
                        </span>
                    </td>

                    <td>
                        <span class="badge payment-<?= strtolower($o['payment_status']) ?>">
                            <?= $o['payment_status'] ?>
                        </span>
                        <div style="font-size: 10px; color: #bbb; margin-top: 6px;">Method: <?= $o['payment_method'] ?></div>
                    </td>

                    <td>
                        <form method="post" style="display: flex; flex-direction: column; gap: 0;">
                            <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                            
                            <select name="new_order_status" class="mgmt-select" style="border: 1.5px solid #000;">
                                <?php 
                                    $o_opts = ['Processing', 'Shipped', 'Delivered', 'Canceled'];
                                    foreach($o_opts as $opt) {
                                        $sel = ($o['order_status'] == $opt) ? 'selected' : '';
                                        echo "<option value='$opt' $sel>$opt</option>";
                                    }
                                ?>
                            </select>

                            <select name="new_payment_status" class="mgmt-select" style="border: 1.5px solid #ccc;">
                                <?php 
                                    $p_opts = ['Pending', 'Paid', 'Failed', 'Canceled'];
                                    foreach($p_opts as $opt) {
                                        $sel = ($o['payment_status'] == $opt) ? 'selected' : '';
                                        echo "<option value='$opt' $sel>$opt</option>";
                                    }
                                ?>
                            </select>

                            <button type="submit" name="update_status" class="save-btn">
                                Save Fulfillment
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/_foot.php'; ?>