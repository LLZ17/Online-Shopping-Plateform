<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$status = $_GET['status_id'] ?? '';

// 检查是否有待处理的订单
if (!isset($_SESSION['pending_order'])) {
    // 没有待处理订单，直接显示成功页面
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Payment Status</title>";
    echo "<style>
        body { font-family: Arial; text-align: center; padding: 50px; background: #f5f5f5; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .success { color: #28a745; font-size: 60px; }
        .btn { display: inline-block; background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 10px; font-weight: bold; }
        .btn-gray { background: #666; }
    </style>";
    echo "</head><body>";
    echo "<div class='container'>";
    echo "<div class='success'>✓</div>";
    echo "<h1>Payment Successful!</h1>";
    echo "<p>Your order has been placed successfully.</p>";
    echo "<div style='margin-top: 30px;'>";
    echo "<a href='memberOrders/history.php' class='btn'>📦 View My Orders</a>";
    echo "<a href='memberProducts/listing.php' class='btn btn-gray'>🛍️ Continue Shopping</a>";
    echo "<a href='index.php' class='btn btn-gray'>🏠 Back to Home</a>";
    echo "</div>";
    echo "</div></body></html>";
    exit();
}

$order = $_SESSION['pending_order'];
$user_id = $_SESSION['user_id'];

if ($status == '1') {
    // 支付成功
    try {
        $pdo->beginTransaction();
        
        $order_number = 'ORD' . date('Ymd') . rand(1000, 9999);
        $points_earned = floor($order['total']);
        $points_used = $order['points_to_use'] ?? 0;
        
        foreach ($order['cart_items'] as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO orders (order_number, user_id, product_id, quantity, selected_size, total_price, shipping_address, contact_phone, points_used, points_earned, order_status, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Processing', 'Paid')
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
                $points_used,
                $points_earned
            ]);
            
            // 更新库存
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // 更新用户积分
        $new_points = ($order['user_points'] ?? 0) - $points_used + $points_earned;
        $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE user_id = ?");
        $stmt->execute([$new_points, $user_id]);
        
        // 清空购物车
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        unset($_SESSION['pending_order']);
        
        // 显示成功页面
        echo "<!DOCTYPE html>";
        echo "<html><head><title>Payment Successful</title>";
        echo "<style>
            body { font-family: Arial; text-align: center; padding: 50px; background: #f5f5f5; }
            .container { max-width: 550px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .success { color: #28a745; font-size: 60px; }
            h1 { color: #333; margin-bottom: 10px; }
            .order-number { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: left; }
            .order-number p { margin: 8px 0; }
            .btn { display: inline-block; background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 10px; font-weight: bold; }
            .btn-gray { background: #666; }
            .btn-home { background: #333; }
        </style>";
        echo "</head><body>";
        echo "<div class='container'>";
        echo "<div class='success'>✓</div>";
        echo "<h1>Payment Successful!</h1>";
        echo "<p>Thank you for your purchase.</p>";
        echo "<div class='order-number'>";
        echo "<p><strong>📋 Order Number:</strong> " . htmlspecialchars($order_number) . "</p>";
        echo "<p><strong>💰 Total Paid:</strong> RM " . number_format($order['total'], 2) . "</p>";
        echo "<p><strong>⭐ Points Earned:</strong> " . $points_earned . "</p>";
        echo "<p><strong>💎 Points Used:</strong> " . $points_used . "</p>";
        echo "</div>";
        echo "<div style='margin-top: 20px;'>";
        echo "<a href='memberOrders/history.php' class='btn'>📦 View My Orders</a>";
        echo "<a href='memberProducts/listing.php' class='btn btn-gray'>🛍️ Continue Shopping</a>";
        echo "<a href='index.php' class='btn btn-gray btn-home'>🏠 Back to Home</a>";
        echo "</div>";
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
        body { font-family: Arial; text-align: center; padding: 50px; background: #f5f5f5; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; }
        .failed { color: red; font-size: 60px; }
        h1 { color: #333; }
        .btn { display: inline-block; background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 10px; }
        .btn-gray { background: #666; }
    </style>";
    echo "</head><body>";
    echo "<div class='container'>";
    echo "<div class='failed'>✗</div>";
    echo "<h1>Payment Failed</h1>";
    echo "<p>Please try again.</p>";
    echo "<div style='margin-top: 30px;'>";
    echo "<a href='member_cart/unified_cart.php' class='btn'>🛒 Back to Cart</a>";
    echo "<a href='index.php' class='btn btn-gray'>🏠 Back to Home</a>";
    echo "</div>";
    echo "</div></body></html>";
    exit();
}
?>