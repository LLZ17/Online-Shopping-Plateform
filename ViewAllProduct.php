<?php
session_start();
// 1. 数据库连接与头部引用
require_once 'includes/db.php';
require_once 'includes/_head.php';

$current_user_id = $_SESSION['user_id'] ?? null;

// --- 2. 预加载：获取所有分类以填充下拉菜单 ---
// 排序逻辑：先按性别（Men/Women），再按分类名称
$cat_query = "SELECT * FROM categories ORDER BY category_gender DESC, category_name ASC";
$cat_stmt = $pdo->prepare($cat_query);
$cat_stmt->execute();
$all_categories = $cat_stmt->fetchAll();

// --- 3. 获取筛选与排序参数 ---
$selected_cat_id = $_GET['cat_id'] ?? ''; 
$sort = $_GET['sort'] ?? '';
$search = isset($_GET['query']) ? trim($_GET['query']) : '';

// --- 4. 构建主查询 SQL (集成 Favourites 收藏检测) ---
$sql = "SELECT p.*, c.category_name, c.category_gender,
        (SELECT COUNT(*) FROM favourites f WHERE f.product_id = p.product_id AND f.user_id = ?) as is_favourite 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE 1=1";
$params = [$current_user_id];

// 分类 ID 精确筛选
if (!empty($selected_cat_id)) {
    $sql .= " AND p.category_id = ?";
    $params[] = $selected_cat_id;
}

// 关键词搜索逻辑
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

