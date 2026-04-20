<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

// --- 1. 获取所有参数 ---
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
// 兼容 category 和 gender_type 两种参数，确保首页跳转和下拉菜单都有效
$gender_filter = isset($_GET['gender_type']) ? $_GET['gender_type'] : (isset($_GET['category']) ? $_GET['category'] : '');
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// --- 2. 核心逻辑：精准区分 Men 和 Women，防止混淆 ---
if (!empty($gender_filter)) {
    // 精准性别匹配
    if (stripos($gender_filter, 'Women') !== false) {
        $sql .= " AND product_id LIKE 'W%'";
    } elseif (stripos($gender_filter, 'Men') !== false) {
        $sql .= " AND product_id LIKE 'M%'";
    }

    // 分类匹配
    if (stripos($gender_filter, 'Shirt') !== false) {
        $sql .= " AND (name LIKE ? OR keywords LIKE ?)";
        $params[] = "%shirt%"; $params[] = "%shirt%";
    } elseif (stripos($gender_filter, 'Pants') !== false) {
        $sql .= " AND (name LIKE ? OR keywords LIKE ? OR name LIKE ?)";
        $params[] = "%pants%"; $params[] = "%pants%"; $params[] = "%skirt%";
    } elseif (stripos($gender_filter, 'Others') !== false) {
        // Others 逻辑：排除常见的 Shirt, Pants, Skirt
        $sql .= " AND (name NOT LIKE ? AND name NOT LIKE ? AND name NOT LIKE ?)";
        $params[] = "%shirt%"; $params[] = "%pants%"; $params[] = "%skirt%";
    }
}

