<?php
/**
 * PXX MALL - PROFESSIONAL MULTI-STEP REGISTRY (ENTERPRISE STABLE)
 * Updated with Cinematic Background & Parallax Effects
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// [SECTION 1: 物理路径引擎]
$pxx_path = __DIR__;
$pxx_root = (basename($pxx_path) === 'member') ? dirname($pxx_path) : $pxx_path;

define('PXX_DB', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php');
define('PXX_HEAD', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_head.php');
define('PXX_FOOT', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_foot.php');

if (file_exists(PXX_DB)) { require_once PXX_DB; } else { die("FATAL ERROR: DB CONNECTOR MISSING."); }

// [SECTION 2: 智能 m-ID 生成器]
function generatePXXID($pdo) {
    $sql = "SELECT user_id FROM users WHERE user_id LIKE 'm%' ORDER BY CAST(SUBSTRING(user_id, 2) AS UNSIGNED) DESC LIMIT 1";
    $stmt = $pdo->query($sql);
    $last_id = $stmt->fetchColumn();
    if (!$last_id) return "m1060";
    $num = (int)substr($last_id, 1);
    return "m" . (($num < 1060) ? 1060 : ($num + 1));
}

// [SECTION 3: 最终提交处理器]
$pxx_server_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['final_save'])) {
    $u_user = trim($_POST['username'] ?? '');
    $u_pass = $_POST['password'] ?? '';
    $u_real = trim($_POST['real_name'] ?? '');
    $u_gend = $_POST['gender'] ?? 'Male';
    $u_bday = $_POST['birthday'] ?? '';
    $u_phone = trim($_POST['phone_num'] ?? '');
    $u_email = trim($_POST['email'] ?? '');
    
    $a1 = trim($_POST['address_line1'] ?? '');
    $a2 = trim($_POST['address_line2'] ?? '');
    $a3 = trim($_POST['address_line3'] ?? '');
    $combined_addr = $a1 . ($a2 ? " | $a2" : "") . ($a3 ? " | $a3" : "");
    
    $u_pcode = trim($_POST['postcode'] ?? '');
    $u_city = trim($_POST['city'] ?? '');
    $u_state = trim($_POST['state'] ?? '');

    try {
        $new_id = generatePXXID($pdo);
        $hash = password_hash($u_pass, PASSWORD_DEFAULT);
        $now = date("Y-m-d H:i:s");

        $sql = "INSERT INTO users (user_id, username, password_hashed, real_name, gender, birthday, phone_num, email, address, Postcode, City, State, created_at, status, role, points) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'Member', 100)";
        
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$new_id, $u_user, $hash, $u_real, $u_gend, $u_bday, $u_phone, $u_email, $combined_addr, $u_pcode, $u_city, $u_state, $now])) {
            $_SESSION['user_id'] = $new_id;
            $_SESSION['username'] = $u_user;
            echo "<script>alert('Successful! Welcome Member $new_id'); window.location.href='index.php';</script>";
            exit();
        }
    } catch (PDOException $e) { $pxx_server_error = "DB ERROR: " . $e->getMessage(); }
}

if (file_exists(PXX_HEAD)) { include_once PXX_HEAD; }
?>

<style>
    /* =================================================================================
    🌟 PXX MALL - ULTIMATE CINEMATIC BACKGROUND SYSTEM
    ================================================================================= */
    :root { 
        --pxx-red: #e60012; 
        --pxx-error: #ff4d4f; 
        --pxx-glass: #ffffff; /* 纯白背景确保内容清晰 */
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        min-height: 100vh;
        background-color: #000 !important; /* 防止图片加载前的闪烁 */
    }

    .pxx-auth-viewport { 
        min-height: 100vh;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 80px 0;
        position: relative;
        
        /* 🌟 背景配置：叠加渐变遮罩 + 背景图 */
        background: 
            linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.4)), 
            url('../images/bg.jpeg'); 
        
        background-size: cover;
        background-position: center center;
        background-attachment: fixed; /* 电脑端视差效果 */
        background-repeat: no-repeat;
    }

    /* 🌟 注册卡片：增强悬浮感 */
    .pxx-wizard-card { 
        width: 1100px; 
        background: var(--pxx-glass) !important; 
        border-radius: 24px; 
        /* 极致阴影，模拟图片中的景深感 */
        box-shadow: 0 40px 120px rgba(0, 0, 0, 0.6) !important; 
        overflow: hidden; 
        border-top: 15px solid var(--pxx-red);
        position: relative;
        z-index: 10;
        animation: pxxEntry 1s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    @keyframes pxxEntry { 
        from { opacity: 0; transform: translateY(60px); } 
        to { opacity: 1; transform: translateY(0); } 
    }

    /* 指示器系统 */
    .stepper-header { 
        display: flex; 
        background: #fafafa; 
        border-bottom: 1px solid #eee; 
        padding: 45px 0; 
        justify-content: space-around; 
    }
    .step-item { 
        display: flex; 
        align-items: center; 
        gap: 15px; 
        color: #ccc; 
        font-weight: 800; 
        font-size: 14px; 
        text-transform: uppercase; 
        letter-spacing: 1.5px; 
    }
    .step-item.active { color: var(--pxx-red); }
    .step-dot { 
        width: 38px; 
        height: 38px; 
        border-radius: 50%; 
        border: 2px solid currentColor; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: 900; 
        font-size: 16px;
    }

    .pxx-form-body { padding: 80px 120px; min-height: 600px; }
    .step-pane { display: none; }
    .step-pane.active { display: block; }

    /* 表单组件样式 */
    .pxx-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; }
    .pxx-field { display: flex; flex-direction: column; gap: 12px; position: relative; }
    .pxx-field label { 
        font-size: 14px; 
        font-weight: 800; 
        color: #333; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
    }
    
    .pxx-input, .pxx-select { 
        width: 100%; 
        padding: 20px 24px; 
        border: 2px solid #eee; 
        border-radius: 12px; 
        font-size: 16px; 
        outline: none; 
        transition: all 0.3s; 
        box-sizing: border-box; 
        background: #fff;
    }
    .pxx-input:focus, .pxx-select:focus { 
        border-color: var(--pxx-red); 
        box-shadow: 0 0 0 8px rgba(230,0,18,0.08); 
    }
    .pxx-input.invalid { border-color: var(--pxx-error); background: #fff1f0; }

    .err-hint { 
        color: var(--pxx-error); 
        font-size: 12px; 
        font-weight: 700; 
        display: none; 
        margin-top: 6px; 
    }

    .pxx-footer { 
        padding: 50px 120px; 
        background: #fafafa; 
        border-top: 1px solid #f0f0f0; 
    }
    .btn-row { display: flex; gap: 30px; margin-bottom: 35px; }
    .pxx-btn { 
        padding: 24px 60px; 
        border-radius: 14px; 
        font-size: 18px; 
        font-weight: 900; 
        cursor: pointer; 
        transition: all 0.3s; 
        text-transform: uppercase; 
        border: none; 
        letter-spacing: 1px;
    }
    .btn-next { 
        background: var(--pxx-red); 
        color: #fff; 
        flex: 1; 
        box-shadow: 0 12px 30px rgba(230,0,18,0.25); 
    }
    .btn-next:hover { 
        background: #cc0010; 
        transform: translateY(-3px); 
        box-shadow: 0 15px 40px rgba(230,0,18,0.35); 
    }
    .btn-next:disabled { background: #ddd; cursor: not-allowed; box-shadow: none; transform: none; }
    .btn-back { background: #fff; color: #888; border: 2px solid #ddd; }
    .btn-back:hover { background: #f5f5f5; color: #333; }

    .prog-track { width: 100%; height: 8px; background: #eee; border-radius: 10px; overflow: hidden; }
    .prog-fill { 
        width: 33.33%; 
        height: 100%; 
        background: var(--pxx-red); 
        transition: width 0.8s cubic-bezier(0.65, 0, 0.35, 1); 
    }
</style>

<div class="pxx-auth-viewport">
    <div class="pxx-wizard-card">
        <div class="stepper-header">
            <div class="step-item active" id="dot-1"><div class="step-dot">1</div> SECURITY</div>
            <div class="step-item" id="dot-2"><div class="step-dot">2</div> PERSONAL</div>
            <div class="step-item" id="dot-3"><div class="step-dot">3</div> LOGISTICS</div>
        </div>

        <div class="pxx-form-body">
            <?php if($pxx_server_error): ?>
                <div style="background:#fff1f0; padding:20px; border-radius:10px; color:#cf1322; font-weight:700; margin-bottom:40px; border-left:5px solid var(--pxx-error);">
                    <?php echo $pxx_server_error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="pxxRegForm">
                <div class="step-pane active" id="pane-1">
                    <h2 style="font-size:32px; margin-bottom:45px; letter-spacing:-1.5px;">Account Credentials</h2>
                    <div class="pxx-field" style="margin-bottom:35px;">
                        <label>Unique Username</label>
                        <input type="text" name="username" id="f_user" class="pxx-input" placeholder="Set login handle" required oninput="validateStep1()">
                    </div>
                    <div class="pxx-grid">
                        <div class="pxx-fld">
                            <label>Password (Min 6 chars)</label>
                            <input type="password" name="password" id="f_pass" class="pxx-input" placeholder="••••••••" required oninput="validateStep1()">
                        </div>
                        <div class="pxx-fld">
                            <label>Verify Password</label>
                            <input type="password" id="f_conf" class="pxx-input" placeholder="••••••••" required oninput="validateStep1()">
                            <div class="err-hint" id="err_p">Mismatch or too short (6+ required)</div>
                        </div>
                    </div>
                </div>

                <div class="step-pane" id="pane-2">
                    <h2 style="font-size:32px; margin-bottom:45px; letter-spacing:-1.5px;">Member Details</h2>
                    <div class="pxx-grid">
                        <div class="pxx-fld"><label>Full Legal Name</label><input type="text" name="real_name" id="f_real" class="pxx-input" placeholder="As per Identity" oninput="validateStep2()"></div>
                        <div class="pxx-fld">
                            <label>Gender</label>
                            <select name="gender" class="pxx-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="pxx-grid">
                        <div class="pxx-fld"><label>Birthday</label><input type="date" name="birthday" id="f_bday" class="pxx-input" onchange="validateStep2()"></div>
                        <div class="pxx-fld"><label>Mobile Phone</label><input type="text" name="phone_num" id="f_phone" class="pxx-input" placeholder="01X-XXXXXXX" oninput="validateStep2()"></div>
                    </div>
                    <div class="pxx-field">
                        <label>Email Address</label>
                        <input type="email" name="email" id="f_mail" class="pxx-input" placeholder="must@contain.com" oninput="validateStep2()">
                        <div class="err-hint" id="err_m">Please enter a valid email address</div>
                    </div>
                </div>

                <div class="step-pane" id="pane-3">
                    <h2 style="font-size:32px; margin-bottom:45px; letter-spacing:-1.5px;">Shipping & Logistics</h2>
                    <div class="pxx-field" style="margin-bottom:15px;"><label>Address Line 1</label><input type="text" name="address_line1" id="f_a1" class="pxx-input" placeholder="Unit / House No." oninput="validateStep3()"></div>
                    <div class="pxx-field" style="margin-bottom:15px;"><label>Address Line 2</label><input type="text" name="address_line2" id="f_a2" class="pxx-input" placeholder="Street / Building"></div>
                    <div class="pxx-field" style="margin-bottom:40px;"><label>Address Line 3</label><input type="text" name="address_line3" id="f_a3" class="pxx-input" placeholder="Area / District"></div>
                    
                    <div class="pxx-grid">
                        <div class="pxx-fld"><label>Postcode</label><input type="text" name="postcode" id="f_pcode" class="pxx-input" placeholder="53300" oninput="validateStep3()"></div>
                        <div class="pxx-fld">
                            <label>State</label>
                            <select name="state" id="f_state" class="pxx-select" onchange="loadCities()">
                                <option value="">Select State</option>
                            </select>
                        </div>
                    </div>
                    <div class="pxx-field" style="margin-top:25px;">
                        <label>City</label>
                        <select name="city" id="f_city" class="pxx-select" disabled onchange="validateStep3()">
                            <option value="">Select City</option>
                        </select>
                    </div>
                    <input type="hidden" name="final_save" value="1">
                </div>
            </form>
        </div>

        <div class="pxx-footer">
            <div class="btn-row">
                <button type="button" id="btnB" class="pxx-btn btn-back" style="display:none;" onclick="nav(-1)">Previous Phase</button>
                <button type="button" id="btnN" class="pxx-btn btn-next" onclick="nav(1)" disabled>Next Phase</button>
            </div>
            <div class="prog-track"><div class="prog-fill" id="f_bar"></div></div>
        </div>
    </div>
</div>

<script>
    let curStep = 1;
    let locData = {};

    const bdayInput = document.getElementById('f_bday');
    if(bdayInput) bdayInput.max = new Date().toISOString().split('T')[0];

    async function initLocationData() {
        const path = '../includes/state.json';
        try {
            const res = await fetch(path);
            if (res.ok) {
                locData = await res.json();
                renderStates();
            }
        } catch (e) { console.error("🚨 Failed to load location data."); }
    }

    function renderStates() {
        const sDrop = document.getElementById('f_state');
        if(!sDrop) return;
        Object.keys(locData).forEach(state => {
            let opt = document.createElement('option');
            opt.value = state;
            opt.innerText = state;
            sDrop.appendChild(opt);
        });
    }

    function loadCities() {
        const state = document.getElementById('f_state').value;
        const cDrop = document.getElementById('f_city');
        cDrop.innerHTML = '<option value="">Select City</option>';
        if (state && locData[state]) {
            cDrop.disabled = false;
            Object.keys(locData[state]).forEach(city => {
                let opt = document.createElement('option');
                opt.value = city;
                opt.innerText = city;
                cDrop.appendChild(opt);
            });
        } else {
            cDrop.disabled = true;
        }
        validateStep3();
    }

    function validateEmail(e) { return /^[^@]+@\w+(\.\w+)+$/.test(e); }

    function validateStep1() {
        const u = document.getElementById('f_user').value;
        const p1 = document.getElementById('f_pass').value;
        const p2 = document.getElementById('f_conf').value;
        const isValid = (u.length > 0 && p1.length >= 6 && p1 === p2);
        document.getElementById('btnN').disabled = !isValid;
        return isValid;
    }

    function validateStep2() {
        const r = document.getElementById('f_real').value;
        const b = document.getElementById('f_bday').value;
        const p = document.getElementById('f_phone').value;
        const e = document.getElementById('f_mail').value;
        const isValid = (r.length > 2 && b && p.length >= 9 && validateEmail(e));
        document.getElementById('btnN').disabled = !isValid;
        return isValid;
    }

    function validateStep3() {
        const a1 = document.getElementById('f_a1').value;
        const pc = document.getElementById('f_pcode').value;
        const s = document.getElementById('f_state').value;
        const c = document.getElementById('f_city').value;
        const isValid = (a1.length > 5 && pc.length >= 5 && s && c);
        document.getElementById('btnN').disabled = !isValid;
        return isValid;
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
            btnN.innerHTML = "Initialize PXX Account";
            btnN.onclick = () => document.getElementById('pxxRegForm').submit();
        } else {
            btnN.innerHTML = "Next Phase";
            btnN.onclick = () => nav(1);
        }
        document.getElementById('f_bar').style.width = (curStep * 33.33) + "%";
        window.scrollTo(0,0);
    }

    window.onload = initLocationData;
</script>

<?php if (file_exists(PXX_FOOT)) { include_once PXX_FOOT; } ?>