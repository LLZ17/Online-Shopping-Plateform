<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR keywords LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY product_id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Our Products</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 20px; }
        .nav-menu { background: #333; padding: 10px 20px; margin-bottom: 20px; text-align: center; border-radius: 5px; }
        .nav-menu a { color: white; margin: 0 15px; text-decoration: none; }
        .nav-menu a:hover { color: #e60012; }
        .nav-menu a.active { color: #e60012; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .product { background: white; border-radius: 10px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .product img { width: 100%; height: 250px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
        .product h3 { margin: 10px 0; font-size: 16px; }
        .product h3 a { text-decoration: none; color: #333; }
        .product h3 a:hover { color: #e60012; }
        .price { color: #e60012; font-size: 22px; font-weight: bold; }
        .stock { font-size: 12px; margin-top: 10px; }
        .in-stock { color: green; }
        .out-stock { color: red; }
        .search-box { margin-bottom: 30px; display: flex; gap: 10px; justify-content: center; }
        .search-box input { padding: 12px; width: 400px; border: 1px solid #ddd; border-radius: 5px; }
        .search-box button { padding: 12px 30px; background: #e60012; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .add-to-cart { background: #e60012; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; margin-top: 10px; width: 100%; }
        .add-to-cart:hover { background: #cc0010; }
        .cart-link { position: fixed; top: 20px; right: 20px; background: #e60012; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; z-index: 100; }
        .cart-link:hover { background: #cc0010; }
        .points { color: #ff9800; font-size: 12px; margin: 5px 0; }
    </style>
</head>
<body>
    <!-- 导航菜单 -->
    <div class="nav-menu">
        <a href="../index.php">🏠 Home</a>
        <a href="listing.php" class="active">🛍️ Products</a>
        <a href="../cart/index.php">🛒 Cart</a>
        <a href="../orders/history.php">📦 Orders</a>
        <?php if(isset($_SESSION['username'])): ?>
            <a href="../profile.php">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></a>
            <a href="../logout.php" style="color: #e60012;">🚪 Logout</a>
        <?php else: ?>
            <a href="../login.php">🔑 Login</a>
            <a href="../signup.php">📝 Sign Up</a>
        <?php endif; ?>
    </div>
    
    <a href="../cart/index.php" class="cart-link">🛒 View Cart</a>
    
    <div class="container">
        <h1>Our Products</h1>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                ⚠️ Please <a href="../login.php">login</a> to add items to cart
            </div>
        <?php endif; ?>
        
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if($search): ?>
                <a href="listing.php" style="margin-left: 10px; padding: 12px 20px; background: #666; color: white; text-decoration: none; border-radius: 5px;">Clear</a>
            <?php endif; ?>
        </form>
        
        <div class="products">
            <?php if (empty($products)): ?>
                <p>No products found.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product">
                        <a href="detail.php?id=<?php echo $product['product_id']; ?>">
                            <img src="../images/<?php echo $product['product_image']; ?>" 
     style="width:100%; height:250px; object-fit:cover;"
     onerror="this.src='../images/default.jpg'">
                        </a>
                        <h3><a href="detail.php?id=<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                        <div class="price">RM <?php echo number_format($product['price'], 2); ?></div>
                        <div class="points">🎁 Earn <?php echo floor($product['price']); ?> points</div>
                        <div class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                            <?php echo $product['stock'] > 0 ? '✓ In Stock (' . $product['stock'] . ')' : '✗ Out of Stock'; ?>
                        </div>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <button class="add-to-cart" data-id="<?php echo $product['product_id']; ?>">Add to Cart</button>
                        <?php else: ?>
                            <button class="add-to-cart" disabled style="background: #ccc;">Out of Stock</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.add-to-cart').click(function(e) {
            e.preventDefault();
            var productId = $(this).data('id');
            var $btn = $(this);
            
            $.ajax({
                url: '../cart/add.php',
                method: 'POST',
                data: { product_id: productId, quantity: 1 },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (confirm('✓ Added to cart! Go to cart now?')) {
                            window.location.href = '../cart/index.php';
                        } else {
                            $btn.text('Added!');
                            setTimeout(function() {
                                $btn.text('Add to Cart');
                            }, 1500);
                        }
                    } else {
                        alert(response.error);
                        if (response.error === 'Please login first') {
                            window.location.href = '../login.php';
                        }
                    }
                },
                error: function() {
                    alert('Error adding to cart');
                }
            });
        });
    });
    </script>
</body>
</html>