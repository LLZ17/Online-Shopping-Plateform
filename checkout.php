<?php
session_start();
require_once 'includes/db.php';

// 检查是否有待处理的订单
if (!isset($_SESSION['pending_order'])) {
    die("No pending order. Please go back to checkout.");
}

$pending_order = $_SESSION['pending_order'];

$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode = 'jmf56yjy';
$baseUrl = "http://localhost/HTML-Online-Shopping-main/";
$returnUrl = $baseUrl . "payment_return.php";

$apiUrl = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl = 'https://dev.toyyibpay.com/';

$orderId = "PXX-" . date("His") . "-" . rand(10, 99);
$billAmount = $pending_order['total'] * 100;  // 使用实际订单金额

$data = array(
    'userSecretKey' => $userSecretKey,
    'categoryCode' => $categoryCode,
    'billName' => 'PXX Mall Fashion Order',
    'billDescription' => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => $billAmount,
    'billReturnUrl' => $returnUrl,
    'billExternalReferenceNo' => $orderId,
    'billTo' => $pending_order['name'],
    'billEmail' => $pending_order['email'],
    'billPhone' => $pending_order['phone']
);

$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, $apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$result = curl_exec($curl);
$errors = curl_error($curl);
curl_close($curl);

if ($errors) {
    die("CURL Error: " . $errors);
}

$obj = json_decode($result);

if (isset($obj[0]->BillCode)) {
    $_SESSION['payment_order_number'] = $orderId;
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    echo "<div style='padding:20px; border:2px solid red;'>";
    echo "<h3>Payment Error</h3>";
    echo "<p>" . htmlspecialchars($result) . "</p>";
    echo "</div>";
}
?>