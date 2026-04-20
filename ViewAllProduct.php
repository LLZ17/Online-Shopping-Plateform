<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/_head.php';

$current_user_id = $_SESSION['user_id'] ?? null;

// --- 1. 获取所有筛选参数 ---
$cat_key = $_GET['cat_key'] ?? '';         // 对应 MS, MP, MO, WS, WP, WO
$category_main = $_GET['category'] ?? ''; // 对应首页传来的 Men 或 Women
$sort = $_GET['sort'] ?? '';               // 对应 price_low 或 price_high
$search = isset($_GET['query']) ? trim($_GET['query']) : '';

// --- 2. 构建基础 SQL ---
// 使用参数化查询防止 SQL 注入
$sql = "SELECT p.*, 
        (SELECT COUNT(*) FROM favourites f WHERE f.product_id = p.product_id AND f.user_id = ?) as is_favourite 
        FROM products p WHERE 1=1";
$params = [$current_user_id];

// --- 3. 核心过滤逻辑 ---

// A. 如果用户在下拉菜单选了具体分类 (MS, MP, MO 等) -> 优先级最高
if (!empty($cat_key)) {
    $sql .= " AND LEFT(p.product_id, 2) = ?";
    $params[] = strtoupper($cat_key);
} 
// B. 否则，如果用户是从首页点 Men/Women 进来的
elseif (!empty($category_main)) {
    if (strtolower($category_main) == 'men') {
        $sql .= " AND p.product_id LIKE 'M%'";
    } elseif (strtolower($category_main) == 'women') {
        $sql .= " AND p.product_id LIKE 'W%'";
    }
}

// C. 搜索框逻辑
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.keywords LIKE ?)";
    $params[] = "%$search%"; 
    $params[] = "%$search%";
}

// --- 4. 排序逻辑 ---
if ($sort == 'price_low') { 
    $sql .= " ORDER BY p.price ASC"; 
} elseif ($sort == 'price_high') { 
    $sql .= " ORDER BY p.price DESC"; 
} else { 
    // 默认按 ID 倒序，确保新品在前
    $sql .= " ORDER BY p.product_id DESC"; 
}

