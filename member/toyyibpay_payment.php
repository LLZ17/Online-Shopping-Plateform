<?php
session_start();
require_once '../includes/db.php';

/**
 * 1. 基础安全检查
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_order'])) {
    header('Location: ../login.php');
    exit();
}

/**
 * 2. 获取数据
 */
$user_id = $_SESSION['user_id'];
$pending_data = $_SESSION['pending_order_data'];

$u_stmt = $pdo->prepare("SELECT real_name, phone_num, email FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user_info = $u_stmt->fetch(PDO::FETCH_ASSOC);

/**
 * 3. ToyyibPay API 配置
 */
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3'; 
$categoryCode  = 'jmf56yjy';
$apiUrl        = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl    = 'https://dev.toyyibpay.com/';

$orderId = "PXX-" . date("His") . "-" . rand(100, 999);
// ToyyibPay 金额要求：分(Cent)，必须是整数
$billAmount = round(floatval($pending_data['total_price']), 2) * 100;

/**
 * 4. 组装数据 (不再强制传递银行ID，让用户在支付页自选)
 */
$postData = [
    'userSecretKey'    => $userSecretKey,
    'categoryCode'     => $categoryCode,
    'billName'         => 'PXX Mall Order',
    'billDescription'  => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo'    => 1,
    'billAmount'       => $billAmount,
    'billReturnUrl'    => "http://localhost:8001/payment_return.php",
    'billExternalReferenceNo' => $orderId,
    'billTo'           => !empty($user_info['real_name']) ? $user_info['real_name'] : 'Customer',
    'billEmail'        => !empty($pending_data['email']) ? $pending_data['email'] : $user_info['email'],
    'billPhone'        => !empty($user_info['phone_num']) ? $user_info['phone_num'] : '0123456789'
];

/**
 * 5. 发起请求
 */
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false
]);

$result = curl_exec($curl);
curl_close($curl);

/**
 * 6. 处理响应
 */
$obj = json_decode($result);

if (is_array($obj) && isset($obj[0]->BillCode)) {
    $_SESSION['payment_order_number'] = $orderId;
    // 成功直接跳转，用户在 ToyyibPay 页面点击下拉框选择银行即可
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    // 如果报错，直接显示返回的内容，方便调试
    die("Payment Error: " . htmlspecialchars($result));
}
?>