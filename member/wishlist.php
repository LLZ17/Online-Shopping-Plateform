<?php require_once __DIR__ . '/../includes/db.php';
      require_once __DIR__ . '/../includes/_head.php';?>

<style>
    /* 愿望清单专用样式 */
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

    .wishlist-header i {
        color: #e60012;
    }

    /* 表格样式 */
    .wishlist-table {
        width: 100%;
        border-collapse: collapse;
    }

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

    /* 商品展示 */
    .product-box {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .product-img {
        width: 80px;
        height: 100px;
        background-color: #f5f5f5;
        border-radius: 6px;
        object-fit: cover;
    }

    .product-info b {
        display: block;
        font-size: 16px;
        color: #222;
        margin-bottom: 5px;
    }

    .product-info span {
        font-size: 13px;
        color: #999;
    }

    .price-tag {
        font-weight: bold;
        color: #e60012;
        font-size: 18px;
    }

    .qty-input {
        width: 60px;
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }

    /* 操作按钮 */

    .btn-add-cart {
        background-color: #e60012;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-add-cart:hover {
        background-color: #333;
    }

    .remove-icon {
        color: #ccc;
        cursor: pointer;
        font-size: 18px;
        transition: 0.3s;
    }

    .remove-icon:hover {
        color: #e60012;
    }

    /* 为空时的状态 */
    .empty-wishlist {
        text-align: center;
        padding: 50px 0;
        color: #999;
    }

    .not-wishlist {
        text-align: center;
        color: rgba(128, 128, 128, 5);
        font-size: 150%;
    }

    .not-wishlist-link {
        display: flex;
        justify-content: center;
        color: rgba(128, 128, 128, 5);
        font-size: 120%;
        text-decoration: underline;
    }

    .not-wishlist-link:hover {
        color: rgba(100, 149, 237, 5);
    }

    .wishlist-link:hover {
        text-decoration: underline;
    }
</style>

<?php
// 1. 拦截未登录用户
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to use Wishlist!'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT p.* FROM products p JOIN favourites f  ON p.product_id = f.product_id WHERE f.user_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetchAll();

if (!$result): ?>
<div class="wishlist-container">
    <div class="wishlist-card">
        <div class="wishlist-header">
            <i class="fa-solid fa-heart"></i> My Wishlist
        </div>
        <p class = "not-wishlist">Seems nothing in your favouite list now!</p>
        <a href = "itsme.php" class = "not-wishlist-link" >Go Shop and search what you like!</a>
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
                    <th style="width: 50%;">Product Details</th>
                    <th style="width: 20%;">Unit Price</th>
                    <th style="width: 15%;">Quantity</th>
                    <th style="width: 20%;">Stock Status</th>
                    <th style="width: 10%;">Action</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($result as $r): ?>
                <tr>
                    <td>
                        <div class="product-box">
                            <img src= "../images/<?php echo $r['product_image']; ?>" class="product-img" alt="<?php echo $r['name']; ?>">
                            <div class="product-info">
                                <b><a href = "../products/listing.php" class = "wishlist-link"><?php echo $r['name']; ?></a></b>
                                <span>Size: <?php echo $r['size'] ?> | Color: <?php echo $r['colour']; ?></span>
                            </div>
                        </div>
                    </td>
                    <td><span class="price-tag">RM <?php echo $r['price']; ?></span></td>
                    <td><input type="number" value="1" class="qty-input"></td>
                    <td><span style="color: #28a745; font-weight: 300;"><?php echo $r['status']; ?></span></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            /*TODO*/<form method = "POST"><button class="btn-add-cart" title="Move to Cart">Add to Cart</button></form>
                            <i class="fa-solid fa-trash-can remove-icon" title="Remove from Wishlist"></i>
                        </div>
                    </td>
                </tr>
<?php endforeach ?>
<?php endif ?>

               
            </tbody>
        </table>

        </div>
</div>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>