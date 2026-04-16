<?php
/**
 * =================================================================================
 * PXX MALL - ADMINISTRATIVE AUTHENTICATION PROCESS
 * =================================================================================
 * 功能：处理管理员登录、权限校验、锁定检查及头像数据同步
 * =================================================================================
 */
session_start();

// 1. 强制设定大马时区
date_default_timezone_set('Asia/Kuala_Lumpur'); 

// 2. 引入数据库连接 (向上跳一级回到根目录)
require_once '../includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']); 

    // 登录失败时回显用户名
    $_SESSION['keep_username'] = $username;

    try {
        // 查找管理员信息
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
                header("Location: login.php"); 
                exit();
            }

            // 检查账户是否被锁定 (30秒逻辑)
            if ($user_status === 'Blocked' && !empty($user['last_login_attempt'])) {
                $time_diff = time() - strtotime($user['last_login_attempt']);
                if ($time_diff < 30) {
                    $_SESSION['error'] = "Account temporarily locked. Try again in " . (30 - $time_diff) . "s.";
                    header("Location: login.php"); 
                    exit();
                }
            }

            // 验证密码
            if (password_verify($password, $user['password_hashed'])) {
                // 登录成功，清除回显 Session
                unset($_SESSION['keep_username']);
                
                // 🌟 核心修复：提取头像文件名并对齐 Header 的 Key
                // 检查 profile_photo 字段，如果为空则给默认图
                $user_avatar = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';

                // 🔥 Remember Me: 写入 token 进数据库和 Cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32)); 
                    $pdo->prepare("UPDATE users SET remember_token = ?, last_login = NOW(), login_attempts = 0 WHERE user_id = ?")
                        ->execute([$token, $user_id]);
                    
                    // 设定 30 天有效期，名称统一为 remember_me 方便 Header 识别
                    setcookie('remember_me', $token, time() + (86400 * 30), "/"); 
                } else {
                    // 普通登录只更新登录时间和重置尝试次数
                    $pdo->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE user_id = ?")
                        ->execute([$user_id]);
                }

                // 🌟 存入 Session 凭证 (必须与 Header 逻辑 100% 对齐)
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user_role;
                $_SESSION['profile_photo'] = $user_avatar; // 👈 Header 会读取这一行

                // 跳转至管理员控制台
                header("Location: portal.php");
                exit();

            } else {
                // 密码错误计数逻辑
                $new_count = (int)$user['login_attempts'] + 1;
                if ($new_count >= 3) {
                    $sql = "UPDATE users SET login_attempts = ?, status = 'Blocked', last_login_attempt = NOW() WHERE user_id = ?";
                } else {
                    $sql = "UPDATE users SET login_attempts = ?, last_login_attempt = NOW() WHERE user_id = ?";
                }
                $pdo->prepare($sql)->execute([$new_count, $user_id]);
                
                $_SESSION['error'] = "Invalid Security Key. (Attempt $new_count/3)";
                header("Location: login.php"); 
                exit();
            }
        } else {
            $_SESSION['error'] = "Administrator not found.";
            header("Location: login.php"); 
            exit();
        }
    } catch (PDOException $e) { 
        // 生产环境下建议记录日志，这里演示直接报错
        die("Security System Error: " . $e->getMessage()); 
    }
} else {
    // 禁止非法访问此处理页
    header("Location: login.php");
    exit();
}
?>