<?php
ob_start();
session_start();
require_once '../includes/db.php';

// --- 处理 AJAX Voucher 验证 ---
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
        echo json_encode(['success' => false, 'message' => 'Invalid code or min spend not met']);
    }
    exit;
}

// 登录检查
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../login.php'); 
    exit(); 
}
$user_id = $_SESSION['user_id'];

// 获取用户信息
$u_stmt = $pdo->prepare("SELECT real_name, email, phone_num, address, postcode, city, state, points, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch();

// 获取购物车选中的 IDs
$ids_str = $_GET['ids'] ?? '';
if (empty($ids_str)) {
    echo "<script>alert('No items selected!'); window.location.href='../cart/unified_cart.php';</script>";
    exit();
}
$ids_array = explode(',', $ids_str);
$placeholders = implode(',', array_fill(0, count($ids_array), '?'));

// 获取选中的商品详情
$sql = "SELECT c.cart_id, c.product_id, c.quantity, c.selected_size, c.colour, p.name, p.price, p.product_image 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.cart_id IN ($placeholders) AND c.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge($ids_array, [$user_id]));
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) { header('Location: ../cart/unified_cart.php'); exit(); }

// 计算初始 Subtotal
$subtotal_raw = 0;
foreach ($cart_items as $item) {
    $subtotal_raw += $item['price'] * $item['quantity'];
}

// 获取可用优惠券
$v_stmt = $pdo->prepare("SELECT * FROM vouchers WHERE min_spend <= ?");
$v_stmt->execute([$subtotal_raw]);
$available_vouchers = $v_stmt->fetchAll();

// 积分计算 (100积分 = RM 1.00)
$user_points = $user['points'] ?? 0;
$auto_points_used = min($user_points, floor($subtotal_raw * 100));
$points_discount = $auto_points_used / 100;

// 【关键：Reward Point 奖励计算】
// 消费 RM 1 获得 1 积分奖励
$points_earned = floor($subtotal_raw);

// 处理订单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // 准备存入 Session 的数据供 ToyyibPay 使用
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
        'points_earned' => $points_earned, // 存入奖励积分
        'email' => $_POST['email']
    ];
    header('Location: toyyibpay_payment.php');
    exit();
}

require_once '../includes/_head.php';
?>

