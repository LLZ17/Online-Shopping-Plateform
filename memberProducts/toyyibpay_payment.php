<?php
session_start();
// 确保使用你的 $pdo 变量
require_once '../includes/db.php';

// 1. 基础检查
if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_order_data'])) {
    header('Location: ../cart/unified_cart.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$pending = $_SESSION['pending_order_data'];

// 2. 获取用户信息
$u_stmt = $pdo->prepare("SELECT real_name, phone_num, email, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch(PDO::FETCH_ASSOC);

// 3. 准备购物车数据
$cart_ids = explode(',', $pending['cart_ids']);
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));

try {
    $pdo->beginTransaction();

    // 从购物车获取商品和分类 ID
    $stmt_cart = $pdo->prepare("SELECT c.*, p.category_id FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.cart_id IN ($placeholders)");
    $stmt_cart->execute($cart_ids);
    $cart_items = $stmt_cart->fetchAll();

    if (!$cart_items) {
        throw new Exception("No items found in cart.");
    }

    // 4. 插入订单记录，初始状态为 'Pending'
    foreach ($cart_items as $item) {
        $ins = $pdo->prepare("INSERT INTO orders (
            user_id, product_id, category_id, quantity, selected_size, colour, 
            total_price, bank_name, payment_status, voucher_id, voucher_discount_amount, 
            points_used, points_discount_amount, points_earned, order_status, order_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?, 'Processing', NOW())");

        $ins->execute([
            $user_id,
            $item['product_id'],
            $item['category_id'],
            $item['quantity'],
            $item['selected_size'],
            $item['colour'],
            $pending['final_total'], 
            $user['bank_name'] ?? 'Online Banking',
            $pending['voucher_id'] ?? null,
            $pending['voucher_discount'] ?? 0,
            $pending['points_used'] ?? 0,
            $pending['points_discount_amount'] ?? 0,
            $pending['points_earned'] ?? 0
        ]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Database Error: " . $e->getMessage());
}

// 5. ToyyibPay API 配置
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode  = 'jmf56yjy';
$apiUrl        = 'https://dev.toyyibpay.com/index.php/api/createBill';

$postData = [
    'userSecretKey'    => $userSecretKey,
    'categoryCode'     => $categoryCode,
    'billName'         => 'PXX Mall Order',
    'billDescription'  => 'Payment for User ID: ' . $user_id,
    'billPriceSetting' => 1,
    'billPayorInfo'    => 1,
    'billAmount'       => floatval($pending['final_total']) * 100, // 转换为分
    'billReturnUrl'    => "http://localhost:8001/memberOrders/history.php",
    'billTo'           => $user['real_name'],
    'billEmail'        => $user['email'],
    'billPhone'        => $user['phone_num']
];

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

// 6. 处理返回结果 (核心修复：解决 stdClass 报错)
$obj = json_decode($result);

// 检查是否为数组且第一项存在
if (is_array($obj) && isset($obj[0]->BillCode)) {
    // 成功获取后跳转，必须使用 -> 访问对象属性
    header("Location: https://dev.toyyibpay.com/" . $obj[0]->BillCode);
    exit;
} else {
    // 错误处理：显示 API 返回的具体错误信息
    echo "<h1>Payment Gateway Error</h1>";
    if (is_array($obj) && isset($obj[0]->msg)) {
        echo "Message: " . htmlspecialchars($obj[0]->msg);
    } else {
        echo "Raw Response: " . htmlspecialchars($result);
    }
}
?>