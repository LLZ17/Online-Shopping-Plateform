<?php
session_start();
require_once '../includes/db.php';

// ==========================================
// 1. 后端逻辑处理 (必须在 require _head.php 之前)
// ==========================================

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='../login.php';</script>";
    exit();
}
$user_id = $_SESSION['user_id'];

// 处理：批量删除选中项 (全选删除)
if (isset($_POST['batch_delete']) && !empty($_POST['items'])) {
    $ids = $_POST['items'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge($ids, [$user_id]);
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id IN ($placeholders) AND user_id = ?");
    $stmt->execute($params);
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

// 处理：全部清空
if (isset($_GET['action']) && $_GET['action'] == 'clear_all') {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

// 处理：单个删除
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $user_id]);
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

// 处理：AJAX 更新数量或规格
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    if ($_POST['ajax_action'] == 'update_qty') {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$_POST['qty'], $_POST['cart_id'], $user_id]);
    } elseif ($_POST['ajax_action'] == 'update_variant') {
        $stmt = $pdo->prepare("UPDATE cart SET selected_size = ?, colour = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->execute([$_POST['size'], $_POST['colour'], $_POST['cart_id'], $user_id]);
    }
    exit();
}

// ==========================================
// 2. 获取数据并引入 UI
// ==========================================
require_once '../includes/_head.php'; 

$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.product_image, p.size as all_sizes, p.colour as all_colours
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();
?>

