<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

$current_user_id = $_SESSION['user_id'] ?? null;

// 获取参数
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$gender_filter = $_GET['gender_type'] ?? $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? '';

// 构建 SQL
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM favourites f WHERE f.product_id = p.product_id AND f.user_id = ?) as is_favourite 
        FROM products p WHERE 1=1";
$params = [$current_user_id];

// 分类筛选逻辑 (包含 Others)
if (!empty($gender_filter)) {
    if (stripos($gender_filter, 'Women') !== false) {
        $sql .= " AND p.product_id LIKE 'W%'";
    } elseif (stripos($gender_filter, 'Men') !== false) {
        $sql .= " AND p.product_id LIKE 'M%'";
    }

    if (stripos($gender_filter, 'Shirt') !== false) {
        $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ?)";
        $params[] = "%shirt%"; $params[] = "%shirt%";
    } elseif (stripos($gender_filter, 'Pants') !== false) {
        $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ? OR p.name LIKE ?)";
        $params[] = "%pants%"; $params[] = "%pants%"; $params[] = "%skirt%";
    } elseif (stripos($gender_filter, 'Others') !== false) {
        // Others 逻辑：排除 Shirt 和 Pants
        $sql .= " AND (p.name NOT LIKE ? AND p.name NOT LIKE ? AND p.name NOT LIKE ?)";
        $params[] = "%shirt%"; $params[] = "%pants%"; $params[] = "%skirt%";
    }
}

// 搜索逻辑
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

// 排序
if ($sort == 'price_low') { $sql .= " ORDER BY p.price ASC"; }
elseif ($sort == 'price_high') { $sql .= " ORDER BY p.price DESC"; }
else { $sql .= " ORDER BY p.product_id DESC"; }

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<link rel="stylesheet" href="css/app.css">
<style>
    .view-all-container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
    
    /* 顶部工具栏：返回按钮 + 筛选 */
    .toolbar { display: flex; gap: 15 echo; margin-bottom: 30px; align-items: center; justify-content: center; background: #f9f9f9; padding: 15px; border-radius: 10px; flex-wrap: wrap; }
    
    .btn-back { text-decoration: none; background: #333; color: white; padding: 10px 20px; border-radius: 6px; font-weight: bold; transition: 0.3s; }
    .btn-back:hover { background: #000; transform: translateX(-5px); }

    .filter-select { padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-width: 220px; font-size: 15px; }

    /* 修复字体截断 (针对 image_f862a3.png) */
    .product-name { 
        min-height: 54px; /* 确保足够高度显示两行 */
        line-height: 1.4; 
        margin-top: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-name a { color: #333; text-decoration: none; font-weight: bold; font-size: 17px; }

    /* Wishlist 按钮样式 (针对 image_6182a6.png) */
    .quick-wishlist {
        position: absolute; top: 15px; right: 15px;
        background: rgba(255,255,255,0.9); border: none;
        padding: 8px 12px; border-radius: 20px;
        cursor: pointer; font-weight: bold; font-size: 13px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: 0.3s;
        z-index: 10;
    }
    .quick-wishlist.active { background: #e60012; color: white; }
    .quick-wishlist:hover { transform: scale(1.1); }
</style>

<div class="view-all-container">
    <div class="toolbar">
        <a href="index.php" class="btn-back">⬅ Back to Home</a>

        <form method="GET" style="display:flex; gap:10px;">
            <select name="gender_type" class="filter-select" onchange="this.form.submit()">
                <option value="">All Products</option>
                <optgroup label="Men">
                    <option value="Men - Shirt" <?php echo $gender_filter == 'Men - Shirt' ? 'selected' : ''; ?>>Men's Shirt</option>
                    <option value="Men - Pants" <?php echo $gender_filter == 'Men - Pants' ? 'selected' : ''; ?>>Men's Pants</option>
                    <option value="Men - Others" <?php echo $gender_filter == 'Men - Others' ? 'selected' : ''; ?>>Men's Others</option>
                </optgroup>
                <optgroup label="Women">
                    <option value="Women - Shirt" <?php echo $gender_filter == 'Women - Shirt' ? 'selected' : ''; ?>>Women's Shirt</option>
                    <option value="Women - Pants" <?php echo $gender_filter == 'Women - Pants' ? 'selected' : ''; ?>>Women's Pants</option>
                    <option value="Women - Others" <?php echo $gender_filter == 'Women - Others' ? 'selected' : ''; ?>>Women's Others</option>
                </optgroup>
            </select>

            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="">Sort By Price</option>
                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Low to High</option>
                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>High to Low</option>
            </select>
        </form>
    </div>

    <div class="products-grid">
        <?php foreach ($products as $product): 
            $img = explode(',', $product['product_image'])[0]; 
        ?>
            <div class="product-card">
                <div class="product-img-wrapper">
                    <button type="button" class="quick-wishlist <?php echo ($product['is_favourite'] > 0) ? 'active' : ''; ?>" 
                            data-id="<?php echo $product['product_id']; ?>">
                        <?php echo ($product['is_favourite'] > 0) ? '♥ Wishlisted' : '♡ Wishlist'; ?>
                    </button>
                    
                    <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                        <img src="images/<?php echo $img; ?>" onerror="this.src='images/default.jpg'">
                    </a>
                </div>

                <div class="product-info">
                    <div class="product-name">
                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </a>
                    </div>
                    <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                    <button type="button" class="add-to-cart-btn" data-id="<?php echo $product['product_id']; ?>">🛒 Add to Cart</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    // --- 修复点：Wishlist 点击处理 ---
    $('.quick-wishlist').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // 防止触发图片跳转详情页

        if(!isLoggedIn) {
            alert('Please login first!');
            window.location.href = 'login.php';
            return;
        }

        var $btn = $(this);
        var pid = $btn.data('id');

        // 发送 AJAX 到后端处理收藏
        $.ajax({
            url: 'wishlist_add.php', // 请确保你有这个处理文件
            method: 'POST',
            data: { product_id: pid },
            success: function(response) {
                // 切换按钮状态和文字
                $btn.toggleClass('active');
                if($btn.hasClass('active')) {
                    $btn.html('♥ Wishlisted');
                } else {
                    $btn.html('♡ Wishlist');
                }
            },
            error: function() {
                alert('Connection error');
            }
        });
    });

    // 购物车处理
    $('.add-to-cart-btn').on('click', function() {
        var pid = $(this).data('id');
        $.post('memberProducts/add.php', { product_id: pid, quantity: 1 }, function() {
            alert('Added to cart!');
        });
    });
});
</script>