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
$description = $product['description'] ?? ''; // 确保变量存在

// --- 新增：建立颜色与图片的逻辑关联 ---
$color_to_image_map = [];
foreach ($colours as $c) {
    $lower_c = strtolower($c); // 统一转小写进行匹配
    $matched_img = '';
    foreach ($all_images as $img) {
        // 如果图片名包含颜色字符串，则认为该图片属于该颜色
        if (strpos(strtolower($img), $lower_c) !== false) {
            $matched_img = $img;
            break; 
        }
    }
    $color_to_image_map[$c] = $matched_img;
}
// ------------------------------------

// 3. 获取评论
$stmt_rev = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
$stmt_rev->execute([$product_id]);
$reviews = $stmt_rev->fetchAll();
$review_count = count($reviews);

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

    @media (max-width: 850px) {
        .product-card { flex-direction: column; }
        .viewer-side { width: 100%; }
    }
</style>

<div class="product-detail-container">
    <div class="product-card">
        
        <div class="viewer-side">
            <div class="main-img-box">
                <img src="../images/<?php echo $all_images[0]; ?>" id="targetDisplay">
            </div>
            <div class="thumb-list">
                <?php foreach($all_images as $i => $img): ?>
                    <div class="thumb-item <?php echo $i===0?'active':''; ?>" onclick="setSlide('<?php echo $img; ?>', this)">
                        <img src="../images/<?php echo $img; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-side">
            <h1 class="p-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div style="font-size:14px; color:#555; margin-bottom: 10px;">
                <span style="color:#ffc107;">4.7 ★★★★★</span> | 
                <span style="margin-left:10px;"><?php echo $review_count; ?> Reviews</span> | 
                <span style="margin-left:10px;"><?php echo $product['sales_count'] ?? 0; ?> Sold</span>
            </div>

            <div class="price-box">RM <?php echo number_format($product['price'], 2); ?></div>

            <form id="purchaseForm">
                <input type="hidden" name="p_id" value="<?php echo $product_id; ?>">

                <?php if(!empty($colours)): ?>
                <div style="margin-bottom:20px;">
                    <p class="spec-label">Colour:</p>
                    <div style="display:flex; flex-wrap:wrap;">
                        <?php foreach($colours as $c): 
                            $img_link = $color_to_image_map[$c]; // 获取对应的图片名
                        ?>
                            <button type="button" class="spec-btn col-opt" 
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
                            <button type="button" class="spec-btn size-opt" onclick="selectOpt(this, 'size', '<?php echo $s; ?>', '')">
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
                        <input type="number" name="qty" value="1" min="1" max="<?php echo $stock; ?>" 
                               style="width:60px; text-align:center; padding:8px; border:none; outline:none;">
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

    <div class="content-section">
        <div class="sect-title">Product Reviews (<?php echo $review_count; ?>)</div>
        <?php if($review_count > 0): ?>
            <?php foreach($reviews as $r): ?>
                <div style="border-bottom:1px solid #eee; padding:20px 0;">
                    <div style="color:#ffc107; margin-bottom:8px;"><?php echo str_repeat('★', $r['rating']); ?></div>
                    <p style="margin:8px 0; color:#333;"><?php echo htmlspecialchars($r['comment']); ?></p>
                    <small style="color:#999;"><?php echo date('Y-m-d H:i', strtotime($r['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding:40px; text-align:center; color:#999;">
                <p>No reviews yet for this product.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 缩略图切换
    function setSlide(img, el) {
        $('#targetDisplay').attr('src', '../images/' + img);
        $('.thumb-item').removeClass('active');
        $(el).addClass('active');
    }

    // 规格选择（包含图片联动）
    function selectOpt(btn, type, val, imgName) {
        if(type === 'color') {
            $('.col-opt').removeClass('active');
            $('#selectedColor').val(val);
            
            // 如果该颜色有对应的图片，则自动切换
            if(imgName && imgName !== '') {
                $('#targetDisplay').attr('src', '../images/' + imgName);
                
                // 同时尝试联动缩略图的高亮
                $('.thumb-item').removeClass('active');
                $(`.thumb-item img[src*="${imgName}"]`).parent().addClass('active');
            }
        } else {
            $('.size-opt').removeClass('active');
            $('#selectedSize').val(val);
        }
        $(btn).addClass('active');
    }

    // 购物车与直购处理
    function handleCart(action) {
        // 验证是否选择了颜色
        if(<?php echo count($colours); ?> > 0 && !$('#selectedColor').val()) {
            return Swal.fire({ icon: 'warning', title: 'Wait', text: 'Please select a color' });
        }
        // 验证是否选择了尺寸
        if(<?php echo count($sizes); ?> > 0 && !$('#selectedSize').val()) {
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
</script>