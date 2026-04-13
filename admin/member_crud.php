<?php
// TEE DE YONG's Part: Member CRUD (Enlarged Premium UI)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$search = $_GET['search'] ?? '';
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit  = 6; 

$where = "1=1";
$params = [];
if ($search !== '') {
    $where .= " AND (real_name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$offset = ($page - 1) * $limit;

try {
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM users WHERE $where");
    $stmtTotal->execute($params);
    $totalMembers = $stmtTotal->fetchColumn();
    $totalPages = ceil($totalMembers / $limit);

    $sql = "SELECT * FROM users WHERE $where ORDER BY user_id DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    die("<div style='padding:50px;'>Error: " . $e->getMessage() . "</div>");
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    .page-title-box { display: none !important; }
    html, body { background: #f0f2f5 !important; font-family: 'Inter', sans-serif; }
    
    /* 💡 放大点 1：增加容器最大宽度 */
    .main-container { max-width: 1400px; margin: 40px auto; padding: 0 30px; }
    
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
    .top-bar h1 { font-size: 40px; font-weight: 900; color: #111; margin: 0; letter-spacing: -2px; }
    .btn-add-pro { background: #2563eb; color: white; padding: 16px 32px; border-radius: 16px; font-weight: 800; text-decoration: none; box-shadow: 0 10px 20px rgba(37,99,235,0.2); font-size: 16px; }

    .filter-card { background: white; padding: 25px; border-radius: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 40px; display: flex; gap: 20px; }
    .input-pro { flex: 1; padding: 16px 24px; border: 2px solid #edf2f7; border-radius: 14px; font-weight: 600; font-size: 16px; outline: none; }
    .btn-search { background: #111; color: white; padding: 16px 45px; border-radius: 14px; font-weight: 800; border: none; cursor: pointer; font-size: 16px; }

    .member-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 35px; margin-bottom: 50px; }

    /* 💡 放大点 2：增加卡片内边距 */
    .member-card { background: white; border-radius: 30px; padding: 35px; border: 1px solid #f1f5f9; transition: 0.4s; position: relative; }
    .member-card:hover { transform: translateY(-8px); box-shadow: 0 25px 50px rgba(0,0,0,0.08); }
    
    .id-badge { position: absolute; top: 30px; right: 30px; background: #f1f5f9; color: #64748b; padding: 6px 15px; border-radius: 12px; font-size: 12px; font-weight: 900; }
    
    /* 💡 放大点 3：头像和名字放大 */
    .user-profile { display: flex; align-items: center; gap: 25px; margin-bottom: 30px; }
    .avatar-circle { width: 90px; height: 90px; border-radius: 28px; background: #eff6ff; display: flex; align-items: center; justify-content: center; font-size: 40px; color: #2563eb; font-weight: 900; }
    
    .name-info h3 { margin: 0; font-size: 26px; font-weight: 800; color: #1e293b; }
    .name-info p { margin: 4px 0 0; font-size: 16px; color: #94a3b8; font-weight: 600; }

    .detail-list { border-top: 2px solid #f8fafc; padding-top: 25px; }
    .detail-item { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 16px; font-weight: 600; }
    .detail-label { color: #94a3b8; }
    .detail-value { color: #334155; }

    .action-row { display: flex; gap: 15px; margin-top: 30px; }
    .btn-edit-pro { flex: 1; text-align: center; padding: 18px; background: #f8fafc; color: #334155; border-radius: 16px; text-decoration: none; font-weight: 800; font-size: 15px; border: 2px solid #e2e8f0; }
    .btn-edit-pro:hover { background: #2563eb; color: #fff; border-color: #2563eb; }
    .btn-del-pro { width: 60px; display: flex; align-items: center; justify-content: center; background: #fff1f0; color: #ef4444; border-radius: 16px; text-decoration: none; font-size: 20px; }

    .pagination { display: flex; justify-content: center; align-items: center; gap: 12px; padding-bottom: 60px; }
    .page-btn { min-width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; background: white; border-radius: 16px; text-decoration: none; font-weight: 800; font-size: 18px; border: 2px solid #edf2f7; transition: 0.3s; }
    .page-btn.active { background: #2563eb; color: white; border-color: #2563eb; box-shadow: 0 10px 20px rgba(37,99,235,0.2); }
</style>

<div id="member-app" class="main-container" style="display:none;">
    <div class="top-bar">
        <h1>User Directory</h1>
        <a href="member_add.php" class="btn-add-pro">+ NEW MEMBER</a>
    </div>

    <form method="GET" class="filter-card">
        <input type="text" name="search" class="input-pro" placeholder="Search members..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">SEARCH</button>
    </form>

    <div class="member-grid">
        <?php foreach ($members as $m): ?>
        <div class="member-card">
            <div class="id-badge">ID: <?= $m['user_id'] ?></div>
            <div class="user-profile">
                <div class="avatar-circle"><?= mb_substr($m['real_name'], 0, 1) ?></div>
                <div class="name-info">
                    <h3><?= htmlspecialchars($m['real_name']) ?></h3>
                    <p>@<?= htmlspecialchars($m['username']) ?></p>
                </div>
            </div>
            <div class="detail-list">
                <div class="detail-item"><span class="detail-label">Email</span><span class="detail-value"><?= $m['email'] ?></span></div>
                <div class="detail-item"><span class="detail-label">Phone</span><span class="detail-value"><?= $m['phone_num'] ?? 'N/A' ?></span></div>
                <div class="detail-item"><span class="detail-label">Gender</span><span class="detail-value"><?= $m['gender'] ?? 'N/A' ?></span></div>
            </div>
            <div class="action-row">
                <a href="member_edit.php?id=<?= $m['user_id'] ?>" class="btn-edit-pro">EDIT PROFILE</a>
                <a href="member_delete.php?id=<?= $m['user_id'] ?>" class="btn-del-pro" onclick="return confirm('Delete user?')">🗑️</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="page-btn <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const app = document.getElementById('member-app');
        app.style.display = 'block';
        let v = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
        if (v) { v.innerHTML = ''; v.appendChild(app); }
    });
</script>

<?php $foot = __DIR__ . '/../includes/_foot.php'; if (file_exists($foot)) { include $foot; } ?>