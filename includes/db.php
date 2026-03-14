<?php
// includes/db.php

$host = 'localhost';     // 数据库服务器地址，XAMPP 本地测试固定是 localhost
$dbname = 'html sql'; // 刚才在 phpMyAdmin 里建的数据库名字
$username = 'root';      // XAMPP 的 MySQL 默认用户名是 root
$password = '';          // XAMPP 的 MySQL 默认密码是空的，留白即可

try {
    // 建立 PDO 连接
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // 设置 PDO 错误模式为异常 (Exception)，这样出错时会报错，方便 debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 设置提取数据时的默认格式为关联数组 (Associative Array)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // 如果你想测试是否连接成功，可以取消下面这行的注释
    // echo "数据库连接成功！";
    
} catch (PDOException $e) {
    // 如果连接失败，页面会停止运行并显示错误信息
    die("数据库连接失败: " . $e->getMessage());
}
?>