<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// 1. 从 URL 获取产品 ID 并查询数据库
$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { exit("Product Not Found!"); }

// --- 核心变量提取 (严格对应你的 SQL 字段) ---
// 图片数组
$all_images = !empty($product['product_image']) ? array_map('trim', explode(',', $product['product_image'])) : ['default.jpg'];
// 颜色和尺寸
$colours = !empty($product['colour']) ? array_map('trim', explode(',', $product['colour'])) : [];
$sizes = !empty($product['size']) ? array_map('trim', explode(',', $product['size'])) : [];
// 评分与销量 (直接引用 Product Table 字段)
$sql_rating = (float)($product['rating'] ?? 0); 
$sql_sales = $product['sales_count'] ?? 0;
$stock = (int)$product['stock'];
$reward_points = floor($product['price']); 

// 2. 获取评论数据 (用于评论列表)
$stmt_rev = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ?");
$stmt_rev->execute([$product_id]);
$reviews = $stmt_rev->fetchAll();
$review_count = count($reviews);

require_once __DIR__ . '/../includes/_head.php';
?>

<style>
    :root { --shopee-red: #e60012; --star-yellow: #ffc107; --bg-gray: #f5f5f5; }
    body { background-color: var(--bg-gray); font-family: -apple-system, Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; }
    .detail-container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }

    /* 主卡片：白色背景容器 */
    .main-card { display: flex; gap: 40px; background: white; padding: 25px; border-radius: 2px; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }

    /* --- 图片浏览器逻辑 --- */
    .image-viewer { width: 450px; flex-shrink: 0; position: relative; }
    .main-img-box { width: 100%; aspect-ratio: 1/1; position: relative; border: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .main-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; }

    /* 左右切换按钮 (针对 image_60162c.png 和 image_986574.png) */
    .nav-arrow { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.2); color: white; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 20px; z-index: 10; display: flex; align-items: center; justify-content: center; }
    .nav-arrow:hover { background: rgba(0,0,0,0.4); }
    .arrow-left { left: 10px; }
    .arrow-right { right: 10px; }

    /* 缩略图列表 (针对图片下方的预览) */
    .thumb-tray { display: flex; gap: 8px; margin-top: 15px; }
    .thumb-unit { width: 80px; height: 80px; border: 2px solid transparent; cursor: pointer; transition: 0.2s; }
    .thumb-unit.active { border-color: var(--shopee-red); }
    .thumb-unit img { width: 100%; height: 100%; object-fit: cover; }

    /* --- 右侧产品信息 --- */
    .info-pane { flex: 1; }
    .p-title { font-size: 22px; font-weight: 500; margin-bottom: 12px; }
    
    /* 评分显示 - 严格对齐 image_978036.png */
    .rating-header { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; font-size: 16px; }
    .r-score { color: var(--shopee-red); border-bottom: 1px solid var(--shopee-red); font-weight: 500; }
    .r-stars { color: var(--star-yellow); }
    .divider { width: 1px; height: 14px; background: #dbdbdb; }

    .price-strip { background: #fafafa; padding: 15px 20px; color: var(--shopee-red); font-size: 32px; font-weight: 500; margin: 20px 0; }

    /* 积分卡片 - 严格对齐 image_512588.png */
    .reward-banner { background: #fffcf5; border: 1px solid #f9eacc; padding: 12px 15px; border-radius: 4px; display: flex; align-items: center; gap: 10px; color: #ff9800; font-size: 14px; margin-bottom: 25px; }

    .attr-row { display: flex; margin-bottom: 20px; align-items: center; }
    .attr-label { width: 110px; color: #757575; font-size: 14px; }
    
    .btn-opt { border: 1px solid rgba(0,0,0,0.09); background: white; padding: 8px 15px; cursor: pointer; border-radius: 2px; font-size: 14px; min-width: 60px; }
    .btn-opt.active { border-color: var(--shopee-red); color: var(--shopee-red); }

    /* 数量加减 */
    .qty-wrap { display: flex; border: 1px solid rgba(0,0,0,0.09); }
    .qty-ctrl { width: 32px; height: 32px; background: white; border: none; cursor: pointer; font-size: 18px; }
    .qty-val { width: 50px; text-align: center; border: none; border-left: 1px solid #eee; border-right: 1px solid #eee; outline: none; }

    .action-row { display: flex; gap: 15px; margin-top: 35px; }
    .btn-cart { flex: 1; height: 48px; background: rgba(208,1,27,.08); color: var(--shopee-red); border: 1px solid var(--shopee-red); cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-buy { flex: 1; height: 48px; background: var(--shopee-red); color: white; border: none; cursor: pointer; font-size: 16px; }

    /* --- 下方详情与评价 --- */
    .extra-section { background: white; margin-top: 20px; padding: 25px; }
    .section-title { background: #fafafa; padding: 15px; text-transform: uppercase; font-size: 18px; font-weight: 400; margin-bottom: 15px; }
    .p-describe { padding: 0 15px 30px 15px; line-height: 1.8; color: #333; font-size: 15px; white-space: pre-wrap; }

    /* 评分概览 - 严格对齐 image_977fff.png */
    .ov-card { background: #fffbf8; border: 1px solid #f9ede5; padding: 30px; display: flex; gap: 50px; align-items: center; margin-bottom: 25px; }
    .ov-big-num { color: var(--shopee-red); font-size: 30px; }
    .tag-filter { display: flex; flex-wrap: wrap; gap: 10px; }
    .filter-btn { padding: 6px 15px; background: white; border: 1px solid rgba(0,0,0,.09); border-radius: 2px; font-size: 14px; cursor: pointer; }
    .filter-btn.active { border-color: var(--shopee-red); color: var(--shopee-red); }
</style>

<div class="detail-container">
    <div class="main-card">
        <div class="image-viewer">
            <button class="nav-arrow arrow-left" onclick="slideImg(-1)">&#10094;</button>
            <div class="main-img-box">
                <img src="../images/<?php echo $all_images[0]; ?>" id="mainTarget">
            </div>
            <button class="nav-arrow arrow-right" onclick="slideImg(1)">&#10095;</button>

            <div class="thumb-tray">
                <?php foreach($all_images as $i => $img): ?>
                    <div class="thumb-unit <?php echo $i==0?'active':''; ?>" onclick="switchTab(<?php echo $i; ?>)">
                        <img src="../images/<?php echo $img; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-pane">
            <h1 class="p-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="rating-header">
                <span class="r-score"><?php echo number_format($sql_rating, 1); ?></span>
                <div class="r-stars">
                    <?php for($i=1; $i<=5; $i++) echo ($i <= round($sql_rating) ? '★' : '☆'); ?>
                </div>
                <div class="divider"></div>
                <div><u><?php echo $review_count; ?></u> Reviews</div>
                <div class="divider"></div>
                <div><?php echo $sql_sales; ?> Sold</div>
            </div>

            <div class="price-strip">RM <?php echo number_format($product['price'], 2); ?></div>

            <div class="reward-banner">
                <i class="fas fa-gift"></i>
                <span>Earn <strong><?php echo $reward_points; ?></strong> reward points with this purchase</span>
            </div>

            <div class="attr-row">
                <div class="attr-label">Stock:</div>
                <div style="font-size:14px;"><?php echo $stock; ?> pieces available</div>
            </div>

            <?php if(!empty($colours)): ?>
            <div class="attr-row">
                <div class="attr-label">Colour:</div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <?php foreach($colours as $idx => $c): ?>
                        <button class="btn-opt col-trigger" onclick="pickColour(this, <?php echo $idx; ?>)"><?php echo $c; ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($sizes)): ?>
            <div class="attr-row">
                <div class="attr-label">Size:</div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <?php foreach($sizes as $s): ?>
                        <button class="btn-opt size-trigger" onclick="pickSize(this)"><?php echo $s; ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="attr-row">
                <div class="attr-label">Quantity:</div>
                <div class="qty-wrap">
                    <button class="qty-ctrl" onclick="stepQty(-1)">-</button>
                    <input type="number" id="qtyField" class="qty-val" value="1" readonly>
                    <button class="qty-ctrl" onclick="stepQty(1)">+</button>
                </div>
            </div>

            <div class="action-row">
                <button class="btn-cart"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                <button class="btn-buy">Buy Now</button>
            </div>
        </div>
    </div>

    <div class="extra-section">
        <div class="section-title">Product Description</div>
        <div class="p-describe">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </div>

        <div class="section-title">Product Ratings</div>
        
        <div class="ov-card">
            <div style="text-align:center;">
                <div class="ov-big-num"><?php echo number_format($sql_rating, 1); ?> <span style="font-size:18px;">out of 5</span></div>
                <div style="color:var(--star-yellow); font-size:22px;">
                    <?php for($i=1; $i<=5; $i++) echo ($i <= round($sql_rating) ? '★' : '☆'); ?>
                </div>
            </div>
            <div class="tag-filter">
                <button class="filter-btn active">All</button>
                <button class="filter-btn">5 Star (<?php echo count(array_filter($reviews, fn($r)=>$r['rating']==5)); ?>)</button>
                <button class="filter-btn">4 Star (<?php echo count(array_filter($reviews, fn($r)=>$r['rating']==4)); ?>)</button>
                <button class="filter-btn">With Comments</button>
            </div>
        </div>

        <div class="rev-list">
            <?php if($review_count > 0): ?>
                <?php foreach($reviews as $r): ?>
                    <div style="display:flex; gap:15px; padding:20px 0; border-bottom:1px solid #f2f2f2;">
                        <div style="width:40px; height:40px; border-radius:50%; background:#eee; display:flex; align-items:center; justify-content:center;"><i class="fas fa-user" style="color:#ccc;"></i></div>
                        <div>
                            <div style="font-size:12px;"><?php echo htmlspecialchars($r['user_name'] ?? 'Buyer'); ?></div>
                            <div style="color:var(--star-yellow); font-size:11px; margin-top:4px;">
                                <?php for($k=1; $k<=5; $k++) echo ($k<=$r['rating']?'★':'☆'); ?>
                            </div>
                            <div style="margin-top:10px; font-size:14px; color:#333;"><?php echo htmlspecialchars($r['comment']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:50px; color:#999;">No reviews yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // JS 数据初始化
    const imgPool = <?php echo json_encode($all_images); ?>;
    let activeIdx = 0;
    const maxStock = <?php echo $stock; ?>;

    const mainTarget = document.getElementById('mainTarget');
    const thumbItems = document.querySelectorAll('.thumb-unit');

    // 1. 核心切换逻辑 (由左右键、预览图、颜色键共享)
    function switchTab(index) {
        if(index < 0 || index >= imgPool.length) return;
        activeIdx = index;
        
        // 更换主图
        mainTarget.src = "../images/" + imgPool[activeIdx];
        
        // 更新预览图高亮
        thumbItems.forEach(t => t.classList.remove('active'));
        if(thumbItems[activeIdx]) thumbItems[activeIdx].classList.add('active');
    }

    // 2. 左右切换按键逻辑 (解决《》是摆设的问题)
    function slideImg(delta) {
        let next = activeIdx + delta;
        if(next < 0) next = imgPool.length - 1;
        if(next >= imgPool.length) next = 0;
        switchTab(next);
    }

    // 3. 颜色选择逻辑 (解决颜色按了没反应的问题)
    function pickColour(btn, idx) {
        // 样式高亮
        document.querySelectorAll('.col-trigger').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        // 联动图片：点击颜色自动切到对应的图片索引
        switchTab(idx);
    }

    function pickSize(btn) {
        document.querySelectorAll('.size-trigger').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function stepQty(n) {
        const field = document.getElementById('qtyField');
        let val = parseInt(field.value);
        if(val + n >= 1 && val + n <= maxStock) field.value = val + n;
    }
</script>