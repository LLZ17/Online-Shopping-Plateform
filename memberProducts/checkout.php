<?php
ob_start();
session_start();
require_once '../includes/db.php';

// --- 逻辑 A: 处理 AJAX Voucher 验证 ---
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

// --- 逻辑 B: 页面加载与订单提交 ---
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$user_id = $_SESSION['user_id'];

$u_stmt = $pdo->prepare("SELECT real_name, email, phone_num, address, postcode, city, state, points, bank_name FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch();

$ids_str = $_GET['ids'] ?? '';
if (empty($ids_str)) {
    echo "<script>alert('No items selected!'); window.location.href='../cart/unified_cart.php';</script>";
    exit();
}
$ids_array = explode(',', $ids_str);
$placeholders = implode(',', array_fill(0, count($ids_array), '?'));

$sql = "SELECT c.cart_id, c.product_id, c.quantity, c.selected_size, c.colour, p.name, p.price, p.product_image, p.category_id 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.cart_id IN ($placeholders) AND c.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge($ids_array, [$user_id]));
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) { header('Location: ../cart/unified_cart.php'); exit(); }

$subtotal_raw = 0;
foreach ($cart_items as $item) {
    $subtotal_raw += $item['price'] * $item['quantity'];
}

$v_stmt = $pdo->prepare("SELECT * FROM vouchers WHERE min_spend <= ?");
$v_stmt->execute([$subtotal_raw]);
$available_vouchers = $v_stmt->fetchAll();

$user_points = $user['points'] ?? 0;
$auto_points_used = min($user_points, floor($subtotal_raw * 100));
$points_discount = $auto_points_used / 100;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $input_email = $_POST['email'] ?? '';
    $allowed_domains = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'icloud.com'];
    $domain = substr(strrchr($input_email, "@"), 1);
    $is_edu = (substr($domain, -4) === '.edu' || str_contains($domain, '.edu.')); 

    if (!in_array($domain, $allowed_domains) && !$is_edu) {
        echo "<script>alert('Error: This email domain is not supported.'); window.history.back();</script>";
        exit();
    }

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
        'points_earned' => 10,
        'email' => $input_email
    ];
    header('Location: toyyibpay_payment.php');
    exit();
}

require_once '../includes/_head.php';
?>

