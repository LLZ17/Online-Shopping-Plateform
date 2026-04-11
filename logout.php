<?php
session_start();
require_once 'includes/db.php'; 

// 1. 🔥 【新增】清除数据库里的 Remember Token (让自动登录失效)
if (isset($_SESSION['user_id'])) {
    // 修正：将 WHERE id 改为 WHERE user_id
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// 2. 🔥 【新增】清除浏览器里的 Cookie
if (isset($_COOKIE['remember_me'])) {
    // 设置过期时间为过去的一个小时，浏览器会自动删除它
    setcookie('remember_me', '', time() - 3600, '/');
}

// 3. 原有的 Session 清除逻辑
$_SESSION = array(); // 彻底清空数组
session_unset();     // 清空变量
session_destroy();   // 销毁 Session

// 4. 跳回首页
header("Location: index.php");
exit();
?>