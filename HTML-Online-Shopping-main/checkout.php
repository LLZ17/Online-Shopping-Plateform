<?php
/**
 * PXX MALL - 支付跳转整合脚本
 * 开发者：4人项目组
 */

// --- 1. 配置部分 (原本在 config.php) ---
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3'; // 从 ToyyibPay Settings -> Personal 获取
$categoryCode  = 'jmf56yjy'; // 从 ToyyibPay Category -> List 获取

// 这里的 URL 必须指向你的 return.php 存放位置
$baseUrl = "http://localhost/pxxmall/"; 
$returnUrl = $baseUrl . "return.php";

// ToyyibPay Sandbox API 地址
$apiUrl = 'https://dev.toyyibpay.com/index.php/api/createBill';
$gatewayUrl = 'https://dev.toyyibpay.com/';


// --- 2. 构造订单数据 ---
$orderId = "PXX-" . date("His") . "-" . rand(10, 99);

$data = array(
    'userSecretKey' => $userSecretKey,
    'categoryCode' => $categoryCode,
    'billName' => 'PXX Mall Fashion Order',
    'billDescription' => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount' => 10100, // 金额：RM 101.00 (10100 sen)
    'billReturnUrl' => $returnUrl,
    'billExternalReferenceNo' => $orderId,
    'billTo' => 'Test Customer',
    'billEmail' => 'customer@pxxmall.com',
    'billPhone' => '0139412222'
);


// --- 3. 发送 CURL 请求 ---
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, $apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

$result = curl_exec($curl);
$errors = curl_error($curl);
curl_close($curl);


// --- 4. 解析结果并跳转 ---
if ($errors) {
    die("CURL Error: " . $errors);
}

$obj = json_decode($result);

if (isset($obj[0]->BillCode)) {
    // 成功获取 BillCode，直接重定向到支付页面
    header("Location: " . $gatewayUrl . $obj[0]->BillCode);
    exit;
} else {
    // 如果报错，打印出错误原因
    echo "<div style='padding:20px; border:2px solid red; font-family:sans-serif;'>";
    echo "<h3 style='color:red;'>API Error (无法创建账单)</h3>";
    echo "<p>返回结果: " . htmlspecialchars($result) . "</p>";
    echo "<p>请检查你的 <b>Secret Key</b> 和 <b>Category Code</b> 是否填对。</p>";
    echo "</div>";
}
?>