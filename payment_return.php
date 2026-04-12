<?php
session_start();
require_once 'includes/db.php';

// 检查是否有待处理的订单
if (!isset($_SESSION['pending_order'])) {
    header('Location: index.php');
    exit();
}

$order = $_SESSION['pending_order'];
$status = $_GET['status_id'] ?? '';

// 支付成功
if ($status == '1') {
    try {
        $pdo->beginTransaction();
        
        $order_number = 'ORD' . date('Ymd') . rand(1000, 9999);
        $user_id = $_SESSION['user_id'];
        $points_earned = floor($order['total']);
        
        foreach ($order['cart_items'] as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO orders (order_number, user_id, product_id, quantity, selected_size, total_price, shipping_address, contact_phone, points_earned, order_status, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Processing', 'Paid')
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
                $points_earned
            ]);
            
            // 更新库存
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // 更新用户积分
        $stmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE user_id = ?");
        $stmt->execute([$points_earned, $user_id]);
        
        // 清空购物车
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        // 清除 session
        unset($_SESSION['pending_order']);
        
        // 显示成功页面
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Payment Successful</title>";
        echo "<style>
            body { font-family: Arial; text-align: center; padding: 50px; background: #f5f5f5; }
            .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; }
            .success { color: #28a745; font-size: 60px; }
            h1 { color: #333; }
            .btn { display: inline-block; background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 10px; }
            .btn-gray { background: #666; }
        </style>";
        echo "</head><body>";
        echo "<div class='container'>";
        echo "<div class='success'>✓</div>";
        echo "<h1>Payment Successful!</h1>";
        echo "<p>Order Number: <strong>" . $order_number . "</strong></p>";
        echo "<p>Total Paid: <strong>RM " . number_format($order['total'], 2) . "</strong></p>";
        echo "<p>Points Earned: <strong>" . $points_earned . "</strong></p>";
        echo "<a href='orders/history.php' class='btn'>View Orders</a>";
        echo "<a href='products/listing.php' class='btn btn-gray'>Continue Shopping</a>";
        echo "</div></body></html>";
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Order failed: " . $e->getMessage();
    }
} else {
    // 支付失败
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Payment Failed</title>";
    echo "<style>
        body { font-family: Arial; text-align: center; padding: 50px; }
        .failed { color: red; font-size: 60px; }
        .btn { background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; }
    </style>";
    echo "</head><body>";
    echo "<div class='failed'>✗</div>";
    echo "<h1>Payment Failed</h1>";
    echo "<a href='cart/unified_cart.php' class='btn'>Back to Cart</a>";
    echo "</body></html>";
    exit();
}
?>