<?php
session_start();

// 1. 引入数据库连接
require_once 'includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 接收表单输入的用户名 and 密码
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // 2. 去数据库找这个用户
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // 🔥 关键修正点：你的数据库字段名叫 password_hashed，所以这里必须用这个名字 🔥
            if (password_verify($password, $user['password_hashed'])) {
                
                // 密码匹配成功，存入 Session
                // 🔥 修正：根据你的报错，这里必须用 user_id
                $_SESSION['user_id'] = $user['user_id']; 
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // ⭐ 这是为你新加的头像逻辑：读取数据库里的图片文件名 ⭐
                // 配合你之前的字段，这里使用 profile_photo
                $_SESSION['profile_image'] = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';

                // --- 🔥 【新增：写入 SQL 记录与 Cookie】 ---
                $token = bin2hex(random_bytes(32)); // 生成随机令牌
                
                // 更新 SQL：记录最后登录时间(last_login) 和 记住我令牌(remember_token)
                // 🔥 修正：WHERE 条件必须改为 user_id
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW(), remember_token = ? WHERE user_id = ?");
                $updateStmt->execute([$token, $user['user_id']]);

                // 写入 Cookie：保存 30 天，这样关机了下次也能进去
                setcookie('remember_me', $token, time() + (86400 * 30), "/");
                // --- 【新增结束】 ---

                // 3. 根据身份跳转
                echo "<script>
                    alert('Login Successful! Welcome " . $user['username'] . "'); 
                    window.location.href='index.php'; 
                </script>";
                exit();
            } else {
                // 密码不匹配
                echo "<script>alert('Wrong Password! 密码错误'); window.history.back();</script>";
            }
        } else {
            // 用户名不存在
            echo "<script>alert('User not found! 用户名不存在'); window.history.back();</script>";
        }
    } catch (PDOException $e) {
        // 如果数据库报错，直接显示出来
        die("Database Error: " . $e->getMessage());
    }
}
?>