<style>
    :root { --shopee-red: #e60012; }
    body { background-color: #f5f5f5; }
    .cart-container { max-width: 1000px; margin: 30px auto; padding-bottom: 120px; }
    
    /* 复选框大小修正 */
    input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--shopee-red); cursor: pointer; }

    .cart-header { display: grid; grid-template-columns: 50px 1fr 120px 150px 120px 100px; background: white; padding: 15px 20px; margin-bottom: 10px; font-weight: bold; }
    .cart-item { display: grid; grid-template-columns: 50px 100px 1fr 120px 150px 120px 100px; align-items: center; background: white; padding: 15px 20px; margin-bottom: 2px; }
    
    .product-img { width: 80px; height: 100px; object-fit: cover; border: 1px solid #eee; }
    .product-info { padding: 0 15px; }
    .variation-box { background: #fafafa; border: 1px dashed #ccc; padding: 4px 8px; font-size: 13px; color: #666; cursor: pointer; display: inline-block; }

    .qty-control { display: flex; border: 1px solid #ddd; width: fit-content; }
    .qty-btn { background: white; border: none; width: 30px; height: 30px; cursor: pointer; }
    .qty-input { width: 40px; text-align: center; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; }

    .footer-bar { position: fixed; bottom: 0; width: 1000px; left: 50%; transform: translateX(-50%); background: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; box-shadow: 0 -5px 10px rgba(0,0,0,0.05); z-index: 99; }
    .checkout-btn { background: var(--shopee-red); color: white; padding: 12px 45px; border: none; font-size: 16px; cursor: pointer; }
    
    /* 弹窗样式 */
    #editModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000; justify-content:center; align-items:center; }
    .modal-content { background:white; padding:25px; border-radius:5px; width:350px; }
</style>

<div class="cart-container">
    <form id="cartForm" method="POST">
        <div class="cart-header">
            <input type="checkbox" id="checkAll">
            <span>Product</span>
            <span style="text-align: center;">Price</span>
            <span style="text-align: center;">Quantity</span>
            <span style="text-align: center;">Total</span>
            <span style="text-align: center;">Action</span>
        </div>

        <?php foreach ($cart_items as $item): 
            // --- 图片逻辑：不区分大小写匹配 ---
            $imgs = explode(',', $item['product_image']);
            $display_img = trim($imgs[0]);
            $user_color = strtolower(trim($item['colour'])); // 转为小写比较
            
            foreach ($imgs as $img) {
                $img_name = strtolower(trim($img));
                if ($user_color !== '' && strpos($img_name, $user_color) !== false) {
                    $display_img = trim($img);
                    break;
                }
            }
        ?>
            <div class="cart-item" data-price="<?php echo $item['price']; ?>" data-id="<?php echo $item['cart_id']; ?>">
                <input type="checkbox" name="items[]" value="<?php echo $item['cart_id']; ?>" class="item-checkbox">
                
                <img src="../images/<?php echo $display_img; ?>" class="product-img" onerror="this.src='../images/default.jpg'">
                
                <div class="product-info">
                    <div style="font-weight: bold; margin-bottom: 5px;"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="variation-box" onclick='openEditModal(<?php echo json_encode($item); ?>)'>
                        Size: <?php echo $item['selected_size'] ?: 'N/A'; ?>, Color: <?php echo $item['colour'] ?: 'N/A'; ?> ▼
                    </div>
                </div>

                <div style="text-align: center;">RM <?php echo number_format($item['price'], 2); ?></div>

                <div style="display: flex; justify-content: center;">
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="updateQty(this, -1)">-</button>
                        <input type="text" value="<?php echo $item['quantity']; ?>" class="qty-input" readonly>
                        <button type="button" class="qty-btn" onclick="updateQty(this, 1)">+</button>
                    </div>
                </div>

                <div style="text-align: center; color: var(--shopee-red); font-weight: bold;">
                    RM <span class="row-total"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>

                <div style="text-align: center;">
                    <a href="?action=remove&id=<?php echo $item['cart_id']; ?>" onclick="return confirm('Delete?')" style="color:#666; text-decoration:none;">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="footer-bar">
            <div>
                <input type="checkbox" id="checkAllBottom"> 全选
                <button type="submit" name="batch_delete" class="btn" style="color:#555; border:none; background:none; cursor:pointer; margin-left:15px;" onclick="return confirm('Delete selected?')">删除选中的产品</button>
                <a href="?action=clear_all" style="color:#555; text-decoration:none; margin-left:15px;" onclick="return confirm('Empty cart?')">全部删除</a>
            </div>
            <div style="display:flex; align-items:center; gap:20px;">
                <div>合计 (<span id="selectedCount">0</span>件): <span class="total-price">RM <span id="grandTotal">0.00</span></span></div>
                <button type="button" onclick="goToCheckout()" class="checkout-btn" id="checkoutBtn" disabled>结算</button>
            </div>
        </div>
    </form>
</div>

<div id="editModal">
    <div class="modal-content">
        <h3>修改规格</h3>
        <input type="hidden" id="editCartId">
        <div style="margin-bottom:15px;">
            <label>Size:</label>
            <select id="selectSize" style="width:100%; padding:8px;"></select>
        </div>
        <div style="margin-bottom:15px;">
            <label>Color:</label>
            <select id="selectColor" style="width:100%; padding:8px;"></select>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="saveVariant()" style="flex:1; background:var(--shopee-red); color:white; border:none; padding:10px; cursor:pointer;">确定</button>
            <button onclick="$('#editModal').hide()" style="flex:1; background:#ccc; border:none; padding:10px; cursor:pointer;">取消</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 全选逻辑
    $('#checkAll, #checkAllBottom').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.item-checkbox, #checkAll, #checkAllBottom').prop('checked', isChecked);
        calculateTotal();
    });

    $('.item-checkbox').on('change', calculateTotal);

    function calculateTotal() {
        let total = 0, count = 0;
        $('.item-checkbox:checked').each(function() {
            let row = $(this).closest('.cart-item');
            total += parseFloat(row.data('price')) * parseInt(row.find('.qty-input').val());
            count++;
        });
        $('#grandTotal').text(total.toFixed(2));
        $('#selectedCount').text(count);
        $('#checkoutBtn').prop('disabled', count === 0);
    }

    // 数量加减
    window.updateQty = function(btn, change) {
        let row = $(btn).closest('.cart-item');
        let input = row.find('.qty-input');
        let newQty = parseInt(input.val()) + change;
        if (newQty < 1) return;
        input.val(newQty);
        row.find('.row-total').text((parseFloat(row.data('price')) * newQty).toFixed(2));
        $.post('unified_cart.php', { ajax_action: 'update_qty', cart_id: row.data('id'), qty: newQty });
        calculateTotal();
    };

    // 弹窗逻辑
    window.openEditModal = function(item) {
        $('#editCartId').val(item.cart_id);
        let sizes = item.all_sizes.split(',');
        let colors = item.all_colours.split(',');
        $('#selectSize').html(sizes.map(s => `<option value="${s.trim()}" ${item.selected_size==s.trim()?'selected':''}>${s.trim()}</option>`));
        $('#selectColor').html(colors.map(c => `<option value="${c.trim()}" ${item.colour==c.trim()?'selected':''}>${c.trim()}</option>`));
        $('#editModal').css('display', 'flex');
    };

    window.saveVariant = function() {
        let data = {
            ajax_action: 'update_variant',
            cart_id: $('#editCartId').val(),
            size: $('#selectSize').val(),
            colour: $('#selectColor').val()
        };
        $.post('unified_cart.php', data, function() { location.reload(); });
    };

    window.goToCheckout = function() {
        $('#cartForm').attr('action', '../checkout/index.php').submit();
    };
});
</script>

<?php require_once '../includes/_foot.php'; ?>