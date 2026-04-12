<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

$stmt = $pdo->prepare("SELECT * FROM products ORDER BY product_id DESC LIMIT 8");
$stmt->execute();
$products = $stmt->fetchAll();
?>
<style>
    .red-heart {
        color: #e60012;
        cursor: pointer;
    }

    .red-heart:hover {
        font-weight: 900;
    }

    .product-card {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 25px;
    }

    .product-container {
        background: white; border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .product-name-container {
    display: flex;
    justify-content: space-between; /* 名字在左，爱心在右 */
    width: 100%; /* 确保容器占满宽度 */
    }

    .clear-btn {
        border: none;
        background: transparent;
    }

</style>

<div class="main-content">
    <div class="container" style = "margin: 30px;">
        <div class="hero">
            <h1>Welcome to PXX Mall</h1>
            <p>Your Best Online Shopping Platform</p>
        </div>
        
        <h2 style="margin: 40px 0 20px 0; font-size: 24px;">Featured Products</h2>
        
        <div class = "product-card">
            <?php foreach ($products as $product): ?>
                <div class = "prodcut-container">
                    <a href="products/detail.php?id=<?php echo $product['product_id']; ?>" style="text-decoration: none; color: inherit;">
                        <img src="images/<?php echo !empty($product['product_image']) ? $product['product_image'] : 'default.jpg'; ?>" 
                             style="width: 100%; height: 250px; object-fit: cover;"
                             onerror="this.src='images/default.jpg'">
                        <div style="padding: 15px;">
                            <div style ="display: flex">
                                <h3 style="margin: 0 0 10px 0; font-size: 16px;"class="product-name-container"><?php echo htmlspecialchars($product['name']); ?>
                                <a href = "/wishlist_add"><form method = 'post'></form>
                                    <button class = "clear-btn" >
                                        <i class="fa-regular fa-heart red-heart"></i>
                                    </button>
                                </a></h3>
                            </div>
                            <div style="color: #e60012; font-size: 22px; font-weight: bold;">RM <?php echo number_format($product['price'], 2); ?></div>
                            <div style="font-size: 12px; color: #ff9800; margin: 8px 0;">🎁 Earn <?php echo floor($product['price']); ?> points</div>
                            <div style="font-size: 12px; color: <?php echo $product['stock'] > 0 ? 'green' : 'red'; ?>;">
                                <?php echo $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ')' : '✗ Out of Stock'; ?>
                            </div>
                        </div>
                    </a>
                    <?php if ($product['stock'] > 0): ?>
                        <button onclick="addToCart(<?php echo $product['product_id']; ?>)" 
                                style="width: 100%; background: #e60012; color: white; border: none; padding: 12px; cursor: pointer; font-weight: bold;">
                            Add to Cart
                        </button>
                    <?php else: ?>
                        <button disabled style="width: 100%; background: #ccc; border: none; padding: 12px;">
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin: 40px 0;">
            <a href="products/listing.php" style="background: #e60012; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View All Products →
            </a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function addToCart(productId) {
    $.ajax({
        url: 'cart/add.php',
        method: 'POST',
        data: { product_id: productId, quantity: 1 },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (confirm('✓ Added to cart! Go to cart now?')) {
                    window.location.href = 'cart/index.php';
                }
            } else {
                alert(response.error);
                if (response.error === 'Please login first') {
                    window.location.href = 'login.php';
                }
            }
        },
        error: function() {
            alert('Error adding to cart');
        }
    });
}
</script>

<?php require_once 'includes/_foot.php'; ?>
