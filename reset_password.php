<?php
/**
 * =================================================================================
 * PXX MALL - ALL-IN-ONE SECURITY RESET ENGINE (V340.0)
 * =================================================================================
 * 开发者：PXX Mall 技术部
 * 位置：项目根目录 /reset_password.php
 * 核心逻辑：
 * 1. [多态模式] 同一页面处理身份验证、OTP校验、密码修改。
 * 2. [自适应] 自动识别并记录角色 (Admin/Member)，切换视觉配色。
 * 3. [安全] 强制 30s OTP、6位长度校验、新旧密码比对。
 * 4. [布局] 100% 修复滚动条问题，页脚引用自外部文件。
 * =================================================================================
 */

ob_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [STAGE 1: 环境依赖] ---
require_once 'lib/Exception.php'; 
require_once 'lib/PHPMailer.php'; 
require_once 'lib/SMTP.php';
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- [STAGE 2: 状态机判定] ---
// 定义当前处于哪个阶段: 'identify', 'verify', 'reset'
$current_stage = 'identify'; 

if (isset($_SESSION['reset_step'])) {
    $current_stage = $_SESSION['reset_step'];
}

$error_feedback = "";
$success_feedback = "";

/**
 * --- [发信引擎] 100% 兼容 30s OTP 逻辑 ---
 */
function fireOTP($pdo, $username, $email, $role) {
    try {
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $expiry = date("Y-m-d H:i:s", strtotime("+30 seconds"));
        
        $sql = "UPDATE users SET email_code = ?, email_expiry = ? WHERE username = ? AND role = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$otp, $expiry, $username, $role]);

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pxxmallmalaysia@gmail.com'; 
        $mail->Password   = 'wfnh iygk roxj ibxz'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'utf-8';

        $mail->SMTPOptions = array(
            'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
        );

        $mail->setFrom('pxxmallmalaysia@gmail.com', 'PXX Mall Global Security');
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = "Security OTP: [ $otp ]";
        
        $color = ($role == 'Member') ? '#28a745' : '#e60012';
        $mail->Body = "
        <div style='background:#f4f4f4; padding:50px; font-family:Arial;'>
            <div style='background:#fff; max-width:600px; margin:auto; border-radius:15px; overflow:hidden; border:1px solid #ddd;'>
                <div style='background:$color; padding:30px; text-align:center; color:#fff;'><h1>PXX Security</h1></div>
                <div style='padding:40px; text-align:center;'>
                    <h2>Verification Code</h2>
                    <div style='font-size:45px; font-weight:900; letter-spacing:10px; color:$color; margin:20px 0;'>$otp</div>
                    <p style='color:red;'>Expires in 30 seconds!</p>
                </div>
            </div>
        </div>";
        return $mail->send() ? "success" : "failed";
    } catch (Exception $e) { return $e->getMessage(); }
}

// 处理 AJAX 重发请求
if (isset($_GET['service']) && $_GET['service'] == 'trigger_resend' && isset($_SESSION['reset_username'])) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/plain');
    echo fireOTP($pdo, $_SESSION['reset_username'], $_SESSION['reset_email'], $_SESSION['reset_role']);
    exit();
}

// --- [STAGE 3: 业务提交逻辑处理] ---

// A. 身份验证阶段提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_identity'])) {
    $u = trim($_POST['username']);
    $e = trim($_POST['email']);
    
    // 自动判断入口角色 (根据你的文件夹路径)
    $r = (strpos($_SERVER['HTTP_REFERER'], 'admin') !== false) ? 'Admin' : 'Member';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND role = ? LIMIT 1");
    $stmt->execute([$u, $e, $r]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['reset_username'] = $u;
        $_SESSION['reset_email'] = $e;
        $_SESSION['reset_role'] = $r;
        $_SESSION['reset_step'] = 'verify';
        fireOTP($pdo, $u, $e, $r);
        header("Location: reset_password.php"); exit();
    } else {
        $error_feedback = "Error: Username and Email do not match our records.";
    }
}

// B. OTP 验证阶段提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_otp'])) {
    $code = trim($_POST['otp_code']);
    $stmt = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$_SESSION['reset_username'], $_SESSION['reset_role']]);
    $db = $stmt->fetch();

    if ($db && $db['email_code'] === $code && strtotime($db['email_expiry']) >= time()) {
        $_SESSION['reset_step'] = 'reset';
        header("Location: reset_password.php"); exit();
    } else {
        $error_feedback = "Invalid or expired verification code.";
    }
}

// C. 最终重置阶段提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_final'])) {
    $p1 = $_POST['p1']; $p2 = $_POST['p2'];
    $u = $_SESSION['reset_username']; $r = $_SESSION['reset_role'];

    if (strlen($p1) < 6) { $error_feedback = "Password must be at least 6 characters."; }
    elseif ($p1 !== $p2) { $error_feedback = "Passwords do not match."; }
    else {
        $stmt = $pdo->prepare("SELECT password_hashed FROM users WHERE username = ? AND role = ?");
        $stmt->execute([$u, $r]);
        if (password_verify($p1, $stmt->fetch()['password_hashed'])) {
            $error_feedback = "New password cannot be the same as old one.";
        } else {
            $hash = password_hash($p1, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hashed = ?, email_code = NULL WHERE username = ? AND role = ?")->execute([$hash, $u, $r]);
            session_destroy();
            echo "<script>alert('Success!'); window.location.href='index.php';</script>"; exit();
        }
    }
}

