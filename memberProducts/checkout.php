<?php
ob_start();
session_start();
require_once '../includes/db.php';

/**
 * ----------------------------------------------------------------
 * PART 1: AJAX & LOGIC LAYER
 * ----------------------------------------------------------------
 */

// 1.1 AJAX 处理：验证优惠券
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

// 1.2 登录验证
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../login.php'); 
    exit(); 
}
$user_id = $_SESSION['user_id'];

// 1.3 获取用户信息
$u_stmt = $pdo->prepare("SELECT real_name, email, phone_num, address, postcode, city, state, points, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch();

// 1.4 获取选中的商品 ID
$ids_str = $_GET['ids'] ?? '';
if (empty($ids_str)) {
    echo "<script>alert('Your selection is empty!'); window.location.href='../cart/unified_cart.php';</script>";
    exit();
}
$ids_array = explode(',', $ids_str);
$placeholders = implode(',', array_fill(0, count($ids_array), '?'));

// 1.5 获取购物车商品数据及产品图片
$sql = "SELECT c.cart_id, c.product_id, c.quantity, c.selected_size, c.colour, p.name, p.price, p.product_image 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.cart_id IN ($placeholders) AND c.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge($ids_array, [$user_id]));
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) { header('Location: ../cart/unified_cart.php'); exit(); }

// 1.6 计算核心金额
$subtotal_raw = 0;
foreach ($cart_items as $item) {
    $subtotal_raw += $item['price'] * $item['quantity'];
}

// 1.7 获取用户可用的优惠券列表
$v_stmt = $pdo->prepare("SELECT * FROM vouchers WHERE min_spend <= ?");
$v_stmt->execute([$subtotal_raw]);
$available_vouchers = $v_stmt->fetchAll();

// 1.8 积分抵扣逻辑 (100 pts = RM 1.00)
$user_points = $user['points'] ?? 0;
$auto_points_used = min($user_points, floor($subtotal_raw * 100));
$points_discount = $auto_points_used / 100;

// 1.9 本次购买可获得的奖励积分 (消费 RM 1 = 1 积分)
$points_earned = floor($subtotal_raw);

