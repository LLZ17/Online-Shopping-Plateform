<?php include 'includes/_head.php'; ?>

<style>
    .login-wrapper { display: flex; justify-content: center; align-items: center; min-height: 80vh; background-color: #f8f9fa; }
    .login-card { background: white; width: 100%; max-width: 400px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #28a745; }
    .login-card h2 { margin-bottom: 30px; color: #333; }
    .form-group { margin-bottom: 20px; text-align: left; }
    .form-group label { display: block; font-size: 14px; color: #666; margin-bottom: 8px; }
    .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; }
    .form-group input:focus { border-color: #28a745; }
    .login-btn { width: 100%; padding: 14px; background-color: #28a745; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.3s; }
    .login-btn:hover { background-color: #218838; }
    .login-footer { margin-top: 25px; display: flex; justify-content: space-between; font-size: 14px; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
    .login-footer a { color: #28a745; font-weight: 600; text-decoration: none; }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <h2>Member Login</h2>
        <form action="login_process.php" method="POST">
            <input type="hidden" name="role" value="member">
            <div class="form-group">
                <label>Email / Username</label>
                <input type="text" name="username" placeholder="Member ID" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="login-btn">Login as Member</button>
        </form>
        <div class="login-footer">
            <span>New Member? <a href="signup.php">Sign up</a></span>
            <a href="footer/help.php">Need Help?</a>
        </div>
    </div>
</div>

<?php include 'includes/_foot.php'; ?>