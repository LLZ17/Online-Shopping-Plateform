<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['pending_order'])) {
    header('Location: cart.php');
    exit();
}

$order = $_SESSION['pending_order'];
$total = $order['total'];

// ToyyibPay 配置
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode = 'jmf56yjy';
$baseUrl = "http://localhost/HTML-Online-Shopping-main/";
$returnUrl = $baseUrl . "payment_return.php";

$apiUrl = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl = 'https://dev.toyyibpay.com/';

$orderId = "PXX-" . date("YmdHis") . "-" . rand(10, 99);

$data = array(
    'userSecretKey' => $userSecretKey,
    'categoryCode' => $categoryCode,
    'billName' => 'PXX Mall Order',
    'billDescription' => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => $total * 100, // 转换成 sen
    'billReturnUrl' => $returnUrl,
    'billExternalReferenceNo' => $orderId,
    'billTo' => $order['name'],
    'billEmail' => $order['email'],
    'billPhone' => $order['phone']
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
    // 保存订单信息到 session，支付成功后使用
    $_SESSION['payment_order'] = [
        'order_number' => $orderId,
        'total' => $total,
        'name' => $order['name'],
        'email' => $order['email'],
        'phone' => $order['phone'],
        'address' => $order['address'],
        'cart_items' => $order['cart_items']
    ];
    
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    echo "<div style='padding:20px; border:2px solid red;'>";
    echo "<h3 style='color:red;'>Payment Error</h3>";
    echo "<p>" . htmlspecialchars($result) . "</p>";
    echo "</div>";
}
?>