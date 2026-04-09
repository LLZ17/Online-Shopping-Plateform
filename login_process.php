<?php
session_start();

/**
 * PXX Mall - 登录处理核心程序
 * 功能：密码哈希验证、身份分流跳转、3次错误锁定、Remember Me 令牌
 */

// 1. 引入数据库连接 (请确保你的 db.php 路径正确)
require_once 'includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收表单输入的用户名 and 密码
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // 2. 去数据库找这个用户
        // 使用 PDO 预处理防止 SQL Injection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            
            // --- 【SECURITY: 3 次错误锁定检查逻辑】 ---
            $now = time();
            // 确保你的数据库有这两列：login_attempts (int), last_attempt_time (datetime)
            $login_attempts = isset($user['login_attempts']) ? $user['login_attempts'] : 0;
            $last_attempt_time = isset($user['last_attempt_time']) ? strtotime($user['last_attempt_time']) : 0;

            if ($login_attempts >= 3) {
                // 检查最后一次尝试时间是否在 10 分钟 (600秒) 内
                if (($now - $last_attempt_time) < 600) {
                    echo "<script>
                        alert('Account Locked! Too many failed attempts. Please try again after 10 minutes.'); 
                        window.history.back();
                    </script>";
                    exit();
                } else {
                    // 时间已过，在后台静默重置尝试次数
                    $resetStmt = $pdo->prepare("UPDATE users SET login_attempts = 0 WHERE user_id = ?");
                    $resetStmt->execute([$user['user_id']]);
                }
            }
            // --- 【锁定检查结束】 ---

            // 🔥 核心：验证加密后的密码 (Password Hashing) 🔥
            // 确保数据库字段名叫 password_hashed
            if (password_verify($password, $user['password_hashed'])) {
                
                // --- 【登录成功逻辑】 ---

                // 1. 存入 Session 供全站使用
                $_SESSION['user_id'] = $user['user_id']; 
                $_SESSION['username'] = $user['username'];
                
                // ⭐ 处理 Role 格式：转小写并去空格，确保 Admin 能对上 admin
                $current_role = strtolower(trim($user['role'])); 
                $_SESSION['role'] = $current_role;

                // 2. 存入头像逻辑
                $_SESSION['profile_image'] = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';

                // 3. 写入登录记录与 Remember Me 令牌
                $token = bin2hex(random_bytes(32)); // 生成安全随机令牌
                
                // 更新 SQL：记录最后登录时间、重置错误次数、保存令牌
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW(), remember_token = ?, login_attempts = 0 WHERE user_id = ?");
                $updateStmt->execute([$token, $user['user_id']]);

                // 4. 写入 Cookie：保存 30 天 (Remember Me)
                setcookie('remember_me', $token, time() + (86400 * 30), "/");

                // --- ⭐ 核心：分流跳转逻辑 ⭐ ---
                if ($current_role === 'admin') {
                    // 如果是管理员，跳去 admin_dashboard.php
                    echo "<script>
                        alert('Welcome back, Admin! Accessing Portal...'); 
                        window.location.href='admin_portal.php'; 
                    </script>";
                } else {
                    // 如果是普通会员，跳去 index.php
                    echo "<script>
                        alert('Login Successful! Welcome " . $user['username'] . "'); 
                        window.location.href='index.php'; 
                    </script>";
                }
                exit();

            } else {
                // --- 【密码错误逻辑】 ---
                // 累加错误次数，并记录当前时间
                $failStmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_attempt_time = NOW() WHERE user_id = ?");
                $failStmt->execute([$user['user_id']]);
                
                // 重新获取最新次数计算剩余机会
                $checkStmt = $pdo->prepare("SELECT login_attempts FROM users WHERE user_id = ?");
                $checkStmt->execute([$user['user_id']]);
                $latest = $checkStmt->fetch();
                $remaining = 3 - $latest['login_attempts'];

                if ($remaining > 0) {
                    echo "<script>alert('Wrong Password! You have $remaining attempts left.'); window.history.back();</script>";
                } else {
                    echo "<script>alert('Account locked for 10 minutes due to multiple failed attempts.'); window.history.back();</script>";
                }
            }
        } else {
            // 用户名不存在
            echo "<script>alert('User not found! Please check your Admin ID.'); window.history.back();</script>";
        }
    } catch (PDOException $e) {
        // 数据库连接或执行报错
        die("Critical Database Error: " . $e->getMessage());
    }
}
?>