// 主题颜色判定
$theme = (isset($_SESSION['reset_role']) && $_SESSION['reset_role'] == 'Admin') ? '#e60012' : '#28a745';
include 'includes/_head.php'; 
?>

<style>
    :root { --main: <?php echo $theme; ?>; }
    body { 
        margin: 0; background-image: url('images/bg.jpeg'); background-size: cover; background-attachment: fixed;
        font-family: 'Poppins', sans-serif; overflow-y: auto !important; 
    }
    .viewport { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 100px 20px; }
    .card { background: #fff; width: 100%; max-width: 500px; border-radius: 30px; box-shadow: 0 30px 100px rgba(0,0,0,0.2); overflow: hidden; border-top: 15px solid var(--main); }
    .banner { padding: 40px; text-align: center; background: #fff; }
    .banner h1 { margin: 0; font-weight: 900; color: #111; font-size: 32px; }
    .body { padding: 50px; text-align: center; }
    .f-input { width: 100%; padding: 18px; border: 2px solid #eee; border-radius: 15px; font-size: 17px; outline: none; margin-bottom: 20px; text-align: center; }
    .f-input:focus { border-color: var(--main); }
    .otp-mode { font-size: 45px; letter-spacing: 15px; font-weight: 900; background: #fafafa; }
    .btn-action { width: 100%; padding: 22px; background: var(--main); color: #fff; border: none; border-radius: 14px; font-weight: 900; font-size: 20px; cursor: pointer; transition: 0.3s; }
    .btn-action:hover { filter: brightness(1.1); transform: translateY(-3px); }
    .alert { background: #fff1f0; border: 1px solid #ffa39e; color: #cf1322; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600; }
    .resend-box { margin-top: 35px; color: #aaa; font-size: 17px; }
</style>

<div class="viewport">
    <div class="card">
        <div class="banner"><h1>Security Hub</h1></div>
        <div class="body">
            <?php if($error_feedback): ?> <div class="alert"><?php echo $error_feedback; ?></div> <?php endif; ?>

            <?php if($current_stage == 'identify'): ?>
                <p style="color:#888; margin-bottom:30px;">Please identify your account credentials</p>
                <form method="POST">
                    <input type="text" name="username" class="f-input" placeholder="Username" required>
                    <input type="email" name="email" class="f-input" placeholder="Registered Email" required>
                    <button type="submit" name="submit_identity" class="btn-action">Verify Account</button>
                </form>
            <?php elseif($current_stage == 'verify'): ?>
                <p style="color:#888; margin-bottom:30px;">Enter the 6-digit code sent to:<br><b><?php echo $_SESSION['reset_email']; ?></b></p>
                <form method="POST">
                    <input type="text" name="otp_code" class="f-input otp-mode" placeholder="000000" maxlength="6" required autofocus>
                    <button type="submit" name="submit_otp" class="btn-action">Verify OTP</button>
                </form>
                <div class="resend-box">
                    <div id="t-ui">Expires in <span id="t-val" style="color:var(--main); font-weight:900;">30</span>s</div>
                    <button id="r-btn" style="display:none; color:var(--main); background:none; border:none; font-weight:700; cursor:pointer;" onclick="resend()">Resend Code</button>
                </div>
            <?php elseif($current_stage == 'reset'): ?>
                <p style="color:#888; margin-bottom:30px;">Identity Verified. Setting new password for: <b><?php echo $_SESSION['reset_username']; ?></b></p>
                <form method="POST">
                    <input type="password" name="p1" class="f-input" placeholder="New Password" required>
                    <input type="password" name="p2" class="f-input" placeholder="Confirm Password" required>
                    <button type="submit" name="submit_final" class="btn-action">Update Password</button>
                </form>
            <?php endif; ?>

            <a href="index.php" style="display:inline-block; margin-top:40px; color:#bbb; text-decoration:none;"><i class="fas fa-chevron-left"></i> Back to Login</a>
        </div>
    </div>
</div>

<script>
(function() {
    let t = 30; let inv = null;
    const tv = document.getElementById('t-val'), rb = document.getElementById('r-btn'), tui = document.getElementById('t-ui');
    function start(s) {
        clearInterval(inv); t = s; if(tui) tui.style.display = 'block'; if(rb) rb.style.display = 'none';
        inv = setInterval(() => { t--; if(tv) tv.innerText = t; if(t <= 0) { clearInterval(inv); if(tui) tui.style.display = 'none'; if(rb) rb.style.display = 'inline-block'; } }, 1000);
    }
    window.resend = function() { rb.innerText = "..."; fetch('reset_password.php?service=trigger_resend').then(r => r.text()).then(st => { if(st.trim()==='success'){alert('Sent!'); start(30);}else{alert(st);rb.innerText="Resend";} }); };
    if(tv) start(30);
})();
</script>

<?php include 'includes/_foot.php'; ?>