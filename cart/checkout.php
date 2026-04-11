<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='../login.php';</script>";
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
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// 获取用户信息
$stmt = $pdo->prepare("SELECT username, email, phone_num FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? $user['username'];
    $email = $_POST['email'] ?? $user['email'];
    $phone = $_POST['phone'] ?? $user['phone_num'];
    $address = $_POST['address'] ?? '';
    
    if (empty($address)) {
        $error = "Shipping address is required";
    } else {
        $_SESSION['pending_order'] = [
            'cart_items' => $cart_items,
            'total' => $total,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ];
        
        echo "<script>window.location.href='../checkout.php';</script>";
        exit();
    }
}
?>

<style>
    .container { max-width: 1000px; margin: 40px auto; display: flex; gap: 30px; flex-wrap: wrap; }
    .checkout-form { flex: 2; background: white; padding: 30px; border-radius: 12px; }
    .order-summary { flex: 1; background: white; padding: 30px; border-radius: 12px; }
    h2 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 10px; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: bold; }
    input, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
    .btn-pay { background: #e60012; color: white; padding: 15px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 16px; margin-top: 10px; }
    .btn-back { background: #666; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; width: 100%; margin-top: 10px; }
    .error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    .summary-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
    .summary-total { font-size: 20px; font-weight: bold; color: #e60012; margin-top: 15px; padding-top: 15px; border-top: 2px solid #ddd; }
</style>

<div class="container">
    <div class="checkout-form">
        <h2>Checkout</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone_num'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Shipping Address</label>
                <textarea name="address" rows="3" required placeholder="Enter your full address"></textarea>
            </div>
            <button type="submit" class="btn-pay">Proceed to Payment →</button>
            <a href="unified_cart.php" class="btn-back">← Back to Cart</a>
        </form>
    </div>
    
    <div class="order-summary">
        <h2>Order Summary</h2>
        <?php foreach ($cart_items as $item): ?>
            <div class="summary-item">
                <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                <span>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
            </div>
        <?php endforeach; ?>
        <div class="summary-total">
            <span>Total</span>
            <span>RM <?php echo number_format($total, 2); ?></span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>