<?php
/**
 * PXX Mall - Admin CRUD (Ultimate Anti-Overlap & Flexible Grid)
 * 📂 Features: Dynamic Grid, Visible Roles, Scrollable Modal, Delete Alert
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. 引入数据库
require_once '../includes/db.php'; 

// ============================================================
// 🌟 AJAX 局部刷新接口
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'fetch_admins') {
    ob_clean(); 
    renderAdminList($pdo);
    exit; 
}

/**
 * 🌟 列表渲染函数
 */
function renderAdminList($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role IN ('Admin', 'S.Admin') ORDER BY created_at DESC");
    $stmt->execute();
    $admins = $stmt->fetchAll();

    if (!$admins) {
        echo '<div style="padding:40px; text-align:center; color:#999; background:#fff; border-radius:12px;">No administrators found.</div>';
        return;
    }

    $counter = 1; 
    foreach ($admins as $admin) {
        $photoFile = !empty($admin['profile_photo']) ? $admin['profile_photo'] : 'default.jpg';
        $finalAvatar = '../images/' . $photoFile;
        
        // 角色和状态的 CSS 类名 (使用 stripos 忽略大小写，防止读取不到)
        $roleClass = (stripos($admin['role'], 'S.') !== false) ? 'sadmin' : 'admin';
        $statusClass = strtolower($admin['status']);

        // 物理合并地址
        $combinedAddr = htmlspecialchars($admin['address']) . ", " . 
                        htmlspecialchars($admin['Postcode']) . " " . 
                        htmlspecialchars($admin['City']) . ", " . 
                        htmlspecialchars($admin['State']);
        ?>
        <div class="pxx-list-card">
            <div class="col-index"><?php echo $counter++; ?></div>

            <div class="col-id">#<?php echo $admin['user_id']; ?></div>

            <div class="col-profile">
                <img src="<?php echo $finalAvatar; ?>" onerror="this.src='../images/default.jpg'" class="list-avatar">
                <div class="name-box">
                    <strong><?php echo htmlspecialchars($admin['real_name'] ?: 'N/A'); ?></strong>
                    <span>@<?php echo htmlspecialchars($admin['username']); ?></span>
                </div>
            </div>

            <div class="col-contact">
                <div class="contact-line"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($admin['email'] ?: 'N/A'); ?></div>
                <div class="contact-line"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($admin['phone_num'] ?: 'N/A'); ?></div>
            </div>

            <div class="col-status">
                <span class="status-pill <?php echo $statusClass; ?>"><?php echo strtoupper($admin['status']); ?></span>
                <span class="role-pill <?php echo $roleClass; ?>"><?php echo strtoupper($admin['role']); ?></span>
            </div>

            <div class="col-actions">
                <button class="btn-primary-detail" onclick='openModal("detail", <?php echo json_encode($admin); ?>, "<?php echo $combinedAddr; ?>")'>
                    Profile Detail
                </button>
                <button class="btn-crud edit" onclick='openModal("edit", <?php echo json_encode($admin); ?>)'>
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="btn-crud delete" onclick="confirmDelete('<?php echo $admin['user_id']; ?>')">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>
        <?php
    }
}

// 获取总人数
$totalStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('Admin', 'S.Admin')");
$totalCount = $totalStmt->fetchColumn();

// 2. 引用头部
if (file_exists('../includes/admin_head.php')) {
    include_once '../includes/admin_head.php';
}
?>

