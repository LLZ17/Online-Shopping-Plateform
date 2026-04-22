<?php
session_start();
require_once '../includes/db.php';

// 1. 安全检查
if (!isset($_SESSION['user_id'])) {
    die("Session expired, please login again.");
}

// 接收来自 checkout.php POST 提交的数据
$user_id = $_SESSION['user_id'];
$cart_ids = $_POST['cart_ids'] ?? ''; // 隐藏域传过来的购物车ID
$final_amount = $_POST['final_amount'] ?? 0;
$v_id = !empty($_POST['applied_v_id']) ? $_POST['applied_v_id'] : NULL;
$v_amt = $_POST['applied_v_amt'] ?? 0;
$p_used = $_POST['points_used'] ?? 0;
$p_disc = $_POST['points_discount'] ?? 0;
$p_earned = $_POST['points_earned'] ?? 0;

if (empty($cart_ids)) {
    die("No items selected. Please go back to cart.");
}

// 2. 获取用户信息（地址等）
$u_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch(PDO::FETCH_ASSOC);
$address_full = $user['address'] . ', ' . $user['postcode'] . ' ' . $user['city'] . ', ' . $user['state'];

try {
    $pdo->beginTransaction();

    $ids_array = explode(',', $cart_ids);
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    
    // 获取购物车中商品的分类 ID
    $stmt_cart = $pdo->prepare("SELECT c.*, p.category_id FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.cart_id IN ($placeholders)");
    $stmt_cart->execute($ids_array);
    $items = $stmt_cart->fetchAll();

    // 3. 核心：将数据 update/insert 到你的 SQL 表
    foreach ($items as $item) {
        $sql = "INSERT INTO orders (
            user_id, product_id, category_id, quantity, selected_size, colour, 
            total_price, bank_name, payment_status, voucher_id, voucher_discount_amount, 
            points_used, points_discount_amount, points_earned, order_status, shipping_address, order_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Paid', ?, ?, ?, ?, ?, 'Processing', ?, NOW())";
        
        $ins = $pdo->prepare($sql);
        $ins->execute([
            $user_id, 
            $item['product_id'], 
            $item['category_id'], 
            $item['quantity'], 
            $item['selected_size'], 
            $item['colour'],
            $final_amount, 
            $user['bank_name'] ?? 'Online Banking',
            $v_id,
            $v_amt,
            $p_used,
            $p_disc,
            $p_earned,
            $address_full
        ]);
    }

    // 4. 插入成功后，立刻清空这几个购物车的商品
    $del = $pdo->prepare("DELETE FROM cart WHERE cart_id IN ($placeholders) AND user_id = ?");
    $del->execute(array_merge($ids_array, [$user_id]));

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Database Error: " . $e->getMessage());
}

// 5. ToyyibPay API (保持不变)
$userSecretKey = '2pl08i3j-hekc-jxfo-218a-02h0ummfdwm3'; 
$categoryCode  = 'jmf56yjy';

$postData = [
    'userSecretKey'    => $userSecretKey,
    'categoryCode'     => $categoryCode,
    'billName'         => 'PXX Mall Order',
    'billDescription'  => 'Order Payment',
    'billPriceSetting' => 1,
    'billPayorInfo'    => 1,
    'billAmount'       => floatval($final_amount) * 100,
    'billReturnUrl'    => "http://localhost:8001/memberHistory/history.php", // 支付完跳回历史页
    'billTo'           => $user['real_name'],
    'billEmail'        => $user['email'],
    'billPhone'        => $user['phone_num']
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://dev.toyyibpay.com/index.php/api/createBill',
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
    echo "Payment Error: " . $result;
}