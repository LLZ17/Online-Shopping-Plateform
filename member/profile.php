<?php
/**
 * PXX Mall - Ultra Premium Member Profile Design
 * Logic: Grid System, State/City Selection, Avatar Management, and Form Validation.
 */

// 1. 初始化会话与数据库
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../includes/db.php'; 

// 2. 假设从 URL 获取当前编辑的 Member ID
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 1; 

// 3. 抓取会员原始数据
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'Member'");
    $stmt->execute([$user_id]);
    $m = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$m) {
        die("<div style='text-align:center; padding:100px; font-family:sans-serif;'>
                <h1 style='color:#ef4444;'>Member Not Found</h1>
                <p>The user record you are looking for does not exist.</p>
                <a href='admin_dashboard.php' style='color:#4f46e5; font-weight:bold;'>Back to Dashboard</a>
             </div>");
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// 4. 处理图片路径
$photo = !empty($m['profile_photo']) ? $m['profile_photo'] : 'default.jpg';
$finalAvatar = "../images/" . $photo;

// 5. 页面渲染开始
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | <?php echo htmlspecialchars($m['real_name']); ?> | PXX Mall</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* =========================================
           🎨 GLOBAL STYLES & VARIABLES
           ========================================= */
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --danger: #ef4444;
            --success: #10b981;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --radius-xl: 20px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--bg-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding: 60px 20px;
            line-height: 1.5;
        }

        /* =========================================
           🏛️ LAYOUT CONTAINER
           ========================================= */
        .premium-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255,255,255,0.7);
            overflow: hidden;
        }

        .header-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 50px 40px;
            text-align: center;
            color: white;
            position: relative;
        }

        .header-banner h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .header-banner p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 15px;
        }

        /* =========================================
           📸 AVATAR COMPONENT
           ========================================= */
        .avatar-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: -60px; /* 向上偏移产生重叠效果 */
            margin-bottom: 40px;
        }

        .avatar-holder {
            position: relative;
            width: 130px;
            height: 130px;
            cursor: pointer;
            z-index: 10;
        }

        .avatar-holder img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid var(--card-bg);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .avatar-holder:hover img {
            filter: brightness(0.8);
            transform: scale(1.02);
        }

        .avatar-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--danger);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid var(--card-bg);
            font-size: 14px;
            transition: var(--transition);
        }

        #file_upload { display: none; }

        /* =========================================
           📝 FORM SYSTEM
           ========================================= */
        .form-container {
            padding: 0 50px 50px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .full-col { grid-column: span 2; }

        .field-box {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .field-box label {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* 专门为 CITY 打造的高级 Label */
        .label-premium {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e293b;
            border-left: 4px solid var(--primary);
            padding-left: 12px;
            margin: 10px 0;
        }

        /* 输入控件统一样式 */
        .pxx-field {
            width: 100%;
            padding: 15px 20px;
            font-size: 15px;
            font-family: inherit;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-lg);
            background-color: white;
            color: var(--text-dark);
            transition: var(--transition);
            box-sizing: border-box;
            outline: none;
        }

        .pxx-field:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .pxx-field::placeholder { color: #cbd5e1; }

        /* 只读样式 */
        .pxx-field:read-only {
            background-color: #f1f5f9;
            cursor: not-allowed;
            color: var(--text-muted);
        }

        /* 自定义下拉框箭头 */
        .select-wrapper {
            position: relative;
        }

        .select-wrapper::after {
            content: "\f078";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            font-size: 12px;
        }

        .pxx-select {
            appearance: none;
            cursor: pointer;
            padding-right: 50px;
        }

        /* =========================================
           💡 ADDRESS SECTION STYLING
           ========================================= */
        .address-group {
            background: #f8fafc;
            padding: 30px;
            border-radius: var(--radius-lg);
            border: 1px dashed var(--border);
            margin-bottom: 30px;
        }

        .address-group-title {
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* =========================================
           🚀 BUTTON SYSTEM
           ========================================= */
        .action-bar {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }

        .btn {
            padding: 18px 30px;
            border-radius: var(--radius-lg);
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
        }

        .btn-save {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }

        .btn-save:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
        }

        .btn-cancel {
            background-color: #f1f5f9;
            color: var(--text-muted);
            flex: 0.5;
        }

        .btn-cancel:hover { background-color: #e2e8f0; }

        /* =========================================
           ✅ VALIDATION STATES
           ========================================= */
        .is-invalid { border-color: var(--danger) !important; }
        .error-msg {
            font-size: 12px;
            color: var(--danger);
            font-weight: 600;
            margin-top: 5px;
            display: none;
        }

        /* 响应式断点 */
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-col { grid-column: span 1; }
            .form-container { padding: 0 25px 40px; }
            .action-bar { flex-direction: column; }
            .btn-cancel { order: 2; }
        }
    </style>
</head>
<body>

<div class="premium-wrapper">
    <div class="header-banner">
        <h1>Profile Customization</h1>
        <p>Manage member account details and shipping information</p>
    </div>

    <form id="memberProfileForm" action="process_member_update.php" method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

        <div class="avatar-section">
            <div class="avatar-holder" onclick="document.getElementById('file_upload').click()" title="Click to Change Photo">
                <img src="<?php echo $finalAvatar; ?>" id="avatar-preview" alt="Member Avatar">
                <div class="avatar-badge">
                    <i class="fa-solid fa-camera"></i>
                </div>
            </div>
            <input type="file" name="profile_photo" id="file_upload" accept="image/*">
            <p style="font-size: 12px; font-weight: 600; color: var(--text-muted); margin-top: 15px;">
                Member ID: #<?php echo str_pad($m['user_id'], 5, '0', STR_PAD_LEFT); ?>
            </p>
        </div>

        <div class="form-container">
            <div class="form-grid">
                <div class="field-box">
                    <label><i class="fa-solid fa-user-tag"></i> Username</label>
                    <input type="text" name="username" class="pxx-field" value="<?php echo htmlspecialchars($m['username']); ?>" readonly>
                </div>

                <div class="field-box">
                    <label><i class="fa-solid fa-signature"></i> Full Name</label>
                    <input type="text" name="real_name" class="pxx-field" value="<?php echo htmlspecialchars($m['real_name']); ?>" placeholder="Enter real name">
                </div>

                <div class="field-box">
                    <label><i class="fa-solid fa-envelope"></i> Email Address</label>
                    <input type="email" id="input_email" name="email" class="pxx-field" value="<?php echo htmlspecialchars($m['email']); ?>" placeholder="name@example.com">
                    <span class="error-msg" id="err_email">Please enter a valid email address.</span>
                </div>

                <div class="field-box">
                    <label><i class="fa-solid fa-phone"></i> Contact Number</label>
                    <input type="text" name="phone_num" class="pxx-field" value="<?php echo htmlspecialchars($m['phone_num']); ?>" placeholder="e.g. 012-3456789">
                </div>
            </div>

            <hr style="border:0; border-top:1px solid var(--border); margin: 40px 0;">

            <div class="address-group">
                <div class="address-group-title">
                    <i class="fa-solid fa-map-location-dot"></i> Shipping & Billing Address
                </div>

                <div class="form-grid">
                    <div class="field-box full-col">
                        <label>Address Line 1</label>
                        <input type="text" name="address_line_1" class="pxx-field" value="<?php echo htmlspecialchars($m['address'] ?? ''); ?>" placeholder="Unit / House Number / Floor">
                    </div>

                    <div class="field-box full-col">
                        <label>Address Line 2</label>
                        <input type="text" name="address_line_2" class="pxx-field" placeholder="Building Name / Street Name / Block">
                    </div>

                    <div class="field-box full-col">
                        <label>Address Line 3</label>
                        <input type="text" name="address_line_3" class="pxx-field" placeholder="Area / District / Section">
                    </div>

                    <div class="field-box">
                        <label>Postcode</label>
                        <input type="text" id="m_postcode" name="postcode" class="pxx-field" value="<?php echo htmlspecialchars($m['Postcode'] ?? ''); ?>" placeholder="53300" maxlength="5">
                        <span class="error-msg" id="err_postcode">Postcode must be 5 digits.</span>
                    </div>

                    <div class="field-box">
                        <label>State</label>
                        <div class="select-wrapper">
                            <select id="m_state" name="state" class="pxx-field pxx-select" onchange="loadCities()">
                                <option value="">Select State</option>
                                </select>
                        </div>
                    </div>

                    <div class="field-box full-col">
                        <label class="label-premium">City</label>
                        <div class="select-wrapper">
                            <select id="m_city" name="city" class="pxx-field pxx-select">
                                <option value="">Select City (Please choose state first)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-bar">
                <button type="submit" class="btn btn-save" id="saveBtn">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Save Profile Changes
                </button>
                <button type="button" class="btn btn-cancel" onclick="window.history.back()">
                    Cancel
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // 1. 全局数据存储
    let stateCityData = {};

    // 2. 页面初始化
    document.addEventListener('DOMContentLoaded', function() {
        initData();
        setupAvatarPreview();
        setupValidation();
    });

    // 3. 异步获取 state.json 并填充州列表
    async function initData() {
        try {
            const response = await fetch('state.json');
            stateCityData = await response.json();
            
            const stateDropdown = document.getElementById('m_state');
            const savedState = "<?php echo $m['State']; ?>";

            // 填充 State
            for (const state in stateCityData) {
                const option = document.createElement('option');
                option.value = state;
                option.textContent = state;
                if(state === savedState) option.selected = true;
                stateDropdown.appendChild(option);
            }

            // 如果已有州，则加载对应的城市
            if(savedState) {
                loadCities("<?php echo $m['City']; ?>");
            }

        } catch (error) {
            console.error('Error loading locations:', error);
        }
    }

    // 4. 联动城市加载函数
    function loadCities(preSelectedCity = "") {
        const selectedState = document.getElementById('m_state').value;
        const cityDropdown = document.getElementById('m_city');
        
        // 清空
        cityDropdown.innerHTML = '<option value="">Select City</option>';

        if (selectedState && stateCityData[selectedState]) {
            stateCityData[selectedState].forEach(city => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                if(city === preSelectedCity) option.selected = true;
                cityDropdown.appendChild(option);
            });
        } else {
            cityDropdown.innerHTML = '<option value="">Select City (Please choose state first)</option>';
        }
    }

    // 5. 头像预览功能
    function setupAvatarPreview() {
        const fileInput = document.getElementById('file_upload');
        const previewImg = document.getElementById('avatar-preview');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    // 增加微动动画反馈
                    previewImg.style.transform = "scale(1.1)";
                    setTimeout(() => previewImg.style.transform = "scale(1)", 200);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // 6. 表单实时验证
    function setupValidation() {
        const form = document.getElementById('memberProfileForm');
        const emailInput = document.getElementById('input_email');
        const postcodeInput = document.getElementById('m_postcode');

        // 邮编验证 (仅限数字)
        postcodeInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if(this.value.length === 5) {
                this.classList.remove('is-invalid');
                document.getElementById('err_postcode').style.display = 'none';
            }
        });

        // 提交拦截
        form.addEventListener('submit', function(e) {
            let hasError = false;

            // 验证邮箱
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                emailInput.classList.add('is-invalid');
                document.getElementById('err_email').style.display = 'block';
                hasError = true;
            } else {
                emailInput.classList.remove('is-invalid');
                document.getElementById('err_email').style.display = 'none';
            }

            // 验证邮编
            if (postcodeInput.value.length !== 5) {
                postcodeInput.classList.add('is-invalid');
                document.getElementById('err_postcode').style.display = 'block';
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                // 震动反馈
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                const btn = document.getElementById('saveBtn');
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving Data...';
                btn.style.opacity = "0.7";
            }
        });
    }
</script>

</body>
</html>