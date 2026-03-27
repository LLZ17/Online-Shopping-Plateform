<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/db.php'; 
include_once __DIR__ . '/includes/_head.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$is_edit = isset($_GET['edit']);

// --- 1. 保存逻辑 ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username  = $_POST['username'];
    $real_name = $_POST['real_name'];
    $gender    = $_POST['gender'];
    $birthday  = $_POST['birthday'];
    $phone     = $_POST['phone_num'];
    $email     = $_POST['email'];
    
    // 对应你数据库的新独立字段
    $address  = $_POST['address'];   
    $postcode = $_POST['postcode'];
    $city     = $_POST['city'];
    $state    = $_POST['state'];
    
    $st = $pdo->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
    $st->execute([$user_id]);
    $u_old = $st->fetch();
    $photo = $u_old['profile_photo'] ?? 'default.jpg';

    if (!empty($_FILES['profile_photo']['name'])) {
        $photo = "user_" . $user_id . "_" . time() . "." . pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES["profile_photo"]["tmp_name"], "images/" . $photo);
    }

    // 这一步非常关键：SQL 必须包含所有字段
    $sql = "UPDATE users SET 
            username=?, real_name=?, gender=?, birthday=?, 
            phone_num=?, email=?, address=?, 
            Postcode=?, City=?, State=?, 
            profile_photo=? WHERE user_id=?";
            
    $pdo->prepare($sql)->execute([
        $username, $real_name, $gender, $birthday, 
        $phone, $email, $address, 
        $postcode, $city, $state, 
        $photo, $user_id
    ]);
    
    echo "<script>window.location.href='profile.php';</script>";
    exit();
}

// --- 2. 获取数据 ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$u = $stmt->fetch();

function d($v) { return htmlspecialchars($v ?? ''); }
?>

