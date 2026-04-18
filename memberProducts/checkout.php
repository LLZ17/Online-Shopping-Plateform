<?php
ob_start();
session_start();
require_once '../includes/db.php';
require_once '../includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

// 1. 获取用户信息 (包含 real_name, phone_num, email)
$stmt = $pdo->prepare("SELECT real_name, email, phone_num, address, postcode, city, state, points, bank_name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. 获取购物车商品 (核心：必须从 cart 表读取 size 和 colour)
$stmt = $pdo->prepare("
    SELECT c.quantity, c.size, c.colour, p.name, p.price, p.product_image 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) { header('Location: ../cart.php'); exit(); }

$full_address = ($user['address'] ?? '') . ", " . ($user['postcode'] ?? '') . " " . ($user['city'] ?? '') . ", " . ($user['state'] ?? '');
$user_points = $user['points'] ?? 0;

// 3. 计算金额与动态赚取积分
$subtotal_raw = 0;
$total_earned_points = 0; 
foreach ($cart_items as $item) {
    $subtotal_raw += $item['price'] * $item['quantity'];
    $total_earned_points += (15 * $item['quantity']); // 同步主页逻辑
}

// 自动积分抵扣逻辑
$auto_points_used = min($user_points, floor($subtotal_raw * 100));
$points_discount = $auto_points_used / 100;

// 4. 提交处理 (Email 逻辑修改)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $input_email = $_POST['email'];
    
    // 检查 Email 是否在数据库中存在 (允许用户更改为其他合法已注册的 Email)
    $check_email = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_email->execute([$input_email]);
    $email_exists = $check_email->fetch();

    if (!filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email format.";
    } elseif (!$email_exists) {
        $error = "This email is not registered in our system.";
    } else {
        $_SESSION['pending_order'] = [
            'total' => $_POST['final_amount'],
            'points_used' => $auto_points_used,
            'points_earned' => $total_earned_points,
            'email' => $input_email
        ];
        header('Location: toyyibpay_payment.php');
        exit();
    }
}
?>

