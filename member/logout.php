<?php
session_start();

/**
 * PXX Mall - Member Logout Process
 * 📂 位置: member/logout.php
 * 🎯 目标: 销毁所有登录凭证并跳回根目录的 index.php
 */

// 1. 跳出一级找到根目录下的 includes/db.php
require_once '../includes/db.php';

// 2. 清除数据库中的 Remember Me Token (安全性保障)
if (isset($_COOKIE['remember_me'])) {
    if (isset($_SESSION['user_id']) && isset($pdo)) {
        try {
            // 确保 SQL 字段名 user_id 与你的数据库一致
            $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (PDOException $e) {
            // 发生错误时静默处理
        }
    }
    // 让浏览器端 Cookie 失效，路径 '/' 表示全站生效
    setcookie('remember_me', '', time() - 3600, '/');
}

// 3. 彻底清空内存中的 Session 数据
$_SESSION = array();

// 4. 销毁浏览器端的 Session ID Cookie 并结束 Session 进程
if (session_id() != "" || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();

// 5. 🌟 关键点：跳转回根目录的 index.php
// 因为 logout.php 在 member 文件夹里，所以 ../index.php 是正确的
header("Location: ../index.php"); 
exit();
?>