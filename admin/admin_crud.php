<?php
/**
 * =================================================================================
 * PXX MALL - PREMIUM ADMIN MANAGEMENT (CRUD SYSTEM)
 * =================================================================================
 * Features: All-in-one CRUD (Create, Read, Update, Delete)
 * Layout: Premium Black & Gold Grid
 * Database Linked: users (role = Admin/S.Admin)
 * =================================================================================
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. 物理挂载基础核心与数据库
require_once '../includes/admin-base.php'; 
require_once '../includes/db.php';     

// 2. 路由与状态控制
$action = $_GET['action'] ?? 'list';
$search = $_GET['search'] ?? '';
$msg = '';

// -----------------------------------------------------------------------------
// 【智能 ID 生成器 (针对 Admin)】 -> e.g., ad1000
// -----------------------------------------------------------------------------
function generateAdminID($pdo) {
    $stmt = $pdo->query("SELECT user_id FROM users WHERE role IN ('Admin', 'S.Admin') AND user_id LIKE 'ad%' ORDER BY CAST(SUBSTRING(user_id, 3) AS UNSIGNED) DESC LIMIT 1");
    $last_id = $stmt->fetchColumn();
    if (!$last_id) return "ad1000";
    $num = (int)substr($last_id, 2);
    return "ad" . ($num + 1);
}

// -----------------------------------------------------------------------------
// 【CRUD: 删除操作 Delete】
// -----------------------------------------------------------------------------
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role IN ('Admin', 'S.Admin')");
        $stmt->execute([$del_id]);
        echo "<script>alert('Admin deleted successfully!'); window.location.href='admin_manage.php';</script>";
        exit;
    } catch (PDOException $e) { $msg = "Delete Error: " . $e->getMessage(); }
}

// -----------------------------------------------------------------------------
// 【CRUD: 添加 / 编辑处理 Create & Update】
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u_user  = trim($_POST['username']);
    $u_real  = trim($_POST['real_name'] ?? '');
    $u_email = trim($_POST['email'] ?? '');
    $u_phone = trim($_POST['phone_num'] ?? '');
    $u_role  = $_POST['role'] ?? 'Admin';
    $u_pass  = $_POST['password'] ?? '';
    
    // 为了满足数据库非空约束，填入默认值
    $u_pcode = '00000'; 
    $u_city  = '-';

    if ($action == 'add') {
        try {
            $new_id = generateAdminID($pdo);
            $hash = password_hash($u_pass, PASSWORD_DEFAULT);
            $now = date("Y-m-d H:i:s");

            $sql = "INSERT INTO users (user_id, username, password_hashed, real_name, email, phone_num, role, Postcode, City, created_at, status, email_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', 'Verified')";
            $pdo->prepare($sql)->execute([$new_id, $u_user, $hash, $u_real, $u_email, $u_phone, $u_role, $u_pcode, $u_city, $now]);
            echo "<script>alert('New Admin Created: $new_id'); window.location.href='admin_manage.php';</script>";
            exit;
        } catch (PDOException $e) { $msg = "Insert Error: " . $e->getMessage(); }
        
    } elseif ($action == 'edit') {
        $edit_id = $_POST['user_id'];
        try {
            if (!empty($u_pass)) {
                // 如果填写了新密码，连密码一起更新
                $hash = password_hash($u_pass, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username=?, real_name=?, email=?, phone_num=?, role=?, password_hashed=? WHERE user_id=?";
                $pdo->prepare($sql)->execute([$u_user, $u_real, $u_email, $u_phone, $u_role, $hash, $edit_id]);
            } else {
                // 不更新密码
                $sql = "UPDATE users SET username=?, real_name=?, email=?, phone_num=?, role=? WHERE user_id=?";
                $pdo->prepare($sql)->execute([$u_user, $u_real, $u_email, $u_phone, $u_role, $edit_id]);
            }
            echo "<script>alert('Admin Data Updated!'); window.location.href='admin_manage.php';</script>";
            exit;
        } catch (PDOException $e) { $msg = "Update Error: " . $e->getMessage(); }
    }
}

// -----------------------------------------------------------------------------
// 【CRUD: 获取列表数据 Read】
// -----------------------------------------------------------------------------
$sql_list = "SELECT * FROM users WHERE role IN ('Admin', 'S.Admin')";
if ($search) { $sql_list .= " AND username LIKE " . $pdo->quote("%$search%"); }
$sql_list .= " ORDER BY created_at DESC";
$admins = $pdo->query($sql_list)->fetchAll();

// 载入头部
include '../includes/_admin_head.php'; 
?>

<style>
    /* 清除默认主面板多余元素，确保独立视觉 */
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid,
    .admin-content-viewport > div[style*="background:white"] { display: none !important; }
    
    html, body { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%) !important; overflow: auto !important; }

    .crud-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }

    /* 黑金标头 */
    .action-header {
        display: flex; justify-content: space-between; align-items: center;
        background: #111; padding: 30px 40px; border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15); margin-bottom: 30px;
    }
    .action-header h1 { margin: 0; font-size: 28px; font-weight: 800; color: #fff; }
    .action-header p { margin: 5px 0 0; color: #eab308; font-size: 14px; font-weight: 600; }

    .search-bar { padding: 12px 20px; border-radius: 10px; border: none; background: rgba(255,255,255,0.1); color: #fff; outline: none; width: 250px; }
    .search-bar:focus { box-shadow: 0 0 0 2px #eab308; }
    
    .btn-gold { background: #eab308; color: #111; padding: 12px 25px; border-radius: 10px; font-weight: 800; text-decoration: none; border: none; cursor: pointer; transition: 0.3s; display: inline-block;}
    .btn-gold:hover { background: #facc15; transform: translateY(-2px); }

    /* --- 卡片网格视图 (List View) --- */
    .member-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
    .m-card {
        background: white; border-radius: 20px; padding: 30px 20px; text-align: center; 
        border: 1px solid #eee; box-shadow: 0 10px 20px rgba(0,0,0,0.03); transition: 0.3s;
        position: relative; overflow: hidden;
    }
    .m-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: #eab308; }
    .m-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: #eab308; }

    /* 🌟 头像永远在第一位 */
    .avatar-wrapper { margin-bottom: 20px; }
    .avatar-img { 
        width: 80px; height: 80px; border-radius: 50%; object-fit: cover; 
        box-shadow: 0 8px 15px rgba(0,0,0,0.1); border: 3px solid #fff; 
    }
    .avatar-text {
        width: 80px; height: 80px; border-radius: 50%; margin: 0 auto;
        background: linear-gradient(135deg, #111, #333); color: #eab308; 
        font-size: 32px; font-weight: 800; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    .m-name { font-size: 20px; font-weight: 800; color: #111; margin: 0 0 5px 0; }
    .m-id { color: #888; font-size: 12px; margin-bottom: 15px; font-weight: 600; }
    .badge-admin { display: inline-block; background: #fef08a; color: #854d0e; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 800; margin-bottom: 20px; }

    .m-actions { display: flex; gap: 10px; border-top: 1px solid #f1f1f1; padding-top: 20px; }
    .btn-act { flex: 1; padding: 10px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; transition: 0.2s; }
    .btn-edit { background: #f8fafc; color: #111; border: 1px solid #eee; }
    .btn-edit:hover { background: #e2e8f0; }
    .btn-del { background: #fff1f0; color: #e60012; border: 1px solid #ffa39e; }
    .btn-del:hover { background: #ffccc7; }

    /* --- 表单视图 (Add/Edit View) --- */
    .form-card { background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .f-group { display: flex; flex-direction: column; gap: 8px; }
    .f-group label { font-size: 13px; font-weight: 800; color: #555; text-transform: uppercase; }
    .f-input { padding: 14px 18px; border: 2px solid #eee; border-radius: 10px; font-size: 15px; outline: none; transition: 0.3s; }
    .f-input:focus { border-color: #eab308; box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.1); }
</style>

<div id="premium-admin-content" class="crud-container">
    
    <?php if($msg): ?>
        <div style="background:#fff1f0; color:#e60012; padding:15px 20px; border-radius:10px; margin-bottom:20px; font-weight:bold; border:1px solid #ffa39e;">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <?php if ($action == 'add' || $action == 'edit'): 
        // 提取编辑数据
        $edit_data = null;
        if ($action == 'edit' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$_GET['id']]);
            $edit_data = $stmt->fetch();
        }
    ?>
        <div class="action-header">
            <div>
                <h1><?= $action == 'add' ? 'Add New Admin' : 'Edit Admin Details' ?></h1>
                <p>Ensure high-level security when granting admin privileges.</p>
            </div>
            <a href="admin_manage.php" class="btn-gold" style="background:#333; color:#fff;">Back to List</a>
        </div>

        <div class="form-card">
            <form method="POST" action="admin_manage.php?action=<?= $action ?>">
                <?php if($action == 'edit'): ?>
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($edit_data['user_id']) ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="f-group">
                        <label>System Username *</label>
                        <input type="text" name="username" class="f-input" required value="<?= htmlspecialchars($edit_data['username'] ?? '') ?>">
                    </div>
                    <div class="f-group">
                        <label><?= $action == 'add' ? 'Login Password *' : 'New Password (Leave blank to keep old)' ?></label>
                        <input type="password" name="password" class="f-input" <?= $action == 'add' ? 'required' : '' ?>>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="f-group">
                        <label>Full Real Name</label>
                        <input type="text" name="real_name" class="f-input" value="<?= htmlspecialchars($edit_data['real_name'] ?? '') ?>">
                    </div>
                    <div class="f-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="f-input" value="<?= htmlspecialchars($edit_data['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="f-group">
                        <label>Mobile Number</label>
                        <input type="text" name="phone_num" class="f-input" value="<?= htmlspecialchars($edit_data['phone_num'] ?? '') ?>">
                    </div>
                    <div class="f-group">
                        <label>Admin Privilege Role *</label>
                        <select name="role" class="f-input" required>
                            <option value="Admin" <?= ($edit_data['role']??'') == 'Admin' ? 'selected' : '' ?>>Admin (Standard)</option>
                            <option value="S.Admin" <?= ($edit_data['role']??'') == 'S.Admin' ? 'selected' : '' ?>>Super Admin (Full Access)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-gold" style="width:100%; margin-top:20px; font-size:18px;">
                    <?= $action == 'add' ? 'Create Admin Account' : 'Save Changes' ?>
                </button>
            </form>
        </div>

    <?php else: ?>
        <div class="action-header">
            <div>
                <h1>Management Team</h1>
                <p>System Administrators: <b><?= count($admins) ?> Active Personnel</b></p>
            </div>
            <div style="display: flex; gap: 15px;">
                <form method="get">
                    <input type="text" name="search" class="search-bar" placeholder="Search admins..." value="<?= htmlspecialchars($search) ?>">
                </form>
                <a href="admin_manage.php?action=add" class="btn-gold">+ Deploy New Admin</a>
            </div>
        </div>

        <div class="member-grid">
            <?php foreach ($admins as $a): ?>
            <div class="m-card">
                
                <div class="avatar-wrapper">
                    <?php if(!empty($a['profile_photo']) && $a['profile_photo'] != 'default.jpg'): ?>
                        <img src="../images/<?= htmlspecialchars($a['profile_photo']) ?>" class="avatar-img" alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-text"><?= strtoupper(substr($a['username'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>

                <h3 class="m-name"><?= htmlspecialchars($a['username']) ?></h3>
                <div class="m-id">ID: <?= htmlspecialchars($a['user_id']) ?></div>
                <div class="badge-admin"><?= htmlspecialchars($a['role']) ?></div>
                
                <div style="font-size:13px; color:#666; margin-bottom:20px;">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($a['email'] ?? 'No Email') ?>
                </div>
                
                <div class="m-actions">
                    <a href="admin_manage.php?action=edit&id=<?= $a['user_id'] ?>" class="btn-act btn-edit">Edit details</a>
                    <a href="admin_manage.php?delete_id=<?= $a['user_id'] ?>" class="btn-act btn-del" onclick="return confirm('WARNING: Are you sure you want to revoke this admin access?');">Revoke</a>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('premium-admin-content');
        if (viewport && content) {
            viewport.innerHTML = ''; 
            viewport.appendChild(content);
        }
    });
</script>

<?php include '../includes/_foot.php'; ?>