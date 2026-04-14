<?php
// TEE DE YONG's Part: Member Management - Final Fix
require_once 'includes/_base.php';
require_once 'includes/db.php';

$_title = 'Admin | Member Management';
include 'includes/_admin_head.php';

// 1. 处理搜索逻辑 (修正表名为 users)
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users"; // 这里已经从 members 改成了 users
if ($search) {
    // 假设你的 ID 字段叫 id，名字字段叫 name
    $sql .= " WHERE name LIKE " . $pdo->quote("%$search%") . " OR id LIKE " . $pdo->quote("%$search%");
}
$sql .= " ORDER BY id DESC"; // 按 ID 倒序排列，新注册的在前面
$members = $pdo->query($sql)->fetchAll();
?>

<style>
    body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
    .content-wrapper { padding: 40px; max-width: 1400px; margin: 0 auto; }
    
    .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
    .search-box { 
        background: white; 
        padding: 12px 25px; 
        border-radius: 15px; 
        display: flex; 
        align-items: center; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        width: 400px;
    }
    .search-box input { border: none; outline: none; margin-left: 10px; flex: 1; font-size: 15px; }

    .member-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
    
    .member-card { 
        background: white; 
        border-radius: 20px; 
        padding: 30px; 
        text-align: center; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.02); 
        transition: 0.3s; 
        border: 1px solid transparent;
    }
    .member-card:hover { transform: translateY(-10px); border-color: #e74c3c; box-shadow: 0 15px 40px rgba(231, 76, 60, 0.1); }

    .avatar { 
        width: 70px; 
        height: 70px; 
        background: #fdf2f0; 
        border-radius: 50%; 
        margin: 0 auto 15px; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        font-size: 22px;
        color: #e74c3c;
        font-weight: 800;
        border: 2px solid #fff;
    }

    .status-badge { 
        background: #e8f5e9; 
        color: #4caf50; 
        padding: 4px 12px; 
        border-radius: 20px; 
        font-size: 11px; 
        font-weight: 700; 
        display: inline-block;
        margin-bottom: 15px;
    }

    .btn-action { margin-top: 20px; display: flex; gap: 8px; justify-content: center; }
    .btn-edit { background: #f1f2f6; color: #333; padding: 8px 15px; border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 600; flex: 1; }
    .btn-del { background: #ffebee; color: #f44336; padding: 8px 15px; border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 600; flex: 1; }
</style>

<div class="content-wrapper">
    <div class="header-section">
        <div>
            <h1 style="font-size: 32px; font-weight: 800; color: #1a1a1a;">Member Directory</h1>
            <p style="color: #666;">Total Registered Users: <b><?= count($members) ?></b></p>
        </div>
        
        <form method="get" class="search-box">
            <span>🔍</span>
            <input type="text" name="search" placeholder="Search name or ID..." value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <form method="GET" class="filter-card">
        <input type="text" name="search" class="input-pro" placeholder="Search members..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search">SEARCH</button>
    </form>

    <div class="member-grid">
        <?php foreach ($members as $m): ?>
        <div class="member-card">
            <div class="avatar">
                <?= strtoupper(substr($m['name'] ?? 'U', 0, 1)) ?>
            </div>

            <div class="status-badge">● Active Member</div>
            
            <h3 style="margin: 0; color: #1a1a1a; font-size: 18px;"><?= htmlspecialchars($m['name'] ?? 'Unknown') ?></h3>
            <p style="color: #bbb; font-size: 12px; margin: 5px 0 20px;">User ID: #<?= $m['id'] ?></p>

            <div style="border-top: 1px solid #f9f9f9; padding-top: 20px; text-align: left;">
                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 10px;">
                    <span style="color: #999;">Email:</span>
                    <span style="color: #333; font-weight: 500;"><?= $m['email'] ?? 'N/A' ?></span>
                </div>
                </div>

            <div class="btn-action">
                <a href="admin_member_edit.php?id=<?= $m['id'] ?>" class="btn-edit">Edit Profile</a>
                <a href="admin_member_delete.php?id=<?= $m['id'] ?>" class="btn-del" onclick="return confirm('Suspend user?')">Suspend</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/_foot.php'; ?>
