<?php
require_once '../includes/db.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 检查管理员 Session
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

$aid = $_SESSION['admin_id'];
$current_role = 'admin';

// 后端逻辑
if (isset($_POST['action'])) {
    $notif_id = $_POST['notif_id'] ?? null;
    if ($_POST['action'] == 'mark_all_read') {
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND role = ?")->execute([$aid, $current_role]);
    } elseif ($_POST['action'] == 'mark_all_unread') {
        $pdo->prepare("UPDATE notifications SET is_read = 0 WHERE user_id = ? AND role = ?")->execute([$aid, $current_role]);
    } elseif ($_POST['action'] == 'delete_all') {
        $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND role = ?")->execute([$aid, $current_role]);
    } elseif ($_POST['action'] == 'delete_single') {
        $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?")->execute([$notif_id, $aid]);
    } elseif ($_POST['action'] == 'toggle_read') {
        $new_status = ($_POST['current_status'] == 1) ? 0 : 1;
        $pdo->prepare("UPDATE notifications SET is_read = ? WHERE id = ? AND user_id = ?")->execute([$new_status, $notif_id, $aid]);
    }
    header("Location: " . $_SERVER['PHP_SELF']); exit;
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND role = ? ORDER BY created_at DESC");
$stmt->execute([$aid, $current_role]);
$notifications = $stmt->fetchAll();

include '../includes/_admin_head.php'; 
?>

<style>
    :root { --theme-red: #d63031; --bg-light: #fdfcfc; }
    body { background-color: var(--bg-light); font-family: 'Inter', sans-serif; }
    .notif-container { max-width: 850px; margin: 40px auto; padding: 0 20px; }
    .notif-header h2 { font-weight: 800; color: #2d3436; margin: 0; }
    .btn-action { padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; border: 1px solid #ddd; background: #fff; cursor: pointer; transition: 0.2s; }
    .btn-action:hover { border-color: var(--theme-red); color: var(--theme-red); }
    .btn-danger { color: #fff; background: var(--theme-red); border: none; }
    .btn-danger:hover { opacity: 0.9; }

    .notif-card { background: #fff; border-radius: 16px; padding: 22px; margin-bottom: 16px; position: relative; display: flex; gap: 18px; border: 1px solid rgba(0,0,0,0.03); transition: 0.3s; }
    .unread { border-left: 5px solid var(--theme-red); box-shadow: 0 4px 15px rgba(214, 48, 49, 0.05); }
    .read { opacity: 0.6; background: #fafafa; border-left: 5px solid #b2bec3; }
    .notif-icon { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; background: #fff5f5; color: var(--theme-red); flex-shrink: 0; }
    .notif-body { flex-grow: 1; padding-right: 50px; }
    .card-actions { position: absolute; right: 15px; top: 15px; display: flex; gap: 5px; }
    .icon-btn { background: none; border: none; color: #d1d1d1; font-size: 18px; cursor: pointer; padding: 5px; }
    .icon-btn:hover { color: var(--theme-red); }
</style>

<div class="notif-container">
    <div class="notif-header d-flex justify-content-between align-items-center mb-4">
        <h2>Admin Center</h2>
        <div class="d-flex gap-2">
            <form method="POST"><input type="hidden" name="action" value="mark_all_read"><button class="btn-action">All Read</button></form>
            <form method="POST"><input type="hidden" name="action" value="mark_all_unread"><button class="btn-action">All Unread</button></form>
            <form method="POST" onsubmit="return confirm('Clear all?')"><input type="hidden" name="action" value="delete_all"><button class="btn-action btn-danger">Clear All</button></form>
        </div>
    </div>
    <?php foreach ($notifications as $row): $is_read = $row['is_read']; ?>
    <div class="notif-card <?php echo $is_read ? 'read' : 'unread'; ?>">
        <div class="card-actions">
            <form method="POST">
                <input type="hidden" name="action" value="toggle_read"><input type="hidden" name="notif_id" value="<?=$row['id']?>"><input type="hidden" name="current_status" value="<?=$is_read?>">
                <button type="submit" class="icon-btn"><?=$is_read ? '✉️' : '📖'?></button>
            </form>
            <form method="POST">
                <input type="hidden" name="action" value="delete_single"><input type="hidden" name="notif_id" value="<?=$row['id']?>">
                <button type="submit" class="icon-btn" onclick="return confirm('Delete?')">&times;</button>
            </form>
        </div>
        <div class="notif-icon"><?=($row['type']=='order'?'🛒':'⚠️')?></div>
        <div class="notif-body">
            <b class="notif-title"><?=htmlspecialchars($row['title'])?></b>
            <p class="notif-msg"><?=htmlspecialchars($row['message'])?></p>
            <small class="text-muted"><?=date('M d, H:i', strtotime($row['created_at']))?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php include '../includes/_admin_foot.php'; ?>