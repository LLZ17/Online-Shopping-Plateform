<?php
// ================================
// PXX MALL - RESET PASSWORD (FINAL)
// ================================

// --- SESSION ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kuala_Lumpur');

// --- DB ---
require_once '../includes/db.php';

// --- CHECK LOGIN ---
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

// --- MESSAGE ---
$error_msg = "";
$success_msg = "";

// --- HANDLE POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // ✅ 1. 长度检查
    if (strlen($new_pass) < 6) {
        $error_msg = "Password must be at least 6 characters.";
    }

    // ✅ 2. 一致性检查
    elseif ($new_pass !== $confirm_pass) {
        $error_msg = "Passwords do not match.";
    }

    // ✅ 3. 强密码（可选但推荐）
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*[0-9]).{6,}$/', $new_pass)) {
        $error_msg = "Password must contain at least 1 uppercase and 1 number.";
    }

    else {
        try {
            // ✅ 4. 获取旧密码
            $stmt = $pdo->prepare("
                SELECT password_hashed 
                FROM users 
                WHERE user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user) {

                $old_hash = $user['password_hashed'];

                // ❗ 新旧不能一样
                if (password_verify($new_pass, $old_hash)) {
                    $error_msg = "New password cannot be same as old password.";
                } else {

                    // ✅ 5. HASH 新密码
                    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);

                    // ✅ 6. UPDATE
                    $update = $pdo->prepare("
                        UPDATE users 
                        SET password_hashed = ?
                        WHERE user_id = ?
                    ");

                    $update->execute([$new_hash, $user_id]);

                    if ($update->rowCount() > 0) {

                        $success_msg = "✅ Password updated successfully!";

                        // 🔒 强制重新登录（推荐）
                        session_destroy();
                        header("refresh:2;url=login.php");

                    } else {
                        $error_msg = "Update failed. Try again.";
                    }
                }

            } else {
                $error_msg = "User not found.";
            }

        } catch (PDOException $e) {
            $error_msg = "Database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 350px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }

        button {
            width: 100%;
            padding: 12px;
            background: green;
            color: white;
            border: none;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>

<body>

<div class="box">
    <h2>Reset Password</h2>

    <?php if ($error_msg): ?>
        <p class="error"><?php echo $error_msg; ?></p>
    <?php endif; ?>

    <?php if ($success_msg): ?>
        <p class="success"><?php echo $success_msg; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button type="submit">Update Password</button>
    </form>

</div>

</body>
</html>