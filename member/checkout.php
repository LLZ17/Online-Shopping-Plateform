<?php
ob_start();
session_start();
require_once '../includes/db.php';

// 1. AJAX 验证优惠券
if (isset($_POST['ajax']) && $_POST['ajax'] === 'check_v') {
    $code = $_POST['code'] ?? '';
    $subtotal = $_POST['subtotal'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = ? AND min_spend <= ?");
    $stmt->execute([$code, $subtotal]);
    $v = $stmt->fetch();
    echo json_encode($v ? ['success'=>true, 'id'=>$v['voucher_id'], 'method'=>$v['discount_method'], 'val'=>$v['discount_value']] : ['success'=>false]);
    exit;
}

// 2. 检查登录和参数
if (!isset($_SESSION['user_id'])) { header('Location: ../login.php'); exit(); }
$user_id = $_SESSION['user_id'];
$ids_str = $_GET['ids'] ?? '';
if (empty($ids_str)) { die("Error: Cart selection is empty."); }

// 3. 获取用户和购物车数据
$u_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch();

$ids_array = explode(',', $ids_str);
$placeholders = implode(',', array_fill(0, count($ids_array), '?'));
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.product_image FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.cart_id IN ($placeholders)");
$stmt->execute($ids_array);
$cart_items = $stmt->fetchAll();

$subtotal = 0;
foreach ($cart_items as $item) { $subtotal += $item['price'] * $item['quantity']; }

$points_earned = floor($subtotal);
$points_discount = min(($user['points'] ?? 0), floor($subtotal * 100)) / 100;

require_once '../includes/_head.php';
?>

<div class="co-wrapper" style="max-width: 1100px; margin: 40px auto; display: flex; gap: 30px; padding: 0 20px;">
    <div style="flex: 2;">
        <form id="mainOrderForm" method="POST" action="toyyibpay_payment.php">
            <div style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                <h2>Shipping Information</h2>
                <div style="margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <strong>Recipient:</strong> <?php echo htmlspecialchars($user['real_name']); ?><br>
                    <strong>Address:</strong> <?php echo htmlspecialchars($user['address'] . ', ' . $user['Postcode'] . ' ' . $user['City']); ?>
                </div>
                
                <div style="margin-top: 20px;">
                    <label>Confirm Email:</label>
                    <input type="email" name="checkout_email" value="<?php echo $user['email']; ?>" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;" required>
                </div>

                <input type="hidden" name="cart_ids" value="<?php echo htmlspecialchars($ids_str); ?>">
                <input type="hidden" name="final_amount" id="h_final">
                <input type="hidden" name="v_id" id="h_v_id">
                <input type="hidden" name="v_amt" id="h_v_amt" value="0">
                <input type="hidden" name="pts_used" value="<?php echo ($points_discount * 100); ?>">
                <input type="hidden" name="pts_earned" value="<?php echo $points_earned; ?>">
                <input type="hidden" name="pts_discount_amt" value="<?php echo $points_discount; ?>">

                <button type="submit" style="width: 100%; background: #e60012; color: #fff; padding: 20px; border: none; border-radius: 12px; font-size: 20px; font-weight: 900; margin-top: 30px; cursor: pointer;">
                    Pay Now (RM <span id="btn_total">0.00</span>)
                </button>
            </div>
        </form>
    </div>

    <div style="flex: 1;">
        <div style="background: #fff; padding: 25px; border-radius: 20px; border: 1px solid #eee;">
            <h3>Order Summary</h3>
            <?php foreach($cart_items as $item): ?>
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <img src="../images/<?php echo explode(',', $item['product_image'])[0]; ?>" style="width: 60px; height: 75px; object-fit: cover; border-radius: 8px;">
                    <div style="font-size: 14px;">
                        <strong><?php echo $item['name']; ?></strong><br>
                        Qty: <?php echo $item['quantity']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <hr>
            <div style="line-height: 2;">
                <div style="display:flex; justify-content:space-between;"><span>Subtotal:</span><span>RM <?php echo number_format($subtotal, 2); ?></span></div>
                <div style="display:flex; justify-content:space-between; color: #2e7d32;"><span>Points Disc:</span><span>- RM <?php echo number_format($points_discount, 2); ?></span></div>
                <div style="display:flex; justify-content:space-between; color: #e60012;"><span>Voucher:</span><span id="v_ui">- RM 0.00</span></div>
                <div style="display:flex; justify-content:space-between;"><span>Shipping:</span><span id="ship_ui">RM 8.00</span></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size: 24px; font-weight: 900; color: #e60012; margin-top: 15px;">
                <span>Total:</span><span id="total_ui">RM 0.00</span>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const sub = <?php echo $subtotal; ?>;
    const ptD = <?php echo $points_discount; ?>;
    function calc() {
        let vD = parseFloat($('#h_v_amt').val()) || 0;
        let after = sub - ptD - vD;
        let ship = (after >= 100 || after <= 0) ? 0 : 8.00;
        let total = Math.max(0, after + ship);
        $('#total_ui, #btn_total').text(total.toFixed(2));
        $('#h_final').val(total.toFixed(2));
        $('#ship_ui').text(ship === 0 ? 'FREE' : 'RM ' + ship.toFixed(2));
    }
    $(document).ready(calc);
</script>
<?php require_once '../includes/_foot.php'; ob_end_flush(); ?>