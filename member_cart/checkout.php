<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.product_image, p.stock 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$stmt = $pdo->prepare("SELECT username, email, phone_num, points FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$user_points = $user['points'] ?? 0;
$max_points_to_use = min($user_points, $total * 100);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? $user['username'];
    $email = $_POST['email'] ?? $user['email'];
    $phone = $_POST['phone'] ?? $user['phone_num'];
    $address = $_POST['address'] ?? '';
    $points_to_use = (int)($_POST['points_to_use'] ?? 0);
    
    if (empty($address)) {
        $error = "Shipping address is required";
    } elseif ($points_to_use > $max_points_to_use) {
        $error = "You don't have enough points";
    } else {
        $points_discount = $points_to_use / 100;
        $final_total = $total - $points_discount;
        
        $_SESSION['pending_order'] = [
            'cart_items' => $cart_items,
            'total' => $total,
            'final_total' => $final_total,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'points_to_use' => $points_to_use,
            'user_points' => $user_points
        ];
        echo "<script>window.location.href='toyyibpay_payment.php';</script>";
        exit();
    }
}
?>

<style>
    .container { max-width: 1000px; margin: 40px auto; display: flex; gap: 30px; flex-wrap: wrap; }
    .checkout-form { flex: 2; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .order-summary { flex: 1; background: white; padding: 30px; border-radius: 12px; height: fit-content; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    h2 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 10px; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
    input, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
    .btn-pay { background: #e60012; color: white; padding: 15px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; margin-top: 10px; }
    .btn-back { background: #666; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; width: 100%; margin-top: 10px; }
    .error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    .summary-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
    .summary-total { font-size: 20px; font-weight: bold; color: #e60012; margin-top: 15px; padding-top: 15px; border-top: 2px solid #ddd; }
    .points-box { background: #fff3e0; padding: 15px; border-radius: 10px; margin: 20px 0; }
    .points-input { width: 150px; padding: 10px; margin-top: 10px; border: 1px solid #ddd; border-radius: 5px; }
</style>

<div class="container">
    <div class="checkout-form">
        <h2>Checkout</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>Full Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required></div>
            <div class="form-group"><label>Phone Number</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone_num'] ?? ''); ?>" required></div>
            <div class="form-group"><label>Shipping Address</label><textarea name="address" rows="3" required placeholder="Enter your full address"></textarea></div>
            
            <div class="points-box">
                <strong>💰 Your Points: <?php echo number_format($user_points); ?></strong><br>
                <small>10 points = RM 0.10 discount | 100 points = RM 1.00</small><br>
                <label>Use points:</label>
                <input type="number" name="points_to_use" class="points-input" min="0" max="<?php echo $max_points_to_use; ?>" value="0" id="points_input">
            </div>
            
            <button type="submit" class="btn-pay">Proceed to Payment →</button>
            <a href="unified_cart.php" class="btn-back">← Back to Cart</a>
        </form>
    </div>
    <div class="order-summary">
        <h2>Order Summary</h2>
        <?php foreach ($cart_items as $item): ?>
            <div class="summary-item"><span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span><span>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span></div>
        <?php endforeach; ?>
        <div class="summary-item"><strong>Subtotal</strong><strong id="subtotal">RM <?php echo number_format($total, 2); ?></strong></div>
        <div id="discount_row" class="summary-item" style="display:none;"><span>Points Discount</span><span id="discount_amount">-RM 0.00</span></div>
        <div class="summary-total"><span>Total</span><span id="total_amount">RM <?php echo number_format($total, 2); ?></span></div>
    </div>
</div>

<script>
    const subtotal = <?php echo $total; ?>;
    function updateTotal() {
        let points = parseInt(document.getElementById('points_input').value) || 0;
        let discount = points / 100;
        let newTotal = subtotal - discount;
        if (discount > 0) {
            document.getElementById('discount_row').style.display = 'flex';
            document.getElementById('discount_amount').innerText = '-RM ' + discount.toFixed(2);
        } else {
            document.getElementById('discount_row').style.display = 'none';
        }
        document.getElementById('total_amount').innerText = 'RM ' + newTotal.toFixed(2);
    }
    document.getElementById('points_input').addEventListener('change', updateTotal);
    document.getElementById('points_input').addEventListener('keyup', updateTotal);
</script>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>