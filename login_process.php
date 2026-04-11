<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur'); 
require_once 'includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $login_source = isset($_POST['login_source']) ? $_POST['login_source'] : 'member';
    $remember = isset($_POST['remember']); // 获取勾选框状态

    $_SESSION['keep_username'] = $username;
    $redirect_back = ($login_source === 'admin') ? "login_admin.php" : "login_member.php";

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $user_id = $user['user_id'];
            $user_role = trim($user['role']);
            $user_status = trim($user['status']);

            // --- 1. 身份交叉检查 ---
            if ($login_source === 'admin' && !in_array($user_role, ['Admin', 'S.Admin'])) {
                $_SESSION['error'] = "Access Denied: Admin credentials required.";
                header("Location: $redirect_back"); exit();
            }
            if ($login_source === 'member' && $user_role !== 'Member') {
                $_SESSION['error'] = "Unauthorized: Please use the Staff Portal.";
                header("Location: $redirect_back"); exit();
            }

            // --- 2. 30秒锁定检查 ---
            if ($user_status === 'Blocked' && !empty($user['last_login_attempt'])) {
                $time_diff = time() - strtotime($user['last_login_attempt']);
                $time_left = 30 - $time_diff;
                if ($time_left > 0) {
                    $_SESSION['lock_seconds'] = $time_left;
                    $_SESSION['error'] = "Account locked. Try again in <span id='timer'>$time_left</span>s.";
                    header("Location: $redirect_back"); exit();
                } else {
                    $pdo->prepare("UPDATE users SET status = 'Active', login_attempts = 0 WHERE user_id = ?")->execute([$user_id]);
                    $user_status = 'Active';
                }
            }

            // --- 3. 密码验证 ---
            if (password_verify($password, $user['password_hashed'])) {
                unset($_SESSION['keep_username']);
                
                // 🔥 Remember Me 逻辑：生成 Token 并写入数据库和 Cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32)); 
                    $upd = $pdo->prepare("UPDATE users SET remember_token = ?, last_login = NOW(), login_attempts = 0 WHERE user_id = ?");
                    $upd->execute([$token, $user_id]);
                    setcookie('remember_pxx', $token, time() + (86400 * 30), "/"); // 有效期 30 天
                } else {
                    $pdo->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE user_id = ?")->execute([$user_id]);
                }

                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user_role;
                $_SESSION['profile_image'] = $user['profile_photo'];

                // 分流跳转
                header("Location: " . (in_array($user_role, ['Admin', 'S.Admin']) ? "admin_portal.php" : "index.php"));
                exit();
            } else {
                // 密码错误计数
                $new_count = (int)$user['login_attempts'] + 1;
                if ($new_count >= 3) {
                    $pdo->prepare("UPDATE users SET login_attempts = ?, status = 'Blocked', last_login_attempt = NOW() WHERE user_id = ?")
                        ->execute([$new_count, $user_id]);
                    $_SESSION['lock_seconds'] = 30;
                    $_SESSION['error'] = "Too many failures. Locked for <span id='timer'>30</span>s.";
                } else {
                    $pdo->prepare("UPDATE users SET login_attempts = ?, last_login_attempt = NOW() WHERE user_id = ?")
                        ->execute([$new_count, $user_id]);
                    $_SESSION['error'] = "Wrong password. " . (3 - $new_count) . " attempts left.";
                }
                header("Location: $redirect_back"); exit();
            }
        } else {
            $_SESSION['error'] = "Account not found.";
            header("Location: $redirect_back"); exit();
        }
    } catch (PDOException $e) { die("DB Error"); }
}