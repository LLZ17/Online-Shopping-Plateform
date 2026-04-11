<?php require_once 'includes/_head.php'; ?>

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
</style>

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
                    <th style="width: 20%;">Stock Status</th>
                    <th style="width: 10%;">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="product-box">
                            <img src="https://via.placeholder.com/80x100" class="product-img" alt="Product">
                            <div class="product-info">
                                <b>Classic Leather Watch</b>
                                <span>Category: Accessories</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="price-tag">RM 199.00</span></td>
                    <td><span style="color: #28a745; font-weight: 600;">In Stock</span></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <button class="btn-add-cart" title="Move to Cart">Add to Cart</button>
                            <i class="fa-solid fa-trash-can remove-icon" title="Remove from Wishlist"></i>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="product-box">
                            <img src="https://via.placeholder.com/80x100" class="product-img" alt="Product">
                            <div class="product-info">
                                <b>Retro Sunglasses</b>
                                <span>Category: Eyewear</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="price-tag">RM 45.00</span></td>
                    <td><span style="color: #28a745; font-weight: 600;">In Stock</span></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <button class="btn-add-cart" title="Move to Cart">Add to Cart</button>
                            <i class="fa-solid fa-trash-can remove-icon" title="Remove from Wishlist"></i>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        </div>
</div>

<?php require_once 'includes/_foot.php'; ?>