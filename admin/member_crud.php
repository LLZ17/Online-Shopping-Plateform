<?php
// TEE DE YONG's Part: Member Management (Premium Layout)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. 确保正确的相对路径
require_once '../includes/_base.php'; 
require_once '../includes/db.php';     

// 2. 获取会员数据 (带搜索功能)
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users"; // 假设你的会员表叫 users
if ($search) {
    $sql .= " WHERE name LIKE " . $pdo->quote("%$search%") . " OR email LIKE " . $pdo->quote("%$search%");
}
$sql .= " ORDER BY user_id DESC";
$members = $pdo->query($sql)->fetchAll();

// 3. 引入侧边栏
include '../includes/_admin_head.php'; 
?>

<style>
    /* 清场代码：隐藏 Dashboard 多余内容 */
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid,
    .admin-content-viewport > div[style*="background:white"] {
        display: none !important;
    }
    
    html, body { 
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%) !important; 
        overflow: auto !important; 
    }

    .crud-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

    /* 顶部标题栏 */
    .action-header {
        display: flex; justify-content: space-between; align-items: center;
        background: white; padding: 30px 40px; border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04); margin-bottom: 30px;
    }
    .action-header h1 { margin: 0; font-size: 28px; font-weight: 800; color: #111; }
    .action-header p { margin: 5px 0 0; color: #888; font-size: 15px; }

    /* 搜索栏 */
    .search-wrapper { position: relative; width: 350px; }
    .search-wrapper input {
        width: 100%; padding: 15px 20px 15px 50px; border-radius: 15px;
        border: 2px solid #f1f1f1; background: #f9f9f9; font-size: 15px;
        transition: 0.3s; outline: none; box-sizing: border-box;
    }
    .search-wrapper input:focus { border-color: #3b82f6; background: white; box-shadow: 0 0 0 4px rgba(59,130,246,0.1); }
    .search-icon { position: absolute; left: 20px; top: 18px; color: #aaa; font-size: 16px; }

    /* 会员卡片网格 */
    .member-grid { 
        display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; 
    }

    .m-card {
        background: white; border-radius: 20px; padding: 30px;
        text-align: center; border: 1px solid #f1f1f1;
        box-shadow: 0 10px 20px rgba(0,0,0,0.02); transition: 0.3s;
    }
    .m-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: #e2e8f0; }

    /* 动态头像 */
    .avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #2dd4bf);
        color: white; font-size: 32px; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px auto; box-shadow: 0 8px 15px rgba(59,130,246,0.3);
    }

    .m-name { font-size: 20px; font-weight: 800; color: #111; margin: 0 0 5px 0; }
    .m-email { font-size: 14px; color: #888; margin: 0 0 15px 0; word-break: break-all; }
    .m-id { display: inline-block; background: #f1f5f9; color: #475569; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 20px; }

    /* 操作按钮 */
    .m-actions { display: flex; gap: 10px; border-top: 1px solid #f1f1f1; padding-top: 20px; }
    .btn-action {
        flex: 1; padding: 12px; border-radius: 12px; font-size: 14px; font-weight: 700;
        text-decoration: none; transition: 0.2s; text-align: center;
    }
    .btn-edit { background: #f8fafc; color: #333; }
    .btn-edit:hover { background: #e2e8f0; }
    .btn-del { background: #fff1f0; color: #e60012; }
    .btn-del:hover { background: #ffe4e1; }
</style>

<div id="premium-member-content" class="crud-container">
    
    <div class="action-header">
        <div>
            <h1>Member Directory</h1>
            <p>Total registered users: <b><?= count($members) ?></b></p>
        </div>
        
        <form method="get" class="search-wrapper">
            <div class="search-icon">🔍</div>
            <input type="text" name="search" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <div class="member-grid">
        <?php foreach ($members as $m): ?>
        <div class="m-card">
            <div class="avatar"><?= strtoupper(substr($m['username'] ?? 'U', 0, 1)) ?></div>
            
            <h3 class="m-name"><?= htmlspecialchars($m['username'] ?? 'Unknown User') ?></h3>
            
            <p class="m-email"><?= htmlspecialchars($m['email'] ?? 'No email provided') ?></p>
            
            <div class="m-id">System ID: #<?= $m['user_id'] ?></div>
            
            <div class="m-actions">
                <a href="member_edit.php?id=<?= $m['user_id'] ?>" class="btn-action btn-edit">Edit Profile</a>
                <a href="member_delete.php?id=<?= $m['user_id'] ?>" class="btn-action btn-del" onclick="return confirm('Delete this member completely?')">Remove</a>
            </div>
        </div>
        <?php endforeach; ?>
   
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('premium-member-content');
        if (viewport && content) {
            viewport.innerHTML = ''; 
            viewport.appendChild(content);
        }
    });
</script>

</main>
<?php include '../includes/_foot.php'; ?>