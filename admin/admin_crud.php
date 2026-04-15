<?php
/**
 * PXX Mall - Admin CRUD (Strict Container Integration)
 * 📂 目标：强行嵌入 image_dd99aa.png 所示的白色内容区域
 */

error_reporting(E_ALL); ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. 物理路径定义 (严格按你的指令)
$pxx_root = dirname(__DIR__);
$pxx_db_file   = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'db.php';
$pxx_head_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_admin_head.php';
$pxx_foot_file = $pxx_root . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . '_admin_foot.php';

if (file_exists($pxx_db_file)) { 
    require_once $pxx_db_file; 
    if (!isset($pdo) && isset($conn)) { $pdo = $conn; }
} else { die("DB MISSING"); }

// AJAX 刷新
if (isset($_GET['action']) && $_GET['action'] == 'fetch_admins') {
    ob_clean();
    renderAdminList($pdo);
    exit;
}

function renderAdminList($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role IN ('Admin', 'S.Admin') ORDER BY created_at DESC");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    $counter = 1;
    foreach ($admins as $admin) {
        $photo = !empty($admin['profile_photo']) ? '../images/'.$admin['profile_photo'] : '../images/default.jpg';
        $roleClass = (stripos($admin['role'], 'S.') !== false) ? 'sadmin' : 'admin';
        $statusClass = strtolower($admin['status']);
        $combinedAddr = htmlspecialchars(($admin['address']??'')." ".($admin['Postcode']??'')." ".($admin['City']??'')." ".($admin['State']??''));
        ?>
        <div class="pxx-row-item">
            <div class="c-idx"><?php echo $counter++; ?></div>
            <div class="c-id">#<?php echo htmlspecialchars($admin['user_id']); ?></div>
            <div class="c-prof">
                <img src="<?php echo $photo; ?>" onerror="this.src='../images/default.jpg'">
                <div class="t-box">
                    <strong><?php echo htmlspecialchars($admin['real_name'] ?: 'N/A'); ?></strong>
                    <small>@<?php echo htmlspecialchars($admin['username']); ?></small>
                </div>
            </div>
            <div class="c-contact">
                <span><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($admin['email'] ?: 'N/A'); ?></span>
                <span><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($admin['phone_num'] ?: 'N/A'); ?></span>
            </div>
            <div class="c-fixed-group">
                <span class="p-status <?php echo $statusClass; ?>"><?php echo strtoupper($admin['status']); ?></span>
                <span class="p-role <?php echo $roleClass; ?>"><?php echo strtoupper($admin['role']); ?></span>
                <button class="b-det" onclick='openModal("detail", <?php echo json_encode($admin); ?>, "<?php echo addslashes($combinedAddr); ?>")'>Profile Detail</button>
                <button class="b-edit" onclick='openModal("edit", <?php echo json_encode($admin); ?>)'>Edit</button>
                <button class="b-del" onclick="confirmDelete('<?php echo $admin['user_id']; ?>')">Delete</button>
            </div>
        </div>
        <?php
    }
}

$totalAdmins = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('Admin', 'S.Admin')")->fetchColumn();

// 引入 HEAD
if (file_exists($pxx_head_file)) { include_once $pxx_head_file; }
?>

