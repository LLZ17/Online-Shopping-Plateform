<?php
ob_start();
session_start();
require_once '../includes/db.php';

/**
 * 1. AJAX 逻辑处理层 (优惠券验证)
 */
if (isset($_POST['ajax']) && $_POST['ajax'] === 'check_v') {
    $code = $_POST['code'] ?? '';
    $subtotal = $_POST['subtotal'] ?? 0;
    
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND min_spend <= ?");
    $stmt->execute([$code, $subtotal]);
    $v = $stmt->fetch();
    
    if ($v) {
        echo json_encode([
            'success' => true, 
            'id' => $v['voucher_id'], 
            'method' => $v['discount_method'], 
            'val' => $v['discount_value']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Voucher invalid or minimum spend not met.']);
    }
    exit;
}

/**
 * 2. 处理表单提交 (存入 Session 并跳转到支付页)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $ids_str = $_POST['cart_ids_hidden'] ?? '';
    if (empty($ids_str)) {
        echo "<script>alert('Your selection is empty!'); window.location.href='../cart/unified_cart.php';</script>";
        exit();
    }
    
    $ids_array = explode(',', $ids_str);
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    
    $sql = "SELECT c.cart_id, c.product_id, c.quantity, p.price FROM cart c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE c.cart_id IN ($placeholders) AND c.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($ids_array, [$_SESSION['user_id']]));
    $cart_items = $stmt->fetchAll();
    
    $subtotal_raw = 0;
    foreach ($cart_items as $item) {
        $subtotal_raw += $item['price'] * $item['quantity'];
    }
    
    $u_stmt = $pdo->prepare("SELECT points FROM users WHERE user_id = ?");
    $u_stmt->execute([$_SESSION['user_id']]);
    $user_data = $u_stmt->fetch();

    $user_points = $user_data['points'] ?? 0;
    $auto_points_used = min($user_points, floor($subtotal_raw * 100));
    $points_discount = $auto_points_used / 100;
    $points_earned = floor($subtotal_raw);
    
    $voucher_amt = floatval($_POST['applied_v_amt'] ?? 0);
    $after_discount = $subtotal_raw - $points_discount - $voucher_amt;
    $shipping = ($after_discount >= 100 || $after_discount <= 0) ? 0 : 8.00;
    $final_amount = max(0, $after_discount + $shipping);
    
    $_SESSION['pending_order'] = [
        'cart_ids'               => $ids_str,
        'final_total'            => $final_amount,
        'voucher_id'             => $_POST['applied_v_id'] ?: null,
        'voucher_discount'       => $voucher_amt,
        'points_used'            => $auto_points_used,
        'points_discount_amount' => $points_discount,
        'points_earned'          => $points_earned
    ];
    
    header('Location: toyyibpay_payment.php');
    exit();
}

/**
 * 3. 初始化页面数据
 */
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$user_id = $_SESSION['user_id'];

$u_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch();

$ids_str = $_GET['ids'] ?? '';
if (empty($ids_str)) {
    echo "<script>alert('Your selection is empty!'); window.location.href='../cart/unified_cart.php';</script>";
    exit();
}
$ids_array = explode(',', $ids_str);
$placeholders = implode(',', array_fill(0, count($ids_array), '?'));

// SQL 核心：获取购物车信息及产品颜色数组
$sql = "SELECT c.cart_id, c.product_id, c.quantity, c.selected_size, c.colour, 
               p.name, p.price, p.product_image, p.stock, p.colour as all_colours 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.cart_id IN ($placeholders) AND c.user_id = ? AND c.is_selected = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge($ids_array, [$user_id]));
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) { header('Location: ../cart/unified_cart.php'); exit(); }

$subtotal_raw = 0;
foreach ($cart_items as $item) {
    $subtotal_raw += $item['price'] * $item['quantity'];
}

$user_points = $user['points'] ?? 0;
$auto_points_used = min($user_points, floor($subtotal_raw * 100));
$points_discount = $auto_points_used / 100;
$points_earned = floor($subtotal_raw);

