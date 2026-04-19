<?php
// TEE DE YONG's Part: Member Edit Profile (Premium UI Edition)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 💡 1. 获取要编辑的会员 ID
$id = $_GET['id'] ?? '';
if (!$id) {
    die("<script>alert('⚠️ No member ID provided!'); window.location.href='member_crud.php';</script>");
}

// 💡 2. 获取当前会员资料
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'Member'"); 
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    die("<div style='padding:50px; text-align:center;'><h2>⚠️ Member not found!</h2><a href='member_crud.php'>Go Back</a></div>");
}

// 💡 3. 处理表单提交更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $real_name = $_POST['real_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_num = $_POST['phone_num'] ?? '';
    $address = $_POST['address'] ?? '';
    $postcode = $_POST['postcode'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    
    $final_photo_path = $member['profile_photo'] ?? ''; 

    try {
        $pdo->beginTransaction();

        // 处理头像上传
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/profiles/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            $tmp_name = $_FILES['photo']['tmp_name'];
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $id . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $final_photo_path = 'uploads/profiles/' . $new_filename;
            }
        }

        // 更新数据库
        $updateStmt = $pdo->prepare("UPDATE users SET real_name = ?, email = ?, phone_num = ?, address = ?, Postcode = ?, City = ?, State = ?, profile_photo = ? WHERE user_id = ?");
        $updateStmt->execute([$real_name, $email, $phone_num, $address, $postcode, $city, $state, $final_photo_path, $id]);

        $pdo->commit();
        echo "<script>alert('✨ Member profile updated successfully!'); window.location.href='member_crud.php';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='padding:20px; color:red;'>Update Error: " . $e->getMessage() . "</div>");
    }
}

// 处理当前显示的头像路径
$currentPhoto = !empty($member['profile_photo']) ? $member['profile_photo'] : 'default.jpg';
$photoDisplayPath = (strpos($currentPhoto, 'http') === 0 || strpos($currentPhoto, '/') === 0) ? $currentPhoto : '../images/' . $currentPhoto;

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.13">

<div id="edit-wrapper" class="scroll-wrapper" style="display:none;">
    <div class="premium-form-card">
        <div class="form-header">
            <h2>Edit Member Profile</h2>
            <p>UPDATING DATA FOR MEMBER ID #<?= htmlspecialchars($id) ?></p>
        </div>
        <div class="form-body">
            <form method="POST" action="" enctype="multipart/form-data">
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Profile Picture</label>
                    <div class="file-upload-box" onclick="document.getElementById('photo-input').click();">
                        <img id="image-preview" class="current-photo" src="<?= htmlspecialchars($photoDisplayPath) ?>" onerror="this.src='../images/default.jpg'">
                        <div class="upload-text">
                            <strong>Change Profile Photo</strong>
                            <span>Click here to upload JPG, PNG, or WEBP</span>
                        </div>
                        <input type="file" name="photo" id="photo-input" accept="image/*" style="display: none;">
                    </div>
                </div>

                <div class="grid-row">
                    <div class="form-group">
                        <label>Username (System ID)</label>
                        <input type="text" class="input-style input-readonly" value="@<?= htmlspecialchars($member['username']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Real Name</label>
                        <input type="text" name="real_name" class="input-style" value="<?= htmlspecialchars($member['real_name'] !== 'N/A' ? $member['real_name'] : '') ?>" placeholder="e.g. Amirul bin Yusuf" required>
                    </div>
                </div>

                <div class="grid-row">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="input-style" value="<?= htmlspecialchars($member['email'] !== 'N/A' ? $member['email'] : '') ?>" placeholder="e.g. member@email.com" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone_num" class="input-style" value="<?= htmlspecialchars($member['phone_num'] !== 'N/A' ? $member['phone_num'] : '') ?>" placeholder="e.g. 0123456789">
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed #e2e8f0; margin: 25px 0;">

                <div class="form-group">
                    <label>Street Address</label>
                    <textarea name="address" class="input-style" rows="2" placeholder="e.g. No. 12, Jalan Ampang Utama..."><?= htmlspecialchars($member['address'] !== 'N/A' ? $member['address'] : '') ?></textarea>
                </div>

                <div class="grid-row-3">
                    <div class="form-group">
                        <label>Postcode</label>
                        <input type="text" name="postcode" class="input-style" value="<?= htmlspecialchars($member['Postcode'] !== 'N/A' ? $member['Postcode'] : '') ?>" placeholder="e.g. 50450">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="input-style" value="<?= htmlspecialchars($member['City'] !== 'N/A' ? $member['City'] : '') ?>" placeholder="e.g. Kuala Lumpur">
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" class="input-style" value="<?= htmlspecialchars($member['State'] !== 'N/A' ? $member['State'] : '') ?>" placeholder="e.g. Wilayah Persekutuan">
                    </div>
                </div>

                <div class="btn-row">
                    <a href="member_crud.php" class="btn-back">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        CANCEL
                    </a>
                    <button type="submit" class="btn-submit">CONFIRM UPDATE</button>
                </div>
                
            </form>
        </div>
    </div>
</div>

<script src="../js/app.js?v=1.13"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
