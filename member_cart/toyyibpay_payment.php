<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['pending_order'])) {
    die("No pending order. Please go back to checkout.");
}

$pending_order = $_SESSION['pending_order'];

// 获取订单信息
$total = $pending_order['final_total'] ?? $pending_order['total'];
$name = $pending_order['name'];
$email = $pending_order['email'];
$phone = $pending_order['phone'];

$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode = 'jmf56yjy';
$baseUrl = "http://localhost:8001/";
$returnUrl = $baseUrl . "payment_return.php";

$apiUrl = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl = 'https://dev.toyyibpay.com/';

$orderId = "PXX-" . date("His") . "-" . rand(10, 99);
$billAmount = $total * 100;

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
    'billTo' => $name,
    'billEmail' => $email,
    'billPhone' => $phone
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
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    echo "<div style='padding:20px; border:2px solid red;'>";
    echo "<h3>Payment Error</h3>";
    echo "<p>" . htmlspecialchars($result) . "</p>";
    echo "</div>";
}
?>