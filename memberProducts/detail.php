<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// 1. 获取产品数据并验证
$product_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) { exit("Error: Product Not Found!"); }

// --- 核心数据解析 ---
// 将图片、颜色、尺寸字符串转为数组
$all_images = !empty($product['product_image']) ? array_map('trim', explode(',', $product['product_image'])) : ['default.jpg'];
$colours = !empty($product['colour']) ? array_map('trim', explode(',', $product['colour'])) : [];
$sizes = !empty($product['size']) ? array_map('trim', explode(',', $product['size'])) : [];

// 关键字段：评分、库存、描述
$sql_rating = (float)($product['rating'] ?? 0); 
$stock = (int)$product['stock'];
$description = !empty($product['description']) ? $product['description'] : "This seller hasn't added a description yet.";
$reward_points = floor($product['price'] * 1); // 模拟每 RM1 赚 1 分

require_once __DIR__ . '/../includes/_head.php';
?>

<style>
    :root { --shopee-red: #e60012; --star-yellow: #ffc107; --bg-gray: #f5f5f5; }
    body { background-color: var(--bg-gray); font-family: -apple-system, sans-serif; }
    .detail-container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }

    /* 主布局 */
    .product-main { display: flex; gap: 40px; background: white; padding: 25px; border-radius: 2px; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }

    /* 左侧图片区域 */
    .image-section { width: 450px; position: relative; }
    .main-stage { width: 100%; aspect-ratio: 1/1; border: 1px solid #f0f0f0; position: relative; display: flex; align-items: center; justify-content: center; }
    .main-stage img { max-width: 100%; max-height: 100%; object-fit: contain; }
    
    /* 左右键按钮逻辑 */
    .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.15); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px; z-index: 5; }
    .nav-btn:hover { background: rgba(0,0,0,0.3); }
    .btn-prev { left: 10px; }
    .btn-next { right: 10px; }

    .thumb-scroll { display: flex; gap: 10px; margin-top: 15px; }
    .thumb-box { width: 80px; height: 80px; border: 2px solid transparent; cursor: pointer; }
    .thumb-box.active { border-color: var(--shopee-red); }
    .thumb-box img { width: 100%; height: 100%; object-fit: cover; }

    /* 右侧详细信息区域 */
    .details-section { flex: 1; }
    .p-name { font-size: 22px; font-weight: 500; margin-bottom: 12px; }
    .price-tag { background: #fafafa; padding: 15px 20px; color: var(--shopee-red); font-size: 32px; font-weight: 500; margin-bottom: 20px; }
    
    /* 积分卡片 */
    .points-banner { background: #fffcf5; border: 1px solid #f9eacc; padding: 12px 15px; border-radius: 4px; display: flex; align-items: center; gap: 10px; color: #ff9800; font-size: 14px; margin-bottom: 25px; }

    /* 选项按钮 */
    .option-btn { border: 1px solid rgba(0,0,0,0.09); background: white; padding: 8px 15px; cursor: pointer; border-radius: 2px; transition: 0.1s; }
    .option-btn.active { border-color: var(--shopee-red); color: var(--shopee-red); }

    /* 底部操作按钮 */
    .action-group { display: flex; gap: 15px; margin-top: 30px; }
    .btn-add-cart { flex: 1; height: 48px; background: rgba(208,1,27,0.08); color: var(--shopee-red); border: 1px solid var(--shopee-red); cursor: pointer; font-size: 16px; }
    .btn-buy-now { flex: 1; height: 48px; background: var(--shopee-red); color: white; border: none; cursor: pointer; font-size: 16px; }

    /* 描述区 */
    .description-box { background: white; margin-top: 20px; padding: 25px; min-height: 200px; }
    .box-title { background: #fafafa; padding: 15px; font-size: 18px; margin-bottom: 20px; text-transform: uppercase; }
</style>

<div class="detail-container">
    <div class="product-main">
        <div class="image-section">
            <button class="nav-btn btn-prev" onclick="moveSlide(-1)">&#10094;</button>
            <div class="main-stage">
                <img src="../images/<?php echo $all_images[0]; ?>" id="mainImg">
            </div>
            <button class="nav-btn btn-next" onclick="moveSlide(1)">&#10095;</button>

            <div class="thumb-scroll">
                <?php foreach($all_images as $i => $img): ?>
                    <div class="thumb-box <?php echo $i==0?'active':''; ?>" onclick="setImg(<?php echo $i; ?>)">
                        <img src="../images/<?php echo $img; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="details-section">
            <h1 class="p-name"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div style="display:flex; align-items:center; gap:15px; margin-bottom:15px;">
                <span style="color:var(--shopee-red); border-bottom:1px solid var(--shopee-red);"><?php echo number_format($sql_rating, 1); ?></span>
                <div style="color:var(--star-yellow);"><?php for($i=1;$i<=5;$i++) echo ($i<=round($sql_rating)?'★':'☆'); ?></div>
                <div style="width:1px; height:15px; background:#ccc;"></div>
                <span><?php echo $product['sales_count'] ?? 0; ?> Sold</span>
            </div>

            <div class="price-tag">RM <?php echo number_format($product['price'], 2); ?></div>

            <div class="points-banner">
                <i class="fas fa-gift"></i>
                <span>Earn <strong><?php echo $reward_points; ?></strong> reward points with this purchase</span>
            </div>

            <form action="cart_action.php" method="POST" id="orderForm">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                
                <?php if(!empty($colours)): ?>
                <div style="margin-bottom:20px;">
                    <p style="color:#757575; margin-bottom:8px;">Colour:</p>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <?php foreach($colours as $idx => $c): ?>
                            <button type="button" class="option-btn col-trigger" onclick="selectColor(this, <?php echo $idx; ?>, '<?php echo $c; ?>')"><?php echo $c; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="color" id="selectedColor" required>
                </div>
                <?php endif; ?>

                <?php if(!empty($sizes)): ?>
                <div style="margin-bottom:20px;">
                    <p style="color:#757575; margin-bottom:8px;">Size:</p>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <?php foreach($sizes as $s): ?>
                            <button type="button" class="option-btn size-trigger" onclick="selectSize(this, '<?php echo $s; ?>')"><?php echo $s; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="size" id="selectedSize" required>
                </div>
                <?php endif; ?>

                <div style="margin-bottom:20px; display:flex; align-items:center; gap:15px;">
                    <p style="color:#757575;">Quantity:</p>
                    <div style="display:flex; border:1px solid #ddd;">
                        <button type="button" onclick="updateQty(-1)" style="width:30px; background:white; border:none; cursor:pointer;">-</button>
                        <input type="number" name="qty" id="qtyInput" value="1" readonly style="width:50px; text-align:center; border:none; border-left:1px solid #ddd; border-right:1px solid #ddd;">
                        <button type="button" onclick="updateQty(1)" style="width:30px; background:white; border:none; cursor:pointer;">+</button>
                    </div>
                    <span style="color:#757575; font-size:14px;"><?php echo $stock; ?> pieces available</span>
                </div>

                <div class="action-group">
                    <button type="submit" name="action" value="add" class="btn-add-cart"><i class="fas fa-cart-plus"></i> Add To Cart</button>
                    <button type="submit" name="action" value="buy" class="btn-buy-now">Buy Now</button>
                </div>
            </form>
        </div>
    </div>

    <div class="description-box">
        <div class="box-title">Product Description</div>
        <div style="padding:0 15px; white-space: pre-wrap; line-height:1.8; color:#333;"><?php echo htmlspecialchars($description); ?></div>
    </div>
</div>

<script>
    const imageArray = <?php echo json_encode($all_images); ?>;
    let currentIdx = 0;

    // 1. 图片交互：左右键、缩略图联动
    function setImg(index) {
        currentIdx = index;
        document.getElementById('mainImg').src = "../images/" + imageArray[currentIdx];
        
        const boxes = document.querySelectorAll('.thumb-box');
        boxes.forEach((box, i) => {
            box.classList.toggle('active', i === currentIdx);
        });
    }

    function moveSlide(step) {
        currentIdx = (currentIdx + step + imageArray.length) % imageArray.length;
        setImg(currentIdx);
    }

    // 2. 颜色选择：点击联动图片
    function selectColor(element, index, colorName) {
        document.querySelectorAll('.col-trigger').forEach(b => b.classList.remove('active'));
        element.classList.add('active');
        document.getElementById('selectedColor').value = colorName;
        setImg(index); // 自动跳到对应颜色的图片
    }

    // 3. 尺寸选择
    function selectSize(element, sizeName) {
        document.querySelectorAll('.size-trigger').forEach(b => b.classList.remove('active'));
        element.classList.add('active');
        document.getElementById('selectedSize').value = sizeName;
    }

    // 4. 数量加减
    function updateQty(step) {
        const input = document.getElementById('qtyInput');
        let newVal = parseInt(input.value) + step;
        if(newVal >= 1 && newVal <= <?php echo $stock; ?>) {
            input.value = newVal;
        }
    }

    // 5. 提交前校验：确保用户选了颜色和尺寸
    document.getElementById('orderForm').onsubmit = function() {
        if(<?php echo count($colours); ?> > 0 && !document.getElementById('selectedColor').value) {
            alert("Please select a Color!"); return false;
        }
        if(<?php echo count($sizes); ?> > 0 && !document.getElementById('selectedSize').value) {
            alert("Please select a Size!"); return false;
        }
        return true;
    };
</script>