<?php
/**
 * =================================================================================
 * PXX MALL - ADMINISTRATIVE & MEMBER REGISTRY PORTAL (ENTERPRISE EDITION V112.0)
 * =================================================================================
 * [DEVELOPMENT NOTES]:
 * - This engine is designed for high-security environments within the PXX Mall ecosystem.
 * - Minimum Code Requirement: 550+ Lines (Includes Security, Logic, UI, & DB).
 * - Core Functionality: AJAX-based Username Shield, Multi-Step Progress, Parallax UI.
 * - Location: Member Registration Module.
 * =================================================================================
 */

// --- [STAGE 1: SYSTEM CORE INITIALIZATION] ---
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// 设置大马时区，确保注册时间准确
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [STAGE 2: PHYSICAL PATH RESOLUTION] ---
// 智能路径识别，确保无论是独立运行还是在二级目录都能找到 includes
$pxx_current_dir = __DIR__;
$pxx_system_root = (basename($pxx_current_dir) === 'member') ? dirname($pxx_current_dir) : $pxx_current_dir;

define('PXX_DB_PATH', $pxx_system_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php');
define('PXX_HEAD_PATH', $pxx_system_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_head.php');
define('PXX_FOOT_PATH', $pxx_system_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_foot.php');

// 强制检查数据库连接
if (file_exists(PXX_DB_PATH)) { 
    require_once PXX_DB_PATH; 
} else { 
    die("PXX SYSTEM CRITICAL: Database connector missing. Registration engine cannot initialize."); 
}

// 变量名标准化 (PDO / mysqli 兼容性层)
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// --- [STAGE 3: AJAX ASYNC USERNAME SHIELD] ---
/**
 * 专门处理前端发来的实时查询请求
 * 如果 URL 中包含 check_user 参数，立即查询数据库并返回状态
 */
if (isset($_GET['check_user'])) {
    $requested_user = trim($_GET['check_user']);
    
    // 基础过滤
    if (empty($requested_user)) {
        echo "empty";
        exit();
    }

    try {
        $check_sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$requested_user]);
        $exists = $check_stmt->fetchColumn();
        
        // 返回纯文本状态供 JavaScript 解析
        if ($exists > 0) {
            echo "taken";
        } else {
            echo "available";
        }
    } catch (PDOException $e) {
        echo "error";
    }
    exit(); // AJAX 请求到此为止，不渲染后续 HTML
}

// --- [STAGE 4: AUTOMATED MEMBER IDENTIFICATION GENERATOR] ---
/**
 * 生成逻辑：以 'm' 开头，后面跟数字。
 * 如果数据库为空，从 m1060 开始。
 */
function pxx_id_generator($pdo) {
    try {
        $sql = "SELECT user_id FROM users WHERE user_id LIKE 'm%' ORDER BY CAST(SUBSTRING(user_id, 2) AS UNSIGNED) DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        $last_id = $stmt->fetchColumn();
        
        if (!$last_id) {
            return "m1060";
        }
        
        $numeric_part = (int)substr($last_id, 1);
        $new_numeric = ($numeric_part < 1060) ? 1060 : ($numeric_part + 1);
        
        return "m" . $new_numeric;
    } catch (Exception $e) {
        return "m" . rand(5000, 9999); // 备用方案，防止 ID 冲突导致注册失败
    }
}

// --- [STAGE 5: FINAL TRANSACTION CONTROLLER] ---
$pxx_master_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pxx_final_submit'])) {
    
    // 输入收集与清理
    $u_user  = trim($_POST['username'] ?? '');
    $u_pass  = $_POST['password'] ?? '';
    $u_real  = trim($_POST['real_name'] ?? '');
    $u_gend  = $_POST['gender'] ?? 'Male';
    $u_bday  = $_POST['birthday'] ?? '';
    $u_phone = trim($_POST['phone_num'] ?? '');
    $u_email = trim($_POST['email'] ?? '');
    
    // 物理地址三级合并
    $addr1 = trim($_POST['address_line1'] ?? '');
    $addr2 = trim($_POST['address_line2'] ?? '');
    $addr3 = trim($_POST['address_line3'] ?? '');
    $full_address = $addr1 . ($addr2 ? " | $addr2" : "") . ($addr3 ? " | $addr3" : "");
    
    $u_postcode = trim($_POST['postcode'] ?? '');
    $u_city     = trim($_POST['city'] ?? '');
    $u_state    = trim($_POST['state'] ?? '');
    $u_agreement = isset($_POST['tnc_check']) ? true : false;

    try {
        // [SECURITY SHIELD 1]: 协议检查
        if (!$u_agreement) {
            $pxx_master_error = "Safety Protocol: Terms and Conditions must be manually accepted.";
        } 
        // [SECURITY SHIELD 2]: 后端数据库查重（防止恶意跳过 AJAX）
        else {
            $final_check = $pdo->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
            $final_check->execute([$u_user]);
            
            if ($final_check->fetch()) {
                $pxx_master_error = "System Conflict: The username '<strong>$u_user</strong>' was claimed just moments ago. Please select another.";
            } else {
                // 执行数据库持久化
                $final_mid = pxx_id_generator($pdo);
                $password_hashed = password_hash($u_pass, PASSWORD_DEFAULT);
                $created_timestamp = date("Y-m-d H:i:s");

                $insert_sql = "INSERT INTO users (user_id, username, password_hashed, real_name, gender, birthday, phone_num, email, address, Postcode, City, State, created_at, status, role, points) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'Member', 100)";
                
                $final_stmt = $pdo->prepare($insert_sql);
                $is_registered = $final_stmt->execute([
                    $final_mid, $u_user, $password_hashed, $u_real, $u_gend, $u_bday, 
                    $u_phone, $u_email, $full_address, $u_postcode, $u_city, $u_state, $created_timestamp
                ]);

                if ($is_registered) {
                    $_SESSION['user_id'] = $final_mid;
                    $_SESSION['username'] = $u_user;
                    $_SESSION['role'] = 'Member';
                    echo "<script>alert('Account $final_mid created successfully! Welcome to PXX Mall.'); window.location.href='index.php';</script>";
                    exit();
                }
            }
        }
    } catch (PDOException $e) {
        $pxx_master_error = "Infrastructure Database Error: " . $e->getMessage();
    }
}

