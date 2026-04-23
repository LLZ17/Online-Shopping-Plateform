<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$status_id = $_GET['status_id'] ?? '';
$billcode = $_GET['billcode'] ?? '';
$order_no = $_GET['order_id'] ?? '';

if (!isset($_SESSION['pending_order'])) {
    if (!empty($order_no)) {
        header("Location: member/orders_history.php");
        exit();
    }
    displayNoOrderPage();
    exit();
}

$order = $_SESSION['pending_order'];
$user_id = $_SESSION['user_id'];

if ($status_id == '1') {
    try {
        $pdo->beginTransaction();
        
        $items_subtotal = $order['subtotal'] ?? 0;
        $points_used = $order['points_to_use'] ?? 0;
        $points_discount = $order['points_discount'] ?? 0;
        $shipping_fee = $order['shipping_fee'] ?? 0;
        $final_total = $order['final_amount'] ?? 0;
        $points_earned = $order['points_earned'] ?? 0;
        $order_group_id = $order['order_group_id'] ?? ('ORD' . date('Ymd') . rand(1000, 9999));
        
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
                'ToyyibPay',
                $order['voucher_id'] ?? null,
                $order['voucher_amount'] ?? 0,
                $points_used,
                $points_discount,
                $points_earned,
                $order['address']
            ]);
            
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        $new_points = ($order['user_points'] ?? 0) - $points_used + $points_earned;
        $stmt = $pdo->prepare("UPDATE users SET points = ? WHERE user_id = ?");
        $stmt->execute([$new_points, $user_id]);
        
        // 只删除本次购买的商品
        $purchased_cart_ids = array_column($order['cart_items'], 'cart_id');
        if (!empty($purchased_cart_ids)) {
            $placeholders = implode(',', array_fill(0, count($purchased_cart_ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id IN ($placeholders) AND user_id = ?");
            $params = array_merge($purchased_cart_ids, [$user_id]);
            $stmt->execute($params);
        }
        
        $pdo->commit();
        
        unset($_SESSION['pending_order']);
        
        displaySuccessPage($order_group_id, $items_subtotal, $points_discount, $shipping_fee, $final_total, $points_earned, $points_used, $order['cart_items']);
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Order processing failed: " . $e->getMessage());
        displayFailPage("Database error occurred. Please contact support.");
        exit();
    }
} elseif ($status_id == '2') {
    displayPendingPage();
    exit();
} else {
    displayFailPage("Payment was not successful. Please try again.");
    exit();
}

function displaySuccessPage($order_id, $subtotal, $points_discount, $shipping_fee, $total, $points_earned, $points_used, $cart_items) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Successful - PXX Mall</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 40px 20px;
            }
            .success-container {
                max-width: 900px;
                margin: 0 auto;
                background: white;
                border-radius: 32px;
                overflow: hidden;
                box-shadow: 0 25px 50px rgba(0,0,0,0.2);
                animation: slideUp 0.5s ease;
            }
            @keyframes slideUp {
                from { transform: translateY(30px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            .success-header {
                background: linear-gradient(135deg, #4caf50, #45a049);
                padding: 50px;
                text-align: center;
                color: white;
            }
            .success-icon {
                width: 100px;
                height: 100px;
                background: rgba(255,255,255,0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                animation: pulse 0.5s ease;
            }
            .success-icon i { font-size: 50px; }
            @keyframes pulse {
                0% { transform: scale(0.8); opacity: 0; }
                100% { transform: scale(1); opacity: 1; }
            }
            .success-header h1 { font-size: 32px; margin-bottom: 10px; }
            .success-header p { opacity: 0.9; }
            .order-number {
                background: rgba(255,255,255,0.2);
                display: inline-block;
                padding: 8px 20px;
                border-radius: 40px;
                margin-top: 15px;
                font-size: 14px;
            }
            .success-body { padding: 40px; }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            .info-card {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 16px;
                text-align: center;
            }
            .info-card i { font-size: 28px; color: #4caf50; margin-bottom: 10px; display: block; }
            .info-card .label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
            .info-card .value { font-size: 20px; font-weight: 700; color: #333; margin-top: 8px; }
            .products-section { margin-top: 30px; }
            .products-title {
                font-size: 18px;
                font-weight: 700;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #e60012;
                display: inline-block;
            }
            .product-item {
                display: flex;
                gap: 15px;
                padding: 15px 0;
                border-bottom: 1px solid #eee;
                align-items: center;
                flex-wrap: wrap;
            }
            .product-img {
                width: 60px;
                height: 80px;
                object-fit: cover;
                border-radius: 10px;
                background: #f5f5f5;
            }
            .product-details { flex: 2; min-width: 150px; }
            .product-name { font-weight: 600; margin-bottom: 5px; }
            .product-meta { font-size: 12px; color: #888; }
            .product-qty { min-width: 60px; text-align: center; font-weight: 600; }
            .product-price { min-width: 100px; text-align: right; font-weight: 700; color: #e60012; }
            .summary-box {
                background: #f8f9fa;
                border-radius: 16px;
                padding: 25px;
                margin-top: 25px;
            }
            .summary-row {
                display: flex;
                justify-content: space-between;
                padding: 12px 0;
                font-size: 16px;
            }
            .summary-row.total {
                border-top: 2px solid #e0e0e0;
                margin-top: 10px;
                padding-top: 15px;
                font-size: 22px;
                font-weight: 800;
                color: #e60012;
            }
            .points-row {
                background: #fff8e1;
                padding: 15px;
                border-radius: 12px;
                margin-top: 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
            }
            .action-buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
                margin-top: 30px;
            }
            .btn {
                padding: 12px 28px;
                border-radius: 40px;
                text-decoration: none;
                font-weight: 600;
                transition: 0.3s;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .btn-primary { background: #e60012; color: white; }
            .btn-primary:hover { background: #cc0010; transform: translateY(-2px); }
            .btn-secondary { background: #f0f0f0; color: #333; }
            .btn-secondary:hover { background: #e0e0e0; transform: translateY(-2px); }
            .btn-outline { border: 2px solid #e60012; color: #e60012; background: transparent; }
            .btn-outline:hover { background: #e60012; color: white; }
            .free-shipping-badge {
                display: inline-block;
                background: #e8f5e9;
                color: #4caf50;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
            }
            .shipping-fee-amount {
                font-weight: 600;
                color: #e60012;
            }
            @media (max-width: 768px) {
                .success-header { padding: 30px; }
                .success-body { padding: 20px; }
                .product-item { flex-direction: column; text-align: center; }
                .product-price { text-align: center; }
                .action-buttons { flex-direction: column; }
                .btn { justify-content: center; }
            }
        </style>
    </head>
    <body>
        <script>
            window.onload = function() {
                Swal.fire({
                    title: 'Payment Successful!',
                    text: 'Order #<?php echo htmlspecialchars($order_id); ?> has been confirmed.',
                    icon: 'success',
                    confirmButtonText: 'Great!',
                    confirmButtonColor: '#4caf50',
                    timer: 5000,
                    timerProgressBar: true
                });
            };
        </script>

        <div class="success-container">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>🎉 Payment Successful!</h1>
                <p>Thank you for your purchase. Your order has been confirmed.</p>
                <div class="order-number">
                    <i class="fas fa-receipt"></i> Order #: <?php echo htmlspecialchars($order_id); ?>
                </div>
            </div>
            
            <div class="success-body">
                <div class="info-grid">
                    <div class="info-card">
                        <i class="fas fa-clock"></i>
                        <div class="label">Order Date</div>
                        <div class="value"><?php echo date('d M Y'); ?></div>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-credit-card"></i>
                        <div class="label">Payment Method</div>
                        <div class="value">ToyyibPay</div>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-truck"></i>
                        <div class="label">Delivery Status</div>
                        <div class="value">Processing</div>
                    </div>
                </div>
                
                <div class="products-section">
                    <div class="products-title">
                        <i class="fas fa-box"></i> Items Ordered
                    </div>
                    <?php foreach ($cart_items as $item): ?>
                    <div class="product-item">
                        <img src="images/<?php echo htmlspecialchars($item['product_image'] ?? 'default.jpg'); ?>" 
                             class="product-img" 
                             onerror="this.src='images/default.jpg'">
                        <div class="product-details">
                            <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="product-meta">Size: <?php echo $item['selected_size'] ?: 'One Size'; ?> | Color: <?php echo $item['colour'] ?: 'Default'; ?></div>
                        </div>
                        <div class="product-qty">x<?php echo $item['quantity']; ?></div>
                        <div class="product-price">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>RM <?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <?php if ($points_discount > 0): ?>
                    <div class="summary-row">
                        <span>Points Discount</span>
                        <span>- RM <?php echo number_format($points_discount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row">
                        <span>Shipping Fee</span>
                        <span>
                            <?php if ($shipping_fee > 0): ?>
                                <span class="shipping-fee-amount">RM <?php echo number_format($shipping_fee, 2); ?></span>
                            <?php else: ?>
                                <span class="free-shipping-badge"><i class="fas fa-gift"></i> FREE</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Paid</span>
                        <span>RM <?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
                
                <div class="points-row">
                    <span><i class="fas fa-star" style="color: #ff9800;"></i> Points Earned</span>
                    <span style="font-weight: 800; color: #ff9800;">+<?php echo number_format($points_earned); ?> points</span>
                </div>
                
                <?php if ($points_used > 0): ?>
                <div class="points-row" style="background: #ffebee;">
                    <span><i class="fas fa-coins" style="color: #e60012;"></i> Points Used</span>
                    <span style="font-weight: 800; color: #e60012;">-<?php echo number_format($points_used); ?> points</span>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="member/orders_history.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View My Orders
                    </a>
                    <a href="ViewAllProduct.php" class="btn btn-secondary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function displayFailPage($message = "Payment failed. Please try again.") {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Failed - PXX Mall</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .fail-container {
                max-width: 450px;
                background: white;
                border-radius: 32px;
                padding: 50px;
                text-align: center;
                box-shadow: 0 25px 50px rgba(0,0,0,0.2);
                animation: slideUp 0.5s ease;
            }
            @keyframes slideUp {
                from { transform: translateY(30px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
            .fail-icon {
                width: 100px;
                height: 100px;
                background: #ffebee;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
            }
            .fail-icon i { font-size: 50px; color: #f44336; }
            h1 { font-size: 28px; color: #333; margin-bottom: 10px; }
            p { color: #666; margin-bottom: 30px; }
            .btn {
                display: inline-block;
                padding: 12px 28px;
                background: #e60012;
                color: white;
                text-decoration: none;
                border-radius: 40px;
                margin: 5px;
                transition: 0.3s;
            }
            .btn:hover { background: #cc0010; transform: translateY(-2px); }
            .btn-gray { background: #666; }
        </style>
    </head>
    <body>
        <div class="fail-container">
            <div class="fail-icon">
                <i class="fas fa-times"></i>
            </div>
            <h1>Payment Failed</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <a href="memberProduct/unified_cart.php" class="btn">← Back to Cart</a>
            <a href="index.php" class="btn btn-gray">🏠 Back to Home</a>
        </div>
    </body>
    </html>
    <?php
}

function displayPendingPage() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Processing - PXX Mall</title>
        <style>
            body { font-family: 'Poppins', sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
            .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 5px 25px rgba(0,0,0,0.08); }
            .pending { color: #ff9800; font-size: 60px; animation: spin 2s linear infinite; }
            @keyframes spin { 100% { transform: rotate(360deg); } }
            .btn { display: inline-block; background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 40px; margin: 10px; font-weight: 600; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="pending">⏳</div>
            <h1>Payment Processing</h1>
            <p>Your payment is being processed. Please check your order history later.</p>
            <a href="member/orders_history.php" class="btn">View Orders</a>
        </div>
    </body>
    </html>
    <?php
}

function displayNoOrderPage() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Status - PXX Mall</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            body { font-family: 'Poppins', sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
            .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 5px 25px rgba(0,0,0,0.08); }
            .success { color: #4caf50; font-size: 60px; }
            .btn { display: inline-block; background: #e60012; color: white; padding: 12px 25px; text-decoration: none; border-radius: 40px; margin: 10px; font-weight: 600; }
            .btn-gray { background: #666; }
        </style>
    </head>
    <body>
        <script>
            window.onload = function() {
                Swal.fire({
                    title: 'Success!',
                    text: 'Your order has been placed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#4caf50'
                });
            };
        </script>
        <div class="container">
            <div class="success">✓</div>
            <h1>Payment Successful!</h1>
            <p>Your order has been placed successfully.</p>
            <a href="member/orders_history.php" class="btn">View My Orders</a>
            <a href="ViewAllProduct.php" class="btn btn-gray">Continue Shopping</a>
        </div>
    </body>
    </html>
    <?php
}
?>