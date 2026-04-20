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
 * 2. 获取用户和订单数据
 */
$user_id = $_SESSION['user_id'];
$pending_data = $_SESSION['pending_order_data'];

$u_stmt = $pdo->prepare("SELECT real_name, phone_num, email, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user_info = $u_stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_info) {
    die("Error: User record not found.");
}

/**
 * 3. 引用并解析 bank.json (增强防御逻辑)
 */
$selectedBankCode = '';
$jsonPath = '../includes/bank.json'; 

if (file_exists($jsonPath)) {
    $jsonRaw = file_get_contents($jsonPath);
    
    // 移除 UTF-8 的 BOM 头以及不可见字符，防止 json_decode 失败返回 null
    $jsonClean = preg_replace('/^[\x00-\x1F\x80-\xFF]+/', '', trim($jsonRaw));
    
    // 解析 JSON
    $banksData = json_decode($jsonClean, true);
    
    // 检查：如果解析失败或结果不是数组，进行二次尝试（处理双重转义情况）
    if (!is_array($banksData) && is_string($banksData)) {
        $banksData = json_decode($banksData, true);
    }

    $userSavedBankName = isset($user_info['bank_name']) ? trim($user_info['bank_name']) : '';

    // 核心修复：只有确认 $banksData 是数组时才进行 key 访问
    if (is_array($banksData) && !empty($userSavedBankName)) {
        // 使用 isset 检查键名是否存在，避免 offset 报错
        if (isset($banksData[$userSavedBankName])) {
            $selectedBankCode = $banksData[$userSavedBankName];
        }
    }
}

/**
 * 4. ToyyibPay API 配置
 */
// 注意：建议将这些 Key 放入配置文件或 .env
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3'; 
$categoryCode = 'jmf56yjy';
$apiUrl = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl = 'https://dev.toyyibpay.com/';

$orderId = "PXX-" . date("His") . "-" . rand(100, 999);
// ToyyibPay 金额以分(Cent)为单位，需要乘以 100
$billAmount = round(floatval($pending_data['total_price']), 2) * 100;

/**
 * 5. 组装提交给 ToyyibPay 的数据
 */
$postData = array(
    'userSecretKey' => $userSecretKey,
    'categoryCode'  => $categoryCode,
    'billName'      => 'PXX Mall Order',
    'billDescription' => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount'    => $billAmount,
    'billReturnUrl' => "http://localhost:8001/payment_return.php",
    'billExternalReferenceNo' => $orderId,
    'billTo'        => !empty($user_info['real_name']) ? $user_info['real_name'] : 'Customer',
    'billEmail'     => !empty($pending_data['email']) ? $pending_data['email'] : $user_info['email'],
    'billPhone'     => !empty($user_info['phone_num']) ? $user_info['phone_num'] : '0123456789',
    
    // 如果匹配不到银行代码，这里传空字符串，ToyyibPay 会让用户在页面上手选
    'billBankID'    => (string)$selectedBankCode 
);

/**
 * 6. 发起 CURL 请求
 */
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, $apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 仅限开发环境使用

$result = curl_exec($curl);
$curl_err = curl_error($curl);
curl_close($curl);

if ($curl_err) {
    die("CURL Error: " . $curl_err);
}

/**
 * 7. 处理响应
 */
$obj = json_decode($result);

// ToyyibPay 成功通常返回一个包含对象的数组，检查第一个元素的 BillCode
if (is_array($obj) && isset($obj[0]->BillCode)) {
    $_SESSION['payment_order_number'] = $orderId;
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    // 错误处理页面
    echo "<div style='padding:40px; text-align:center; font-family:sans-serif;'>";
    echo "<h2 style='color:#e60012;'>Payment Gateway Error</h2>";
    echo "<p style='color:#666;'>API Response: " . htmlspecialchars($result) . "</p>";
    echo "<p>Debug Info: Requested Bank [" . htmlspecialchars($user_info['bank_name'] ?? 'None') . "]</p>";
    echo "<a href='checkout.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#333; color:#fff; text-decoration:none; border-radius:4px;'>← Back to Checkout</a>";
    echo "</div>";
}
?>