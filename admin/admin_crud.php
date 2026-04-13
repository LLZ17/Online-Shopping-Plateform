<?php
// TEE DE YONG's Part: Admin Management (Premium Layout - Fixed SQL)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/_base.php'; 
require_once '../includes/db.php';     

$search = $_GET['search'] ?? '';

// 🌟 核心魔法：去 users 表找人，但只找 role 是 Admin 或 S.Admin 的人！
$sql = "SELECT * FROM users WHERE role IN ('Admin', 'S.Admin')"; 

if ($search) {
    // 因为前面用了 WHERE，这里要用 AND 连接搜索条件
    $sql .= " AND username LIKE " . $pdo->quote("%$search%");
}

// 🌟 换成你表里真正的编号 user_id
$sql .= " ORDER BY user_id DESC";

try {
    $admins = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    die("<div style='padding: 50px; text-align: center; font-family: sans-serif;'><h2>⚠️ 数据库错误</h2><p>" . $e->getMessage() . "</p></div>");
}

include '../includes/_admin_head.php'; 
?>

<style>
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid,
    .admin-content-viewport > div[style*="background:white"] { display: none !important; }
    
    html, body { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%) !important; overflow: auto !important; }

    .crud-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

    /* 黑金风格顶部标题栏 */
    .action-header {
        display: flex; justify-content: space-between; align-items: center;
        background: #111; padding: 30px 40px; border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 30px;
    }
    .action-header h1 { margin: 0; font-size: 28px; font-weight: 800; color: #fff; }
    .action-header p { margin: 5px 0 0; color: #aaa; font-size: 15px; }

    /* 搜索和添加按钮区 */
    .header-actions { display: flex; gap: 15px; }
    .search-wrapper { position: relative; width: 300px; }
    .search-wrapper input {
        width: 100%; padding: 12px 20px; border-radius: 12px;
        border: none; background: rgba(255,255,255,0.1); color: white; font-size: 15px; outline: none;
        box-sizing: border-box; transition: 0.3s;
    }
    .search-wrapper input:focus { background: rgba(255,255,255,0.2); box-shadow: 0 0 0 2px #eab308; }
    .search-wrapper input::placeholder { color: #888; }
    
    .btn-add { background: #eab308; color: #111; padding: 12px 25px; border-radius: 12px; font-weight: 800; text-decoration: none; transition: 0.3s; }
    .btn-add:hover { background: #facc15; transform: translateY(-2px); }

    /* 卡片网格 */
    .member-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }

    .m-card {
        background: white; border-radius: 20px; padding: 30px; text-align: center; 
        border: 1px solid #f1f1f1; box-shadow: 0 10px 20px rgba(0,0,0,0.02); transition: 0.3s;
        position: relative; overflow: hidden;
    }
    .m-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: #eab308; }
    
    /* 顶部金边 */
    .m-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: #eab308; }

    /* 尊贵黑金头像 */
    .avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, #111, #333); color: #eab308; 
        font-size: 32px; font-weight: 800; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px auto; box-shadow: 0 8px 15px rgba(0,0,0,0.2);
    }

    .m-name { font-size: 20px; font-weight: 800; color: #111; margin: 0 0 10px 0; }
    .badge-admin { display: inline-block; background: #fef08a; color: #854d0e; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; margin-bottom: 20px; text-transform: uppercase; }

    /* 操作按钮 */
    .m-actions { display: flex; gap: 10px; border-top: 1px solid #f1f1f1; padding-top: 20px; }
    .btn-action { flex: 1; padding: 12px; border-radius: 12px; font-size: 14px; font-weight: 700; text-decoration: none; transition: 0.2s; text-align: center; }
    .btn-edit { background: #f8fafc; color: #333; }
    .btn-edit:hover { background: #e2e8f0; }
</style>

<div id="premium-admin-content" class="crud-container">
    
    <div class="action-header">
        <div>
            <h1>Staff & Admins</h1>
            <p>System Administrators: <b><?= count($admins) ?></b></p>
        </div>
        <div class="header-actions">
            <form method="get" class="search-wrapper">
                <input type="text" name="search" placeholder="Search admin..." value="<?= htmlspecialchars($search) ?>">
            </form>
            <a href="#" class="btn-add" onclick="alert('This will open the Add Admin form!')">+ New Admin</a>
        </div>
    </div>

    <div class="member-grid">
        <?php foreach ($admins as $a): ?>
        <div class="m-card">
            <div class="avatar"><?= strtoupper(substr($a['username'] ?? 'A', 0, 1)) ?></div>
            <h3 class="m-name"><?= htmlspecialchars($a['username'] ?? 'Unknown') ?></h3>
            
            <div class="badge-admin"><?= htmlspecialchars($a['role'] ?? 'Admin') ?></div>
            
            <div class="m-actions">
              <a href="admin_add.php" class="btn-add">+ New Admin</a>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

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

</main>
<?php include '../includes/_foot.php'; ?>