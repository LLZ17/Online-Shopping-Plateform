<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

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

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// 删除商品
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header('Location: index.php');
    exit();
}

// 更新数量
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $qty) {
        $qty = max(1, (int)$qty);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$qty, $cart_id, $user_id]);
    }
    header('Location: index.php');
    exit();
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; }
    .cart-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
    .cart-card { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .cart-title { font-size: 28px; font-weight: bold; color: #333; border-bottom: 3px solid #e60012; padding-bottom: 15px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
    .cart-table { width: 100%; border-collapse: collapse; }
    .cart-table th { text-align: left; padding: 15px; color: #888; font-weight: 500; border-bottom: 1px solid #eee; }
    .cart-table td { padding: 20px 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .product-info { display: flex; align-items: center; gap: 15px; }
    .product-img { width: 70px; height: 90px; object-fit: cover; border-radius: 8px; background: #f5f5f5; }
    .product-name { font-weight: bold; color: #333; margin-bottom: 5px; }
    .product-detail { font-size: 12px; color: #999; margin-top: 5px; }
    .qty-input { width: 65px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; text-align: center; }
    .price { font-weight: bold; color: #e60012; }
    .remove-link { color: #e60012; text-decoration: none; font-size: 13px; }
    .remove-link:hover { text-decoration: underline; }
    .cart-summary { margin-top: 30px; text-align: right; border-top: 2px solid #eee; padding-top: 25px; }
    .total { font-size: 24px; font-weight: bold; color: #e60012; margin-bottom: 20px; }
    .btn { padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; transition: 0.3s; }
    .btn-update { background: #666; color: white; margin-right: 10px; }
    .btn-update:hover { background: #555; }
    .btn-checkout { background: #e60012; color: white; }
    .btn-checkout:hover { background: #cc0010; transform: translateY(-2px); }
    .btn-shop { background: #333; color: white; margin-right: 10px; }
    .btn-shop:hover { background: #222; }
    .empty-cart { text-align: center; padding: 60px; color: #999; }
    .empty-cart i { font-size: 60px; margin-bottom: 20px; display: block; }
    .empty-cart a { color: #e60012; text-decoration: none; font-weight: bold; }
</style>

<div class="cart-container">
    <div class="cart-card">
        <div class="cart-title">
            <i class="fa-solid fa-cart-shopping"></i> My Shopping Cart
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fa-solid fa-bag-shopping"></i>
                <p>Your cart is empty.</p>
                <a href="../products/listing.php">← Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Product</th>
                            <th style="width: 15%;">Price</th>
                            <th style="width: 15%;">Quantity</th>
                            <th style="width: 15%;">Subtotal</th>
                            <th style="width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="../images/<?php echo $item['product_image'] ?: 'default.jpg'; ?>" class="product-img" onerror="this.src='../images/default.jpg'">
                                        <div>
                                            <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <div class="product-detail">
                                                Size: <strong><?php echo !empty($item['selected_size']) ? $item['selected_size'] : 'N/A'; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="price">RM <?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" min="1" 
                                           max="<?php echo $item['stock']; ?>" class="qty-input">
                                </td>
                                <td class="price">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td><a href="?remove=<?php echo $item['cart_id']; ?>" class="remove-link" onclick="return confirm('Remove this item?')">Remove</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <div class="total">Total: RM <?php echo number_format($total, 2); ?></div>
                    <button type="submit" name="update_cart" class="btn btn-update">Update Cart</button>
                    <a href="../products/listing.php" class="btn btn-shop">← Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout →</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>