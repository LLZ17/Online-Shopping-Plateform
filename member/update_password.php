<?php
/**
 * =================================================================================
 * PXX MALL - PROFILE SECURITY UPDATE (V430.0)
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- 数据库连接 (根据你的目录结构，如果在 member 文件夹内则用 ../) ---
require_once '../includes/db.php';

// --- 获取当前登录用户的 ID ---
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "<script>alert('Please login first'); window.location.href='login.php';</script>";
    exit;
}

$error_msg = "";
$success_msg = "";

// --- 处理表单提交 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_update'])) {
    $new_p = $_POST['new_password'] ?? '';
    $conf_p = $_POST['confirm_password'] ?? '';

    // 1. 验证：长度必须至少 6 位
    if (strlen($new_p) < 6) {
        $error_msg = "Security Alert: Password must be at least 6 characters!";
    }
    // 2. 验证：两次输入一致
    elseif ($new_p !== $conf_p) {
        $error_msg = "Validation Error: Passwords do not match!";
    }
    else {
        try {
            // 3. 获取旧密码进行比对
            $stmt = $pdo->prepare("SELECT username, password_hashed FROM users WHERE user_id = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user) {
                // 4. 验证：不能和旧密码一样
                if (password_verify($new_p, $user['password_hashed'])) {
                    $error_msg = "Error: New password cannot be the same as your current one!";
                } else {
                    // 5. 执行更新
                    $new_hash = password_hash($new_p, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password_hashed = ? WHERE user_id = ?");
                    $update->execute([$new_hash, $user_id]);

                    $success_msg = "✅ Password updated successfully!";
                    // 在 Profile 页面通常不需要销毁 Session，只需让用户知道成功了
                }
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error occurred.";
        }
    }
}

include '../includes/_head.php'; 
?>

   

<head>
    <link rel = "stylesheet" href = "../css/app.css">
</head>

<div class="security-update-card">
    <h2 style="margin-top:0;">Security Settings</h2>
    <p style="color:#777; font-size:14px; margin-bottom:25px;">Update your account password</p>

    <?php if ($error_msg): ?>
        <div class="msg err-msg"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
        <div class="msg ok-msg"><?php echo $success_msg; ?></div>
        <button onclick="window.location.href='profile.php?page=security'" class="btn-update" style="background:#555 !important;">Back to Profile</button>
    <?php endif; ?>

    <?php if (!$success_msg): ?>
    <form method="POST">
        <input type="password" name="new_password" class="f-input" placeholder="New Password (min 6 chars)" required>
        <input type="password" name="confirm_password" class="f-input" placeholder="Confirm New Password" required>
        
        <button type="submit" name="submit_update" class="btn-update">Update Password</button>
        
        <div style="margin-top:20px;">
            <a href="profile.php" style="color:#aaa; text-decoration:none; font-size:13px;">Cancel and Return</a>
        </div>
    </form>
    <?php endif; ?>
</div>