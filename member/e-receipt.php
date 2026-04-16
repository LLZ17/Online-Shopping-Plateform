<?php
/**
 * =================================================================================
 * PXX MALL - PROFESSIONAL e-INVOICE SYSTEM (V400.0)
 * =================================================================================
 */

session_start();
require_once 'includes/db.php'; // 确保你的数据库连接文件正确

// 1. 权限与参数检查
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    die("Unauthorized access or Order ID missing.");
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];
$brand_green = '#28a745'; 
$brand_red   = '#e60012'; // Logo 专用红色

// --- [STAGE 2: 从 SQL 提取数据] ---
try {
    // A. 提取订单详情 + 用户信息 + 银行/卡号资料
    // 假设你的 users 表有 card_number 和 bank_account 字段
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email, u.card_number, u.bank_account, u.address as user_address
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $data = $stmt->fetch();

    if (!$data) { die("Order record not found."); }

    // B. 提取商品清单
    $item_stmt = $pdo->prepare("
        SELECT oi.*, p.product_name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id = ?
    ");
    $item_stmt->execute([$order_id]);
    $items = $item_stmt->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

/**
 * 隐藏敏感信息逻辑
 */
function maskInfo($str, $show = 4) {
    if (!$str) return "N/A";
    return str_repeat("X", strlen($str) - $show) . substr($str, -$show);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PXX_Invoice_<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 0 !important; }
            .invoice-box { width: 100% !important; border: 1px solid #eee !important; box-shadow: none !important; margin: 0 !important; }
        }

        body { background: #f4f7f6; font-family: 'Inter', sans-serif; padding: 40px 0; color: #333; }
        .invoice-box { background: #fff; width: 850px; margin: auto; border-radius: 20px; box-shadow: 0 30px 60px rgba(0,0,0,0.1); overflow: hidden; border-top: 10px solid <?php echo $brand_green; ?>; }
        
        /* Logo 样式 */
        .logo-text { color: <?php echo $brand_red; ?>; font-size: 40px; font-weight: 900; letter-spacing: -2px; margin: 0; }
        .logo-text span { font-size: 18px; vertical-align: top; margin-left: 2px; font-weight: 700; }

        .header { padding: 50px; display: flex; justify-content: space-between; border-bottom: 2px solid #f8f9fa; }
        .company-info p { margin: 3px 0; font-size: 13px; color: #777; line-height: 1.5; }

        .meta-info { text-align: right; }
        .meta-info h2 { margin: 0; font-size: 28px; color: #2d3436; }

        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; padding: 40px 50px; background: #fafafa; }
        .section-title { font-size: 11px; font-weight: 900; color: #bbb; text-transform: uppercase; margin-bottom: 10px; display: block; }
        
        .item-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .item-table th { background: #fcfcfc; padding: 15px; border-bottom: 2px solid #eee; text-align: left; font-size: 12px; color: #999; }
        .item-table td { padding: 15px; border-bottom: 1px solid #f6f6f6; }

        .summary { padding: 30px 50px 50px 50px; display: flex; justify-content: flex-end; }
        .total-card { width: 300px; text-align: right; }
        .total-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 16px; }
        .grand-total { border-top: 2px solid <?php echo $brand_green; ?>; padding-top: 15px; margin-top: 10px; font-size: 24px; font-weight: 900; color: <?php echo $brand_green; ?>; }

        .footer { background: #fcfcfc; padding: 30px; text-align: center; color: #ccc; font-size: 11px; border-top: 1px solid #eee; }

        /* 按钮 */
        .action-bar { width: 850px; margin: 0 auto 20px auto; display: flex; justify-content: space-between; }
        .btn { padding: 12px 25px; border-radius: 8px; border: none; font-weight: 700; cursor: pointer; text-decoration: none; }
        .btn-pdf { background: <?php echo $brand_green; ?>; color: #fff; }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <a href="index.php" style="color:#888; text-decoration:none;"><i class="fas fa-chevron-left"></i> Back</a>
        <button onclick="window.print()" class="btn btn-pdf"><i class="fas fa-download"></i> SAVE AS PDF</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="company-info">
                <h1 class="logo-text">PXX Mall<span>&reg;</span></h1>
                <div style="margin-top:15px;">
                    <p><strong>PXX MALL MALAYSIA SDN BHD</strong></p>
                    <p>Company Reg No: [这里填写你的公司注册号]</p>
                    <p>SST ID: [这里填写你的SST号]</p>
                    <p>Address: [这里填写你的办公地址]</p>
                    <p>Contact: [这里填写你的电话]</p>
                </div>
            </div>
            <div class="meta-info">
                <h2>e-INVOICE</h2>
                <p>#INV-<?php echo str_pad($data['order_id'], 6, '0', STR_PAD_LEFT); ?></p>
                <p>Date: <?php echo date('d M Y', strtotime($data['created_at'])); ?></p>
            </div>
        </div>

        <div class="details-grid">
            <div>
                <span class="section-title">Customer Details</span>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($data['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($data['email']); ?></p>
                <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($data['user_address'] ?? 'No address provided')); ?></p>
            </div>
            <div>
                <span class="section-title">Payment & Bank Info</span>
                <p><strong>Bank Account:</strong> <?php echo maskInfo($data['bank_account']); ?></p>
                <p><strong>Card Number:</strong> <?php echo maskInfo($data['card_number']); ?></p>
                <p><strong>Method:</strong> ToyyibPay / Bank Transfer</p>
            </div>
        </div>

        <div style="padding: 0 50px;">
            <table class="item-table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Unit (RM)</th>
                        <th style="text-align: right;">Total (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($item['price'], 2); ?></td>
                        <td style="text-align: right;"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="summary">
            <div class="total-card">
                <div class="total-row"><span>Subtotal</span><span>RM <?php echo number_format($data['total_amount'], 2); ?></span></div>
                <div class="total-row"><span>SST (0%)</span><span>RM 0.00</span></div>
                <div class="total-row grand-total">
                    <span>GRAND TOTAL</span>
                    <span>RM <?php echo number_format($data['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature required.</p>
            <p>Thank you for shopping with PXX Mall.</p>
        </div>
    </div>

</body>
</html>