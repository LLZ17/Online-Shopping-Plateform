<?php
/**
 * PXX Mall - Pure Email Verification System
 * Focus: PHPMailer Integration & 60s Expiry Logic
 */

// 1. 引入老师提供的 PHPMailer 核心文件
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 核心函数：发送验证邮件 ---
function sendOTP($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $u = $stmt->fetch();

    $code = sprintf("%06d", mt_rand(0, 999999)); // 生成6位随机码
    $expiry = date("Y-m-d H:i:s", strtotime("+1 minute")); // 1分钟后失效
    
    // 存入数据库：这是验证的唯一依据
    $pdo->prepare("UPDATE users SET email_code = ?, email_expiry = ? WHERE user_id = ?")
        ->execute([$code, $expiry, $user_id]);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '你的Gmail@gmail.com'; // 🔥 填入你的 Gmail
        $mail->Password   = '你的16位AppPassword';   // 🔥 填入你的 App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('noreply-system@pxxmall.com', 'PXX System');
        $mail->addAddress($u['email']);
        $mail->isHTML(true);
        $mail->Subject = "Your Verification Code: $code";
        $mail->Body    = "Your security code is <b>$code</b>. It will expire in 60 seconds.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// 逻辑 1：处理验证提交
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_now'])) {
    $user_input = $_POST['otp_input'];
    
    $check = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE user_id = ?");
    $check->execute([$user_id]);
    $row = $check->fetch();

    if ($row && $row['email_code'] === $user_input) {
        if (strtotime($row['email_expiry']) >= time()) {
            // 验证成功
            $pdo->prepare("UPDATE users SET email_verified = 1, email_code = NULL WHERE user_id = ?")->execute([$user_id]);
            echo "<script>alert('Verified Successfully!'); window.location.href='profile.php';</script>";
            exit();
        } else {
            $msg = "Code expired. Please resend.";
        }
    } else {
        $msg = "Invalid verification code.";
    }
}

// 逻辑 2：处理重发请求 (AJAX)
if (isset($_GET['action']) && $_GET['action'] == 'resend') {
    if (sendOTP($pdo, $user_id)) { exit('success'); } else { exit('failed'); }
}

// 首次加载页面自动发信
$init = $pdo->prepare("SELECT email_code FROM users WHERE user_id = ?");
$init->execute([$user_id]);
$curr = $init->fetch();
if (!$curr['email_code']) {
    sendOTP($pdo, $user_id);
}

// 获取用户信息用于显示
$stmt_u = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt_u->execute([$user_id]);
$user_info = $stmt_u->fetch();
?>

<style>
    .v-page { width: 1200px; margin: 80px auto; display: flex; justify-content: center; }
    .v-card { background: #fff; width: 500px; padding: 60px; text-align: center; border-radius: 4px; box-shadow: 0 1px 15px rgba(0,0,0,0.05); }
    .v-icon { font-size: 60px; color: #e60012; margin-bottom: 25px; }
    .otp-input { width: 100%; padding: 15px; font-size: 32px; text-align: center; letter-spacing: 12px; border: 2px solid #eee; border-radius: 4px; margin: 25px 0; outline: none; }
    .otp-input:focus { border-color: #e60012; }
    .btn-submit { width: 100%; background: #e60012; color: #fff; border: none; padding: 16px; font-weight: bold; cursor: pointer; border-radius: 2px; }
    .resend-text { margin-top: 30px; font-size: 14px; color: #888; }
    #timer { color: #e60012; font-weight: bold; }
    .btn-resend { color: #0055aa; text-decoration: none; font-weight: bold; cursor: pointer; display: none; }
</style>

<div class="v-page">
    <div class="v-card">
        <i class="fas fa-shield-alt v-icon"></i>
        <h2>Verify Your Email</h2>
        <p style="color:#666;">Code sent to: <strong><?php echo htmlspecialchars($user_info['email']); ?></strong></p>

        <?php if($msg): ?>
            <p style="color:#e60012; background:#fff1f0; padding:10px; font-size:14px;"><?php echo $msg; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp_input" class="otp-input" placeholder="000000" maxlength="6" required autofocus>
            <button type="submit" name="verify_now" class="btn-submit">VERIFY NOW</button>
        </form>

        <div class="resend-text">
            <span id="timer-box">Resend available in <span id="timer">60</span>s</span>
            <a id="resend-link" class="btn-resend" onclick="handleResend()">Resend Code</a>
        </div>
    </div>
</div>

<script>
    let timeLeft = 60;
    let timerElement = document.getElementById('timer');
    let timerBox = document.getElementById('timer-box');
    let resendLink = document.getElementById('resend-link');

    let countdown = setInterval(() => {
        timeLeft--;
        timerElement.innerText = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerBox.style.display = 'none';
            resendLink.style.display = 'inline-block';
        }
    }, 1000);

    function handleResend() {
        fetch('verify_email.php?action=resend')
        .then(res => res.text())
        .then(data => {
            if(data.trim() === 'success') {
                alert('A new code has been sent!');
                location.reload();
            } else {
                alert('Failed to send. Check SMTP settings.');
            }
        });
    }
</script>

<?php include_once __DIR__ . '/includes/_foot.php'; ?>