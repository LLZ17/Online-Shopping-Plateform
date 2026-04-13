<?php
// TEE DE YONG's Part: Member Edit (Fixed column to 'real_name')
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$id = $_GET['id'] ?? '';
if (!$id) {
    die("<script>alert('⚠️ No Member ID provided!'); window.location.href='member_crud.php';</script>");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    die("<div style='padding:50px; text-align:center;'><h2>⚠️ Member not found!</h2><a href='member_crud.php'>Go Back</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 💡 接收 real_name
    $real_name = $_POST['real_name'] ?? '';
    $email = $_POST['email'] ?? '';

    try {
        // 💡 数据库更新语句换成 real_name
        $updateStmt = $pdo->prepare("UPDATE users SET real_name = ?, email = ? WHERE user_id = ?");
        $updateStmt->execute([$real_name, $email, $id]);

        echo "<script>alert('✨ Member profile updated successfully!'); window.location.href='member_crud.php';</script>";
        exit;

    } catch (Exception $e) {
        die("<div style='padding:20px; color:red;'>Update Error: " . $e->getMessage() . "</div>");
    }
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    .page-title-box { display: none !important; }
    html, body { background: #f8fafc !important; overflow: hidden !important; }
    .scroll-wrapper { height: 100vh; overflow-y: auto; padding-bottom: 150px; box-sizing: border-box; }
    .premium-form-card { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04); overflow: hidden; font-family: 'Inter', sans-serif; }
    .form-header { background: #111; padding: 25px 35px; border-bottom: 4px solid #2563eb; }
    .form-header h2 { margin: 0; color: white; font-size: 24px; font-weight: 900; letter-spacing: -0.5px; }
    .form-header p { margin: 5px 0 0; color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .form-body { padding: 30px 35px; }
    .form-group { margin-bottom: 25px; }
    .form-group label { display: block; font-weight: 800; color: #64748b; margin-bottom: 10px; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
    .input-style { width: 100%; padding: 16px 20px; border-radius: 12px; border: 2px solid #f1f5f9; font-size: 15px; font-weight: 600; color: #1e293b; background: #f8fafc; transition: all 0.3s ease; box-sizing: border-box; }
    .input-style:focus { border-color: #2563eb; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .btn-row { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 25px; border-top: 1px solid #f1f5f9; }
    .btn-submit { background: #111; color: #3b82f6; padding: 16px 35px; border: 2px solid #3b82f6; border-radius: 12px; font-size: 14px; font-weight: 900; cursor: pointer; transition: all 0.3s ease; }
    .btn-submit:hover { background: #3b82f6; color: #fff; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59,130,246,0.2); }
    .btn-back { color: #94a3b8; text-decoration: none; font-size: 13px; font-weight: 800; padding-left: 5px; transition: 0.3s; }
    .btn-back:hover { color: #1e293b; }
</style>

<div id="edit-wrapper" class="scroll-wrapper" style="display:none;">
    <div id="edit-member-app" class="premium-form-card">
        <div class="form-header">
            <h2>Edit Member Profile</h2>
            <p>UPDATING USER ID #<?= htmlspecialchars($id) ?></p>
        </div>
        <div class="form-body">
            <form method="POST" action="">
                
                <div class="form-group">
                    <label>Real Name</label>
                    <input type="text" name="real_name" class="input-style" value="<?= htmlspecialchars($member['real_name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="input-style" value="<?= htmlspecialchars($member['email'] ?? '') ?>" required>
                </div>

                <div class="btn-row">
                    <a href="member_crud.php" class="btn-back">← CANCEL</a>
                    <button type="submit" class="btn-submit">CONFIRM UPDATE</button>
                </div>
                
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const myContent = document.getElementById('edit-wrapper');
        if (!myContent) return;
        myContent.style.display = 'block';

        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
        if (!viewport) {
            const allElements = document.body.getElementsByTagName('*');
            for (let el of allElements) {
                if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                    viewport = el.parentElement; break;
                }
            }
        }
        if (viewport) {
            viewport.innerHTML = ''; viewport.appendChild(myContent);
        } else {
            myContent.style.position = 'fixed'; myContent.style.top = '60px'; myContent.style.right = '0';
            myContent.style.width = 'calc(100vw - 250px)'; myContent.style.height = 'calc(100vh - 60px)';
            myContent.style.background = '#f8fafc'; myContent.style.zIndex = '9999'; document.body.appendChild(myContent);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; }
?>