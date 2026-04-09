<?php
ob_start(); // 开启缓冲区，拦截所有 HTML 输出
/**
 * =================================================================================
 * PXX MALL - PROFESSIONAL IDENTITY VERIFICATION SYSTEM (VERSION 4.0)
 * =================================================================================
 * Database Requirements: 
 * - email_code (varchar 6)
 * - email_expiry (datetime)
 * - email_verified (int 1, default 0)
 * - email_status (enum 'Verified', 'Unverified')
 * =================================================================================
 */

// --- [1. 引入核心库与开启严苛模式] ---
// 路径适配你的 lib 目录
require_once 'lib/PHPMailer.php';
require_once 'lib/SMTP.php';
require_once 'lib/Exception.php';

// 开启所有错误报告，任何 SQL 或变量错误都会直接显示
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// --- [2. 引入项目全局依赖] ---
require_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/_head.php';

// 登录守卫：防止未授权访问
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/**
 * --- [3. 核心发信逻辑 - 深度展开版] ---
 * 修复点：使用完全限定命名空间实例化，解决 Class Not Found 报错
 */
function sendSecurityVerificationOTP($pdo, $user_id) {
    // A. 提取用户信息：获取 Email 和 Username 用于个性化邮件
    $sql_user = "SELECT email, username FROM users WHERE user_id = ?";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC); 
    
    if (!$user) return false;
    
    $target_email = $user['email'];
    $target_name  = $user['username'];

    // B. 生成加密级 6 位纯数字验证码
    // sprintf 确保 001234 这种格式不丢失首位 0
    $generated_otp = sprintf("%06d", mt_rand(0, 999999));
    
    // C. 设定严格的 60 秒生存周期
    $expiry_time = date("Y-m-d H:i:s", strtotime("+1 minute"));
    
    // D. 执行 SQL 更新，将 Code 写入数据库
    $sql_update = "UPDATE users SET email_code = ?, email_expiry = ? WHERE user_id = ?";
    $stmt_upd = $pdo->prepare($sql_update);
    $is_recorded = $stmt_upd->execute([$generated_otp, $expiry_time, $user_id]);

    if (!$is_recorded) {
        return false; 
    }

    // E. 🔥 核心修复点：实例化 PHPMailer
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pxxmallmalaysia@gmail.com'; 
        $mail->Password   = 'wfnh iygk roxj ibxz';       
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'utf-8';

        // 核心安全补丁：跳过本地环境的 SSL 验证
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // 设置发件人与收件人
        $mail->setFrom('pxxmallmalaysia@gmail.com', 'noreply@PXX Mall Security System');
        $mail->addAddress($target_email, $target_name);

        $mail->isHTML(true);
        $mail->Subject = "Verification Code: $generated_otp for " . $user['username'];
        
        // --- 修正 141-142 行报错点：使用 Heredoc 语法锁定 HTML 内容 ---
        $email_body = <<<HTML
