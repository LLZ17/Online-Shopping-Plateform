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
    } catch (PDOException $e) { 
        $errorMsg = "Database Error: " . $e->getMessage(); 
    }
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'S.Admin'");
        $stmt->execute([$_GET['delete']]);
        header("Location: admin_crud.php?msg=deleted"); exit;
    } catch (PDOException $e) { 
        $errorMsg = "Delete Failed: " . $e->getMessage(); 
    }
}

// --- 抓取列表 ---
try {
    $sql = "SELECT * FROM users WHERE role IN ('Admin', 'S.Admin') ORDER BY user_id ASC";
    $admins = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    $admins = []; 
}

// 引入头部（在这里加载 CSS）
$admin_head = $pxx_root . '/includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<!-- ✅ 引入外部 CSS -->
<link rel="stylesheet" href="../css/app.css">

<div id="toastBox">
    <span style="background:#d4af37;color:#000;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;">✓</span>
    <span>Workspace Synced Successfully!</span>
</div>

<div class="admin-container">
    <div class="page-header">
        <h1>My Admin CRUD</h1>
        <div style="display:flex; gap:12px;">
            <button onclick="handleRefresh()" class="btn-op" id="refreshBtn" style="background:#fff;border:1px solid #e2e8f0;">
                🔄 Refresh
            </button>
            <button onclick="openModal('add')" class="btn-op" style="background:#0f172a;color:#fff;">
                ➕ Create Admin
            </button>
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
                    <td>#<?= htmlspecialchars($a['user_id']) ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img src="../images/<?= $photo ?>" style="width:45px;height:45px;border-radius:12px;object-fit:cover;">
                            <div>
                                <strong><?= htmlspecialchars($a['real_name'] ?: 'N/A') ?></strong><br>
                                <small>@<?= htmlspecialchars($a['username']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge <?= $roleClass ?>"><?= strtoupper($a['role']) ?></span></td>
                    <td><span class="badge <?= ($a['status']==='Blocked')?'bg-blocked':'bg-active' ?>">
                        <?= strtoupper($a['status']) ?>
                    </span></td>
                    <td>
                        <div class="action-group">
                            <button onclick='openModal("detail", <?= json_encode($a) ?>)' class="btn-op btn-detail">Detail</button>
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

<!-- Modal -->
<div class="modal-overlay" id="adminModal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Admin Identity</h2>

        <form id="modalForm" method="POST" action="admin_crud.php">
            <input type="hidden" name="action" id="form_action">
            <input type="hidden" name="user_id" id="field_id">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="field_user" required>
            </div>

            <div class="form-group">
                <label>Real Name</label>
                <input type="text" name="real_name" id="field_real">
            </div>

            <div class="form-group" id="pass_group">
                <label>Password</label>
                <input type="password" name="password" id="field_pass">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" id="field_role">
                    <option value="Admin">Admin</option>
                    <option value="S.Admin">S.Admin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" id="field_status">
                    <option value="Active">Active</option>
                    <option value="Blocked">Blocked</option>
                </select>
            </div>

            <div class="form-group info-only">
                <label>Created At</label>
                <input type="text" id="field_created" readonly>
            </div>

            <div id="modalFooter"></div>
        </form>
    </div>
</div>

<!-- ✅ 引入 JS -->
<script src="../js/app.js"></script>

<?php 
$foot = $pxx_root . '/includes/_admin_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>