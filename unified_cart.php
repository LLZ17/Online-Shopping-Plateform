<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='../login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// ==========================================
// 1. 后端处理逻辑 (都在同一个文件内)
// ==========================================

// 处理：全部删除
if (isset($_GET['action']) && $_GET['action'] == 'clear_all') {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    header("Location: unified_cart.php");
    exit();
}

// 处理：删除单项
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $user_id]);
    header("Location: unified_cart.php");
    exit();
}

// 处理：AJAX 更新数量和规格 (由底部的 JS 调用)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    if ($_POST['ajax_action'] == 'update_qty') {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$_POST['qty'], $_POST['cart_id'], $user_id]);
        echo json_encode(['success' => true]);
        exit();
    }
    
    if ($_POST['ajax_action'] == 'update_variant') {
        $stmt = $pdo->prepare("UPDATE cart SET selected_size = ?, colour = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$_POST['size'], $_POST['colour'], $_POST['cart_id'], $user_id]);
        echo json_encode(['success' => true]);
        exit();
    }
}

// 获取购物车商品
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.product_image, p.stock, p.size as available_sizes, p.colour as available_colours
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();
?>

<style>
    :root { --shopee-red: #e60012; --bg-gray: #f5f5f5; }
    body { background-color: var(--bg-gray); }
    .cart-container { max-width: 1100px; margin: 30px auto; padding-bottom: 100px; }
    
    /* 头部 */
    .cart-header { display: grid; grid-template-columns: 50px 1fr 150px 150px 150px 100px; background: white; padding: 15px 20px; margin-bottom: 15px; border-radius: 3px; font-weight: bold; }

    /* 商品行 */
    .cart-item { display: grid; grid-template-columns: 50px 120px 1fr 150px 150px 150px 100px; align-items: center; background: white; padding: 20px; margin-bottom: 5px; border-radius: 3px; }
    .product-img { width: 100px; height: 120px; object-fit: cover; border-radius: 2px; }
    .product-name { font-weight: bold; text-decoration: none; color: #333; margin-bottom: 10px; display: block; }
    
    /* 规格修改样式 */
    .variant-box { background: #fafafa; border: 1px dashed #ccc; padding: 5px 10px; font-size: 13px; color: #666; cursor: pointer; display: inline-block; }
    .variant-box:hover { border-color: var(--shopee-red); color: var(--shopee-red); }

    /* 数量控制 */
    .qty-control { display: flex; align-items: center; border: 1px solid #ddd; width: fit-content; }
    .qty-btn { border: none; background: white; padding: 5px 10px; cursor: pointer; }
    .qty-input { width: 40px; text-align: center; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; padding: 5px 0; }

    .price { color: #333; }
    .subtotal { color: var(--shopee-red); font-weight: bold; }

    /* 底部固定栏 */
    .cart-footer { position: fixed; bottom: 0; left: 0; right: 0; background: white; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 100; }
    .checkout-btn { background: var(--shopee-red); color: white; padding: 12px 40px; border: none; border-radius: 2px; font-size: 16px; cursor: pointer; }
    .checkout-btn:disabled { background: #ccc; cursor: not-allowed; }

    /* 弹窗 Modal */
    #editModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; }
    .modal-content { background:white; padding:25px; border-radius:5px; width:350px; }
    .modal-content select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
    
    input[type="checkbox"] { transform: scale(1.3); accent-color: var(--shopee-red); cursor: pointer; }
</style>

<div class="cart-container">
    <div class="cart-header">
        <input type="checkbox" id="selectAll">
        <span>Product</span>
        <span>Unit Price</span>
        <span>Quantity</span>
        <span>Total Price</span>
        <span>Action</span>
    </div>

    <?php if (empty($cart_items)): ?>
        <div style="background:white; padding:50px; text-align:center;">Your cart is empty. <a href="../index.php">Shop Now</a></div>
    <?php else: ?>
        <form id="cartForm" action="../checkout/index.php" method="POST">
            <?php foreach ($cart_items as $item): 
                // 图片匹配逻辑
                $imgs = explode(',', $item['product_image']);
                $display_img = trim($imgs[0]);
                $selected_color = strtolower(trim($item['colour']));
                foreach ($imgs as $img) {
                    if (strpos(strtolower(trim($img)), $selected_color) !== false) {
                        $display_img = trim($img); break;
                    }
                }
            ?>
                <div class="cart-item" data-id="<?php echo $item['cart_id']; ?>" data-price="<?php echo $item['price']; ?>">
                    <input type="checkbox" name="items[]" value="<?php echo $item['cart_id']; ?>" class="item-checkbox">
                    
                    <img src="../images/<?php echo $display_img; ?>" class="product-img" onerror="this.src='../images/default.jpg'">
                    
                    <div>
                        <a href="detail.php?id=<?php echo $item['product_id']; ?>" class="product-name"><?php echo htmlspecialchars($item['name']); ?></a>
                        <div class="variant-box" onclick='openModal(<?php echo json_encode($item); ?>)'>
                            Variation: <?php echo $item['selected_size']; ?>, <?php echo $item['colour']; ?> ▼
                        </div>
                    </div>

                    <div class="price">RM <?php echo number_format($item['price'], 2); ?></div>

                    <div class="qty-control">
                        <button type="button" class="qty-btn minus">-</button>
                        <input type="text" value="<?php echo $item['quantity']; ?>" class="qty-input" readonly>
                        <button type="button" class="qty-btn plus">+</button>
                    </div>

                    <div class="subtotal">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>

                    <a href="?action=remove&id=<?php echo $item['cart_id']; ?>" onclick="return confirm('Remove?')" style="text-decoration:none; color:#666;">Delete</a>
                </div>
            <?php endforeach; ?>

            <div class="cart-footer">
                <div>
                    <a href="?action=clear_all" onclick="return confirm('Clear ALL items?')" style="color:#666; margin-right:20px;">Delete All</a>
                    Selected (<span id="count">0</span>)
                </div>
                <div style="display:flex; align-items:center; gap:20px;">
                    <div style="font-size:18px;">Total: <span style="color:var(--shopee-red); font-weight:bold; font-size:24px;">RM <span id="totalPrice">0.00</span></span></div>
                    <button type="submit" class="checkout-btn" id="checkoutBtn" disabled>Check Out</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<div id="editModal">
    <div class="modal-content">
        <h3>Change Variation</h3>
        <input type="hidden" id="modalCartId">
        <label>Size:</label>
        <select id="modalSize"></select>
        <label>Colour:</label>
        <select id="modalColour"></select>
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button onclick="saveChanges()" style="flex:1; background:var(--shopee-red); color:white; border:none; padding:10px; cursor:pointer;">Confirm</button>
            <button onclick="$('#editModal').hide()" style="flex:1; background:#eee; border:none; padding:10px; cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function refreshTotal() {
        let total = 0, count = 0;
        $('.item-checkbox:checked').each(function() {
            let row = $(this).closest('.cart-item');
            total += parseFloat(row.data('price')) * parseInt(row.find('.qty-input').val());
            count++;
        });
        $('#totalPrice').text(total.toFixed(2));
        $('#count').text(count);
        $('#checkoutBtn').prop('disabled', count === 0);
    }

    $('#selectAll').on('change', function() {
        $('.item-checkbox').prop('checked', $(this).is(':checked'));
        refreshTotal();
    });

    $('.item-checkbox').on('change', refreshTotal);

    $('.qty-btn').on('click', function() {
        let row = $(this).closest('.cart-item');
        let input = row.find('.qty-input');
        let qty = parseInt(input.val());
        qty = $(this).hasClass('plus') ? qty + 1 : (qty > 1 ? qty - 1 : 1);
        input.val(qty);
        
        row.find('.subtotal').text('RM ' + (parseFloat(row.data('price')) * qty).toFixed(2));
        refreshTotal();
        
        // AJAX 同步到数据库
        $.post('unified_cart.php', { ajax_action: 'update_qty', cart_id: row.data('id'), qty: qty });
    });
});

function openModal(item) {
    $('#modalCartId').val(item.cart_id);
    
    // 解析产品允许的规格
    let sizes = item.available_sizes ? item.available_sizes.split(',') : ['FreeSize'];
    let colours = item.available_colours ? item.available_colours.split(',') : ['Default'];
    
    $('#modalSize').html(sizes.map(s => `<option value="${s.trim()}" ${item.selected_size == s.trim() ? 'selected' : ''}>${s.trim()}</option>`));
    $('#modalColour').html(colours.map(c => `<option value="${c.trim()}" ${item.colour == c.trim() ? 'selected' : ''}>${c.trim()}</option>`));
    
    $('#editModal').css('display', 'flex');
}

function saveChanges() {
    let data = {
        ajax_action: 'update_variant',
        cart_id: $('#modalCartId').val(),
        size: $('#modalSize').val(),
        colour: $('#modalColour').val()
    };
    $.post('unified_cart.php', data, function() {
        location.reload(); // 简单起见，修改完直接刷新页面看新图片和新规格
    });
}
</script>

<?php require_once '../includes/_foot.php'; ?>