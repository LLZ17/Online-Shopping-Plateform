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

// 明确使用 FETCH_ASSOC 确保返回数组
$u_stmt = $pdo->prepare("SELECT real_name, phone_num, email, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user_info = $u_stmt->fetch(PDO::FETCH_ASSOC);

// 如果查不到用户信息，报错停止
if (!$user_info) {
    die("Error: User record not found in database.");
}

/**
 * 3. 引用 bank.json 并匹配银行代码
 */
$selectedBankCode = '';
$jsonPath = 'bank.json'; // 确认文件名没有 s

if (file_exists($jsonPath)) {
    $jsonContent = file_get_contents($jsonPath);
    $banksData = json_decode($jsonContent, true);
    
    $userSavedBankName = isset($user_info['bank_name']) ? trim($user_info['bank_name']) : '';

    // 确保解析出来的 JSON 是数组且用户有存银行名字
    if (is_array($banksData) && !empty($userSavedBankName)) {
        foreach ($banksData as $b) {
            // 只有当 $b 是数组且包含 name 键时才对比，防止 TypeError
            if (is_array($b) && isset($b['name']) && $b['name'] === $userSavedBankName) {
                $selectedBankCode = $b['bank']; 
                break;
            }
        }
    }
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
 * 5. 组装提交给 ToyyibPay 的数据
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
    'billTo'        => !empty($user_info['real_name']) ? $user_info['real_name'] : 'Customer',
    'billEmail'     => !empty($pending_data['email']) ? $pending_data['email'] : $user_info['email'],
    'billPhone'     => !empty($user_info['phone_num']) ? $user_info['phone_num'] : '0123456789',
    'billBankID'    => $selectedBankCode // 如果匹配成功，这里会是 RHB0218 之类的代码
);

/**
 * 6. 发起 CURL 请求
 */
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, $apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

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

if (isset($obj[0]->BillCode)) {
    $_SESSION['payment_order_number'] = $orderId;
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    echo "<div style='padding:40px; font-family:sans-serif; text-align:center;'>";
    echo "<h2 style='color:red;'>Payment Gateway Error</h2>";
    // 如果失败，打印出完整的返回信息用于排查
    echo "<p>Details: " . htmlspecialchars($result) . "</p>";
    echo "<a href='checkout.php'>Back to Checkout</a>";
    echo "</div>";
}
?>