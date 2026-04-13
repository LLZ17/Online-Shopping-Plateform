<?php
ob_start();
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// 获取购物车商品
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.product_image, p.stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: unified_cart.php');
    exit();
}

// 计算总额
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// 获取用户信息
$stmt = $pdo->prepare("SELECT points, username, email, phone_num, address FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_points = $user['points'] ?? 0;

$points_to_use = 0;
$points_discount = 0;
$shipping_fee = 0;
$final_total = $subtotal;
$name = $user['username'];
$email = $user['email'];
$phone = $user['phone_num'];
$address = $user['address'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $points_to_use = isset($_POST['use_points']) ? (int)$_POST['use_points'] : 0;
    $name = $_POST['name'] ?? $user['username'];
    $email = $_POST['email'] ?? $user['email'];
    $phone = $_POST['phone'] ?? $user['phone_num'];
    $address = $_POST['address'] ?? $user['address'];
    
    // 积分限制
    if ($points_to_use > $user_points) {
        $points_to_use = $user_points;
    }
    if ($points_to_use < 0) $points_to_use = 0;
    
    // 精确计算折扣
    $points_discount = $points_to_use / 100;
    $after_discount = $subtotal - $points_discount;
    if ($after_discount < 0) $after_discount = 0;
    
    // 运费计算（满 RM100 免邮）
    $shipping_fee = 0;
    if ($after_discount < 100) {
        $shipping_fee = 8.00;
    }
    $final_total = $after_discount + $shipping_fee;
    
    $_SESSION['pending_order'] = [
        'cart_items' => $cart_items,
        'subtotal' => $subtotal,
        'points_to_use' => $points_to_use,
        'points_discount' => $points_discount,
        'shipping_fee' => $shipping_fee,
        'total' => $final_total,
        'user_points' => $user_points,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address
    ];
    
    header('Location: toyyibpay_payment.php');
    exit();
}
?>

<style>
    .checkout-container { max-width: 1200px; margin: 40px auto; display: flex; gap: 40px; flex-wrap: wrap; padding: 0 20px; }
    .checkout-form { flex: 1.5; min-width: 300px; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .order-summary { flex: 1; min-width: 300px; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: fit-content; }
    h2 { margin-bottom: 20px; color: #1a1a1a; border-bottom: 2px solid #e60012; padding-bottom: 10px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
    .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
    .points-section { background: #fff3e0; padding: 20px; border-radius: 12px; margin: 20px 0; }
    .points-input { width: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
    .points-info { font-size: 13px; color: #ff9800; margin-top: 8px; }
    .cart-item { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #eee; }
    .cart-item img { width: 60px; height: 80px; object-fit: cover; border-radius: 8px; }
    .cart-item-info { flex: 1; }
    .cart-item-name { font-weight: 600; }
    .cart-item-price { color: #e60012; }
    .total-row { display: flex; justify-content: space-between; padding: 12px 0; font-size: 16px; }
    .grand-total { font-size: 24px; font-weight: bold; color: #e60012; border-top: 2px solid #eee; padding-top: 15px; margin-top: 15px; }
    .btn-checkout { width: 100%; background: #e60012; color: white; padding: 15px; border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 20px; }
    .btn-checkout:hover { background: #cc0010; }
    .free-shipping-note { font-size: 12px; color: #4caf50; margin-top: 5px; }
</style>

<div class="checkout-container">
    <div class="checkout-form">
        <h2>Shipping Information</h2>
        <form method="POST" id="checkoutForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="form-group">
                <label>Shipping Address</label>
                <textarea name="address" rows="3" required><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            
            <div class="points-section">
                <label style="font-weight: 700;">🎁 Use Reward Points</label>
                <div style="display: flex; gap: 15px; align-items: center; margin-top: 12px; flex-wrap: wrap;">
                    <input type="number" name="use_points" id="points_input" class="points-input" value="0" min="0" max="<?php echo $user_points; ?>" step="100">
                    <span>Available: <strong><?php echo number_format($user_points); ?></strong> points</span>
                </div>
                <div class="points-info">
                    💡 100 points = RM 1.00 discount
                </div>
            </div>
            
            <button type="submit" class="btn-checkout">Proceed to Payment →</button>
        </form>
    </div>
    
    <div class="order-summary">
        <h2>Order Summary</h2>
        <?php foreach ($cart_items as $item): 
            $item_total = $item['price'] * $item['quantity'];
        ?>
            <div class="cart-item">
                <img src="../images/<?php echo htmlspecialchars($item['product_image'] ?? 'default.jpg'); ?>" onerror="this.src='../images/default.jpg'">
                <div class="cart-item-info">
                    <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="cart-item-price">RM <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></div>
                </div>
                <div class="cart-item-price">RM <?php echo number_format($item_total, 2); ?></div>
            </div>
        <?php endforeach; ?>
        
        <div class="total-row">
            <span>Subtotal:</span>
            <span>RM <?php echo number_format($subtotal, 2); ?></span>
        </div>
        
        <div class="total-row" id="discount_row">
            <span>Points Discount:</span>
            <span id="discount_amount">RM 0.00</span>
        </div>
        
        <div class="total-row" id="shipping_row">
            <span>Shipping Fee:</span>
            <span id="shipping_amount">RM 0.00</span>
        </div>
        
        <div class="grand-total">
            <span>Total:</span>
            <span id="total_amount">RM <?php echo number_format($final_total, 2); ?></span>
        </div>
        
        <div class="free-shipping-note" id="shipping_note">
            🚚 Free shipping on orders RM100+
        </div>
        
        <div style="margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 8px; font-size: 12px; color: #666; text-align: center;">
            <i class="fas fa-shield-alt"></i> Secure payment powered by ToyyibPay
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var subtotal = <?php echo $subtotal; ?>;
    var maxPoints = <?php echo $user_points; ?>;
    
    function updateTotals() {
        var points = parseInt($('#points_input').val()) || 0;
        
        if (points > maxPoints) {
            points = maxPoints;
            $('#points_input').val(points);
        }
        if (points < 0) {
            points = 0;
            $('#points_input').val(0);
        }
        
        // 计算折扣
        var discount = points / 100;
        var afterDiscount = subtotal - discount;
        if (afterDiscount < 0) afterDiscount = 0;
        
        // 计算运费（满 RM100 免邮）
        var shipping = 0;
        if (afterDiscount < 100) {
            shipping = 8.00;
        }
        var total = afterDiscount + shipping;
        
        // 更新显示
        $('#discount_amount').text('RM ' + discount.toFixed(2));
        $('#shipping_amount').text(shipping > 0 ? 'RM ' + shipping.toFixed(2) : 'FREE');
        $('#total_amount').text('RM ' + total.toFixed(2));
        
        // 更新运费说明
        if (afterDiscount >= 100) {
            $('#shipping_note').html('✅ Congratulations! You qualify for <strong>FREE shipping</strong>');
            $('#shipping_note').css('color', '#4caf50');
        } else {
            var need = (100 - afterDiscount).toFixed(2);
            $('#shipping_note').html('🚚 Add RM ' + need + ' more to get FREE shipping');
            $('#shipping_note').css('color', '#ff9800');
        }
        
        // 高亮折扣行
        if (points > 0) {
            $('#discount_row').css('background', '#e8f5e9');
        } else {
            $('#discount_row').css('background', 'transparent');
        }
    }
    
    $('#points_input').on('input change', function() {
        updateTotals();
    });
    
    // 初始化
    updateTotals();
});
</script>

<?php require_once '../includes/_foot.php'; 
ob_end_flush();
?>