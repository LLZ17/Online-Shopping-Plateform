<?php
/**
 * =================================================================================
 * PXX MALL - ULTIMATE MULTI-STEP REGISTRY (V110.6 - UNIQUE USERNAME ENFORCED)
 * =================================================================================
 * [CORE UPDATES]:
 * 1. Database Check: Implemented real-time username collision detection.
 * 2. Visual: 1100px Wizard Card with Parallax Background & Red Theme.
 * 3. Security: Multi-phase Validation + Server-side Guardrail.
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// --- [STAGE 1: 物理路径引擎与依赖加载] ---
$pxx_path = __DIR__;
$pxx_root = (basename($pxx_path) === 'member') ? dirname($pxx_path) : $pxx_path;

define('PXX_DB', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php');
define('PXX_HEAD', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_head.php');
define('PXX_FOOT', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_foot.php');

// 数据库连接检测
if (file_exists(PXX_DB)) { 
    require_once PXX_DB; 
} else { 
    die("PXX SYSTEM FATAL: Database connector missing."); 
}

// 适配变量名
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// --- [STAGE 2: MEMBER ID 自动递增逻辑] ---
function generatePXXID($pdo) {
    try {
        $sql = "SELECT user_id FROM users WHERE user_id LIKE 'm%' ORDER BY CAST(SUBSTRING(user_id, 2) AS UNSIGNED) DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        $last_id = $stmt->fetchColumn();
        if (!$last_id) return "m1060";
        $num = (int)substr($last_id, 1);
        return "m" . (($num < 1060) ? 1060 : ($num + 1));
    } catch (Exception $e) {
        return "m" . rand(2000, 9999);
    }
}

// --- [STAGE 3: 核心控制器 - 包含 USERNAME 查重逻辑] ---
$pxx_server_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['final_save'])) {
    // 数据收集与清洗
    $u_user = trim($_POST['username'] ?? '');
    $u_pass = $_POST['password'] ?? '';
    $u_real = trim($_POST['real_name'] ?? '');
    $u_gend = $_POST['gender'] ?? 'Male';
    $u_bday = $_POST['birthday'] ?? '';
    $u_phone = trim($_POST['phone_num'] ?? '');
    $u_email = trim($_POST['email'] ?? '');
    
    // 物流地址合并
    $a1 = trim($_POST['address_line1'] ?? '');
    $a2 = trim($_POST['address_line2'] ?? '');
    $a3 = trim($_POST['address_line3'] ?? '');
    $combined_addr = $a1 . ($a2 ? " | $a2" : "") . ($a3 ? " | $a3" : "");
    
    $u_pcode = trim($_POST['postcode'] ?? '');
    $u_city = trim($_POST['city'] ?? '');
    $u_state = trim($_POST['state'] ?? '');
    $u_agree = isset($_POST['tnc_agree']) ? true : false;

    try {
        // 1. 协议检查
        if (!$u_agree) {
            $pxx_server_error = "Policy Error: You must accept the Terms and Conditions to proceed.";
        } else {
            // 🔥 2. 核心查重：检查 Username 是否已存在
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $check_stmt->execute([$u_user]);
            $user_exists = $check_stmt->fetchColumn();

            if ($user_exists > 0) {
                // 如果名字已经存在，拦截并报错
                $pxx_server_error = "Registration Failed: Username '<strong>$u_user</strong>' already exists. Please choose a different one.";
            } else {
                // 3. 执行最终注册流程
                $new_id = generatePXXID($pdo);
                $hash = password_hash($u_pass, PASSWORD_DEFAULT);
                $now = date("Y-m-d H:i:s");

                $sql = "INSERT INTO users (user_id, username, password_hashed, real_name, gender, birthday, phone_num, email, address, Postcode, City, State, created_at, status, role, points) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'Member', 100)";
                
                $stmt = $pdo->prepare($sql);
                $success = $stmt->execute([
                    $new_id, $u_user, $hash, $u_real, $u_gend, $u_bday, 
                    $u_phone, $u_email, $combined_addr, $u_pcode, $u_city, $u_state, $now
                ]);

                if ($success) {
                    $_SESSION['user_id'] = $new_id;
                    $_SESSION['username'] = $u_user;
                    $_SESSION['role'] = 'Member';
                    echo "<script>alert('Account Created! Your Member ID is $new_id'); window.location.href='index.php';</script>";
                    exit();
                }
            }
        }
    } catch (PDOException $e) {
        $pxx_server_error = "Database Infrastructure Error: " . $e->getMessage();
    }
}

if (file_exists(PXX_HEAD)) { include_once PXX_HEAD; }
?>

<style>
    /* =================================================================================
       PXX MALL - STYLE ENGINE
       ================================================================================= */
    :root { 
        --pxx-red: #e60012; 
        --pxx-error: #ff4d4f; 
        --pxx-glass: #ffffff;
    }

    body, html { margin: 0; padding: 0; width: 100%; min-height: 100vh; background: transparent !important; }

    .pxx-auth-viewport { 
        min-height: 100vh; width: 100%; display: flex; justify-content: center; align-items: center; 
        padding: 80px 0; background-image: url('../images/bg.jpeg'); 
        background-size: cover; background-position: center; background-attachment: fixed;
    }

    .pxx-wizard-card { 
        width: 1100px; background: var(--pxx-glass); border-radius: 30px; 
        box-shadow: 0 60px 200px rgba(0,0,0,0.8); overflow: hidden; 
        border-top: 20px solid var(--pxx-red); position: relative;
        animation: cardAppear 0.8s ease-out;
    }

    @keyframes cardAppear { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

    /* Stepper Header */
    .stepper-header { display: flex; background: #f9f9f9; border-bottom: 1px solid #eee; padding: 50px 0; justify-content: space-around; }
    .step-item { display: flex; align-items: center; gap: 15px; color: #ccc; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 1.5px; }
    .step-item.active { color: var(--pxx-red); }
    .step-dot { width: 40px; height: 40px; border-radius: 50%; border: 2.5px solid currentColor; display: flex; align-items: center; justify-content: center; font-weight: 900; }

    /* Form Body */
    .pxx-form-body { padding: 80px 140px; min-height: 550px; }
    .step-pane { display: none; }
    .step-pane.active { display: block; animation: fadeIn 0.5s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .pxx-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; }
    .pxx-field { display: flex; flex-direction: column; gap: 12px; }
    .pxx-field label { font-size: 13px; font-weight: 800; color: #444; text-transform: uppercase; }
    .pxx-input, .pxx-select { width: 100%; padding: 20px; border: 2px solid #eee; border-radius: 14px; font-size: 16px; transition: 0.3s; }
    .pxx-input:focus { border-color: var(--pxx-red); outline: none; box-shadow: 0 0 0 5px rgba(230,0,18,0.1); }

    /* TnC Scroll Box */
    .tnc-wrapper { margin-top: 40px; padding: 25px; background: #fafafa; border-radius: 15px; border: 1px solid #eee; }
    .tnc-scroll { 
        height: 140px; overflow-y: scroll; background: #fff; padding: 20px; 
        font-size: 13px; color: #666; border: 1px solid #eee; border-radius: 10px; margin-bottom: 20px;
        line-height: 1.7;
    }
    .tnc-scroll::-webkit-scrollbar { width: 5px; }
    .tnc-scroll::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    .tnc-agree-row { display: flex; align-items: center; gap: 12px; font-weight: 700; cursor: pointer; color: #333; }
    .tnc-agree-row input { width: 22px; height: 22px; cursor: pointer; accent-color: var(--pxx-red); }

    /* Footer Navigation */
    .pxx-footer { padding: 50px 140px; background: #f9f9f9; border-top: 1px solid #eee; }
    .btn-row { display: flex; gap: 30px; margin-bottom: 30px; }
    .pxx-btn { padding: 22px 60px; border-radius: 15px; font-size: 18px; font-weight: 900; cursor: pointer; transition: 0.3s; text-transform: uppercase; border: none; letter-spacing: 1px; }
    .btn-next { background: var(--pxx-red); color: #fff; flex: 1; box-shadow: 0 10px 25px rgba(230,0,18,0.2); }
    .btn-next:disabled { background: #ccc; cursor: not-allowed; box-shadow: none; }
    .btn-back { background: #fff; color: #888; border: 2px solid #ddd; }
    
    .prog-track { width: 100%; height: 10px; background: #e9ecef; border-radius: 10px; overflow: hidden; }
    .prog-fill { width: 33.33%; height: 100%; background: var(--pxx-red); transition: 0.7s cubic-bezier(0.65, 0, 0.35, 1); }
</style>

<div class="pxx-auth-viewport">
    <div class="pxx-wizard-card">
        <div class="stepper-header">
            <div class="step-item active" id="dot-1"><div class="step-dot">1</div> Security</div>
            <div class="step-item" id="dot-2"><div class="step-dot">2</div> Personal</div>
            <div class="step-item" id="dot-3"><div class="step-dot">3</div> Logistics</div>
        </div>

        <div class="pxx-form-body">
            <?php if($pxx_server_error): ?>
                <div style="background:#fff1f0; padding:25px; border-radius:15px; color:#cf1322; font-weight:700; margin-bottom:45px; border-left:8px solid var(--pxx-error); box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $pxx_server_error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="pxxRegForm">
                <div class="step-pane active" id="pane-1">
                    <h2 style="font-size:32px; margin-bottom:40px; letter-spacing:-1px;">Login Credentials</h2>
                    <div class="pxx-field" style="margin-bottom:35px;">
                        <label>Unique Username</label>
                        <input type="text" name="username" id="f_user" class="pxx-input" placeholder="Choose your handle" required oninput="validateStep1()">
                    </div>
                    <div class="pxx-grid">
                        <div class="pxx-field">
                            <label>Password (6+ chars)</label>
                            <input type="password" name="password" id="f_pass" class="pxx-input" placeholder="••••••••" required oninput="validateStep1()">
                        </div>
                        <div class="pxx-field">
                            <label>Verify Password</label>
                            <input type="password" id="f_conf" class="pxx-input" placeholder="••••••••" required oninput="validateStep1()">
                        </div>
                    </div>
                </div>

                <div class="step-pane" id="pane-2">
                    <h2 style="font-size:32px; margin-bottom:40px; letter-spacing:-1px;">Personal Identity</h2>
                    <div class="pxx-grid">
                        <div class="pxx-field"><label>Full Name</label><input type="text" name="real_name" id="f_real" class="pxx-input" placeholder="As per Identity" oninput="validateStep2()"></div>
                        <div class="pxx-field">
                            <label>Gender</label>
                            <select name="gender" class="pxx-select"><option value="Male">Male</option><option value="Female">Female</option></select>
                        </div>
                    </div>
                    <div class="pxx-grid">
                        <div class="pxx-field"><label>Birthday</label><input type="date" name="birthday" id="f_bday" class="pxx-input" onchange="validateStep2()"></div>
                        <div class="pxx-field"><label>Mobile Phone</label><input type="text" name="phone_num" id="f_phone" class="pxx-input" placeholder="01X-XXXXXXX" oninput="validateStep2()"></div>
                    </div>
                    <div class="pxx-field">
                        <label>Email Address</label>
                        <input type="email" name="email" id="f_mail" class="pxx-input" placeholder="user@domain.com" oninput="validateStep2()">
                    </div>
                </div>

                <div class="step-pane" id="pane-3">
                    <h2 style="font-size:32px; margin-bottom:40px; letter-spacing:-1px;">Shipping & Policies</h2>
                    <div class="pxx-field" style="margin-bottom:20px;"><label>Street Address</label><input type="text" name="address_line1" id="f_a1" class="pxx-input" placeholder="No, Street, Area" oninput="validateStep3()"></div>
                    
                    <div class="pxx-grid">
                        <div class="pxx-field"><label>Postcode</label><input type="text" name="postcode" id="f_pcode" class="pxx-input" placeholder="53300" oninput="validateStep3()"></div>
                        <div class="pxx-field">
                            <label>State</label>
                            <select name="state" id="f_state" class="pxx-select" onchange="loadCities()"><option value="">Select State</option></select>
                        </div>
                    </div>
                    <div class="pxx-field">
                        <label>City</label>
                        <select name="city" id="f_city" class="pxx-select" disabled onchange="validateStep3()"><option value="">Select City</option></select>
                    </div>

                    <div class="tnc-wrapper">
                        <label style="font-size:12px; font-weight:900; color:#333; text-transform:uppercase;">PXX Mall® User Agreement</label>
                        <div class="tnc-scroll">
                            <strong>1. Academic Scope:</strong> This platform is a student project for educational simulation. No actual commerce is conducted.<br><br>
                            <strong>2. Data Privacy:</strong> We adhere to PDPA principles. Your data is used strictly for testing purposes within this project.<br><br>
                            <strong>3. Account Usage:</strong> Users are responsible for their login credentials. PXX Mall is not liable for unauthorized access due to weak passwords.<br><br>
                            <strong>4. Product Information:</strong> Clothing data and images are for reference and educational demonstration only.
                        </div>
                        <label class="tnc-agree-row">
                            <input type="checkbox" name="tnc_agree" id="f_agree" onchange="validateStep3()">
                            I agree to the Terms of Service and Privacy Policy.
                        </label>
                    </div>

                    <input type="hidden" name="final_save" value="1">
                </div>
            </form>
        </div>

        <div class="pxx-footer">
            <div class="btn-row">
                <button type="button" id="btnB" class="pxx-btn btn-back" style="display:none;" onclick="nav(-1)">Back</button>
                <button type="button" id="btnN" class="pxx-btn btn-next" onclick="nav(1)" disabled>Continue</button>
            </div>
            <div class="prog-track"><div class="prog-fill" id="f_bar"></div></div>
        </div>
    </div>
</div>

<script>
    let curStep = 1;
    let locData = {};

    // 初始化地理数据
    async function initLocation() {
        const res = await fetch('../includes/state.json');
        if(res.ok) {
            locData = await res.json();
            const sDrop = document.getElementById('f_state');
            Object.keys(locData).forEach(s => {
                let o = document.createElement('option'); o.value = s; o.innerText = s;
                sDrop.appendChild(o);
            });
        }
    }

    function loadCities() {
        const state = document.getElementById('f_state').value;
        const cDrop = document.getElementById('f_city');
        cDrop.innerHTML = '<option value="">Select City</option>';
        if(state && locData[state]) {
            cDrop.disabled = false;
            Object.keys(locData[state]).forEach(c => {
                let o = document.createElement('option'); o.value = c; o.innerText = c;
                cDrop.appendChild(o);
            });
        } else { cDrop.disabled = true; }
        validateStep3();
    }

    // 校验逻辑
    function validateStep1() {
        const u = document.getElementById('f_user').value;
        const p1 = document.getElementById('f_pass').value;
        const p2 = document.getElementById('f_conf').value;
        const ok = (u.length >= 3 && p1.length >= 6 && p1 === p2);
        document.getElementById('btnN').disabled = !ok;
    }

    function validateStep2() {
        const r = document.getElementById('f_real').value;
        const b = document.getElementById('f_bday').value;
        const e = document.getElementById('f_mail').value;
        const ok = (r.length >= 3 && b !== "" && e.includes('@'));
        document.getElementById('btnN').disabled = !ok;
    }

    function validateStep3() {
        const a = document.getElementById('f_a1').value;
        const pc = document.getElementById('f_pcode').value;
        const s = document.getElementById('f_state').value;
        const c = document.getElementById('f_city').value;
        const agree = document.getElementById('f_agree').checked;
        const ok = (a.length >= 5 && pc.length >= 5 && s !== "" && c !== "" && agree);
        document.getElementById('btnN').disabled = !ok;
    }

    function nav(dir) {
        document.getElementById('pane-' + curStep).classList.remove('active');
        document.getElementById('dot-' + curStep).classList.remove('active');
        curStep += dir;
        document.getElementById('pane-' + curStep).classList.add('active');
        document.getElementById('dot-' + curStep).classList.add('active');

        document.getElementById('btnB').style.display = (curStep === 1) ? 'none' : 'block';
        const btnN = document.getElementById('btnN');
        
        if(curStep === 3) {
            btnN.innerText = "Complete Registration";
            btnN.onclick = () => document.getElementById('pxxRegForm').submit();
            validateStep3();
        } else {
            btnN.innerText = "Continue";
            btnN.onclick = () => nav(1);
            curStep === 1 ? validateStep1() : validateStep2();
        }
        document.getElementById('f_bar').style.width = (curStep * 33.33) + "%";
        window.scrollTo(0,0);
    }

    window.onload = initLocation;
</script>

<?php if (file_exists(PXX_FOOT)) { include_once PXX_FOOT; } ?>