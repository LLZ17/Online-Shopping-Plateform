<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }

// 检查 Session 是否有数据
if (!isset($_SESSION['pending_order_data'])) {
    die("Error: No pending order data found. Please go back to checkout.");
}

$pending_data = $_SESSION['pending_order_data'];
$user_id = $_SESSION['user_id'];

// 获取用户信息
$u_stmt = $pdo->prepare("SELECT real_name, phone_num, email FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user_info = $u_stmt->fetch();

// --- ToyyibPay 参数准备 ---
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode = 'jmf56yjy';
$apiUrl = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl = 'https://dev.toyyibpay.com/';

// 1. 强制转换金额：必须是整数（分），不能有小数点
// 例如 RM 10.50 必须变成 1050
$raw_amount = floatval($pending_data['total_price']);
$billAmount = round($raw_amount, 2) * 100;

// 2. 检查金额是否合法
if ($billAmount <= 0) {
    die("Error: Invalid payment amount (RM " . $raw_amount . ").");
}

// 3. 准备数据并处理可能的空值（ToyyibPay 必填项）
$payorName  = !empty($user_info['real_name']) ? $user_info['real_name'] : 'Customer';
$payorEmail = !empty($pending_data['email']) ? $pending_data['email'] : ($user_info['email'] ?? 'test@test.com');
$payorPhone = !empty($user_info['phone_num']) ? $user_info['phone_num'] : '0123456789';

$orderId = "PXX-" . date("His") . "-" . rand(100, 999);

$data = array(
    'userSecretKey' => $userSecretKey,
    'categoryCode' => $categoryCode,
    'billName' => 'PXX Mall Order',
    'billDescription' => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => $billAmount,
    'billReturnUrl' => "http://localhost:8001/payment_return.php",
    'billExternalReferenceNo' => $orderId,
    'billTo' => $payorName,
    'billEmail' => $payorEmail,
    'billPhone' => $payorPhone
);

$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, $apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($curl);
curl_close($curl);

$obj = json_decode($result);

if (isset($obj[0]->BillCode)) {
    $_SESSION['payment_order_number'] = $orderId;
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    // 如果还是报错，打印出完整的错误详情排查
    echo "<h3>Payment Gateway Error</h3>";
    echo "Raw Result: " . htmlspecialchars($result);
    echo "<br><br><a href='checkout.php'>Go Back</a>";
}
?>