<style>
    /* 🌟 强行覆盖你截图里的空白区域 */
    .pxx-main-wrapper {
        position: relative;
        z-index: 100;
        background: #fff;
        margin: 20px;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        min-height: 80vh;
    }

    .pxx-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 20px; }
    .pxx-header h2 { font-size: 24px; font-weight: 800; color: #333; margin: 0; }
    .pxx-header p { color: #888; margin: 5px 0 0; }

    .pxx-list-container { display: flex; flex-direction: column; gap: 15px; }
    .pxx-row-item { display: flex; align-items: center; padding: 15px 25px; background: #fff; border: 1px solid #f0f0f0; border-radius: 8px; transition: 0.2s; }
    .pxx-row-item:hover { background: #fafafa; border-color: #ddd; }

    .c-idx { width: 40px; font-weight: 800; color: #ccc; }
    .c-id { width: 100px; font-weight: 700; color: #999; }
    .c-prof { width: 250px; display: flex; align-items: center; gap: 15px; }
    .c-prof img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
    .t-box strong { display: block; font-size: 15px; color: #333; }
    .t-box small { color: #888; }
    .c-contact { flex: 1; display: flex; flex-direction: column; gap: 4px; font-size: 13px; color: #666; }
    
    /* 5个核心元素锁定 */
    .c-fixed-group { display: flex; align-items: center; gap: 12px; flex-shrink: 0; white-space: nowrap; }
    .p-status { padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; }
    .active { background: #e6fffa; color: #38b2ac; }
    .blocked { background: #fff5f5; color: #f56565; }
    .p-role { padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid #eee; }
    .sadmin { background: #fff1f0; color: #e60012; }
    .admin { background: #ebf8ff; color: #3182ce; }

    .b-det { background: #ebf4ff; color: #3182ce; border: none; padding: 10px 15px; border-radius: 6px; font-weight: 700; cursor: pointer; }
    .b-edit { background: #10b981; color: white; border: none; padding: 10px 15px; border-radius: 6px; font-weight: 700; cursor: pointer; }
    .b-del { background: #ef4444; color: white; border: none; padding: 10px 15px; border-radius: 6px; font-weight: 700; cursor: pointer; }

    /* Modal */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 99999; }
    .modal-content { background: #fff; width: 750px; padding: 40px; border-radius: 15px; max-height: 85vh; overflow-y: auto; }
</style>

<div class="pxx-main-wrapper">
    <div class="pxx-header">
        <div>
            <h2>Admin Master List</h2>
            <p>Direct Database Management | Total: <strong><?php echo $totalAdmins; ?></strong></p>
        </div>
        <div style="display:flex; gap:10px;">
            <button class="b-edit" id="refreshBtn" style="background:#444;">Refresh</button>
            <button class="b-edit" onclick="openModal('add')" style="background:#e60012;">+ Create Admin</button>
        </div>
    </div>

    <div class="pxx-list-container" id="adminList">
        <?php renderAdminList($pdo); ?>
    </div>
</div>

<div class="modal-overlay" id="adminModal">
    <div class="modal-content">
        <h2 id="modalTitle">Details</h2>
        <form id="adminForm" action="process_admin.php" method="POST">
            <input type="hidden" name="user_id" id="m_id">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:20px;">
                <div><label>USERNAME</label><input type="text" name="username" id="m_user" style="width:100%; padding:10px;"></div>
                <div><label>REAL NAME</label><input type="text" name="real_name" id="m_real" style="width:100%; padding:10px;"></div>
                <div><label>ROLE</label><select name="role" id="m_role" style="width:100%; padding:10px;"><option value="Admin">Admin</option><option value="S.Admin">S.Admin</option></select></div>
                <div><label>STATUS</label><select name="status" id="m_status" style="width:100%; padding:10px;"><option value="Active">Active</option><option value="Blocked">Blocked</option></select></div>
                <div><label>CREATED AT</label><input type="text" id="m_created" readonly style="width:100%; padding:10px; background:#f9f9f9;"></div>
                <div><label>LAST LOGIN</label><input type="text" id="m_last" readonly style="width:100%; padding:10px; background:#f9f9f9;"></div>
            </div>
            <div style="margin-top:20px;"><label>FULL ADDRESS</label><textarea id="m_addr" rows="2" readonly style="width:100%; padding:10px; background:#f9f9f9;"></textarea></div>
            <div id="modalFooter" style="margin-top:30px; display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="b-edit" style="padding:15px;">Save Changes</button>
                <button type="button" onclick="closeModal()" style="background:#eee; border:none; padding:15px; border-radius:8px; cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// 🌟 强行删除那个讨厌的占位文字
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('div, p').forEach(el => {
        if (el.textContent.includes("Administrative Workspace Ready") && !el.closest('.pxx-main-wrapper')) {
            el.style.display = 'none';
        }
    });
});

document.getElementById('refreshBtn').onclick = function() {
    fetch('admin_crud.php?action=fetch_admins').then(res => res.text()).then(html => {
        document.getElementById('adminList').innerHTML = html;
        alert('Refreshed!');
    });
};
function openModal(mode, data = null, fullAddress = "") {
    document.getElementById('adminModal').style.display = 'flex';
    document.getElementById('adminForm').reset();
    if(mode === 'detail') {
        document.getElementById('modalTitle').innerText = "View Admin: " + data.user_id;
        document.getElementById('modalFooter').innerHTML = '<button type="button" onclick="closeModal()" style="width:100%; padding:15px; background:#eee; border-radius:8px; border:none;">Close</button>';
        fillData(data, true, fullAddress);
    } else {
        document.getElementById('modalTitle').innerText = (mode === 'edit' ? "Edit Admin" : "Create Admin");
        document.getElementById('modalFooter').innerHTML = '<button type="submit" class="b-edit" style="padding:15px;">Save</button><button type="button" onclick="closeModal()" style="padding:15px; border:none; background:#eee; border-radius:8px;">Cancel</button>';
        fillData(data || {}, false, fullAddress);
    }
}
function fillData(data, disabled, fullAddress) {
    document.getElementById('m_id').value = data.user_id || '';
    document.getElementById('m_user').value = data.username || '';
    document.getElementById('m_real_name').value = data.real_name || '';
    document.getElementById('m_role').value = data.role || 'Admin';
    document.getElementById('m_status').value = data.status || 'Active';
    document.getElementById('m_created').value = data.created_at || 'N/A';
    document.getElementById('m_last').value = data.last_login || 'N/A';
    document.getElementById('m_addr').value = fullAddress || 'N/A';
    document.querySelectorAll('#adminForm input, #adminForm select').forEach(el => { if(el.id !== 'm_created' && el.id !== 'm_last') el.disabled = disabled; });
}
function closeModal() { document.getElementById('adminModal').style.display = 'none'; }
function confirmDelete(id) { if(confirm("Delete Admin " + id + "?")) window.location.href="process_admin.php?delete="+id; }
</script>

<?php if (file_exists($pxx_foot_file)) { include_once $pxx_foot_file; } ?>