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

include '../includes/_head.php'; 
$saved_user = isset($_SESSION['keep_username']) ? $_SESSION['keep_username'] : '';
?>

<style>
    /* 🔥 极简纯白设计：全局背景白色，无阴影 */
    body {
        background-color: #ffffff; 
        margin: 0; padding: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    input::-ms-reveal, input::-ms-clear { display: none; }

    .login-wrapper { 
        display: flex; justify-content: center; align-items: center; 
        min-height: 100vh; background-color: #ffffff; 
    }
    
    /* 🔥 卡片：纯白 + 1.5px 细边框 + 无阴影 */
    .login-card { 
        background: #ffffff; 
        width: 100%; max-width: 500px; 
        border-radius: 20px; 
        border: 1.5px solid #eeeeee; 
        box-shadow: none !important; 
        text-align: center; overflow: hidden; 
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
        border-bottom: 1.5px solid #eeeeee;
    }
    
    .card-body { padding: 50px 60px; }

    .welcome-text { font-size: 30px; font-weight: 500; margin-bottom: 40px; color: #25893c; }

    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label { display: block; margin-bottom: 12px; font-weight: 700; font-size: 24px; color: #333; }
    
    /* 输入框样式：去掉阴影 */
    .form-group input { 
        width: 100%; padding: 18px; border: 2px solid #f0f0f0; 
        border-radius: 12px; font-size: 18px; outline: none; 
        box-sizing: border-box; background-color: #ffffff;
        box-shadow: none !important; transition: 0.3s; 
    }
    .form-group input:focus { border-color: <?php echo $color; ?>; }

    /* 密码图标容器 */
    .input-container { position: relative; width: 100%; display: flex; align-items: center; }
    .input-container input { padding-right: 60px; }
    .toggle-pw { position: absolute; right: 20px; cursor: pointer; color: #cccccc; font-size: 26px; z-index: 10; }

    .remember-row { display: flex; align-items: center; gap: 12px; margin: 30px 0; font-size: 18px; color: #666; cursor: pointer; }
    .remember-row input { width: 22px; height: 22px; }

    /* 登录按钮：去掉阴影 */
    .login-btn { 
        width: 100%; padding: 20px; background: <?php echo $color; ?>; 
        color: white; border: none; border-radius: 12px; 
        font-weight: 900; font-size: 26px; cursor: pointer; 
        box-shadow: none !important; transition: 0.2s;
    }
    .login-btn:active { transform: scale(0.98); }

    .footer-links { display: flex; flex-direction: column; gap: 20px; margin-top: 30px; font-size: 18px; }
    .link-blue { color: #1a73e8; text-decoration: none; font-weight: 600; }
    
    .back-btn {
        display: inline-flex; align-items: center; 
        justify-content: center; gap: 8px; 
        margin-top: 10px; color: #999; text-decoration: none;
    }

    /* 🔥 Need Help 弹窗样式：缩小宽度 + 允许内部滑动 */
    .modal-overlay { 
        display: none; position: fixed; z-index: 9999; 
        left: 0; top: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.4); 
    }
    .modal-box { 
        background: #ffffff; margin: 12% auto; 
        width: 320px; max-height: 350px; 
        border-radius: 15px; overflow: hidden; 
        display: flex; flex-direction: column; 
        border: 1.5px solid #eeeeee; box-shadow: none;
    }
    .modal-body {
        padding: 25px; overflow-y: auto; /* 核心：允许内部滚动 */
        text-align: left; font-size: 15px; line-height: 1.6; color: #444;
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div class="portal-banner">
            <?php echo $tag_text; ?>
        </div>

        <div class="card-body">
            <div class="welcome-text">Welcome to PXX Mall!</div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div style="background:#fff5f5; color:#dc2626; padding:15px; border-radius:10px; margin-bottom:25px; text-align:left; border: 1px solid #fee2e2;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
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

                <button type="submit" class="login-btn">LOGIN IN</button>
            </form>

            <div class="footer-links">
                <a href="forgot_password.php" class="link-blue">Forgot password?</a>
                
                <span style="color: #666;">Not a member? <a href="signup.php" style="color:<?php echo $color; ?>; text-decoration:none; font-weight:bold;">Sign up</a></span>

                <a href="javascript:void(0)" onclick="toggleModal(true)" style="color:#777; text-decoration:none;">
                    <i class="fas fa-question-circle"></i> Need Help?
                </a>

                <hr style="border: 0; border-top: 1.5px solid #eeeeee; margin: 10px 0;">
                <a href="../login_selection.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Login Selection page
                </a>
            </div>
        </div>
    </div>
</div>

<div id="helpModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-body">
            <h3 style="color:<?php echo $color; ?>; margin-top:0;">Member Login Support</h3>
            <p><strong>Login Issues?</strong><br>Ensure your username and password are correct. Member accounts are separate from Admin accounts.</p>
            <p><strong>Forgot Password?</strong><br>Use the "Forgot password?" link on the main page to receive a reset link via your registered email.</p>
            <p><strong>New User?</strong><br>If you don't have an account, click the "Sign up" link to join PXX Mall and enjoy exclusive shopping benefits.</p>
            <p><strong>Cookie Usage:</strong><br>Selecting "Keep me logged in" will store a secure token on this browser for 30 days so you don't have to sign in repeatedly.</p>
        </div>
        <div style="padding:15px; text-align:center; border-top:1px solid #eee;">
            <button onclick="toggleModal(false)" style="padding:10px 30px; background:<?php echo $color; ?>; color:white; border:none; border-radius:6px; cursor:pointer;">Got it</button>
        </div>
    </div>
</div>

<script>
// 控制弹窗显示/隐藏
function toggleModal(show) { 
    const modal = document.getElementById('helpModal');
    if(modal) {
        modal.style.display = show ? 'block' : 'none';
    }
}

// 切换密码可见性
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

// 点击背景关闭弹窗
window.onclick = function(event) {
    const modal = document.getElementById('helpModal');
    if (event.target == modal) {
        toggleModal(false);
    }
}
</script>

<?php include '../includes/_foot.php'; ?>