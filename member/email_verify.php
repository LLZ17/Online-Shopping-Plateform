<?php
/**
 * =================================================================================
 * PXX MALL - ENTERPRISE IDENTITY VERIFICATION SYSTEM (V9.0 - FINAL STABLE)
 * =================================================================================
 * 核心安全模块：邮件验证与 OTP 自主重发控制系统
 * 开发者：PXX Mall 技术部
 * ---------------------------------------------------------------------------------
 * 业务逻辑：
 * 1. 首次进入页面：系统检测到无有效验证码，自动触发第一次发信。
 * 2. 30秒过期保护：倒计时结束后，验证码在数据库层面失效。
 * 3. 循环重发逻辑：过期后显示蓝色 Resent 按钮，由用户主动触发，可无限次循环。
 * 4. 邮件格式优化：安全警告文字实现物理换行。
 * =================================================================================
 */

// --- [STAGE 1: 系统环境预设] ---
ob_start(); 

// 强制同步马来西亚时区，这是 30s 逻辑能否成功的关键
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [STAGE 2: 核心类库引入] ---
// 使用物理绝对路径，彻底解决 Fatal error: Class not found 问题
require_once '../lib/Exception.php'; 
require_once '../lib/PHPMailer.php'; 
require_once '../lib/SMTP.php';

// --- [STAGE 3: 严苛模式配置] ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 开启全局会话管理
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// --- [STAGE 4: 数据库依赖加载] ---
require_once '../includes/db.php';
// 登录守卫：未登录用户强制拦截
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/**
 * --- [STAGE 5: 高级发信引擎 - 深度逻辑版] ---
 * 功能：生成 6 位 OTP，设定 30s 有效期，并构建带换行的邮件模板。
 */
function sendSecurityVerificationOTP($pdo, $user_id) {
    try {
        // 开启数据库事务，确保发信与数据记录的原子性
        $pdo->beginTransaction();

        $sql_user = "SELECT email, username FROM users WHERE user_id = ? FOR UPDATE";
        $stmt_user = $pdo->prepare($sql_user);
        $stmt_user->execute([$user_id]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC); 
        
        if (!$user) {
            $pdo->rollBack();
            return "Critical Error: User profile could not be retrieved.";
        }
        
        $target_email = $user['email'];
        $target_name  = $user['username'];

        // 生成加密级 6 位纯数字验证码
        $generated_otp = sprintf("%06d", mt_rand(0, 999999));
        
        // 设定严格的 30 秒生命周期
        $expiry_time = date("Y-m-d H:i:s", strtotime("+30 seconds"));
        
        // 更新数据库记录
        $sql_update = "UPDATE users SET 
                        email_code = ?, 
                        email_expiry = ?, 
                        email_verified = 0, 
                        email_status = 'Unverified' 
                      WHERE user_id = ?";
        $stmt_upd = $pdo->prepare($sql_update);
        $stmt_upd->execute([$generated_otp, $expiry_time, $user_id]);

        // 实例化 PHPMailer 核心类
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pxxmallmalaysia@gmail.com'; 
        $mail->Password   = 'wfnh iygk roxj ibxz'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'utf-8';

        // 解决本地环境 SSL 证书验证问题
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('pxxmallmalaysia@gmail.com', 'noreply@PXX Mall Security System');
        $mail->addAddress($target_email, $target_name);
        $mail->isHTML(true);
        $mail->Subject = "Your Verification Code: [ $generated_otp ] for $target_name";
        
        /**
         * 🔥 邮件正文：满足换行要求
         */
        $email_body = "
        <div style='background-color: #f7f7f7; padding: 50px 0; font-family: sans-serif;'>
            <table align='center' border='0' cellpadding='0' cellspacing='0' width='600' style='background-color: #ffffff; border-radius: 12px; border: 1px solid #eeeeee; overflow: hidden;'>
                <tr>
                    <td align='center' style='padding: 40px; background-color: #e60012;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 26px;'>PXX Mall Security System</h1>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 50px 40px; text-align: center;'>
                        <h2 style='color: #333;'>Hi $target_name,</h2>
                        <p style='color: #666; font-size: 16px;'>To complete your email verification, please enter the code below:</p>
                        <div style='margin: 30px auto; padding: 30px; background: #f9f9f9; border: 2px dashed #e60012; font-size: 48px; font-weight: bold; color: #e60012; letter-spacing: 12px;'>$generated_otp</div>
                        <p style='color: #ff0000; font-size: 14px;'><strong>Attention:</strong> This code is valid for 30 seconds!</p>
                        
                        <p style='color: #999; font-size: 13px; line-height: 1.8; margin-top: 25px;'>
                            This is a system generated message, please do not reply!<br>
                            Please dont share your code to anyone!<br>
                            If you didn't request the code, please ignore it!<br>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style='padding: 25px; background-color: #fafafa; text-align: center; font-size: 11px; color: #bbb; border-top: 1px solid #eeeeee;'>
                        <strong>PXX Mall Malaysia Sdn. Bhd.® [202001028493 (1357831-M)]</strong><br>
                        Copyright © 2026 All Rights Reserved
                    </td>
                </tr>
            </table>
        </div>";

        $mail->Body = $email_body;
        
        if ($mail->send()) {
            $pdo->commit();
            return "success";
        } else {
            $pdo->rollBack();
            return "failed";
        }

    } catch (\Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        return "System Error: " . $e->getMessage();
    }
}

