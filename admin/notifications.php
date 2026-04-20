<?php
require_once '../includes/db.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. 权限检查：同时允许 Admin 和 S.Admin 进入
if (!isset($_SESSION['user_id']) || !in_array(trim($_SESSION['role']), ['Admin', 'S.Admin'])) {
    header("Location: login.php");
    exit;
}

$aid = $_SESSION['user_id'];
$is_super = (trim($_SESSION['role']) === 'S.Admin'); // 判断是否为超级管理员
$current_role = 'admin'; 

// 2. 处理操作逻辑
if (isset($_POST['action'])) {
    try {
        $notif_id = $_POST['notif_id'] ?? null;
        if ($_POST['action'] == 'mark_all_read') {
            $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND role = ?")->execute([$aid, $current_role]);
        } elseif ($_POST['action'] == 'delete_all') {
            $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND role = ?")->execute([$aid, $current_role]);
        } elseif ($_POST['action'] == 'toggle_read') {
            $new_status = ($_POST['current_status'] == 1) ? 0 : 1;
            $pdo->prepare("UPDATE notifications SET is_read = ? WHERE id = ? AND user_id = ?")->execute([$new_status, $notif_id, $aid]);
        }
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit;
    } catch (PDOException $e) { die($e->getMessage()); }
}

// 3. 获取数据
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND role = ? ORDER BY created_at DESC");
$stmt->execute([$aid, $current_role]);
$notifications = $stmt->fetchAll();

// 引入你的后台头部 (包含 Sidebar 和 Topbar)
include '../includes/_admin_head.php'; 
?>

<style>
    /* 适配你的后台内容区域背景色 */
    .admin-content-inner {
        padding: 25px;
        background-color: #f4f7f6; 
        min-height: 100vh;
    }

    /* 顶部标题栏：参考 Product CRUD 风格 */
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .content-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    /* 按钮样式：参考你截图中的蓝白按钮风格 */
    .btn-group-tools { display: flex; gap: 10px; }
    
    .btn-tool {
        padding: 8px 18px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: 0.2s;
    }

    .btn-white { background: #fff; border: 1px solid #dce1e7; color: #444; }
    .btn-white:hover { background: #f8f9fa; border-color: #c3cad1; }
    
    .btn-red-outline { background: #fff; border: 1px solid #ff0000; color: #ff0000; }
    .btn-red-outline:hover { background: #ff0000; color: #fff; }

    /* 通知列表容器 */
    .notif-card-list {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #eef1f5;
        overflow: hidden;
    }

    .notif-row {
        padding: 20px;
        border-bottom: 1px solid #f0f3f7;
        display: flex;
        align-items: flex-start;
        gap: 15px;
        position: relative;
        transition: 0.2s;
    }
    .notif-row:last-child { border-bottom: none; }
    .notif-row:hover { background-color: #fafbfc; }

    /* 未读状态指示器 */
    .status-indicator {
        width: 4px;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        background: #ff0000;
        display: none;
    }
    .unread .status-indicator { display: block; }
    .is-read { opacity: 0.55; }

    /* 图标框 */
    .icon-wrapper {
        width: 42px; height: 42px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
        background: #f8f9fa; color: #555;
    }

    .notif-main-text { flex-grow: 1; }
    .notif-main-text h5 { margin: 0 0 4px 0; font-size: 16px; font-weight: 600; color: #333; }
    .notif-main-text p { margin: 0; font-size: 14px; color: #666; line-height: 1.5; }
    .notif-meta { margin-top: 8px; font-size: 12px; color: #999; font-weight: 500; }

    /* 角色标签 */
    .role-badge {
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        text-transform: uppercase;
        margin-left: 8px;
        background: #eee;
    }
    .badge-sadmin { background: #ffebeb; color: #ff0000; border: 1px solid #ffcccc; }
</style>

<div class="admin-content-inner">
    <div class="content-header">
        <h2>Notifications 
            <?php if($is_super): ?><span class="role-badge badge-sadmin">S.Admin Access</span><?php endif; ?>
        </h2>
        
        <div class="btn-group-tools">
            <form method="POST">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn-tool btn-white">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
            </form>
            <form method="POST" onsubmit="return confirm('Clear all notifications?');">
                <input type="hidden" name="action" value="delete_all">
                <button type="submit" class="btn-tool btn-red-outline">
                    <i class="fas fa-trash-alt"></i> Clear All
                </button>
            </form>
        </div>
    </div>

    <div class="notif-card-list">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $row): 
                $is_unread = !$row['is_read'];
                $type = $row['type'];
                // 图标逻辑
                $icon = 'bell';
                if($type == 'order') $icon = 'shopping-cart';
                elseif($type == 'stock') $icon = 'exclamation-triangle';
                elseif($type == 'system') $icon = 'cog';
            ?>
                <div class="notif-row <?php echo $is_unread ? 'unread' : 'is-read'; ?>">
                    <div class="status-indicator"></div>
                    
                    <div class="icon-wrapper">
                        <i class="fas fa-<?php echo $icon; ?>"></i>
                    </div>

                    <div class="notif-main-text">
                        <h5><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p><?php echo htmlspecialchars($row['message']); ?></p>
                        <div class="notif-meta">
                            <i class="far fa-clock"></i> <?php echo date('d M Y, g:i A', strtotime($row['created_at'])); ?>
                        </div>
                    </div>

                    <div class="row-actions">
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_read">
                            <input type="hidden" name="notif_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $row['is_read']; ?>">
                            <button type="submit" style="background:none; border:none; color:#ccc; cursor:pointer;">
                                <i class="fas <?php echo $is_unread ? 'fa-envelope' : 'fa-envelope-open'; ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 60px; text-align: center; color: #999;">
                <img src="../images/no-data.png" style="width: 80px; opacity: 0.3; margin-bottom: 15px;" onerror="this.style.display='none'">
                <p>Workspace Ready. No new notifications.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/_admin_foot.php'; ?>