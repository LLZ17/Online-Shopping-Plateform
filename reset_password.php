<?php
/**
 * =================================================================================
 * PXX MALL - UNIVERSAL DUAL-THEME RESET ENGINE (V380.0)
 * =================================================================================
 * 1. [路由隔离] 自动判定从 /member/ 或 /admin/ 跳转而来。
 * 2. [视觉定制] Member = #28a745 (绿) | Admin = #e60012 (红)。
 * 3. [逻辑严密] 30s OTP 倒计时 + 旧密码比对防重用。
 * =================================================================================
 */

ob_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [STAGE 1: 核心依赖加载] ---
require_once 'lib/Exception.php'; 
require_once 'lib/PHPMailer.php'; 
require_once 'lib/SMTP.php';
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- [STAGE 2: 智能身份与颜色引擎] ---
/**
 * 🌟 核心逻辑：如果是第一次进入（Identify 阶段），
 * 我们根据 HTTP_REFERER (来源网页) 来锁定它是 Member 还是 Admin。
 */
if (!isset($_SESSION['reset_step']) || $_SESSION['reset_step'] == 'identify') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    // 如果来源路径包含 'admin' 关键字，强制进入 Admin 红色模式
    if (strpos(strtolower($referer), 'admin') !== false) {
        $_SESSION['reset_role'] = 'Admin';
        $_SESSION['theme_color'] = '#e60012'; // 红色
    } else {
        // 否则默认为 Member 绿色模式
        $_SESSION['reset_role'] = 'Member';
        $_SESSION['theme_color'] = '#28a745'; // 绿色
    }
}

$role  = $_SESSION['reset_role'];
$color = $_SESSION['theme_color'];
$stage = $_SESSION['reset_step'] ?? 'identify';
$error_feedback = "";

/**
 * --- [发信引擎] ---
 */
function sendDynamicOTP($pdo, $u, $e, $r, $c) {
    try {
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $exp = date("Y-m-d H:i:s", strtotime("+30 seconds"));
        
        $pdo->prepare("UPDATE users SET email_code = ?, email_expiry = ? WHERE username = ? AND role = ?")
            ->execute([$otp, $exp, $u, $r]);

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'pxxmallmalaysia@gmail.com'; 
        $mail->Password = 'wfnh iygk roxj ibxz'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'utf-8';
        $mail->SMTPOptions = array('ssl'=>array('verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true));

        $mail->setFrom('pxxmallmalaysia@gmail.com', "PXX Mall $r Security");
        $mail->addAddress($e, $u);
        $mail->isHTML(true);
        $mail->Subject = "Verification Code: [$otp]";
        
        $mail->Body = "
        <div style='background:#f4f4f4; padding:50px; font-family:Arial;'>
            <div style='background:#fff; max-width:500px; margin:auto; border-radius:25px; overflow:hidden; border-top:12px solid $c;'>
                <div style='padding:40px; text-align:center;'>
                    <h2 style='color:$c;'>Account Recovery ($r)</h2>
                    <p>Enter the code below to reset your security credentials:</p>
                    <div style='font-size:50px; font-weight:900; letter-spacing:12px; color:$c; margin:25px 0;'>$otp</div>
                    <p style='color:red; font-size:12px;'>This code is valid for 30 seconds only.</p>
                </div>
            </div>
        </div>";
        return $mail->send() ? "success" : "failed";
    } catch (Exception $ex) { return $ex->getMessage(); }
}

// 处理 AJAX 重发
if (isset($_GET['service']) && $_GET['service'] == 'resend' && isset($_SESSION['reset_username'])) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/plain');
    echo sendDynamicOTP($pdo, $_SESSION['reset_username'], $_SESSION['reset_email'], $role, $color);
    exit();
}

// --- [STAGE 3: 逻辑提交控制] ---

// 1. 验证 Username + Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_identity'])) {
    $u = trim($_POST['u']); $e = trim($_POST['e']);
    
    // 🌟 核心：精准查询。如果当前是 Member 模式，只搜 Member 账号
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND role = ? LIMIT 1");
    $stmt->execute([$u, $e, $role]);
    $found = $stmt->fetch();

    if ($found) {
        $_SESSION['reset_username'] = $u;
        $_SESSION['reset_email'] = $e;
        $_SESSION['reset_step'] = 'verify';
        sendDynamicOTP($pdo, $u, $e, $role, $color);
        header("Location: reset_password.php"); exit();
    } else {
        $error_feedback = "The system cannot find a <b>$role</b> account matching these credentials.";
    }
}

// 2. 验证 OTP (30s 逻辑)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_otp'])) {
    $code = trim($_POST['otp']);
    $stmt = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$_SESSION['reset_username'], $role]);
    $db = $stmt->fetch();

    if ($db && $db['email_code'] === $code && strtotime($db['email_expiry']) >= time()) {
        $_SESSION['reset_step'] = 'reset';
        header("Location: reset_password.php"); exit();
    } else { $error_feedback = "Invalid or expired verification code."; }
}

// 3. 执行最终重置
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_final'])) {
    $p1 = $_POST['p1']; $p2 = $_POST['p2'];
    if (strlen($p1) < 6) { $error_feedback = "Security: Password must be at least 6 characters."; }
    elseif ($p1 !== $p2) { $error_feedback = "Validation: Passwords match failed."; }
    else {
        // 🌟 检查新旧密码是否一样
        $stmt = $pdo->prepare("SELECT password_hashed FROM users WHERE username = ? AND role = ?");
        $stmt->execute([$_SESSION['reset_username'], $role]);
        if (password_verify($p1, $stmt->fetch()['password_hashed'])) {
            $error_feedback = "Alert: New password cannot be the same as your old one.";
        } else {
            $h = password_hash($p1, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hashed = ?, email_code = NULL WHERE username = ? AND role = ?")
                ->execute([$h, $_SESSION['reset_username'], $role]);
            
            $login_target = ($role == 'Admin') ? 'admin/login.php' : 'member/login.php';
            session_destroy();
            echo "<script>alert('Password Updated Successfully!'); window.location.href='$login_target';</script>"; exit();
        }
    }
}

