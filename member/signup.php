<?php
/**
 * =================================================================================
 * PXX MALL - PROFESSIONAL MULTI-STEP REGISTRY (ENTERPRISE STABLE)
 * =================================================================================
 * 1. Security Phase: Username & Password (Length & Match)
 * 2. Profile Phase: Personal Info, Email Regex, Birthday Lock
 * 3. Logistics Phase: Triple Address, JSON Dynamic States/Cities
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// [SECTION 1: 物理路径引擎 - 解决各种环境下的加载报错]
$pxx_path = __DIR__;
$pxx_root = (basename($pxx_path) === 'member') ? dirname($pxx_path) : $pxx_path;

define('PXX_DB', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php');
define('PXX_HEAD', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_head.php');
define('PXX_FOOT', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_foot.php');

if (file_exists(PXX_DB)) { require_once PXX_DB; } else { die("FATAL ERROR: DB CONNECTOR MISSING."); }
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

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
    // 基础资料
    $u_user = trim($_POST['username'] ?? '');
    $u_pass = $_POST['password'] ?? '';
    $u_real = trim($_POST['real_name'] ?? '');
    $u_gend = $_POST['gender'] ?? 'Male';
    $u_bday = $_POST['birthday'] ?? '';
    $u_phone = trim($_POST['phone_num'] ?? '');
    $u_email = trim($_POST['email'] ?? '');
    
    // 地址物理合并逻辑
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
    :root { 
        --pxx-red: #e60012; 
        --pxx-error: #ff4d4f; 
        --pxx-glass: rgba(255, 255, 255, 0.98); 
    }

    body { 
        font-family: 'Inter', system-ui, sans-serif; 
        margin: 0; 
        background: #f0f2f5; 
        overflow-x: hidden; /* 电脑端防止细微溢出 */
    }

    /* =================================================================================
   PXX MALL - ULTIMATE BACKGROUND REPAIR (DESKTOP ONLY)
   逻辑：彻底移除干扰底色，强制图片视差置顶
   ================================================================================= */

/* 🔥 修复重点 1：彻底清理底色干扰（必须使用 !important 垂直展开优先级） */
:root { 
    --pxx-red: #e60012; 
    --pxx-error: #ff4d4f; 
    --pxx-glass: rgba(255, 255, 255, 1); /* 既然背景图出了，卡片必须纯白，全不透明 */
}

html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    min-height: 100vh;
    /* 彻底消灭 #f0f2f5 灰色，改为透明 */
    background: transparent !important; 
    background-color: transparent !important;
}

/* 🔥 修复重点 2：路径修正 & 电脑端视差参数 */
.pxx-auth-viewport { 
    min-height: 100vh;
    width: 100vw; /* 锁定视口宽度 */
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 0;
    
    /* 物理路径：../ 代表退出 member 文件夹，进入根目录的 images */
    background-image: url('../images/bg.jpeg'); 
    
    /* 极简电脑端参数：铺满、居中、固定 */
    background-size: cover;          /* 强制铺满全屏，不留白 */
    background-position: center center; /* 图片居中对齐 */
    background-attachment: fixed;    /* 滚动时背景固定，产生高级视差效果 */
    background-repeat: no-repeat;    /* 禁止图片重复 */
    
    /* 再次加固：确保 viewport 容器本身没有底色干扰 */
    background-color: transparent !important; 
}

/* 🔥 修复重点 3：增强卡片对比度（既然去掉了背景遮罩） */
.pxx-wizard-card {
    width: 1100px; /* 电脑端固定宽度 */
    background: var(--pxx-glass) !important;
    border-radius: 24px;
    /* 加深阴影，让卡片在复杂的背景图上跳出来 */
    box-shadow: 0 50px 180px rgba(0,0,0,0.8) !important; 
    overflow: hidden;
    border-top: 15px solid var(--pxx-red);
    position: relative;
    z-index: 10; /* 确保卡片在图片上面 */
    animation: cardAppear 1s cubic-bezier(0.2, 0.8, 0.2, 1);
}

