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

$subtotal = 0;
foreach ($order['cart_items'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$points_to_use = $order['points_to_use'] ?? 0;
$points_discount = $points_to_use / 100;
$voucher_amount = $order['voucher_amount'] ?? 0;
$after_discount = $subtotal - $points_discount - $voucher_amount;
$shipping_fee = ($after_discount >= 100 || $after_discount <= 0) ? 0 : 8.00;
$final_total = max(0, $after_discount + $shipping_fee);
$points_earned = floor($final_total);

$order_group_id = 'ORD' . date('Ymd') . rand(1000, 9999);

try {
    $pdo->beginTransaction();
    
    foreach ($order['cart_items'] as $item) {
        $cat_stmt = $pdo->prepare("SELECT category_id FROM products WHERE product_id = ?");
        $cat_stmt->execute([$item['product_id']]);
        $category_id = $cat_stmt->fetchColumn();
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                user_id, product_id, category_id, quantity, selected_size, colour,
                total_price, bank_name, payment_status, voucher_id, voucher_discount_amount,
                points_used, points_discount_amount, points_earned, order_status, shipping_address, payment_time
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Paid', ?, ?, ?, ?, ?, 'Processing', ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $item['product_id'],
            $category_id,
            $item['quantity'],
            $item['selected_size'] ?: null,
            $item['colour'] ?? null,
            $item['price'] * $item['quantity'],
            'Direct Payment',
            $order['voucher_id'] ?? null,
            $voucher_amount,
            $points_to_use,
            $points_discount,
            $points_earned,
            $order['address']
        ]);
        
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    $new_points = ($order['user_points'] ?? 0) - $points_to_use + $points_earned;
    $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE user_id = ?");
    $stmt->execute([$new_points, $user_id]);
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    $pdo->commit();
    
    unset($_SESSION['pending_order']);
    
    header("Location: order_confirmation.php?order_group=" . $order_group_id);
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Order failed: " . $e->getMessage());
    $_SESSION['checkout_error'] = "Order failed. Please try again.";
    header('Location: checkout.php');
    exit();
}
?>