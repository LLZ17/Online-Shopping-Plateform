<?php
/**
 * =================================================================================
 * PXX MALL - UNIVERSAL DUAL-THEME RESET ENGINE (V382.0)
 * =================================================================================
 */

ob_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [STAGE 1: 核心加载] ---
require_once 'lib/Exception.php'; 
require_once 'lib/PHPMailer.php'; 
require_once 'lib/SMTP.php';
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- [配置：允许的域名] ---
$allowed_domains = ['gmail.com', 'outlook.com', 'yahoo.com', 'hotmail.com']; 

/**
 * 频率限制：1分钟内最多请求 2 次
 */
function canRequestCode() {
    $now = time();
    if (!isset($_SESSION['last_req_time']) || ($now - $_SESSION['last_req_time']) > 60) {
        $_SESSION['last_req_time'] = $now;
        $_SESSION['req_count'] = 1;
        return true;
    }
    if ($_SESSION['req_count'] < 2) {
        $_SESSION['req_count']++;
        return true;
    }
    return false;
}

// --- [STAGE 2: 身份与视觉引擎] ---
if (!isset($_SESSION['reset_step']) || $_SESSION['reset_step'] == 'identify') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos(strtolower($referer), 'admin') !== false) {
        $_SESSION['reset_role'] = 'Admin';
        $_SESSION['theme_color'] = '#e60012'; 
    } else {
        $_SESSION['reset_role'] = 'Member';
        $_SESSION['theme_color'] = '#28a745';
    }
    $_SESSION['reset_step'] = 'identify'; 
}

$role  = $_SESSION['reset_role'];
$color = $_SESSION['theme_color'];
$stage = $_SESSION['reset_step'];
$error_feedback = "";

