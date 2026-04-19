<?php 
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

// 获取购物车数据
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

// 处理更新和删除...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $qty) {
        $qty = max(1, (int)$qty);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$qty, $cart_id, $user_id]);
    }
    header("Location: cart.php");
    exit();
}

if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: cart.php");
    exit();
}
?>

<link rel="stylesheet" href="../css/app.css">

<div class="cart-page-container">
    <div class="cart-card">
        <div class="cart-title">
            <i class="fa-solid fa-cart-shopping"></i> My Shopping Cart
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="cart-empty">
                <p>Your cart is empty.</p>
                <a href="products/listing.php" class="cart-btn-gray">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="POST" id="cartUpdateForm">
                <div class="cart-table-wrapper">
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
                            <?php foreach ($cart_items as $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <div class="cart-item-box">
                                            <img src="../images/<?php echo htmlspecialchars($item['product_image'] ?: 'default.jpg'); ?>" 
                                                 class="cart-item-img" 
                                                 onerror="this.src='../images/default.jpg'">
                                            <div class="cart-item-details">
                                                <b><?php echo htmlspecialchars($item['name']); ?></b>
                                                <span>Size: <?php echo htmlspecialchars($item['selected_size'] ?: 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cart-price-col">RM <?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" 
                                               value="<?php echo $item['quantity']; ?>" min="1" class="cart-qty-input">
                                    </td>
                                    <td class="cart-total-col">RM <?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <a href="?remove=<?php echo $item['cart_id']; ?>" class="cart-remove-link" data-id="<?php echo $item['cart_id']; ?>">
                                            Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="cart-total-section">
                    Total: RM <?php echo number_format($total, 2); ?>
                </div>
                
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="cart-btn-gray" id="cartUpdateBtn">Update Cart</button>
                    <a href="products/listing.php" class="cart-btn-gray">Continue Shopping</a>
                    <a href="checkout.php" class="cart-btn-red">Proceed to Checkout →</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="../js/app.js"></script>

<?php require_once '../includes/_foot.php'; ?>
