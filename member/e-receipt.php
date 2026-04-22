<?php
/**
 * PXX MALL - AUTOMATED INVOICE SYSTEM (V4.1)
 * 基于你的 OTP 发信架构优化
 */

// --- [STAGE 1: 环境与依赖] ---
ob_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// 复用你代码里的绝对路径加载方式
require_once '../lib/Exception.php'; 
require_once '../lib/PHPMailer.php'; 
require_once '../lib/SMTP.php';
require_once '../includes/db.php';

session_start();

// 权限检查
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    die("Unauthorized access.");
}

$order_id = $_GET['order_id'];
$user_id  = $_SESSION['user_id'];

// --- [STAGE 2: 数据提取] ---
try {
    // 提取订单和用户信息
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) { die("Order not found."); }

    // 提取商品清单
    $item_stmt = $pdo->prepare("SELECT oi.*, p.product_name FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
    $item_stmt->execute([$order_id]);
    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Database Error.");
}

// --- [STAGE 3: PHPMailer 发信引擎] ---
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    // 复用你 OTP 代码中的 SMTP 配置
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'pxxmallmalaysia@gmail.com'; 
    $mail->Password   = 'wfnh iygk roxj ibxz'; // 建议稍后将此敏感信息放入 config 文件
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->CharSet    = 'utf-8';

    // 解决 SSL 问题 (复用你的配置)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom('pxxmallmalaysia@gmail.com', 'PXX Mall Billing System');
    $mail->addAddress($order['email'], $order['username']);
    $mail->isHTML(true);
    $mail->Subject = "Invoice for Your Order #INV-" . str_pad($order_id, 6, '0', STR_PAD_LEFT);

    // --- [STAGE 4: 构建发票 HTML 正文] ---
    // 这里复用你第一个发票模板的 UI 风格
    $items_html = "";
    foreach($items as $item) {
        $subtotal = number_format($item['price'] * $item['quantity'], 2);
        $items_html .= "<tr>
            <td style='padding:10px; border-bottom:1px solid #eee;'>{$item['product_name']} (x{$item['quantity']})</td>
            <td align='right' style='padding:10px; border-bottom:1px solid #eee;'>RM {$subtotal}</td>
        </tr>";
    }

    $mail->Body = "
    <div style='font-family:sans-serif; background:#f4f4f4; padding:30px;'>
        <div style='max-width:600px; margin:auto; background:#fff; border-top:10px solid #e60012; padding:40px; border-radius:8px;'>
            <h1 style='color:#e60012;'>PXX Mall</h1>
            <p>Hi <strong>{$order['username']}</strong>,</p>
            <p>Thank you for shopping with us! Here is your order summary:</p>
            
            <table width='100%' cellspacing='0' cellpadding='0' style='margin-top:20px;'>
                <thead>
                    <tr style='background:#f9f9f9;'>
                        <th align='left' style='padding:10px;'>Description</th>
                        <th align='right' style='padding:10px;'>Total</th>
                    </tr>
                </thead>
                <tbody>
                    $items_html
                </tbody>
            </table>

            <div style='text-align:right; margin-top:20px; font-size:20px;'>
                <strong>Grand Total: RM " . number_format($order['total_amount'], 2) . "</strong>
            </div>

            <hr style='border:none; border-top:1px solid #eee; margin:30px 0;'>
            <p style='font-size:12px; color:#999; text-align:center;'>
                This is an automated invoice. No signature is required.<br>
                PXX Mall Malaysia Sdn. Bhd. © 2026
            </p>
        </div>
    </div>";

    if ($mail->send()) {
        echo "<script>alert('Invoice has been sent to {$order['email']}'); window.location.href='profile.php?page=orders';</script>";
    }

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}