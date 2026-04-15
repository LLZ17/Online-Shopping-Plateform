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

// --- [STAGE 2: 数据库连接引用] ---
require_once '../includes/db.php';

// --- [STAGE 3: 页面基础变量] ---
$color = '#28a745'; // Member 专属翠绿色
$page_title = "PXX Mall | New Password";

// 引用公共头部 (确保侧边栏和资源路径正确)
include '../includes/_head.php'; 

// --- [STAGE 4: 前端反馈处理] ---
$error_msg = "";
$success_msg = "";

// 处理来自同一页面的提交逻辑（为了演示方便，我们将逻辑写在一起）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // 🌟 核心逻辑 1：至少六个字
    if (strlen($new_pass) < 6) {
        $error_msg = "Security Error: Password must be at least 6 characters.";
    } 
    // 🌟 核心逻辑 2：密码必须一样
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
        --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    body {
        margin: 0; padding: 0;
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        background-image: url('../images/bg.jpeg'); 
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        overflow: hidden;
    }

    /* 毛玻璃背景增强 */
    .blur-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(3px);
        z-index: 1;
    }

    .reset-viewport { 
        position: relative; z-index: 10;
        display: flex; justify-content: center; align-items: center; 
        min-height: 100vh; padding: 20px;
    }

    /* 🌟 重置卡片主体 */
    .reset-card { 
        background: var(--pxx-white); 
        width: 100%; max-width: 480px; 
        border-radius: 30px; 
        border: 1px solid rgba(0,0,0,0.05); 
        box-shadow: 0 25px 70px rgba(0,0,0,0.15); 
        text-align: center; 
        overflow: hidden; 
        animation: slideInUp 0.6s ease-out;
    }

    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(50px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* 顶部 Banner */
    .card-banner { 
        background-color: var(--pxx-green); 
        padding: 45px 20px; 
        color: var(--pxx-white); 
    }
    .card-banner h2 {
        margin: 0; font-size: 26px; font-weight: 900; 
        letter-spacing: 1.5px; text-transform: uppercase;
    }

    .card-content { padding: 45px 50px; }

    /* 表单控件 */
    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label { 
        display: block; margin-bottom: 10px; 
        font-weight: 700; font-size: 17px; color: #333; 
    }
    
    .form-input { 
        width: 100%; padding: 18px 20px; 
        border: 2px solid var(--pxx-border); 
        border-radius: 14px; font-size: 16px; 
        outline: none; transition: var(--transition); 
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
        transition: var(--transition);
        text-transform: uppercase;
        margin-top: 10px;
    }
    .btn-submit:hover { 
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
            <h2>New Password</h2>
        </div>

        <div class="card-content">
            
            <?php if($error_msg): ?>
                <div class="alert-box alert-red">
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