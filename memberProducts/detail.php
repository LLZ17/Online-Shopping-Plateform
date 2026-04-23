<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// 1. 获取数据
$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { die("Product not found!"); }

// 2. 处理规格与图片
$all_images = !empty($product['product_image']) ? array_map('trim', explode(',', $product['product_image'])) : ['default.jpg'];
$colours = !empty($product['colour']) ? array_map('trim', explode(',', $product['colour'])) : [];
$sizes = !empty($product['size']) ? array_map('trim', explode(',', $product['size'])) : [];
$stock = (int)$product['stock'];
$description = $product['description'] ?? '';

// 产品评分（从 products 表读取）
$product_rating = isset($product['rating']) ? (float)$product['rating'] : 0;
$full_stars = floor($product_rating);
$half_star = ($product_rating - $full_stars) >= 0.5 ? 1 : 0;
$empty_stars = 5 - $full_stars - $half_star;

// 已售数量（从 products 表读取）
$sales_count = isset($product['sales_count']) ? (int)$product['sales_count'] : 0;

// --- 修复：更智能的颜色与图片映射 ---
$color_to_image_map = [];
foreach ($colours as $c) {
    $lower_c = strtolower($c);
    $matched_img = '';
    
    foreach ($all_images as $img) {
        $lower_img = strtolower($img);
        if (strpos($lower_img, $lower_c) !== false) {
            $matched_img = $img;
            break;
        }
        // 颜色变体匹配
        $color_variants = [
            'red' => ['red', 'maroon', 'crimson', 'ruby'],
            'blue' => ['blue', 'navy', 'sky', 'azure', 'cobalt'],
            'green' => ['green', 'lime', 'emerald', 'olive', 'jade'],
            'black' => ['black', 'dark', 'obsidian'],
            'white' => ['white', 'snow', 'ivory', 'cream'],
            'yellow' => ['yellow', 'gold', 'amber', 'lemon'],
            'pink' => ['pink', 'rose', 'magenta', 'fuchsia', 'salmon'],
            'purple' => ['purple', 'violet', 'lavender', 'plum'],
            'orange' => ['orange', 'coral', 'peach', 'tangerine'],
            'gray' => ['gray', 'grey', 'silver', 'slate', 'ash'],
            'brown' => ['brown', 'coffee', 'chocolate', 'tan', 'beige']
        ];
        
        if (isset($color_variants[$lower_c])) {
            foreach ($color_variants[$lower_c] as $variant) {
                if (strpos($lower_img, $variant) !== false) {
                    $matched_img = $img;
                    break 2;
                }
            }
        }
    }
    
    if (empty($matched_img) && !empty($all_images)) {
        $matched_img = $all_images[0];
    }
    
    $color_to_image_map[$c] = $matched_img;
}
// ------------------------------------

// 3. 获取评论及统计数据（从 reviews 表）
$stmt_rev = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
$stmt_rev->execute([$product_id]);
$reviews = $stmt_rev->fetchAll();
$review_count = count($reviews);

// 计算评分统计
$rating_stats = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$total_rating = 0;
$with_comments = 0;
$with_media = 0;

foreach ($reviews as $r) {
    $rating = (int)$r['rating'];
    if ($rating >= 1 && $rating <= 5) {
        $rating_stats[$rating]++;
        $total_rating += $rating;
    }
    if (!empty(trim($r['comment'] ?? ''))) $with_comments++;
    if (!empty(trim($r['media'] ?? ''))) $with_media++;
}

// 评论平均分（基于 reviews 表实际数据）
$avg_review_rating = $review_count > 0 ? round($total_rating / $review_count, 1) : 0;
$active_filter = isset($_GET['rating_filter']) ? (int)$_GET['rating_filter'] : 0;

require_once __DIR__ . '/../includes/_head.php';
?>

