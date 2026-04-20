<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

// --- 1. 获取所有参数 ---
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$gender_type = isset($_GET['gender_type']) ? trim($_GET['gender_type']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// --- 2. 智能搜索逻辑 ---
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR keywords LIKE ? OR description LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

// --- 3. Gender & Type 筛选逻辑 (包含 Others 修复) ---
if (!empty($gender_type)) {
    // 处理性别过滤
    if (strpos($gender_type, 'Men') !== false) { 
        $sql .= " AND product_id LIKE 'M%'"; 
    } elseif (strpos($gender_type, 'Women') !== false) { 
        $sql .= " AND product_id LIKE 'W%'"; 
    }
    
    // 处理具体类型过滤
    if (strpos($gender_type, 'Shirt') !== false) { 
        $sql .= " AND (name LIKE ? OR keywords LIKE ?)"; 
        $params[] = "%shirt%"; $params[] = "%shirt%"; 
    } elseif (strpos($gender_type, 'Pants') !== false) { 
        // 包含 Pants 和 Skirt
        $sql .= " AND (name LIKE ? OR keywords LIKE ? OR name LIKE ?)"; 
        $params[] = "%pants%"; $params[] = "%pants%"; $params[] = "%skirt%"; 
    } elseif (strpos($gender_type, 'Others') !== false) {
        // Others: 排除 Shirt, Pants 和 Skirt
        $sql .= " AND (name NOT LIKE ? AND name NOT LIKE ? AND name NOT LIKE ?)";
        $params[] = "%shirt%"; $params[] = "%pants%"; $params[] = "%skirt%";
    }
}

// --- 4. 排序逻辑 ---
if ($sort == 'price_low') { 
    $sql .= " ORDER BY price ASC"; 
} elseif ($sort == 'price_high') { 
    $sql .= " ORDER BY price DESC"; 
} else { 
    $sql .= " ORDER BY product_id DESC"; 
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<style>
    .products-container { max-width: 1200px; margin: 20px auto; padding: 0 15px; font-family: 'Arial', sans-serif; background: #fff; }
    
    /* 筛选栏样式 */
    .filter-section { display: flex; gap: 15px; margin-bottom: 30px; justify-content: center; flex-wrap: wrap; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #eee; }
    .filter-select { padding: 12px; border: 1px solid #ccc; border-radius: 6px; min-width: 220px; font-size: 16px; outline: none; }
    .clear-btn { padding: 12px 20px; background: #333; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; }

    /* 网格布局 */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px; }
    
    /* 卡片样式 (完全根据你提供的图示排版) */
    .product-card { background: #fff; border-radius: 4px; overflow: hidden; display: flex; flex-direction: column; transition: 0.3s; }
    
    .card-img-container { width: 100%; height: 300px; background: #f9f9f9; overflow: hidden; }
    .card-img-container img { width: 100%; height: 100%; object-fit: cover; display: block; }
    
    .card-content { padding: 15px 0; display: flex; flex-direction: column; gap: 12px; }
    
    /* 标题：粗体且大 */
    .product-title { font-size: 24px; font-weight: bold; color: #333; text-decoration: none; line-height: 1.2; height: 58px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    /* 价格：大红、加粗、醒目 */
    .product-price { color: #e60012; font-size: 28px; font-weight: 900; margin-top: 5px; }

    /* 积分：浅黄色背景 */
    .pts-badge { background: #fff8e1; color: #ffa000; font-size: 18px; padding: 6px 14px; border-radius: 6px; font-weight: bold; align-self: flex-start; display: flex; align-items: center; gap: 8px; }

    /* 库存：绿色打勾 */
    .stock-status { font-size: 22px; font-weight: bold; margin: 5px 0; display: flex; align-items: center; gap: 8px; }
    .instock { color: #2e7d32; }
    .lowstock { color: #d32f2f; }
    .outstock { color: #9e9e9e; }

    /* 评分和销量：左右布局 */
    .meta-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f0f0f0; padding-top: 15px; margin-top: 5px; }
    .rating-box { display: flex; align-items: center; gap: 10px; font-size: 24px; color: #ffa000; font-weight: bold; }
    .sold-box { font-size: 24px; color: #888; }

    /* 按钮：黑色背景 */
    .btn-add { width: 100%; background: #000; color: #fff; border: none; padding: 18px; border-radius: 12px; font-size: 22px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-top: 10px; }
    .btn-add:hover { background: #333; }
    .btn-add:disabled { background: #ccc; cursor: not-allowed; }
</style>

<div class="products-container">
    <a href="index.php" style="text-decoration:none; color:#666; font-size:14px; margin-bottom: 20px; display: inline-block;">← Back to Home</a>
    
    <h1 style="text-align:center; font-size: 32px; margin-bottom: 30px;">Fashion Collection</h1>

    <form method="GET" action="ViewAllProduct.php" class="filter-section">
        <?php if($search): ?> <input type="hidden" name="query" value="<?php echo htmlspecialchars($search); ?>"> <?php endif; ?>
        
        <select name="gender_type" class="filter-select" onchange="this.form.submit()">
            <option value="" disabled <?php echo empty($gender_type) ? 'selected' : ''; ?>>Select Gender & Type...</option>
            <option value="">All Products</option>
            <optgroup label="Men">
                <option value="Men - Shirt" <?php echo $gender_type == 'Men - Shirt' ? 'selected' : ''; ?>>Men's Shirt</option>
                <option value="Men - Pants" <?php echo $gender_type == 'Men - Pants' ? 'selected' : ''; ?>>Men's Pants</option>
                <option value="Men - Others" <?php echo $gender_type == 'Men - Others' ? 'selected' : ''; ?>>Men's Others</option>
            </optgroup>
            <optgroup label="Women">
                <option value="Women - Shirt" <?php echo $gender_type == 'Women - Shirt' ? 'selected' : ''; ?>>Women's Shirt</option>
                <option value="Women - Pants and Skirt" <?php echo $gender_type == 'Women - Pants and Skirt' ? 'selected' : ''; ?>>Women's Pants & Skirt</option>
                <option value="Women - Others" <?php echo $gender_type == 'Women - Others' ? 'selected' : ''; ?>>Women's Others</option>
            </optgroup>
        </select>

        <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="">Sort By Price</option>
            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>

        <?php if($gender_type || $sort || $search): ?>
            <a href="ViewAllProduct.php" class="clear-btn">Clear All</a>
        <?php endif; ?>
    </form>

    <div class="products-grid">
        <?php if(empty($products)): ?>
            <p style="grid-column: 1/-1; text-align: center; padding: 50px; color: #999; font-size: 20px;">No products matching your criteria.</p>
        <?php else: ?>
            <?php foreach($products as $product): 
                // 图片处理逻辑：优先寻找包含 "main" 的图片
                $images = preg_split('/[\s,]+/', $product['product_image']);
                $display_img = (!empty($images[0])) ? $images[0] : 'default.jpg';
                foreach($images as $img) {
                    if(stripos($img, 'main') !== false) { 
                        $display_img = $img; 
                        break; 
                    }
                }

                // 库存显示逻辑
                $stk = (int)$product['stock'];
                if($stk <= 0) { 
                    $stk_class = 'outstock'; $stk_icon = '✗'; $stk_text = "Out of Stock"; 
                } elseif($stk <= 10) { 
                    $stk_class = 'lowstock'; $stk_icon = '⚠'; $stk_text = "Low Stock ($stk)"; 
                } else { 
                    $stk_class = 'instock'; $stk_icon = '✓'; $stk_text = "In Stock ($stk)"; 
                }
            ?>
            <div class="product-card">
                <div class="card-img-container">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo htmlspecialchars($display_img); ?>" onerror="this.src='images/default.jpg'">
                    </a>
                </div>

                <div class="card-content">
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>" class="product-title">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </a>
                    
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>

                    <div class="pts-badge">
                        🎁 +<?php echo floor($product['price']); ?> pts
                    </div>

                    <div class="stock-status <?php echo $stk_class; ?>">
                        <?php echo $stk_icon; ?> <?php echo $stk_text; ?>
                    </div>

                    <div class="meta-row">
                        <div class="rating-box">
                            <span>⭐</span> 
                            <span><?php echo number_format($product['rating'], 1); ?></span>
                        </div>
                        <div class="sold-box">
                            Sold <?php echo number_format($product['sales_count']); ?>
                        </div>
                    </div>

                    <button class="btn-add add-to-cart" data-id="<?php echo $product['product_id']; ?>" <?php echo $stk <= 0 ? 'disabled' : ''; ?>>
                        Add to Cart
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 处理 AJAX 添加到购物车
    $('.add-to-cart').off('click').on('click', function() {
        var productId = $(this).data('id');
        var $btn = $(this);
        
        $btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: 'memberProducts/add.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Item added to cart!');
                    $btn.prop('disabled', false).text('Add to Cart');
                } else {
                    alert(response.error);
                    if(response.error === 'Please login first') {
                        window.location.href = 'login.php';
                    }
                    $btn.prop('disabled', false).text('Add to Cart');
                }
            },
            error: function() {
                alert('Server error. Please try again later.');
                $btn.prop('disabled', false).text('Add to Cart');
            }
        });
    });
});
</script>

<?php require_once 'includes/_foot.php'; ?>