// 执行查询
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<style>
    .view-all-container { max-width: 1300px; margin: 30px auto; padding: 0 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    
    /* 工具栏：保持 Back, Filter, Sort, Reset 在一行 */
    .toolbar { 
        display: flex; gap: 15px; margin-bottom: 35px; align-items: center; justify-content: center; 
        background: #fdfdfd; padding: 20px; border-radius: 12px; border: 1px solid #eee; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
    }
    
    .btn-back { text-decoration: none; background: #333; color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: bold; transition: 0.3s; }
    .btn-back:hover { background: #000; }

    .btn-reset { 
        text-decoration: none; background: #fff; color: #ff4757; padding: 10px 15px; 
        border-radius: 6px; font-size: 14px; font-weight: bold; border: 1px solid #ff4757; transition: 0.3s; 
    }
    .btn-reset:hover { background: #ff4757; color: #fff; }

    .filter-select { padding: 12px; border-radius: 8px; border: 1px solid #ccc; min-width: 220px; font-size: 15px; outline: none; cursor: pointer; }
    .filter-select:focus { border-color: #000; }

    /* 商品展示网格 */
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
    
    .product-card { 
        background: #fff; border-radius: 15px; overflow: hidden; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: 0.3s; position: relative; border: 1px solid #f0f0f0; 
    }
    .product-card:hover { transform: translateY(-8px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    
    /* 图片区域 */
    .product-img-wrapper { height: 350px; overflow: hidden; background: #f9f9f9; }
    .product-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .product-card:hover img { transform: scale(1.05); }

    /* Wishlist 按钮 */
    .wishlist-btn { 
        position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); border: none; 
        padding: 8px 15px; border-radius: 20px; cursor: pointer; font-weight: bold; z-index: 10; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: 0.3s;
    }
    .wishlist-btn.active { background: #ff4757; color: #fff; }

    /* 商品信息修复文字截断 */
    .product-info { padding: 20px; }
    .product-name { 
        min-height: 54px; line-height: 1.5; margin-bottom: 10px; 
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; 
    }
    .product-name a { color: #222; text-decoration: none; font-weight: bold; font-size: 18px; }
    
    .product-price { font-size: 24px; color: #e60012; font-weight: 800; margin-bottom: 15px; }
    
    .meta-data { display: flex; justify-content: space-between; font-size: 13px; color: #888; margin-bottom: 15px; border-top: 1px solid #eee; padding-top: 10px; }

    /* 购物车按钮 */
    .add-btn { 
        width: 100%; padding: 12px; background: #000; color: #fff; border: none; 
        border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; 
    }
    .add-btn:hover { background: #333; }
</style>

<div class="view-all-container">
    <div class="toolbar">
        <a href="index.php" class="btn-back">⬅ Back</a>
        
        <form method="GET" action="ViewAllProduct.php" style="display: flex; gap: 10px; align-items: center;">
            <?php if(!empty($search)): ?>
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($search); ?>">
            <?php endif; ?>

            <?php if(!empty($category_main) && empty($cat_key)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_main); ?>">
            <?php endif; ?>

            <select name="cat_key" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <optgroup label="MEN">
                    <option value="MS" <?php echo $cat_key == 'MS' ? 'selected' : ''; ?>>Men's Shirt (MS)</option>
                    <option value="MP" <?php echo $cat_key == 'MP' ? 'selected' : ''; ?>>Men's Pants (MP)</option>
                    <option value="MO" <?php echo $cat_key == 'MO' ? 'selected' : ''; ?>>Men's Others (MO)</option>
                </optgroup>
                <optgroup label="WOMEN">
                    <option value="WS" <?php echo $cat_key == 'WS' ? 'selected' : ''; ?>>Women's Shirt (WS)</option>
                    <option value="WP" <?php echo $cat_key == 'WP' ? 'selected' : ''; ?>>Women's Pants (WP)</option>
                    <option value="WO" <?php echo $cat_key == 'WO' ? 'selected' : ''; ?>>Women's Others (WO)</option>
                </optgroup>
            </select>

            <select name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="">Sort By Price</option>
                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>

            <?php if(!empty($cat_key) || !empty($sort) || !empty($category_main) || !empty($search)): ?>
                <a href="ViewAllProduct.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="products-grid">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): 
                $images = explode(',', $product['product_image']);
                $display_img = trim($images[0]);
            ?>
                <div class="product-card">
                    <button type="button" class="wishlist-btn <?php echo ($product['is_favourite'] > 0) ? 'active' : ''; ?>" 
                            data-id="<?php echo $product['product_id']; ?>">
                        <?php echo ($product['is_favourite'] > 0) ? '♥ Wishlisted' : '♡ Wishlist'; ?>
                    </button>

                    <div class="product-img-wrapper">
                        <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                            <img src="images/<?php echo htmlspecialchars($display_img); ?>" onerror="this.src='images/default.jpg'">
                        </a>
                    </div>

                    <div class="product-info">
                        <div class="product-name">
                            <a href="memberProducts/detail.php?id=<?php echo $product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </div>
                        
                        <div class="product-price">RM <?php echo number_format($product['price'], 2); ?></div>
                        
                        <div class="meta-data">
                            <span>Stock: <?php echo $product['stock']; ?></span>
                            <span>ID: <strong><?php echo $product['product_id']; ?></strong></span>
                        </div>

                        <button type="button" class="add-btn" data-id="<?php echo $product['product_id']; ?>">🛒 Add to Cart</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                <h2 style="color: #ccc;">No products found in this category.</h2>
                <a href="ViewAllProduct.php" style="color: #333;">Show all products</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    // Wishlist 异步切换
    $('.wishlist-btn').on('click', function(e) {
        e.preventDefault();
        if(!isLoggedIn) {
            alert('Please login to use Wishlist!');
            return;
        }
        var $btn = $(this);
        var pid = $btn.data('id');
        
        $.post('wishlist_add.php', { product_id: pid }, function(response) {
            $btn.toggleClass('active');
            if($btn.hasClass('active')) {
                $btn.html('♥ Wishlisted');
            } else {
                $btn.html('♡ Wishlist');
            }
        });
    });

    // 购物车异步添加
    $('.add-btn').on('click', function() {
        var pid = $(this).data('id');
        $.post('memberProducts/add.php', { product_id: pid, quantity: 1 }, function(response) {
            alert('Product added to cart!');
        });
    });
});
</script>

<?php require_once 'includes/_foot.php'; ?>