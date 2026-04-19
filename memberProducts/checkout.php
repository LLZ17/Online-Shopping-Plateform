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

// 1. 获取用户信息 (real_name, phone_num, email)
$stmt = $pdo->prepare("SELECT real_name, email, phone_num, address, postcode, city, state, points, bank_name FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. 获取购物车商品 (注意：selected_size 是你 SQL 里的字段名)
$stmt = $pdo->prepare("
    SELECT c.quantity, c.selected_size, c.colour, p.name, p.price, p.product_image 
    FROM cart c 
    JOIN products p ON c.product_id = p.product_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) { header('Location: ../cart.php'); exit(); }

$full_address = ($user['address'] ?? '') . ", " . ($user['postcode'] ?? '') . " " . ($user['city'] ?? '') . ", " . ($user['state'] ?? '');
$user_points = $user['points'] ?? 0;

// 3. 计算金额与动态赚取积分 (每件 15 分)
$subtotal_raw = 0;
$total_earned_points = 0; 
foreach ($cart_items as $item) {
    $subtotal_raw += $item['price'] * $item['quantity'];
    $total_earned_points += (15 * $item['quantity']); 
}

// 自动积分抵扣
$auto_points_used = min($user_points, floor($subtotal_raw * 100));
$points_discount = $auto_points_used / 100;

// 4. 处理付款提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $input_email = $_POST['email'];
    
    // 检查 Email 是否存在于系统
    $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->execute([$input_email]);
    
    if (!$check->fetch()) {
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
    .checkout-layout { max-width: 1200px; margin: 30px auto; display: flex; gap: 40px; padding: 0 20px; }
    .left-profile { flex: 2.8; } /* 个人资料区域显著加大 */
    .right-summary { flex: 1.2; } /* Summary 区域缩小 */
    
    .ui-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-bottom: 25px; }
    .label-title { font-size: 11px; color: #999; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 1px; }
    .data-field { background: #f8f9fa; padding: 15px; border-radius: 12px; border: 1px solid #eee; font-size: 16px; margin-bottom: 20px; font-weight: 500; }

    /* 商品列表样式 */
    .item-row { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f0f0f0; align-items: center; }
    .item-row img { width: 70px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
    .item-details { flex: 1; }
    .item-meta { font-size: 12px; color: #666; margin-top: 4px; }

    /* Voucher & Bank 按钮 */
    .v-group { display: flex; gap: 10px; margin-top: 5px; }
    .v-input { flex: 1; padding: 12px; border: 1.5px solid #eee; border-radius: 10px; }
    .v-btn { background: #000; color: #fff; border: none; padding: 0 20px; border-radius: 10px; cursor: pointer; font-weight: bold; }
    .btn-action { display: inline-block; margin-top: 10px; padding: 10px 20px; background: #f0f0f0; color: #333; text-decoration: none; border-radius: 10px; font-size: 14px; font-weight: bold; border: 1px solid #ddd; }

    /* 积分与金额：严格同一行 */
    .points-row { display: flex; justify-content: space-between; align-items: center; background: #fff9e6; padding: 15px; border-radius: 12px; border: 1px solid #ffeeba; margin-bottom: 10px; }
    .sum-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
    
    .btn-pay-main { width: 100%; background: #e60012; color: white; padding: 20px; border: none; border-radius: 15px; font-size: 22px; font-weight: 900; cursor: pointer; margin-top: 15px; box-shadow: 0 8px 20px rgba(230,0,18,0.3); }
</style>

<div class="checkout-layout">
    <div class="left-profile">
        <div class="ui-card">
            <h2 style="margin-top:0; font-size: 28px;">Shipping & Recipient</h2>
            <hr style="border:0; border-top:1px solid #eee; margin-bottom:25px;">
            
            <?php if($error): ?><div style="color:red; background:#fff5f5; padding:15px; border-radius:10px; margin-bottom:20px; font-weight:bold;">⚠️ <?php echo $error; ?></div><?php endif; ?>

            <form id="orderSubmitForm" method="POST">
                <div style="display: flex; gap: 25px;">
                    <div style="flex:1;">
                        <span class="label-title">Full Name</span>
                        <div class="data-field"><?php echo htmlspecialchars($user['real_name'] ?: 'N/A'); ?></div>
                    </div>
                    <div style="flex:1;">
                        <span class="label-title">Phone Number</span>
                        <div class="data-field"><?php echo htmlspecialchars($user['phone_num'] ?: 'N/A'); ?></div>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="label-title">Educational / Registered Email</label>
                    <input type="email" name="email" class="data-field" style="width:100%; box-sizing:border-box;" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div style="margin-bottom:20px;">
                    <span class="label-title">Complete Address</span>
                    <div class="data-field" style="min-height:60px;"><?php echo htmlspecialchars($full_address); ?></div>
                </div>

                <div style="margin-bottom:20px;">
                    <span class="label-title">Preferred Payment Bank</span>
                    <div class="data-field" style="display:flex; justify-content:space-between; align-items:center;">
                        <span>🏦 <?php echo htmlspecialchars($user['bank_name'] ?: 'Not Linked'); ?></span>
                    </div>
                    <a href="../memberProfile/edit_profile.php" class="btn-action">+ Add / Edit Bank Info</a>
                </div>

                <div style="margin-bottom:25px;">
                    <span class="label-title">Voucher Code</span>
                    <div class="v-group">
                        <input type="text" id="promo_input" class="v-input" placeholder="Enter code here">
                        <button type="button" class="v-btn">Apply Code</button>
                    </div>
                </div>

                <input type="hidden" name="final_amount" id="form_final_amt">
                <input type="hidden" name="place_order" value="1">
            </form>
        </div>

        <div style="background: #e1f5fe; color: #0288d1; padding: 20px; border-radius: 15px; font-size: 16px; border: 1px solid #b3e5fc; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-envelope-open-text"></i>
            <span>An e-receipt will be sent to <strong><?php echo htmlspecialchars($user['email']); ?></strong> upon successful checkout.</span>
        </div>
    </div>

    <div class="right-summary">
        <div class="ui-card" style="padding: 25px;">
            <h3 style="margin:0 0 20px 0; font-size: 20px;">Order Summary</h3>
            
            <div style="max-height: 300px; overflow-y: auto; margin-bottom:20px; padding-right:5px;">
                <?php foreach($cart_items as $item): ?>
                    <div class="item-row">
                        <img src="../images/<?php echo htmlspecialchars($item['product_image']); ?>" onerror="this.src='../images/default.jpg'">
                        <div class="item-details">
                            <div style="font-weight:700; font-size:14px;"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="item-meta">
                                Size: <?php echo htmlspecialchars($item['selected_size'] ?? 'N/A'); ?> | 
                                Color: <?php echo htmlspecialchars($item['colour'] ?? 'N/A'); ?>
                            </div>
                            <div style="font-size:13px; margin-top:5px; font-weight:bold;">RM <?php echo number_format($item['price'], 2); ?> <span style="font-weight:normal; color:#999;">x<?php echo $item['quantity']; ?></span></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="points-row">
                <span style="font-size:13px; color:#2e7d32; font-weight:bold;">Points Used (<?php echo $auto_points_used; ?> pts)</span>
                <span style="color:#2e7d32; font-weight:bold;">- RM <?php echo number_format($points_discount, 2); ?></span>
            </div>
            <p style="font-size:12px; color:#a0522d; margin-bottom:20px; font-weight:600;">✨ Earn <b><?php echo $total_earned_points; ?></b> points from this order!</p>

            <div class="sum-row">
                <span>Voucher Discount</span>
                <span style="color:red; font-weight:bold;">- RM 0.00</span>
            </div>
            <div class="sum-row">
                <span>Estimated Shipping</span>
                <span id="shipping_ui">RM 8.00</span>
            </div>
            
            <div style="border-top: 2px dashed #eee; padding-top:15px; margin-top:15px; display:flex; justify-content:space-between; align-items:flex-end;">
                <span style="font-weight:800; font-size:18px;">Total Pay:</span>
                <span id="grand_total_ui" style="font-size:28px; font-weight:900; color:#e60012;">RM 0.00</span>
            </div>

            <button type="submit" form="orderSubmitForm" class="btn-pay-main">Checkout RM <span id="btn_price">0.00</span></button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const rawSubtotal = <?php echo $subtotal_raw; ?>;
    const pointsDeduct = <?php echo $points_discount; ?>;

    function runCalc() {
        let afterDisc = rawSubtotal - pointsDeduct;
        if(afterDisc < 0) afterDisc = 0;
        
        let shipping = (afterDisc >= 100 || afterDisc <= 0) ? 0 : 8.00;
        let finalAmt = afterDisc + shipping;

        $('#shipping_ui').text(shipping === 0 ? 'FREE' : 'RM ' + shipping.toFixed(2));
        $('#grand_total_ui').text('RM ' + finalAmt.toFixed(2));
        $('#btn_price').text(finalAmt.toFixed(2));
        $('#form_final_amt').val(finalAmt.toFixed(2));
    }

    $(document).ready(runCalc);
</script>

<?php require_once '../includes/_foot.php'; ob_end_flush(); ?>