<?php
/**
 * PXX MALL - COMPLETE REGISTRY
 * Features: English UI, 6-Char Password Security, AJAX Unique Check, T&C Modal
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// [SECTION 1: Database & Path Configuration]
$pxx_path = __DIR__;
$pxx_root = (basename($pxx_path) === 'member') ? dirname($pxx_path) : $pxx_path;
define('PXX_DB', $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php');

if (file_exists(PXX_DB)) { 
    require_once PXX_DB; 
} else { 
    die("FATAL ERROR: DB CONNECTOR MISSING AT " . PXX_DB); 
}

// Ensure $pdo is the connection variable
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// [SECTION 2: AJAX Username Check]
if (isset($_GET['check_user'])) {
    $requested_user = trim($_GET['check_user']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$requested_user]);
    echo ($stmt->fetchColumn() > 0) ? "taken" : "available";
    exit;
}

// [SECTION 3: m-ID Generator]
function generatePXXID($pdo) {
    $sql = "SELECT user_id FROM users WHERE user_id LIKE 'm%' ORDER BY CAST(SUBSTRING(user_id, 2) AS UNSIGNED) DESC LIMIT 1";
    $stmt = $pdo->query($sql);
    $last_id = $stmt->fetchColumn();
    if (!$last_id) return "m1060";
    $num = (int)substr($last_id, 1);
    return "m" . (($num < 1060) ? 1060 : ($num + 1));
}

// [SECTION 4: Form Processor]
$server_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['final_save'])) {
    $u_user = trim($_POST['username'] ?? '');
    $u_pass = $_POST['password'] ?? '';
    
    try {
        $new_id = generatePXXID($pdo);
        $hash = password_hash($u_pass, PASSWORD_DEFAULT);
        $now = date("Y-m-d H:i:s");

        $sql = "INSERT INTO users (user_id, username, password_hashed, created_at, status, role, points) 
                VALUES (?, ?, ?, ?, 'Active', 'Member', 100)";
        
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$new_id, $u_user, $hash, $now])) {
            $_SESSION['user_id'] = $new_id;
            $_SESSION['username'] = $u_user;
            
            // Welcoming user by their username
            echo "<script>alert('Successful! Welcome, $u_user'); window.location.href='../index.php';</script>";
            exit();
        }
    } catch (PDOException $e) { 
        $server_msg = "Database Error: " . $e->getMessage(); 
    }
}

// Include Header
if (file_exists('../includes/_head.php')) { include('../includes/_head.php'); }
?>

    <link rel="stylesheet" href="../css/app.css">



<div class="pxx-main-container">
    <div class="pxx-card">
        <h2 style="margin-top:0; text-align:center; letter-spacing:-1.5px;">Member Sign-up</h2>

        <form action="" method="POST" id="pxxRegForm">
            <div class="pxx-field">
                <label>Username</label>
                <input type="text" name="username" id="f_user" class="pxx-input" placeholder="Create a username" required>
                <div id="user-feedback" class="msg-hint"></div>
            </div>

            <div class="pxx-field">
                <label>Password</label>
                <input type="password" name="password" id="f_pass" class="pxx-input" placeholder="Create password" required oninput="validateSecurity()">
                <div id="pass-len-msg" class="msg-hint err-text">Password must be at least 6 characters.</div>
            </div>

            <div class="pxx-field">
                <label>Confirm Password</label>
                <input type="password" id="f_conf" class="pxx-input" placeholder="Confirm password" required oninput="validateSecurity()">
                <div id="pass-match-msg" class="msg-hint err-text">Passwords do not match.</div>
            </div>

            <div style="font-size: 13px; margin: 20px 0; display: flex; align-items: flex-start; gap: 10px; color: #555;">
                <input type="checkbox" id="f_tnc" disabled style="transform: scale(1.2);">
                <span>I Agree to <span class="tnc-link" onclick="openTnC()">Terms & Conditions</span></span>
            </div>

            <input type="hidden" name="final_save" value="1">
            <button type="submit" id="btnS" class="btn-submit" disabled>Register Account</button>
        </form>
    </div>
</div>

<div id="tncModal" class="pxx-modal">
    <div class="modal-content">
        <h2 style="color:var(--pxx-red); margin-top:0;">Terms & Conditions</h2>
        <div class="tnc-scroll">
            <h3 style="color:#333;">1. Data Privacy (PDPA)</h3>
            <p>Your privacy is our priority. We strictly collect only necessary information to process your orders. Your data will never be sold to third-party advertisers.</p>
            <h3 style="color:#333;">2. User Security</h3>
            <p>You are responsible for maintaining the confidentiality of your password. Use a strong password to prevent unauthorized access.</p>
            <h3 style="color:#333;">3. Member Benefits</h3>
            <p>By joining, you receive 100 initial points and access to exclusive PXX Mall promotions.</p>
        </div>
        <button type="button" class="btn-submit" onclick="acceptTnC()">Accept & Continue</button>
    </div>
</div>

<script>
    let isUserValid = false;
    let isPassValid = false;

    // Modal Controls
    function openTnC() { document.getElementById('tncModal').style.display = 'flex'; }
    function acceptTnC() {
        document.getElementById('f_tnc').checked = true;
        document.getElementById('f_tnc').disabled = false;
        document.getElementById('tncModal').style.display = 'none';
        validateForm();
    }

    // Security Validation (Length & Match)
    function validateSecurity() {
        const p1 = document.getElementById('f_pass').value;
        const p2 = document.getElementById('f_conf').value;
        const lenMsg = document.getElementById('pass-len-msg');
        const matchMsg = document.getElementById('pass-match-msg');
        const input2 = document.getElementById('f_conf');

        // Length Check
        lenMsg.style.display = (p1.length > 0 && p1.length < 6) ? 'block' : 'none';

        // Match Check
        if (p2.length > 0) {
            if (p1 !== p2) {
                matchMsg.style.display = 'block';
                input2.classList.add('input-error');
                isPassValid = false;
            } else {
                matchMsg.style.display = 'none';
                input2.classList.remove('input-error');
                isPassValid = (p1.length >= 6);
            }
        } else {
            matchMsg.style.display = 'none';
        }
        
        validateForm();
    }

    // AJAX Username Verification
    let timer;
    document.getElementById('f_user').addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const u = document.getElementById('f_user').value.trim();
            const f = document.getElementById('user-feedback');
            if (u.length < 3) { f.style.display = 'none'; isUserValid = false; validateForm(); return; }

            const res = await fetch('?check_user=' + encodeURIComponent(u));
            const status = await res.text();
            f.style.display = 'block';
            f.innerText = (status === 'taken') ? '✖ Username already exists' : '✔ Username is available';
            f.className = (status === 'taken') ? 'msg-hint err-text' : 'msg-hint ok-text';
            isUserValid = (status !== 'taken');
            validateForm();
        }, 500);
    });

    // Final Enable/Disable Button
    function validateForm() {
        const tnc = document.getElementById('f_tnc').checked;
        const btn = document.getElementById('btnS');
        btn.disabled = !(isUserValid && isPassValid && tnc);
    }
</script>

<?php 
if(file_exists('../includes/_foot.php')) { include('../includes/_foot.php'); } 
?>