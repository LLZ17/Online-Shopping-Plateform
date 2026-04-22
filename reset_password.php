<?php
/**
 * PXX MALL - SECURE RESET ENGINE (V6.0 - COMPLETE)
 * 功能：
 * 1. OTP 有效期 30 秒
 * 2. 手动重发（用户点击按钮）
 * 3. 密码至少 6 位
 * 4. 两次密码必须一致
 * 5. 新密码不能与旧密码相同
 */

ob_start();
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once 'includes/db.php';

// 角色初始化
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos(strtolower($referer), 'login') !== false || !isset($_SESSION['reset_step'])) {
    $_SESSION['reset_role'] = (strpos(strtolower($referer), 'admin') !== false) ? 'Admin' : 'Member';
    $_SESSION['theme_color'] = ($_SESSION['reset_role'] == 'Admin') ? '#e60012' : '#28a745';
    $_SESSION['reset_step'] = 'identify';
}

$role  = $_SESSION['reset_role'];
$color = $_SESSION['theme_color'];
$stage = $_SESSION['reset_step'];
$error_feedback = "";
$debug_output = "";

function sendNuclearOTP($pdo, $u, $e, $r, $c, &$debug_output) {
    try {
        $otp = sprintf("%06d", mt_rand(0, 999999));
        // OTP 有效期 30 秒
        $exp = date("Y-m-d H:i:s", strtotime("+30 seconds"));
        
        $stmt = $pdo->prepare("UPDATE users SET email_code = ?, email_expiry = ? WHERE username = ? AND role = ?");
        $stmt->execute([$otp, $exp, $u, $r]);

        if (!file_exists('lib/PHPMailer.php')) {
            return "ERROR: PHPMailer not found";
        }

        require_once 'lib/PHPMailer.php';
        require_once 'lib/SMTP.php';
        require_once 'lib/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->SMTPDebug = 0;
        ob_start();
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pxxmallmalaysia@gmail.com';
        $mail->Password = 'wfnh iygk roxj ibxz';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 15;

        $mail->setFrom('pxxmallmalaysia@gmail.com', "noreply@PXX Mall Security System ($r)");
        $mail->addAddress($e, $u);
        $mail->isHTML(true);
        $mail->Subject = "Your Verification Code: [$otp]";
        $mail->Body = "
            <div style='font-family:Arial,sans-serif; padding:20px; border:3px solid $c; border-radius:15px;'>
                <h2 style='color:$c;'>Identity Verification</h2>
                <p>Hello <b>$u</b>,</p>
                <p>Your verification code is:</p>
                <div style='background:#f4f4f4; padding:15px; text-align:center; font-size:32px; font-weight:bold; letter-spacing:5px; color:#333; border-radius:10px;'>
                    $otp
                </div>
                <p style='color:#e11d48; font-size:14px; margin-top:15px; font-weight:bold;'>⏱️ This code expires in 30 seconds.</p>
            </div>";
        
        $sent = $mail->send();
        $debug_output = ob_get_clean();
        
        return $sent ? "SUCCESS" : "ERROR: " . $mail->ErrorInfo;
        
    } catch (Exception $ex) {
        $debug_output = ob_get_clean();
        return "ERROR: " . $ex->getMessage();
    }
}

