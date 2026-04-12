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

require_once __DIR__ . '../includes/db.php'; 
include_once __DIR__ . '../includes/_head.php';

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
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
    if ($active_page == 'addresses' && (!empty($addr1) || !empty($addr2) || !empty($addr3))) {
        $full_address = $addr1;
        if (!empty($addr2)) $full_address .= " | " . $addr2;
        if (!empty($addr3)) $full_address .= " | " . $addr3;
    } else {
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
        
        if (move_uploaded_file($temp_name, "images/" . $new_filename)) {
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
</style>

<div class="pxx-master-wrapper">
    
    <div class="pxx-sidebar-nav">
        <div class="sidebar-user-header">
            <img src="images/<?php echo d($u['profile_photo'] ?: 'default.jpg'); ?>">
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
            
            <a href="?page=addresses" class="nav-link-item <?php echo ($active_page=='addresses')?'active':''; ?>">My Addresses</a>
            
            <a href="?page=bank" class="nav-link-item <?php echo ($active_page=='bank')?'active':''; ?>">Bank Accounts / Cards</a>
            
            <a href="?page=security" class="nav-link-item <?php echo ($active_page=='security')?'active':''; ?>">Account Security</a>
            
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
                            <img src="images/<?php echo d($u['profile_photo'] ?: 'default.jpg'); ?>" id="displayAvatarImg">
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
            
            <form action="profile.php?page=addresses" method="POST">
                
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 1</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line1" class="pxx-input-large" placeholder="Unit / House Number / Floor No.">
                    </div>
                </div>
                
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 2</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line2" class="pxx-input-large" placeholder="Building Name / Street Name">
                    </div>
                </div>
                
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 3</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line3" class="pxx-input-large" placeholder="Area / District / Section">
                    </div>
                </div>

                <div style="margin-left: 205px; color: #aaa; font-size: 13px; margin-bottom: 30px;">
                    <i class="fas fa-info-circle"></i> Current System Address: <?php echo d($u['address']); ?>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Postcode</div>
                    <div class="form-content-pxx">
                        <input type="text" name="postcode" class="pxx-input-large" value="<?php echo d($u['Postcode']); ?>" placeholder="5-digit Postal Code">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">City</div>
                    <div class="form-content-pxx">
                        <input type="text" name="city" class="pxx-input-large" value="<?php echo d($u['City']); ?>" placeholder="Enter City">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">State</div>
                    <div class="form-content-pxx">
                        <input type="text" name="state" class="pxx-input-large" value="<?php echo d($u['State']); ?>" placeholder="Selangor / Wilayah Persekutuan / etc.">
                    </div>
                </div>

                <input type="hidden" name="username" value="<?php echo d($u['username']); ?>">
                <input type="hidden" name="real_name" value="<?php echo d($u['real_name']); ?>">
                <input type="hidden" name="email" value="<?php echo d($u['email']); ?>">
                <input type="hidden" name="phone_num" value="<?php echo d($u['phone_num']); ?>">
                <input type="hidden" name="gender" value="<?php echo d($u['gender']); ?>">
                <input type="hidden" name="birthday" value="<?php echo d($u['birthday']); ?>">

                <div class="form-row-pxx" style="margin-top: 40px;">
                    <div class="form-label-pxx"></div>
                    <div class="form-content-pxx">
                        <button type="submit" class="btn-save-master">Save Address Information</button>
                    </div>
                </div>
            </form>

        <?php elseif ($active_page == 'bank'): ?>
            <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Bank Accounts / Cards</h2>
                <button type="button" class="btn-save-master" style="padding: 10px 25px; font-size: 14px;" onclick="window.location.href='add_card.php'">
                    <i class="fas fa-plus"></i> Add New Card
                </button>
            </div>
            
            <div style="border: 2px dashed #eee; padding: 120px 0; text-align: center; color: #bbb; border-radius: 12px; background: #fafafa; margin-top: 20px;">
                <i class="fas fa-university" style="font-size: 70px; color: #f0f0f0; margin-bottom: 25px;"></i>
                <p style="font-size: 16px;">No active cards or bank accounts found.</p>
                <p style="font-size: 14px; margin-top: 10px;">Link a payment method to enjoy faster, secure checkout.</p>
            </div>

        <?php elseif ($active_page == 'security'): ?>
            <div class="dashboard-header">
                <h2>Account Security</h2>
            </div>
            
            <div class="security-data-row">
                <div class="sec-meta-info">
                    <strong>Email Address Address</strong><br>
                    <span style="color: #888; font-size: 14px; line-height: 2;"><?php echo d($u['email']); ?></span>
                    <span class="status-badge <?php echo $email_verified_status ? 'badge-verified' : 'badge-unverified'; ?>">
                        <?php echo $email_verified_status ? 'Verified' : 'Unverified'; ?>
                    </span>
                </div>
                <div class="sec-action-link">
                    <a href="<?php echo $email_verified_status ? 'edit_email.php' : 'email_verify.php'; ?>" class="blue-link-action">
                        <?php echo $email_verified_status ? 'Change Email' : 'Verify Now'; ?>
                    </a>
                </div>
            </div>

            <div class="security-data-row">
                <div class="sec-meta-info">
                    <strong>Primary Mobile Number</strong><br>
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
                    <a href="change_password.php" class="blue-link-action">Update Password</a>
                </div>
            </div>

            <div style="margin-top: 60px; padding: 25px; background: #fffcf5; border: 1px solid #ffecad; border-radius: 8px; color: #856404; font-size: 14px;">
                <i class="fas fa-shield-virus"></i> Your account security score is <strong>High</strong>. Always keep your recovery data updated.
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    /**
     * Handles the visual and logical removal of the profile picture
     */
    function triggerPhotoDelete() {
        if(confirm('Warning: Are you sure you want to delete your current photo and revert to the default avatar?')) {
            // Update preview image instantly
            document.getElementById('displayAvatarImg').src = 'images/default.jpg';
            // Set the hidden flag for PHP
            document.getElementById('avatarDelFlagInput').value = '1';
            // Optionally notify the user
            console.log("Avatar marked for deletion upon form submission.");
        }
    }
</script>

<?php 
// Standard footer inclusion
$footer_path = __DIR__ . '../includes/_foot.php';
if (file_exists($footer_path)) {
    include_once $footer_path; 
}
?>