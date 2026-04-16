<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// 处理删除请求
if (isset($_GET['remove'])) {
    $cart_id = trim($_GET['remove']);
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

// 处理更新购物车请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $qty) {
        $qty = max(1, (int)$qty);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$qty, $cart_id, $user_id]);
    }
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

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
?>

<style>
    .cart-container { max-width: 1000px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    h1 { color: #333; border-bottom: 2px solid #e60012; padding-bottom: 15px; margin-bottom: 25px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
    .product-img { width: 80px; height: 100px; object-fit: cover; border-radius: 8px; background: #f5f5f5; }
    .product-info { display: flex; align-items: center; gap: 15px; }
    .product-name { font-weight: bold; color: #333; }
    .product-size { font-size: 12px; color: #999; margin-top: 5px; }
    .qty-input { width: 70px; padding: 8px; border: 1px solid #ddd; border-radius: 5px; text-align: center; }
    .price { font-weight: bold; color: #e60012; }
    .remove-link { color: #e60012; text-decoration: none; font-size: 13px; cursor: pointer; }
    .remove-link:hover { text-decoration: underline; }
    .total { text-align: right; font-size: 24px; font-weight: bold; color: #e60012; margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee; }
    .btn { background: #e60012; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-left: 10px; font-size: 14px; }
    .btn-gray { background: #666; }
    .empty-cart { text-align: center; padding: 50px; color: #999; }
    .cart-actions { text-align: right; margin-top: 20px; }
    .back-link { display: inline-block; margin-bottom: 20px; color: #666; text-decoration: none; font-size: 14px; }
    .back-link:hover { color: #e60012; }
    .product-link { text-decoration: none; color: #333; }
    .product-link:hover { color: #e60012; text-decoration: underline; }
    .remove-col { text-align: center; width: 80px; }
</style>

<div class="cart-container">
    <a href="../index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <h1>🛒 My Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <p>Your cart is empty.</p>
            <a href="listing.php" class="btn btn-gray">Continue Shopping</a>
        </div>
    <?php else: ?>
        <form method="POST" id="cartForm">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th class="remove-col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <img src="../images/<?php echo $item['product_image'] ?: 'default.jpg'; ?>" 
                                             class="product-img" 
                                             onerror="this.src='../images/default.jpg'">
                                        <div>
                                            <a href="detail.php?id=<?php echo $item['product_id']; ?>" class="product-link">
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            </a>
                                            <br>
                                            <small class="product-size">Size: <?php echo !empty($item['selected_size']) ? $item['selected_size'] : 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </div>
                                <td class="price">RM <?php echo number_format($item['price'], 2); ?></div>
                                <td>
                                    <input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" class="qty-input">
                                </div>
                                <td class="price">RM <?php echo number_format($subtotal, 2); ?></div>
                                <td class="remove-col">
                                    <a href="unified_cart.php?remove=<?php echo $item['cart_id']; ?>" class="remove-link" id="remove-<?php echo $item['cart_id']; ?>">
                                        <i class="fas fa-trash-alt"></i> Remove
                                    </a>
                                </div>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="total">Total: RM <?php echo number_format($total, 2); ?></div>
            
            <div class="cart-actions">
                <button type="submit" name="update_cart" class="btn btn-gray" id="updateCartBtn">
                    <i class="fas fa-sync-alt"></i> Update Cart
                </button>
                <a href="listing.php" class="btn btn-gray">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="checkout.php" class="btn">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout →
                </a>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Update Cart 按钮 - 绑定事件，没有 inline
    $('#updateCartBtn').on('click', function(e) {
        // 不需要阻止默认行为，让表单正常提交
        // 但可以先验证数量
        var isValid = true;
        $('.qty-input').each(function() {
            var val = parseInt($(this).val());
            if (isNaN(val) || val < 1) {
                alert('Quantity must be at least 1');
                $(this).val(1);
                isValid = false;
                return false;
            }
        });
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Remove 按钮 - 绑定事件，没有 inline onclick
    $('.remove-link').on('click', function(e) {
        if (!confirm('Are you sure you want to remove this item?')) {
            e.preventDefault();
        }
    });
    
    // 数量输入框验证
    $('.qty-input').on('change', function() {
        var val = parseInt($(this).val());
        if (isNaN(val) || val < 1) {
            $(this).val(1);
        }
    });
});
</script>

<?php require_once '../includes/_foot.php'; ?>