<div style='background-color: #f7f7f7; padding: 50px 0; font-family: Helvetica, Arial, sans-serif;'>
    <table align='center' border='0' cellpadding='0' cellspacing='0' width='600' style='background-color: #ffffff; border-radius: 12px; border: 1px solid #eeeeee; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
        <tr>
            <td align='center' style='padding: 40px 0; background-color: #e60012;'>
                <h1 style='color: #ffffff; margin: 0; font-size: 26px; text-transform: uppercase; letter-spacing: 3px;'>PXX Mall Security System</h1>
            </td>
        </tr>
        <tr>
            <td style='padding: 50px 40px;'>
                <p style='font-size: 20px; color: #333333; margin-bottom: 25px;'>Hello, <strong>$target_name</strong>!</p>
                <p style='font-size: 16px; color: #666666; line-height: 1.8;'>You have requested a security verification code to access your PXX Mall account. For your verification, please enter the following 6-digit code on the verification page:</p>
                
                <div style='margin: 45px 0; padding: 40px; background-color: #fcfcfc; border: 2px dashed #000000; text-align: center; border-radius: 8px;'>
                    <span style='font-size: 52px; font-weight: 900; color: #e60012; letter-spacing: 15px; font-family: monospace;'>$generated_otp</span>
                </div>
                
                <table border='0' cellpadding='0' cellspacing='0' width='100%' style='border-top: 1px solid #eeeeee; padding-top: 30px;'>
                    <tr>
                        <td style='font-size: 13px; color: #000000; line-height: 1.6; text-align: center;'>
                            ⚠️ <strong>Security Note:</strong> This code is only valid for <strong>60 seconds</strong>.<br>
                            Never share this code with anyone. Our staff will never ask for your OTP.<br>
                            If you did not make this request, please ignore it!
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
HTML;

        $mail->Body = $email_body;

        if($mail->send()) {
            return "success";
        } else {
            return "Error: " . $mail->ErrorInfo;
        }

    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

/**
 * --- [4. 业务逻辑处理：AJAX & POST] ---
 */
$error_feedback = "";

// 处理 AJAX 重发请求
if (isset($_GET['service']) && $_GET['service'] == 'trigger_resend') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/plain; charset=UTF-8');
    $result = sendSecurityVerificationOTP($pdo, $user_id);
    echo trim($result); 
    exit(); 
}

// 处理表单提交确认
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_confirm_otp'])) {
    $user_input = $_POST['otp_input_field'];
    if (!ctype_digit($user_input) || strlen($user_input) !== 6) {
        $error_feedback = "Format Error: Please enter a 6-digit numeric code.";
    } else {
        $stmt_check = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE user_id = ?");
        $stmt_check->execute([$user_id]);
        $data_record = $stmt_check->fetch();

        if ($data_record && $data_record['email_code'] === $user_input) {
            if (strtotime($data_record['email_expiry']) >= time()) {
                $sql_verify_success = "UPDATE users SET email_verified = 1, email_status = 'Verified', email_code = NULL WHERE user_id = ?";
                $pdo->prepare($sql_verify_success)->execute([$user_id]);
                echo "<script>alert('Verified Successfully!'); window.location.href='profile.php?page=security';</script>";
                exit();
            } else {
                $error_feedback = "Timeout: Code has expired. Please request a new code.";
            }
        } else {
            $error_feedback = "Error: The code entered is incorrect.";
        }
    }
}

// 首次加载检测
$stmt_init = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE user_id = ?");
$stmt_init->execute([$user_id]);
$initial_row = $stmt_init->fetch();
if (!$initial_row['email_code'] || strtotime($initial_row['email_expiry']) < time()) {
    sendSecurityVerificationOTP($pdo, $user_id);
}

// 获取 UI 数据
$stmt_ui = $pdo->prepare("SELECT email, username FROM users WHERE user_id = ?");
$stmt_ui->execute([$user_id]);
$ui_data = $stmt_ui->fetch();

function esc_html($val) { return htmlspecialchars($val ?? ''); }
?>

