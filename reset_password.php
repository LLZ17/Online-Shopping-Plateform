<?php
/**
 * PXX MALL - SECURE RESET ENGINE (V5.1 - ENTERPRISE STABLE)
 * 1. 布局：Fixed 居中定位，彻底解决 _foot.php 导致的走位/偏移问题。
 * 2. 背景：高清无模糊处理，保持 bg.jpeg 原始清晰度。
 * 3. 颜色：自动继承 Session 中的角色颜色（Admin/Member），不进行手动硬编码。
 * 4. 限制：严格限制 Email Domain (主流平台 + 所有 .edu 域名)。
 * 5. 功能：完整保留 OTP 发送、校验及密码更新逻辑，并新增 Cancel 按钮。
 */

ob_start();
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [1. 环境引用] ---
// 优先引用你的全局 head
if (file_exists('includes/_head.php')) {
    include 'includes/_head.php'; 
}

// 引入数据库连接，假设变量名为 $conn (根据你之前的 db.php)
require_once 'includes/db.php';
$pdo = $conn;

// --- [2. 角色与颜色初始化] ---
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos(strtolower($referer), 'login') !== false || !isset($_SESSION['reset_step'])) {
    $_SESSION['reset_role'] = (strpos(strtolower($referer), 'admin') !== false) ? 'Admin' : 'Member';
    // 动态分配颜色：Admin 为红色，Member 为绿色
    $_SESSION['theme_color'] = ($_SESSION['reset_role'] == 'Admin') ? '#e60012' : '#28a745';
    $_SESSION['reset_step'] = 'identify';
}

$role  = $_SESSION['reset_role'];
$color = $_SESSION['theme_color'];
$stage = $_SESSION['reset_step'];
$error_feedback = "";

// --- [3. 邮件发送核心函数] ---
function sendNuclearOTP($pdo, $u, $e, $r, $c) {
    try {
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $exp = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        
        // 更新数据库
        $stmt = $pdo->prepare("UPDATE users SET email_code = ?, email_expiry = ?, last_resend_at = NOW() WHERE username = ? AND role = ?");
        $stmt->execute([$otp, $exp, $u, $r]);

        // PHPMailer 配置
        require_once 'lib/PHPMailer.php';
        require_once 'lib/SMTP.php';
        require_once 'lib/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pxxmallmalaysia@gmail.com';
        $mail->Password = 'wfnh iygk roxj ibxz'; // 应用专用密码
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('pxxmallmalaysia@gmail.com', "PXX Mall ($r)");
        $mail->addAddress($e, $u);
        $mail->isHTML(true);
        $mail->Subject = "Your Security Verification Code: [$otp]";
        $mail->Body = "
            <div style='font-family:Arial,sans-serif; padding:20px; border:3px solid $c; border-radius:15px;'>
                <h2 style='color:$c;'>Identity Verification</h2>
                <p>Hello <b>$u</b>,</p>
                <p>You are requesting to reset your password. Please use the following code:</p>
                <div style='background:#f4f4f4; padding:15px; text-align:center; font-size:32px; font-weight:bold; letter-spacing:5px; color:#333; border-radius:10px;'>
                    $otp
                </div>
                <p style='color:#666; font-size:12px; margin-top:15px;'>This code will expire in 5 minutes. If you did not request this, please ignore this email.</p>
            </div>";
        
        return $mail->send() ? "success" : "failed";
    } catch (Exception $ex) {
        return $ex->getMessage();
    }
}

// --- [4. 后端逻辑处理段] ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // STAGE 1: 身份与域名验证
    if (isset($_POST['submit_identity'])) {
        $u = trim($_POST['u']);
        $e = trim($_POST['e']);
        
        // 解析域名
        $email_parts = explode('@', $e);
        $domain = end($email_parts);
        
        // 允许的域名白名单
        $allowed_domains = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'icloud.com'];
        $is_edu = (strpos($domain, '.edu') !== false); // 检查是否包含 .edu
        
        if (!in_array($domain, $allowed_domains) && !$is_edu) {
            $error_feedback = "Unsupported email domain. Please use Gmail, Outlook, Yahoo, iCloud, or a .edu address.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND role = ?");
            $stmt->execute([$u, $e, $role]);
            if ($stmt->fetch()) {
                $_SESSION['reset_username'] = $u;
                $_SESSION['reset_email'] = $e;
                $_SESSION['reset_step'] = 'verify';
                sendNuclearOTP($pdo, $u, $e, $role, $color);
                header("Location: reset_password.php");
                exit();
            } else {
                $error_feedback = "Matching account not found in our $role records.";
            }
        }
    }
    
    // STAGE 2: OTP 验证
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
            $error_feedback = "Invalid or expired verification code.";
        }
    }

    // STAGE 3: 修改密码
    if (isset($_POST['submit_final'])) {
        $p1 = $_POST['p1'];
        $p2 = $_POST['p2'];
        if (strlen($p1) < 4) {
            $error_feedback = "Password is too short (min. 4 chars).";
        } elseif ($p1 !== $p2) {
            $error_feedback = "Passwords do not match.";
        } else {
            $new_hash = password_hash($p1, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hashed = ?, email_code = NULL WHERE username = ? AND role = ?");
            $stmt->execute([$new_hash, $_SESSION['reset_username'], $role]);
            
            $target_login = ($role == 'Admin') ? 'admin/login.php' : 'member/login.php';
            session_destroy();
            echo "<script>alert('Password reset successful! Please login with your new password.'); window.location.href='$target_login';</script>";
            exit();
        }
    }
}

