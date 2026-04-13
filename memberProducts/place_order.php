<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_SESSION['pending_order'])) {
    header('Location: unified_cart.php');
    exit();
}

$order = $_SESSION['pending_order'];
$user_id = $_SESSION['user_id'];

$points_to_use = $order['points_to_use'];
$user_points = $order['user_points'];
$subtotal = $order['total'];

// 积分计算：10 points = RM 0.10
$points_discount = $points_to_use / 100;
$final_total = $subtotal - $points_discount;
$points_earned = floor($final_total);

$order_number = 'ORD' . date('Ymd') . rand(1000, 9999);

try {
    $pdo->beginTransaction();
    
    foreach ($order['cart_items'] as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, user_id, product_id, quantity, selected_size, total_price, shipping_address, contact_phone, points_used, points_earned, order_status, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Processing', 'Pending')
        ");
        $stmt->execute([
            $order_number,
            $user_id,
            $item['product_id'],
            $item['quantity'],
            $item['selected_size'] ?? '',
            $item['price'] * $item['quantity'],
            $order['address'],
            $order['phone'],
            $points_to_use,
            $points_earned
        ]);
        
        // 更新库存
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // 更新用户积分
    $new_points = $user_points - $points_to_use + $points_earned;
    $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE user_id = ?");
    $stmt->execute([$new_points, $user_id]);
    
    // 清空购物车
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    $pdo->commit();
    
    unset($_SESSION['pending_order']);
    
    header("Location: order_confirmation.php?order_number=" . $order_number);
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['checkout_error'] = "Order failed: " . $e->getMessage();
    header('Location: checkout.php');
    exit();
}
?>