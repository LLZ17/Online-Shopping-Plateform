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

// --- 获取当前用户的旧 Email (用于查重) ---
$stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();
$old_email = $current_user['email'] ?? '';

// 4. 处理提交逻辑 (POST Processing)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_email'])) {
    $new_email = trim($_POST['update_email']);
    $error_msg = "";

    // A. 检查是否和旧 Email 一样
    if ($new_email === $old_email) {
        $error_msg = "Error:The new email address cannot be the same as the current email address！";
    } 
    // B. 检查域名限制 (允许 common domains 和 .edu)
    else {
        $allowed_domains = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'icloud.com'];
        $domain = substr(strrchr($new_email, "@"), 1);
        
        // 逻辑：如果在允许名单里，或者是以 .edu 结尾的域名，则通过
        $is_edu = (substr($domain, -4) === '.edu' || str_contains($domain, '.edu.')); 
        
        if (!in_array($domain, $allowed_domains) && !$is_edu) {
            $error_msg = "Error:This email domain is not currently supported. Please use Gmail, Outlook, or your school's (.edu) email address!";
        }
    }

    // C. 执行数据库更新
    if (empty($error_msg)) {
        try {
            $update_sql = "UPDATE `users` SET `email` = ?, `email_verified` = 0, `email_status` = 'Unverified' WHERE `user_id` = ? ;";
            $email_update = $pdo->prepare($update_sql);
            $success = $email_update->execute([$new_email, $_SESSION['user_id']]);

            if ($success) {
                header("Location: profile.php?page=security");
                exit();
            }
        } catch (PDOException $e) {
            $error_msg = "更新失败：" . $e->getMessage();
        }
    }
}

// 5. 逻辑处理完后，开始引入头部 HTML
require_once __DIR__ . '/../includes/_head.php'; 
?>

<style>
    /* 1. 页面大框架 */
    body {
        /* 在这里修改你的背景图路径 */
        background-image: url('../images/bg.jpeg'); 
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        margin: 0;
    }

    .cart-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 30px;
        background: rgba(255, 255, 255, 0.95); /* 加了透明度让背景隐约可见 */
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    h1 {
        margin-bottom: 25px;
        padding-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #e60012;
    }

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
        font-weight: 400;
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

    .btn-cancel a {
        color: white;
        text-decoration: none;
    }

    .error-alert {
        color: #e60012;
        background: #ffe6e6;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
</style>

<div class="cart-container">
    <h1>📧 Changing Email Address</h1>

    <?php if (!empty($error_msg)): ?>
        <div class="error-alert"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="edit_email.php" method="POST">
        <label for="email" class="card-flex">Enter Your New Email Address:</label>
        
        <div class="card-flex">
            <input type="email" id="email" name="update_email" class="email-input" 
                   placeholder="Example: user@example.com" 
                   value="<?php echo isset($_POST['update_email']) ? htmlspecialchars($_POST['update_email']) : ''; ?>"
                   autofocus required>
        </div>
        <div>
            <button type="button" class="btn-cancel"><a href="profile.php?page=security">Cancel</a></button>
            <button type="submit" class="btn-submit">Update</button>
        </div>
    </form>
</div>

<?php require_once '../includes/_foot.php'; ?>