<style>
    :root { --pxx-primary: #e60012; --pxx-shadow: rgba(0, 0, 0, 0.08); }
    body { background-color: #f0f2f5; font-family: 'Segoe UI', system-ui; margin: 0; }
    .pxx-main-layout { width: 1200px; margin: 100px auto; display: flex; justify-content: center; align-items: center; min-height: 500px; }
    .pxx-verify-card { background: #ffffff; width: 650px; padding: 90px 110px; border-radius: 8px; box-shadow: 0 20px 60px var(--pxx-shadow); text-align: center; border-top: 10px solid var(--pxx-primary); animation: slideUp 0.6s ease-out; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .v-badge { width: 100px; height: 100px; background: #fff5f5; color: var(--pxx-primary); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 45px; margin-bottom: 35px; border: 2px solid #ffebeb; }
    .v-h1 { font-size: 34px; font-weight: 900; margin-bottom: 15px; color: #1a1a1a; letter-spacing: -1px; }
    .v-p { color: #777; font-size: 17px; margin-bottom: 50px; line-height: 1.7; }
    .pxx-otp-field { width: 100%; padding: 22px 0; font-size: 48px; font-weight: 900; text-align: center; letter-spacing: 25px; border: 2px solid #e0e0e0; border-radius: 12px; background: #fafafa; outline: none; transition: 0.4s; margin-bottom: 35px; color: #111; }
    .pxx-otp-field:focus { border-color: var(--pxx-primary); background: #fff; box-shadow: 0 15px 35px rgba(230,0,18,0.12); transform: scale(1.02); }
    .pxx-submit-btn { width: 100%; background-color: var(--pxx-primary); color: #ffffff; border: none; padding: 24px; font-size: 20px; font-weight: 800; border-radius: 6px; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 3px; }
    .alert-box { background-color: #fff1f0; border: 1px solid #ffa39e; color: var(--pxx-primary); padding: 18px; margin-bottom: 35px; border-radius: 6px; font-size: 15px; font-weight: 600; }
    .timer-container { margin-top: 50px; font-size: 16px; color: #bbb; }
    #timer-val { color: var(--pxx-primary); font-weight: 800; }
    .resend-action-btn { color: #0056b3; font-weight: bold; text-decoration: underline; cursor: pointer; display: none; background: none; border: none; font-size: 16px; padding: 10px; margin: 20px auto 0; text-align: center; width: fit-content; }
</style>

<div class="pxx-main-layout">
    <div class="pxx-verify-card">
        <div class="v-badge"><i class="fas fa-user-shield"></i></div>
        <div class="v-h1">Hello, <?php echo esc_html($ui_data['username']); ?>!</div>
        <p class="v-p">To verify your email, please enter the 6-digits code <br>sent to: <strong style="color:#222;"><?php echo esc_html($ui_data['email']); ?></strong></p>

        <?php if($error_feedback): ?><div class="alert-box"><?php echo $error_feedback; ?></div><?php endif; ?>

        <form method="POST">
            <input type="text" name="otp_input_field" class="pxx-otp-field" placeholder="000000" maxlength="6" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required autofocus autocomplete="off">
            <button type="submit" name="action_confirm_otp" class="pxx-submit-btn">Complete Verification</button>
        </form>

        <div class="timer-container">
            <div id="countdown-ui">Your code expires in <span id="timer-val">60</span>s</div>
            <button id="resend-btn-ui" class="resend-action-btn" onclick="executeResendService()">Resent a new code</button>
        </div>
    </div>
</div>

<script>
(function() {
    let timeLeft = 60; let timerInterval = null;
    const timerVal = document.getElementById('timer-val');
    const countdownUI = document.getElementById('countdown-ui');
    const resendBtn = document.getElementById('resend-btn-ui');

    function startTimer(s) {
        clearInterval(timerInterval); timeLeft = s;
        countdownUI.style.display = 'block'; resendBtn.style.display = 'none';
        timerInterval = setInterval(() => {
            timeLeft--; if(timerVal) timerVal.innerText = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timerInterval); countdownUI.style.display = 'none';
                resendBtn.style.display = 'block'; resendBtn.disabled = false;
            }
        }, 1000);
    }

    window.executeResendService = function() {
        resendBtn.innerText = "Sending..."; resendBtn.disabled = true;
        fetch('email_verify.php?service=trigger_resend').then(res => res.text()).then(status => {
            if (status.trim() === 'success') { alert("Success: A new verification code has been sent!"); startTimer(60); }
            else { alert("Error: " + status); resendBtn.disabled = false; resendBtn.innerText = "Resent a new code"; }
        });
    };
    startTimer(60);
})();
</script>

<?php include_once __DIR__ . '/includes/_foot.php'; ?>