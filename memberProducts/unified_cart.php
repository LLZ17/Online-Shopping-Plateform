<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cart_id = trim($_POST['cart_id']);
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: unified_cart.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $qty) {
        $qty = max(1, (int)$qty);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$qty, $cart_id, $user_id]);
    }
    header("Location: unified_cart.php");
    exit();
}


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
?>

<link rel="stylesheet" href="../css/app.css">

<div class="unified-cart-container">
    <a href="../index.php" class="unified-cart-back-link">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <h1>🛒 My Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="unified-cart-empty">
            <p>Your cart is empty.</p>
            <a href="listing.php" class="unified-cart-btn-gray">Continue Shopping</a>
        </div>
    <?php else: ?>
        <form method="POST" id="unifiedCartForm">
            <div class="unified-cart-table-wrapper">
                <table class="unified-cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th class="unified-cart-remove-col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td>
                                    <div class="unified-cart-product-info">
                                        <img src="../images/<?php echo $item['product_image'] ?: 'default.jpg'; ?>" 
                                             class="unified-cart-product-img" 
                                             onerror="this.src='../images/default.jpg'">
                                        <div>
                                            <a href="detail.php?id=<?php echo $item['product_id']; ?>" class="unified-cart-product-link">
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            </a>
                                            <br>
                                            <small class="unified-cart-product-size">Size: <?php echo !empty($item['selected_size']) ? $item['selected_size'] : 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </div>
                                <td class="unified-cart-price">RM <?php echo number_format($item['price'], 2); ?></div>
                                <td>
                                    <input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" class="unified-cart-qty-input">
                                </div>
                                <td class="unified-cart-price">RM <?php echo number_format($subtotal, 2); ?></div>
                                <td class="unified-cart-remove-col">
                                    <button type="button" class="unified-cart-remove-btn" data-id="<?php echo $item['cart_id']; ?>">
                                        <i class="fas fa-trash-alt"></i> Remove
                                    </button>
                                </div>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="unified-cart-total">Total: RM <?php echo number_format($total, 2); ?></div>
            
            <div class="unified-cart-actions">
                <button type="submit" name="update_cart" class="unified-cart-btn-gray" id="unifiedCartUpdateBtn">
                    <i class="fas fa-sync-alt"></i> Update Cart
                </button>
                <a href="listing.php" class="unified-cart-btn-gray">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="../member/checkout.php" class="unified-cart-btn-red">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout →
                </a>
            </div>
        </form>
        
        
        <form method="POST" id="removeForm" style="display: none;">
            <input type="hidden" name="remove_item" value="1">
            <input type="hidden" name="cart_id" id="removeCartId">
        </form>
    <?php endif; ?>
</div>

<script src="../js/app.js"></script>

<?php require_once '../includes/_foot.php'; ?>
