<?php
session_start();
require_once '../includes/db.php';

/**
 * 1. 基础安全检查
 */
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_SESSION['pending_order_data'])) {
    echo "<script>alert('No pending order data found.'); window.location.href='checkout.php';</script>";
    exit();
}

/**
 * 2. 获取数据
 */
$user_id = $_SESSION['user_id'];
$pending_data = $_SESSION['pending_order_data'];

$u_stmt = $pdo->prepare("SELECT real_name, phone_num, email, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user_info = $u_stmt->fetch();

/**
 * 3. 【核心修改】引用并解析 banks.json
 */
$selectedBankCode = '';
$jsonPath = '../includes/bank.json'; // 请确保路径指向你存放 banks.json 的正确位置

if (file_exists($jsonPath)) {
    $jsonContent = file_get_contents($jsonPath);
    $banksData = json_decode($jsonContent, true);
    
    // 假设你数据库存的是银行的 ID（比如 "1"），而 JSON 里对应的键是 "bank"
    // 我们遍历 JSON 找到匹配项
    foreach ($banksData as $b) {
        if ($b['id'] == $user_info['bank_name']) {
            $selectedBankCode = $b['bank']; // 获取 JSON 里的银行代码，如 RHB0218
            break;
        }
    }
} else {
    // 如果没找到 JSON，就直接用数据库里的原始值
    $selectedBankCode = $user_info['bank_name'];
}

/**
 * 4. ToyyibPay API 配置
 */
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode = 'jmf56yjy';
$apiUrl = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl = 'https://dev.toyyibpay.com/';

$orderId = "PXX-" . date("His") . "-" . rand(100, 999);
$billAmount = round(floatval($pending_data['total_price']), 2) * 100;

/**
 * 5. 组装数据
 */
$data = array(
    'userSecretKey' => $userSecretKey,
    'categoryCode'  => $categoryCode,
    'billName'      => 'PXX Mall Order',
    'billDescription' => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount'    => $billAmount,
    'billReturnUrl' => "http://localhost:8001/payment_return.php",
    'billExternalReferenceNo' => $orderId,
    'billTo'        => $user_info['real_name'] ?: 'Customer',
    'billEmail'     => $pending_data['email'] ?: $user_info['email'],
    'billPhone'     => $user_info['phone_num'] ?: '0123456789',
    
    // 引用从 JSON 匹配出来的银行代码
    'billBankID'    => $selectedBankCode 
);

/**
 * 6. 发起 CURL
 */
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
    echo "Payment Error: " . htmlspecialchars($result);
}
?>