/**
 * ----------------------------------------------------------------
 * PART 2: ORDER SUBMISSION
 * ----------------------------------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $_SESSION['pending_order_data'] = [
        'user_id' => $user_id,
        'cart_ids' => $ids_array,
        'items' => $cart_items,
        'total_price' => $_POST['final_amount'],
        'bank_name' => $user['bank_name'],
        'voucher_id' => $_POST['applied_v_id'],
        'voucher_discount_amount' => $_POST['applied_v_amt'],
        'points_used' => $auto_points_used,
        'points_discount_amount' => $points_discount,
        'points_earned' => $points_earned,
        'email' => $_POST['email']
    ];
    header('Location: toyyibpay_payment.php');
    exit();
}

require_once '../includes/_head.php';
?>

<style>
    :root { 
        --px-red: #e60012; 
        --px-green: #2e7d32; 
        --px-orange: #ef6c00; 
        --px-gold-bg: #fffcf0;
        --px-gold-border: #ffecb3;
    }
    .co-wrapper { max-width: 1200px; margin: 40px auto; display: flex; gap: 40px; padding: 0 20px; font-family: 'Inter', sans-serif; }
    .co-left { flex: 2.5; } 
    .co-right { flex: 1.5; } 
    
    .co-card { background: white; padding: 35px; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); margin-bottom: 30px; border: 1px solid #f2f2f2; }
    .co-card h2, .co-card h3 { margin-top: 0; font-weight: 800; }
    
    .co-label { font-size: 12px; color: #999; font-weight: 700; text-transform: uppercase; margin-bottom: 10px; display: block; }
    .co-data { background: #f9f9f9; padding: 18px; border-radius: 14px; border: 1px solid #eee; font-size: 16px; margin-bottom: 25px; color: #333; }
    
    /* Voucher Styles */
    .v-tabs { display: flex; gap: 12px; margin-bottom: 15px; }
    .v-btn { flex: 1; padding: 14px; border: 1px solid #e0e0e0; background: #fff; cursor: pointer; border-radius: 12px; font-weight: 700; }
    .v-btn.active { background: #000; color: #fff; }
    .v-pane { display: none; padding: 20px; background: #f5f5f5; border-radius: 15px; margin-bottom: 20px; }
    .v-pane.active { display: block; }
    
    /* Item Summary */
    .summary-list { max-height: 400px; overflow-y: auto; padding-right: 10px; margin-bottom: 20px; }
    .sum-item { display: flex; gap: 20px; padding: 20px 0; border-bottom: 1px solid #f0f0f0; }
    .sum-item img { width: 80px; height: 100px; object-fit: cover; border-radius: 12px; }
    
    /* Points Row UI */
    .pts-row { display: flex; justify-content: space-between; align-items: center; background: #f1f8e9; padding: 15px 18px; border-radius: 15px; border: 1px solid #dcedc8; color: var(--px-green); font-weight: 700; margin-bottom: 8px; }
    
    /* Reward Banner (Based on Image 1) */
    .reward-banner { background: var(--px-gold-bg); border: 1px solid var(--px-gold-border); padding: 15px 18px; border-radius: 15px; display: flex; align-items: center; gap: 10px; color: var(--px-orange); font-weight: 600; margin-bottom: 20px; }
    
    .pay-btn { width: 100%; background: var(--px-red); color: white; padding: 20px; border: none; border-radius: 20px; font-size: 22px; font-weight: 900; cursor: pointer; margin-top: 10px; }
    .sum-detail-line { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; color: #666; }
</style>

<div class="co-wrapper">
    <div class="co-left">
        <div class="co-card">
            <h2>Recipient Details</h2>
            <hr style="border:0; border-top:1px solid #eee; margin-bottom:30px;">
            <form id="orderForm" method="POST">
                <div style="display:flex; gap:25px;">
                    <div style="flex:1;">
                        <span class="co-label">Full Name</span>
                        <div class="co-data"><?php echo htmlspecialchars($user['real_name']); ?></div>
                    </div>
                    <div style="flex:1;">
                        <span class="co-label">Contact Number</span>
                        <div class="co-data"><?php echo htmlspecialchars($user['phone_num']); ?></div>
                    </div>
                </div>

                <span class="co-label">Shipping Address</span>
                <div class="co-data">
                    <?php echo htmlspecialchars($user['address'] . ', ' . $user['postcode'] . ' ' . $user['city'] . ', ' . $user['state']); ?>
                </div>

                <span class="co-label">E-Receipt Destination</span>
                <input type="email" name="email" class="co-data" style="width:100%; box-sizing:border-box;" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <div style="margin-top:35px;">
                    <span class="co-label">Promo & Vouchers</span>
                    <div class="v-tabs">
                        <button type="button" class="v-btn active" id="btn_t0" onclick="switchTab(0)">Enter Code</button>
                        <button type="button" class="v-btn" id="btn_t1" onclick="switchTab(1)">Available Vouchers</button>
                    </div>

                    <div id="pane_0" class="v-pane active">
                        <div style="display:flex; gap:12px;">
                            <input type="text" id="code_input" class="v-btn" style="flex:2; text-align:left;" placeholder="e.g. SAVE10">
                            <button type="button" class="v-btn active" style="flex:1;" onclick="ajaxApplyCode()">Validate</button>
                        </div>
                    </div>

                    <div id="pane_1" class="v-pane">
                        <select id="v_select" class="v-btn" style="width:100%;" onchange="applyVSelect()">
                            <option value="0">-- Choose a voucher --</option>
                            <?php foreach($available_vouchers as $v): ?>
                                <option value="<?php echo $v['voucher_id']; ?>" data-val="<?php echo $v['discount_value']; ?>" data-method="<?php echo $v['discount_method']; ?>">
                                    <?php echo htmlspecialchars($v['code']); ?> (RM <?php echo $v['discount_value']; ?> <?php echo $v['discount_method'] == '*' ? '%' : 'Off'; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="v_msg" style="margin-top:10px; font-size:13px; font-weight:700;"></div>
                </div>

                <input type="hidden" name="applied_v_id" id="h_v_id">
                <input type="hidden" name="applied_v_amt" id="h_v_amt" value="0">
                <input type="hidden" name="final_amount" id="h_final">
                <input type="hidden" name="place_order" value="1">
            </form>
        </div>
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
                            <div style="font-weight:700; font-size:14px;"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div style="font-size:12px; color:#888;"><?php echo htmlspecialchars($item['selected_size']); ?> | <?php echo htmlspecialchars($item['colour']); ?></div>
                            <div style="display:flex; justify-content:space-between; margin-top:5px; font-weight:800;">
                                <span style="color:#666;">RM <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                                <span>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pts-row">
                <span>Points Discount</span>
                <span>- RM <?php echo number_format($points_discount, 2); ?></span>
            </div>

            <div class="reward-banner">
                <span style="font-size: 18px;">🎁</span>
                <span>Earn <b style="font-size: 18px; color: #e65100;"><?php echo $points_earned; ?></b> reward points with this purchase</span>
            </div>

            <div style="padding: 10px 0;">
                <div class="sum-detail-line">
                    <span>Subtotal</span>
                    <span style="color:#333; font-weight:700;">RM <?php echo number_format($subtotal_raw, 2); ?></span>
                </div>
                <div class="sum-detail-line">
                    <span>Voucher Discount</span>
                    <span id="v_amt_ui" style="color:var(--px-red); font-weight:700;">- RM 0.00</span>
                </div>
                <div class="sum-detail-line">
                    <span>Estimated Shipping</span>
                    <span id="ship_ui" style="color:#333; font-weight:700;">RM 8.00</span>
                </div>
            </div>

            <hr style="border:0; border-top:2px dashed #eee; margin:20px 0;">
            
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:20px;">
                <span style="font-weight:800; font-size:18px;">Total Amount</span>
                <span id="total_ui" style="font-size:32px; font-weight:900; color:var(--px-red);">RM 0.00</span>
            </div>

            <button type="submit" form="orderForm" class="pay-btn">Proceed to Pay</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const subtotal = <?php echo $subtotal_raw; ?>;
    const ptDisc = <?php echo $points_discount; ?>;
    let vDisc = 0;

    function switchTab(t) {
        $('.v-btn').removeClass('active'); $('#btn_t' + t).addClass('active');
        $('.v-pane').removeClass('active'); $('#pane_' + t).addClass('active');
        vDisc = 0; $('#h_v_id').val(''); $('#h_v_amt').val(0); calculateFinal();
    }

    function ajaxApplyCode() {
        let code = $('#code_input').val();
        $.post('checkout.php', { ajax: 'check_v', code: code, subtotal: subtotal }, function(data) {
            let res = JSON.parse(data);
            if(res.success) {
                vDisc = (res.method === '-') ? parseFloat(res.val) : subtotal * (parseFloat(res.val)/100);
                $('#h_v_id').val(res.id); $('#h_v_amt').val(vDisc.toFixed(2));
                $('#v_msg').css('color','green').text('Voucher applied!');
                calculateFinal();
            } else {
                $('#v_msg').css('color','red').text(res.message);
                vDisc = 0; calculateFinal();
            }
        });
    }

    function applyVSelect() {
        let opt = $('#v_select option:selected');
        if(opt.val() == "0") { vDisc = 0; } 
        else {
            let val = parseFloat(opt.data('val')), method = opt.data('method');
            vDisc = (method === '-') ? val : subtotal * (val/100);
            $('#h_v_id').val(opt.val());
        }
        $('#h_v_amt').val(vDisc.toFixed(2));
        calculateFinal();
    }

    function calculateFinal() {
        let currentSub = subtotal - ptDisc - vDisc;
        if(currentSub < 0) currentSub = 0;
        let shipping = (currentSub >= 100) ? 0 : 8.00;
        let final = currentSub + shipping;

        $('#v_amt_ui').text('- RM ' + vDisc.toFixed(2));
        $('#ship_ui').text(shipping === 0 ? 'FREE' : 'RM ' + shipping.toFixed(2));
        $('#total_ui').text('RM ' + final.toFixed(2));
        $('#h_final').val(final.toFixed(2));
    }

    $(document).ready(function() { calculateFinal(); });
</script>

<?php require_once '../includes/_foot.php'; ob_end_flush(); ?>