<style>
    .p-container { max-width: 700px; margin: 30px auto; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
    .p-header { text-align: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 25px; margin-bottom: 30px; }
    .p-avatar { width: 160px; height: 160px; border-radius: 50%; object-fit: cover; border: 5px solid #e60012; margin-bottom: 15px; display: block; margin-left: auto; margin-right: auto; }
    .p-username { font-size: 32px; font-weight: bold; color: #111; margin: 10px 0; }
    .p-points { display: inline-block; background: #fff7e6; color: #d4a017; padding: 8px 20px; border-radius: 30px; font-weight: bold; border: 1px solid #ffe58f; font-size: 16px; }

    .f-section { text-align: left; }
    .f-item { margin-bottom: 25px; display: block; width: 100%; }
    .f-label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
    .f-val { font-size: 16px; color: #333; padding: 15px; background: #fff; border-radius: 10px; border: 1px solid #ddd; display: block; min-height: 24px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05); }
    .f-inp { width: 100%; padding: 15px; border: 1px solid #ccc; border-radius: 10px; box-sizing: border-box; font-size: 16px; display: block; background: #fff; }
    
    .addr-box { background:#fcfcfc; padding:20px; border:2px dashed #eee; border-radius:15px; }
    .addr-row { margin-bottom: 15px; }
    .addr-sub { font-size: 13px; color: #888; margin-bottom: 5px; display: block; font-weight: bold; }

    .btn-red { background: #e60012; color: #fff; border: none; padding: 18px; border-radius: 40px; font-weight: bold; width: 100%; cursor: pointer; margin-top: 20px; font-size: 18px; }
    .btn-dark { display: block; background: #222; color: #fff; text-decoration: none; padding: 18px; border-radius: 40px; text-align: center; font-weight: bold; margin-top: 20px; font-size: 18px; }
    .read-only { background: #f9f9f9; color: #888; border-left: 5px solid #ddd; }
</style>

<div class="main-body">
    <div class="p-container">
        <div class="p-header">
            <img src="images/<?php echo d($u['profile_photo'] ?: 'default.jpg'); ?>" class="p-avatar" onerror="this.src='https://via.placeholder.com/160'">
            <h2 class="p-username"><?php echo d($u['username']); ?></h2>
            <div class="p-points">⭐ Total Points: <?php echo d($u['points'] ?? '0'); ?></div>
        </div>

        <form action="profile.php" method="POST" enctype="multipart/form-data" class="f-section">
            
            <div class="f-item">
                <label class="f-label">Login Username</label>
                <?php echo $is_edit ? '<input name="username" class="f-inp" value="'.d($u['username']).'">' : '<div class="f-val">'.d($u['username']).'</div>'; ?>
            </div>

            <div class="f-item">
                <label class="f-label">Full Name</label>
                <?php echo $is_edit ? '<input name="real_name" class="f-inp" value="'.d($u['real_name']).'">' : '<div class="f-val">'.d($u['real_name']).'</div>'; ?>
            </div>

            <div class="f-item">
                <label class="f-label">Gender</label>
                <?php if($is_edit): ?>
                    <select name="gender" class="f-inp">
                        <option value="Male" <?php echo ($u['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($u['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                <?php else: ?>
                    <div class="f-val"><?php echo d($u['gender'] ?: 'Not Specified'); ?></div>
                <?php endif; ?>
            </div>

            <div class="f-item">
                <label class="f-label">Birthday</label>
                <?php echo $is_edit ? '<input type="date" name="birthday" class="f-inp" value="'.d($u['birthday']).'">' : '<div class="f-val">'.d($u['birthday']).'</div>'; ?>
            </div>

            <div class="f-item">
                <label class="f-label">Phone Number</label>
                <?php echo $is_edit ? '<input name="phone_num" class="f-inp" value="'.d($u['phone_num']).'">' : '<div class="f-val">'.d($u['phone_num']).'</div>'; ?>
            </div>

            <div class="f-item">
                <label class="f-label">Email Address</label>
                <?php echo $is_edit ? '<input name="email" type="email" class="f-inp" value="'.d($u['email']).'">' : '<div class="f-val">'.d($u['email']).'</div>'; ?>
            </div>

            <div class="f-item">
                <label class="f-label">Shipping Address</label>
                <?php if($is_edit): ?>
                    <div class="addr-box">
                        <div class="addr-row">
                            <span class="addr-sub">House / Street Address</span>
                            <input name="address" class="f-inp" value="<?php echo d($u['address']); ?>">
                        </div>
                        <div class="addr-row">
                            <span class="addr-sub">Postcode</span>
                            <input name="postcode" class="f-inp" value="<?php echo d($u['Postcode']); ?>">
                        </div>
                        <div class="addr-row">
                            <span class="addr-sub">City</span>
                            <input name="city" class="f-inp" value="<?php echo d($u['City']); ?>">
                        </div>
                        <div class="addr-row">
                            <span class="addr-sub">State</span>
                            <input name="state" class="f-inp" value="<?php echo d($u['State']); ?>">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="f-val" style="line-height:1.8;">
                        <?php 
                            echo d($u['address']) . "<br>";
                            echo d($u['Postcode']) . " " . d($u['City']) . "<br>";
                            echo d($u['State']);
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="f-item">
                <label class="f-label">Account Created At</label>
                <div class="f-val read-only"><?php echo d($u['created_at']); ?></div>
            </div>

            <div class="action-btn">
                <?php if($is_edit): ?>
                    <div class="f-item" style="margin-top:20px;">
                        <label class="f-label">Update Avatar Image</label>
                        <input type="file" name="profile_photo">
                    </div>
                    <button type="submit" class="btn-red">Save Changes Now</button>
                    <a href="profile.php" style="display:block; text-align:center; margin-top:15px; color:#999; text-decoration:none;">Cancel Edit</a>
                <?php else: ?>
                    <a href="profile.php?edit=1" class="btn-dark">Edit My Profile</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php 
$foot = __DIR__ . '/includes/_foot.php';
if (file_exists($foot)) { include_once $foot; }
?>