// 排序逻辑
if ($sort == 'price_low') { 
    $sql .= " ORDER BY p.price ASC"; 
} elseif ($sort == 'price_high') { 
    $sql .= " ORDER BY p.price DESC"; 
} else { 
    $sql .= " ORDER BY p.created_at DESC"; // 默认显示最新上架
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<link rel="stylesheet" href="css/app.css">
<style>
    /* 页面整体容器 */
    .view-all-container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }

    /* 顶部工具栏：返回按钮 + 筛选 */
    .toolbar { 
        display: flex; gap: 15px; margin-bottom: 40px; align-items: center; justify-content: center; 
        background: #fdfdfd; padding: 20px; border-radius: 12px; border: 1px solid #eee;
    }
    .btn-back-home { 
        text-decoration: none; background: #333; color: white; padding: 12px 20px; 
        border-radius: 8px; font-weight: bold; transition: 0.3s; display: flex; align-items: center;
    }
    .btn-back-home:hover { background: #000; transform: translateX(-5px); }

    .filter-form { display: flex; gap: 10px; }
    .filter-select { 
        padding: 12px; border: 1px solid #ddd; border-radius: 8px; 
        min-width: 250px; font-size: 15px; cursor: pointer;
    }

    /* 修复字体截断 (解决 image_f862a3.png 问题) */
    .product-name { 
        min-height: 56px; /* 强制预留两行高度 */
        line-height: 1.4; 
        margin: 15px 0 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-name a { color: #222; text-decoration: none; font-weight: 700; font-size: 18px; }
    .product-name a:hover { color: #e60012; }

    /* 图片与 Wishlist 布局 */
    .product-img-wrapper { position: relative; height: 360px; border-radius: 12px; overflow: hidden; background: #f5f5f5; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .product-card:hover img { transform: scale(1.08); }

    .wishlist-btn {
        position: absolute; top: 15px; right: 15px;
        background: rgba(255, 255, 255, 0.95); border: none;
        padding: 8px 15px; border-radius: 20px;
        cursor: pointer; font-weight: bold; font-size: 13px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 10; transition: 0.3s;
    }
    .wishlist-btn.active { background: #e60012; color: white; }

    /* 价格与信息栏 */
    .product-price { font-size: 26px; color: #e60012; font-weight: 900; margin-bottom: 8px; }
    .info-row { display: flex; justify-content: space-between; font-size: 14px; color: #666; margin-bottom: 12px; }
    .points-tag { background: #fff4e5; color: #ff9800; padding: 4px 10px; border-radius: 6px; font-weight: bold; }
</style>

<div class="view-all-container">
    <div class="toolbar">
        <a href="index.php" class="btn-back-home">⬅ Back to Home</a>

        <form method="GET" action="ViewAllProduct.php" class="filter-form">
            <?php if(!empty($search)): ?>
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($search); ?>">
            <?php endif; ?>

            <select name="cat_id" class="filter-select" onchange="this.form.submit()">
                <option value="">Select Categories (All)</option>
                <?php 
                $last_gender = '';
                foreach ($all_categories as $cat): 
                    if ($last_gender !== $cat['category_gender']):
                        if ($last_gender !== '') echo '</optgroup>';
                        $last_gender = $cat['category_gender'];
                        echo '<optgroup label="' . strtoupper($last_gender) . ' Section">';
                    endif;
                ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo ($selected_cat_id == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_gender'] . " - " . $cat['category_name']); ?>
                    </option>
                <?php endforeach; ?>
                </optgroup>
            </select>

            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="">Sort By Price</option>
                <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </form>
    </div>

    <div class="products-grid">
        <?php if (empty($products)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px; color: #999;">
                <h3>No products found for the selected criteria.</h3>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): 
                $img_list = preg_split('/[\s,]+/', $product['product_image']);
                $display_img = !empty($img_list[0]) ? $img_list[0] : 'default.jpg';
            ?>
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <button type="button" class="wishlist-btn <?php echo ($product['is_favourite'] > 0) ? 'active' : ''; ?>" 
                                data-id="<?php echo $product['product_id']; ?>">
                            <?php echo ($product['is_favourite'] > 0) ? '♥ Wishlisted' : '♡ Wishlist'; ?>
                        </button>

                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <img src="images/<?php echo htmlspecialchars($display_img); ?>" 
                                 onerror="this.src='images/default.jpg'">
                        </a>
                    </div>

                    <div class="product-info">
                        <div class="product-name">
                            <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </div>

                        <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>

                        <div class="info-row">
                            <span>Stock: <strong><?php echo $product['stock']; ?></strong></span>
                            <span>Sold: <strong><?php echo $product['sales_count'] ?? 0; ?></strong></span>
                        </div>

                        <div class="info-row" style="margin-bottom: 20px;">
                            <span class="points-tag">🎁 +<?php echo floor($product['price']); ?> pts</span>
                            <span style="color: <?php echo $product['stock'] > 0 ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;">
                                <?php echo $product['stock'] > 0 ? '✓ In Stock' : '✗ Out of Stock'; ?>
                            </span>
                        </div>

                        <?php if ($product['stock'] > 0): ?>
                            <button type="button" class="add-to-cart-btn" data-id="<?php echo $product['product_id']; ?>">
                                🛒 Add to Cart
                            </button>
                        <?php else: ?>
                            <button type="button" class="add-to-cart-btn" disabled style="background:#ccc;">Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    $(document).ready(function() {
        // --- 1. Wishlist 收藏逻辑 ---
        $('.wishlist-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // 关键：防止点击收藏时触发图片的详情页跳转

            if(!isLoggedIn) {
                alert('Please login to manage your wishlist.');
                window.location.href = 'login.php';
                return;
            }

            var $btn = $(this);
            var pid = $btn.data('id');

            $.post('wishlist_add.php', { product_id: pid }, function(response) {
                // 切换按钮样式和文字
                $btn.toggleClass('active');
                if($btn.hasClass('active')) {
                    $btn.html('♥ Wishlisted');
                } else {
                    $btn.html('♡ Wishlist');
                }
            }).fail(function() {
                alert('Error connecting to server.');
            });
        });

        // --- 2. Add to Cart 购物车逻辑 ---
        $('.add-to-cart-btn').on('click', function() {
            var pid = $(this).data('id');
            var $btn = $(this);
            
            $btn.prop('disabled', true).html('Adding...');

            $.post('memberProducts/add.php', { product_id: pid, quantity: 1 }, function(res) {
                alert('Success! Product added to cart.');
                $btn.prop('disabled', false).html('🛒 Add to Cart');
            }).fail(function() {
                alert('Failed to add product.');
                $btn.prop('disabled', false).html('🛒 Add to Cart');
            });
        });
    });
</script>

<?php require_once 'includes/_foot.php'; ?>