<style>
    /* 核心布局 */
    .product-detail-container { 
        max-width: 1200px; 
        margin: 20px auto; 
        padding: 0 15px; 
        font-family: Arial, sans-serif;
    }

    .product-card { 
        display: flex !important; 
        flex-direction: row; 
        align-items: flex-start;
        gap: 40px; 
        background: white; 
        padding: 25px; 
        border-radius: 4px; 
        box-shadow: 0 1px 5px rgba(0,0,0,0.1); 
    }

    /* 左侧图片区 */
    .viewer-side { 
        width: 450px; 
        flex-shrink: 0; 
    }

    .main-img-box { 
        width: 100%; 
        aspect-ratio: 1/1; 
        border: 1px solid #f0f0f0; 
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #fff;
    }
    .main-img-box img { max-width: 100%; max-height: 100%; object-fit: contain; }

    .thumb-list {
        display: flex; 
        gap: 10px; 
        margin-top: 15px; 
        overflow-x: auto;
        padding-bottom: 5px;
    }
    .thumb-item { 
        width: 70px; 
        height: 70px; 
        cursor: pointer; 
        border: 2px solid #eee; 
        flex-shrink: 0;
    }
    .thumb-item.active { border-color: #e60012; }
    .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

    /* 右侧信息区 */
    .info-side { 
        flex: 1; 
        min-width: 0; 
    }

    .p-title { font-size: 24px; font-weight: 500; margin-bottom: 10px; color: #333; }
    .price-box { background: #fafafa; padding: 20px; color: #e60012; font-size: 32px; font-weight: 600; margin: 15px 0; border-radius: 4px; }
    
    /* 产品信息行 - 评分/Reviews/Sold */
    .product-meta {
        font-size: 14px; 
        color: #555; 
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .product-meta .stars-display {
        color: #ee4d2d;
        font-size: 14px;
        letter-spacing: 1px;
    }
    
    .product-meta .stars-display .empty {
        color: #ddd;
    }
    
    .product-meta .rating-number {
        color: #ee4d2d;
        font-weight: 600;
        margin-right: 2px;
    }
    
    .product-meta .separator {
        color: #ddd;
    }

    .spec-label { margin-bottom: 8px; color: #757575; font-size: 14px; }
    .spec-btn { 
        border: 1px solid #ddd; 
        background: #fff; 
        padding: 8px 16px; 
        margin: 0 10px 10px 0;
        cursor: pointer; 
        min-width: 80px;
        transition: all 0.2s;
    }
    .spec-btn:hover { border-color: #e60012; }
    .spec-btn.active { border-color: #e60012; color: #e60012; background: #fff4f4; }

    /* 按钮组 */
    .action-group { display: flex; gap: 15px; margin-top: 30px; }
    .btn-cart { flex: 1; height: 50px; background: #fff4f4; color: #e60012; border: 1px solid #e60012; cursor: pointer; font-size: 16px; }
    .btn-buy { flex: 1; height: 50px; background: #e60012; color: white; border: none; cursor: pointer; font-size: 16px; }
    .btn-cart:hover { background: #ffebeb; }
    .btn-buy:hover { background: #d00010; }

    /* 详情与评论区 */
    .content-section { background: white; margin-top: 20px; padding: 25px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .sect-title { font-size: 18px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; border-left: 4px solid #e60012; padding-left: 15px; }

    /* ===== 评分统计面板样式（类似图片） ===== */
    .rating-panel {
        display: flex;
        align-items: center;
        gap: 40px;
        padding: 25px;
        background: #fffbf5;
        border-radius: 8px;
        margin-bottom: 25px;
    }
    
    .rating-summary {
        text-align: center;
        min-width: 120px;
    }
    
    .rating-big {
        font-size: 48px;
        color: #ee4d2d;
        font-weight: 600;
        line-height: 1;
    }
    
    .rating-big span {
        font-size: 18px;
        color: #ee4d2d;
        font-weight: 400;
    }
    
    .rating-stars-big {
        color: #ee4d2d;
        font-size: 20px;
        margin-top: 8px;
        letter-spacing: 2px;
    }
    
    .rating-stars-big .empty {
        color: #ddd;
    }
    
    .rating-filters {
        flex: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .filter-btn {
        padding: 8px 20px;
        border: 1px solid #e8e8e8;
        background: #fff;
        color: #333;
        cursor: pointer;
        border-radius: 2px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .filter-btn:hover {
        border-color: #ee4d2d;
        color: #ee4d2d;
    }
    
    .filter-btn.active {
        border-color: #ee4d2d;
        color: #ee4d2d;
        background: #fff4f4;
    }

    /* 评论列表 */
    .review-item {
        border-bottom: 1px solid #eee;
        padding: 20px 0;
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .reviewer-name {
        font-weight: 600;
        color: #333;
    }
    
    .review-date {
        color: #999;
        font-size: 13px;
    }
    
    .review-stars {
        color: #ffc107;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .review-text {
        color: #333;
        line-height: 1.6;
    }
    
    .review-media {
        display: flex;
        gap: 10px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    
    .review-media img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }

    /* 空状态 */
    .empty-reviews {
        text-align: center;
        padding: 40px;
        color: #999;
    }

    /* 购买提示 */
    .purchase-hint {
        background: #fff8e1;
        border: 1px solid #ffe082;
        padding: 15px 20px;
        border-radius: 4px;
        margin-bottom: 20px;
        color: #f57c00;
        font-size: 14px;
    }

    @media (max-width: 850px) {
        .product-card { flex-direction: column; }
        .viewer-side { width: 100%; }
        .rating-panel { flex-direction: column; gap: 20px; }
    }
</style>

<div class="product-detail-container">
    <div class="product-card">
        
        <div class="viewer-side">
            <div class="main-img-box">
                <img src="../images/<?php echo $all_images[0]; ?>" id="targetDisplay" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="thumb-list" id="thumbList">
                <?php foreach($all_images as $i => $img): ?>
                    <div class="thumb-item <?php echo $i===0?'active':''; ?>" data-img="<?php echo $img; ?>" onclick="setSlide('<?php echo $img; ?>', this)">
                        <img src="../images/<?php echo $img; ?>" alt="thumbnail">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-side">
            <h1 class="p-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <!-- 产品信息：评分 | Reviews | Sold -->
            <div class="product-meta">
                <span class="stars-display">
                    <?php 
                    // 实心星
                    for ($i = 0; $i < $full_stars; $i++) echo '★';
                    // 半星（如果有）
                    if ($half_star) echo '★';
                    // 空心星
                    for ($i = 0; $i < $empty_stars; $i++) echo '<span class="empty">★</span>';
                    ?>
                </span>
                <span class="rating-number"><?php echo number_format($product_rating, 1); ?></span>
                <span class="separator">|</span>
                <span><?php echo $review_count; ?> Reviews</span>
                <span class="separator">|</span>
                <span><?php echo number_format($sales_count); ?> Sold</span>
            </div>

            <div class="price-box">RM <?php echo number_format($product['price'], 2); ?></div>

            <form id="purchaseForm">
                <input type="hidden" name="p_id" value="<?php echo $product_id; ?>">

                <?php if(!empty($colours)): ?>
                <div style="margin-bottom:20px;">
                    <p class="spec-label">Colour:</p>
                    <div style="display:flex; flex-wrap:wrap;">
                        <?php foreach($colours as $c): 
                            $img_link = $color_to_image_map[$c] ?? $all_images[0];
                        ?>
                            <button type="button" class="spec-btn col-opt" 
                                    data-color="<?php echo $c; ?>"
                                    data-img="<?php echo $img_link; ?>"
                                    onclick="selectOpt(this, 'color', '<?php echo $c; ?>', '<?php echo $img_link; ?>')">
                                <?php echo $c; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="sel_color" id="selectedColor">
                </div>
                <?php endif; ?>

                <?php if(!empty($sizes)): ?>
                <div style="margin-bottom:20px;">
                    <p class="spec-label">Size:</p>
                    <div style="display:flex; flex-wrap:wrap;">
                        <?php foreach($sizes as $s): ?>
                            <button type="button" class="spec-btn size-opt" 
                                    data-size="<?php echo $s; ?>"
                                    onclick="selectOpt(this, 'size', '<?php echo $s; ?>', '')">
                                <?php echo $s; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="sel_size" id="selectedSize">
                </div>
                <?php endif; ?>

                <div style="margin-bottom:25px; display:flex; align-items:center; gap:15px;">
                    <span class="spec-label" style="margin-bottom:0;">Quantity:</span>
                    <div style="display:flex; align-items:center; border:1px solid #ddd;">
                        <button type="button" onclick="changeQty(-1)" style="width:32px; height:32px; border:none; background:#f5f5f5; cursor:pointer;">-</button>
                        <input type="number" name="qty" id="qtyInput" value="1" min="1" max="<?php echo $stock; ?>" 
                               style="width:50px; text-align:center; border:none; outline:none; -moz-appearance:textfield;">
                        <button type="button" onclick="changeQty(1)" style="width:32px; height:32px; border:none; background:#f5f5f5; cursor:pointer;">+</button>
                    </div>
                    <span style="font-size:13px; color:#999;"><?php echo $stock; ?> pieces available</span>
                </div>

                <div class="action-group">
                    <button type="button" onclick="handleCart('add')" class="btn-cart">Add To Cart</button>
                    <button type="button" onclick="handleCart('buy')" class="btn-buy">Buy Now</button>
                </div>
            </form>
        </div>
    </div>

    <div class="content-section">
        <div class="sect-title">Product Description</div>
        <div style="line-height:1.8; color: #444; font-size: 15px;">
            <?php echo nl2br(htmlspecialchars($description)); ?>
        </div>
    </div>

    <!-- ===== 评论展示区域 ===== -->
    <div class="content-section" id="reviewsSection">
        <div class="sect-title">Product Reviews</div>
        
        <!-- 评分统计面板 -->
        <div class="rating-panel">
            <div class="rating-summary">
                <div class="rating-big"><?php echo number_format($avg_review_rating, 1); ?><span> out of 5</span></div>
                <div class="rating-stars-big">
                    <?php 
                    $rv_full = floor($avg_review_rating);
                    $rv_half = ($avg_review_rating - $rv_full) >= 0.5 ? 1 : 0;
                    $rv_empty = 5 - $rv_full - $rv_half;
                    for ($i = 0; $i < $rv_full; $i++) echo '★';
                    if ($rv_half) echo '★';
                    for ($i = 0; $i < $rv_empty; $i++) echo '<span class="empty">★</span>';
                    ?>
                </div>
            </div>
            <div class="rating-filters">
                <button class="filter-btn <?php echo $active_filter === 0 ? 'active' : ''; ?>" onclick="filterReviews(0)">All (<?php echo $review_count; ?>)</button>
                <button class="filter-btn <?php echo $active_filter === 5 ? 'active' : ''; ?>" onclick="filterReviews(5)">5 Star (<?php echo $rating_stats[5]; ?>)</button>
                <button class="filter-btn <?php echo $active_filter === 4 ? 'active' : ''; ?>" onclick="filterReviews(4)">4 Star (<?php echo $rating_stats[4]; ?>)</button>
                <button class="filter-btn <?php echo $active_filter === 3 ? 'active' : ''; ?>" onclick="filterReviews(3)">3 Star (<?php echo $rating_stats[3]; ?>)</button>
                <button class="filter-btn <?php echo $active_filter === 2 ? 'active' : ''; ?>" onclick="filterReviews(2)">2 Star (<?php echo $rating_stats[2]; ?>)</button>
                <button class="filter-btn <?php echo $active_filter === 1 ? 'active' : ''; ?>" onclick="filterReviews(1)">1 Star (<?php echo $rating_stats[1]; ?>)</button>
                <button class="filter-btn <?php echo $active_filter === -1 ? 'active' : ''; ?>" onclick="filterReviews(-1)">With Comments (<?php echo $with_comments; ?>)</button>
                <button class="filter-btn <?php echo $active_filter === -2 ? 'active' : ''; ?>" onclick="filterReviews(-2)">With Media (<?php echo $with_media; ?>)</button>
            </div>
        </div>

        <!-- 购买后评价提示 -->
        <div class="purchase-hint">
            <strong>Purchased this product?</strong> You can leave a review from your <a href="../member/orders.php" style="color: #ee4d2d; text-decoration: underline;">Order History</a> page after delivery.
        </div>

        <!-- 评论列表 -->
        <div id="reviewsList">
            <?php if($review_count > 0): ?>
                <?php foreach($reviews as $r): ?>
                    <div class="review-item" data-rating="<?php echo $r['rating']; ?>" data-has-comment="<?php echo !empty(trim($r['comment'] ?? '')) ? '1' : '0'; ?>" data-has-media="<?php echo !empty(trim($r['media'] ?? '')) ? '1' : '0'; ?>">
                        <div class="review-header">
                            <span class="reviewer-name"><?php echo htmlspecialchars($r['user_name'] ?? 'Anonymous'); ?></span>
                            <span class="review-date"><?php echo date('Y-m-d H:i', strtotime($r['created_at'])); ?></span>
                        </div>
                        <div class="review-stars"><?php echo str_repeat('★', $r['rating']); ?></div>
                        <?php if(!empty($r['comment'])): ?>
                            <p class="review-text"><?php echo htmlspecialchars($r['comment']); ?></p>
                        <?php endif; ?>
                        <?php if(!empty($r['media'])): 
                            $media_files = array_map('trim', explode(',', $r['media']));
                        ?>
                            <div class="review-media">
                                <?php foreach($media_files as $m): ?>
                                    <img src="../uploads/reviews/<?php echo $m; ?>" onclick="window.open(this.src)" alt="review media">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-reviews">
                    <p>No reviews yet for this product.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ===== 图片切换 =====
    function setSlide(img, el) {
        $('#targetDisplay').attr('src', '../images/' + img);
        $('.thumb-item').removeClass('active');
        $(el).addClass('active');
    }

    // ===== 规格选择（修复图片联动） =====
    function selectOpt(btn, type, val, imgName) {
        if(type === 'color') {
            $('.col-opt').removeClass('active');
            $('#selectedColor').val(val);
            
            if(imgName && imgName !== '') {
                var $targetThumb = $('.thumb-item[data-img="' + imgName + '"]');
                if ($targetThumb.length > 0) {
                    $('#targetDisplay').attr('src', '../images/' + imgName);
                    $('.thumb-item').removeClass('active');
                    $targetThumb.addClass('active');
                    $targetThumb[0].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                } else {
                    var $fuzzyMatch = $('.thumb-item img[src*="' + imgName.toLowerCase().replace(/[^a-z0-9]/g, '') + '"]').closest('.thumb-item');
                    if ($fuzzyMatch.length > 0) {
                        $fuzzyMatch.click();
                    }
                }
            }
        } else {
            $('.size-opt').removeClass('active');
            $('#selectedSize').val(val);
        }
        $(btn).addClass('active');
    }

    // ===== 数量调整 =====
    function changeQty(delta) {
        var input = document.getElementById('qtyInput');
        var val = parseInt(input.value) || 1;
        var max = parseInt(input.max) || 999;
        var newVal = val + delta;
        if (newVal >= 1 && newVal <= max) {
            input.value = newVal;
        }
    }

    // ===== 购物车处理 =====
    function handleCart(action) {
        var hasColors = <?php echo count($colours); ?> > 0;
        var hasSizes = <?php echo count($sizes); ?> > 0;
        
        if(hasColors && !$('#selectedColor').val()) {
            return Swal.fire({ icon: 'warning', title: 'Wait', text: 'Please select a color' });
        }
        if(hasSizes && !$('#selectedSize').val()) {
            return Swal.fire({ icon: 'warning', title: 'Wait', text: 'Please select a size' });
        }

        const formData = $('#purchaseForm').serialize() + '&btn_action=' + action;

        $.ajax({
            url: 'add.php',
            type: 'POST',
            data: formData,
            success: function(data) {
                try {
                    let res = JSON.parse(data);
                    if(res.success) {
                        if(action === 'buy') {
                            window.location.href = '../member/checkout.php';
                        } else {
                            Swal.fire({ icon: 'success', title: 'Done', text: 'Added to cart successfully!' });
                        }
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.error || 'Failed to process request' });
                    }
                } catch(e) {
                    console.error("Response Parse Error:", data);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'System Error. Check Console.' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Network connection failed' });
            }
        });
    }

    // ===== 筛选评论 =====
    function filterReviews(filter) {
        $('.filter-btn').removeClass('active');
        $('.filter-btn[onclick="filterReviews(' + filter + ')"]').addClass('active');
        
        var visibleCount = 0;
        
        $('.review-item').each(function() {
            var $item = $(this);
            var rating = parseInt($item.data('rating'));
            var hasComment = $item.data('has-comment') === '1';
            var hasMedia = $item.data('has-media') === '1';
            var show = false;
            
            if (filter === 0) show = true;
            else if (filter > 0 && filter <= 5) show = (rating === filter);
            else if (filter === -1) show = hasComment;
            else if (filter === -2) show = hasMedia;
            
            $item.toggle(show);
            if (show) visibleCount++;
        });
        
        if (visibleCount === 0 && $('.review-item').length > 0) {
            if ($('#noFilterResults').length === 0) {
                $('#reviewsList').append('<div id="noFilterResults" class="empty-reviews"><p>No reviews match this filter.</p></div>');
            }
        } else {
            $('#noFilterResults').remove();
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>