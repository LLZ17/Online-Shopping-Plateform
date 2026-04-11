<?php require_once 'includes/_head.php'; ?>
<div class="main-content">
    <div class="container">
        <div class="hero">
            <h1>Welcome to PXX Mall</h1>
            <p>Your Best Online Shopping Platform</p>
        </div>
        
        <div class="product-grid">
            </div>
    </div>
</div>
<style>
    /* 购物车整体容器 */
    .cart-page-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .cart-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        padding: 30px;
    }

    .cart-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 25px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* 购物车表格样式 */
    .cart-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cart-table th {
        text-align: left;
        padding: 15px;
        border-bottom: 2px solid #f4f4f4;
        color: #888;
        font-size: 14px;
    }

    .cart-table td {
        padding: 20px 15px;
        border-bottom: 1px solid #eee;
    }

    /* 商品信息区域 */
    .item-box {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .item-image {
        width: 80px;
        height: 100px;
        background-color: #f0f0f0;
        border-radius: 6px;
        object-fit: cover;
    }

    .item-details b {
        display: block;
        font-size: 16px;
        margin-bottom: 5px;
        color: #222;
    }

    .item-details span {
        font-size: 13px;
        color: #999;
    }

    /* 价格和数量 */
    .price-col { font-weight: 600; color: #444; }
    .total-col { font-weight: bold; color: #e60012; }
    
    .qty-input {
        width: 60px;
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }

    .remove-link {
        color: #999;
        font-size: 13px;
        cursor: pointer;
        transition: 0.3s;
    }

    .remove-link:hover { color: #e60012; text-decoration: underline; }

    /* 底部结算区 */
    .cart-checkout-section {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 15px;
        padding-top: 20px;
        border-top: 2px solid #f4f4f4;
    }

    .final-price-wrapper {
        font-size: 18px;
        color: #555;
    }

    .final-price-wrapper span {
        font-size: 28px;
        font-weight: 900;
        color: #e60012;
        margin-left: 10px;
    }

    .btn-pay {
        background-color: #e60012;
        color: white;
        padding: 15px 60px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 16px;
        transition: 0.3s;
    }

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

    .btn-pay:hover {
        background-color: #333;
        transform: translateY(-2px);
    }
</style>

<?php
require_once 'includes/db.php';

$sql = "SELECT * FROM products";
$result = $pdo->query($sql)->fetchAll(); ?>
<div class="cart-page-container">
    <div class="cart-card">
        <div class="cart-title">
            <i class="fa-solid fa-cart-shopping"></i>Products
        </div>

        <table class="cart-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Product</th>
                    <th style="width: 15%;">Price</th>
                    <th style="width: 15%;">Quantity</th>
                    <th style="width: 15%;">Stock Status</th>
                    <th style="width: 10%;">Action</th>
                </tr>
            </thead>
            <tbody>

<?php foreach ($result as $r): ?>
    <tr>
        <td>
            <div class="item-box">
                <img src="images/<?php echo $r['product_image']; ?>" alt="<?php echo $r['name']; ?>" class = "item-image">
                <div class="item-details">
                    <b><?php echo $r['name']; ?></b>
                    <span>Size: <?php echo $r['size'] ?> | Color: <?php echo $r['colour']; ?></span>
                </div>
            </div>
        </td>
        <td class="price-col">RM <?php echo $r['price']; ?></td>
        <td><input type="number" value="1" class="qty-input"></td>
        <td><span style="color: #28a745; font-weight: 300;"><?php echo $r['status']; ?></span></td>
        <div style="display: flex; align-items: center; gap: 15px;">
            <td><button class="btn-add-cart" title="Move to Cart">Add to Cart</button></td>
        </div>
    </tr>
<?php endforeach ?>
</table>
<?php require_once 'includes/_foot.php'; ?>