<style>
    .co-wrapper { max-width: 1200px; margin: 30px auto; display: flex; gap: 40px; padding: 0 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .co-left { flex: 2.6; } 
    .co-right { flex: 1.4; } 
    .co-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); margin-bottom: 25px; border: 1px solid #f0f0f0; }
    .co-label { font-size: 11px; color: #aaa; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.5px; }
    .co-data { background: #fbfbfb; padding: 15px; border-radius: 12px; border: 1px solid #eee; font-size: 16px; margin-bottom: 20px; color: #333; }
    
    .v-tabs { display: flex; gap: 10px; margin-bottom: 12px; }
    .v-btn { flex: 1; padding: 12px; border: 1px solid #ddd; background: #fff; cursor: pointer; border-radius: 10px; font-weight: 600; font-size: 13px; transition: 0.2s; }
    .v-btn.active { background: #000; color: #fff; border-color: #000; }
    .v-pane { display: none; padding: 15px; background: #f8f8f8; border-radius: 12px; }
    .v-pane.active { display: block; }

    .sum-item { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f5f5f5; align-items: center; }
    .sum-item img { width: 85px; height: 110px; object-fit: cover; border-radius: 8px; background: #f0f0f0; }
    
    .pts-row { display: flex; justify-content: space-between; align-items: center; background: #fff9e6; padding: 15px; border-radius: 12px; border: 1px solid #ffeeba; margin-bottom: 12px; font-weight: bold; color: #2e7d32; }
    .ship-promo-box { background: #fff0f1; border: 1px dashed #e60012; padding: 15px; border-radius: 12px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
    .add-btn { background: #e60012; color: white; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 800; }
    
    .sum-line { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
    .pay-btn { width: 100%; background: #e60012; color: white; padding: 18px; border: none; border-radius: 15px; font-size: 22px; font-weight: 900; cursor: pointer; margin-top: 15px; }
    .cancel-btn { width: 100%; background: #fff; color: #888; padding: 12px; border: 1px solid #eee; border-radius: 15px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 10px; text-align: center; display: block; text-decoration: none; transition: 0.2s; }
    .cancel-btn:hover { background: #f5f5f5; color: #333; }
    .info-note { font-size: 12px; color: #e60012; margin-top: -10px; margin-bottom: 20px; font-weight: 600; }
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
                <div class="info-note">⚠️ Wrong info? Please edit in <a href="../memberProfile/edit_profile.php" style="color:#e60012; text-decoration:underline;">Profile Settings</a>.</div>

                <span class="co-label">E-Receipt Email</span>
                <input type="email" name="email" class="co-data" style="width:100%; box-sizing:border-box;" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <span class="co-label">Preferred Bank</span>
                <div class="co-data" style="display:flex; justify-content:space-between; align-items:center;">
                    <span>🏦 <?php echo htmlspecialchars($user['bank_name'] ?: 'Not Set'); ?></span>
                    <a href="../memberProfile/edit_profile.php" style="color:#e60012; font-size:12px; font-weight:bold; text-decoration:none;">EDIT</a>
                </div>

                <span class="co-label">Voucher Options</span>
                <div class="v-tabs">
                    <button type="button" class="v-btn active" id="btn_t0" onclick="switchTab(0)">Enter Code</button>
                    <button type="button" class="v-btn" id="btn_t1" onclick="switchTab(1)">My Vouchers</button>
                </div>

                <div id="pane_0" class="v-pane active">
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="code_input" class="v-btn" style="flex:2; text-align:left; cursor:text;" placeholder="e.g. PROMO2026">
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
                
                <p style="font-size: 13px; color: #666; margin-top: 20px; padding: 12px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #e60012;">
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
                <span id="ship_promo_text" style="font-size:13px; color:#e60012; font-weight:600;"></span>
                <a href="../cart/unified_cart.php" class="add-btn">ADD</a>
            </div>

            <div style="max-height:350px; overflow-y:auto; margin-bottom:15px;">
                <?php foreach($cart_items as $item): 
                    // 图片匹配逻辑: 尝试根据颜色名称匹配，如 "10_brown.jpg"
                    $color_part = !empty($item['colour']) ? "_" . strtolower(trim($item['colour'])) : "";
                    $pid = $item['product_id'];
                    $img_with_color = $pid . $color_part . ".jpg";
                    
                    // 如果你的文件命名就是原本的 product_image，那保持原样，
                    // 但你可以尝试让程序寻找特定的颜色图片
                    $display_img = $item['product_image']; 
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

            <div class="sum-line"><span>Subtotal</span><span>RM <?php echo number_format($subtotal_raw, 2); ?></span></div>
            <div class="sum-line"><span>Voucher</span><span id="v_amt_ui" style="color:red; font-weight:bold;">- RM 0.00</span></div>
            <div class="sum-line"><span>Shipping</span><span id="ship_ui">RM 8.00</span></div>
            
            <hr style="border:0; border-top:2px dashed #eee; margin:15px 0;">
            <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                <span style="font-weight:bold;">Total Amount:</span>
                <span id="total_ui" style="font-size:28px; font-weight:900; color:#e60012;">RM 0.00</span>
            </div>

            <button type="submit" form="orderForm" class="pay-btn">Pay RM <span id="btn_amt">0.00</span></button>
            <a href="../cart/unified_cart.php" class="cancel-btn">Cancel Order</a>
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
        if(opt.val() == "0") { vDisc = 0; $('#h_v_id').val(''); }
        else {
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

        // 免邮差额提示
        if(after < 100) {
            let diff = 100 - after;
            $('#ship_promo').show();
            $('#ship_promo_text').html("Add <b>RM " + diff.toFixed(2) + "</b> for Free Shipping!");
        } else {
            $('#ship_promo').show();
            $('#ship_promo_text').html("✅ Free Shipping Unlocked!");
            $('.add-btn').hide();
        }

        $('#v_amt_ui').text('- RM ' + vDisc.toFixed(2));
        $('#ship_ui').text(ship === 0 ? 'FREE' : 'RM ' + ship.toFixed(2));
        $('#total_ui').text('RM ' + final.toFixed(2));
        $('#btn_amt').text(final.toFixed(2));
        $('#h_final').val(final.toFixed(2));
    }

    $(document).ready(calc);
</script>

<?php require_once '../includes/_foot.php'; ob_end_flush(); ?>