/**
 * 发送邮件引擎
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
                    <h2 style='color:$c;'>Security Sync ($r)</h2>
                    <p>Use the code below to verify your identity:</p>
                    <div style='font-size:50px; font-weight:900; letter-spacing:12px; color:$c; margin:25px 0;'>$otp</div>
                    <p style='color:red; font-size:12px;'>Code expires in 30s.</p>
                </div>
            </div>
        </div>";
        return $mail->send() ? "success" : "failed";
    } catch (Exception $ex) { return $ex->getMessage(); }
}

// --- [STAGE 3: 业务提交逻辑] ---

// A. 处理 AJAX 手动重发
if (isset($_GET['service']) && $_GET['service'] == 'resend' && isset($_SESSION['reset_username'])) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/plain');
    if (!canRequestCode()) {
        echo "Limit reached (2 times/min). Please wait.";
    } else {
        echo sendDynamicOTP($pdo, $_SESSION['reset_username'], $_SESSION['reset_email'], $role, $color);
    }
    exit();
}

// B. 第一步：验证身份并初次发码
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_identity'])) {
    $u = trim($_POST['u']); 
    $e = trim($_POST['e']);
    
    // 域名核验：列表存在 OR 包含 .edu
    $domain = strtolower(substr(strrchr($e, "@"), 1));
    $is_valid_domain = in_array($domain, $allowed_domains) || (strpos($domain, '.edu') !== false);

    if (!$is_valid_domain) {
        $error_feedback = "Error: Email domain <b>@$domain</b> is not allowed.";
    } elseif (!canRequestCode()) {
        $error_feedback = "Too many requests. Try again later.";
    } else {
        // SQL 严格查询
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND role = ? LIMIT 1");
        $stmt->execute([$u, $e, $role]);
        if ($stmt->fetch()) {
            $_SESSION['reset_username'] = $u;
            $_SESSION['reset_email'] = $e;
            $_SESSION['reset_step'] = 'verify';
            sendDynamicOTP($pdo, $u, $e, $role, $color);
            header("Location: reset_password.php"); 
            exit();
        } else {
            $error_feedback = "Invalid credentials for $role portal.";
        }
    }
}

// C. 第二步：验证 OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_otp'])) {
    $code = trim($_POST['otp']);
    $stmt = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$_SESSION['reset_username'], $role]);
    $user_data = $stmt->fetch();

    if ($user_data && $user_data['email_code'] === $code && strtotime($user_data['email_expiry']) >= time()) {
        $_SESSION['reset_step'] = 'reset';
        header("Location: reset_password.php"); exit();
    } else { 
        $error_feedback = "Code is incorrect or has expired."; 
    }
}

// D. 第三步：最终重置
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_final'])) {
    $p1 = $_POST['p1']; $p2 = $_POST['p2'];
    if (strlen($p1) < 6) { $error_feedback = "Password too short (Min 6)."; }
    elseif ($p1 !== $p2) { $error_feedback = "Passwords do not match."; }
    else {
        $stmt = $pdo->prepare("SELECT password_hashed FROM users WHERE username = ? AND role = ?");
        $stmt->execute([$_SESSION['reset_username'], $role]);
        if (password_verify($p1, $stmt->fetch()['password_hashed'])) {
            $error_feedback = "New password cannot be the same as old.";
        } else {
            $h = password_hash($p1, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hashed = ?, email_code = NULL WHERE username = ? AND role = ?")
                ->execute([$h, $_SESSION['reset_username'], $role]);
            
            $login_url = ($role == 'Admin') ? 'admin/login.php' : 'member/login.php';
            session_destroy();
            echo "<script>alert('Sync Complete!'); window.location.href='$login_url';</script>"; exit();
        }
    }
}

include 'includes/_head.php'; 
?>

<style>
    :root { --main: <?php echo $color; ?>; }
    body { margin: 0; background: url('images/bg.jpeg') center/cover fixed; font-family: 'Poppins', sans-serif; }
    .viewport { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
    .auth-card { background: #fff; width: 100%; max-width: 480px; border-radius: 30px; box-shadow: 0 30px 100px rgba(0,0,0,0.2); overflow: hidden; border-top: 12px solid var(--main); }
    .card-banner { padding: 40px 40px 0; text-align: center; }
    .card-banner h1 { font-weight: 900; font-size: 28px; color: #222; margin: 0; }
    .card-body { padding: 40px; text-align: center; }
    .f-input { width: 100%; padding: 18px; border: 2px solid #f0f0f0; border-radius: 15px; font-size: 16px; margin-bottom: 20px; text-align: center; box-sizing: border-box; outline: none; transition: 0.3s; }
    .f-input:focus { border-color: var(--main); background: #fffdfd; }
    .btn-action { width: 100%; padding: 18px; background: var(--main); color: #fff; border: none; border-radius: 12px; font-weight: 800; font-size: 18px; cursor: pointer; transition: 0.2s; }
    .btn-action:hover { filter: brightness(1.1); transform: translateY(-2px); }
    .alert-box { background: #fff1f0; border: 1px solid #ffa39e; color: #cf1322; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; font-weight: bold; }
    .timer-zone { margin-top: 25px; font-size: 15px; color: #999; }
    .btn-resend { color: var(--main); text-decoration: underline; background: none; border: none; font-weight: bold; cursor: pointer; display: none; }
</style>

<div class="viewport">
    <div class="auth-card">
        <div class="card-banner">
            <h1><?php echo $role; ?> Access</h1>
        </div>
        <div class="card-body">
            
            <?php if($error_feedback): ?> <div class="alert-box"><?php echo $error_feedback; ?></div> <?php endif; ?>

            <?php if($stage == 'identify'): ?>
                <p style="color:#666; font-size:14px; margin-bottom:25px;">Provide your registered credentials.</p>
                <form method="POST">
                    <input type="text" name="u" class="f-input" placeholder="Username" required>
                    <input type="email" name="e" class="f-input" placeholder="Registered Email" required>
                    <button type="submit" name="submit_identity" class="btn-action">Request Code</button>
                </form>

            <?php elseif($stage == 'verify'): ?>
                <p style="color:#666; font-size:14px; margin-bottom:25px;">OTP sent to: <b><?php echo $_SESSION['reset_email']; ?></b></p>
                <form method="POST">
                    <input type="text" name="otp" class="f-input" 
                           style="font-size:35px; letter-spacing:8px; font-weight:900;" 
                           placeholder="000000" maxlength="6" inputmode="numeric" 
                           oninput="this.value = this.value.replace(/[^0-9]/g, '');" 
                           required autofocus>
                    <button type="submit" name="submit_otp" class="btn-action">Verify OTP</button>
                </form>
                <div class="timer-zone">
                    <div id="t-ui">Valid for <span id="t-val" style="color:var(--main); font-weight:900;">30</span>s</div>
                    <button id="r-btn" class="btn-resend" onclick="reSend()">Request New Code</button>
                </div>

            <?php elseif($stage == 'reset'): ?>
                <p style="color:#666; font-size:14px; margin-bottom:25px;">Enter your new secure password.</p>
                <form method="POST">
                    <input type="password" name="p1" class="f-input" placeholder="New Password" required>
                    <input type="password" name="p2" class="f-input" placeholder="Confirm Password" required>
                    <button type="submit" name="submit_final" class="btn-action">Finalize Reset</button>
                </form>
            <?php endif; ?>

            <div style="margin-top:30px; border-top:1px solid #f5f5f5; padding-top:20px;">
                <a href="index.php" style="color:#ccc; text-decoration:none; font-size:13px;">← Back to Home</a>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let timeLeft = 30;
    let timerInt = null;
    const valDisplay = document.getElementById('t-val');
    const resendBtn = document.getElementById('r-btn');
    const timerUI = document.getElementById('t-ui');

    function startTimer(duration) {
        clearInterval(timerInt);
        timeLeft = duration;
        if(timerUI) timerUI.style.display = 'block';
        if(resendBtn) resendBtn.style.display = 'none';
        
        timerInt = setInterval(() => {
            timeLeft--;
            if(valDisplay) valDisplay.innerText = timeLeft;
            if(timeLeft <= 0) {
                clearInterval(timerInt);
                if(timerUI) timerUI.style.display = 'none';
                if(resendBtn) resendBtn.style.display = 'inline-block';
            }
        }, 1000);
    }

    window.reSend = function() {
        resendBtn.innerText = "Sending...";
        resendBtn.disabled = true;
        fetch('reset_password.php?service=resend')
            .then(res => res.text())
            .then(data => {
                if(data.trim() === 'success') {
                    alert('New code dispatched!');
                    startTimer(30);
                } else {
                    alert(data);
                    resendBtn.innerText = "Request New Code";
                    resendBtn.disabled = false;
                }
            });
    };

    if(valDisplay) startTimer(30);
})();
</script>

<?php include 'includes/_foot.php'; ?>