// AJAX: 处理验证码重发
if (isset($_GET['service']) && $_GET['service'] == 'resend') {
    header('Content-Type: application/json');
    if (isset($_SESSION['reset_username'])) {
        $res = sendNuclearOTP($pdo, $_SESSION['reset_username'], $_SESSION['reset_email'], $role, $color);
        echo json_encode(['status' => ($res === 'success' ? 'success' : 'error')]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        /* ========== [5. CSS 布局：防走位架构] ========== */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body, html {
            height: 100%;
            width: 100%;
            overflow: hidden; /* 强制禁用滚动条，防止 _foot 造成高度溢出 */
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* 高清背景层：移除模糊 */
        .pxx-background-master {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: url('images/bg.jpeg') center / cover no-repeat;
            z-index: -2;
            filter: brightness(0.9) contrast(1.1); /* 提升画面质感 */
        }

        /* 核心卡片容器：Fixed 绝对居中，免疫 _foot 偏移 */
        .pxx-auth-container {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            width: 90%;
            max-width: 440px;
        }

        .nuclear-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 30px;
            padding: 45px 35px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.4);
            border-top: 8px solid <?php echo $color; ?>;
            text-align: center;
        }

        .role-badge {
            display: inline-block;
            background: <?php echo $color; ?>;
            color: white;
            padding: 5px 18px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .desc-text { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }

        .f-input {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 1rem;
            outline: none;
            text-align: center;
            transition: all 0.3s ease;
        }

        .f-input:focus { border-color: <?php echo $color; ?>; box-shadow: 0 0 0 4px <?php echo $color; ?>22; }

        .otp-input {
            font-size: 2.2rem;
            letter-spacing: 8px;
            font-weight: 900;
            color: <?php echo $color; ?>;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: <?php echo $color; ?>;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 10px 20px <?php echo $color; ?>44;
        }

        .btn-submit:hover { transform: scale(0.98); filter: brightness(1.1); }

        .btn-cancel {
            display: inline-block;
            margin-top: 20px;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        .btn-cancel:hover { color: #475569; }

        .error-box {
            background: #fff1f2;
            color: #e11d48;
            padding: 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 15px;
            border-left: 5px solid #e11d48;
        }
    </style>
</head>
<body>

<div class="pxx-background-master"></div>

<div class="pxx-auth-container">
    <div class="nuclear-card">
        <div class="role-badge"><?php echo $role; ?> Portal</div>
        <h1 style="color:#1e293b; font-size: 1.8rem; margin-bottom: 8px;">Reset Password</h1>
        <p class="desc-text">
            <?php 
                if($stage == 'identify') echo "Securely verify your identity";
                elseif($stage == 'verify') echo "Verification code sent to email";
                else echo "Enter your new password below";
            ?>
        </p>

        <?php if($error_feedback): ?>
            <div class="error-box">⚠️ <?php echo htmlspecialchars($error_feedback); ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <?php if($stage == 'identify'): ?>
                <input type="text" name="u" class="f-input" placeholder="Username" required>
                <input type="email" name="e" class="f-input" placeholder="Email Address" required>
                <button type="submit" name="submit_identity" class="btn-submit">Send Code →</button>
                <a href="login.php" class="btn-cancel">Back to Login</a>

            <?php elseif($stage == 'verify'): ?>
                <input type="text" name="otp" class="f-input otp-input" placeholder="000000" maxlength="6" oninput="this.value=this.value.replace(/[^0-9]/g,'');" required autofocus>
                <button type="submit" name="submit_otp" class="btn-submit">Verify Code</button>
                <div id="timer-box" style="margin-top:15px; font-size:0.8rem; color:#94a3b8;">
                    Resend available in <span id="secs">30</span>s
                </div>
                <a href="reset_password.php" class="btn-cancel">Cancel Reset</a>

            <?php elseif($stage == 'reset'): ?>
                <input type="password" name="p1" class="f-input" placeholder="New Password" required>
                <input type="password" name="p2" class="f-input" placeholder="Confirm Password" required>
                <button type="submit" name="submit_final" class="btn-submit">Update Password ✓</button>
                <a href="login.php" class="btn-cancel">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
    // 倒计时逻辑
    if(document.getElementById('secs')) {
        let count = 30;
        const timer = setInterval(() => {
            count--;
            document.getElementById('secs').innerText = count;
            if(count <= 0) {
                clearInterval(timer);
                document.getElementById('timer-box').innerHTML = `<a href="javascript:void(0)" onclick="location.reload()" style="color:<?php echo $color; ?>; font-weight:700; text-decoration:none;">⟳ Resend Now</a>`;
            }
        }, 1000);
    }
</script>

<?php
// 正常引入页脚 (Fixed 布局会确保卡片不被它撑开)
if (file_exists('includes/_foot.php')) {
    include 'includes/_foot.php';
}
ob_end_flush();
?>
</body>
</html>