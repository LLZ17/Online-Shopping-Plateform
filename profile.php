<?php
include 'header.php'; // 引入你刚才改好的 header
require_once 'includes/db.php';

// 检查是否登录，没登录直接踢回登录页
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// --- 🔥 处理表单提交 (更新资料) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    
    // 获取当前头像作为默认值
    $stmt_old = $pdo->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
    $stmt_old->execute([$user_id]);
    $old_user = $stmt_old->fetch();
    $photo_name = $old_user['profile_photo']; 

    // 处理头像上传
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "image/"; // 你的文件夹叫 image
        $photo_name = time() . "_" . basename($_FILES["profile_photo"]["name"]);
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_dir . $photo_name)) {
            $_SESSION['profile_image'] = $photo_name; // 上传成功才更新 Session
        }
    }

    try {
        $updateSql = "UPDATE users SET email = ?, username = ?, profile_photo = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([$email, $username, $photo_name, $user_id]);
        
        // 🔥 同步更新 Session，这样 Header 里的名字会立刻改变
        $_SESSION['username'] = $username;
        $_SESSION['profile_image'] = !empty($photo_name) ? $photo_name : 'default.jpg';
        
        $message = "Profile updated successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// --- 🔥 获取用户最新数据 ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<style>
    .profile-container { max-width: 600px; margin: 50px auto; padding: 30px; border: 1px solid #ddd; border-radius: 8px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .profile-header { text-align: center; margin-bottom: 25px; }
    .current-avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #e60012; margin-bottom: 10px; }
    .form-group { margin-bottom: 15px; text-align: left; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
    .form-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
    .save-btn { background: #e60012; color: white; border: none; padding: 12px 20px; width: 100%; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 10px; transition: background 0.3s; }
    .save-btn:hover { background: #c5000f; }
    .msg { color: #2e7d32; background: #e8f5e9; padding: 10px; border-radius: 4px; text-align: center; margin-bottom: 15px; font-weight: bold; }
</style>

<div class="main-content">
    <div class="profile-container">
        <div class="profile-header">
            <h2>Account Settings</h2>
            <img src="image/<?php echo !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg'; ?>" class="current-avatar">
            <p style="color: #666;">Manage your personal information</p>
        </div>

        <?php if($message) echo "<p class='msg'>$message</p>"; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required placeholder="Enter new username">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label>Update Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/*">
                <small style="color: #888;">Leave blank to keep current photo</small>
            </div>

            <button type="submit" class="save-btn">Save Changes</button>
        </form>
    </div>
</div>

<?php 
// 引入 footer
if(file_exists('footer.php')) {
    include 'footer.php'; 
}
?>