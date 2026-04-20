<?php
// 1. 引入数据库连接 (PDO)
require_once '../includes/db.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. 检查登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$current_role = 'member'; 

// --- 3. 后端处理逻辑 ---
if (isset($_POST['action'])) {
    try {
        $notif_id = $_POST['notif_id'] ?? null;

        if ($_POST['action'] == 'mark_all_read') {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND role = ?");
            $stmt->execute([$uid, $current_role]);
        } 
        elseif ($_POST['action'] == 'mark_all_unread') {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 0 WHERE user_id = ? AND role = ?");
            $stmt->execute([$uid, $current_role]);
        }
        elseif ($_POST['action'] == 'delete_all') {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND role = ?");
            $stmt->execute([$uid, $current_role]);
        } 
        elseif ($_POST['action'] == 'delete_single') {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$notif_id, $uid]);
        }
        elseif ($_POST['action'] == 'toggle_read') {
            $new_status = ($_POST['current_status'] == 1) ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$new_status, $notif_id, $uid]);
        }
        
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// --- 4. 获取数据 ---
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND role = ? ORDER BY created_at DESC");
$stmt->execute([$uid, $current_role]);
$notifications = $stmt->fetchAll();

include '../includes/_head.php'; 
?>

<style>
    :root {
        --theme-green: #10b981; /* 翡翠绿 */
        --theme-green-soft: #ecfdf5;
        --text-dark: #1f2937;
        --text-grey: #6b7280;
        --bg-light: #f9fafb;
    }

    body { background-color: var(--bg-light); font-family: 'Inter', sans-serif; }
    .notif-container { max-width: 850px; margin: 40px auto; padding: 0 20px; }
    
    .notif-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
    .notif-header h2 { font-weight: 800; color: var(--text-dark); margin: 0; font-size: 1.8rem; }
    
    /* 顶部按钮组 */
    .header-actions { display: flex; gap: 8px; }
    .btn-action { padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; transition: 0.2s; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; color: var(--text-dark); shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-action:hover { border-color: var(--theme-green); color: var(--theme-green); background: var(--theme-green-soft); }
    .btn-danger-outline { color: #ef4444; border-color: #fee2e2; }
    .btn-danger-outline:hover { background: #fef2f2; border-color: #ef4444; color: #ef4444; }

    /* 卡片设计 */
    .notif-card {
        background: #fff; border-radius: 16px; padding: 22px; margin-bottom: 16px;
        position: relative; display: flex; gap: 18px;
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        transition: 0.3s;
    }
    
    .unread { border-left: 5px solid var(--theme-green); }
    .read { opacity: 0.65; background: #fafafa; border-left: 5px solid #d1d5db; }
    
    .notif-icon { 
        width: 50px; height: 50px; border-radius: 14px; display: flex; 
        align-items: center; justify-content: center; font-size: 22px; 
        flex-shrink: 0; background: var(--theme-green-soft); color: var(--theme-green);
    }
    .icon-system { background: #eff6ff; color: #3b82f6; }
    .icon-order { background: #ecfdf5; color: #10b981; }
    .icon-stock { background: #fff7ed; color: #f97316; }

    .notif-body { flex-grow: 1; padding-right: 50px; }
    .notif-title { font-weight: 700; color: var(--text-dark); margin-bottom: 5px; display: block; font-size: 1.05rem; }
    .notif-msg { color: var(--text-grey); font-size: 14px; line-height: 1.6; margin: 0; }
    .notif-time { font-size: 12px; color: #9ca3af; margin-top: 12px; display: block; font-weight: 500; }

    /* 单项操作 */
    .card-actions { position: absolute; right: 15px; top: 15px; display: flex; gap: 5px; }
    .icon-btn { background: none; border: none; color: #d1d5db; font-size: 18px; cursor: pointer; transition: 0.2s; padding: 5px; border-radius: 5px; }
    .icon-btn:hover { color: var(--theme-green); background: var(--theme-green-soft); }
    .btn-del:hover { color: #ef4444; background: #fef2f2; }

    .empty-box { text-align: center; padding: 80px 20px; background: #fff; border-radius: 20px; border: 2px dashed #e5e7eb; color: #9ca3af; }
</style>

<div class="notif-container">
    <div class="notif-header">
        <h2>Notifications</h2>
        <?php if (count($notifications) > 0): ?>
        <div class="header-actions">
            <form method="POST">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn-action">Mark all read</button>
            </form>
            <form method="POST">
                <input type="hidden" name="action" value="mark_all_unread">
                <button type="submit" class="btn-action">Mark all unread</button>
            </form>
            <form method="POST" onsubmit="return confirm('Clear all messages?');">
                <input type="hidden" name="action" value="delete_all">
                <button type="submit" class="btn-action btn-danger-outline">Clear All</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="notif-list">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $row): 
                $is_read = $row['is_read'];
                $type = $row['type'];
                $icon = ($type == 'order') ? '📦' : (($type == 'stock') ? '🔥' : '📢');
            ?>
                <div class="notif-card <?php echo $is_read ? 'read' : 'unread'; ?>">
                    <div class="card-actions">
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_read">
                            <input type="hidden" name="notif_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $is_read; ?>">
                            <button type="submit" class="icon-btn" title="<?php echo $is_read ? 'Mark as unread' : 'Mark as read'; ?>">
                                <?php echo $is_read ? '✉️' : '📖'; ?>
                            </button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete_single">
                            <input type="hidden" name="notif_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="icon-btn btn-del" onclick="return confirm('Delete?')">&times;</button>
                        </form>
                    </div>

                    <div class="notif-icon icon-<?php echo $type; ?>">
                        <?php echo $icon; ?>
                    </div>

                    <div class="notif-body">
                        <span class="notif-title"><?php echo htmlspecialchars($row['title']); ?></span>
                        <p class="notif-msg"><?php echo htmlspecialchars($row['message']); ?></p>
                        <span class="notif-time">
                            <i class="far fa-clock"></i> <?php echo date('M d, Y • g:i A', strtotime($row['created_at'])); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-box">
                <p style="font-size: 1.1rem; color: #4b5563;">All caught up!</p>
                <p>No new notifications at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/_foot.php'; ?>