<?php include 'includes/_head.php'; ?>

<style>
    .login-wrapper { display: flex; justify-content: center; align-items: center; min-height: 80vh; background-color: #f8f9fa; }
    .login-card { background: white; width: 100%; max-width: 400px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #e60012; }
    .login-card h2 { margin-bottom: 30px; color: #333; }
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-group label { display: block; font-size: 14px; color: #666; margin-bottom: 8px; }
    .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; }
    .form-group input:focus { border-color: #e60012; }
    .login-btn { width: 100%; padding: 14px; background-color: #e60012; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; }
    .login-btn:hover { background-color: #cc0010; }
    .login-footer { margin-top: 25px; display: flex; justify-content: flex-end; font-size: 14px; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
    .login-footer a { color: #e60012; font-weight: 600; text-decoration: none; }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <h2>Admin Portal</h2>
        <form action="login_process.php" method="POST">
            <input type="hidden" name="role" value="admin">
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="username" placeholder="Admin ID" required>
            </div>
            <div class="form-group">
                <label>Access Key</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="login-btn">Admin Login</button>
        </form>
        <div class="login-footer">
            <a href="footer/help.php">Need Help?</a>
        </div>
    </div>
</div>

<?php include 'includes/_foot.php'; ?>