<style>
    :root { --px-red: #e60012; --px-green: #2e7d32; }
    .co-wrapper { max-width: 1200px; margin: 30px auto; display: flex; gap: 40px; padding: 0 20px; font-family: sans-serif; }
    .co-left { flex: 2.6; } 
    .co-right { flex: 1.4; } 
    .co-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); margin-bottom: 25px; border: 1px solid #f0f0f0; }
    .co-label { font-size: 11px; color: #aaa; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.5px; }
    .co-data { background: #fbfbfb; padding: 15px; border-radius: 12px; border: 1px solid #eee; font-size: 16px; margin-bottom: 20px; color: #333; }
    
    /* Voucher 样式 */
    .v-tabs { display: flex; gap: 10px; margin-bottom: 12px; }
    .v-btn { flex: 1; padding: 12px; border: 1px solid #ddd; background: #fff; cursor: pointer; border-radius: 10px; font-weight: 600; font-size: 13px; }
    .v-btn.active { background: #000; color: #fff; border-color: #000; }
    .v-pane { display: none; padding: 15px; background: #f8f8f8; border-radius: 12px; }
    .v-pane.active { display: block; }

    /* 商品行 */
    .sum-item { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f5f5f5; align-items: center; }
    .sum-item img { width: 85px; height: 110px; object-fit: cover; border-radius: 8px; background: #f0f0f0; }
    
    /* 积分与优惠提示 */
    .pts-row { display: flex; justify-content: space-between; align-items: center; background: #fff9e6; padding: 15px; border-radius: 12px; border: 1px solid #ffeeba; margin-bottom: 12px; font-weight: bold; color: var(--px-green); }
    .ship-promo-box { background: #fff0f1; border: 1px dashed var(--px-red); padding: 15px; border-radius: 12px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
    .add-btn { background: var(--px-red); color: white; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 800; }
    
    .pay-btn { width: 100%; background: var(--px-red); color: white; padding: 18px; border: none; border-radius: 15px; font-size: 22px; font-weight: 900; cursor: pointer; margin-top: 15px; }
    .info-note { font-size: 12px; color: var(--px-red); margin-top: -10px; margin-bottom: 20px; font-weight: 600; }
</style>

<div class="co-wrapper">
    <div class="co-left">
        <div class="co-card">
            <h2 style="margin-top:0; font-size: 26px;">Recipient Details</h2>
            <hr style="border:0; border-top:1px solid #eee; margin-bottom:25px;">

            <form id="orderForm" method="POST">
                <div style="display:flex; gap:20px;">
                    <div style="flex:1;">
                        <span class="co-label">Full Name</span>
                        <div class="co-data"><?php echo htmlspecialchars($user['real_name']); ?></div>
                    </div>
                    <div style="flex:1;">
                        <span class="co-label">Phone</span>
                        <div class="co-data"><?php echo htmlspecialchars($user['phone_num']); ?></div>
                    </div>
                </div>

                <span class="co-label">Shipping Address</span>
                <div class="co-data" style="min-height:50px;"><?php echo htmlspecialchars($user['address'] . ', ' . $user['postcode'] . ' ' . $user['city'] . ', ' . $user['state']); ?></div>
                
                <div class="info-note">⚠️ Wrong info? Please edit in <a href="../memberProfile/edit_profile.php" style="color:var(--px-red); text-decoration:underline;">Profile Settings</a>.</div>

                <span class="co-label">E-Receipt Email</span>
                <input type="email" name="email" class="co-data" style="width:100%; box-sizing:border-box;" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <span class="co-label">Preferred Bank</span>
                <div class="co-data" style="display:flex; justify-content:space-between; align-items:center;">
                    <span>🏦 <?php echo htmlspecialchars($user['bank_name'] ?: 'Not Set'); ?></span>
                    <a href="../memberProfile/edit_profile.php" style="color:var(--px-red); font-size:12px; font-weight:bold; text-decoration:none;">EDIT</a>
                </div>

                <span class="co-label">Voucher Options</span>
                <div class="v-tabs">
                    <button type="button" class="v-btn active" id="btn_t0" onclick="switchTab(0)">Enter Code</button>
                    <button type="button" class="v-btn" id="btn_t1" onclick="switchTab(1)">My Vouchers</button>
                </div>

                <div id="pane_0" class="v-pane active">
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="code_input" class="v-btn" style="flex:2; text-align:left; cursor:text;" placeholder="Enter Promo Code">
                        <button type="button" class="v-btn active" style="flex:1;" onclick="ajaxApplyCode()">Apply</button>
                    </div>
                </div>

                <div id="pane_1" class="v-pane">
                    <select id="v_select" class="v-btn" style="width:100%; text-align:left;" onchange="applyVSelect()">
                        <option value="0">-- Select from your list --</option>
                        <?php foreach($available_vouchers as $v): ?>
                            <option value="<?php echo $v['voucher_id']; ?>" data-val="<?php echo $v['discount_value']; ?>" data-method="<?php echo $v['discount_method']; ?>">
                                <?php echo htmlspecialchars($v['code']); ?> (RM <?php echo $v['discount_value']; ?> <?php echo $v['discount_method'] == '*' ? '%' : 'Off'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="v_msg" style="margin-top:8px; font-size:12px; font-weight:bold;"></div>

                <p style="font-size: 13px; color: #666; margin-top: 20px; padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid var(--px-red);">
                    📧 E-Receipt will be sent to: <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                </p>

                <input type="hidden" name="applied_v_id" id="h_v_id">
                <input type="hidden" name="applied_v_amt" id="h_v_amt" value="0">
                <input type="hidden" name="final_amount" id="h_final">
                <input type="hidden" name="place_order" value="1">
            </form>
        </div>
    </div>

    <div class="co-right">
        <div class="co-card">
            <h3 style="margin-top:0;">Order Summary</h3>
            
            <div id="ship_promo" class="ship-promo-box">
                <span id="ship_promo_text" style="font-size:13px; color:var(--px-red); font-weight:600;"></span>
                <a href="../cart/unified_cart.php" class="add-btn">ADD</a>
            </div>

            <div style="max-height:350px; overflow-y:auto; margin-bottom:15px;">
                <?php foreach($cart_items as $item): 
                    $imgs = explode(',', $item['product_image']);
                    $display_img = trim($imgs[0]); 
                    $user_color = strtolower(trim($item['colour'])); 
                    if ($user_color !== '') {
                        foreach ($imgs as $img) {
                            if (strpos(strtolower(trim($img)), $user_color) !== false) {
                                $display_img = trim($img); 
                                break;
                            }
                        }
                    }
                ?>
                    <div class="sum-item">
                        <img src="../images/<?php echo $display_img; ?>" onerror="this.src='../images/default.jpg'">
                        <div style="flex:1;">
                            <div style="font-weight:700; font-size:14px;"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div style="font-size:12px; color:#888;">
                                Size: <?php echo htmlspecialchars($item['selected_size']); ?> | Color: <?php echo htmlspecialchars($item['colour']); ?>
                            </div>
                            <div style="font-weight:bold; font-size:14px; margin-top:5px; display:flex; justify-content:space-between;">
                                <span>RM <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                                <span style="color:#000;">RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pts-row">
                <span>Points Discount</span>
                <span>- RM <?php echo number_format($points_discount, 2); ?></span>
            </div>

            <div style="background: #fff8e1; border: 1px solid #ffecb3; padding: 12px; border-radius: 12px; margin-top: -8px; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; color: #f57c00; font-weight: bold; font-size: 14px;">
                <span style="font-size: 18px;">🎁</span>
                <span>Earn <span style="font-size:18px; color: #ef6c00;"><?php echo $points_earned; ?></span> reward points with this purchase</span>
            </div>

            <div style="padding: 10px 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color:#666;">Subtotal</span>
                    <span style="font-weight:600;">RM <?php echo number_format($subtotal_raw, 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color:#666;">Voucher Discount</span>
                    <span id="v_amt_ui" style="color:var(--px-red); font-weight:bold;">- RM 0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color:#666;">Shipping Fee</span>
                    <span id="ship_ui" style="font-weight:600;">RM 8.00</span>
                </div>
            </div>
            
            <hr style="border:0; border-top:2px dashed #eee; margin:15px 0;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                <span style="font-weight:bold; font-size:16px;">Total Amount:</span>
                <span id="total_ui" style="font-size:28px; font-weight:900; color:var(--px-red);">RM 0.00</span>
            </div>

            <button type="submit" form="orderForm" class="pay-btn">Pay RM <span id="btn_amt">0.00</span></button>
            <a href="../cart/unified_cart.php" style="display:block; text-align:center; margin-top:15px; color:#888; text-decoration:none; font-size:14px;">Cancel & Return to Cart</a>
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
        // 切换标签重置 Voucher
        vDisc = 0; $('#h_v_id').val(''); $('#h_v_amt').val(0); $('#v_msg').text('');
        calc();
    }

    function ajaxApplyCode() {
        let code = $('#code_input').val();
        if(!code) return;
        $.post('checkout.php', { ajax: 'check_v', code: code, subtotal: subtotal }, function(response) {
            let res = JSON.parse(response);
            if(res.success) {
                vDisc = (res.method === '-') ? parseFloat(res.val) : subtotal * (parseFloat(res.val)/100);
                $('#h_v_id').val(res.id); $('#h_v_amt').val(vDisc.toFixed(2));
                $('#v_msg').css('color','green').text('Success: Voucher applied!');
                calc();
            } else {
                $('#v_msg').css('color','red').text(res.message);
                vDisc = 0; calc();
            }
        });
    }

    function applyVSelect() {
        let opt = $('#v_select option:selected');
        if(opt.val() == "0") {
            vDisc = 0; $('#h_v_id').val('');
        } else {
            let val = parseFloat(opt.data('val'));
            let method = opt.data('method');
            vDisc = (method === '-') ? val : subtotal * (val/100);
            $('#h_v_id').val(opt.val());
        }
        $('#h_v_amt').val(vDisc.toFixed(2));
        calc();
    }

    function calc() {
        let after = subtotal - ptDisc - vDisc;
        if(after < 0) after = 0;
        
        let ship = (after >= 100) ? 0 : 8.00;
        let final = after + ship;

        // 更新免邮提示
        if(after < 100) {
            let diff = 100 - after;
            $('#ship_promo').show();
            $('#ship_promo_text').html("Add <b>RM " + diff.toFixed(2) + "</b> for Free Shipping!");
            $('.add-btn').show();
        } else {
            $('#ship_promo').show();
            $('#ship_promo_text').html("✅ Free Shipping Unlocked!");
            $('.add-btn').hide();
        }

        // 更新 UI
        $('#v_amt_ui').text('- RM ' + vDisc.toFixed(2));
        $('#ship_ui').text(ship === 0 ? 'FREE' : 'RM ' + ship.toFixed(2));
        $('#total_ui').text('RM ' + final.toFixed(2));
        $('#btn_amt').text(final.toFixed(2));
        $('#h_final').val(final.toFixed(2));
    }

    $(document).ready(function(){
        calc();
    });
</script>

<?php require_once '../includes/_foot.php'; ob_end_flush(); ?>