@keyframes cardAppear { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

    /* --- [注册卡片 - 1100px 稳定布局] --- */
    .pxx-wizard-card { 
        width: 1100px; /* 电脑端直接固定宽度，不抖动 */
        background: var(--pxx-glass); 
        border-radius: 24px; 
        box-shadow: 0 50px 150px rgba(0,0,0,0.6); 
        overflow: hidden; 
        border-top: 15px solid var(--pxx-red);
        animation: cardFadeIn 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
        position: relative;
    }

    @keyframes cardFadeIn { 
        from { opacity: 0; transform: translateY(50px); } 
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

    /* 底部操作区 */
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

    /* 进度条 */
    .prog-track { width: 100%; height: 8px; background: #eee; border-radius: 10px; overflow: hidden; }
    .prog-fill { 
        width: 33.33%; 
        height: 100%; 
        background: var(--pxx-red); 
        transition: width 0.8s cubic-bezier(0.65, 0, 0.35, 1); 
    }
</style>
<div class="pxx-viewport">
    <div class="pxx-wizard-card">
        
        <div class="stepper-header">
            <div class="step-item active" id="dot-1"><div class="step-dot">1</div> SECURITY</div>
            <div class="step-item" id="dot-2"><div class="step-dot">2</div> PERSONAL</div>
            <div class="step-item" id="dot-3"><div class="step-dot">3</div> LOGISTICS</div>
        </div>

        <div class="pxx-form-body">
            <?php if($pxx_server_error): ?><div style="background:#fff1f0; padding:20px; border-radius:10px; color:#cf1322; font-weight:700; margin-bottom:40px; border-left:5px solid var(--pxx-error);"><?php echo $pxx_server_error; ?></div><?php endif; ?>

            <form action="" method="POST" id="pxxRegForm">
                
                <div class="step-pane active" id="pane-1">
                    <h2 style="font-size:32px; margin-bottom:45px; letter-spacing:-1.5px;">Account Credentials</h2>
                    <div class="pxx-field" style="margin-bottom:35px;">
                        <label>Unique Username</label>
                        <input type="text" name="username" id="f_user" class="pxx-input" placeholder="Set login handle" required>
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
                        <div class="pxx-fld"><label>Birthday (Max Today)</label><input type="date" name="birthday" id="f_bday" class="pxx-input" onchange="validateStep2()"></div>
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
    let locData = {}; // 用于存储完整的 JSON 数据

    // 1. 初始化：日期限制
    const bdayInput = document.getElementById('f_bday');
    if(bdayInput) bdayInput.max = new Date().toISOString().split('T')[0];

    /**
     * 🔥 核心修复 1：适配嵌套 JSON 结构
     */
    async function initLocationData() {
        // 尝试加载路径
        const paths = ['../includes/state.json', 'includes/state.json', '/HTML_Ass/includes/state.json'];
        
        for (let path of paths) {
            try {
                const res = await fetch(path);
                if (res.ok) {
                    locData = await res.json();
                    console.log("✅ PXX Success: JSON Data Loaded from " + path);
                    renderStates();
                    return;
                }
            } catch (e) { console.warn("PXX Notice: Trying next path..."); }
        }
        console.error("🚨 PXX Critical: Failed to load location data.");
    }

    function renderStates() {
        const sDrop = document.getElementById('f_state');
        if(!sDrop) return;
        sDrop.innerHTML = '<option value="">Select State</option>';
        
        // 获取 State 名字 (如 Johor, Selangor...)
        Object.keys(locData).forEach(stateName => {
            let opt = document.createElement('option');
            opt.value = stateName;
            opt.innerText = stateName;
            sDrop.appendChild(opt);
        });
    }

    /**
     * 🔥 核心修复 2：联动加载城市
     * 适配结构：locData[state][cityName]
     */
    function loadCities() {
        const selectedState = document.getElementById('f_state').value;
        const cDrop = document.getElementById('f_city');
        const pInput = document.getElementById('f_pcode'); // 获取邮编框，方便自动填充
        
        cDrop.innerHTML = '<option value="">Select City</option>';
        
        if (selectedState && locData[selectedState]) {
            cDrop.disabled = false;
            cDrop.style.background = "#fff";
            
            // 🔥 这里的关键：提取嵌套对象里的 Key 作为城市名
            const cities = Object.keys(locData[selectedState]); 
            
            cities.forEach(cityName => {
                let opt = document.createElement('option');
                opt.value = cityName;
                opt.innerText = cityName;
                cDrop.appendChild(opt);
            });
            console.log("✅ Cities rendered for: " + selectedState);
        } else {
            cDrop.disabled = true;
            cDrop.style.background = "#f9f9f9";
        }
        validateStep3();
    }

    /**
     * 🌟 附加功能：当选了城市，自动填入第一个对应的 Postcode
     */
    function handleCityChange() {
        const state = document.getElementById('f_state').value;
        const city = document.getElementById('f_city').value;
        const pInput = document.getElementById('f_pcode');

        if (state && city && locData[state][city]) {
            // 获取该城市对应的邮编列表的第一个
            const postcodes = locData[state][city];
            if (postcodes.length > 0) {
                pInput.value = postcodes[0]; 
            }
        }
        validateStep3();
    }

    // --- [校验逻辑 - 保持原有专业性] ---
    function validateEmail(e) {
        return /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(e);
    }

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
        const isEmailOk = validateEmail(e);
        const isValid = (r.length > 2 && b && p.length >= 9 && isEmailOk);
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
            btnN.innerHTML = "Next Phase <i class='fas fa-chevron-right'></i>";
            btnN.onclick = () => nav(1);
        }
        document.getElementById('f_bar').style.width = (curStep * 33.33) + "%";
        window.scrollTo(0,0);
    }

    window.onload = initLocationData;
</script>
<?php if (file_exists(PXX_FOOT)) { include_once PXX_FOOT; } ?>