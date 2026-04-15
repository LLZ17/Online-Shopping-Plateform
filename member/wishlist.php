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

<style>
    /* ==========================================
       愿望清单专用样式
       ========================================== */
    .wishlist-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .wishlist-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 30px;
    }

    .wishlist-header {
        font-size: 26px;
        font-weight: bold;
        margin-bottom: 25px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 2px solid #f4f4f4;
        padding-bottom: 15px;
    }

    .wishlist-header i { color: #e60012; }

    .wishlist-table { width: 100%; border-collapse: collapse; }

    .wishlist-table th {
        text-align: left;
        padding: 15px;
        color: #888;
        font-size: 14px;
        border-bottom: 1px solid #eee;
    }

    .wishlist-table td {
        padding: 20px 15px;
        border-bottom: 1px solid #f9f9f9;
        vertical-align: middle;
    }

    .product-box { display: flex; align-items: center; gap: 20px; }

    .product-img {
        width: 80px;
        height: 100px;
        background-color: #f5f5f5;
        border-radius: 6px;
        object-fit: cover;
    }

    .product-info b { display: block; font-size: 16px; color: #222; margin-bottom: 5px; }

    .product-info span { font-size: 13px; color: #999; }

    .price-tag { font-weight: bold; color: #e60012; font-size: 18px; }

    /* 样式更新：使详情按钮作为链接也能呈现按钮外观 */
    .btn-details {
        display: inline-block;
        width: 175px;
        background-color: #e60012;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 14px;
        text-decoration: none;
        transition: 0.3s;
        text-align: center;
    }

    .btn-details:hover { background-color: #333; color: white; }

    .remove-icon {
        color: #ccc;
        cursor: pointer;
        font-size: 18px;
        transition: 0.3s;
    }

    .remove-icon:hover { color: #e60012; }

    .not-wishlist { text-align: center; color: gray; font-size: 150%; }

    .not-wishlist-link {
        display: flex;
        justify-content: center;
        color: gray;
        font-size: 120%;
        text-decoration: underline;
    }

    .not-wishlist-link:hover { color: cornflowerblue; }
</style>

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