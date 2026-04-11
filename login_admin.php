<?php 
session_start();
require_once 'includes/db.php'; 

// 🔥 Remember Me 自动恢复逻辑
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_pxx'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$_COOKIE['remember_pxx']]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['user_id'] = $u['user_id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['role'] = $u['role'];
        header("Location: " . (in_array($u['role'], ['Admin', 'S.Admin']) ? "admin_portal.php" : "index.php"));
        exit();
    }
}

$is_admin = (basename($_SERVER['PHP_SELF']) == 'login_admin.php');
$color = $is_admin ? '#e60012' : '#28a745';
$tag_text = $is_admin ? 'ADMIN PORTAL LOGIN' : 'MEMBER LOGIN';

include 'includes/_head.php'; 
$saved_user = isset($_SESSION['keep_username']) ? $_SESSION['keep_username'] : '';
?>

<style>
    /* 🔥 全屏背景图设置 */
    body {
        background-image: url('YOUR_IMAGE_LINK_HERE'); /* 填入你的背景图链接 */
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        margin: 0; padding: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    input::-ms-reveal, input::-ms-clear { display: none; }

    .login-wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: rgba(0,0,0,0.3); }
    
    /* 🔥 主卡片容器 */
    .login-card { 
        background: white; 
        width: 100%; 
        max-width: 500px; 
        border-radius: 20px; 
        box-shadow: 0 20px 60px rgba(0,0,0,0.3); 
        text-align: center; 
        overflow: hidden; /* 确保顶部色块贴合圆角 */
    }

    /* 🔥 还原图片中的全宽色块效果 */
    .portal-banner {
        background-color: <?php echo $color; ?>;
        padding: 40px 20px;
        color: #ffffff;
        font-size: 32px;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .card-body { padding: 50px 60px; }

    .welcome-text { font-size: 33px; font-weight: 900; margin-bottom: 40px; color: #111; }

    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label { display: block; margin-bottom: 12px; font-weight: 700; font-size: 22px; color: #333; }
    
    .form-group input { 
        width: 100%; padding: 18px; border: 2px solid #eee; 
        border-radius: 12px; font-size: 18px; outline: none; 
        box-sizing: border-box; transition: 0.3s; 
    }
    .form-group input:focus { border-color: <?php echo $color; ?>; }

    /* 图标入框 */
    .input-container { position: relative; width: 100%; display: flex; align-items: center; }
    .input-container input { padding-right: 60px; }
    .toggle-pw { position: absolute; right: 20px; cursor: pointer; color: #ccc; font-size: 26px; z-index: 10; }

    .remember-row { display: flex; align-items: center; gap: 12px; margin: 30px 0; font-size: 18px; color: #666; cursor: pointer; }
    .remember-row input { width: 20px; height: 20px; }

    .login-btn { 
        width: 100%; padding: 20px; background: <?php echo $color; ?>; 
        color: white; border: none; border-radius: 12px; 
        font-weight: 900; font-size: 26px; cursor: pointer; transition: 0.3s;
    }

    .footer-links { display: flex; flex-direction: column; gap: 20px; margin-top: 30px; font-size: 18px; }
    .link-blue { color: #1a73e8; text-decoration: none; font-weight: 600; }
    
    /* 🔥 Back 按钮样式 */
    .back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 10px;
        color: #777;
        text-decoration: none;
        font-weight: 500;
        transition: 0.3s;
    }
    .back-btn:hover { color: <?php echo $color; ?>; }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div class="portal-banner">
            <?php echo $tag_text; ?>
        </div>

        <div class="card-body">
            <div class="welcome-text">Welcome to PXX Mall!</div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div style="background:#fee2e2; color:#dc2626; padding:15px; border-radius:10px; margin-bottom:25px; text-align:left; border-left:6px solid #dc2626;">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <input type="hidden" name="login_source" value="<?php echo $is_admin ? 'admin' : 'member'; ?>">
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($saved_user); ?>" placeholder="Enter username" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-container">
                        <input type="password" name="password" id="pw_main" placeholder="••••••••" required>
                        <i class="fas fa-eye toggle-pw" onclick="togglePassword('pw_main', this)"></i>
                    </div>
                </div>

                <label class="remember-row">
                    <input type="checkbox" name="remember"> Keep me logged in
                </label>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="footer-links">
                <a href="forgot_password.php" class="link-blue">Forgot password?</a>
                
                <?php if(!$is_admin): ?>
                    <span style="color: #666;">Not a member? <a href="signup.php" style="color:#28a745; text-decoration:none; font-weight:bold;">Sign up</a></span>
                <?php endif; ?>

                <a href="javascript:void(0)" onclick="toggleModal(true)" style="color:#777; text-decoration:none;">
                    <i class="fas fa-question-circle"></i> Need Help?
                </a>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
                <a href="login.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Portal Selection
                </a>
            </div>
        </div>
    </div>
</div>

<div id="helpModal" style="display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:white; margin:15% auto; width:320px; max-height:380px; border-radius:15px; overflow:hidden; display:flex; flex-direction:column; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <div style="padding:25px; overflow-y:auto; text-align:left; font-size:15px; line-height:1.6; color:#444;">
            <h3 style="color:<?php echo $color; ?>; margin-top:0;">System Help</h3>
            <p><strong>Admin Access:</strong> Requires valid staff credentials and secondary authorization.</p>
            <p><strong>Member Access:</strong> For registered mall shoppers only.</p>
        </div>
        <div style="padding:15px; text-align:center; border-top:1px solid #eee;">
            <button onclick="toggleModal(false)" style="padding:10px 30px; background:<?php echo $color; ?>; color:white; border:none; border-radius:6px; cursor:pointer;">Got it</button>
        </div>
    </div>
</div>

<script>
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
function toggleModal(show) { document.getElementById('helpModal').style.display = show ? 'block' : 'none'; }
</script>

<?php include 'includes/_foot.php'; ?>