// 加载头部
if (file_exists(PXX_HEAD_PATH)) { include_once PXX_HEAD_PATH; }
?>

<style>
    /* ============================================================
       PXX MALL - MODERN VISUAL SYSTEM (V3.0)
       ============================================================ */
    :root { 
        --pxx-red: #e60012; 
        --pxx-red-hover: #cc0010;
        --pxx-error: #ff4d4f; 
        --pxx-success: #2ecc71;
        --pxx-glass: rgba(255, 255, 255, 0.98); 
    }

    body, html { 
        margin: 0; padding: 0; width: 100%; min-height: 100vh;
        background: transparent !important; 
        font-family: 'Poppins', 'Segoe UI', sans-serif;
    }

    .pxx-auth-viewport { 
        min-height: 100vh; width: 100%; display: flex; justify-content: center; align-items: center; 
        padding: 80px 0; background-image: url('../images/bg.jpeg'); 
        background-size: cover; background-position: center; background-attachment: fixed;
        position: relative; z-index: 1;
    }

    /* 遮罩层增强对比度 */
    .pxx-auth-viewport::after {
        content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.3); z-index: -1;
    }

    .pxx-wizard-card { 
        width: 1100px; background: var(--pxx-glass); border-radius: 40px; 
        box-shadow: 0 80px 250px rgba(0,0,0,0.9); overflow: hidden; 
        border-top: 25px solid var(--pxx-red); position: relative;
        animation: cardSpringEntry 1.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes cardSpringEntry { from { opacity: 0; transform: translateY(80px); } to { opacity: 1; transform: translateY(0); } }

    /* --- Stepper Navigation --- */
    .stepper-header { 
        display: flex; background: #fafafa; border-bottom: 2px solid #f0f0f0; 
        padding: 65px 0; justify-content: space-around; 
    }
    .step-item { 
        display: flex; align-items: center; gap: 20px; color: #ddd; 
        font-weight: 800; font-size: 15px; text-transform: uppercase; 
        letter-spacing: 2px; transition: all 0.5s;
    }
    .step-item.active { color: var(--pxx-red); }
    .step-dot { 
        width: 45px; height: 45px; border-radius: 50%; border: 3.5px solid currentColor; 
        display: flex; align-items: center; justify-content: center; 
        font-weight: 900; font-size: 18px;
    }

    /* --- Form Body Layout --- */
    .pxx-form-body { padding: 80px 140px; min-height: 550px; }
    .step-pane { display: none; }
    .step-pane.active { display: block; animation: paneSlideIn 0.8s ease; }
    @keyframes paneSlideIn { from { opacity: 0; transform: translateX(50px); } to { opacity: 1; transform: translateX(0); } }

    .pxx-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-bottom: 40px; }
    .pxx-field { display: flex; flex-direction: column; gap: 15px; position: relative; }
    .pxx-field label { font-size: 13px; font-weight: 900; color: #333; text-transform: uppercase; letter-spacing: 1.5px; }
    
    .pxx-input, .pxx-select { 
        width: 100%; padding: 24px; border: 2.5px solid #f2f2f2; 
        border-radius: 18px; font-size: 18px; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: #fff;
    }
    .pxx-input:focus, .pxx-select:focus { 
        border-color: var(--pxx-red); outline: none; 
        box-shadow: 0 0 0 10px rgba(230,0,18,0.1); 
    }

    /* 🔥 用户名警告样式 */
    .pxx-warning-hint {
        font-size: 14px; font-weight: 800; margin-top: 10px; padding: 5px 10px;
        border-radius: 8px; display: none;
    }
    .pxx-warning-hint.taken { color: #fff; background-color: var(--pxx-error); display: block; }
    .pxx-warning-hint.available { color: #fff; background-color: var(--pxx-success); display: block; }

    /* --- TnC Interface --- */
    .pxx-tnc-box { 
        margin-top: 50px; padding: 40px; background: #fafafa; 
        border-radius: 25px; border: 1px solid #f0f0f0; 
    }
    .tnc-scroll-viewport { 
        height: 180px; overflow-y: auto; background: #fff; 
        padding: 30px; font-size: 14px; color: #666; 
        border: 1px solid #eee; border-radius: 15px; margin-bottom: 35px;
        line-height: 1.8; text-align: justify;
    }
    .tnc-scroll-viewport::-webkit-scrollbar { width: 8px; }
    .tnc-scroll-viewport::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
    
    .pxx-checkbox-row { 
        display: flex; align-items: center; gap: 18px; 
        cursor: pointer; font-weight: 800; color: #222; 
    }
    .pxx-checkbox-row input { 
        width: 28px; height: 28px; accent-color: var(--pxx-red); cursor: pointer; 
    }

    /* --- Footer Actions --- */
    .pxx-footer { padding: 60px 140px; background: #fdfdfd; border-top: 2px solid #f5f5f5; }
    .btn-row { display: flex; gap: 40px; margin-bottom: 40px; }
    .pxx-btn { 
        padding: 26px 70px; border-radius: 20px; font-size: 20px; 
        font-weight: 900; cursor: pointer; transition: 0.4s; 
        text-transform: uppercase; border: none; letter-spacing: 2px;
    }
    .btn-next { background: var(--pxx-red); color: #fff; flex: 1; box-shadow: 0 15px 40px rgba(230,0,18,0.3); }
    .btn-next:hover:not(:disabled) { background: var(--pxx-red-hover); transform: translateY(-5px); }
    .btn-next:disabled { background: #dfdfdf; cursor: not-allowed; box-shadow: none; }
    .btn-back { background: #fff; color: #888; border: 3.5px solid #eee; }

    .prog-track { width: 100%; height: 14px; background: #f0f0f0; border-radius: 20px; overflow: hidden; }
    .prog-fill { 
        width: 33.33%; height: 100%; background: var(--pxx-red); 
        transition: width 0.9s cubic-bezier(0.65, 0, 0.35, 1); 
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
            <?php if($pxx_master_error): ?>
                <div style="background:#fff2f0; padding:30px; border-radius:20px; color:#cf1322; font-weight:800; margin-bottom:50px; border-left:12px solid var(--pxx-error); box-shadow: 0 15px 35px rgba(255,0,0,0.1);">
                    <i class="fas fa-exclamation-triangle" style="margin-right:15px; font-size:22px;"></i> <?php echo $pxx_master_error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="pxxRegForm">
                <div class="step-pane active" id="pane-1">
                    <h2 style="font-size:38px; margin-bottom:50px; letter-spacing:-2px;">Login Credentials</h2>
                    <div class="pxx-field" style="margin-bottom:45px;">
                        <label>Choose a Unique Username</label>
                        <input type="text" name="username" id="f_user" class="pxx-input" placeholder="Desired username" required oninput="pxx_ajax_user_check()">
                        <div id="user_feedback" class="pxx-warning-hint"></div>
                    </div>
                    <div class="pxx-grid">
                        <div class="pxx-field">
                            <label>Secure Password</label>
                            <input type="password" name="password" id="f_pass" class="pxx-input" placeholder="Minimum 6 chars" required oninput="validateStep1()">
                        </div>
                        <div class="pxx-field">
                            <label>Confirm Password</label>
                            <input type="password" id="f_conf" class="pxx-input" placeholder="Repeat password" required oninput="validateStep1()">
                        </div>
                    </div>
                    <p style="margin-top:20px; color:#aaa; font-size:13px; font-weight:600;">* Our security tunnel requires passwords to be robust and usernames to be unique.</p>
                </div>

                <div class="step-pane" id="pane-2">
                    <h2 style="font-size:38px; margin-bottom:50px; letter-spacing:-2px;">Member Profile</h2>
                    <div class="pxx-grid">
                        <div class="pxx-field"><label>Full Name</label><input type="text" name="real_name" id="f_real" class="pxx-input" placeholder="As per Identity Card" oninput="validateStep2()"></div>
                        <div class="pxx-field">
                            <label>Gender Selection</label>
                            <select name="gender" class="pxx-select"><option value="Male">Male</option><option value="Female">Female</option></select>
                        </div>
                    </div>
                    <div class="pxx-grid">
                        <div class="pxx-field"><label>Date of Birth</label><input type="date" name="birthday" id="f_bday" class="pxx-input" onchange="validateStep2()"></div>
                        <div class="pxx-field"><label>Mobile Phone</label><input type="text" name="phone_num" id="f_phone" class="pxx-input" placeholder="e.g., 012-3456789" oninput="validateStep2()"></div>
                    </div>
                    <div class="pxx-field">
                        <label>Active Email Address</label>
                        <input type="email" name="email" id="f_mail" class="pxx-input" placeholder="username@example.com" oninput="validateStep2()">
                    </div>
                </div>

                <div class="step-pane" id="pane-3">
                    <h2 style="font-size:38px; margin-bottom:50px; letter-spacing:-2px;">Logistics & Legal</h2>
                    <div class="pxx-field" style="margin-bottom:30px;"><label>Street Address Line</label><input type="text" name="address_line1" id="f_a1" class="pxx-input" placeholder="House/Unit No. & Street" oninput="validateStep3()"></div>
                    
                    <div class="pxx-grid">
                        <div class="pxx-field"><label>Postal Code</label><input type="text" name="postcode" id="f_pcode" class="pxx-input" placeholder="53300" oninput="validateStep3()"></div>
                        <div class="pxx-field">
                            <label>State Region</label>
                            <select name="state" id="f_state" class="pxx-select" onchange="loadCities()"><option value="">Select Region</option></select>
                        </div>
                    </div>
                    <div class="pxx-field">
                        <label>City / District</label>
                        <select name="city" id="f_city" class="pxx-select" disabled onchange="validateStep3()"><option value="">Select District</option></select>
                    </div>

                    <div class="pxx-tnc-box">
                        <label style="font-size:12px; font-weight:900; color:#111;">PXX MALL TERMS OF USE & DATA PROTECTION</label>
                        <div class="tnc-scroll-viewport">
                            <strong>1. ACADEMIC INTEGRITY:</strong> PXX Mall® is a specialized academic simulation project. All data entered is used solely for the purpose of demonstrating technical competence. No real-world commerce or delivery will take place.<br><br>
                            <strong>2. DATA PRIVACY PROTECTION:</strong> We are committed to protecting your privacy in accordance with Malaysia's Personal Data Protection Act (PDPA). Your information will never be sold, leased, or distributed to outside organizations.<br><br>
                            <strong>3. SECURITY PROTOCOLS:</strong> Members are responsible for maintaining the confidentiality of their passwords. We recommend using unique credentials to prevent unauthorized access to your student profile.<br><br>
                            <strong>4. CONTENT OWNERSHIP:</strong> Any apparel photography used in this portal is for illustrative educational purposes and remains the property of the respective copyright holders.
                        </div>
                        <label class="pxx-checkbox-row">
                            <input type="checkbox" name="tnc_check" id="f_agree" onchange="validateStep3()">
                            I solemnly swear that I have read and agree to all terms above.
                        </label>
                    </div>

                    <input type="hidden" name="pxx_final_submit" value="1">
                </div>
            </form>
        </div>

        <div class="pxx-footer">
            <div class="btn-row">
                <button type="button" id="btnB" class="pxx-btn btn-back" style="display:none;" onclick="pxx_navigate(-1)">Previous Phase</button>
                <button type="button" id="btnN" class="pxx-btn btn-next" onclick="pxx_navigate(1)" disabled>Continue Phase</button>
            </div>
            <div class="prog-track"><div class="prog-fill" id="f_bar"></div></div>
        </div>
    </div>
</div>

<script>
    /**
     * ============================================================
     * PXX MALL - ADMINISTRATIVE JS ENGINE (V112.0)
     * ============================================================
     */
    let pxx_current_step = 1;
    let pxx_loc_cache = {};
    let pxx_user_valid = false; // 实时用户名可用性标志

    // 生日限制逻辑：禁止未来日期
    const birthField = document.getElementById('f_bday');
    if(birthField) {
        birthField.max = new Date().toISOString().split('T')[0];
    }

    /**
     * 🔥 AJAX 核心：实时检查用户名是否存在
     * 逻辑：如果用户名存在，立即锁定按钮并显示红色警告。
     */
    async function pxx_ajax_user_check() {
        const userInput = document.getElementById('f_user');
        const feedback = document.getElementById('user_feedback');
        const username = userInput.value.trim();
        
        // 长度不足时不检查
        if (username.length < 3) {
            feedback.style.display = "none";
            pxx_user_valid = false;
            validateStep1();
            return;
        }

        try {
            // 通过 Fetch API 发送异步请求
            const response = await fetch(`register.php?check_user=${encodeURIComponent(username)}`);
            const status = await response.text();

            if (status === "taken") {
                feedback.innerText = "❌ USERNAME ALREADY EXISTS! ACCESS BLOCKED.";
                feedback.className = "pxx-warning-hint taken";
                pxx_user_valid = false; // 锁定
            } else if (status === "available") {
                feedback.innerText = "✅ USERNAME AVAILABLE. ACCESS GRANTED.";
                feedback.className = "pxx-warning-hint available";
                pxx_user_valid = true; // 通行
            } else {
                feedback.style.display = "none";
                pxx_user_valid = false;
            }
        } catch (error) {
            console.error("PXX AJAX Engine Error:", error);
        }
        
        // 每次检查完都刷新 Step 1 的按钮状态
        validateStep1();
    }

    /**
     * 第一阶段校验
     */
    function validateStep1() {
        const u = document.getElementById('f_user').value.trim();
        const p1 = document.getElementById('f_pass').value;
        const p2 = document.getElementById('f_conf').value;
        
        // 🔥 核心判定：必须用户名可用 && 密码匹配 && 密码长度 >= 6
        const isStep1Ok = (pxx_user_valid && u.length >= 3 && p1.length >= 6 && p1 === p2);
        
        const nextBtn = document.getElementById('btnN');
        if (pxx_current_step === 1) {
            nextBtn.disabled = !isStep1Ok;
        }
    }

    /**
     * 第二阶段校验
     */
    function validateStep2() {
        const name = document.getElementById('f_real').value.trim();
        const bday = document.getElementById('f_bday').value;
        const phone = document.getElementById('f_phone').value.trim();
        const email = document.getElementById('f_mail').value.trim();
        
        const isStep2Ok = (name.length >= 3 && bday !== "" && phone.length >= 9 && email.includes('@'));
        
        if (pxx_current_step === 2) {
            document.getElementById('btnN').disabled = !isStep2Ok;
        }
    }

    /**
     * 第三阶段校验
     */
    function validateStep3() {
        const addr = document.getElementById('f_a1').value.trim();
        const pcode = document.getElementById('f_pcode').value.trim();
        const state = document.getElementById('f_state').value;
        const city = document.getElementById('f_city').value;
        const agreed = document.getElementById('f_agree').checked;
        
        const isStep3Ok = (addr.length >= 8 && pcode.length >= 5 && state !== "" && city !== "" && agreed);
        
        if (pxx_current_step === 3) {
            document.getElementById('btnN').disabled = !isStep3Ok;
        }
    }

    /**
     * 地理数据加载
     */
    async function pxx_init_locations() {
        const possiblePaths = ['../includes/state.json', 'includes/state.json'];
        for (let path of possiblePaths) {
            try {
                const res = await fetch(path);
                if(res.ok) {
                    pxx_loc_cache = await res.json();
                    const stateSelect = document.getElementById('f_state');
                    Object.keys(pxx_loc_cache).forEach(s => {
                        let opt = document.createElement('option');
                        opt.value = s; opt.innerText = s;
                        stateSelect.appendChild(opt);
                    });
                    break;
                }
            } catch (e) {}
        }
    }

    /**
     * 联动加载城市
     */
    function loadCities() {
        const selectedState = document.getElementById('f_state').value;
        const citySelect = document.getElementById('f_city');
        citySelect.innerHTML = '<option value="">Select District</option>';
        
        if(selectedState && pxx_loc_cache[selectedState]) {
            citySelect.disabled = false;
            Object.keys(pxx_loc_cache[selectedState]).forEach(c => {
                let opt = document.createElement('option');
                opt.value = c; opt.innerText = c;
                citySelect.appendChild(opt);
            });
        } else {
            citySelect.disabled = true;
        }
        validateStep3();
    }

    /**
     * 页面导航逻辑
     */
    function pxx_navigate(direction) {
        // 移除当前活跃状态
        document.getElementById('pane-' + pxx_current_step).classList.remove('active');
        document.getElementById('dot-' + pxx_current_step).classList.remove('active');
        
        // 更新步数
        pxx_current_step += direction;
        
        // 激活新状态
        document.getElementById('pane-' + pxx_current_step).classList.add('active');
        document.getElementById('dot-' + pxx_current_step).classList.add('active');
        
        // 控制返回按钮显示
        document.getElementById('btnB').style.display = (pxx_current_step === 1) ? 'none' : 'block';
        
        const nextButton = document.getElementById('btnN');
        if(pxx_current_step === 3) {
            nextButton.innerText = "Initialize PXX Membership";
            nextButton.onclick = () => {
                if(!nextButton.disabled) {
                    document.getElementById('pxxRegForm').submit();
                }
            };
            validateStep3();
        } else {
            nextButton.innerText = "Continue Phase";
            nextButton.onclick = () => pxx_navigate(1);
            if(pxx_current_step === 1) validateStep1();
            else validateStep2();
        }
        // 更新进度条
        document.getElementById('f_bar').style.width = (pxx_current_step * 33.33) + "%";
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // 页面加载初始化
    window.onload = pxx_init_locations;
</script>

<?php
// 加载页脚
if (file_exists(PXX_FOOT_PATH)) { include_once PXX_FOOT_PATH; }
?>