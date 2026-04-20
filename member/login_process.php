<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur'); 
require_once '../includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']); 

    $_SESSION['keep_username'] = $username;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $user_id = $user['user_id'];
            $user_role = trim($user['role']);
            $user_status = trim($user['status']);
            $attempts = (int)$user['login_attempts'];

            // 1. 权限拦截
            if ($user_role !== 'Member') {
                $_SESSION['error'] = "Unauthorized: Please use the Administrative Portal.";
                header("Location: login.php"); exit();
            }

            // 2. 锁定检查 (30秒锁定)
            if ($user_status === 'Blocked' && !empty($user['last_login_attempt'])) {
                $time_diff = time() - strtotime($user['last_login_attempt']);
                if ($time_diff < 30) {
                    $wait_time = 30 - $time_diff;
                    $_SESSION['error'] = "Too many attempts. Account locked. Please wait {$wait_time}s.";
                    header("Location: login.php"); exit();
                }
            }

            // 3. 密码验证
            if (password_verify($password, $user['password_hashed'])) {
                unset($_SESSION['keep_username']); 
                
                $user_avatar = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';

                // --- 修复点：登录成功后重置 login_attempts 和 status ---
                if ($remember) {
                    $token = bin2hex(random_bytes(32)); 
                    $sql = "UPDATE users SET remember_token = ?, last_login = NOW(), login_attempts = 0, status = 'Active' WHERE user_id = ?";
                    $pdo->prepare($sql)->execute([$token, $user_id]);
                    setcookie('remember_me', $token, time() + (86400 * 30), "/"); 
                } else {
                    $sql = "UPDATE users SET last_login = NOW(), login_attempts = 0, status = 'Active' WHERE user_id = ?";
                    $pdo->prepare($sql)->execute([$user_id]);
                }

                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user_role;
                $_SESSION['profile_photo'] = $user_avatar;

                header("Location: ../index.php");
                exit();

            } else {
                // 4. 密码错误处理：计算剩余次数
                $new_count = $attempts + 1;
                $max_attempts = 3;
                $remaining = $max_attempts - $new_count;

                if ($new_count >= $max_attempts) {
                    // 超过3次，执行锁定
                    $sql = "UPDATE users SET login_attempts = ?, status = 'Blocked', last_login_attempt = NOW() WHERE user_id = ?";
                    $pdo->prepare($sql)->execute([$new_count, $user_id]);
                    $_SESSION['error'] = "Too many failed attempts. Your account has been locked for 30s.";
                } else {
                    // 还没到3次，提醒剩余机会
                    $sql = "UPDATE users SET login_attempts = ?, last_login_attempt = NOW() WHERE user_id = ?";
                    $pdo->prepare($sql)->execute([$new_count, $user_id]);
                    $_SESSION['error'] = "Incorrect password. You have **{$remaining}** attempts left.";
                }
                
                header("Location: login.php"); 
                exit();
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