<style>
    .checkout-wrap { max-width: 1200px; margin: 30px auto; display: flex; gap: 30px; padding: 0 20px; align-items: flex-start; }
    .left-section { flex: 2.2; } /* 个人资料更大 */
    .right-section { flex: 1; }   /* Summary 更小 */
    
    .ui-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 25px; }
    .label-title { font-size: 11px; color: #888; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .data-val { background: #f9f9f9; padding: 15px; border-radius: 10px; border: 1px solid #eee; font-size: 16px; margin-bottom: 20px; }

    .item-box { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f5f5f5; }
    .item-box img { width: 50px; height: 65px; object-fit: cover; border-radius: 5px; }
    .item-meta { font-size: 11px; color: #777; margin-top: 3px; }

    .v-group { display: flex; gap: 8px; margin-top: 5px; }
    .v-input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
    .v-btn { background: #333; color: white; border: none; padding: 0 15px; border-radius: 8px; cursor: pointer; }

    .points-row { display: flex; justify-content: space-between; align-items: center; background: #fff9e6; padding: 12px; border-radius: 10px; border: 1px solid #ffeeba; margin-bottom: 15px; font-weight: bold; }
    .sum-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
    
    .pay-btn-red { width: 100%; background: #e60012; color: white; padding: 18px; border: none; border-radius: 12px; font-size: 20px; font-weight: 800; cursor: pointer; margin-top: 15px; }
</style>

<div class="checkout-wrap">
    <div class="left-section">
        <div class="ui-card">
            <h2 style="margin-top:0; border-bottom: 2px solid #eee; padding-bottom: 10px;">Billing Details</h2>
            
            <?php if($error): ?><div style="color:red; background:#fff5f5; padding:10px; border-radius:8px; margin-bottom:15px;">⚠️ <?php echo $error; ?></div><?php endif; ?>

            <form id="mainOrderForm" method="POST">
                <div style="display: flex; gap: 20px;">
                    <div style="flex:1;">
                        <span class="label-title">Recipient Name</span>
                        <div class="data-val"><?php echo htmlspecialchars($user['real_name'] ?: 'Not Set'); ?></div>
                    </div>
                    <div style="flex:1;">
                        <span class="label-title">Phone Number</span>
                        <div class="data-val"><?php echo htmlspecialchars($user['phone_num'] ?: 'Not Set'); ?></div>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="label-title">Email Address (Registered)</label>
                    <input type="email" name="email" class="data-val" style="width:100%; box-sizing:border-box;" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div style="margin-bottom:20px;">
                    <span class="label-title">Shipping Address</span>
                    <div class="data-val" style="min-height:50px;"><?php echo htmlspecialchars($full_address); ?></div>
                </div>

                <div style="margin-bottom:20px;">
                    <span class="label-title">Preferred Bank</span>
                    <div class="data-val" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>🏦 <?php echo htmlspecialchars($user['bank_name'] ?: 'None'); ?></span>
                        <a href="../memberProfile/edit_profile.php" style="font-size:12px; color:red; font-weight:bold;">+ Add Bank</a>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <span class="label-title">Voucher Code</span>
                    <div class="v-group">
                        <input type="text" id="promo_code" class="v-input" placeholder="Enter code">
                        <button type="button" class="v-btn" onclick="applyPromo()">Apply</button>
                    </div>
                </div>

                <input type="hidden" name="final_amount" id="final_amt_val">
                <input type="hidden" name="place_order" value="1">
            </form>
        </div>

        <div style="background: #e3f2fd; color: #1976d2; padding: 15px; border-radius: 12px; font-size: 14px; border: 1px solid #b3e5fc;">
            <i class="fas fa-info-circle"></i> An e-receipt will be sent to <strong><?php echo htmlspecialchars($user['email']); ?></strong> upon success.
        </div>
    </div>

    <div class="right-section">
        <div class="ui-card" style="padding: 20px;">
            <h3 style="margin:0 0 15px 0;">Order Summary</h3>
            
            <div style="max-height: 250px; overflow-y: auto; margin-bottom:15px;">
                <?php foreach($cart_items as $item): ?>
                    <div class="item-box">
                        <img src="../images/<?php echo htmlspecialchars($item['product_image']); ?>" onerror="this.src='../images/default.jpg'">
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:13px;"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-meta">
                                Size: <?php echo !empty($item['size']) ? htmlspecialchars($item['size']) : 'N/A'; ?> | 
                                Colour: <?php echo !empty($item['colour']) ? htmlspecialchars($item['colour']) : 'N/A'; ?>
                            </div>
                            <div style="font-size:12px; margin-top:3px;">RM <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="points-row">
                <span style="font-size:13px; color:#2e7d32;">Points Discount (<?php echo $auto_points_used; ?> pts)</span>
                <span style="color:#2e7d32;">- RM <?php echo number_format($points_discount, 2); ?></span>
            </div>
            <p style="font-size:11px; color:#856404; margin-bottom:15px;">✨ You will earn <b><?php echo $total_earned_points; ?></b> points!</p>

            <div class="sum-row">
                <span>Voucher</span>
                <span id="v_disc_ui" style="color:red;">- RM 0.00</span>
            </div>
            <div class="sum-row">
                <span>Shipping</span>
                <span id="ship_ui">RM 8.00</span>
            </div>
            
            <div style="border-top: 1px dashed #ddd; padding-top:10px; margin-top:10px; display:flex; justify-content:space-between; align-items:baseline;">
                <span style="font-weight:bold;">Subtotal:</span>
                <span id="total_ui" style="font-size:22px; font-weight:900; color:#e60012;">RM 0.00</span>
            </div>

            <button type="submit" form="mainOrderForm" class="pay-btn-red">Pay RM <span id="btn_amt_ui">0.00</span></button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const subtotal = <?php echo $subtotal_raw; ?>;
    const ptsDisc = <?php echo $points_discount; ?>;
    let vDisc = 0;

    function calc() {
        let after = subtotal - ptsDisc - vDisc;
        if(after < 0) after = 0;
        let ship = (after >= 100 || after <= 0) ? 0 : 8.00;
        let final = after + ship;

        $('#v_disc_ui').text('- RM ' + vDisc.toFixed(2));
        $('#ship_ui').text(ship === 0 ? 'FREE' : 'RM ' + ship.toFixed(2));
        $('#total_ui').text('RM ' + final.toFixed(2));
        $('#btn_amt_ui').text(final.toFixed(2));
        $('#final_amt_val').val(final.toFixed(2));
    }

    function applyPromo() {
        // 这里对接你的 check_voucher.php
        alert("Voucher check logic triggered for: " + $('#promo_code').val());
    }

    $(document).ready(calc);
</script>