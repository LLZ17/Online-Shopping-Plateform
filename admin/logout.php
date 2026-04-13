<?php
/**
 * =================================================================================
 * PXX MALL - ADMINISTRATIVE LOGOUT SYSTEM
 * =================================================================================
 * 位置：admin/logout.php
 * 功能：销毁管理员会话、清除 Remember Me Token，并安全重定向
 * =================================================================================
 */
session_start();

// 1. 强制设定大马时区（保持系统一致性）
date_default_timezone_set('Asia/Kuala_Lumpur');

// 2. 引入数据库（用于清除数据库里的 Token）
// 物理路径：admin 文件夹的上一层是根目录，所以用 ../
require_once '../includes/db.php'; 

try {
    // 🔥 3. 如果用户处于登录状态，先清除数据库中的 remember_token
    if (isset($_SESSION['user_id']) && isset($pdo)) {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
} catch (PDOException $e) {
    // 静默处理错误，不阻断退出流程
}

// 4. 清除浏览器中的 Remember Me Cookie
if (isset($_COOKIE['remember_me'])) {
    // 设置过期时间为 1 小时前，通知浏览器立即删除
    setcookie('remember_me', '', time() - 3600, '/');
}

// 5. 彻底清除 Session 数据
$_SESSION = array(); // 清空内存中的变量

// 6. 销毁服务器上的 Session 文件
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 7. 🌟 核心跳转：跳回管理员登录页
// 因为此文件在 admin 文件夹内，所以直接跳转到 login.php 即可
header("Location: ../index.php");
exit();
?>