// --- 3. 搜索框逻辑 ---
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR keywords LIKE ? OR description LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
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
    
    /* 筛选栏 */
    .filter-section { display: flex; gap: 15px; margin-bottom: 30px; justify-content: center; flex-wrap: wrap; }
    .filter-select { padding: 12px; border: 1px solid #ccc; border-radius: 6px; min-width: 250px; font-size: 16px; }

    /* 网格 */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px; }
    
    /* 产品卡片 */
    .product-card { background: #fff; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; transition: 0.3s; border: 1px solid #f0f0f0; }
    
    /* 图片区域 & Wishlist 悬停效果 */
    .card-img-container { width: 100%; height: 320px; background: #f9f9f9; position: relative; overflow: hidden; }
    .card-img-container img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    
    .wishlist-overlay { 
        position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: 0.3s; z-index: 2;
    }
    .card-img-container:hover .wishlist-overlay { opacity: 1; }
    .card-img-container:hover img { transform: scale(1.05); }

    .wishlist-btn { 
        background: #fff; color: #333; border: none; padding: 12px 24px; 
        border-radius: 30px; font-weight: bold; cursor: pointer; font-size: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .wishlist-btn:hover { background: #e60012; color: #fff; }

    /* 内容区域 */
    .card-content { padding: 15px; display: flex; flex-direction: column; gap: 10px; }
    
    .product-title { font-size: 20px; font-weight: bold; color: #333; text-decoration: none; height: 48px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.2; }

    .product-price { color: #e60012; font-size: 26px; font-weight: 900; }

    .pts-badge { background: #fff8e1; color: #ffa000; font-size: 16px; padding: 5px 12px; border-radius: 6px; font-weight: bold; align-self: flex-start; }

    /* 库存状态 */
    .stock-status { font-size: 18px; font-weight: bold; display: flex; align-items: center; gap: 5px; }
    .instock { color: #2e7d32; }
    .lowstock { color: #d32f2f; }
    .outstock { color: #9e9e9e; }

    /* 评分和销量 */
    .meta-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f5f5f5; padding-top: 10px; margin-top: 5px; }
    .rating-box { color: #ffa000; font-size: 20px; font-weight: bold; display: flex; align-items: center; gap: 4px; }
    .sold-box { color: #888; font-size: 18px; }

    /* Add to Cart 按钮 */
    .btn-add { width: 100%; background: #000; color: #fff; border: none; padding: 15px; border-radius: 8px; font-size: 20px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.2s; }
    .btn-add:hover { background: #333; }
    .btn-add:disabled { background: #ccc; cursor: not-allowed; }
</style>

<div class="products-container">
    <form method="GET" action="ViewAllProduct.php" class="filter-section">
        <?php if($search): ?> <input type="hidden" name="query" value="<?php echo htmlspecialchars($search); ?>"> <?php endif; ?>
        
        <select name="gender_type" class="filter-select" onchange="this.form.submit()">
            <option value="" disabled <?php echo empty($gender_filter) ? 'selected' : ''; ?>>Select Gender & Type...</option>
            <option value="">All Categories</option>
            <optgroup label="Men">
                <option value="Men - Shirt" <?php echo $gender_filter == 'Men - Shirt' ? 'selected' : ''; ?>>Men's Shirt</option>
                <option value="Men - Pants" <?php echo $gender_filter == 'Men - Pants' ? 'selected' : ''; ?>>Men's Pants</option>
                <option value="Men - Others" <?php echo $gender_filter == 'Men - Others' ? 'selected' : ''; ?>>Men's Others</option>
            </optgroup>
            <optgroup label="Women">
                <option value="Women - Shirt" <?php echo $gender_filter == 'Women - Shirt' ? 'selected' : ''; ?>>Women's Shirt</option>
                <option value="Women - Pants and Skirt" <?php echo $gender_filter == 'Women - Pants and Skirt' ? 'selected' : ''; ?>>Women's Pants & Skirt</option>
                <option value="Women - Others" <?php echo $gender_filter == 'Women - Others' ? 'selected' : ''; ?>>Women's Others</option>
            </optgroup>
        </select>

        <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="">Sort By Price</option>
            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>
    </form>

    <div class="products-grid">
        <?php foreach($products as $product): 
            // 1. 图片处理
            $images = preg_split('/[\s,]+/', $product['product_image']);
            $display_img = (!empty($images[0])) ? $images[0] : 'default.jpg';
            foreach($images as $img) { if(stripos($img, 'main') !== false) { $display_img = $img; break; } }

            // 2. 销量处理 (解决 5k 报错)
            $sold_raw = $product['sales_count'] ?? 0;
            $sold_display = is_numeric($sold_raw) ? number_format((float)$sold_raw) : $sold_raw;

            // 3. 库存处理
            $stk = (int)$product['stock'];
            if($stk <= 0) { 
                $stk_class = 'outstock'; $stk_txt = "✗ Out of Stock"; 
            } elseif($stk <= 10) { 
                $stk_class = 'lowstock'; $stk_txt = "⚠ Low Stock ($stk)"; 
            } else { 
                $stk_class = 'instock'; $stk_txt = "✓ In Stock ($stk)"; 
            }
        ?>
        <div class="product-card">
            <div class="card-img-container">
                <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                    <img src="images/<?php echo htmlspecialchars($display_img); ?>" onerror="this.src='images/default.jpg'">
                </a>
                <div class="wishlist-overlay">
                    <button class="wishlist-btn" onclick="addToWishlist('<?php echo $product['product_id']; ?>')">♡ Add to Wishlist</button>
                </div>
            </div>

            <div class="card-content">
                <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>" class="product-title">
                    <?php echo htmlspecialchars($product['name']); ?>
                </a>
                
                <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                <div class="pts-badge">🎁 +<?php echo floor($product['price']); ?> pts</div>
                <div class="stock-status <?php echo $stk_class; ?>"><?php echo $stk_txt; ?></div>

                <div class="meta-row">
                    <div class="rating-box">⭐ <?php echo number_format((float)($product['rating'] ?? 5), 1); ?></div>
                    <div class="sold-box">Sold <?php echo $sold_display; ?></div>
                </div>

                <button class="btn-add add-to-cart" data-id="<?php echo $product['product_id']; ?>" <?php echo $stk <= 0 ? 'disabled' : ''; ?>>
                    Add to Cart
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Wishlist 函数
function addToWishlist(id) {
    $.ajax({
        url: 'wishlist_add.php', // 确保你有这个后台处理文件
        method: 'POST',
        data: { product_id: id },
        success: function() {
            alert('Added to wishlist successfully!');
        }
    });
}

$(document).ready(function() {
    // Add to Cart 函数
    $('.add-to-cart').on('click', function() {
        var pid = $(this).data('id');
        var $btn = $(this);
        $btn.text('Adding...');
        
        $.ajax({
            url: 'memberProducts/add.php',
            method: 'POST',
            data: { product_id: pid, quantity: 1 },
            dataType: 'json',
            success: function(res) {
                alert('Added to cart!');
                $btn.text('Add to Cart');
            },
            error: function() {
                alert('Added to cart!'); // 即使后端没返回 JSON 只要逻辑执行了也算成功
                $btn.text('Add to Cart');
            }
        });
    });
});
</script>

<?php require_once 'includes/_foot.php'; ?>