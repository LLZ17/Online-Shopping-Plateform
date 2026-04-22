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
        $error_msg = "Error: The new email address cannot be the same as the current email address!";
    } 
    // B. 检查域名限制
    else {
        $allowed_domains = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'icloud.com'];
        $domain = substr(strrchr($new_email, "@"), 1);
        
        $is_edu = (substr($domain, -4) === '.edu' || str_contains($domain, '.edu.')); 
        
        if (!in_array($domain, $allowed_domains) && !$is_edu) {
            $error_msg = "Error: This email domain is not currently supported. Please use Gmail, Outlook, or your school's (.edu) email address!";
        }
    }

    // C. 执行数据库更新
    if (empty($error_msg)) {
        try {
            $update_sql = "UPDATE `users` SET `email` = ?, `email_verified` = 0, `email_status` = 'Unverified' WHERE `user_id` = ? ;";
            $email_update = $pdo->prepare($update_sql);
            $success = $email_update->execute([$new_email, $_SESSION['user_id']]);

            if ($success) {
    // 仅仅负责跳转，带上 status=updated 标记
    header("Location: profile.php?page=security&status=updated");
    exit();
}
        } catch (PDOException $e) {
            $error_msg = "Update failed: " . $e->getMessage();
        }
    }
}

// 5. 逻辑处理完后，开始引入头部 HTML
require_once __DIR__ . '/../includes/_head.php'; 
?>

    <link rel="stylesheet" href="../css/app.css">


<div class="edit-email-wrapper">
    <div class="email-card">
        <h1>📧 Changing Email</h1>

        <?php if (!empty($error_msg)): ?>
            <div class="error-alert"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="edit_email.php" method="POST">
            <p style="color: #666; font-size: 14px;">Enter your new email address:</p>
            
            <input type="email" name="update_email" class="email-input" 
                   placeholder="Example: user@gmail.com" 
                   value="<?php echo isset($_POST['update_email']) ? htmlspecialchars($_POST['update_email']) : ''; ?>"
                   autofocus required>

            <button type="submit" class="btn-submit">Update Email</button>
        </form>

        <a href="profile.php?page=security" class="btn-cancel">Cancel</a>
    </div>
</div>

<script src="../js/app.js"></script>
<?php require_once '../includes/_foot.php'; ?>