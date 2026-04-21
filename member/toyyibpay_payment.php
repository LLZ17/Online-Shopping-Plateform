<?php
session_start();
require_once '../includes/db.php';

/**
 * 1. 基础安全检查
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_order_data'])) {
    header('Location: ../login.php');
    exit();
}

/**
 * 2. 获取用户数据
 */
$user_id = $_SESSION['user_id'];
$pending_data = $_SESSION['pending_order_data'];

// 确保数据库中有 bank_name 和 address
$u_stmt = $pdo->prepare("SELECT real_name, phone_num, email, address, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user_info = $u_stmt->fetch(PDO::FETCH_ASSOC);

/**
 * 3. 银行映射表 (完全匹配你提供的图片)
 */
$bankMap = [
    "Maybank2U"          => "MBB0227",
    "CIMB Clicks"        => "BCB0235",
    "Bank Islam"         => "BIMB0340",
    "Bank Rakyat"        => "BKRM0602",
    "AmBank"             => "AMB0209",
    "RHB Bank"           => "RHB0218",
    "Affin Bank"         => "ABB0233",
    "Alliance Bank"      => "ABMB0212",
    "Bank Muamalat"      => "BMMB0341",
    "BSN"                => "BSN0601",
    "Hong Leong Bank"    => "HLB0224",
    "HSBC"               => "HSBC0223",
    "KFH"                => "KFH0346",
    "OCBC Bank"          => "OCBC0229",
    "Public Bank"        => "PBB0233",
    "Standard Chartered" => "SCB0216",
    "UOB Bank"           => "UOB0226",
    "Agro Bank"          => "AGRO01"
];

$selectedBankCode = '';
$rawDbBankName = isset($user_info['bank_name']) ? trim($user_info['bank_name']) : '';

if (!empty($rawDbBankName)) {
    foreach ($bankMap as $name => $code) {
        if (strcasecmp($name, $rawDbBankName) === 0) {
            $selectedBankCode = $code;
            break;
        }
    }
}

/**
 * 4. API 配置
 */
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3'; 
$categoryCode  = 'jmf56yjy';
$apiUrl        = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl    = 'https://dev.toyyibpay.com/';

$orderId = "PXX-" . date("His") . "-" . rand(100, 999);
$billAmount = round(floatval($pending_data['total_price']), 2) * 100;

/**
 * 5. 提交数据
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
    'billPhone'        => !empty($user_info['phone_num']) ? $user_info['phone_num'] : '0123456789',
    'billAddress'      => !empty($user_info['address']) ? $user_info['address'] : 'No Address Provided',
    'billBankID'       => (string)$selectedBankCode 
];

/**
 * 6. 发起请求
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
 * 7. 智能跳转处理
 */
$obj = json_decode($result);

if (is_array($obj) && isset($obj[0]->BillCode)) {
    $_SESSION['payment_order_number'] = $orderId;
    $billCode = $obj[0]->BillCode;

    // --- 核心优化：强制拼接银行代码到跳转链接中 ---
    // 如果匹配到了银行代码，我们尝试直接在 URL 后面带上银行参数
    if (!empty($selectedBankCode)) {
        // 某些版本的 ToyyibPay 支持这种格式直接跳过选择页
        header("Location: " . $gatewayUrl . $billCode . "?bank=" . $selectedBankCode);
    } else {
        header("Location: " . $gatewayUrl . $billCode);
    }
    exit;
} else {
    echo "<h3>Payment Gateway Error</h3>";
    echo "Response: " . htmlspecialchars($result);
    exit;
}
?>