<?php
/**
 * PXX Mall - Ultimate Member Settings Portal (FULL VERSION)
 * Layout: Fixed 1200px Ultra-Wide Dashboard
 * Features: Editable Username, Triple Address Lines, Avatar X Delete, Full Security
 * Version: 4.0 (No Compression / No Cuts)
 */

// --- 1. SESSION & DATABASE CORE ---
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

/**
 * 🔥 核心修复点 1：物理路径对齐
 * 使用 dirname(__DIR__) 确保路径 100% 准确，解决 member../ 报错
 */
$pxx_root = dirname(__DIR__);
$pxx_db_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php';
$pxx_head_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_head.php';
$pxx_foot_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_foot.php';

if (file_exists($pxx_db_file)) {
    require_once $pxx_db_file;
} else {
    die("CRITICAL: Database connector missing at $pxx_db_file");
}

/**
 * 🔥 核心修复点 2：PDO 变量防空保护
 * 确保 $pdo 变量在当前作用域可用，解决 line 100 报错
 */
global $pdo;
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='../login_selection.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$active_page = $_GET['page'] ?? 'my_profile';

// --- 2. THE MASTER SAVE LOGIC (FULL IMPLEMENTATION) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Extracting Base Fields
    $username  = isset($_POST['username']) ? trim($_POST['username']) : ''; 
    $real_name = isset($_POST['real_name']) ? trim($_POST['real_name']) : '';
    $gender    = isset($_POST['gender']) ? $_POST['gender'] : '';
    $birthday  = isset($_POST['birthday']) ? $_POST['birthday'] : '';
    $phone     = isset($_POST['phone_num']) ? trim($_POST['phone_num']) : '';
    $email     = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Address Line 1, 2, 3 Advanced Merger
    $addr1 = isset($_POST['address_line1']) ? trim($_POST['address_line1']) : '';
    $addr2 = isset($_POST['address_line2']) ? trim($_POST['address_line2']) : '';
    $addr3 = isset($_POST['address_line3']) ? trim($_POST['address_line3']) : '';
    
    // Only update address if we are on the Address Page or the user submitted new lines
    // --- 修改后的地址合并逻辑 ---
    $addr1 = isset($_POST['address_line1']) ? trim($_POST['address_line1']) : '';
    $addr2 = isset($_POST['address_line2']) ? trim($_POST['address_line2']) : '';
    $addr3 = isset($_POST['address_line3']) ? trim($_POST['address_line3']) : '';
    
    // 只有在地址页提交且有内容时才更新全址
    if ($active_page == 'addresses' && (!empty($addr1) || !empty($addr2) || !empty($addr3))) {
        $full_address = $addr1;
        // 使用英文逗号 (,) 作为链接符号
        if (!empty($addr2)) $full_address .= ", " . $addr2; 
        if (!empty($addr3)) $full_address .= ", " . $addr3;
    } else {
        // 如果是其他页面提交，则保持原样
        $full_address = $_POST['current_full_address'] ?? '';
    }
    
    $postcode  = isset($_POST['postcode']) ? trim($_POST['postcode']) : '';
    $city      = isset($_POST['city']) ? trim($_POST['city']) : '';
    $state     = isset($_POST['state']) ? trim($_POST['state']) : '';
    
    // Avatar Logic: Handle New Upload or "X" Delete Command
    $st_photo = $pdo->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
    $st_photo->execute([$user_id]);
    $u_old_data = $st_photo->fetch();
    $photo_filename = $u_old_data['profile_photo'] ?? 'default.jpg';

    // Check for "X" button delete flag
    if (isset($_POST['delete_avatar_flag']) && $_POST['delete_avatar_flag'] == '1') {
        $photo_filename = 'default.jpg';
    } 
    // Check for new file upload
    elseif (!empty($_FILES['profile_photo']['name'])) {
        $temp_name = $_FILES["profile_photo"]["tmp_name"];
        $ext = pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION);
        $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
        
        if (move_uploaded_file($temp_name, "../images/" . $new_filename)) {
            $photo_filename = $new_filename;
        }
    }

    // Prepare and Execute the Mega Update SQL
    $update_sql = "UPDATE users SET 
                    username = ?, real_name = ?, gender = ?, birthday = ?, 
                    phone_num = ?, email = ?, address = ?, 
                    Postcode = ?, City = ?, State = ?, 
                    profile_photo = ? WHERE user_id = ?";
            
    $stmt_update = $pdo->prepare($update_sql);
    $stmt_update->execute([
        $username, $real_name, $gender, $birthday, 
        $phone, $email, $full_address, 
        $postcode, $city, $state, 
        $photo_filename, $user_id
    ]);
    
    // Sync session data
    $_SESSION['username'] = $username;
    $_SESSION['profile_image'] = $photo_filename;
    
    echo "<script>alert('Success: Your account settings have been updated!'); window.location.href='profile.php?page=$active_page';</script>";
    exit();
}