<style>
    /* =========================================
       🎨 核心 UI 系统
       ========================================= */
    :root {
        --pxx-red: #ef4444;
        --pxx-green: #10b981;
        --pxx-blue: #3182ce;
        --pxx-bg: #f8f9fc;
        --border: #edf2f7;
    }

    body { background-color: var(--pxx-bg) !important; margin: 0; padding-bottom: 60px; font-family: 'Inter', sans-serif; }
    .admin-main-viewport { padding: 40px; max-width: 1550px; margin: 0 auto; }

    /* 顶部导航控制栏 */
    .crud-header {
        display: flex; justify-content: space-between; align-items: center;
        background: white; padding: 25px 40px; border-radius: 16px;
        margin-bottom: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.02);
    }

    .header-info h2 { margin: 0; font-size: 24px; font-weight: 800; color: #2d3748; }
    .header-info p { margin: 5px 0 0; color: #718096; font-size: 14px; }

    .btn-top {
        padding: 12px 24px; border-radius: 10px; border: none; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s;
    }
    .btn-refresh { background: #34495e; color: white; }
    .btn-add { background: #e60012; color: white; }

    /* =========================================
       🌟 卡片排版 (核心修复：max-content 确保内容不被挤压)
       ========================================= */
    .admin-list-container { display: flex; flex-direction: column; gap: 14px; }

    .pxx-list-card {
        background: white; border-radius: 12px; padding: 22px 30px;
        display: grid; 
        /* 1. 序号: 40px 
           2. ID: 100px (足够拉开与头像的距离)
           3. 头像/名字: 240px
           4. 联系方式: 1fr (自动吸收剩余空间)
           5. 状态和角色: max-content (确保标签绝不被压缩)
           6. 按钮组: max-content (确保按钮完整显示)
        */
        grid-template-columns: 40px 100px 240px 1fr max-content max-content;
        align-items: center; border: 1px solid var(--border); transition: 0.2s;
        gap: 20px; 
    }
    .pxx-list-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.06); border-color: #cbd5e0; }

    .col-index { font-weight: 800; color: #cbd5e0; font-size: 16px; text-align: center; }
    .col-id { font-weight: 700; color: #a0aec0; font-size: 15px; }

    .col-profile { display: flex; align-items: center; gap: 18px; }
    .list-avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; background: #eee; }
    .name-box strong { display: block; font-size: 16px; color: #1a202c; }
    .name-box span { font-size: 13px; color: #718096; }

    .col-contact { display: flex; flex-direction: column; gap: 6px; font-size: 14px; color: #4a5568; }
    .contact-line i { width: 18px; color: #cbd5e0; margin-right: 8px; }

    /* 状态与角色 (使用 flex 并排) */
    .col-status { display: flex; flex-direction: row; gap: 10px; align-items: center; white-space: nowrap; }
    .status-pill { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; }
    .active { background: #e6fffa; color: #38b2ac; }
    .blocked { background: #fff5f5; color: #f56565; }

    .role-pill { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid var(--border); }
    .sadmin { background: #fff1f0; color: #e60012; }
    .admin { background: #ebf8ff; color: #3182ce; }

    /* 操作按钮组 */
    .col-actions { display: flex; justify-content: flex-end; gap: 12px; align-items: center; }
    
    .btn-primary-detail { background: #ebf4ff; color: var(--pxx-blue); border: none; padding: 12px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s; white-space: nowrap; }
    .btn-primary-detail:hover { background: var(--pxx-blue); color: white; }
    
    .btn-crud { width: 44px; height: 44px; border-radius: 8px; border: none; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: 0.2s; }
    .edit { background: var(--pxx-green); }
    .edit:hover { background: #059669; }
    .delete { background: var(--pxx-red); }
    .delete:hover { background: #dc2626; }

    /* =========================================
       🌟 增强型可滑动弹窗 (Modal)
       ========================================= */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 10001;
        backdrop-filter: blur(4px);
    }
    .modal-content {
        background: white; width: 850px; border-radius: 20px; padding: 45px; position: relative;
        max-height: 85vh; /* 限制高度开启滑动 */
        overflow-y: auto; 
        box-shadow: 0 30px 70px rgba(0,0,0,0.2);
    }
    .close-modal { position: absolute; top: 25px; right: 30px; font-size: 32px; cursor: pointer; color: #cbd5e0; }

    .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 10px; }
    .fld-box { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
    .fld-box label { font-size: 11px; font-weight: 800; color: #a0aec0; text-transform: uppercase; letter-spacing: 1px; }
    .fld-box input, .fld-box select, .fld-box textarea { padding: 14px; border: 1px solid var(--border); border-radius: 10px; font-family: inherit; font-size: 14px; }
    .fld-box input:focus { border-color: var(--pxx-blue); outline: none; }

    /* 弹窗底部按钮 */
    .btn-save { background: var(--pxx-green); color: white; border: none; padding: 16px; border-radius: 10px; font-weight: 800; cursor: pointer; margin-top: 10px; font-size: 15px; }
    .btn-cancel { background: #f1f5f9; color: #475569; border: none; padding: 16px; border-radius: 10px; font-weight: 800; cursor: pointer; margin-top: 10px; font-size: 15px; }

    .fa-spin-fast { animation: spin 0.6s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>

<div class="admin-main-viewport">
    <div class="crud-header">
        <div class="header-info">
            <h2>Administrative Workspace</h2>
            <p>Direct database management for all Admin roles. 
                <strong style="color:var(--pxx-red); margin-left:15px;">Total Admins: <?php echo $totalCount; ?></strong>
            </p>
        </div>
        <div style="display:flex; gap:15px;">
            <button class="btn-top btn-refresh" id="refreshBtn"><i class="fa-solid fa-arrows-rotate"></i> Refresh Records</button>
            <button class="btn-top btn-add" onclick="openModal('add')"><i class="fa-solid fa-plus"></i> Create Admin</button>
        </div>
    </div>

    <div class="admin-list-container" id="adminList">
        <?php renderAdminList($pdo); ?>
    </div>
</div>

<div class="modal-overlay" id="adminModal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle" style="margin:0; color:#2d3748;">Profile Detail</h2>
        <hr style="border:0; border-top:1px solid #edf2f7; margin: 25px 0;">

        <form id="adminForm" action="process_admin.php" method="POST">
            <input type="hidden" name="user_id" id="m_user_id">
            
            <div class="modal-grid">
                <div class="fld-box"><label>Username / Handle</label><input type="text" name="username" id="m_username" required></div>
                <div class="fld-box"><label>Legal Real Name</label><input type="text" name="real_name" id="m_real_name"></div>
                <div class="fld-box"><label>System Permission Role</label>
                    <select name="role" id="m_role">
                        <option value="Admin">Admin</option>
                        <option value="S.Admin">S.Admin</option>
                    </select>
                </div>
                <div class="fld-box"><label>Account Status</label>
                    <select name="status" id="m_status">
                        <option value="Active">Active</option>
                        <option value="Blocked">Blocked</option>
                    </select>
                </div>
                
                <div class="fld-box"><label>Account Created At</label><input type="text" id="m_created_at" readonly style="background:#f8fafc; color:#94a3b8;"></div>
                <div class="fld-box"><label>Last Login Session</label><input type="text" id="m_last_login" readonly style="background:#f8fafc; color:#94a3b8;"></div>
                
                <div class="fld-box"><label>Assigned Gender</label>
                    <select name="gender" id="m_gender">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="fld-box"><label>Email</label><input type="email" name="email" id="m_email"></div>
            </div>
            
            <div class="fld-box">
                <label>Combined Mailing Address (Full Location)</label>
                <textarea id="m_full_address" rows="2" readonly style="background:#f8fafc; line-height:1.6;"></textarea>
            </div>

            <div style="margin-top:25px; display:flex; flex-direction:column;" id="modalFooter">
                <button type="submit" class="btn-save">Save Database Changes</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel & Close</button>
            </div>
        </form>
    </div>
</div>

<script>
// =========================================
// 🚀 AJAX Refresh + Success Prompt
// =========================================
document.getElementById('refreshBtn').onclick = function() {
    const icon = this.querySelector('i');
    icon.classList.add('fa-spin-fast');
    
    fetch('admin_crud.php?action=fetch_admins')
    .then(res => res.text())
    .then(html => {
        setTimeout(() => {
            document.getElementById('adminList').innerHTML = html;
            icon.classList.remove('fa-spin-fast');
            alert('✅ Admin Records Synchronized Successfully!');
        }, 500);
    });
};

function openModal(mode, data = null, fullAddress = "") {
    const modal = document.getElementById('adminModal');
    const title = document.getElementById('modalTitle');
    const footer = document.getElementById('modalFooter');
    const form = document.getElementById('adminForm');

    modal.style.display = 'flex';
    form.reset();

    if(mode === 'detail') {
        title.innerText = "Viewing Detail: " + data.user_id;
        footer.innerHTML = '<button type="button" class="btn-cancel" onclick="closeModal()" style="width:100%">Close Details</button>';
        fillData(data, true, fullAddress);
    } else {
        title.innerText = (mode === 'edit') ? "Edit Administrator" : "Create New Admin";
        footer.innerHTML = '<button type="submit" class="btn-save">Save Database Changes</button><button type="button" class="btn-cancel" onclick="closeModal()">Cancel & Close</button>';
        fillData(data || {}, false, fullAddress);
    }
}

function fillData(data, disabled, fullAddress) {
    document.getElementById('m_user_id').value = data.user_id || '';
    document.getElementById('m_username').value = data.username || '';
    document.getElementById('m_real_name').value = data.real_name || '';
    document.getElementById('m_role').value = data.role || 'Admin';
    document.getElementById('m_status').value = data.status || 'Active';
    document.getElementById('m_gender').value = data.gender || 'Male';
    document.getElementById('m_email').value = data.email || '';
    
    document.getElementById('m_created_at').value = data.created_at || 'N/A';
    document.getElementById('m_last_login').value = data.last_login || 'N/A';
    document.getElementById('m_full_address').value = fullAddress || 'No address data provided';

    const inputs = document.querySelectorAll('#adminForm input, #adminForm select, #adminForm textarea');
    inputs.forEach(el => {
        if(el.id === 'm_created_at' || el.id === 'm_last_login' || el.id === 'm_full_address') {
            el.disabled = true;
        } else if(el.type !== 'hidden') {
            el.disabled = disabled;
        }
    });
}

function closeModal() { document.getElementById('adminModal').style.display = 'none'; }

// 删除确认
function confirmDelete(id) {
    if(confirm("🚨 WARNING: You are about to permanently delete Admin " + id + ".\nClick OK to proceed.")) {
        window.location.href = "process_admin.php?delete=" + id;
    }
}
</script>

<?php 
// 3. 引用页脚
if (file_exists('../includes/_foot.php')) {
    include_once '../includes/_foot.php';
}
?>