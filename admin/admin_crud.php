<?php
/**
 * PXX Mall - Admin Management (Full CRUD Integration)
 * 📂 Separated Edition: Logic + HTML Structure
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pxx_root = dirname(__DIR__);
require_once $pxx_root . '/includes/db.php'; 

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// 💡 处理 Edit (更新) 提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_admin') {
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, real_name = ?, role = ?, status = ? WHERE user_id = ?");
        $stmt->execute([$_POST['username'], $_POST['real_name'], $_POST['role'], $_POST['status'], $_POST['user_id']]);
        header("Location: admin_crud.php?msg=updated");
        exit;
    } catch (PDOException $e) { $errorMsg = "Update Failed: " . $e->getMessage(); }
}

// 💡 处理 Delete 提交
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'S.Admin'");
        $stmt->execute([$_GET['delete']]);
        header("Location: admin_crud.php?msg=deleted");
        exit;
    } catch (PDOException $e) { $errorMsg = "Delete Failed: " . $e->getMessage(); }
}

// 抓取列表
try {
    $sql = "SELECT * FROM users WHERE role IN ('Admin', 'S.Admin') ORDER BY user_id ASC";
    $admins = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $admins = []; }

$admin_head = $pxx_root . '/includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css">

<div class="admin-container">
    <div class="page-header">
        <h1>Admin Workspace</h1>
        <div style="display: flex; gap: 12px;">
            <button onclick="handleRefresh()" class="btn-op" style="background:#fff; border:1px solid #e2e8f0;"><span>🔄</span> Refresh</button>
            <a href="admin_add.php" class="btn-op" style="background:#0f172a; color:#fff;"><span>➕</span> Create Admin</a>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Administrator</th>
                    <th>System Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $a): 
                    $photo = !empty($a['profile_photo']) ? $a['profile_photo'] : 'default.jpg';
                    $fullAddr = htmlspecialchars(($a['address']??'').", ".($a['Postcode']??'')." ".($a['City']??'').", ".($a['State']??''));
                ?>
                <tr>
                    <td style="font-weight: 800; color: #94a3b8;">#<?= htmlspecialchars($a['user_id']) ?></td>
                    <td>
                        <div class="profile-info">
                            <img src="../images/<?= htmlspecialchars($photo) ?>" class="profile-img" onerror="this.src='../images/default.jpg'">
                            <div class="user-names">
                                <span class="u-realname"><?= htmlspecialchars($a['real_name'] ?: 'N/A') ?></span>
                                <span style="font-size:12px; color:#94a3b8;">@<?= htmlspecialchars($a['username']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-role"><?= strtoupper($a['role']) ?></span></td>
                    <td><span class="badge <?= ($a['status'] === 'Blocked') ? 'bg-blocked' : 'bg-active' ?>"><?= strtoupper($a['status']) ?></span></td>
                    <td>
                        <div class="action-group">
                            <button onclick='openModal("detail", <?= json_encode($a) ?>, "<?= addslashes($fullAddr) ?>")' class="btn-op btn-detail">👁️ Detail</button>
                            <button onclick='openModal("edit", <?= json_encode($a) ?>)' class="btn-op btn-edit">✏️ Edit</button>
                            <button onclick='handleDelete("<?= $a['user_id'] ?>")' class="btn-op btn-delete">🗑️ Delete</button>
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
            <input type="hidden" name="action" value="edit_admin">
            <input type="hidden" name="user_id" id="field_id">
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group"><label>Username</label><input type="text" name="username" id="field_user" required></div>
                <div class="form-group"><label>Real Name</label><input type="text" name="real_name" id="field_real"></div>
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
                <div class="form-group"><label>Created At</label><input type="text" id="field_created" readonly style="background:#f8fafc;"></div>
                <div class="form-group"><label>Last Login</label><input type="text" id="field_login" readonly style="background:#f8fafc;"></div>
            </div>
            
            <div class="form-group"><label>Full Address</label><textarea id="field_addr" rows="2" readonly style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0; background:#f8fafc; font-family:inherit;"></textarea></div>

            <div id="modalFooter" class="modal-footer"></div>
        </form>
    </div>
</div>

<script src="../js/app.js"></script>

<?php 
$foot = $pxx_root . '/includes/_admin_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>