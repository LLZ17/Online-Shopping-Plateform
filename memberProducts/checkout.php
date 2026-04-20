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
 * 2. 初始化数据
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

$sql = "SELECT c.cart_id, c.product_id, c.quantity, c.selected_size, c.colour, p.name, p.price, p.product_image 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.cart_id IN ($placeholders) AND c.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge($ids_array, [$user_id]));
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) { header('Location: ../cart/unified_cart.php'); exit(); }

// 核心金额逻辑
$subtotal_raw = 0;
foreach ($cart_items as $item) {
    $subtotal_raw += $item['price'] * $item['quantity'];
}

// 积分逻辑
$user_points = $user['points'] ?? 0;
$auto_points_used = min($user_points, floor($subtotal_raw * 100));
$points_discount = $auto_points_used / 100;
$points_earned = floor($subtotal_raw);

// 免运费进度计算 (门槛 RM 100)
$shipping_threshold = 100;
$remaining_for_free_shipping = $shipping_threshold - $subtotal_raw;

$available_vouchers = $pdo->prepare("SELECT * FROM vouchers WHERE min_spend <= ?");
$available_vouchers->execute([$subtotal_raw]);
$vouchers = $available_vouchers->fetchAll();

require_once '../includes/_head.php';
?>

<style>
    :root { --px-red: #e60012; --px-orange: #ef6c00; }
    .co-wrapper { max-width: 1200px; margin: 40px auto; display: flex; gap: 40px; padding: 0 20px; font-family: 'Inter', sans-serif; }
    .co-left { flex: 2.2; } .co-right { flex: 1.8; } 
    .co-card { background: white; padding: 35px; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #f2f2f2; }
    
    h2, h3 { margin-top: 0; font-weight: 800; color: #111; }
    .co-label { font-size: 12px; color: #999; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .co-data { background: #f9f9f9; padding: 18px; border-radius: 12px; border: 1px solid #eee; font-size: 16px; margin-bottom: 15px; color: #333; }

    /* 免运费进度条样式 */
    .free-shipping-tip { background: #fff8e1; border: 1px solid #ffe082; padding: 15px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: #856404; font-weight: 600; }
    
    .wrong-info-box { display: flex; align-items: center; gap: 8px; margin: -5px 0 25px 5px; font-size: 14px; color: #e60012; }
    .wrong-info-box a { color: #e60012; text-decoration: underline; font-weight: bold; }
    
    .receipt-info-box { display: flex; align-items: center; gap: 12px; padding: 15px 18px; background: #fffcfc; border-left: 4px solid #e60012; border-radius: 4px; margin: 10px 0 25px 0; font-size: 14px; color: #444; }

    .pts-discount-bar { background: #f1f8e9; border: 1px solid #c5e1a5; padding: 15px 20px; border-radius: 15px; color: #2e7d32; font-weight: 800; display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 18px; }
    .reward-banner { background: #fffcf0; border: 1px solid #ffecb3; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 10px; color: #ef6c00; font-weight: 600; margin-bottom: 15px; }

    .v-tabs { display: flex; gap: 12px; margin-bottom: 15px; }
    .v-tab-btn { flex: 1; padding: 14px; border: 1px solid #e0e0e0; background: #fff; cursor: pointer; border-radius: 12px; font-weight: 700; transition: 0.3s; }
    .v-tab-btn.active { background: #000; color: #fff; border-color: #000; }
    .v-content { display: none; padding: 20px; background: #f5f5f5; border-radius: 15px; margin-bottom: 20px; }
    .v-content.active { display: block; }

    .sum-item { display: flex; gap: 20px; padding: 20px 0; border-bottom: 1px solid #f0f0f0; }
    .sum-item img { width: 90px; height: 115px; object-fit: cover; border-radius: 12px; }
    
    /* 按钮组 */
    .btn-group { display: flex; gap: 15px; margin-top: 20px; }
    .pay-btn { flex: 2; background: var(--px-red); color: white; padding: 18px; border: none; border-radius: 15px; font-size: 20px; font-weight: 900; cursor: pointer; box-shadow: 0 8px 20px rgba(230,0,0,0.15); transition: 0.3s; }
    .cancel-btn { flex: 1; background: white; color: #666; padding: 18px; border: 1px solid #ddd; border-radius: 15px; font-size: 18px; font-weight: 700; cursor: pointer; text-align: center; text-decoration: none; transition: 0.3s; }
    .continue-btn { display: block; width: 100%; text-align: center; padding: 15px; background: #333; color: white; border-radius: 12px; text-decoration: none; font-weight: 700; margin-top: 20px; }
</style>

<div class="co-wrapper">
    <div class="co-left">
        <?php if ($remaining_for_free_shipping > 0): ?>
            <div class="free-shipping-tip">
                <span>🚚</span>
                <span>Add <b>RM <?php echo number_format($remaining_for_free_shipping, 2); ?></b> more to get <b>FREE SHIPPING!</b></span>
            </div>
        <?php else: ?>
            <div class="free-shipping-tip" style="background: #e8f5e9; border-color: #a5d6a7; color: #2e7d32;">
                <span>🎉</span>
                <span>Congratulations! You've unlocked <b>FREE SHIPPING!</b></span>
            </div>
        <?php endif; ?>

        <div class="co-card">
            <h2>Shipping & Recipient</h2>
            <hr style="border:0; border-top:1px solid #eee; margin-bottom:30px;">

            <span class="co-label">Full Name</span>
            <div class="co-data"><?php echo htmlspecialchars($user['real_name']); ?></div>

            <span class="co-label">Contact Number</span>
            <div class="co-data"><?php echo htmlspecialchars($user['phone_num']); ?></div>

            <span class="co-label">Shipping Address</span>
            <div class="co-data">
                <?php 
                    $full_address = array_filter([
                        $user['address'] ?? '',
                        $user['postcode'] ?? '',
                        $user['city'] ?? '',
                        $user['state'] ?? ''
                    ]);
                    echo htmlspecialchars(implode(', ', $full_address)); 
                ?>
            </div>

            <div class="wrong-info-box">
                <span style="font-size:16px;">⚠️</span>
                <span>Wrong info? Edit in <a href="../memberProfile/edit_profile.php">Profile Settings</a>.</span>
            </div>

            <form id="orderForm" method="POST" action="toyyibpay_payment.php">
                <span class="co-label">E-Receipt Destination</span>
                <div class="co-data" style="background: #f0f0f0; color: #777;">
                    <?php echo htmlspecialchars($user['email']); ?>
                </div>
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

                <div class="receipt-info-box">
                    <span>📧</span>
                    <span>E-Receipt will be sent to: <strong><?php echo htmlspecialchars($user['email']); ?></strong></span>
                </div>

                <h3 style="margin-top:40px;">Discounts & Vouchers</h3>
                <div class="v-tabs">
                    <button type="button" class="v-tab-btn active" onclick="switchTab(0)">Manual Code</button>
                    <button type="button" class="v-tab-btn" onclick="switchTab(1)">My Vouchers</button>
                </div>

                <div id="tab_0" class="v-content active">
                    <div style="display:flex; gap:12px;">
                        <input type="text" id="v_code_input" class="co-data" style="flex:2; margin-bottom:0;" placeholder="Enter Promo Code">
                        <button type="button" class="v-tab-btn active" style="flex:1;" onclick="applyManualCode()">Apply</button>
                    </div>
                </div>

                <div id="tab_1" class="v-content">
                    <select id="v_select_box" class="co-data" style="width:100%;" onchange="applySelectCode()">
                        <option value="0">-- Select a Voucher --</option>
                        <?php foreach($vouchers as $v): ?>
                            <option value="<?php echo $v['voucher_id']; ?>" data-val="<?php echo $v['discount_value']; ?>" data-method="<?php echo $v['discount_method']; ?>">
                                <?php echo htmlspecialchars($v['code']); ?> (RM <?php echo $v['discount_value']; ?> OFF)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <input type="hidden" name="applied_v_id" id="h_v_id">
                <input type="hidden" name="applied_v_amt" id="h_v_amt" value="0">
                <input type="hidden" name="final_amount" id="h_final">
                <input type="hidden" name="place_order" value="1">
            </form>
        </div>

        <a href="product_list.php" class="continue-btn">🛍️ Keep Shopping</a>
    </div>

    <div class="co-right">
        <div class="co-card" style="position: sticky; top: 20px;">
            <h3>Order Summary</h3>
            <div class="summary-list">
                <?php foreach($cart_items as $item): 
                    $imgs = explode(',', $item['product_image']);
                    $display_img = trim($imgs[0]);
                ?>
                    <div class="sum-item">
                        <img src="../images/<?php echo $display_img; ?>" onerror="this.src='../images/default.jpg'">
                        <div style="flex:1;">
                            <div style="font-weight:800; font-size:15px;"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div style="font-size:13px; color:#888; margin-top:2px;">
                                Size: <strong><?php echo htmlspecialchars($item['selected_size']); ?></strong> | Color: <?php echo htmlspecialchars($item['colour']); ?>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-top:10px;">
                                <span style="font-size:14px; color:#666;">RM <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                                <span style="font-weight:800; font-size:18px;">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="reward-banner" style="margin-top:25px;">
                <span>🎁</span>
                <span>Earn <b style="font-size: 18px; color: #e65100;"><?php echo $points_earned; ?></b> reward points</span>
            </div>

            <div class="pts-discount-bar">
                <span>Points Discount</span>
                <span>- RM <?php echo number_format($points_discount, 2); ?></span>
            </div>

            <div style="font-size:16px; color:#555; line-height:2.2;">
                <div style="display:flex; justify-content:space-between;">
                    <span>Subtotal</span><span style="color:#111; font-weight:700;">RM <?php echo number_format($subtotal_raw, 2); ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; color:var(--px-red);">
                    <span>Voucher Discount</span><span id="v_amt_ui" style="font-weight:700;">- RM 0.00</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Shipping Fee</span><span id="ship_ui" style="color:#111; font-weight:700;">RM 8.00</span>
                </div>
            </div>

            <hr style="border:0; border-top:2px dashed #eee; margin:20px 0;">
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:20px;">
                <span style="font-weight:800; font-size:18px;">Total Payable</span>
                <span id="total_ui" style="font-size:32px; font-weight:900; color:var(--px-red);">RM 0.00</span>
            </div>

            <div class="btn-group">
                <a href="../cart/unified_cart.php" class="cancel-btn">Cancel</a>
                <button type="submit" form="orderForm" class="pay-btn">Confirm & Pay</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const subtotal = <?php echo $subtotal_raw; ?>;
    const ptDisc = <?php echo $points_discount; ?>;
    let vDisc = 0;

    function switchTab(idx) {
        $('.v-tab-btn').removeClass('active'); $('.v-tab-btn').eq(idx).addClass('active');
        $('.v-content').removeClass('active'); $('#tab_'+idx).addClass('active');
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

    function updateTotals() {
        let afterDisc = subtotal - ptDisc - vDisc;
        let shipping = (afterDisc >= 100 || afterDisc <= 0) ? 0 : 8.00;
        let total = Math.max(0, afterDisc + shipping);

        $('#v_amt_ui').text('- RM ' + vDisc.toFixed(2));
        $('#ship_ui').text(shipping === 0 ? 'FREE' : 'RM ' + shipping.toFixed(2));
        $('#total_ui').text('RM ' + total.toFixed(2));
        $('#h_v_amt').val(vDisc.toFixed(2));
        $('#h_final').val(total.toFixed(2));
    }

    $(document).ready(updateTotals);
</script>

<?php require_once '../includes/_foot.php'; ob_end_flush(); ?>