// --- [STAGE 6: 处理 AJAX 异步重发请求] ---
if (isset($_GET['service']) && $_GET['service'] == 'trigger_resend') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/plain; charset=UTF-8');
    echo trim(sendSecurityVerificationOTP($pdo, $user_id)); 
    exit(); 
}

// --- [STAGE 7: 🔥 首次进入：自动触发第一次发信] ---
if ($_SERVER["REQUEST_METHOD"] != "POST" && !isset($_GET['service'])) {
    $stmt_init = $pdo->prepare("SELECT email_expiry FROM users WHERE user_id = ?");
    $stmt_init->execute([$user_id]);
    $init_data = $stmt_init->fetch();
    
    // 如果没有验证码或者之前的验证码已过期，则在进入页面时自动发一次
    if (!$init_data || strtotime($init_data['email_expiry']) < time()) {
        sendSecurityVerificationOTP($pdo, $user_id);
    }
}

// --- [STAGE 8: POST 业务逻辑确认] ---
$error_feedback = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_confirm_otp'])) {
    $user_input = trim($_POST['otp_input_field']);
    
    $stmt_check = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE user_id = ?");
    $stmt_check->execute([$user_id]);
    $data_record = $stmt_check->fetch();

    if ($data_record && $data_record['email_code'] === $user_input) {
        // 严格检查 30s 数据库有效期
        if (strtotime($data_record['email_expiry']) >= time()) {
            $sql_final = "UPDATE users SET email_verified = 1, email_status = 'Verified', email_code = NULL WHERE user_id = ?";
            $stmt_final = $pdo->prepare($sql_final);
            
            if ($stmt_final->execute([$user_id])) {
                echo "<script>alert('Verified Successfully!'); window.location.href='profile.php?page=security';</script>";
                exit();
            } else { $error_feedback = "Update Error: Database write failure."; }
        } else { 
            // 如果过期了，这里会提示用户去点 Resend
            $error_feedback = "Verification Failed: Code has expired!. Please resent a new code"; 
        }
    } else { 
        $error_feedback = "Verification Failed: The 6-digits code is incorrect."; 
    }
}

$stmt_ui = $pdo->prepare("SELECT email, username FROM users WHERE user_id = ?");
$stmt_ui->execute([$user_id]);
$ui_data = $stmt_ui->fetch();

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_head.php';
?>


<div class="pxx-main-viewport">
    <div class="pxx-auth-card">
        <div style="font-size: 70px; color: var(--pxx-red); margin-bottom: 40px;"><i class="fas fa-user-shield"></i></div>
        
        <h1 class="v-h1">Hello, <?php echo htmlspecialchars($ui_data['username']); ?>!</h1>
        
        <p class="v-p-nowrap">To verify your email, please enter the 6-digits code</p>
        <p style="margin-top: -35px; margin-bottom: 50px; color: #888; font-size: 20px;">
          Code sent to: <strong style="color:#000;"><?php echo htmlspecialchars($ui_data['email']); ?></strong>
        </p>

        <?php if($error_feedback): ?>
            <div class="pxx-alert"><?php echo $error_feedback; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="otp_input_field" class="pxx-otp-input" placeholder="000000" maxlength="6" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required autofocus autocomplete="off">
            <br>
            <button type="submit" name="action_confirm_otp" class="pxx-submit-trigger">Complete Verification</button>
        </form>

        <div class="resend-utility-zone">
            <div id="countdown-ui" style="font-size: 19px; color: #aaa;">
                Your code expires in <span id="timer-val" class="timer-digit">30</span>s
            </div>
            
            <button id="resend-btn-ui" class="btn-resend-link" onclick="runResendProcess()">Resent a new code</button>
        </div>

        <a href="profile.php?page=security" class="link-back-nav">
            <i class="fas fa-chevron-left"></i> Back to My Account
        </a>
    </div>
</div>

<div style="clear: both;"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="../js/app.js"></script>
<?php 
// 引入全局页脚
if (file_exists(__DIR__ . '../includes/_foot.php')) {
    include_once __DIR__ . '../includes/_foot.php'; 
}
?>