// ==================== 后端逻辑处理 ====================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // STAGE 1: 发送 OTP
    if (isset($_POST['submit_identity'])) {
        $u = trim($_POST['u']);
        $e = trim($_POST['e']);
        
        $email_parts = explode('@', $e);
        $domain = end($email_parts);
        $allowed = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'icloud.com'];
        $is_edu = (strpos($domain, '.edu') !== false);
        
        if (!in_array($domain, $allowed) && !$is_edu) {
            $error_feedback = "Unsupported email domain.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND role = ?");
            $stmt->execute([$u, $e, $role]);
            
            if ($stmt->fetch()) {
                $_SESSION['reset_username'] = $u;
                $_SESSION['reset_email'] = $e;
                
                $result = sendNuclearOTP($pdo, $u, $e, $role, $color, $debug_output);
                
                if ($result === "SUCCESS") {
                    $_SESSION['reset_step'] = 'verify';
                    $_SESSION['otp_sent_time'] = time();
                    header("Location: reset_password.php");
                    exit();
                } else {
                    $error_feedback = "Email failed: " . $result;
                }
            } else {
                $error_feedback = "Account not found.";
            }
        }
    }
    
    // STAGE 2: 验证 OTP
    if (isset($_POST['submit_otp'])) {
        $otp_input = trim($_POST['otp']);
        $stmt = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE username = ? AND role = ?");
        $stmt->execute([$_SESSION['reset_username'], $role]);
        $row = $stmt->fetch();
        
        if ($row && $row['email_code'] === $otp_input && strtotime($row['email_expiry']) >= time()) {
            $_SESSION['reset_step'] = 'reset';
            header("Location: reset_password.php");
            exit();
        } else {
            $error_feedback = "Invalid or expired code. Codes expire in 30 seconds.";
        }
    }

    // STAGE 3: 修改密码
    if (isset($_POST['submit_final'])) {
        $p1 = $_POST['p1'];
        $p2 = $_POST['p2'];
        
        // 1. 检查长度至少 6 位
        if (strlen($p1) < 6) {
            $error_feedback = "Password must be at least 6 characters.";
        } 
        // 2. 检查两次密码是否一致
        elseif ($p1 !== $p2) {
            $error_feedback = "Passwords do not match.";
        } 
        else {
            // 3. 检查新密码是否与旧密码相同（关键修复）
            $stmt = $pdo->prepare("SELECT password_hashed FROM users WHERE username = ? AND role = ?");
            $stmt->execute([$_SESSION['reset_username'], $role]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($p1, $user['password_hashed'])) {
                // 新密码与旧密码相同，报错
                $error_feedback = "New password cannot be the same as old password.";
            } else {
                // 4. 更新密码
                $new_hash = password_hash($p1, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hashed = ?, email_code = NULL WHERE username = ? AND role = ?");
                $stmt->execute([$new_hash, $_SESSION['reset_username'], $role]);
                
                $target = ($role == 'Admin') ? 'admin/login.php' : 'member/login.php';
                session_destroy();
                echo "<script>alert('Password reset successful!'); window.location.href='$target';</script>";
                exit();
            }
        }
    }
}

// AJAX: 手动重发 OTP
if (isset($_GET['service']) && $_GET['service'] == 'resend') {
    header('Content-Type: application/json');
    if (isset($_SESSION['reset_username'])) {
        $res = sendNuclearOTP($pdo, $_SESSION['reset_username'], $_SESSION['reset_email'], $role, $color, $debug_output);
        if ($res === 'SUCCESS') {
            $_SESSION['otp_sent_time'] = time();
        }
        echo json_encode([
            'status' => ($res === 'SUCCESS'),
            'error' => $res,
            'debug' => $debug_output
        ]);
    } else {
        echo json_encode(['status' => false, 'error' => 'Session expired']);
    }
    exit();
}

