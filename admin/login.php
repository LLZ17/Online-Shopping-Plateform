<?php
session_start();
require_once '../includes/db.php'; 

// 🔥 Remember Me 自动登录检测 (仅限管理员)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_pxx'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$_COOKIE['remember_pxx']]);
    $u = $stmt->fetch();
    
    if ($u && in_array(trim($u['role']), ['Admin', 'S.Admin'])) {
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['role'] = $u['role'];
        header("Location: portal.php"); 
        exit();
    }
}

$color = '#e60012'; // 管理员主题红
$tag_text = 'ADMIN PORTAL LOGIN';
include '../includes/_head.php'; 
$saved_user = isset($_SESSION['keep_username']) ? $_SESSION['keep_username'] : '';
?>

    <link rel="stylesheet" href="../css/app.css">



<style>
    .portal-banner, .login-btn { background-color: <?php echo $color; ?>; }
    .form-group input:focus { border-color: <?php echo $color; ?>; }
    .modal-body h3 { color: <?php echo $color; ?>; }
    .modal-box button { background: <?php echo $color; ?>; }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div class="portal-banner">
            <?php echo $tag_text; ?>
        </div>

        <div class="card-body">
            <div class="welcome-text">Welcome to Admin Portal!</div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div style="background:#fff5f5; color:#dc2626; padding:15px; border-radius:10px; margin-bottom:25px; text-align:left; border: 1px solid #fee2e2;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <input type="hidden" name="login_source" value="admin">
                
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($saved_user); ?>" placeholder="Enter your username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-container">
                        <input type="password" name="password" id="pw_main" placeholder="Enter your password" required>
                        <i class="fas fa-eye toggle-pw" onclick="togglePassword('pw_main', this)"></i>
                    </div>
                </div>

                <label class="remember-row">
                    <input type="checkbox" name="remember"> Keep me logged in
                </label>

                <button type="submit" class="login-btn">LOGIN</button>
            </form>

            <div class="footer-links">
                <a href="../reset_password.php?role=admin" class="link-blue">Forgot password?</a>
                
                <a href="javascript:void(0)" onclick="toggleModal(true)" style="color:#777; text-decoration:none;">
                    <i class="fas fa-question-circle"></i> Need Help?
                </a>

                <hr style="border: 0; border-top: 1.5px solid #eeeeee; margin: 10px 0;">
                <a href="../login_selection.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Login Selection
                </a>
            </div>
        </div>
    </div>
</div>

<div id="helpModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-body">
            <h3 style="margin-top:0;">Admin Login Support</h3>
            <p><strong>Security:</strong> Restricted area. Unauthorized access attempts will be logged.</p>
            <p><strong>Lockout:</strong> Accounts lock for 30s after 3 failed attempts.</p>
            <p><strong>Credentials:</strong> Please use your issued administrative credentials. Member accounts cannot log in here.</p>
        </div>
        <div style="padding:15px; text-align:center; border-top:1px solid #eee;">
            <button onclick="toggleModal(false)" style="padding:10px 30px; color:white; border:none; border-radius:6px; cursor:pointer;">Got it</button>
        </div>
    </div>
</div>

<script src="../js/app.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/_foot.php'; ?>