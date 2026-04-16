<?php
/**
 * =================================================================================
 * PXX MALL - UNIVERSAL DUAL-THEME RESET ENGINE (V388.0)
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

// --- [配置与白名单] ---
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

// --- [STAGE 2: 强制初始化逻辑 - 解决 Session 残留问题] ---
$referer = $_SERVER['HTTP_REFERER'] ?? '';
// 如果来源页面包含 login 字样，说明是新的一次点击，强制清空旧数据
if (strpos(strtolower($referer), 'login') !== false || !isset($_SESSION['reset_step'])) {
    unset($_SESSION['reset_username']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_step']);
    unset($_SESSION['req_count']);
    
    // 判定身份模式
    $_SESSION['reset_role'] = (strpos(strtolower($referer), 'admin') !== false) ? 'Admin' : 'Member';
    $_SESSION['theme_color'] = ($_SESSION['reset_role'] == 'Admin') ? '#e60012' : '#28a745';
    $_SESSION['reset_step'] = 'identify'; 
}

$role  = $_SESSION['reset_role'];
$color = $_SESSION['theme_color'];
$stage = $_SESSION['reset_step'];
$error_feedback = "";

/**
 * 发信引擎 - 邮件内容绑定 Username
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
        $mail->Subject = "Verification Code for $u: [$otp]";
        $mail->Body = "
        <div style='background:#f4f4f4; padding:50px; font-family:Arial;'>
            <div style='background:#fff; max-width:500px; margin:auto; border-radius:25px; overflow:hidden; border-top:12px solid $c;'>
                <div style='padding:40px; text-align:center;'>
                    <h2 style='color:$c;'>Security Sync</h2>
                    <p>Hello <b>$u</b>, you are requesting a password reset for your <b>$r</b> account.</p>
                    <p>Your verification code is:</p>
                    <div style='font-size:50px; font-weight:900; letter-spacing:12px; color:$c; margin:25px 0;'>$otp</div>
                    <p style='color:red; font-size:12px;'>This code is valid for 30 seconds only.</p>
                </div>
            </div>
        </div>";
        return $mail->send() ? "success" : "failed";
    } catch (Exception $ex) { return $ex->getMessage(); }
}

// --- [STAGE 3: 逻辑提交控制] ---

// A. 处理 AJAX 重发 (需校验 Session 是否已被清空)
if (isset($_GET['service']) && $_GET['service'] == 'resend') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: text/plain');
    if (empty($_SESSION['reset_username'])) {
        echo "AUTH_EXPIRED: Please restart the process from login page.";
    } elseif (!canRequestCode()) {
        echo "LIMIT_REACHED";
    } else {
        echo sendDynamicOTP($pdo, $_SESSION['reset_username'], $_SESSION['reset_email'], $role, $color);
    }
    exit();
}

// B. 第一步：身份核验 (不通过则不发码)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_identity'])) {
    $u = trim($_POST['u'] ?? ''); 
    $e = trim($_POST['e'] ?? '');
    $domain = strtolower(substr(strrchr($e, "@"), 1));
    $is_valid_domain = in_array($domain, $allowed_domains) || (strpos($domain, '.edu') !== false);

    if (empty($u) || empty($e)) {
        $error_feedback = "Username and Email must be filled.";
    } elseif (!$is_valid_domain) {
        $error_feedback = "Domain <b>@$domain</b> not authorized.";
    } elseif (!canRequestCode()) {
        $error_feedback = "Too many requests. Please wait a minute.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND email = ? AND role = ? LIMIT 1");
        $stmt->execute([$u, $e, $role]);
        if ($stmt->fetch()) {
            $_SESSION['reset_username'] = $u;
            $_SESSION['reset_email'] = $e;
            $_SESSION['reset_step'] = 'verify';
            sendDynamicOTP($pdo, $u, $e, $role, $color);
            header("Location: reset_password.php"); exit();
        } else {
            $error_feedback = "Account not found in $role database.";
        }
    }
}

// C. 第二步：验证 OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_otp'])) {
    $code = trim($_POST['otp']);
    $stmt = $pdo->prepare("SELECT email_code, email_expiry FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$_SESSION['reset_username'], $role]);
    $db = $stmt->fetch();

    if ($db && $db['email_code'] === $code && strtotime($db['email_expiry']) >= time()) {
        $_SESSION['reset_step'] = 'reset';
        header("Location: reset_password.php"); exit();
    } else { $error_feedback = "Invalid or expired code."; }
}

// D. 第三步：重置密码
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_final'])) {
    $p1 = $_POST['p1']; $p2 = $_POST['p2'];
    if (strlen($p1) < 6) { $error_feedback = "Min 6 characters."; }
    elseif ($p1 !== $p2) { $error_feedback = "Passwords do not match."; }
    else {
        $h = password_hash($p1, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hashed = ?, email_code = NULL WHERE username = ? AND role = ?")
            ->execute([$h, $_SESSION['reset_username'], $role]);
        $target = ($role == 'Admin') ? 'admin/login.php' : 'member/login.php';
        session_destroy();
        echo "<script>alert('Password Reset Success!'); window.location.href='$target';</script>"; exit();
    }
}

include 'includes/_head.php'; 
?>

<style>
    :root { --main: <?php echo $color; ?>; }
    body { margin: 0; background: url('images/bg.jpeg') center/cover fixed; font-family: 'Poppins', sans-serif; }
    .viewport { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
    .auth-card { background: #fff; width: 100%; max-width: 480px; border-radius: 30px; box-shadow: 0 30px 80px rgba(0,0,0,0.2); overflow: hidden; border-top: 15px solid var(--main); }
    .card-body { padding: 40px; text-align: center; }
    .f-input { width: 100%; padding: 18px; border: 2px solid #eee; border-radius: 15px; font-size: 16px; margin-bottom: 20px; outline: none; box-sizing: border-box; text-align: center; }
    .btn-action { width: 100%; padding: 18px; background: var(--main); color: #fff; border: none; border-radius: 12px; font-weight: 800; font-size: 18px; cursor: pointer; }
    .alert-box { background: #fff1f0; border: 1px solid #ffa39e; color: #cf1322; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; font-weight: bold; }
    .timer-zone { margin-top: 25px; color: #999; }
    .btn-resend { color: var(--main); text-decoration: underline; background: none; border: none; font-weight: bold; cursor: pointer; display: none; }
</style>

<div class="viewport">
    <div class="auth-card">
        <div class="card-body">
            <h1 style="margin-bottom: 30px;"><?php echo $role; ?> Identity</h1>
            <?php if($error_feedback): ?> <div class="alert-box"><?php echo $error_feedback; ?></div> <?php endif; ?>

            <?php if($stage == 'identify'): ?>
                <form method="POST">
                    <input type="text" name="u" class="f-input" placeholder="Username" autocomplete="off" required>
                    <input type="email" name="e" class="f-input" placeholder="Email Address" autocomplete="off" required>
                    <button type="submit" name="submit_identity" class="btn-action">Verify Account</button>
                </form>

            <?php elseif($stage == 'verify'): ?>
                <p>Hello <b><?php echo $_SESSION['reset_username']; ?></b>, enter your code:</p>
                <form method="POST">
                    <input type="text" name="otp" class="f-input" style="font-size:35px; letter-spacing:8px; font-weight:900;" 
                           maxlength="6" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required autofocus>
                    <button type="submit" name="submit_otp" class="btn-action">Confirm OTP</button>
                </form>
                <div class="timer-zone">
                    <div id="t-ui">Link expires in <span id="t-val">30</span>s</div>
                    <button id="r-btn" class="btn-resend" onclick="reSend()">Request New Code</button>
                </div>

            <?php elseif($stage == 'reset'): ?>
                <form method="POST">
                    <input type="password" name="p1" class="f-input" placeholder="New Password" required>
                    <input type="password" name="p2" class="f-input" placeholder="Repeat New Password" required>
                    <button type="submit" name="submit_final" class="btn-action">Sync Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function() {
    let timeLeft = 30;
    let timerInt = null;
    const vDisp = document.getElementById('t-val'), rBtn = document.getElementById('r-btn'), tUI = document.getElementById('t-ui');

    function startTimer(s) {
        if(!vDisp) return;
        clearInterval(timerInt);
        timeLeft = s; tUI.style.display = 'block'; rBtn.style.display = 'none';
        timerInt = setInterval(() => {
            timeLeft--; vDisp.innerText = timeLeft;
            if(timeLeft <= 0) {
                clearInterval(timerInt); tUI.style.display = 'none'; rBtn.style.display = 'inline-block';
            }
        }, 1000);
    }

    window.reSend = function() {
        rBtn.innerText = "Syncing..."; rBtn.disabled = true;
        fetch('reset_password.php?service=resend')
            .then(res => res.text())
            .then(data => {
                const s = data.trim();
                if(s === 'success') { alert('New code has been dispatched.'); startTimer(30); }
                else { 
                    alert('Action Blocked: ' + s); 
                    rBtn.innerText = "Request New Code"; rBtn.disabled = false; 
                }
            }).catch(() => { rBtn.disabled = false; });
    };

    if(vDisp) startTimer(30);
})();
</script>

<?php include 'includes/_foot.php'; ?>