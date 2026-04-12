<?php require_once 'includes/_head.php'; ?>

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

    .btn-pay:hover {
        background-color: #333;
        transform: translateY(-2px);
    }
</style>

<div class="cart-container">
    <h1>🛒 My Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <p>Your cart is empty.</p>
            <a href="products/listing.php" class="btn btn-gray">Continue Shopping</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <table>
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="padding:15px;">Product</th>
                        <th style="padding:15px;">Price</th>
                        <th style="padding:15px;">Quantity</th>
                        <th style="padding:15px;">Subtotal</th>
                        <th style="padding:15px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                        <tr>
                            <td style="padding:15px;">
                                <div class="product-info">
                                    <img src="images/<?php echo $item['product_image'] ?: 'default.jpg'; ?>" class="product-img" onerror="this.src='images/default.jpg'">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                        <small>Size: <?php echo !empty($item['selected_size']) ? $item['selected_size'] : 'N/A'; ?></small>
                                    </div>
                                </div>
                            </div>
                            <td class="price">RM <?php echo number_format($item['price'], 2); ?></div>
                            <td><input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="qty-input"></div>
                            <td class="price">RM <?php echo number_format($subtotal, 2); ?></div>
                            <td><a href="?remove=<?php echo $item['cart_id']; ?>" class="remove-link" onclick="return confirm('Remove this item?')">Remove</a></div>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total">
                Total: RM <?php echo number_format($total, 2); ?>
            </div>
            
            <div class="cart-actions">
                <button type="submit" name="update_cart" class="btn btn-gray">Update Cart</button>
                <a href="products/listing.php" class="btn btn-gray">Continue Shopping</a>
                <a href="checkout.php" class="btn">Proceed to Checkout →</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../includes/_foot.php'; ?>