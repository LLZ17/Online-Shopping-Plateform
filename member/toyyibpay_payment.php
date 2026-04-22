<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_order_data'])) {
    header('Location: ../cart/unified_cart.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$pending = $_SESSION['pending_order_data'];

// 获取用户信息
$u_stmt = $pdo->prepare("SELECT real_name, phone_num, email, address, postcode, city, state, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch(PDO::FETCH_ASSOC);

// 准备插入订单的数据
$cart_ids = explode(',', $pending['cart_ids']);
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));

try {
    $pdo->beginTransaction();

    // 从购物车获取详细信息
    $stmt_cart = $pdo->prepare("SELECT c.*, p.category_id FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.cart_id IN ($placeholders)");
    $stmt_cart->execute($cart_ids);
    $cart_items = $stmt_cart->fetchAll();

    // 每一项插入一条 order 记录，匹配你的 18 个字段
    foreach ($cart_items as $item) {
        $ins = $pdo->prepare("INSERT INTO orders (
            user_id, product_id, category_id, quantity, selected_size, colour, 
            total_price, bank_name, payment_status, voucher_id, voucher_discount_amount, 
            points_used, points_discount_amount, points_earned, order_status, order_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Paid', ?, ?, ?, ?, ?, 'Processing', NOW())");

        $ins->execute([
            $user_id,
            $item['product_id'],
            $item['category_id'],
            $item['quantity'],
            $item['selected_size'],
            $item['colour'],
            $pending['final_total'], // 实付总金额
            $user['bank_name'] ?? 'Online Banking',
            $pending['voucher_id'] ?? null,
            $pending['voucher_discount'] ?? 0,
            $pending['points_used'] ?? 0,
            $pending['points_discount_amount'] ?? 0,
            $pending['points_earned'] ?? 0
        ]);
    }

    // 成功插入后清空购物车
    $del = $pdo->prepare("DELETE FROM cart WHERE cart_id IN ($placeholders) AND user_id = ?");
    $del->execute(array_merge($cart_ids, [$user_id]));

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Database Error: " . $e->getMessage());
}

// ToyyibPay API 调用 (保持不变)
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3';
$categoryCode  = 'jmf56yjy';
$apiUrl        = 'https://dev.toyyibpay.com/index.php/api/createBill';

$postData = [
    'userSecretKey'    => $userSecretKey,
    'categoryCode'     => $categoryCode,
    'billName'         => 'PXX Mall Order',
    'billDescription'  => 'Payment for Order',
    'billPriceSetting' => 1,
    'billPayorInfo'    => 1,
    'billAmount'       => floatval($pending['final_total']) * 100,
    'billReturnUrl'    => "http://localhost:8001/memberHistory/history.php",
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

$obj = json_decode($result);
if (isset($obj[0]->BillCode)) {
    header("Location: https://dev.toyyibpay.com/" . $obj[0]->BillCode);
    exit;
} else {
    echo "Gateway Error: " . $result;
}