// --- 3. REFRESH DATA FOR DISPLAY ---
$stmt_fetch = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt_fetch->execute([$user_id]);
$u = $stmt_fetch->fetch();

// Secondary logic for verification status
$email_verified_status = (isset($u['email_verified']) && $u['email_verified'] == 1) ? true : false;

// Helper function for safe output
function d($val) { 
    return htmlspecialchars($val ?? ''); 
}

if (file_exists($pxx_head_file)) {
    include_once $pxx_head_file;
}
?>

<style>
    /* --- GLOBAL MASTER CSS --- */
    body { 
        background-color: #f5f5f5; 
        color: #333; 
        font-family: 'Helvetica Neue', Arial, sans-serif; 
        margin: 0; 
        padding: 0; 
    }
    
    /* Strong 1200px Width Constraint */
    .pxx-master-wrapper { 
        width: 1200px; 
        margin: 40px auto; 
        display: flex; 
        gap: 40px; 
        box-sizing: border-box; 
    }

    /* --- SIDEBAR NAVIGATION (PERFECTLY ALIGNED) --- */
    .pxx-sidebar-nav { 
        width: 280px; 
        flex-shrink: 0; 
    }
    
    .sidebar-user-header { 
        display: flex; 
        align-items: center; 
        gap: 15px; 
        padding-bottom: 25px; 
        border-bottom: 1px solid #ddd; 
        margin-bottom: 30px; 
    }
    
    .sidebar-user-header img { 
        width: 60px; 
        height: 60px; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 1px solid #eee; 
    }
    
    .sidebar-user-header .user-meta { 
        font-weight: bold; 
        font-size: 15px; 
    }

    /* 🔥 Icon Alignment with 35px spacing */
    .nav-section-header { 
        font-size: 16px; 
        font-weight: 700; 
        color: #333; 
        display: flex; 
        align-items: center; 
        margin-bottom: 20px; 
    }
    
    .nav-section-header i { 
        color: #e60012; 
        width: 35px; 
        font-size: 20px; 
        text-align: left; 
    }

    .nav-link-item { 
        display: block; 
        padding: 12px 0 12px 50px; 
        text-decoration: none; 
        color: #555; 
        font-size: 15px; 
        transition: all 0.2s ease; 
    }
    
    .nav-link-item:hover, .nav-link-item.active { 
        color: #e60012; 
        font-weight: 600; 
    }

    /* 🔥 LOGOUT ALIGNED WITH "MY ACCOUNT" TEXT */
    .logout-nav-container { 
        margin-top: 50px; 
    }
    
    .logout-link-aligned { 
        display: flex; 
        align-items: center; 
        padding: 12px 0; 
        text-decoration: none; 
        color: #bbb; 
        font-size: 15px; 
        transition: color 0.2s; 
    }
    
    .logout-link-aligned:hover { 
        color: #e60012; 
    }
    
    .logout-link-aligned i { 
        width: 35px; 
        text-align: left; 
        font-size: 20px; 
    }
    
    .logout-link-aligned span { 
        padding-left: 15px; /* Matches the gap of upper items */
    }

    /* --- RIGHT CONTENT DASHBOARD --- */
    .pxx-main-dashboard { 
        flex: 1; 
        background: #fff; 
        padding: 50px; 
        border-radius: 4px; 
        box-shadow: 0 1px 6px rgba(0,0,0,0.08); 
        min-height: 800px; 
    }
    
    .dashboard-header { 
        border-bottom: 1px solid #efefef; 
        padding-bottom: 20px; 
        margin-bottom: 45px; 
    }
    
    .dashboard-header h2 { 
        margin: 0; 
        font-size: 24px; 
        font-weight: 500; 
    }

    /* --- ADVANCED FORM LAYOUT --- */
    .form-row-pxx { 
        display: flex; 
        margin-bottom: 35px; 
        align-items: flex-start; 
    }
    
    .form-label-pxx { 
        width: 170px; 
        text-align: right; 
        color: #666; 
        font-size: 15px; 
        margin-right: 35px; 
        padding-top: 12px; 
    }
    
    .form-content-pxx { 
        flex: 1; 
    }
    
    /* 🔥 ULTRA LONG INPUT FOR ADDRESS 1/2/3 */
    .pxx-input-large { 
        width: 100%; 
        padding: 14px 15px; 
        border: 1px solid #ddd; 
        border-radius: 2px; 
        font-size: 15px; 
        box-sizing: border-box; 
        margin-bottom: 15px; 
        transition: border-color 0.2s;
    }
    
    .pxx-input-large:focus { 
        border-color: #e60012; 
        outline: none; 
    }
    
    /* Gender Horizontal Alignment */
    .gender-horizontal-wrap { 
        display: flex; 
        gap: 40px; 
        padding-top: 12px; 
    }
    
    .gender-horizontal-wrap label { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        cursor: pointer; 
        font-size: 15px;
    }

    .points-badge { 
        font-size: 20px; 
        color: #e60012; 
        font-weight: bold; 
    }
    
    .btn-save-master { 
        background: #e60012; 
        color: #fff; 
        border: none; 
        padding: 16px 70px; 
        border-radius: 2px; 
        cursor: pointer; 
        font-size: 16px; 
        font-weight: 600; 
        transition: background 0.3s;
    }
    
    .btn-save-master:hover { 
        background: #d31d1d; 
    }

    /* 🔥 AVATAR WITH DELETE 'X' BUTTON */
    .avatar-interactive-box { 
        position: relative; 
        width: 110px; 
        height: 110px; 
        margin-bottom: 20px; 
    }
    
    .avatar-interactive-box img { 
        width: 100%; 
        height: 100%; 
        border-radius: 50%; 
        object-fit: cover; 
        border: 1px solid #ddd; 
    }
    
    .avatar-remove-btn { 
        position: absolute; 
        top: 0; 
        right: 0; 
        background: #e60012; 
        color: #fff; 
        border: none; 
        width: 26px; 
        height: 26px; 
        border-radius: 50%; 
        cursor: pointer; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: bold; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* SECURITY ITEM LIST */
    .security-data-row { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 30px 0; 
        border-bottom: 1px solid #f9f9f9; 
    }
    
    .status-badge { 
        font-size: 11px; 
        padding: 3px 12px; 
        border-radius: 12px; 
        font-weight: bold; 
        text-transform: uppercase; 
        margin-left: 10px; 
        border: 1px solid transparent;
    }
    
    .badge-unverified { background: #fff1f0; color: #e60012; border-color: #ffa39e; }
    .badge-verified { background: #e6fffb; color: #13c2c2; border-color: #87e8de; }

    .blue-link-action { 
        color: #0055aa; 
        text-decoration: none; 
        font-size: 15px; 
        font-weight: bold; 
    }

    .blue-link-action:hover { 
        text-decoration: underline;
    }
</style>

<div class="pxx-master-wrapper">
    
    <div class="pxx-sidebar-nav">
        <div class="sidebar-user-header">
            <img src="../images/<?php echo d($u['profile_photo'] ?: 'default.jpg'); ?>">
            <div class="user-meta">
                <div class="user-name-text"><?php echo d($u['username']); ?></div>
                <a href="?page=my_profile" style="font-size: 12px; color: #888; text-decoration: none; font-weight: normal; margin-top: 5px; display: block;">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>

        <nav>
            <div class="nav-section-header">
                <i class="fas fa-user-circle"></i> My Account
            </div>
            
            <a href="?page=my_profile" class="nav-link-item <?php echo ($active_page=='my_profile')?'active':''; ?>">My Profile</a>
            
            <a href="?page=addresses" class="nav-link-item <?php echo ($active_page=='addresses')?'active':''; ?>">My Shipping Addresses</a>
            
            <a href="?page=bank" class="nav-link-item <?php echo ($active_page=='bank')?'active':''; ?>">My Bank Accounts / Cards</a>
            
            <a href="?page=security" class="nav-link-item <?php echo ($active_page=='security')?'active':''; ?>">My Account Security</a>
            
            <div class="logout-nav-container">
                <a href="../logout.php" class="logout-link-aligned">
                    <i class="fas fa-power-off"></i>
                    <span>Logout System</span>
                </a>
            </div>
        </nav>
    </div>

    <div class="pxx-main-dashboard">

        <?php if ($active_page == 'my_profile'): ?>
            <div class="dashboard-header">
                <h2>My Profile</h2>
            </div>
            
            <form action="profile.php?page=my_profile" method="POST" enctype="multipart/form-data" id="profileFormMaster">
                
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Profile Picture</div>
                    <div class="form-content-pxx">
                        <div class="avatar-interactive-box">
                            <img src="../images/<?php echo d($u['profile_photo'] ?: 'default.jpg'); ?>" id="displayAvatarImg">
                            <?php if ($u['profile_photo'] != 'default.jpg'): ?>
                                <button type="button" class="avatar-remove-btn" onclick="triggerPhotoDelete()">×</button>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="profile_photo" style="font-size: 13px; color: #777;">
                        <input type="hidden" name="delete_avatar_flag" id="avatarDelFlagInput" value="0">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Username</div>
                    <div class="form-content-pxx">
                        <input type="text" name="username" class="pxx-input-large" value="<?php echo d($u['username']); ?>" placeholder="Set your account username">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Full Name</div>
                    <div class="form-content-pxx">
                        <input type="text" name="real_name" class="pxx-input-large" value="<?php echo d($u['real_name']); ?>" placeholder="Enter your full legal name">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Gender</div>
                    <div class="form-content-pxx">
                        <div class="gender-horizontal-wrap">
                            <label><input type="radio" name="gender" value="Male" <?php echo ($u['gender']=='Male')?'checked':''; ?>> Male</label>
                            <label><input type="radio" name="gender" value="Female" <?php echo ($u['gender']=='Female')?'checked':''; ?>> Female</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Date of Birth</div>
                    <div class="form-content-pxx">
                        <input type="date" name="birthday" class="pxx-input-large" value="<?php echo d($u['birthday']); ?>">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">PXX Loyalty Points</div>
                    <div class="form-content-pxx">
                        <span class="points-badge">⭐ <?php echo d($u['points'] ?? 0); ?> Points</span>
                    </div>
                </div>
                
                <input type="hidden" name="email" value="<?php echo d($u['email']); ?>">
                <input type="hidden" name="phone_num" value="<?php echo d($u['phone_num']); ?>">
                <input type="hidden" name="current_full_address" value="<?php echo d($u['address']); ?>">

                <div class="form-row-pxx" style="margin-top: 50px;">
                    <div class="form-label-pxx"></div>
                    <div class="form-content-pxx">
                        <button type="submit" class="btn-save-master">Update Profile Settings</button>
                    </div>
                </div>
            </form>

        <?php elseif ($active_page == 'addresses'): ?>
            <div class="dashboard-header">
                <h2>My Shipping Addresses</h2>
            </div>

            <div style="margin-left: 0; color: #aaa; font-size: 13px; margin-bottom: 30px; line-height: 1.6;">
                <i class="fas fa-info-circle"></i> Current System Address: 
                <?php 
                    // 1. 使用英文逗号拆分数据库中的地址字段
                    $addr_parts = explode(", ", $u['address'] ?? ''); 
                    
                    // 2. 整合所有地址片段
                    $raw_segments = array_merge($addr_parts, [$u['Postcode'], $u['City'], $u['State']]);
                    $formatted_segments = [];
                    
                    // 3. 遍历并为非空内容加上括号 ()
                    foreach ($raw_segments as $seg) {
                        $trimmed = trim($seg ?? '');
                        if (!empty($trimmed)) {
                            $formatted_segments[] = d($trimmed);
                        }
                    }
                    echo implode(", ", $formatted_segments);
                ?>
            </div>
            
            <form action="profile.php?page=addresses" method="POST">
                
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 1</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line1" class="pxx-input-large" 
                               value="" placeholder="Unit / House Number / Floor No.">
                    </div>
                </div>
                
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 2</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line2" class="pxx-input-large" 
                               value="" placeholder="Building Name / Street Name">
                    </div>
                </div>
                
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 3</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line3" class="pxx-input-large" 
                               value="" placeholder="Area / District / Section">
                    </div>
                </div>

                <div class="fld-box">
                <label>State</label>
                <select name="state" id="m_state" onchange="updateCitySelection()">
                <option value="">Select State</option>
                </select>
</div>
<div class="fld-box">
    <label>City</label>
    <select name="city" id="m_city">
        <option value="">Select City (Select State first)</option>
        </select>
</div>
<div class="fld-box">
    <label>Postcode</label>
    <input type="text" name="postcode" id="m_postcode">
</div>

                <input type="hidden" name="username" value="<?php echo d($u['username']); ?>">
                <input type="hidden" name="real_name" value="<?php echo d($u['real_name']); ?>">
                <input type="hidden" name="email" value="<?php echo d($u['email']); ?>">
                <input type="hidden" name="phone_num" value="<?php echo d($u['phone_num']); ?>">
                <input type="hidden" name="gender" value="<?php echo d($u['gender']); ?>">
                <input type="hidden" name="birthday" value="<?php echo d($u['birthday']); ?>">
                <input type="hidden" name="current_full_address" value="<?php echo d($u['address']); ?>">

                <div class="form-row-pxx" style="margin-top: 40px;">
                    <div class="form-label-pxx"></div>
                    <div class="form-content-pxx">
                        <button type="submit" class="btn-save-master">Save Address Information</button>
                    </div>
                </div>
            </form>

        <?php elseif ($active_page == 'security'): ?>
            <div class="dashboard-header">
                <h2>Account Security</h2>
            </div>
            
            <div class="security-data-row">
                <div class="sec-meta-info">
                    <strong>Email Address</strong><br>
                    <span style="color: #888; font-size: 14px; line-height: 2;"><?php echo d($u['email']); ?></span>
                    <span class="status-badge <?php echo $email_verified_status ? 'badge-verified' : 'badge-unverified'; ?>">
                        <?php echo $email_verified_status ? 'Verified' : 'Unverified'; ?>
                    </span>
                </div>
                <div class="sec-action-link">
                    <a href = "edit_email.php" class = "blue-link-action">Change Email</a>
                    <?php echo $email_verified_status ? '' : ' | '; ?>
                    <a href = "email_verify.php" class="blue-link-action">
                        <?php echo $email_verified_status ? '' : 'Verify Now', ''; ?></a>
                </div>
            </div>

            <div class="security-data-row">
                <div class="sec-meta-info">
                    <strong>Phone Number</strong><br>
                    <span style="color: #888; font-size: 14px; line-height: 2;"><?php echo d($u['phone_num']); ?></span>
                    <span class="status-badge badge-verified">Verified</span>
                </div>
                <div class="sec-action-link">
                    <a href="edit_phone.php" class="blue-link-action">Change Number</a>
                </div>
            </div>

            <div class="security-data-row" style="border: none;">
                <div class="sec-meta-info">
                    <strong>Login Password</strong><br>
                    <span style="color: #888; font-size: 14px; line-height: 2;">******** (Recommended: Change every 90 days)</span>
                </div>
                <div class="sec-action-link">
                    <a href="reset_password.php" class="blue-link-action">Update Password</a>
                </div>
            </div>

            <div style="margin-top: 60px; padding: 25px; background: #fffcf5; border: 1px solid #ffecad; border-radius: 8px; color: #856404; font-size: 14px;">
                <i class="fas fa-shield-virus"></i> Your account security score is <strong>High</strong>. Always keep your recovery data updated.
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    function triggerPhotoDelete() {
        if(confirm('Warning: Are you sure you want to delete your current photo and revert to the default avatar?')) {
            document.getElementById('displayAvatarImg').src = '../images/default.jpg';
            document.getElementById('avatarDelFlagInput').value = '1';
        }
    }
</script>

<?php 
if (file_exists($pxx_foot_file)) {
    include_once $pxx_foot_file; 
}
?>