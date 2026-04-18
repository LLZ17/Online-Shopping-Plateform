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
    /* 🔥 全局背景图逻辑 */
    body {
        margin: 0; padding: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
        background-image: url('../images/bg.jpeg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
    }

    input::-ms-reveal, input::-ms-clear { display: none; }

    /* 高级遮罩层：防止图片太亮 */
    .login-wrapper { 
        display: flex; justify-content: center; align-items: center; 
        min-height: 100vh; 
       
    }
    
    /* 🔥 磨砂玻璃卡片效果 */
    .login-card { 
        background: #ffffff; 
        backdrop-filter: blur(10px); /* 磨砂效果 */
        -webkit-backdrop-filter: blur(10px);
        width: 100%; max-width: 500px; 
        border-radius: 24px; 
        border: 1px solid rgba(255, 255, 255, 0.3); 
        box-shadow: 0 20px 40px rgba(0,0,0,0.3) !important; 
        text-align: center; overflow: hidden; 
        animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* 顶部全宽青色 Banner */
    .portal-banner {
        background-color: <?php echo $color; ?>;
        padding: 40px 20px;
        color: #ffffff;
        font-size: 32px;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .card-body { padding: 40px 60px; }

    .welcome-text { font-size: 28px; font-weight: 600; margin-bottom: 35px; color: #333; }

    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label { display: block; margin-bottom: 10px; font-weight: 700; font-size: 20px; color: #333; }
    
    .form-group input { 
        width: 100%; padding: 16px; border: 2px solid #eeeeee; 
        border-radius: 12px; font-size: 17px; outline: none; 
        box-sizing: border-box; background-color: #ffffff;
        transition: 0.3s; 
    }
    .form-group input:focus { border-color: <?php echo $color; ?>; background: #fff; }

    .input-container { position: relative; width: 100%; display: flex; align-items: center; }
    .input-container input { padding-right: 60px; }
    .toggle-pw { position: absolute; right: 20px; cursor: pointer; color: #999; font-size: 24px; z-index: 10; }

    .remember-row { display: flex; align-items: center; gap: 12px; margin: 25px 0; font-size: 16px; color: #555; cursor: pointer; }
    .remember-row input { width: 20px; height: 20px; accent-color: <?php echo $color; ?>; }

    /* 登录按钮 */
    .login-btn { 
        width: 100%; padding: 18px; background: <?php echo $color; ?>; 
        color: white; border: none; border-radius: 12px; 
        font-weight: 800; font-size: 24px; cursor: pointer; 
        transition: 0.3s;
        box-shadow: 0 8px 15px rgba(40, 167, 69, 0.2);
    }
    .login-btn:hover { filter: brightness(1.1); transform: translateY(-2px); }
    .login-btn:active { transform: scale(0.98); }

    .footer-links { display: flex; flex-direction: column; gap: 18px; margin-top: 25px; font-size: 16px; }
    .link-blue { color: #1a73e8; text-decoration: none; font-weight: 600; }
    
    .back-btn {
        display: inline-flex; align-items: center; 
        justify-content: center; gap: 8px; 
        margin-top: 10px; color: #888; text-decoration: none;
        font-weight: 500;
    }
    .back-btn:hover { color: <?php echo $color; ?>; }

    /* Need Help 弹窗 */
    .modal-overlay { 
        display: none; position: fixed; z-index: 9999; 
        left: 0; top: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.6); 
    }
    .modal-box { 
        background: #ffffff; margin: 10% auto; 
        width: 340px; max-height: 450px; 
        border-radius: 20px; overflow: hidden; 
        display: flex; flex-direction: column; 
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    }
    .modal-body {
        padding: 30px; overflow-y: auto;
        text-align: left; font-size: 15px; line-height: 1.6; color: #444;
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div class="portal-banner">
            <?php echo $tag_text; ?>
        </div>

        <div class="card-body">
            <div class="welcome-text">Hi! Welcome Back.</div>
            
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
                    <i class="fas fa-arrow-left"></i> Back to selection
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

<script>
function toggleModal(show) { 
    const modal = document.getElementById('helpModal');
    if(modal) {
        modal.style.display = show ? 'block' : 'none';
    }
}

function togglePassword(id, icon) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('helpModal');
    if (event.target == modal) {
        toggleModal(false);
    }
}
</script>

<?php include '../includes/_foot.php'; ?>