$v_stmt = $pdo->prepare("SELECT * FROM vouchers WHERE min_spend <= ?");
$v_stmt->execute([$subtotal_raw]);
$available_vouchers = $v_stmt->fetchAll();

require_once '../includes/_head.php';
?>

<style>
    :root { --px-red: #e60012; --px-orange: #ef6c00; }
    .co-wrapper { max-width: 1200px; margin: 40px auto; display: flex; gap: 40px; padding: 0 20px; font-family: 'Inter', sans-serif; }
    .co-left { flex: 2.2; } .co-right { flex: 1.8; } 
    .co-card { background: white; padding: 35px; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #f2f2f2; }
    h2, h3 { margin-top: 0; font-weight: 800; color: #111; }
    .co-label { font-size: 12px; color: #999; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .co-data { background: #f9f9f9; padding: 18px; border-radius: 12px; border: 1px solid #eee; font-size: 16px; margin-bottom: 15px; color: #333; line-height: 1.5; }
    .wrong-info-box { display: flex; align-items: center; gap: 8px; margin: -5px 0 25px 5px; font-size: 14px; color: var(--px-red); }
    .wrong-info-box a { color: var(--px-red); text-decoration: underline; font-weight: bold; }
    .receipt-info-box { display: flex; align-items: center; gap: 12px; padding: 15px 18px; background: #fffcfc; border-left: 4px solid var(--px-red); border-radius: 4px; margin: 10px 0 25px 0; font-size: 14px; color: #444; }
    .pts-discount-bar { background: #f1f8e9; border: 1px solid #c5e1a5; padding: 15px 20px; border-radius: 15px; color: #2e7d32; font-weight: 800; display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 18px; }
    .reward-banner { background: #fffcf0; border: 1px solid #ffecb3; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 10px; color: #ef6c00; font-weight: 600; margin-bottom: 15px; }
    .ship-prog-container { margin-bottom: 20px; }
    .ship-prog-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .ship-prog-bar { width: 100%; height: 6px; background: #eee; border-radius: 10px; overflow: hidden; }
    .ship-prog-fill { width: 0%; height: 100%; background: linear-gradient(90deg, #ff9800, #e60012); transition: width 0.6s ease; }
    .v-tabs { display: flex; gap: 12px; margin-bottom: 15px; }
    .v-tab-btn { flex: 1; padding: 14px; border: 1px solid #e0e0e0; background: #fff; cursor: pointer; border-radius: 12px; font-weight: 700; }
    .v-tab-btn.active { background: #000; color: #fff; border-color: #000; }
    .v-content { display: none; padding: 20px; background: #f5f5f5; border-radius: 15px; margin-bottom: 20px; }
    .v-content.active { display: block; }
    .sum-item { display: flex; gap: 20px; padding: 20px 0; border-bottom: 1px solid #f0f0f0; }
    .sum-item img { width: 90px; height: 115px; object-fit: cover; border-radius: 12px; }
    .pay-btn { flex: 2; background: var(--px-red); color: white; padding: 18px; border: none; border-radius: 15px; font-size: 20px; font-weight: 900; cursor: pointer; }
    .cancel-btn { flex: 1; background: white; color: #666; padding: 18px; border: 1px solid #ddd; border-radius: 15px; font-size: 18px; font-weight: 700; text-align: center; text-decoration: none; }
</style>

<div class="co-wrapper">
    <div class="co-left">
        <div class="co-card">
            <h2>Shipping Details</h2>
            <hr style="border:0; border-top:1px solid #eee; margin-bottom:30px;">
            <span class="co-label">Full Name:</span>
            <div class="co-data"><?php echo htmlspecialchars($user['real_name']); ?></div>
            <span class="co-label">Contact Number:</span>
            <div class="co-data"><?php echo htmlspecialchars($user['phone_num']); ?></div>
            <span class="co-label">Shipping Address:</span>
            <div class="co-data">
                <?php 
                    $addr = trim($user['address'] ?? '');
                    $post = trim($user['Postcode'] ?? '');
                    $city = trim($user['City'] ?? '');
                    $stat = trim($user['State'] ?? '');
                    $full = $addr . ($post ? ", $post" : "") . ($city ? ", $city" : "") . ($stat ? ", $stat" : "");
                    echo htmlspecialchars($full) ?: "No address found.";
                ?>
            </div>
            <div class="wrong-info-box">
                <span>⚠️ Wrong shipping detail? Please change in <a href="../member/profile.php">My Account</a>.</span>
            </div>
            <form id="orderForm" method="POST">
                <span class="co-label">Email Address:</span>
                <input type="email" name="email" class="co-data" style="width:100%; box-sizing:border-box;" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <div class="receipt-info-box">
                    <span>📧 E-Receipt will be sent automatically to: <strong><?php echo htmlspecialchars($user['email']); ?></strong></span>
                </div>
                <h3 style="margin-top:40px;">Vouchers</h3>
                <div class="v-tabs">
                    <button type="button" class="v-tab-btn active" onclick="switchTab(0)">My Code</button>
                    <button type="button" class="v-tab-btn" onclick="switchTab(1)">My Vouchers</button>
                </div>
                <div id="tab_0" class="v-content active">
                    <div style="display:flex; gap:12px;">
                        <input type="text" id="v_code_input" class="co-data" style="flex:2; margin-bottom:0;" placeholder="Enter Promo Code">
                        <button type="button" class="v-tab-btn" style="flex:1; background:#000; color:#fff;" onclick="applyManualCode()">Apply</button>
                    </div>
                </div>
                <div id="tab_1" class="v-content">
                    <select id="v_select_box" class="co-data" style="width:100%;" onchange="applySelectCode()">
                        <option value="0">-- Select a Voucher --</option>
                        <?php foreach($available_vouchers as $v): ?>
                            <option value="<?php echo $v['voucher_id']; ?>" data-val="<?php echo $v['discount_value']; ?>" data-method="<?php echo $v['discount_method']; ?>">
                                <?php echo htmlspecialchars($v['code']); ?> (RM <?php echo $v['discount_value']; ?> OFF)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="applied_v_id" id="h_v_id">
                <input type="hidden" name="applied_v_amt" id="h_v_amt" value="0">
                <input type="hidden" name="cart_ids_hidden" value="<?php echo htmlspecialchars($ids_str); ?>">
                <input type="hidden" name="place_order" value="1">
            </form>
        </div>
    </div>

    <div class="co-right">
        <div class="co-card" style="position: sticky; top: 20px;">
            <div class="ship-prog-container">
                <div class="ship-prog-header">
                    <span id="shipping_msg" style="font-size:13px; font-weight:700;"></span>
                </div>
                <div class="ship-prog-bar"><div id="shipping_bar" class="ship-prog-fill"></div></div>
            </div>

            <h3>Order Summary</h3>
            <div class="summary-list">
                <?php foreach($cart_items as $item): 
                    // 精准取图逻辑
                    $imgs_arr = array_filter(array_map('trim', explode(',', $item['product_image'])));
                    $cols_arr = array_filter(array_map('trim', explode(',', $item['all_colours'])));
                    $selected_col = trim($item['colour']);
                    
                    $img_index = 0; 
                    foreach ($cols_arr as $idx => $color_name) {
                        if (strcasecmp($color_name, $selected_col) === 0) {
                            $img_index = $idx;
                            break;
                        }
                    }
                    $display_img = $imgs_arr[$img_index] ?? ($imgs_arr[0] ?? 'default.jpg');
                ?>
                    <div class="sum-item">
                        <img src="../images/<?php echo htmlspecialchars($display_img); ?>" onerror="this.src='../images/default.jpg'">
                        <div style="flex:1;">
                            <div style="font-weight:800; font-size:15px;"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div style="font-size:13px; color:#888;">Size: <?php echo htmlspecialchars($item['selected_size']); ?> | Color: <?php echo htmlspecialchars($item['colour']); ?></div>
                            <div style="display:flex; justify-content:space-between; margin-top:10px;">
                                <span style="font-size:14px; color:#666;">RM <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                                <span style="font-weight:800;">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pts-discount-bar" style="margin-top:25px;">
                <span>Points Discount:</span><span>- RM <?php echo number_format($points_discount, 2); ?></span>
            </div>

            <div style="line-height:2.2; font-size:15px;">
                <div style="display:flex; justify-content:space-between;"><span>Subtotal:</span><b>RM <?php echo number_format($subtotal_raw, 2); ?></b></div>
                <div style="display:flex; justify-content:space-between; color:var(--px-red);"><span>Voucher:</span><span id="v_amt_ui">- RM 0.00</span></div>
                <div style="display:flex; justify-content:space-between;"><span>Shipping:</span><span id="ship_ui">RM 8.00</span></div>
            </div>

            <hr style="border:0; border-top:2px dashed #eee; margin:20px 0;">
            <div style="display:flex; justify-content:space-between; align-items:baseline;">
                <span style="font-weight:800;">Total:</span>
                <span id="total_ui" style="font-size:32px; font-weight:900; color:var(--px-red);">RM 0.00</span>
            </div>

            <div style="display:flex; gap:15px; margin-top:25px;">
                <a href="../cart/unified_cart.php" class="cancel-btn">Cancel</a>
                <button type="submit" form="orderForm" class="pay-btn">Pay Now</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const subtotal = <?php echo $subtotal_raw; ?>;
    const ptDisc = <?php echo $points_discount; ?>;
    let vDisc = 0;

    function updateTotals() {
        let afterDisc = subtotal - ptDisc - vDisc;
        let threshold = 100;
        let prog = Math.min(100, (subtotal / threshold) * 100);
        
        if(subtotal >= threshold) {
            $('#shipping_msg').html('🎉 Free shipping unlocked!');
            $('#shipping_bar').css('width', '100%');
        } else {
            $('#shipping_msg').html('Add <b>RM '+(threshold-subtotal).toFixed(2)+'</b> more for Free Shipping!');
            $('#shipping_bar').css('width', prog + '%');
        }

        let shipping = (afterDisc >= threshold || afterDisc <= 0) ? 0 : 8.00;
        let total = Math.max(0, afterDisc + shipping);

        $('#v_amt_ui').text('- RM ' + vDisc.toFixed(2));
        $('#ship_ui').text(shipping === 0 ? 'FREE' : 'RM ' + shipping.toFixed(2));
        $('#total_ui').text('RM ' + total.toFixed(2));
        $('#h_v_amt').val(vDisc.toFixed(2));
    }

    function switchTab(i) {
        $('.v-tab-btn').removeClass('active').eq(i).addClass('active');
        $('.v-content').removeClass('active').eq(i).addClass('active');
        vDisc = 0; $('#h_v_id').val(''); updateTotals();
    }

    function applyManualCode() {
        let code = $('#v_code_input').val();
        if(!code) return;
        $.post('checkout.php', {ajax:'check_v', code:code, subtotal:subtotal}, function(data){
            let res = JSON.parse(data);
            if(res.success) {
                vDisc = (res.method === '-') ? parseFloat(res.val) : subtotal * (parseFloat(res.val)/100);
                $('#h_v_id').val(res.id);
                updateTotals();
            } else { alert(res.message); }
        });
    }

    function applySelectCode() {
        let opt = $('#v_select_box option:selected');
        if(opt.val() == "0") { vDisc = 0; } 
        else {
            let val = parseFloat(opt.data('val')), method = opt.data('method');
            vDisc = (method === '-') ? val : subtotal * (val/100);
            $('#h_v_id').val(opt.val());
        }
        updateTotals();
    }

    $(document).ready(updateTotals);
</script>
<?php require_once '../includes/_foot.php'; ob_end_flush(); ?>