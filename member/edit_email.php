<?php 
// 1. 启动会话
session_start();

// 2. 身份检测 (必须在最前面)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// 3. 引入数据库配置
require_once __DIR__ . '/../includes/db.php';

// 4. 处理提交逻辑 (POST Processing)
// 把这个搬到 HTML 之前，这样我们才能在这里使用 header() 跳转
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_email'])) {
    try {
        $update_sql = "UPDATE `users` SET `email` = ?,`email_verified` = 0, `email_status` = 'Unverified' WHERE `user_id` = ? ;";
        $email_update = $pdo->prepare($update_sql);
        $success = $email_update->execute([$_POST['update_email'], $_SESSION['user_id']]);

        if ($success) {
            // 更新成功后重定向，防止刷新页面重复提交
            header("Location: profile.php?page=security");
            exit();
        }
    } catch (PDOException $e) {
        // 如果报错，可以记录日志或定义一个错误变量
        $error_msg = "更新失败：" . $e->getMessage();
    }
}

// 5. 逻辑处理完后，开始引入头部 HTML
require_once __DIR__ . '/../includes/_head.php'; 
?>

<style>
    /* 1. 页面大框架 */
    .cart-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    h1 {
        margin-bottom: 25px;
        padding-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #e60012;
    }

    /* 修正了你之前的宽度和字体属性 */
    .email-input {
        width: 100%;
        max-width: 400px;
        height: 45px;
        padding: 0 15px;
        font-size: 1.2rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding-bottom: 15px;
        padding-top: 15px;
    }

    .email-input::placeholder {
        font-weight: 400; /* 修正：placeholder 通常设为 normal 或 lighter */
    }

    .card-flex {
        padding-bottom: 10px;
        padding-top: 10px;
    }
    
    .btn-submit {
        background: #e60012;
        color: white;
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .btn-cancel {
        background: #9c9494;
        color: white;
        padding: 10px 25px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        padding-bottom: 10px;
        padding-top: 10px;
        opacity: 0.767;
    }
</style>

<div class="cart-container">
    <h1>📧 Changing Email Address</h1>

    <form action="edit_email.php" method="POST">
        <label for="email" class="card-flex">Enter Your new email address:</label>
        
        <div  class="card-flex">
            <input type="email" id="email" name="update_email" class="email-input" 
                   placeholder="Example: user@example.com" autofocus required>
        </div>
        <div>
            <button class="btn-cancel"><a href = "profile.php?page=security" >Cancel</a></button>
            <button type="submit" class="btn-submit">Update</button>
        </div>
    </form>
</div>

<?php require_once '../includes/_foot.php'; ?>