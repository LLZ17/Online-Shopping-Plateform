<?php
// TEE DE YONG's Part: Edit Member Profile (Premium Layout)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/_base.php'; 
require_once '../includes/db.php';     

$id = $_GET['id'] ?? '';

// 如果提交了表单
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // 🔴 提醒：如果你的数据库列名叫 username 或 user_id，请在这里修改！
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
    
    if ($stmt->execute([$name, $email, $id])) {
        echo "<script>alert('✨ Member profile updated successfully!'); window.location.href='member_crud.php';</script>";
        exit;
    }
}

// 🔴 提醒：如果主键不叫 id，请改这里！
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    header("Location: member_crud.php");
    exit;
}

include '../includes/_admin_head.php'; 
?>

<style>
    /* 隐藏多余 Dashboard */
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid,
    .admin-content-viewport > div[style*="background:white"] { display: none !important; }
    
    html, body { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%) !important; overflow: auto !important; }

    .edit-container { max-width: 900px; margin: 60px auto; background: white; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.06); }
    
    .edit-header { 
        background: #1e293b; /* 沉稳的深蓝灰色 */
        color: white; padding: 40px 50px; 
        display: flex; justify-content: space-between; align-items: center;
        border-radius: 20px 20px 0 0;
    }
    .edit-header h2 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: 1px; }
    .edit-header .sys-id { background: rgba(255,255,255,0.1); padding: 8px 15px; border-radius: 20px; font-size: 14px; font-weight: 600; }
    
    .form-body { padding: 40px 50px 50px 50px; } 
    
    .form-group { margin-bottom: 30px; }
    .form-group label { display: block; font-weight: 700; color: #1a202c; margin-bottom: 10px; font-size: 14px; text-transform: uppercase; }
    
    .form-group input { 
        width: 100%; padding: 18px 20px; border-radius: 12px; 
        border: 2px solid transparent; font-size: 16px; font-weight: 500;
        color: #333; background: #f4f7f6; font-family: inherit; transition: 0.3s;
        box-sizing: border-box; 
    }
    .form-group input:focus { border-color: #3b82f6; background: white; outline: none; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
    
    /* 无法修改的只读输入框 */
    .form-group input[readonly] { background: #e2e8f0; color: #64748b; cursor: not-allowed; }

    .action-row { display: flex; align-items: center; justify-content: space-between; margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee; }
    
    .btn-submit { 
        background: #3b82f6; color: white; padding: 18px 50px; 
        border: none; border-radius: 12px; font-size: 16px; font-weight: 800; 
        cursor: pointer; transition: 0.3s;
    }
    .btn-submit:hover { background: #2563eb; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59,130,246,0.2); }
    
    .btn-cancel { color: #888; text-decoration: none; font-weight: 600; font-size: 15px; transition: 0.2s; }
    .btn-cancel:hover { color: #111; }
</style>

<div id="member-edit-form" class="edit-container">
    <div class="edit-header">
        <h2>Edit Member Profile</h2>
        <div class="sys-id">System ID: #<?= htmlspecialchars($id) ?></div>
    </div>
    
    <div class="form-body">
        <form method="post" action="">
            
            <div class="form-group">
                <label>System ID (Read Only)</label>
                <input type="text" value="<?= htmlspecialchars($id) ?>" readonly>
            </div>

            <div class="form-group">
                <label>Member Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($member['name'] ?? '') ?>" required>
            </div>
           <div class="form-group">
    <label>Email Address</label>
    <input type="text" name="email" value="<?= htmlspecialchars($member['email'] ?? '') ?>" required>
               </div>

            <div class="action-row">
                <a href="member_crud.php" class="btn-cancel">← Cancel & Return</a>
                <button type="submit" class="btn-submit">SAVE CHANGES</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('member-edit-form');
        if (viewport && content) {
            viewport.innerHTML = ''; 
            viewport.appendChild(content);
        }
    });
</script>

</main>
<?php include '../includes/_foot.php'; ?>