<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='login.php';</script>";
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

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $qty) {
        $qty = max(1, (int)$qty);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$qty, $cart_id, $user_id]);
    }
    echo "<script>window.location.href='cart.php';</script>";
    exit();
}
?>

<style>
    .cart-container { max-width: 1000px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; }
    h1 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 15px; margin-bottom: 25px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
    .product-img { width: 80px; height: 100px; object-fit: cover; border-radius: 8px; }
    .product-info { display: flex; align-items: center; gap: 15px; }
    .qty-input { width: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 5px; text-align: center; }
    .price { font-weight: bold; color: #e60012; }
    .remove-link { color: #e60012; text-decoration: none; }
    .total { text-align: right; font-size: 24px; font-weight: bold; color: #e60012; margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee; }
    .btn { background: #e60012; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-left: 10px; }
    .btn-gray { background: #666; }
    .empty-cart { text-align: center; padding: 50px; color: #999; }
    .cart-actions { text-align: right; margin-top: 20px; }
</style>

<div class="cart-container">
    <h1>🛒 My Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <p>Your cart is empty.</p>
            <a href="products/listing.php" class="btn btn-gray">Continue Shopping</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <table>
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="padding:15px;">Product</th>
                        <th style="padding:15px;">Price</th>
                        <th style="padding:15px;">Quantity</th>
                        <th style="padding:15px;">Subtotal</th>
                        <th style="padding:15px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                        <tr>
                            <td style="padding:15px;">
                                <div class="product-info">
                                    <img src="images/<?php echo $item['product_image'] ?: 'default.jpg'; ?>" class="product-img" onerror="this.src='images/default.jpg'">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                        <small>Size: <?php echo !empty($item['selected_size']) ? $item['selected_size'] : 'N/A'; ?></small>
                                    </div>
                                </div>
                            </div>
                            <td class="price">RM <?php echo number_format($item['price'], 2); ?></div>
                            <td><input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="qty-input"></div>
                            <td class="price">RM <?php echo number_format($subtotal, 2); ?></div>
                            <td><a href="?remove=<?php echo $item['cart_id']; ?>" class="remove-link" onclick="return confirm('Remove this item?')">Remove</a></div>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                Total: RM <?php echo number_format($total, 2); ?>
            </div>
            
            <div class="cart-actions">
                <button type="submit" name="update_cart" class="btn btn-gray">Update Cart</button>
                <a href="products/listing.php" class="btn btn-gray">Continue Shopping</a>
                <a href="checkout.php" class="btn">Proceed to Checkout →</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/_foot.php'; ?>