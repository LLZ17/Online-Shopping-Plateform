<?php
// 1. 引入你的 PDO 数据库连接
require_once '../includes/db.php'; 

// 2. Session 管理
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 检查登录状态 (确保 Session 名与你登录页一致)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$current_role = 'member'; 

// --- 3. 后端处理逻辑 (使用 PDO) ---

// Action: 全部标记为已读
if (isset($_POST['action']) && $_POST['action'] == 'mark_all_read') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND role = ?");
    $stmt->execute([$uid, $current_role]);
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit;
}

// Action: 一键删除所有通知
if (isset($_POST['action']) && $_POST['action'] == 'delete_all') {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND role = ?");
    $stmt->execute([$uid, $current_role]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- 4. 获取通知列表 ---
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND role = ? ORDER BY created_at DESC");
$stmt->execute([$uid, $current_role]);
$notifications = $stmt->fetchAll();

// 引入头部
include '../includes/_head.php'; 
?>

<style>
    .notice-wrapper { max-width: 900px; margin: 30px auto; min-height: 500px; }
    .notice-item { border-radius: 10px; margin-bottom: 15px; border: 1px solid #f0f0f0; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    .notice-item.unread { border-left: 5px solid #007bff; background: #ffffff; }
    .notice-item.read { opacity: 0.65; background: #fdfdfd; border-left: 5px solid #dee2e6; }
    .type-tag { font-size: 10px; padding: 3px 10px; border-radius: 20px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; display: inline-block; }
    .tag-system { background: #e3f2fd; color: #0d47a1; }
    .tag-order { background: #e8f5e9; color: #1b5e20; }
    .tag-stock { background: #fff3e0; color: #e65100; }
    .empty-state { padding: 80px 0; color: #adb5bd; border: 1px dashed #dee2e6; }
</style>

<div class="container">
    <div class="notice-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 font-weight-bold">Notifications</h3>
            <div>
                <?php if (count($notifications) > 0): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="btn btn-sm btn-light border mr-2">Mark all as read</button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Clear all messages?');">
                        <input type="hidden" name="action" value="delete_all">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Clear All</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="notification-list">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $row): ?>
                    <div class="card notice-item <?php echo $row['is_read'] ? 'read' : 'unread'; ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="type-tag tag-<?php echo htmlspecialchars($row['type']); ?>">
                                        <?php 
                                            $types = ['system' => 'System', 'order' => 'Order', 'stock' => 'Restock'];
                                            echo $types[$row['type']] ?? 'General'; 
                                        ?>
                                    </span>
                                    <h5 class="h6 mb-2 font-weight-bold text-dark"><?php echo htmlspecialchars($row['title']); ?></h5>
                                </div>
                                <small class="text-muted"><?php echo date('M d, H:i', strtotime($row['created_at'])); ?></small>
                            </div>
                            <p class="mb-0 text-muted" style="font-size: 14.5px;">
                                <?php echo htmlspecialchars($row['message']); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state text-center bg-white rounded shadow-sm">
                    <p class="mb-0">You're all caught up! No new notifications.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// 引入页脚
include '../includes/_foot.php'; 
?>