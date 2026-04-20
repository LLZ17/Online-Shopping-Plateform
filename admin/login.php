<?php 
session_start();
require_once '../includes/db.php'; 

// 🔥 Remember Me 自动登录检测 (仅限管理员)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_pxx'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$_COOKIE['remember_pxx']]);
    $u = $stmt->fetch();
    
    // 验证确实是管理员才自动进入
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

<style>
    /* 🔥 全局背景图逻辑：确保图片 100% 清晰 */
    body {
        margin: 0; padding: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
        /* 🌟 确保路径正确：从 admin 文件夹跳回根目录找 images */
        background-image: url('../images/bg.jpeg'); 
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        background-color: #ffffff;
    }

    /* 隐藏浏览器原生图标 */
    input::-ms-reveal, input::-ms-clear { display: none; }

    /* 🌟 核心修正 1：去掉所有背景遮罩，并为底部 Coming Soon 留出空间 */
    .login-wrapper { 
        display: flex; justify-content: center; align-items: center; 
        min-height: 100vh; 
        background: transparent; /* 完全透明，露出背景图 */
        padding-bottom: 80px; /* 向上推，防止压到图片底部的文字 */
        box-sizing: border-box;
    }
    
    /* 🔥 核心修正 2：卡片保持纯白不透明，去掉模糊效果 */
    .login-card { 
        background: #ffffff; /* 🌟 纯白，确保文字清晰 */
        width: 100%; max-width: 500px; 
        border-radius: 20px; 
        border: 1.5px solid #eeeeee; 
        /* 使用轻微阴影增加层次感，但不遮挡背景细节 */
        box-shadow: 0 15px 40px rgba(0,0,0,0.1) !important; 
        text-align: center; overflow: hidden; 
    }

    /* 顶部红色 Banner */
    .portal-banner {
        background-color: <?php echo $color; ?>;
        padding: 45px 20px;
        color: #ffffff;
        font-size: 32px;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        border-bottom: 1.5px solid #eeeeee;
    }
    
    .card-body { padding: 50px 60px; }

    .welcome-text { 
        font-size: 30px; font-weight: 500; 
        margin-bottom: 40px; color: #ff0000; 
    }

    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label { 
        display: block; margin-bottom: 12px; 
        font-weight: 700; font-size: 24px; color: #333; 
    }
    
    .form-group input { 
        width: 100%; padding: 18px; 
        border: 2px solid #f0f0f0; 
        border-radius: 12px; font-size: 20px; 
        outline: none; box-sizing: border-box; 
        background-color: #ffffff;
        box-shadow: none !important;
        transition: 0.3s; 
    }
    .form-group input:focus { border-color: <?php echo $color; ?>; }

    .input-container { position: relative; width: 100%; display: flex; align-items: center; }
    .input-container input { padding-right: 60px; }
    .toggle-pw { 
        position: absolute; right: 20px; 
        cursor: pointer; color: #cccccc; font-size: 26px; z-index: 10;
    }

    .remember-row { 
        display: flex; align-items: center; gap: 12px; 
        margin: 30px 0; font-size: 18px; color: #666; cursor: pointer; 
    }
    .remember-row input { width: 22px; height: 22px; }

    .login-btn { 
        width: 100%; padding: 20px; 
        background: <?php echo $color; ?>; color: white; 
        border: none; border-radius: 12px; 
        font-weight: 900; font-size: 28px; 
        cursor: pointer; box-shadow: none !important;
        transition: 0.2s;
    }
    .login-btn:active { transform: scale(0.98); }

    .footer-links { 
        display: flex; flex-direction: column; 
        gap: 20px; margin-top: 30px; font-size: 18px; 
    }
    .link-blue { color: #1a73e8; text-decoration: none; font-weight: 600; }
    
    .back-btn {
        display: inline-flex; align-items: center; 
        justify-content: center; gap: 8px; 
        margin-top: 10px; color: #999; text-decoration: none;
    }

    /* Need Help 弹窗专用样式 */
    .modal-overlay {
        display: none; position: fixed; z-index: 9999; 
        left: 0; top: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.5); 
    }
    .modal-box {
        background: #ffffff; margin: 12% auto; 
        width: 320px; 
        max-height: 350px; 
        border-radius: 15px; overflow: hidden; 
        display: flex; flex-direction: column; 
        border: 1.5px solid #eeeeee;
    }
    .modal-body {
        padding: 25px; overflow-y: auto;
        text-align: left; font-size: 15px; line-height: 1.6; color: #444;
    }
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
            <h3 style="color:<?php echo $color; ?>; margin-top:0;">Admin Login Support</h3>
            <p><strong>Security:</strong> Restricted area. Unauthorized access attempts will be logged.</p>
            <p><strong>Lockout:</strong> Accounts lock for 30s after 3 failed attempts.</p>
            <p><strong>Credentials:</strong> Please use your issued administrative credentials. Member accounts cannot log in here.</p>
        </div>
        <div style="padding:15px; text-align:center; border-top:1px solid #eee;">
            <button onclick="toggleModal(false)" style="padding:10px 30px; background:<?php echo $color; ?>; color:white; border:none; border-radius:6px; cursor:pointer;">Got it</button>
        </div>
    </div>
</div>

<script>
function toggleModal(show) { 
    const modal = document.getElementById('helpModal');
    if(modal) { modal.style.display = show ? 'block' : 'none'; }
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
    if (event.target == modal) { toggleModal(false); }
}
</script>

<?php include '../includes/_foot.php'; ?>