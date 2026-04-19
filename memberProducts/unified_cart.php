<?php
// --- 1. 逻辑处理层 (必须在最顶部) ---
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first'); window.location.href='../login.php';</script>";
    exit();
}
$user_id = $_SESSION['user_id'];

// 处理单品删除
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $user_id]);
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

// 处理清空购物车
if (isset($_GET['action']) && $_GET['action'] == 'clear_all') {
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

// 处理批量删除 (POST)
if (isset($_POST['batch_delete']) && !empty($_POST['items'])) {
    $ids = $_POST['items'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge($ids, [$user_id]);
    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id IN ($placeholders) AND user_id = ?");
    $stmt->execute($params);
    echo "<script>window.location.href='unified_cart.php';</script>";
    exit();
}

// 处理 AJAX 更新 (数量或规格)
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

// --- 2. 渲染层 ---
require_once '../includes/_head.php'; 

$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.product_image, p.stock, p.size as all_sizes, p.colour as all_colours
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();
?>

<style>
    :root { --shopee-red: #e60012; --text-gray: #757575; }
    body { background-color: #f5f5f5; font-family: sans-serif; }
    .cart-container { max-width: 1100px; margin: 30px auto; padding-bottom: 120px; }
    
    input[type="checkbox"] { width: 19px; height: 19px; accent-color: var(--shopee-red); cursor: pointer; }

    /* 表头 */
    .cart-header { display: grid; grid-template-columns: 50px 1fr 120px 150px 150px 100px; background: white; padding: 15px 20px; margin-bottom: 15px; font-weight: bold; color: #888; border-radius: 2px; }

    /* 商品行 */
    .cart-item { display: grid; grid-template-columns: 50px 100px 1fr 120px 150px 150px 100px; align-items: center; background: white; padding: 20px; margin-bottom: 2px; }
    
    .product-img { width: 80px; height: 100px; object-fit: cover; border: 1px solid #f2f2f2; }
    .product-info { padding: 0 20px; }
    .product-name { font-weight: 500; color: #333; text-decoration: none; font-size: 15px; display: block; margin-bottom: 8px; }

    /* Variation 按钮 */
    .variant-btn { background: #fafafa; border: 1px dashed #ccc; padding: 5px 10px; font-size: 13px; color: var(--text-gray); cursor: pointer; display: inline-block; }
    .variant-btn:hover { border-color: var(--shopee-red); color: var(--shopee-red); }

    /* 数量控制 */
    .qty-control { display: flex; border: 1px solid #ddd; width: fit-content; }
    .qty-btn { background: white; border: none; width: 32px; height: 32px; cursor: pointer; font-size: 18px; }
    .qty-btn:disabled { color: #ccc; cursor: not-allowed; }
    .qty-input { width: 45px; text-align: center; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; outline: none; background: #fff; font-weight: bold; pointer-events: none; }

    /* 底部结算栏 - 修复走位问题 */
    .footer-bar { position: fixed; bottom: 0; width: 1100px; left: 50%; transform: translateX(-50%); background: white; display: flex; justify-content: space-between; align-items: center; padding: 12px 30px; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); z-index: 1000; }
    
    .footer-left { display: flex; align-items: center; }
    .select-all-box { display: flex; align-items: center; gap: 12px; margin-right: 30px; min-width: 120px; }
    .footer-ops { display: flex; gap: 20px; } 
    
    .footer-right { display: flex; align-items: center; gap: 30px; }
    .total-info { display: flex; align-items: baseline; text-align: right; }
    .total-label { font-size: 14px; color: #333; margin-right: 10px; }
    .total-amount { color: var(--shopee-red); font-size: 26px; font-weight: bold; }
    .currency { color: var(--shopee-red); font-size: 18px; font-weight: bold; margin-right: 4px; }

    .checkout-btn { background: var(--shopee-red); color: white; padding: 12px 60px; border: none; font-size: 16px; font-weight: bold; cursor: pointer; border-radius: 2px; min-width: 200px; }
    .checkout-btn:disabled { background: #ccc; cursor: not-allowed; }

    .batch-btn { background: none; border: none; color: #333; cursor: pointer; font-size: 14px; padding: 0; }
    .batch-btn:hover { color: var(--shopee-red); }

    /* 规格 Modal */
    #editModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10001; justify-content:center; align-items:center; }
    .modal-content { background:white; padding:30px; border-radius:4px; width:380px; }
    .modal-content h3 { margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px; }
    .modal-content select { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ddd; }
</style>

<div class="cart-container">
    <form id="cartForm" method="POST">
        <div class="cart-header">
            <input type="checkbox" id="checkAllTop">
            <span>Product</span>
            <span style="text-align: center;">Unit Price</span>
            <span style="text-align: center;">Quantity</span>
            <span style="text-align: center;">Subtotal</span>
            <span style="text-align: center;">Action</span>
        </div>

        <?php foreach ($cart_items as $item): 
            $imgs = explode(',', $item['product_image']);
            $display_img = trim($imgs[0]); 
            $user_color = strtolower(trim($item['colour'])); 
            foreach ($imgs as $img) {
                if ($user_color !== '' && strpos(strtolower(trim($img)), $user_color) !== false) {
                    $display_img = trim($img); break;
                }
            }
        ?>
            <div class="cart-item" data-price="<?php echo $item['price']; ?>" data-id="<?php echo $item['cart_id']; ?>" data-stock="<?php echo $item['stock']; ?>">
                <input type="checkbox" name="items[]" value="<?php echo $item['cart_id']; ?>" class="item-checkbox">
                
                <img src="../images/<?php echo $display_img; ?>" class="product-img" onerror="this.src='../images/default.jpg'">
                
                <div class="product-info">
                    <a href="detail.php?id=<?php echo $item['product_id']; ?>" class="product-name"><?php echo htmlspecialchars($item['name']); ?></a>
                    <div class="variant-btn" onclick='openVariantModal(<?php echo json_encode($item); ?>)'>
                        Variation: <?php echo $item['colour'] ?: 'Default'; ?>, <?php echo $item['selected_size'] ?: 'N/A'; ?> ▼
                    </div>
                </div>

                <div style="text-align: center;">RM <?php echo number_format($item['price'], 2); ?></div>

                <div style="display: flex; justify-content: center;">
                    <div class="qty-control">
                        <button type="button" class="qty-btn" onclick="updateQty(this, -1)">-</button>
                        <input type="text" value="<?php echo $item['quantity']; ?>" class="qty-input" readonly>
                        <button type="button" class="qty-btn" onclick="updateQty(this, 1)" <?php echo ($item['quantity'] >= $item['stock']) ? 'disabled' : ''; ?>>+</button>
                    </div>
                </div>

                <div style="text-align: center; color: var(--shopee-red); font-weight: bold;">
                    RM <span class="row-total"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>

                <div style="text-align: center;">
                    <a href="?action=remove&id=<?php echo $item['cart_id']; ?>" onclick="return confirm('Delete?')" style="color: #666; text-decoration: none; font-size: 14px;">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="footer-bar">
            <div class="footer-left">
                <div class="select-all-box">
                    <input type="checkbox" id="checkAllBottom">
                    <label for="checkAllBottom" style="cursor: pointer;">Select All</label>
                </div>
                <div class="footer-ops">
                    <button type="submit" name="batch_delete" class="batch-btn" onclick="return confirm('Delete selected?')">Delete Selected</button>
                    <a href="?action=clear_all" class="batch-btn" style="text-decoration:none;" onclick="return confirm('Clear all?')">Clear All</a>
                </div>
            </div>
            
            <div class="footer-right">
                <div class="total-info">
                    <span class="total-label">Total (<span id="selectedCount">0</span> items):</span>
                    <span class="currency">RM</span>
                    <span id="grandTotal" class="total-amount">0.00</span>
                </div>
                <button type="button" onclick="submitToCheckout()" class="checkout-btn" id="checkoutBtn" disabled>Check Out</button>
            </div>
        </div>
    </form>
</div>

<div id="editModal">
    <div class="modal-content">
        <h3>Change Variation</h3>
        <input type="hidden" id="modalCartId">
        <label>Size:</label>
        <select id="modalSizeSelect"></select>
        <label>Color:</label>
        <select id="modalColorSelect"></select>
        <div style="display:flex; gap:10px; margin-top:10px;">
            <button onclick="saveNewVariant()" style="flex:1; background:var(--shopee-red); color:white; border:none; padding:12px; cursor:pointer; font-weight:bold;">Confirm</button>
            <button onclick="$('#editModal').hide()" style="flex:1; background:#eee; border:none; padding:12px; cursor:pointer;">Cancel</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 1. 全选同步逻辑
    $('#checkAllTop, #checkAllBottom').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.item-checkbox, #checkAllTop, #checkAllBottom').prop('checked', isChecked);
        updateTotals();
    });

    $('.item-checkbox').on('change', function() {
        let allCount = $('.item-checkbox').length;
        let checkedCount = $('.item-checkbox:checked').length;
        $('#checkAllTop, #checkAllBottom').prop('checked', allCount === checkedCount);
        updateTotals();
    });

    // 2. 更新总额
    function updateTotals() {
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

    // 3. 数量加减 + 库存限制
    window.updateQty = function(btn, change) {
        let row = $(btn).closest('.cart-item');
        let input = row.find('.qty-input');
        let plusBtn = row.find('.qty-btn').last();
        let stock = parseInt(row.data('stock'));
        let newQty = parseInt(input.val()) + change;

        if (newQty < 1 || newQty > stock) return;

        input.val(newQty);
        row.find('.row-total').text((parseFloat(row.data('price')) * newQty).toFixed(2));
        plusBtn.prop('disabled', newQty >= stock);

        $.post('unified_cart.php', { ajax_action: 'update_qty', cart_id: row.data('id'), qty: newQty });
        updateTotals();
    };

    // 4. 修改规格 (Modal)
    window.openVariantModal = function(item) {
        $('#modalCartId').val(item.cart_id);
        let sizes = item.all_sizes ? item.all_sizes.split(',') : ['N/A'];
        $('#modalSizeSelect').html(sizes.map(s => `<option value="${s.trim()}" ${item.selected_size == s.trim() ? 'selected' : ''}>${s.trim()}</option>`));
        let colors = item.all_colours ? item.all_colours.split(',') : ['Default'];
        $('#modalColorSelect').html(colors.map(c => `<option value="${c.trim()}" ${item.colour == c.trim() ? 'selected' : ''}>${c.trim()}</option>`));
        $('#editModal').css('display', 'flex');
    };

    window.saveNewVariant = function() {
        let data = {
            ajax_action: 'update_variant',
            cart_id: $('#modalCartId').val(),
            size: $('#modalSizeSelect').val(),
            colour: $('#modalColorSelect').val()
        };
        $.post('unified_cart.php', data, function() { location.reload(); });
    };

    // 5. 核心：只提交勾选的 ID 到 Checkout
    window.submitToCheckout = function() {
        let selectedIds = [];
        $('.item-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert("Please select items to check out.");
            return;
        }

        // 通过 URL 传参跳转
        window.location.href = 'checkout.php?ids=' + selectedIds.join(',');
    };
});
</script>

<?php require_once '../includes/_foot.php'; ?>