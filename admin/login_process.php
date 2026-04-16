<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PXX Mall - Admin Login</title>
    <link rel="stylesheet" href="../css/login_style.css">
</head>
<body>

<div class="login-card">
    <h2>Admin Login</h2>
    <p>Please enter your security credentials.</p>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="error-msg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="auth_process.php" method="POST" id="loginForm">
        <div class="f-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= $_SESSION['keep_username'] ?? '' ?>" required>
        </div>
        <div class="f-group">
            <label>Security Key (Password)</label>
            <input type="password" name="password" required>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="font-size: 12px; color: #64748b; cursor: pointer;">
                <input type="checkbox" name="remember"> Keep me signed in
            </label>
        </div>

        <button type="submit" class="btn-login" id="submitBtn">Sign In to Dashboard</button>
    </form>
</div>

<script src="../js/login_script.js"></script>
</body>
</html>