<?php
// includes/db.php

$host = '127.0.0.1';     // 建议用 127.0.0.1，比 localhost 更稳定
$dbname = 'html_sql';    // 确保你的 phpMyAdmin 里数据库名字真的带空格
$username = 'root';      
$password = '';          
$port = 3306;            // <--- 新加这一行，匹配你的 MySQL 端口

try {
    // 建立 PDO 连接，注意这里增加了 ;port=$port
    $dsn = "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $username, $password);
    
    // 设置 PDO 错误模式
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 设置提取数据格式
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // 测试连接（调试用，正式运行可注释掉）
    // echo "数据库连接成功！当前端口: " . $port;
    
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}
?>