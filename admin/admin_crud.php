<?php
// PXX Mall - Admin Management (S.Admin Red Edition)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pxx_root = dirname(__DIR__);
require_once $pxx_root . '/includes/db.php'; 

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// --- 💡 后端处理逻辑 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    try {
        if ($action === 'add_admin') {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, real_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$_POST['username'], $hashedPassword, $_POST['real_name'], $_POST['role'], $_POST['status']]);
            header("Location: admin_crud.php?msg=added"); exit;
        }
        if ($action === 'edit_admin') {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, real_name = ?, role = ?, status = ? WHERE user_id = ?");
            $stmt->execute([$_POST['username'], $_POST['real_name'], $_POST['role'], $_POST['status'], $_POST['user_id']]);
            header("Location: admin_crud.php?msg=updated"); exit;
        }
    } catch (PDOException $e) { $errorMsg = "Database Error: " . $e->getMessage(); }
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'S.Admin'");
        $stmt->execute([$_GET['delete']]);
        header("Location: admin_crud.php?msg=deleted"); exit;
    } catch (PDOException $e) { $errorMsg = "Delete Failed: " . $e->getMessage(); }
}

// --- 抓取列表 ---
try {
    $sql = "SELECT * FROM users WHERE role IN ('Admin', 'S.Admin') ORDER BY user_id ASC";
    $admins = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $admins = []; }

