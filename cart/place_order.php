<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: index.php');
    exit();
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Get user points
$stmt = $pdo->prepare("SELECT points FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_points = $user['points'] ?? 0;

$shipping_address = $_POST['shipping_address'] ?? '';
$contact_phone = $_POST['contact_phone'] ?? '';
$points_to_use = (int)($_POST['points_to_use'] ?? 0);
$max_points_to_use = min($user_points, $subtotal * 100);

$error = '';

if (empty($shipping_address)) {
    $error = "Shipping address is required";
} elseif (empty($contact_phone)) {
    $error = "Contact phone is required";
} elseif ($points_to_use > $max_points_to_use) {
    $error = "Invalid points amount";
}

if ($error) {
    $_SESSION['checkout_error'] = $error;
    header('Location: checkout.php');
    exit();
}

// 积分计算：10 points = RM 0.10
$points_discount = $points_to_use / 100;
$final_total = $subtotal - $points_discount;
$points_earned = floor($final_total);
$order_number = 'ORD' . date('Ymd') . rand(1000, 9999);

try {
    $pdo->beginTransaction();
    
    // 为每个购物车商品创建一个订单（适配队友的表结构）
    foreach ($cart_items as $item) {
        $subtotal_item = $item['price'] * $item['quantity'];
        $selected_size = $item['selected_size'] ?? '';
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, user_id, product_id, quantity, selected_size, total_price, shipping_address, contact_phone, points_used, points_earned, order_status, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Processing', 'Pending')
        ");
        $stmt->execute([
            $order_number, 
            $user_id, 
            $item['product_id'], 
            $item['quantity'], 
            $selected_size,
            $subtotal_item, 
            $shipping_address, 
            $contact_phone, 
            $points_to_use, 
            $points_earned
        ]);
    }
    
    // Update user points
    $new_points = $user_points - $points_to_use + $points_earned;
    $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE user_id = ?");
    $stmt->execute([$new_points, $user_id]);
    
    // Update product stock
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    $pdo->commit();
    
    // 显示成功页面
    echo "<!DOCTYPE html>";
    echo "<html><head><title>Order Confirmed</title>";
    echo "<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .success { color: #28a745; font-size: 60px; margin-bottom: 20px; }
        h1 { color: #333; margin-bottom: 10px; }
        .order-number { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .btn { display: inline-block; background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin: 10px; }
        .btn-gray { background: #666; }
        .points-info { margin: 15px 0; color: #ff9800; }
    </style>";
    echo "</head><body>";
    echo "<div class='container'>";
    echo "<div class='success'>✓</div>";
    echo "<h1>Order Confirmed!</h1>";
    echo "<p>Thank you for your purchase.</p>";
    echo "<div class='order-number'>";
    echo "<strong>Order Number:</strong> " . htmlspecialchars($order_number) . "<br>";
    echo "<strong>Total Paid:</strong> RM " . number_format($final_total, 2) . "<br>";
    echo "</div>";
    echo "<div class='points-info'>";
    echo "🎁 Points Earned: <strong>" . $points_earned . "</strong><br>";
    echo "💎 Points Used: <strong>" . $points_to_use . "</strong> (RM " . number_format($points_discount, 2) . " discount)";
    echo "</div>";
    echo "<a href='../products/listing.php' class='btn btn-gray'>Continue Shopping</a>";
    echo "<a href='../orders/history.php' class='btn'>View Orders</a>";
    echo "</div></body></html>";
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['checkout_error'] = "Order failed: " . $e->getMessage();
    header('Location: checkout.php');
    exit();
}
?>