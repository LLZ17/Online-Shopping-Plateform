<?php 
session_start(); 

// 1. 安全拦截：未登录用户重定向
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_selection.php");
    exit();
}

require_once __DIR__ . '/../includes/db.php';

// ==========================================
// 2. 移除愿望清单数据的逻辑
// ==========================================
if (isset($_GET['remove_id'])) {
    $product_to_remove = $_GET['remove_id'];
    $current_user = $_SESSION['user_id'];

    try {
        $delete_sql = "DELETE FROM `favourites` WHERE `product_id` = ? AND `user_id` = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$product_to_remove, $current_user]);

        header("Location: wishlist.php");
        exit();
    } catch (PDOException $e) {
        die("Error removing item: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/_head.php';
?>
<head>
    <link rel = "stylesheet" href = "../css/app.css">
</head>
<?php
// 3. 获取当前用户的愿望清单数据
$user_id = $_SESSION['user_id'];
$sql = "SELECT p.* FROM products p JOIN favourites f ON p.product_id = f.product_id WHERE f.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$result = $stmt->fetchAll();

if (!$result): ?>
<div class="wishlist-container">
    <div class="wishlist-card">
        <div class="wishlist-header">
            <i class="fa-solid fa-heart"></i> My Wishlist
        </div>
        <p class="not-wishlist">Seems nothing in your favourite list now!</p>
        <a href="../index.php" class="not-wishlist-link">Go Shop and search what you like!</a>
    </div>
</div>
<?php else: ?>
<div class="wishlist-container">
    <div class="wishlist-card">
        <div class="wishlist-header">
            <i class="fa-solid fa-heart"></i> My Wishlist
        </div>

        <table class="wishlist-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Products</th>
                    <th style="width: 20%;">Unit Price</th>
                    <th style="width: 15%;">Stock Status</th>
                    <th style="width: 20%;">Action</th> </tr>
            </thead>
            <tbody>
<?php foreach ($result as $r): ?>
                <tr>
                    <td>
                        <div class="product-box">
                            <img src="../images/<?php echo htmlspecialchars($r['product_image']); ?>" class="product-img" alt="<?php echo htmlspecialchars($r['name']); ?>">
                            <div class="product-info">
                                <b><a href="../memberProducts/detail.php?id=<?php echo $r['product_id']; ?>" class="wishlist-link"><?php echo htmlspecialchars($r['name']); ?></a></b>
                                <span>Size: <?php echo $r['size'] ?> | Color: <?php echo $r['colour']; ?></span>
                            </div>
                        </div>
                    </td>
                    <td><span class="price-tag">RM <?php echo number_format($r['price'], 2); ?></span></td>
                    <td><span style="color: #28a745; font-weight: 300;"><?php echo $r['status'] ?? 'In Stock'; ?></span></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <a href="../memberProducts/detail.php?id=<?php echo $r['product_id']; ?>" 
                               class="btn-details" title="View Product Details">🛒 Product Details</a>
                            
                            <a href="wishlist.php?remove_id=<?php echo $r['product_id']; ?>" 
                               onclick="return confirm('Confirm remove this item from wishlist?')">
                                <i class="fa-solid fa-trash-can remove-icon" title="Remove from Wishlist"></i>
                            </a>
                        </div>
                    </td>
                </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php 
require_once __DIR__ . '/../includes/_foot.php';
?>