// 取消重置
if (isset($_GET['cancel'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// 脱敏显示邮箱
function maskEmail($email) {
    if (empty($email)) return '';
    $parts = explode('@', $email);
    return substr($parts[0], 0, 1) . '***@' . ($parts[1] ?? '');
}
$display_email = maskEmail($_SESSION['reset_email'] ?? '');

// 计算 OTP 剩余时间
$time_left = 30;
if (isset($_SESSION['otp_sent_time'])) {
    $elapsed = time() - $_SESSION['otp_sent_time'];
    $time_left = max(0, 30 - $elapsed);
}
?>

<?php if (file_exists('includes/_head.php')) include 'includes/_head.php'; ?>

<style>
.pxx-reset-bg {
    position: fixed; top: 0; left: 0;
    width: 100%; height: 100%;
    background: url('images/bg.jpeg') center / cover no-repeat;
    z-index: -1;
}
.pxx-reset-container {
    position: relative; z-index: 10;
    width: 100%; min-height: calc(100vh - 120px);
    display: flex; align-items: center; justify-content: center;
    padding: 40px 20px; margin-top: 120px;
}
.nuclear-card {
    background: rgba(255,255,255,0.95);
    border-radius: 30px; padding: 45px 35px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    border-top: 8px solid <?php echo $color; ?>;
    text-align: center; width: 90%; max-width: 440px;
}
.role-badge {
    display: inline-block; background: <?php echo $color; ?>;
    color: white; padding: 5px 18px; border-radius: 50px;
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase;
    margin-bottom: 15px;
}
.email-display {
    background: #f1f5f9; padding: 10px 20px; border-radius: 25px;
    font-size: 0.9rem; color: #475569; margin-bottom: 25px;
    font-weight: 600; display: inline-block;
}
.email-display::before { content: "📧 "; }
.f-input {
    width: 100%; padding: 15px; margin-bottom: 15px;
    border: 2px solid #e2e8f0; border-radius: 15px;
    font-size: 1rem; outline: none; text-align: center;
    transition: all 0.3s;
}
.f-input:focus { border-color: <?php echo $color; ?>; box-shadow: 0 0 0 4px <?php echo $color; ?>22; }
.otp-input { font-size: 2.2rem; letter-spacing: 8px; font-weight: 900; color: <?php echo $color; ?>; }
.btn-submit {
    width: 100%; padding: 15px; background: <?php echo $color; ?>;
    color: white; border: none; border-radius: 50px;
    font-size: 1rem; font-weight: 800; cursor: pointer;
    transition: 0.3s; box-shadow: 0 10px 20px <?php echo $color; ?>44;
}
.btn-submit:hover { transform: scale(0.98); filter: brightness(1.1); }
.btn-resend {
    display: inline-block; margin-top: 15px;
    background: transparent; color: <?php echo $color; ?>;
    border: 2px solid <?php echo $color; ?>;
    padding: 10px 30px; border-radius: 25px;
    font-weight: 700; cursor: pointer; transition: 0.3s;
}
.btn-resend:hover { background: <?php echo $color; ?>; color: white; }
.btn-resend:disabled {
    border-color: #94a3b8; color: #94a3b8;
    cursor: not-allowed; background: transparent;
}
.btn-cancel {
    display: inline-block; margin-top: 20px;
    color: #64748b; text-decoration: none; font-weight: 600;
    font-size: 0.9rem;
}
.btn-cancel:hover { color: <?php echo $color; ?>; }
.error-box {
    background: #fff1f2; color: #e11d48;
    padding: 12px; border-radius: 12px;
    font-size: 0.85rem; margin-bottom: 15px;
    border-left: 5px solid #e11d48;
}
.timer-box {
    margin: 15px 0; font-size: 0.9rem; color: #64748b;
}
.timer-box .secs {
    color: <?php echo $color; ?>; font-weight: 800; font-size: 1.1rem;
}
.expired-notice {
    color: #e11d48; font-size: 0.85rem; margin: 10px 0;
    font-weight: 600;
}
.hint-text {
    color: #94a3b8; font-size: 0.8rem;
    margin-top: -10px; margin-bottom: 15px;
}
</style>

<div class="pxx-reset-bg"></div>

<div class="pxx-reset-container">
    <div class="nuclear-card">
        <div class="role-badge"><?php echo $role; ?> Portal</div>
        <h1 style="color:#1e293b; font-size: 1.8rem; margin-bottom: 8px;">Reset Password</h1>
        
        <p style="color:#64748b; font-size:0.95rem; margin-bottom: 10px;">
            <?php 
                if($stage == 'identify') echo "Enter your details to verify your account!";
                elseif($stage == 'verify') echo "Code sent to:";
                else echo "Enter new password";
            ?>
        </p>

        <?php if($stage == 'verify'): ?>
            <div class="email-display"><?php echo htmlspecialchars($display_email); ?></div>
        <?php endif; ?>

        <?php if($error_feedback): ?>
            <div class="error-box">⚠️ <?php echo htmlspecialchars($error_feedback); ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" onsubmit="return validateForm()">
            
            <?php if($stage == 'identify'): ?>
                <!-- 第一阶段：输入用户名和邮箱 -->
                <input type="text" name="u" class="f-input" placeholder="Username" required 
                       value="<?php echo htmlspecialchars($_POST['u'] ?? ''); ?>">
                <input type="email" name="e" class="f-input" placeholder="Email Address" required
                       value="<?php echo htmlspecialchars($_POST['e'] ?? ''); ?>">
                <button type="submit" name="submit_identity" class="btn-submit">Verify Email →</button>
                <a href="login.php" class="btn-cancel">Back to Login</a>

            <?php elseif($stage == 'verify'): ?>
                <!-- 第二阶段：输入 OTP -->
                <input type="text" name="otp" class="f-input otp-input" placeholder="000000" 
                       maxlength="6" oninput="this.value=this.value.replace(/[^0-9]/g,'');" required autofocus>
                <button type="submit" name="submit_otp" class="btn-submit">Verify Email</button>
                
                <!-- 30秒倒计时 -->
                <div class="timer-box" id="timer-box">
                    ⏱️ Code expires in <span class="secs" id="secs"><?php echo $time_left; ?></span>s
                </div>
                
                <!-- 过期提示 -->
                <div class="expired-notice" id="expired-notice" style="display:none;">
                    ⚠️ Code expired! Please request a new one.
                </div>
                
                <!-- 手动重发按钮（倒计时结束后启用） -->
                <button type="button" id="resend-btn" class="btn-resend" onclick="resendOTP()" disabled>
                    Resend Code
                </button>
                
                <div style="margin-top:15px;">
                    <a href="?cancel=1" class="btn-cancel">Cancel</a>
                </div>

            <?php elseif($stage == 'reset'): ?>
                <!-- 第三阶段：设置新密码 -->
                <input type="password" name="p1" id="p1" class="f-input" 
                       placeholder="New Password" minlength="6" required>
                <div class="hint-text">Minimum 6 characters • Cannot be same as old password</div>
                
                <input type="password" name="p2" id="p2" class="f-input" 
                       placeholder="Confirm Password" minlength="6" required>
                
                <button type="submit" name="submit_final" class="btn-submit">Update Password ✓</button>
                <a href="login.php" class="btn-cancel">Cancel</a>
            <?php endif; ?>
            
        </form>
    </div>
</div>

<script>
<?php if($stage == 'verify'): ?>
// ==================== OTP 30秒倒计时 + 手动重发 ====================

let count = <?php echo $time_left; ?>;
const timerBox = document.getElementById('timer-box');
const secsSpan = document.getElementById('secs');
const expiredNotice = document.getElementById('expired-notice');
const resendBtn = document.getElementById('resend-btn');

function updateTimer() {
    if (count > 0) {
        secsSpan.innerText = count;
        count--;
        setTimeout(updateTimer, 1000);
    } else {
        // 倒计时结束，显示过期提示，启用重发按钮
        timerBox.style.display = 'none';
        expiredNotice.style.display = 'block';
        resendBtn.disabled = false;
        resendBtn.innerText = 'Resend Code';
    }
}

// 启动倒计时
if (count > 0) {
    updateTimer();
} else {
    // 已经过期
    timerBox.style.display = 'none';
    expiredNotice.style.display = 'block';
    resendBtn.disabled = false;
}

// 手动重发函数（用户点击后执行）
function resendOTP() {
    resendBtn.disabled = true;
    resendBtn.innerText = 'Sending...';
    
    fetch('?service=resend')
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                // 重发成功，重置倒计时为 30 秒
                alert('New code sent! Valid for 30 seconds.');
                count = 30;
                timerBox.style.display = 'block';
                expiredNotice.style.display = 'none';
                resendBtn.disabled = true;
                resendBtn.innerText = 'Resend Code';
                updateTimer();
            } else {
                alert('Failed to resend: ' + data.error);
                resendBtn.disabled = false;
                resendBtn.innerText = 'Resend Code';
            }
        })
        .catch(err => {
            alert('Network error. Please try again.');
            resendBtn.disabled = false;
            resendBtn.innerText = 'Resend Code';
        });
}
<?php endif; ?>

<?php if($stage == 'reset'): ?>
// ==================== 密码验证 ====================

function validateForm() {
    const p1 = document.getElementById('p1').value;
    const p2 = document.getElementById('p2').value;
    
    if (p1.length < 6) {
        alert('Password must be at least 6 characters.');
        return false;
    }
    if (p1 !== p2) {
        alert('Passwords do not match.');
        return false;
    }
    return true;
}

// 实时检查两次密码是否一致
document.getElementById('p2').addEventListener('input', function() {
    const p1 = document.getElementById('p1').value;
    const p2 = this.value;
    
    if (p2.length >= 6 && p1 !== p2) {
        this.style.borderColor = '#e11d48'; // 红色：不一致
    } else if (p1 === p2 && p2.length >= 6) {
        this.style.borderColor = '#22c55e'; // 绿色：一致
    } else {
        this.style.borderColor = '#e2e8f0'; // 默认
    }
});
<?php endif; ?>
</script>

<?php if (file_exists('includes/_foot.php')) include 'includes/_foot.php'; ob_end_flush(); ?>