$admin_head = $pxx_root . '/includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');
    
    .admin-container { width: 100%; box-sizing: border-box; padding: 20px; font-family: 'Plus Jakarta Sans', sans-serif; }
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
    .page-header h1 { font-size: 28px; font-weight: 900; color: #0f172a; margin: 0; }
    
    .table-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { background: #f8fafc; padding: 18px 20px; color: #64748b; font-size: 11px; font-weight: 800; text-transform: uppercase; }
    td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

    /* 角色标签通用样式 */
    .badge { padding: 6px 14px; border-radius: 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; display: inline-block; }
    
    /* 普通 Admin: 保持原样 */
    .bg-role-admin { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    
    /* 【修改：S.Admin 红色风格】 */
    .bg-role-sadmin { 
        background: #fee2e2; 
        color: #b91c1c; 
        border: 1px solid #fecaca; 
    }

    .bg-active { background: #dcfce7; color: #15803d; }
    .bg-blocked { background: #fee2e2; color: #b91c1c; }

    /* 布局修复：Actions 并排显示 */
    .action-group { display: flex; flex-direction: row; gap: 8px; align-items: center; white-space: nowrap; }

    .btn-op { padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.2s; border: none; }
    .btn-detail { background: #f1f5f9; color: #475569; }
    .btn-edit { background: #dcfce7; color: #15803d; }
    .btn-delete { background: #fee2e2; color: #b91c1c; }
    .btn-op:hover { transform: translateY(-2px); filter: brightness(0.95); }

    /* Toast 通知 */
    #toastBox {
        position: fixed; top: 30px; right: 30px; z-index: 99999;
        background: #1a1a1a; color: #d4af37; padding: 16px 24px;
        border-radius: 16px; border: 1px solid #d4af37;
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        display: none; align-items: center; gap: 12px; font-weight: 800;
        animation: slideIn 0.4s ease forwards;
    }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    /* Modal */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); display: none; justify-content: center; align-items: center; z-index: 10000; backdrop-filter: blur(4px); }
    .modal-content { background: #fff; width: 550px; border-radius: 24px; padding: 35px; position: relative; max-height: 90vh; overflow-y: auto; }
    .modal-close { position: absolute; top: 20px; right: 20px; font-size: 24px; cursor: pointer; color: #94a3b8; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
    .form-group input, .form-group select { width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid #e2e8f0; font-family: inherit; font-weight: 600; box-sizing: border-box; }
</style>

<div id="toastBox">
    <span style="background: #d4af37; color: #000; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">✓</span>
    <span>Workspace Synced Successfully!</span>
</div>

<div class="admin-container">
    <div class="page-header">
        <h1>MyAdmin CRUD</h1>
        <div style="display: flex; gap: 12px;">
            <button onclick="handleRefresh()" class="btn-op" id="refreshBtn" style="background:#fff; border:1px solid #e2e8f0;"><span>🔄</span> Refresh</button>
            <button onclick="openModal('add')" class="btn-op" style="background:#0f172a; color:#fff;"><span>➕</span> Create Admin</button>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Administrator</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $a): 
                    $photo = !empty($a['profile_photo']) ? $a['profile_photo'] : 'default.jpg';
                    $roleClass = ($a['role'] === 'S.Admin') ? 'bg-role-sadmin' : 'bg-role-admin';
                    $fullAddr = htmlspecialchars(($a['address']??'').", ".($a['Postcode']??'')." ".($a['City']??'').", ".($a['State']??''));
                ?>
                <tr>
                    <td style="font-weight: 800; color: #94a3b8;">#<?= htmlspecialchars($a['user_id']) ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <img src="../images/<?= $photo ?>" style="width:45px; height:45px; border-radius:12px; object-fit:cover;">
                            <div>
                                <span style="font-weight:800; display:block;"><?= htmlspecialchars($a['real_name'] ?: 'N/A') ?></span>
                                <span style="font-size:12px; color:#94a3b8;">@<?= htmlspecialchars($a['username']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge <?= $roleClass ?>"><?= strtoupper($a['role']) ?></span></td>
                    <td><span class="badge <?= ($a['status'] === 'Blocked') ? 'bg-blocked' : 'bg-active' ?>"><?= strtoupper($a['status']) ?></span></td>
                    <td>
                        <div class="action-group">
                            <button onclick='openModal("detail", <?= json_encode($a) ?>, "<?= addslashes($fullAddr) ?>")' class="btn-op btn-detail">Detail</button>
                            <button onclick='openModal("edit", <?= json_encode($a) ?>)' class="btn-op btn-edit">Edit</button>
                            <button onclick='handleDelete("<?= $a['user_id'] ?>")' class="btn-op btn-delete">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="adminModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle" style="margin-top:0; font-weight:900;">Admin Identity</h2>
        <hr style="border:0; border-top:1px solid #f1f5f9; margin:20px 0;">
        <form id="modalForm" method="POST" action="admin_crud.php">
            <input type="hidden" name="action" id="form_action">
            <input type="hidden" name="user_id" id="field_id">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group"><label>Username</label><input type="text" name="username" id="field_user" required></div>
                <div class="form-group"><label>Real Name</label><input type="text" name="real_name" id="field_real"></div>
                <div class="form-group" id="pass_group"><label>Password</label><input type="password" name="password" id="field_pass"></div>
                <div class="form-group"><label>Role</label>
                    <select name="role" id="field_role">
                        <option value="Admin">Admin</option>
                        <option value="S.Admin">S.Admin</option>
                    </select>
                </div>
                <div class="form-group"><label>Status</label>
                    <select name="status" id="field_status">
                        <option value="Active">Active</option>
                        <option value="Blocked">Blocked</option>
                    </select>
                </div>
                <div class="form-group info-only"><label>Created At</label><input type="text" id="field_created" readonly style="background:#f8fafc;"></div>
            </div>
            <div id="modalFooter" style="margin-top:20px;"></div>
        </form>
    </div>
</div>

<script>
function handleRefresh() {
    const btn = document.getElementById('refreshBtn');
    btn.innerHTML = "<span>⏳</span> Syncing...";
    setTimeout(() => {
        document.getElementById('toastBox').style.display = 'flex';
        setTimeout(() => { window.location.reload(); }, 800);
    }, 400);
}

function handleDelete(id) {
    if(confirm("🚨 Are you sure you want to delete this account?")) {
        window.location.href = "admin_crud.php?delete=" + id;
    }
}

function openModal(mode, data = null, addr = "") {
    const modal = document.getElementById('adminModal');
    const footer = document.getElementById('modalFooter');
    modal.style.display = 'flex';
    document.getElementById('modalForm').reset();

    if(mode === 'add') {
        document.getElementById('modalTitle').innerText = "Create Admin";
        document.getElementById('form_action').value = "add_admin";
        document.getElementById('pass_group').style.display = 'block';
        document.querySelectorAll('.info-only').forEach(e => e.style.display = 'none');
        document.querySelectorAll('#modalForm input, #modalForm select').forEach(e => e.disabled = false);
        footer.innerHTML = '<button type="submit" class="btn-op" style="width:100%; justify-content:center; background:#0f172a; color:#fff;">Create Now</button>';
    } else {
        document.getElementById('form_action').value = "edit_admin";
        document.getElementById('pass_group').style.display = 'none';
        document.querySelectorAll('.info-only').forEach(e => e.style.display = 'block');
        document.getElementById('field_id').value = data.user_id;
        document.getElementById('field_user').value = data.username;
        document.getElementById('field_real').value = data.real_name || '';
        document.getElementById('field_role').value = data.role;
        document.getElementById('field_status').value = data.status;
        document.getElementById('field_created').value = data.created_at;

        const isDetail = mode === 'detail';
        document.getElementById('modalTitle').innerText = isDetail ? "Admin Detail" : "Edit Admin";
        document.querySelectorAll('#modalForm input, #modalForm select').forEach(e => {
            if(e.id !== 'field_id') e.disabled = isDetail;
        });
        footer.innerHTML = isDetail ? 
            '<button type="button" onclick="closeModal()" class="btn-op btn-detail" style="width:100%; justify-content:center;">Close</button>' :
            '<button type="submit" class="btn-op" style="width:100%; justify-content:center; background:#10b981; color:#fff;">Update Profile</button>';
    }
}

function closeModal() { document.getElementById('adminModal').style.display = 'none'; }

document.addEventListener("DOMContentLoaded", function() {
    let vp = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    let con = document.querySelector('.admin-container');
    if (vp && con && vp !== con.parentNode) {
        vp.innerHTML = '';
        vp.appendChild(con);
    }
});
</script>

<?php 
$foot = $pxx_root . '/includes/_admin_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>