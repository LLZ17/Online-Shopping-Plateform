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

            // 🔥 严格权限守卫：只有 Admin 和 S.Admin 允许通过此页面登录
            if (!in_array($user_role, ['Admin', 'S.Admin'])) {
                $_SESSION['error'] = "Access Denied: This portal is for Administrators only.";
                header("Location: login.php"); exit();
            }

            // 检查账户是否被锁定 (30秒逻辑)
            if ($user_status === 'Blocked' && !empty($user['last_login_attempt'])) {
                $time_diff = time() - strtotime($user['last_login_attempt']);
                if ($time_diff < 30) {
                    $_SESSION['error'] = "Account temporylocked. Try again in " . (30 - $time_diff) . "s.";
                    header("Location: login.php"); exit();
                }
            }

            if (password_verify($password, $user['password_hashed'])) {
                unset($_SESSION['keep_username']);
                
                // 🔥 Remember Me: 存入 Token
                if ($remember) {
                    $token = bin2hex(random_bytes(32)); 
                    $pdo->prepare("UPDATE users SET remember_token = ?, last_login = NOW(), login_attempts = 0 WHERE user_id = ?")
                        ->execute([$token, $user_id]);
                    setcookie('remember_pxx', $token, time() + (86400 * 30), "/"); 
                } else {
                    $pdo->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE user_id = ?")->execute([$user_id]);
                }

                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user_role;
                $_SESSION['profile_image'] = $user['profile_photo'];

                header("Location: portal.php");
                exit();
            } else {
                // 错误计数
                $new_count = (int)$user['login_attempts'] + 1;
                $sql = ($new_count >= 3) ? "UPDATE users SET login_attempts = ?, status = 'Blocked', last_login_attempt = NOW() WHERE user_id = ?" : "UPDATE users SET login_attempts = ?, last_login_attempt = NOW() WHERE user_id = ?";
                $pdo->prepare($sql)->execute([$new_count, $user_id]);
                $_SESSION['error'] = "Invalid Security Key.";
                header("Location: login.php"); exit();
            }
        } else {
            $_SESSION['error'] = "Administrator not found.";
            header("Location: login.php"); exit();
        }
    } catch (PDOException $e) { die("Security Error"); }
}