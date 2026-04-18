<?php
session_start();
require_once '../includes/db.php'; // 确保你的数据库连接路径正确

// --- 第一步：基础验证 ---
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$pending_order = $_SESSION['pending_order'];

// --- 第二步：从 SQL 获取用户完整资料（包括全名和银行） ---
$query = "SELECT full_name, bank_name, email, phone FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$userData = mysqli_fetch_assoc($result);

// 检查是否设置了银行
if (empty($userData['bank_name'])) {
    $_SESSION['error_msg'] = "请先在个人资料中设置您的银行户口。";
    header('Location: ../profile.php'); // 跳回个人资料页
    exit();
}

// --- 第三步：通过 JSON 转换银行名称为 Bank ID ---
$bankId = "";
if (file_exists("banks.json")) {
    $banksJson = file_get_contents("banks.json");
    $banks = json_decode($banksJson, true);
    // 这里的 $userData['bank_name'] 必须和 JSON 里的 Key 完全一致
    $bankId = $banks[$userData['bank_name']] ?? "";
}

// --- 第四步：配置 toyyibPay 参数 ---
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode = 'jmf56yjy';
$orderId = "PXX-" . date("His") . "-" . rand(10, 99);
$billAmount = $pending_order['total'] * 100; // 转换为分 (Sen)

$data = array(
    'userSecretKey' => $userSecretKey,
    'categoryCode'  => $categoryCode,
    'billName'      => 'PXX Mall Order',
    'billDescription' => 'Payment for Order ' . $orderId,
    'billPriceSetting' => 1,
    'billPayorInfo'    => 1,
    'billAmount'       => $billAmount,
    'billReturnUrl'    => "http://localhost:8001/payment_return.php",
    'billExternalReferenceNo' => $orderId,
    'billTo'           => $userData['full_name'], // 使用 SQL 里的全名
    'billEmail'        => $userData['email'],
    'billPhone'        => $userData['phone']
);

// --- 第五步：cURL 核心代码 (把数据发给 toyyibPay) ---
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_URL, 'https://dev.toyyibpay.com/index.php/api/createBill');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 开发环境设为 false

$response = curl_exec($curl);
$errors = curl_error($curl);
curl_close($curl);

if ($errors) {
    die("网络连接错误: " . $errors);
}

// --- 第六步：解析结果并跳转 ---
$obj = json_decode($response);

if (isset($obj[0]->BillCode)) {
    $billCode = $obj[0]->BillCode;
    $_SESSION['payment_order_number'] = $orderId;

    // 拼接最终跳转链接
    $jumpUrl = "https://dev.toyyibpay.com/" . $billCode;
    
    // 如果找到了对应的 Bank ID，就让 toyyibPay 默认选中
    if (!empty($bankId)) {
        $jumpUrl .= "?bank=" . $bankId;
    }

    header("Location: " . $jumpUrl);
    exit;
} else {
    // 打印错误详情，方便调试
    echo "<h3>支付创建失败</h3>";
    echo "<p>API 返回内容: " . htmlspecialchars($response) . "</p>";
}
?>