<?php
session_start();
require_once '../includes/db.php';

// 1. 检查登录
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// 2. 检查是否有待支付订单数据 (对应 checkout.php 里的键名)
if (!isset($_SESSION['pending_order_data'])) {
    echo "<script>alert('No pending order found. Please try again.'); window.location.href='checkout.php';</script>";
    exit();
}

$pending_data = $_SESSION['pending_order_data'];
$user_id = $_SESSION['user_id'];

// 3. 从数据库补全支付人的姓名和电话（ToyyibPay 必须字段）
$u_stmt = $pdo->prepare("SELECT real_name, phone_num FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user_info = $u_stmt->fetch();

// --- ToyyibPay 配置 ---
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode = 'jmf56yjy';
$baseUrl = "http://localhost:8001/"; // 确保你的本地开发环境地址正确
$returnUrl = $baseUrl . "payment_return.php";

$apiUrl = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl = 'https://dev.toyyibpay.com/';

// 生成临时订单号用于支付网关
$orderId = "PXX-" . date("His") . "-" . rand(100, 999);
$billAmount = $pending_data['total_price'] * 100; // 转换为分 (Sen)

$data = array(
    'userSecretKey' => $userSecretKey,
    'categoryCode' => $categoryCode,
    'billName' => 'PXX Mall Order',
    'billDescription' => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => $billAmount,
    'billReturnUrl' => $returnUrl,
    'billExternalReferenceNo' => $orderId,
    'billTo' => $user_info['real_name'], // 来自数据库
    'billEmail' => $pending_data['email'], // 来自 checkout 填写的 email
    'billPhone' => $user_info['phone_num'] // 来自数据库
);

$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, $apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($curl);
$errors = curl_error($curl);
curl_close($curl);

if ($errors) {
    die("CURL Error: " . $errors);
}

$obj = json_decode($result);

if (isset($obj[0]->BillCode)) {
    // 将这次支付的单号存入 session，方便 return 页面查询
    $_SESSION['payment_order_number'] = $orderId;
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    echo "<div style='padding:40px; text-align:center; font-family:sans-serif;'>";
    echo "<h3 style='color:red;'>Payment Gateway Error</h3>";
    echo "<p>Message: " . htmlspecialchars($result) . "</p>";
    echo "<hr style='width:200px; margin:20px auto;'>";
    echo "<a href='../cart/unified_cart.php' style='color:#666; text-decoration:none;'>Back to Cart</a>";
    echo "</div>";
}
?>