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