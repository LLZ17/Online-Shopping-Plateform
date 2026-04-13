<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_number = isset($_GET['order_number']) ? $_GET['order_number'] : '';

if (empty($order_number)) {
    header('Location: orders_history.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 检查订单是否存在且状态为 Processing
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE order_number = ? AND user_id = ? AND order_status = 'Processing'
    ");
    $stmt->execute([$order_number, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['cancel_error'] = 'Order cannot be cancelled. Only processing orders can be cancelled.';
        header('Location: orders_history.php');
        exit();
    }

    // 更新订单状态为 Cancelled
    $stmt = $pdo->prepare("
        UPDATE orders SET order_status = 'Cancelled', payment_status = 'Canceled' 
        WHERE order_number = ? AND user_id = ?
    ");
    $stmt->execute([$order_number, $user_id]);

    // 恢复库存
    $stmt = $pdo->prepare("
        UPDATE products p 
        JOIN orders o ON p.product_id = o.product_id 
        SET p.stock = p.stock + o.quantity 
        WHERE o.order_number = ?
    ");
    $stmt->execute([$order_number]);

    $_SESSION['cancel_success'] = 'Order has been cancelled successfully.';
    
} catch (PDOException $e) {
    $_SESSION['cancel_error'] = 'Database error: ' . $e->getMessage();
}

header('Location: orders_history.php');
exit();
?>