<?php 
session_start();
// 自动向上跳一级寻找数据库连接
require_once '../includes/db.php'; 

// 🔥 Member 专属 Remember Me 自动恢复逻辑
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_pxx'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$_COOKIE['remember_pxx']]);
    $u = $stmt->fetch();
    
    // 关键：只有 Member 角色才允许通过此入口自动登录
    if ($u && trim($u['role']) === 'Member') {
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['role'] = $u['role'];
        header("Location: ../index.php"); 
        exit();
    }
}

$color = '#28a745'; // 会员专属青绿色
$tag_text = 'MEMBER LOGIN';

// 🖼️ 背景图设置：在这里放入你的图片链接
$bg_image_url = 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop'; 

include '../includes/_head.php'; 
$saved_user = isset($_SESSION['keep_username']) ? $_SESSION['keep_username'] : '';
?>
<style>
    :root {
        --theme-color: <?php echo htmlspecialchars($color ?? '#28a745'); ?>;
    }
</style>

    <link rel="stylesheet" href="../css/app.css">


<div class="login-wrapper">
    <div class="login-card">
        <div class="portal-banner">
            <?php echo $tag_text; ?>
        </div>

        <div class="card-body">
            <div class="welcome-text">Welcome to PXX Mall!</div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div style="background:#fff1f0; color:#cf1322; padding:15px; border-radius:10px; margin-bottom:25px; text-align:left; border: 1px solid #ffa39e; font-weight: 600;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <input type="hidden" name="login_source" value="member">
                
                <div class="form-group">
                    <label>Username</label>
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
                <a href="../reset_password.php?role=member" class="link-blue">Forgot password?</a>                
                <span style="color: #666;">New to PXX Mall? <a href="signup.php" style="color:<?php echo $color; ?>; text-decoration:none; font-weight:bold;">Sign up here</a></span>

                <a href="javascript:void(0)" onclick="toggleModal(true)" style="color:#777; text-decoration:none; font-size:14px;">
                    <i class="fas fa-question-circle"></i> Need Help?
                </a>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
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
            <h3 style="color:<?php echo $color; ?>; margin-top:0; font-size:22px;">Member Support</h3>
            <p><strong>Login Issues?</strong><br>Ensure your username is correct. Caps lock might affect your password.</p>
            <p><strong>Forgot Password?</strong><br>Click the "Forgot password?" link to reset it via email.</p>
            <p><strong>Account Security:</strong><br>We never ask for your password via email or phone. Keep your credentials private.</p>
            <p><strong>Keep me logged in:</strong><br>This feature uses cookies to keep you signed in for 30 days. Don't use this on public computers.</p>
        </div>
        <div style="padding:20px; text-align:center; border-top:1px solid #eee;">
            <button onclick="toggleModal(false)" style="padding:12px 40px; background:<?php echo $color; ?>; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:700;">Got it</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="../js/app.js"></script>

<?php include '../includes/_foot.php'; ?>