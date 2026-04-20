<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// 1. 数据抓取与安全处理
$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { exit("Product Not Found!"); }

// 2. 解析规格
$all_images = !empty($product['product_image']) ? array_map('trim', explode(',', $product['product_image'])) : ['default.jpg'];
$colours = !empty($product['colour']) ? array_map('trim', explode(',', $product['colour'])) : [];
$sizes = !empty($product['size']) ? array_map('trim', explode(',', $product['size'])) : [];

// 3. 抓取该产品的真实评论
$stmt_rev = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
$stmt_rev->execute([$product_id]);
$reviews = $stmt_rev->fetchAll();
$review_count = count($reviews);

// 4. 计算评分分布 (用于进度条展示)
$rating_data = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
$sum_rating = 0;
foreach($reviews as $r) {
    $rating_data[$r['rating']]++;
    $sum_rating += $r['rating'];
}
$avg_rating = ($review_count > 0) ? round($sum_rating / $review_count, 1) : 0;

// 检查当前登录用户是否已评价过
$user_id = $_SESSION['user_id'] ?? null;
$has_reviewed = false;
if($user_id) {
    $check_stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
    $check_stmt->execute([$product_id, $user_id]);
    $has_reviewed = $check_stmt->fetchColumn();
}

require_once __DIR__ . '/../includes/_head.php';
?>