include 'includes/_head.php'; 
?>

<style>
    :root { --main-theme: <?php echo $color; ?>; }
    body { 
        margin: 0; background: url('images/bg.jpeg') fixed center/cover; 
        font-family: 'Poppins', sans-serif; overflow-y: auto !important; 
    }
    .viewport { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 100px 20px; }
    .auth-card { background: #fff; width: 100%; max-width: 520px; border-radius: 35px; box-shadow: 0 40px 120px rgba(0,0,0,0.25); overflow: hidden; border-top: 15px solid var(--main-theme); }
    .card-banner { padding: 50px 40px 20px 40px; text-align: center; }
    .card-banner h1 { margin: 0; font-weight: 950; font-size: 34px; color: #111; letter-spacing: -1.5px; text-transform: uppercase; }
    .card-body { padding: 40px 60px 60px 60px; text-align: center; }
    .f-input { width: 100%; padding: 20px; border: 2.5px solid #eee; border-radius: 18px; font-size: 18px; outline: none; margin-bottom: 25px; text-align: center; transition: 0.3s; }
    .f-input:focus { border-color: var(--main-theme); background: #fafafa; }
    .btn-action { width: 100%; padding: 22px; background: var(--main-theme); color: #fff; border: none; border-radius: 16px; font-weight: 900; font-size: 22px; cursor: pointer; transition: 0.3s; text-transform: uppercase; }
    .btn-action:hover { filter: brightness(1.1); transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .alert-box { background: #fff1f0; border: 1.5px solid #ffa39e; color: #cf1322; padding: 18px; border-radius: 14px; margin-bottom: 30px; font-size: 14px; font-weight: 700; }
    .timer-zone { margin-top: 40px; color: #aaa; font-size: 18px; }
    .btn-resend { color: var(--main-theme); text-decoration: underline; background: none; border: none; font-weight: 800; cursor: pointer; display: none; }
</style>

<div class="viewport">
    <div class="auth-card">
        <div class="card-banner">
            <h1><?php echo $role; ?> Portal</h1>
        </div>
        <div class="card-body">
            
            <?php if($error_feedback): ?> <div class="alert-box"><i class="fas fa-exclamation-circle"></i> <?php echo $error_feedback; ?></div> <?php endif; ?>

            <?php if($stage == 'identify'): ?>
                <p style="color:#888; margin-bottom:35px;">Enter your <b><?php echo $role; ?></b> credentials to receive a verification code.</p>
                <form method="POST">
                    <input type="text" name="u" class="f-input" placeholder="Username" required>
                    <input type="email" name="e" class="f-input" placeholder="Registered Email" required>
                    <button type="submit" name="submit_identity" class="btn-action">Verify Account</button>
                </form>

            <?php elseif($stage == 'verify'): ?>
                <p style="color:#888; margin-bottom:35px;">A 6-digit code has been sent to:<br><b><?php echo $_SESSION['reset_email']; ?></b></p>
                <form method="POST">
                    <input type="text" name="otp" class="f-input" style="font-size:48px; letter-spacing:15px; font-weight:950;" placeholder="000000" maxlength="6" required autofocus>
                    <button type="submit" name="submit_otp" class="btn-action">Confirm OTP</button>
                </form>
                <div class="timer-zone">
                    <div id="t-ui">Link expires in <span id="t-val" style="color:var(--main-theme); font-weight:900;">30</span>s</div>
                    <button id="r-btn" class="btn-resend" onclick="reSync()">Resend New Code</button>
                </div>

            <?php elseif($stage == 'reset'): ?>
                <p style="color:#888; margin-bottom:35px;">Identity Secure. Reset password for:<br><b><?php echo $_SESSION['reset_username']; ?></b></p>
                <form method="POST">
                    <input type="password" name="p1" class="f-input" placeholder="New Password" required>
                    <input type="password" name="p2" class="f-input" placeholder="Confirm Password" required>
                    <button type="submit" name="submit_final" class="btn-action">Sync Credentials</button>
                </form>
            <?php endif; ?>

            <div style="margin-top:45px; border-top:1.5px solid #f0f0f0; padding-top:25px;">
                <a href="index.php" style="color:#bbb; text-decoration:none; font-weight:600;"><i class="fas fa-chevron-left"></i> Back to Portal</a>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let t = 30; let inv = null;
    const tv = document.getElementById('t-val'), rb = document.getElementById('r-btn'), tui = document.getElementById('t-ui');
    function startTimer(s) {
        clearInterval(inv); t = s; if(tui) tui.style.display = 'block'; if(rb) rb.style.display = 'none';
        inv = setInterval(() => { t--; if(tv) tv.innerText = t; if(t <= 0) { clearInterval(inv); if(tui) tui.style.display = 'none'; if(rb) rb.style.display = 'inline-block'; } }, 1000);
    }
    window.reSync = function() { 
        rb.innerText = "Syncing..."; 
        fetch('reset_password.php?service=resend').then(r => r.text()).then(st => { 
            if(st.trim()==='success'){alert('A new code has been sent!'); startTimer(30);}
            else {alert(st); rb.innerText="Resend New Code";} 
        }); 
    };
    if(tv) startTimer(30);
})();
</script>

<?php include 'includes/_foot.php'; ?>