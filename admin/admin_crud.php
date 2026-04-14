<?php
/**
 * =================================================================================
 * PXX MALL - ENTERPRISE NEW ADMIN DEPLOYMENT (V1.0)
 * =================================================================================
 * 功能：独立的添加管理员页面
 * 包含字段：user_id (自动生成 ad1001+), username, password, real_name, gender, 
 * phone_num, email, address, Postcode, City, State, role 等完整资料。
 * =================================================================================
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. 物理挂载基础核心与数据库
require_once '../includes/db.php';     

$pxx_msg = '';
$pxx_msg_type = '';

// -----------------------------------------------------------------------------
// 【智能 ID 生成器】自动寻找 ad 开头的最大数字并 +1
// -----------------------------------------------------------------------------
function generateNewAdminID($pdo) {
    $stmt = $pdo->query("SELECT user_id FROM users WHERE user_id LIKE 'ad%' ORDER BY CAST(SUBSTRING(user_id, 3) AS UNSIGNED) DESC LIMIT 1");
    $last_id = $stmt->fetchColumn();
    if (!$last_id) return "ad1001";
    $num = (int)substr($last_id, 2);
    return "ad" . ($num + 1);
}

// -----------------------------------------------------------------------------
// 【表单提交处理逻辑】
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deploy_admin'])) {
    
    // 基础鉴权与数据提取
    $a_user    = trim($_POST['username']);
    $a_pass    = $_POST['password'];
    $a_conf    = $_POST['confirm_password'];
    $a_role    = $_POST['role']; // Admin or S.Admin
    
    // 详细资料提取
    $a_real    = trim($_POST['real_name'] ?? '');
    $a_gend    = $_POST['gender'] ?? 'Male';
    $a_phone   = trim($_POST['phone_num'] ?? '');
    $a_email   = trim($_POST['email'] ?? '');
    $a_addr    = trim($_POST['address'] ?? '');
    $a_pcode   = trim($_POST['postcode'] ?? '');
    $a_city    = trim($_POST['city'] ?? '');
    $a_state   = trim($_POST['state'] ?? '');

    // 1. 校验密码一致性
    if ($a_pass !== $a_conf) {
        $pxx_msg = "SECURITY ALERT: Passwords do not match.";
        $pxx_msg_type = "error";
    } elseif (strlen($a_pass) < 6) {
        $pxx_msg = "SECURITY ALERT: Password must be at least 6 characters.";
        $pxx_msg_type = "error";
    } else {
        // 2. 查重：确保 Username 唯一
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $check->execute([$a_user]);
        
        if ($check->fetchColumn() > 0) {
            $pxx_msg = "CONFLICT: The username '$a_user' is already in use.";
            $pxx_msg_type = "error";
        } else {
            try {
                // 3. 执行物理插入
                $new_ad_id = generateNewAdminID($pdo);
                $hash_pass = password_hash($a_pass, PASSWORD_DEFAULT);
                $now = date("Y-m-d H:i:s");

                // 强制默认值：新 Admin 默认 Active，并且邮箱/手机状态设为 Verified
                $sql = "INSERT INTO users (
                            user_id, username, password_hashed, real_name, gender, 
                            phone_num, email, address, Postcode, City, State, 
                            role, status, email_status, phone_status, points, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'Verified', 'Verified', 0, ?)";
                
                $stmt = $pdo->prepare($sql);
                $status = $stmt->execute([
                    $new_ad_id, $a_user, $hash_pass, $a_real, $a_gend, 
                    $a_phone, $a_email, $a_addr, $a_pcode, $a_city, $a_state, 
                    $a_role, $now
                ]);

                if ($status) {
                    echo "<script>alert('System Administrator [$new_ad_id] successfully deployed!'); window.location.href='admin_manage.php';</script>";
                    exit;
                }
            } catch (PDOException $e) {
                $pxx_msg = "DATABASE ERROR: " . $e->getMessage();
                $pxx_msg_type = "error";
            }
        }
    }
}

// 物理加载 Header
include '../includes/_admin_head.php'; 
?>

<style>
    /* 屏蔽默认模板可能带有的多余组件 */
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid { display: none !important; }
    
    body { background: #f4f7f6; font-family: 'Inter', sans-serif; }
    
    .deploy-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }

    /* 顶部标头 */
    .deploy-header {
        background: #111; padding: 35px 50px; border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15); margin-bottom: 30px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .deploy-header h1 { margin: 0; font-size: 32px; font-weight: 900; color: #fff; letter-spacing: -1px; }
    .deploy-header p { margin: 8px 0 0; color: #eab308; font-size: 15px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    
    .btn-return { 
        background: rgba(255,255,255,0.1); color: #fff; padding: 12px 25px; border-radius: 10px; 
        font-weight: 800; text-decoration: none; border: 1px solid rgba(255,255,255,0.2); transition: 0.3s; 
    }
    .btn-return:hover { background: #fff; color: #111; }

    /* 表单卡片主体 */
    .deploy-card { 
        background: #fff; border-radius: 20px; padding: 50px; 
        box-shadow: 0 10px 40px rgba(0,0,0,0.06); border-top: 10px solid #eab308; 
    }

    /* 警告/错误提示框 */
    .alert-box { padding: 20px; border-radius: 12px; margin-bottom: 35px; font-weight: 800; display: flex; align-items: center; gap: 15px; }
    .alert-error { background: #fff1f0; color: #cf1322; border-left: 6px solid #ff4d4f; }
    .alert-success { background: #f6ffed; color: #389e0d; border-left: 6px solid #52c41a; }

    /* 表单控件布局 */
    .section-title { font-size: 18px; font-weight: 900; color: #111; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin: 40px 0 25px 0; text-transform: uppercase; letter-spacing: 1px; }
    .section-title:first-child { margin-top: 0; }
    .section-title i { color: #eab308; margin-right: 10px; }

    .pxx-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 25px; }
    .pxx-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-bottom: 25px; }
    
    .f-field { display: flex; flex-direction: column; gap: 10px; position: relative; }
    .f-field label { font-size: 13px; font-weight: 800; color: #444; text-transform: uppercase; }
    
    .f-input, .f-select { 
        width: 100%; padding: 16px 20px; border: 2px solid #eee; border-radius: 10px; 
        font-size: 15px; outline: none; transition: 0.3s; background: #fafafa; box-sizing: border-box;
    }
    .f-input:focus, .f-select:focus { border-color: #eab308; background: #fff; box-shadow: 0 0 0 5px rgba(234, 179, 8, 0.1); }
    .f-input.error { border-color: #ff4d4f; background: #fff1f0; }

    /* 提交按钮 */
    .btn-submit { 
        width: 100%; background: #eab308; color: #111; border: none; padding: 22px; 
        border-radius: 12px; font-size: 18px; font-weight: 900; cursor: pointer; 
        transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; margin-top: 30px; 
    }
    .btn-submit:hover { background: #111; color: #eab308; transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
    .btn-submit:disabled { background: #ddd; color: #888; cursor: not-allowed; transform: none; box-shadow: none; }
</style>

<div id="premium-deploy-content" class="deploy-container">
    
    <div class="deploy-header">
        <div>
            <h1>Deploy Administrator</h1>
            <p><i class="fas fa-shield-alt"></i> Grant Enterprise Access Level</p>
        </div>
        <a href="admin_manage.php" class="btn-return"><i class="fas fa-arrow-left"></i> Return to Directory</a>
    </div>

    <div class="deploy-card">
        
        <?php if($pxx_msg): ?>
            <div class="alert-box <?= $pxx_msg_type == 'error' ? 'alert-error' : 'alert-success' ?>">
                <i class="fas <?= $pxx_msg_type == 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle' ?>"></i> 
                <?= $pxx_msg ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            
            <div class="section-title"><i class="fas fa-key"></i> Security Credentials</div>
            
            <div class="pxx-grid-2">
                <div class="f-field">
                    <label>Unique Username *</label>
                    <input type="text" name="username" class="f-input" placeholder="System login handle" required autofocus>
                </div>
                <div class="f-field">
                    <label>Privilege Role *</label>
                    <select name="role" class="f-select" required>
                        <option value="Admin">Standard Admin (Manage Products/Orders)</option>
                        <option value="S.Admin">Super Admin (Full System Access)</option>
                    </select>
                </div>
            </div>

            <div class="pxx-grid-2">
                <div class="f-field">
                    <label>Login Password *</label>
                    <input type="password" name="password" id="p_main" class="f-input" placeholder="Min 6 characters" required oninput="validatePasswords()">
                </div>
                <div class="f-field">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" id="p_conf" class="f-input" placeholder="Match password" required oninput="validatePasswords()">
                    <span id="p_warn" style="color:#ff4d4f; font-size:12px; font-weight:700; display:none; margin-top:5px;">Passwords do not match.</span>
                </div>
            </div>

            <div class="section-title"><i class="fas fa-id-card"></i> Personal Identity</div>

            <div class="pxx-grid-2">
                <div class="f-field">
                    <label>Full Real Name</label>
                    <input type="text" name="real_name" class="f-input" placeholder="As per official documents">
                </div>
                <div class="f-field">
                    <label>Gender</label>
                    <select name="gender" class="f-select">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="pxx-grid-2">
                <div class="f-field">
                    <label>Contact Number</label>
                    <input type="text" name="phone_num" class="f-input" placeholder="01X-XXXXXXX">
                </div>
                <div class="f-field">
                    <label>Email Address</label>
                    <input type="email" name="email" class="f-input" placeholder="admin@pxxmall.com">
                </div>
            </div>

            <div class="section-title"><i class="fas fa-map-marker-alt"></i> Location Details</div>

            <div class="f-field" style="margin-bottom: 25px;">
                <label>Full Address</label>
                <input type="text" name="address" class="f-input" placeholder="Unit, Building, Street Name...">
            </div>

            <div class="pxx-grid-3">
                <div class="f-field">
                    <label>Postcode</label>
                    <input type="text" name="postcode" class="f-input" placeholder="5-Digit Code">
                </div>
                <div class="f-field">
                    <label>City</label>
                    <input type="text" name="city" class="f-input" placeholder="City Name">
                </div>
                <div class="f-field">
                    <label>State</label>
                    <input type="text" name="state" class="f-input" placeholder="State Name">
                </div>
            </div>

            <input type="hidden" name="deploy_admin" value="1">
            <button type="submit" id="btn_deploy" class="btn-submit">Authorize & Deploy Admin</button>

        </form>
    </div>
</div>

<script>
    // 强制重绘，适配你的后台模板框架
    document.addEventListener("DOMContentLoaded", function() {
        const vp = document.querySelector('.admin-content-viewport');
        const ct = document.getElementById('premium-deploy-content');
        if (vp && ct) { vp.innerHTML = ''; vp.appendChild(ct); }
    });

    // 前端安全：即时密码验证
    function validatePasswords() {
        const p1 = document.getElementById('p_main');
        const p2 = document.getElementById('p_conf');
        const warn = document.getElementById('p_warn');
        const btn = document.getElementById('btn_deploy');

        if (p2.value === "") {
            p2.classList.remove('error'); warn.style.display = 'none'; btn.disabled = false; return;
        }

        if (p1.value !== p2.value || p1.value.length < 6) {
            p2.classList.add('error'); warn.style.display = 'block'; btn.disabled = true;
        } else {
            p2.classList.remove('error'); warn.style.display = 'none'; btn.disabled = false;
        }
    }
</script>

<?php include '../includes/_foot.php'; ?>