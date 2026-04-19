<?php
ob_start();
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

// AJAX 验证优惠券
if (isset($_GET['action']) && $_GET['action'] == 'validate_voucher') {
    $code = $_GET['code'] ?? '';
    $current_subtotal = (float)($_GET['subtotal'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND status = 'Active' AND expiry_date > NOW() LIMIT 1");
    $stmt->execute([$code]);
    $voucher = $stmt->fetch();

    header('Content-Type: application/json');
    
    if (!$voucher) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired voucher code.']);
    } elseif ($current_subtotal < $voucher['min_spend']) {
        echo json_encode(['success' => false, 'message' => 'Min spend not reached. You need RM ' . number_format($voucher['min_spend'], 2) . ' to use this.']);
    } elseif ($voucher['usage_limit'] !== null && $voucher['used_count'] >= $voucher['usage_limit']) {
        echo json_encode(['success' => false, 'message' => 'Voucher usage limit reached.']);
    } else {
        echo json_encode([
            'success' => true, 
            'discount_value' => (float)$voucher['discount_value'],
            'voucher_id' => $voucher['voucher_id']
        ]);
    }
    exit();
}

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
$voucher_discount = 0;
$voucher_id = null;
$shipping_fee = 0;
$final_total = $subtotal;
$name = $user['username'];
$email = $user['email'];
$phone = $user['phone_num'];
$address = $user['address'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $points_to_use = isset($_POST['use_points']) ? (int)$_POST['use_points'] : 0;
    $voucher_code = $_POST['voucher_code'] ?? '';
    $name = $_POST['name'] ?? $user['username'];
    $email = $_POST['email'] ?? $user['email'];
    $phone = $_POST['phone'] ?? $user['phone_num'];
    $address = $_POST['address'] ?? $user['address'];
    
    if ($points_to_use > $user_points) $points_to_use = $user_points;
    if ($points_to_use < 0) $points_to_use = 0;
    $points_discount = $points_to_use / 100;

    if (!empty($voucher_code)) {
        $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND status = 'Active' AND expiry_date > NOW() LIMIT 1");
        $stmt->execute([$voucher_code]);
        $v = $stmt->fetch();
        if ($v && $subtotal >= $v['min_spend'] && ($v['usage_limit'] === null || $v['used_count'] < $v['usage_limit'])) {
            $voucher_discount = (float)$v['discount_value'];
            $voucher_id = $v['voucher_id'];
            $update_stmt = $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE voucher_id = ?");
            $update_stmt->execute([$voucher_id]);
        }
    }
    
    $after_discount = $subtotal - $points_discount - $voucher_discount;
    if ($after_discount < 0) $after_discount = 0;
    
    if ($after_discount < 100) {
        $shipping_fee = 8.00;
    }
    $final_total = $after_discount + $shipping_fee;
    
    $_SESSION['pending_order'] = [
        'cart_items' => $cart_items,
        'subtotal' => $subtotal,
        'points_to_use' => $points_to_use,
        'points_discount' => $points_discount,
        'voucher_discount' => $voucher_discount,
        'voucher_id' => $voucher_id,
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

<link rel="stylesheet" href="../css/app.css">

<div class="member-checkout-container">
    <div class="member-checkout-form">
        <h2>Shipping Information</h2>
        <form method="POST" id="memberCheckoutForm">
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
            
            <div class="member-points-section">
                <label class="points-label">🎁 Use Reward Points (100 points = RM 1)</label>
                <div class="points-input-group">
                    <input type="number" name="use_points" id="memberPointsInput" class="member-points-input" 
                           value="0" min="0" max="<?php echo $user_points; ?>" step="1"
                           style="width: 180px; padding: 12px; border: 2px solid #e60012; border-radius: 8px; font-size: 16px;">
                    <span style="font-size: 14px;">Available: <strong><?php echo number_format($user_points); ?></strong> points</span>
                    <small style="display: block; width: 100%; margin-top: 8px; color: #888; font-size: 12px;">※ You can use any number of points (1 point = RM 0.01)</small>
                </div>
            </div>

            <div class="member-voucher-section">
                <label class="voucher-label">🎟️ Use Discount Voucher</label>
                <div class="voucher-input-group">
                    <input type="text" name="voucher_code" id="memberVoucherInput" class="member-voucher-input" placeholder="Enter Code">
                    <button type="button" id="memberApplyVoucherBtn" class="member-apply-voucher-btn">Apply Voucher</button>
                    <span id="memberVoucherMsg" class="voucher-msg"></span>
                </div>
            </div>
            
            <button type="submit" class="member-checkout-btn">Proceed to Payment →</button>
        </form>
    </div>
    
    <div class="member-order-summary">
        <h2>Order Summary</h2>
        <?php foreach ($cart_items as $item): 
            $item_total = $item['price'] * $item['quantity'];
        ?>
            <div class="member-cart-item">
                <img src="../images/<?php echo htmlspecialchars($item['product_image'] ?? 'default.jpg'); ?>" class="member-cart-img" onerror="this.src='../images/default.jpg'">
                <div class="member-cart-info">
                    <div class="member-cart-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="member-cart-price">RM <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></div>
                </div>
                <div class="member-cart-subtotal">RM <?php echo number_format($item_total, 2); ?></div>
            </div>
        <?php endforeach; ?>
        
        <div class="member-total-row">
            <span>Subtotal:</span>
            <span id="memberSubtotalDisplay">RM <?php echo number_format($subtotal, 2); ?></span>
        </div>
        
        <div class="member-total-row" id="memberDiscountRow">
            <span>Points Discount:</span>
            <span id="memberDiscountAmount">RM 0.00</span>
        </div>

        <div class="member-total-row" id="memberVoucherRow">
            <span>Voucher Discount:</span>
            <span id="memberVoucherDiscountDisplay">RM 0.00</span>
        </div>
        
        <div class="member-total-row" id="memberShippingRow">
            <span>Shipping Fee:</span>
            <span id="memberShippingAmount">RM 0.00</span>
        </div>
        
        <div class="member-grand-total">
            <span>Total:</span>
            <span id="memberTotalAmount">RM <?php echo number_format($final_total, 2); ?></span>
        </div>
        
        <div class="member-shipping-note" id="memberShippingNote">
            🚚 Free shipping on orders RM100+
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var subtotal = <?php echo (float)$subtotal; ?>;
    var maxPoints = <?php echo (int)$user_points; ?>;
    var currentVoucherDiscount = 0;
    
    console.log("Checkout page loaded. Subtotal: " + subtotal + ", Max Points: " + maxPoints);
    
    function updateTotals() {
        var points = parseInt($('#memberPointsInput').val()) || 0;
        console.log("Points entered: " + points);
        
        if (points > maxPoints) { 
            points = maxPoints; 
            $('#memberPointsInput').val(points); 
        }
        if (points < 0) { 
            points = 0; 
            $('#memberPointsInput').val(0); 
        }
        
        var pDiscount = points / 100;
        var afterDiscount = subtotal - pDiscount - currentVoucherDiscount;
        if (afterDiscount < 0) afterDiscount = 0;
        
        var shipping = (afterDiscount < 100 && afterDiscount > 0) ? 8.00 : 0;
        var total = afterDiscount + shipping;
        
        $('#memberDiscountAmount').text('RM ' + pDiscount.toFixed(2));
        $('#memberVoucherDiscountDisplay').text('RM ' + currentVoucherDiscount.toFixed(2));
        $('#memberShippingAmount').text(shipping > 0 ? 'RM ' + shipping.toFixed(2) : 'FREE');
        $('#memberTotalAmount').text('RM ' + total.toFixed(2));
        
        if (afterDiscount >= 100) {
            $('#memberShippingNote').html('✅ Congratulations! You qualify for <strong>FREE shipping</strong>');
        } else {
            var need = (100 - afterDiscount).toFixed(2);
            $('#memberShippingNote').html('🚚 Add RM ' + need + ' more to get FREE shipping');
        }
    }
    
    
    $('#memberPointsInput').on('input change keyup', function() {
        console.log("Input changed");
        updateTotals();
    });
    
    
    $('#memberApplyVoucherBtn').on('click', function() {
        var code = $('#memberVoucherInput').val();
        console.log("Applying voucher: " + code);
        
        if (!code) { 
            alert('Please enter a voucher code.'); 
            return; 
        }

        $.ajax({
            url: 'checkout.php',
            type: 'GET',
            data: { action: 'validate_voucher', code: code, subtotal: subtotal },
            dataType: 'json',
            success: function(res) {
                console.log("Voucher response:", res);
                if (res.success) {
                    currentVoucherDiscount = parseFloat(res.discount_value);
                    $('#memberVoucherMsg').css('color', '#4caf50').text('✅ Voucher applied: -RM ' + currentVoucherDiscount.toFixed(2));
                } else {
                    currentVoucherDiscount = 0;
                    $('#memberVoucherMsg').css('color', '#e60012').text('❌ ' + res.message);
                }
                updateTotals();
            },
            error: function() {
                alert('Voucher code error.');
            }
        });
    });
    
    
    updateTotals();
    
    
    $('#memberPointsInput').prop('disabled', false);
    $('#memberPointsInput').prop('readonly', false);
});
</script>

<?php require_once '../includes/_foot.php'; 
ob_end_flush();
?>
