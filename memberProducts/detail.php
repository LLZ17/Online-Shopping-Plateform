<?php
session_start();
// 包含数据库连接
require_once __DIR__ . '/../includes/db.php';

// 1. 获取产品 ID 并从数据库抓取数据
$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found! Please check your product ID.");
}

// 2. 解析产品规格
$all_images = !empty($product['product_image']) ? array_map('trim', explode(',', $product['product_image'])) : ['default.jpg'];
$colours = !empty($product['colour']) ? array_map('trim', explode(',', $product['colour'])) : [];
$sizes = !empty($product['size']) ? array_map('trim', explode(',', $product['size'])) : [];

// 3. 核心显示字段与库存逻辑
$sql_rating = (float)($product['rating'] ?? 0); 
$stock = (int)$product['stock'];
$is_out_of_stock = ($stock <= 0); // 判断是否没货
$description = !empty($product['description']) ? $product['description'] : "No description provided.";
$reward_points = floor($product['price'] * 1); 

// 4. 获取评论列表
$stmt_rev = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
$stmt_rev->execute([$product_id]);
$reviews = $stmt_rev->fetchAll();
$review_count = count($reviews);

require_once __DIR__ . '/../includes/_head.php';
?>

<style>
    :root { --shopee-red: #e60012; --star-yellow: #ffc107; --bg-gray: #f5f5f5; --orange-tint: #fffbf8; }
    body { background-color: var(--bg-gray); font-family: -apple-system, sans-serif; }
    .main-wrap { max-width: 1200px; margin: 20px auto; padding: 0 15px; }

    /* 操作提示条 */
    .msg-alert { background: #e6fffa; color: #2c7a7b; padding: 12px; border-radius: 4px; margin-bottom: 20px; text-align: center; border: 1px solid #b2f5ea; }

    /* 产品展示卡片 */
    .product-card { display: flex; gap: 40px; background: white; padding: 25px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
    .viewer-side { width: 450px; position: relative; }
    .main-img-box { width: 100%; aspect-ratio: 1/1; border: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: center; position: relative; }
    .main-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
    
    .nav-arrow { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.1); border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; color: #fff; z-index: 5; }
    .nav-arrow:hover { background: rgba(0,0,0,0.3); }
    .arrow-l { left: 10px; } .arrow-r { right: 10px; }

    .thumbs { display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; }
    .thumb-item { width: 80px; height: 80px; border: 2px solid transparent; cursor: pointer; flex-shrink: 0; }
    .thumb-item.active { border-color: var(--shopee-red); }
    .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

    /* 右侧信息区 */
    .info-side { flex: 1; }
    .p-title { font-size: 22px; font-weight: 500; margin-bottom: 12px; }
    .stats-row { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; font-size: 14px; }
    .price-box { background: #fafafa; padding: 15px 20px; color: var(--shopee-red); font-size: 30px; font-weight: 600; margin-bottom: 20px; }
    
    .points-card { background: #fffcf5; border: 1px solid #f9eacc; padding: 12px; border-radius: 4px; color: #ff9800; display: flex; align-items: center; gap: 10px; font-size: 14px; margin-bottom: 25px; }

    .spec-btn { border: 1px solid rgba(0,0,0,0.09); background: #fff; padding: 8px 15px; cursor: pointer; min-width: 60px; }
    .spec-btn.active { border-color: var(--shopee-red); color: var(--shopee-red); }

    /* 按钮组样式 */
    .action-group { display: flex; gap: 15px; margin-top: 30px; }
    .btn-cart { flex: 1; height: 48px; background: rgba(208,1,27,0.08); color: var(--shopee-red); border: 1px solid var(--shopee-red); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-buy { flex: 1; height: 48px; background: var(--shopee-red); color: white; border: none; cursor: pointer; font-size: 16px; }
    .btn-wishlist { flex: 1; height: 48px; background: #fff; color: #555; border: 1px solid #ddd; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; }
    .btn-wishlist:hover { border-color: var(--shopee-red); color: var(--shopee-red); }
    .btn-disabled { flex: 1; height: 48px; background: #eee; color: #bbb; border: 1px solid #ddd; cursor: not-allowed; font-size: 16px; }

    .content-section { background: white; margin-top: 20px; padding: 25px; }
    .sect-title { background: #fafafa; padding: 12px 15px; font-size: 18px; margin-bottom: 20px; }
</style>

<div class="main-wrap">
    <?php if (isset($_GET['wishlist'])): ?>
        <div class="msg-alert">
            <?php echo ($_GET['wishlist'] == 'added') ? "❤️ Added to Wishlist!" : "💔 Removed from Wishlist!"; ?>
        </div>
    <?php endif; ?>

    <div class="product-card">
        <div class="viewer-side">
            <button class="nav-arrow arrow-l" onclick="changeSlide(-1)">&#10094;</button>
            <div class="main-img-box">
                <img src="../images/<?php echo $all_images[0]; ?>" id="targetDisplay">
            </div>
            <button class="nav-arrow arrow-r" onclick="changeSlide(1)">&#10095;</button>

            <div class="thumbs">
                <?php foreach($all_images as $i => $img): ?>
                    <div class="thumb-item <?php echo $i==0?'active':''; ?>" onclick="setSlide(<?php echo $i; ?>)">
                        <img src="../images/<?php echo $img; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-side">
            <h1 class="p-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="stats-row">
                <span style="color:var(--star-yellow);"><?php echo number_format($sql_rating, 1); ?> ★★★★★</span>
                <span style="color:#757575;">| <?php echo $review_count; ?> Reviews | <?php echo $product['sales_count'] ?? 0; ?> Sold</span>
            </div>

            <div class="price-box">RM <?php echo number_format($product['price'], 2); ?></div>

            <div class="points-card">
                <i class="fas fa-coins"></i>
                <span>Earn <strong><?php echo $reward_points; ?></strong> reward points</span>
            </div>

            <form method="POST" id="mainForm">
                <input type="hidden" name="p_id" value="<?php echo $product_id; ?>">
                
                <?php if(!empty($colours)): ?>
                <div style="margin-bottom:20px;">
                    <label style="color:#757575; display:block; margin-bottom:10px;">Colour:</label>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <?php foreach($colours as $idx => $c): ?>
                            <button type="button" class="spec-btn col-opt" onclick="pickColor(this, <?php echo $idx; ?>, '<?php echo $c; ?>')"><?php echo $c; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="sel_color" id="inputColor">
                </div>
                <?php endif; ?>

                <?php if(!empty($sizes)): ?>
                <div style="margin-bottom:20px;">
                    <label style="color:#757575; display:block; margin-bottom:10px;">Size:</label>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <?php foreach($sizes as $s): ?>
                            <button type="button" class="spec-btn size-opt" onclick="pickSize(this, '<?php echo $s; ?>')"><?php echo $s; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="sel_size" id="inputSize">
                </div>
                <?php endif; ?>

                <div style="margin-bottom:25px; display:flex; align-items:center; gap:15px;">
                    <span style="color:#757575;">Quantity:</span>
                    <div style="display:flex; border:1px solid #ddd;">
                        <button type="button" onclick="adjQty(-1)" style="width:32px; background:#fff; border:none; cursor:pointer;">-</button>
                        <input type="number" name="qty" id="qtyVal" value="1" readonly style="width:50px; text-align:center; border:none; border-left:1px solid #ddd; border-right:1px solid #ddd;">
                        <button type="button" onclick="adjQty(1)" style="width:32px; background:#fff; border:none; cursor:pointer;">+</button>
                    </div>
                    <span style="color:<?php echo $is_out_of_stock ? 'red' : '#757575'; ?>; font-size:14px;">
                        <?php echo $is_out_of_stock ? 'Out of Stock' : $stock . ' pieces available'; ?>
                    </span>
                </div>

                <div class="action-group">
                    <?php if($is_out_of_stock): ?>
                        <button type="submit" name="btn_action" value="wishlist" 
                                onclick="this.form.action='wishlist_toggle.php';" 
                                class="btn-wishlist">
                            <i class="fas fa-heart"></i> Add To Wishlist
                        </button>
                        <button type="button" class="btn-disabled" disabled>Sold Out</button>
                    <?php else: ?>
                        <button type="submit" name="btn_action" value="add" 
                                onclick="this.form.action='add.php';" 
                                class="btn-cart">
                            <i class="fas fa-cart-plus"></i> Add To Cart
                        </button>
                        <button type="submit" name="btn_action" value="buy" 
                                onclick="this.form.action='add.php';" 
                                class="btn-buy">Buy Now</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="content-section">
        <div class="sect-title">PRODUCT DESCRIPTION</div>
        <div style="padding:0 15px; line-height:1.8; white-space: pre-wrap;"><?php echo htmlspecialchars($description); ?></div>
    </div>
</div>

<script>
    const images = <?php echo json_encode($all_images); ?>;
    let currentIdx = 0;

    function setSlide(i) {
        currentIdx = i;
        document.getElementById('targetDisplay').src = "../images/" + images[currentIdx];
        document.querySelectorAll('.thumb-item').forEach((box, index) => {
            box.classList.toggle('active', index === currentIdx);
        });
    }

    function changeSlide(n) {
        currentIdx = (currentIdx + n + images.length) % images.length;
        setSlide(currentIdx);
    }

    function pickColor(btn, i, name) {
        document.querySelectorAll('.col-opt').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('inputColor').value = name;
        setSlide(i);
    }

    function pickSize(btn, name) {
        document.querySelectorAll('.size-opt').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('inputSize').value = name;
    }

    function adjQty(n) {
        const input = document.getElementById('qtyVal');
        let val = parseInt(input.value) + n;
        if(val >= 1 && val <= <?php echo $stock; ?>) input.value = val;
    }

    document.getElementById('mainForm').onsubmit = function() {
        const action = document.activeElement.value;

        // 如果点击的是 wishlist 按钮，不需要校验规格
        if (action === 'wishlist') return true;

        // 校验逻辑
        if(<?php echo count($colours); ?> > 0 && !document.getElementById('inputColor').value) {
            alert("Please select a Color!"); return false;
        }
        if(<?php echo count($sizes); ?> > 0 && !document.getElementById('inputSize').value) {
            alert("Please select a Size!"); return false;
        }
        return true;
    };
</script>