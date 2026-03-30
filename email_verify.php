<?php
/**
 * PXX Mall - Unified Email Verification System
 * Features: Auto-send on load, 60s Expiry, Resend Logic, DB Validation
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 核心函数：发送验证码 ---
function sendVerificationCode($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $u = $stmt->fetch();
    
    $code = sprintf("%06d", mt_rand(0, 999999)); // 生成6位码
    $expiry = date("Y-m-d H:i:s", strtotime("+1 minute")); // 1分钟失效
    
    // 更新数据库
    $pdo->prepare("UPDATE users SET email_code = ?, email_expiry = ? WHERE user_id = ?")
        ->execute([$code, $expiry, $user_id]);
    
    // 邮件头
    $to = $u['email'];
    $subject = "PXX Mall - Security Verification Code";
    $message = "Your security code is: " . $code . "\r\nThis code will expire in 60 seconds.";
    $headers = "From: noreply-system@pxxmall.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
    
    return mail($to, $subject, $message, $headers);
}

// --- 逻辑处理 1：如果是点击“Resend” (AJAX请求) ---
if (isset($_GET['action']) && $_GET['action'] == 'resend') {
    if (sendVerificationCode($pdo, $user_id)) {
        exit('success');
    } else {
        exit('failed');
    }
}

// --- 逻辑处理 2：用户提交验证码 ---
$error_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_verify'])) {
    $input_code = $_POST['otp_code'];
    
    $check = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE user_id = ?");
    $check->execute([$user_id]);
    $data = $check->fetch();
    
    if ($data && $data['email_code'] === $input_code) {
        if (strtotime($data['email_expiry']) >= time()) {
            // 验证通过
            $pdo->prepare("UPDATE users SET email_verified = 1, email_code = NULL WHERE user_id = ?")
                ->execute([$user_id]);
            echo "<script>alert('Verification Successful!'); window.location.href='profile.php?page=security';</script>";
            exit();
        } else {
            $error_msg = "Code expired. Please resend.";
        }
    } else {
        $error_msg = "Invalid verification code.";
    }
}

// --- 逻辑处理 3：首次进入页面自动发送 ---
$check_init = $pdo->prepare("SELECT email_code FROM users WHERE user_id = ?");
$check_init->execute([$user_id]);
$current_code = $check_init->fetch();
if (!$current_code['email_code']) {
    sendVerificationCode($pdo, $user_id);
}
?>

<style>
    body { background-color: #f5f5f5; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    .v-wrapper { width: 1200px; margin: 80px auto; display: flex; justify-content: center; }
    .v-card { background: #fff; width: 550px; padding: 60px; border-radius: 4px; box-shadow: 0 1px 15px rgba(0,0,0,0.05); text-align: center; }
    
    .v-icon { font-size: 60px; color: #e60012; margin-bottom: 25px; }
    .v-title { font-size: 24px; font-weight: 500; margin-bottom: 10px; color: #333; }
    .v-subtitle { color: #888; font-size: 15px; margin-bottom: 35px; line-height: 1.6; }
    
    .otp-input-field { 
        width: 100%; padding: 15px; font-size: 28px; font-weight: bold; 
        text-align: center; letter-spacing: 12px; border: 2px solid #eee; 
        border-radius: 4px; outline: none; transition: 0.3s;
    }
    .otp-input-field:focus { border-color: #e60012; }
    
    .btn-verify-submit { 
        width: 100%; background: #e60012; color: #fff; border: none; 
        padding: 16px; font-size: 16px; font-weight: 600; cursor: pointer; 
        margin-top: 25px; border-radius: 2px;
    }
    
    .resend-container { margin-top: 30px; font-size: 14px; color: #666; }
    #timer-num { color: #e60012; font-weight: bold; }
    .btn-resend-link { color: #0055aa; text-decoration: none; font-weight: bold; cursor: pointer; }
    .btn-resend-link.disabled { color: #ccc; cursor: not-allowed; pointer-events: none; }
    
    .err-box { color: #e60012; background: #fff1f0; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
</style>

<div class="v-wrapper">
    <div class="v-card">
        <i class="fas fa-shield-alt v-icon"></i>
        <div class="v-title">Security Verification</div>
        <div class="v-subtitle">
            To protect your account, please enter the 6-digit code sent to your registered email address.
        </div>

        <?php if($error_msg): ?>
            <div class="err-box"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp_code" class="otp-input-field" placeholder="000000" maxlength="6" required autofocus>
            <button type="submit" name="submit_verify" class="btn-verify-submit">VERIFY & PROCEED</button>
        </form>

        <div class="resend-container">
            <div id="countdown-area">
                Resend code in <span id="timer-num">60</span> seconds
            </div>
            <a id="resend-link" class="btn-resend-link disabled" style="display:none;" onclick="handleResend()">Resend Verification Code</a>
        </div>

        <a href="profile.php?page=security" style="display:inline-block; margin-top:40px; color:#999; text-decoration:none; font-size:13px;">
            <i class="fas fa-arrow-left"></i> Back to Account Settings
        </a>
    </div>
</div>

<script>
let seconds = 60;
const timerNum = document.getElementById('timer-num');
const resendLink = document.getElementById('resend-link');
const countdownArea = document.getElementById('countdown-area');

function startCountdown() {
    let timer = setInterval(() => {
        seconds--;
        timerNum.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(timer);
            countdownArea.style.display = 'none';
            resendLink.style.display = 'block';
            resendLink.classList.remove('disabled');
        }
    }, 1000);
}

function handleResend() {
    // 调用自身路径 + 参数来重发
    fetch('verify_email.php?action=resend')
    .then(res => res.text())
    .then(data => {
        if(data.trim() === 'success') {
            alert('A new code has been sent to your email!');
            window.location.reload(); // 刷新页面重新开始倒计时
        } else {
            alert('Error: Could not send email. Please try again later.');
        }
    });
}

// 页面加载即开始倒计时
window.onload = startCountdown;
</script>

<?php include_once __DIR__ . '/includes/_foot.php'; ?>