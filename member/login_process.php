<?php
session_start();
// 1. 强制设定大马时区
date_default_timezone_set('Asia/Kuala_Lumpur'); 
require_once '../includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']); 

    // 失败时回显用户名
    $_SESSION['keep_username'] = $username;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $user_id = $user['user_id'];
            $user_role = trim($user['role']);
            $user_status = trim($user['status']);

            // 🔥 权限拦截：严禁管理员从会员入口进入
            if ($user_role !== 'Member') {
                $_SESSION['error'] = "Unauthorized: Please use the Administrative Portal.";
                header("Location: login.php"); exit();
            }

            // 30秒锁定检查
            if ($user_status === 'Blocked' && !empty($user['last_login_attempt'])) {
                $time_diff = time() - strtotime($user['last_login_attempt']);
                if ($time_diff < 30) {
                    $_SESSION['error'] = "Too many attempts. Account locked for " . (30 - $time_diff) . "s.";
                    header("Location: login.php"); exit();
                }
            }

            // 密码验证
            if (password_verify($password, $user['password_hashed'])) {
                unset($_SESSION['keep_username']); 
                
                // 🌟 核心修复：把数据库里的头像文件名存入 Session
                // 这样 Header 一加载就能立刻读到正确的图片名
                $user_avatar = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';

                // 🔥 Remember Me: 写入 remember_token
                if ($remember) {
                    $token = bin2hex(random_bytes(32)); 
                    $pdo->prepare("UPDATE users SET remember_token = ?, last_login = NOW(), login_attempts = 0 WHERE user_id = ?")
                        ->execute([$token, $user_id]);
                    
                    // 注意：这里的 Cookie 名称要跟 Header 里的 $_COOKIE['remember_me'] 对应
                    // 你之前的代码写的是 remember_pxx，我建议统一改成 remember_me
                    setcookie('remember_me', $token, time() + (86400 * 30), "/"); 
                } else {
                    $pdo->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE user_id = ?")->execute([$user_id]);
                }

                // 2. 存入 Session 供 Header 使用
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user_role;
                $_SESSION['profile_photo'] = $user_avatar; // 👈 存入头像文件名

                header("Location: ../index.php"); // 跳转到会员主页
                exit();
            } else {
                $new_count = (int)$user['login_attempts'] + 1;
                $sql = ($new_count >= 3) ? "UPDATE users SET login_attempts = ?, status = 'Blocked', last_login_attempt = NOW() WHERE user_id = ?" : "UPDATE users SET login_attempts = ?, last_login_attempt = NOW() WHERE user_id = ?";
                $pdo->prepare($sql)->execute([$new_count, $user_id]);
                $_SESSION['error'] = "Incorrect password.";
                header("Location: login.php"); exit();
            }
        } else {
            $_SESSION['error'] = "Account not found.";
            header("Location: login.php"); exit();
        }
    } catch (PDOException $e) { 
        die("System Error: " . $e->getMessage()); 
    }
}
?>