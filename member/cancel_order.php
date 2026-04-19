<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 修复：使用 order_id 而不是 order_number
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

if (empty($order_id)) {
    header('Location: orders_history.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 检查订单是否存在且状态为 Processing
    $stmt = $pdo->prepare("
        SELECT * FROM orders 
        WHERE order_id = ? AND user_id = ? AND order_status = 'Processing'
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['cancel_error'] = 'Order cannot be cancelled. Only processing orders can be cancelled.';
        header('Location: orders_history.php');
        exit();
    }

    // 更新订单状态为 Cancelled
    $stmt = $pdo->prepare("
        UPDATE orders SET order_status = 'Cancelled', payment_status = 'Canceled' 
        WHERE order_id = ? AND user_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);

    // 恢复库存
    $stmt = $pdo->prepare("
        UPDATE products p 
        JOIN orders o ON p.product_id = o.product_id 
        SET p.stock = p.stock + o.quantity 
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);

    $_SESSION['cancel_success'] = 'Order has been cancelled successfully.';
    
} catch (PDOException $e) {
    $_SESSION['cancel_error'] = 'Database error: ' . $e->getMessage();
}

header('Location: orders_history.php');
exit();
?>