<style>
    /* 核心 UI 规范 - 确保各部分不跑位 */
    :root { --main-red: #e60012; --bg: #f5f5f5; --border: rgba(0,0,0,0.09); }
    body { background: var(--bg); font-family: sans-serif; }
    .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
    
    /* 产品主区域 */
    .product-main { background: #fff; display: flex; padding: 25px; gap: 30px; border-radius: 2px; }
    .gallery { width: 450px; }
    .main-img { width: 100%; aspect-ratio: 1/1; border: 1px solid #f0f0f0; object-fit: contain; }
    
    /* 价格与积分卡片 */
    .price-tag { font-size: 30px; color: var(--main-red); background: #fafafa; padding: 15px; margin: 20px 0; font-weight: 600; }
    .reward-banner { background: #fffcf5; border: 1px solid #f9eacc; color: #ff9800; padding: 10px; display: flex; align-items: center; gap: 10px; font-size: 14px; margin-bottom: 15px; }

    /* 选项按钮选中态 */
    .opt-btn { border: 1px solid var(--border); background: #fff; padding: 8px 15px; cursor: pointer; }
    .opt-btn.active { border-color: var(--main-red); color: var(--main-red); }

    /* 评论区样式 */
    .section-card { background: #fff; margin-top: 20px; padding: 25px; }
    .review-summary { display: flex; align-items: center; background: #fffbf8; border: 1px solid #f9ede5; padding: 30px; }
    .rating-bars { flex: 1; margin-left: 40px; }
    .bar-row { display: flex; align-items: center; gap: 10px; height: 20px; margin-bottom: 5px; }
    .bar-outer { flex: 1; height: 8px; background: #eee; border-radius: 4px; overflow: hidden; }
    .bar-inner { height: 100%; background: #ffc107; transition: width 0.3s; }
</style>

<div class="container">
    <div class="product-main">
        <div class="gallery">
            <img src="../images/<?php echo $all_images[0]; ?>" class="main-img" id="mainDisplay">
            <div style="display:flex; gap:10px; margin-top:10px;">
                <?php foreach($all_images as $img): ?>
                    <img src="../images/<?php echo $img; ?>" style="width:80px; height:80px; cursor:pointer; border:1px solid #ddd;" onclick="document.getElementById('mainDisplay').src=this.src">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="details" style="flex:1;">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <div style="margin:10px 0; color:#666;">
                <span style="color:#ffc107;">★ <?php echo $avg_rating; ?></span> | <?php echo $review_count; ?> Reviews | <?php echo $product['sales_count'] ?? 0; ?> Sold
            </div>
            
            <div class="price-tag">RM <?php echo number_format($product['price'], 2); ?></div>

            <div class="reward-banner">
                <i class="fas fa-gift"></i>
                Earn <?php echo floor($product['price']); ?> reward points with this purchase
            </div>

            <form action="cart.php" method="POST">
                <input type="hidden" name="pid" value="<?php echo $product_id; ?>">
                <div style="margin-bottom:15px;">
                    <p style="color:#757575;">Colour:</p>
                    <?php foreach($colours as $c): ?>
                        <button type="button" class="opt-btn" onclick="selectOpt(this, 'color', '<?php echo $c; ?>')"><?php echo $c; ?></button>
                    <?php endforeach; ?>
                    <input type="hidden" name="selected_color" id="input_color" required>
                </div>

                <div class="action-btns" style="display:flex; gap:10px; margin-top:30px;">
                    <button type="submit" name="add_to_cart" style="flex:1; height:48px; border:1px solid var(--main-red); color:var(--main-red); background:#fff;">Add to Cart</button>
                    <button type="submit" name="buy_now" style="flex:1; background:var(--main-red); color:#fff; border:none;">Buy Now</button>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card">
        <h3 style="background:#fafafa; padding:10px;">PRODUCT DESCRIPTION</h3>
        <div style="padding:15px; white-space: pre-wrap;"><?php echo htmlspecialchars($product['description']); ?></div>
    </div>

    <div class="section-card">
        <h3 style="background:#fafafa; padding:10px;">CUSTOMER REVIEWS</h3>
        
        <div class="review-summary">
            <div style="text-align:center;">
                <div style="font-size:32px; color:var(--main-red); font-weight:600;"><?php echo $avg_rating; ?> <small style="font-size:16px; color:#666;">out of 5</small></div>
                <div style="color:#ffc107; font-size:20px;">★★★★★</div>
            </div>
            
            <div class="rating-bars">
                <?php for($i=5; $i>=1; $i--): 
                    $percent = ($review_count > 0) ? ($rating_data[$i] / $review_count * 100) : 0;
                ?>
                <div class="bar-row">
                    <span style="width:30px;"><?php echo $i; ?> ★</span>
                    <div class="bar-outer"><div class="bar-inner" style="width:<?php echo $percent; ?>%;"></div></div>
                    <span style="width:30px; color:#999;"><?php echo $rating_data[$i]; ?></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div style="margin-top:20px;">
            <?php if($user_id && !$has_reviewed): ?>
                <div style="background:#fffef5; padding:20px; border:1px dashed #eee;">
                    <h4>Write Your Review</h4>
                    <form action="post_review.php" method="POST">
                        <input type="hidden" name="pid" value="<?php echo $product_id; ?>">
                        <textarea name="comment" style="width:100%; height:80px; margin:10px 0; border:1px solid #ddd;" placeholder="Share your thoughts..."></textarea>
                        <button type="submit" style="background:var(--main-red); color:#fff; border:none; padding:8px 20px; border-radius:4px;">Submit Review</button>
                    </form>
                </div>
            <?php elseif($has_reviewed): ?>
                <div style="background:#f6ffed; border:1px solid #b7eb8f; padding:10px; margin-bottom:15px; border-radius:4px;">
                    <i class="fas fa-check-circle" style="color:#52c41a;"></i> You have already reviewed this product.
                </div>
            <?php endif; ?>

            <?php if($review_count > 0): ?>
                <?php foreach($reviews as $rev): ?>
                    <div style="padding:20px 0; border-bottom:1px solid #eee;">
                        <strong>@<?php echo htmlspecialchars($rev['username'] ?? 'User'); ?></strong>
                        <div style="color:#ffc107; margin:5px 0;">★★★★★</div>
                        <p><?php echo htmlspecialchars($rev['comment']); ?></p>
                        <small style="color:#999;"><?php echo $rev['created_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:50px; color:#999;">No reviews yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function selectOpt(btn, type, val) {
    // 同组按钮取消 active
    btn.parentNode.querySelectorAll('.opt-btn').forEach(b => b.classList.remove('active'));
    // 当前选中
    btn.classList.add('active');
    // 赋值给隐藏 input
    document.getElementById('input_' + type).value = val;
}
</script>