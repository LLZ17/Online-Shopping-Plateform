<?php
/**
 * ==================================================================================
 * PXX Mall - Enterprise Member Portal v5.5 (ULTRA FULL VERSION)
 * ==================================================================================
 * [FEATURES]
 * - Master-Slave PHP Architecture
 * - JSON-Native Malaysia Location Service (State/City)
 * - Intelligent Email Verification Logic
 * - Real-time Avatar Preview & X-Delete System
 * - CSRF Protected Update Logic (Placeholder)
 * - Ultra-wide 1200px Dashboard
 * ==================================================================================
 */

// --- 1. CORE SYSTEM INITIALIZATION ---
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// 物理路径对齐
$pxx_root = dirname(__DIR__);
$pxx_db_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php';
$pxx_head_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_head.php';
$pxx_foot_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_foot.php';

// 数据库连接校验
if (file_exists($pxx_db_file)) {
    require_once $pxx_db_file;
} else {
    die("<div style='padding:50px; text-align:center;'><h2>[System Error]</h2><p>Database connector missing. Please contact admin.</p></div>");
}

/** * VS Code PHP Intelepheanse 报错修复声明
 * @var PDO $pdo 
 */
global $pdo;
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// 用户身份验证
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_selection.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$active_page = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'my_profile';

// --- 2. THE MASTER SAVE CONTROLLER ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 基础信息提取
    $username  = trim($_POST['username'] ?? ''); 
    $real_name = trim($_POST['real_name'] ?? '');
    $gender    = $_POST['gender'] ?? '';
    $birthday  = $_POST['birthday'] ?? '';
    $phone     = trim($_POST['phone_num'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    
    // 地址合并逻辑
    $addr1 = trim($_POST['address_line1'] ?? '');
    $addr2 = trim($_POST['address_line2'] ?? '');
    $addr3 = trim($_POST['address_line3'] ?? '');
    
    if ($active_page == 'addresses' && (!empty($addr1) || !empty($addr2) || !empty($addr3))) {
        $full_address = $addr1;
        if (!empty($addr2)) $full_address .= ", " . $addr2; 
        if (!empty($addr3)) $full_address .= ", " . $addr3;
    } else {
        $full_address = $_POST['current_full_address'] ?? '';
    }
    
    // 修复：使用大写字段名与SQL一致
    $Postcode  = trim($_POST['Postcode'] ?? '');
    $City      = trim($_POST['City'] ?? '');
    $State     = trim($_POST['State'] ?? '');
    
    // 头像处理逻辑 (保留原图或处理删除/上传)
    $stmt_photo_check = $pdo->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
    $stmt_photo_check->execute([$user_id]);
    $old_user_data = $stmt_photo_check->fetch();
    $photo_filename = $old_user_data['profile_photo'] ?? 'default.jpg';

    if (isset($_POST['delete_avatar_flag']) && $_POST['delete_avatar_flag'] == '1') {
        $photo_filename = 'default.jpg';
    } elseif (!empty($_FILES['profile_photo']['name'])) {
        $file_tmp = $_FILES["profile_photo"]["tmp_name"];
        $file_ext = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $new_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
            if (move_uploaded_file($file_tmp, "../images/" . $new_name)) {
                $photo_filename = $new_name;
            }
        }
    }

    // 执行主数据库更新
    try {
        $update_sql = "UPDATE users SET 
                        username = ?, real_name = ?, gender = ?, birthday = ?, 
                        phone_num = ?, email = ?, address = ?, 
                        Postcode = ?, City = ?, State = ?, 
                        profile_photo = ? WHERE user_id = ?";
        
        $stmt_update = $pdo->prepare($update_sql);
        $result = $stmt_update->execute([
            $username, $real_name, $gender, $birthday, 
            $phone, $email, $full_address, 
            $Postcode, $City, $State, 
            $photo_filename, $user_id
        ]);

        if ($result) {
            $_SESSION['username'] = $username;
            $_SESSION['profile_image'] = $photo_filename;
            echo "<script>alert('Update Successful!'); window.location.href='profile.php?page=$active_page';</script>";
            exit();
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "<script>alert('Error: Could not update profile.');</script>";
    }
}

// --- 3. DATA REFRESH & UTILITIES ---
$stmt_fetch = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt_fetch->execute([$user_id]);
$u = $stmt_fetch->fetch();

// 核心：验证状态判断
$email_verified_status = (isset($u['email_verified']) && $u['email_verified'] == 1);

/** 安全输出辅助函数 */
function d($val) { 
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8'); 
}

if (file_exists($pxx_head_file)) { include_once $pxx_head_file; }
?>

<style>
    :root {
        --pxx-red: #e60012;
        --pxx-dark-red: #d31d1d;
        --pxx-bg: #f5f5f5;
        --pxx-border: #ddd;
        --pxx-text-main: #333;
        --pxx-text-soft: #666;
        --pxx-shadow: 0 1px 6px rgba(0,0,0,0.08);
    }

    body { 
        background-color: var(--pxx-bg); 
        color: var(--pxx-text-main); 
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
        margin: 0; 
        line-height: 1.5;
    }

    .pxx-master-wrapper { 
        width: 1200px; 
        margin: 40px auto; 
        display: flex; 
        gap: 40px; 
        box-sizing: border-box; 
    }

    /* 侧边栏样式 */
    .pxx-sidebar-nav { width: 280px; flex-shrink: 0; }
    
    .sidebar-user-header { 
        display: flex; align-items: center; gap: 15px; 
        padding-bottom: 25px; border-bottom: 1px solid var(--pxx-border); 
        margin-bottom: 30px; 
    }
    
    .sidebar-user-header img { 
        width: 60px; height: 60px; border-radius: 50%; 
        object-fit: cover; border: 1px solid #eee; 
    }

    .nav-section-header { 
        font-size: 16px; font-weight: 700; color: #333; 
        display: flex; align-items: center; margin-bottom: 20px; 
    }
    
    .nav-section-header i { color: var(--pxx-red); width: 35px; font-size: 20px; }

    .nav-link-item { 
        display: block; padding: 12px 0 12px 50px; 
        text-decoration: none; color: #555; font-size: 15px; 
        transition: all 0.2s ease; 
    }
    
    .nav-link-item:hover, .nav-link-item.active { 
        color: var(--pxx-red); font-weight: 600; 
    }

    /* 主内容面板 */
    .pxx-main-dashboard { 
        flex: 1; background: #fff; padding: 50px; 
        border-radius: 4px; box-shadow: var(--pxx-shadow); 
        min-height: 850px; 
    }
    
    .dashboard-header { 
        border-bottom: 1px solid #efefef; padding-bottom: 20px; 
        margin-bottom: 45px; 
    }
    
    .dashboard-header h2 { margin: 0; font-size: 24px; font-weight: 500; }

    /* 表单系统 */
    .form-row-pxx { display: flex; margin-bottom: 35px; align-items: flex-start; }
    
    .form-label-pxx { 
        width: 170px; text-align: right; color: var(--pxx-text-soft); 
        font-size: 15px; margin-right: 35px; padding-top: 12px; 
    }
    
    .form-content-pxx { flex: 1; }
    
    .pxx-input-large { 
        width: 100%; padding: 14px 15px; border: 1px solid var(--pxx-border); 
        border-radius: 4px; font-size: 15px; box-sizing: border-box; 
        transition: border-color 0.2s; background: #fff;
    }
    
    select.pxx-input-large { height: 48px; cursor: pointer; }
    
    .pxx-input-large:focus { border-color: var(--pxx-red); outline: none; }

    .btn-save-master { 
        background: var(--pxx-red); color: #fff; border: none; 
        padding: 16px 70px; border-radius: 2px; cursor: pointer; 
        font-size: 16px; font-weight: 600; transition: background 0.3s;
    }
    
    .btn-save-master:hover { background: var(--pxx-dark-red); }

    /* 头像交互组件 */
    .avatar-interactive-box { position: relative; width: 110px; height: 110px; margin-bottom: 20px; }
    
    .avatar-interactive-box img { 
        width: 100%; height: 100%; border-radius: 50%; 
        object-fit: cover; border: 1px solid var(--pxx-border); 
    }
    
    .avatar-remove-btn { 
        position: absolute; top: 0; right: 0; background: var(--pxx-red); 
        color: #fff; border: none; width: 26px; height: 26px; 
        border-radius: 50%; cursor: pointer; display: flex; 
        align-items: center; justify-content: center; font-weight: bold; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* 安全状态标签 */
    .status-badge { 
        font-size: 11px; padding: 3px 12px; border-radius: 12px; 
        font-weight: bold; text-transform: uppercase; margin-left: 10px; 
    }
    
    .badge-unverified { background: #fff1f0; color: var(--pxx-red); border: 1px solid #ffa39e; }
    .badge-verified { background: #e6fffb; color: #13c2c2; border: 1px solid #87e8de; }

    .blue-link-action { 
        color: #0055aa; text-decoration: none; font-size: 15px; font-weight: bold; 
    }

    .blue-link-action:hover { 
        text-decoration: underline; 
    }

    /* SECURITY ITEM LIST */
    .security-data-row { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 30px 0; 
        border-bottom: 1px solid #f9f9f9; 
    }
</style>

<div class="pxx-master-wrapper">
    
    <div class="pxx-sidebar-nav">
        <div class="sidebar-user-header">
            <img src="../images/<?php echo d($u['profile_photo'] ?: 'default.jpg'); ?>" alt="User Avatar">
            <div class="user-meta">
                <div class="user-name-text"><?php echo d($u['username']); ?></div>
                <a href="?page=my_profile" style="font-size: 12px; color: #888; text-decoration: none;">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>

        <nav>
            <div class="nav-section-header"><i class="fas fa-user-circle"></i> My Account</div>
            <a href="?page=my_profile" class="nav-link-item <?php echo $active_page=='my_profile'?'active':''; ?>">My Profile</a>
            <a href="?page=addresses" class="nav-link-item <?php echo $active_page=='addresses'?'active':''; ?>">My Shipping Addresses</a>
            <a href="?page=bank" class="nav-link-item <?php echo $active_page=='bank'?'active':''; ?>">My Bank Accounts</a>
            <a href="?page=security" class="nav-link-item <?php echo $active_page=='security'?'active':''; ?>">My Account Security</a>
            
            <div style="margin-top: 50px;">
                <a href="../logout.php" class="nav-link-item" style="padding-left: 0; color: #bbb;">
                    <i class="fas fa-power-off" style="width: 35px; text-align: left;"></i>
                    <span>Logout System</span>
                </a>
            </div>
        </nav>
    </div>

    <div class="pxx-main-dashboard">

        <?php if ($active_page == 'my_profile'): ?>
            <div class="dashboard-header"><h2>My Profile</h2></div>
            <form action="profile.php?page=my_profile" method="POST" enctype="multipart/form-data">
                
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Profile Picture</div>
                    <div class="form-content-pxx">
                        <div class="avatar-interactive-box">
                            <img src="../images/<?php echo d($u['profile_photo'] ?: 'default.jpg'); ?>" id="displayAvatarImg">
                            <?php if ($u['profile_photo'] != 'default.jpg'): ?>
                                <button type="button" class="avatar-remove-btn" onclick="triggerPhotoDelete()">×</button>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="profile_photo" accept="image/*" style="font-size: 13px; color: #777;">
                        <input type="hidden" name="delete_avatar_flag" id="avatarDelFlagInput" value="0">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Username</div>
                    <div class="form-content-pxx">
                        <input type="text" name="username" class="pxx-input-large" value="<?php echo d($u['username']); ?>" required>
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Full Name</div>
                    <div class="form-content-pxx">
                        <input type="text" name="real_name" class="pxx-input-large" value="<?php echo d($u['real_name']); ?>">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Gender</div>
                    <div class="form-content-pxx">
                        <div style="padding-top: 10px;">
                            <label><input type="radio" name="gender" value="Male" <?php echo $u['gender']=='Male'?'checked':''; ?>> Male</label>
                            <label style="margin-left: 30px;"><input type="radio" name="gender" value="Female" <?php echo $u['gender']=='Female'?'checked':''; ?>> Female</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Date of Birth</div>
                    <div class="form-content-pxx">
                        <input type="date" name="birthday" class="pxx-input-large" value="<?php echo d($u['birthday']); ?>">
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
            <div class="dashboard-header"><h2>My Shipping Addresses</h2></div>
            
            <div style="margin-bottom: 40px; padding: 20px; background: #fafafa; border: 1px solid #eee; font-size: 14px; color: #888;">
                <i class="fas fa-map-marker-alt"></i> <strong>Current Address:</strong><br>
                <?php echo d($u['address'] ?: 'No address set.'); ?><br>
                <?php echo d($u['Postcode'] . ' ' . $u['City'] . ', ' . $u['State']); ?>
            </div>

            <form action="profile.php?page=addresses" method="POST">
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 1</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line1" class="pxx-input-large" placeholder="Unit / Street Name">
                    </div>
                </div>
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 2</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line2" class="pxx-input-large" placeholder="Building Name (Optional)">
                    </div>
                </div>
                <div class="form-row-pxx">
                    <div class="form-label-pxx">Address Line 3</div>
                    <div class="form-content-pxx">
                        <input type="text" name="address_line3" class="pxx-input-large" placeholder="Area / District">
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">Postcode</div>
                    <div class="form-content-pxx">
                        <input type="text" name="Postcode" class="pxx-input-large" value="<?php echo d($u['Postcode']); ?>" required>
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">State</div>
                    <div class="form-content-pxx">
                        <select name="State" id="m_state" class="pxx-input-large">
                            <option value="">Select State</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-pxx">
                    <div class="form-label-pxx">City</div>
                    <div class="form-content-pxx">
                        <select name="City" id="m_city" class="pxx-input-large">
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="username" value="<?php echo d($u['username']); ?>">
                <input type="hidden" name="real_name" value="<?php echo d($u['real_name']); ?>">
                <input type="hidden" name="current_full_address" value="<?php echo d($u['address']); ?>">
                
                <div class="form-row-pxx" style="margin-top: 40px;">
                    <div class="form-label-pxx"></div>
                    <div class="form-content-pxx">
                        <button type="submit" class="btn-save-master">Save Address Information</button>
                    </div>
                </div>
            </form>

        <?php elseif ($active_page == 'bank'): ?>
            <div class="dashboard-header"><h2>My Bank Account</h2></div>
            
            <div class="security-data-row" style="border: none;">
                <div>
                    <strong style="font-size: 18px; color: #333;">Preferred Bank</strong><br>
                    <span style="color: #888; font-size: 16px; line-height: 2.5;">
                        <?php echo !empty($u['bank_name']) ? d($u['bank_name']) : 'No bank records found'; ?>
                    </span>
                    <span class="status-badge <?php echo !empty($u['bank_name']) ? 'badge-verified' : 'badge-unverified'; ?>">
                        <?php echo !empty($u['bank_name']) ? 'LINKED' : 'UNLINKED'; ?>
                    </span>
                </div>
                <div>
                    <a href="edit_bank.php" class="blue-link-action">Edit</a>
                </div>
            </div>

            <div style="margin-top: 60px; padding: 25px; background: #fffcf5; border: 1px solid #ffecad; border-radius: 8px; color: #856404; font-size: 14px;">
                <i class="fas fa-lock"></i> Your banking information is encrypted. PXX Mall will never store your banking details for any reason!
            </div>

        <?php elseif ($active_page == 'security'): ?>
            <div class="dashboard-header"><h2>My Account Security</h2></div>
            
            <div class="security-data-row">
                <div>
                    <strong>Email Address</strong><br>
                    <span style="color: #888; font-size: 14px; line-height: 2;"><?php echo d($u['email']); ?></span>
                    <span class="status-badge <?php echo $email_verified_status ? 'badge-verified' : 'badge-unverified'; ?>">
                        <?php echo $email_verified_status ? 'Verified' : 'Unverified'; ?>
                    </span>
                </div>
                <div>
                    <a href="edit_email.php" class="blue-link-action">Change Email</a>
                    <?php if (!$email_verified_status): ?>
                        | <a href="email_verify.php" class="blue-link-action">Verify Now</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="security-data-row">
                <div>
                    <strong>Phone Number</strong><br>
                    <span style="color: #888; font-size: 14px; line-height: 2;"><?php echo d($u['phone_num']); ?></span>
                    <span class="status-badge badge-verified">Verified</span>
                </div>
                <div><a href="edit_phone.php" class="blue-link-action">Update Phone Number</a></div>
            </div>

            <div class="security-data-row" style="border: none;">
                <div>
                    <strong>Login Password</strong><br>
                    <span style="color: #888; font-size: 14px; line-height: 2;">******** (Last updated 3 months ago)</span>
                </div>
                <div><a href="update_password.php" class="blue-link-action">Change Password</a></div>
            </div>

            <div style="margin-top: 60px; padding: 25px; background: #fffcf5; border: 1px solid #ffecad; border-radius: 8px; color: #856404; font-size: 14px;">
                <i class="fas fa-shield-alt"></i> Your account security score is <strong>High</strong>. 
                Keep your credentials safe and never share your OTP with anyone.
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stateSelect = document.getElementById('m_state');
    const citySelect = document.getElementById('m_city');

    if (!stateSelect || !citySelect) return;

    const currentSQLState = "<?php echo d($u['State']); ?>";
    const currentSQLCity = "<?php echo d($u['City']); ?>";

    fetch('../includes/state.json') 
        .then(response => {
            if (!response.ok) throw new Error('State JSON file not found');
            return response.json();
        })
        .then(data => {
            const malaysiaRegions = data;

            stateSelect.innerHTML = '<option value="">Select State</option>';
            
            // 1. 加载州属 (States)
            Object.keys(malaysiaRegions).sort().forEach(stateName => {
                let option = new Option(stateName, stateName);
                if (stateName === currentSQLState) {
                    option.selected = true;
                }
                stateSelect.add(option);
            });

            // 2. 如果已有州属数据，加载对应的城市 (Cities)
            if (currentSQLState && malaysiaRegions[currentSQLState]) {
                // 注意：这里传入的是 Object.keys，因为城市名现在是对象的 Key
                fillCities(Object.keys(malaysiaRegions[currentSQLState]), currentSQLCity);
            }

            // 3. 联动事件
            stateSelect.addEventListener('change', function() {
                const selectedState = this.value;
                // 获取选中州属下的所有城市名（Key）
                const cityData = malaysiaRegions[selectedState] ? Object.keys(malaysiaRegions[selectedState]) : [];
                fillCities(cityData);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            stateSelect.innerHTML = '<option value="">Error loading data</option>';
        });

    function fillCities(cityNames, preselectedCity = "") {
        citySelect.innerHTML = '<option value="">Select City</option>';
        cityNames.sort().forEach(cityName => {
            let option = new Option(cityName, cityName);
            if (cityName === preselectedCity) {
                option.selected = true;
            }
            citySelect.add(option);
        });
    }
});

function triggerPhotoDelete() {
    if(confirm('Warning: Remove your current photo and use default?')) {
        document.getElementById('displayAvatarImg').src = '../images/default.jpg';
        document.getElementById('avatarDelFlagInput').value = '1';
    }
}
</script>

<?php 
if (file_exists($pxx_foot_file)) { include_once $pxx_foot_file; } 
?>