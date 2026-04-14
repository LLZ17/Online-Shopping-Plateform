<?php
/**
 * =================================================================================
 * PXX MALL - MEMBER PASSWORD RESET SYSTEM (V190.0 - SCROLL & SECURITY FIX)
 * =================================================================================
 * 核心修正：
 * 1. [安全] 严禁新密码与旧密码相同。
 * 2. [布局] 修复无法滑动问题，确保页面内容完整展示。
 * 3. [加密] 采用标准的 PASSWORD_DEFAULT 算法，对接登录系统。
 * =================================================================================
 */

// --- [STAGE 1: 核心初始化] ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [STAGE 2: 数据库依赖] ---
require_once '../includes/db.php';

// --- [STAGE 3: 页面基础变量] ---
$color = '#28a745'; 
$page_title = "PXX Mall | Secure Reset";

// 引用公共头部
include '../includes/_head.php'; 

// --- [STAGE 4: 高级逻辑处理引擎] ---
$error_msg = "";
$success_msg = "";

// 🌟 演示逻辑：假设我们要重置的是当前 Session 记录的 Email
// 如果你还没做 Session 传递，这里可以先指定一个测试账号
$user_email = $_SESSION['reset_email'] ?? 'member@example.com'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // 1. 长度校验
    if (strlen($new_pass) < 6) {
        $error_msg = "Security Policy: Password must be at least 6 characters.";
    } 
    // 2. 一致性校验
    elseif ($new_pass !== $confirm_pass) {
        $error_msg = "Validation Error: Passwords do not match.";
    } 
    else {
        try {
            // 3. 🌟 获取旧密码哈希值进行比对
            $stmt = $pdo->prepare("SELECT password_hashed FROM users WHERE email = ? AND role = 'Member' LIMIT 1");
            $stmt->execute([$user_email]);
            $user_data = $stmt->fetch();

            if ($user_data) {
                $old_hash = $user_data['password_hashed'];
                
                // 🌟 核心：新旧密码不能一样
                if (password_verify($new_pass, $old_hash)) {
                    $error_msg = "Security Alert: New password cannot be the same as your old password.";
                } else {
                    // 4. 执行更新 (使用标准加密，确保能正常登录)
                    $new_hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE users SET password_hashed = ? WHERE email = ?");
                    $update_stmt->execute([$new_hashed_pass, $user_email]);
                    
                    $success_msg = "Password updated! You can now use your new credentials to login.";
                }
            } else {
                $error_msg = "Account Error: Could not identify the member account.";
            }
        } catch (PDOException $e) {
            $error_msg = "System Error: Database transaction failed.";
        }
    }
}
?>

<style>
    /* ============================================================
       [PRO STYLING] 极致扁平化 CSS 引擎
       ============================================================ */
    :root {
        --pxx-green: <?php echo $color; ?>;
        --pxx-green-dark: #218838;
        --pxx-white: #ffffff;
        --pxx-border: #f0f0f0;
        --trans: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    body {
        margin: 0; padding: 0;
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        background-image: url('../images/bg.jpeg'); 
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        /* 🌟 核心修正：允许页面滚动，看到页脚 */
        overflow-y: auto !important; 
        min-height: 100vh;
    }

    /* 背景模糊层 */
    .glass-mask {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(3px);
        z-index: 1;
    }

    .reset-viewport { 
        position: relative; z-index: 10;
        display: flex; justify-content: center; align-items: center; 
        min-height: 100vh; padding: 50px 20px; /* 增加上下间距 */
    }

    /* 🌟 卡片主体 */
    .reset-card { 
        background: var(--pxx-white); 
        width: 100%; max-width: 480px; 
        border-radius: 30px; 
        border: 1px solid rgba(0,0,0,0.05); 
        box-shadow: 0 25px 70px rgba(0,0,0,0.15); 
        text-align: center; 
        overflow: hidden; 
        animation: cardEnter 0.6s ease-out;
    }

    @keyframes cardEnter {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .card-banner { 
        background-color: var(--pxx-green); 
        padding: 40px 20px; 
        color: var(--pxx-white); 
    }
    .card-banner h2 {
        margin: 0; font-size: 26px; font-weight: 900; 
        letter-spacing: 1.5px; text-transform: uppercase;
        font-family: 'Montserrat', sans-serif;
    }

    .card-form-body { padding: 45px 50px; }

    /* 表单组件 */
    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label { 
        display: block; margin-bottom: 12px; 
        font-weight: 700; font-size: 17px; color: #333; 
    }
    
    .form-input { 
        width: 100%; padding: 18px 20px; 
        border: 2px solid var(--pxx-border); 
        border-radius: 14px; font-size: 16px; 
        outline: none; transition: var(--trans); 
        background: #fafafa;
    }
    .form-input:focus { 
        border-color: var(--pxx-green); 
        background: #fff;
        box-shadow: 0 0 0 5px rgba(40, 167, 69, 0.1);
    }

    /* 提交按钮 */
    .btn-update { 
        width: 100%; padding: 20px; 
        background: var(--pxx-green); 
        color: white; border: none; 
        border-radius: 14px; 
        font-weight: 900; font-size: 20px; 
        cursor: pointer; 
        transition: var(--trans);
        text-transform: uppercase;
    }
    .btn-update:hover { 
        background: var(--pxx-green-dark);
        transform: translateY(-2px);
    }

    /* 提示框 */
    .alert {
        padding: 15px; border-radius: 12px; margin-bottom: 25px;
        font-size: 14px; font-weight: 600; text-align: left;
        display: flex; align-items: center; gap: 10px;
    }
    .alert-error { background: #fff1f0; color: #cf1322; border: 1px solid #ffa39e; }
    .alert-success { background: #f6ffed; color: #389e0d; border: 1px solid #b7eb8f; }

    .card-footer { margin-top: 30px; border-top: 1px solid var(--pxx-border); padding-top: 25px; }
    .back-to-login { 
        color: #999; text-decoration: none; 
        font-size: 14px; font-weight: 600; 
        display: inline-flex; align-items: center; gap: 8px;
    }
    .back-to-login:hover { color: var(--pxx-green); }
</style>

<div class="glass-mask"></div>

<div class="reset-viewport">
    <div class="reset-card">
        <div class="card-banner">
            <h2>Secure Reset</h2>
        </div>

        <div class="card-form-body">
            
            <?php if($error_msg): ?>
                <div class="alert alert-error">
                    <i class="fas fa-times-circle"></i>
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <?php if($success_msg): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-input" 
                           placeholder="At least 6 characters" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-input" 
                           placeholder="Confirm new password" required>
                </div>

                <button type="submit" class="btn-update">
                    Update Password
                </button>
            </form>
            
            <div class="card-footer">
                <a href="login.php" class="back-to-login">
                    <i class="fas fa-arrow-left"></i> 
                    Return to Login Portal
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
/**
 * 🌟 底部引用：在此处引用公共 Footer，它会自动出现在页面最下方
 */
include '../includes/_foot.php'; 
?>