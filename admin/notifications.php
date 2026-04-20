<?php
// PXX Mall - Notification Center (Universal Admin Edition)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pxx_root = dirname(__DIR__);
require_once $pxx_root . '/includes/db.php'; 

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 权限检查
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php"); exit;
}

// 这里的变量直接跟着 Session 走，确保 100% 匹配
$aid = $_SESSION['user_id']; 
$current_role = $_SESSION['role']; 

// --- 后端处理逻辑 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    try {
        if ($action === 'mark_all_read') {
            $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND role = ?")->execute([$aid, $current_role]);
        } 
        elseif ($action === 'mark_all_unread') {
            $pdo->prepare("UPDATE notifications SET is_read = 0 WHERE user_id = ? AND role = ?")->execute([$aid, $current_role]);
        }
        elseif ($action === 'delete_all') {
            $pdo->prepare("DELETE FROM notifications WHERE user_id = ? AND role = ?")->execute([$aid, $current_role]);
        }
        elseif ($action === 'toggle_read') {
            $new_status = ($_POST['current_status'] == 1) ? 0 : 1;
            $pdo->prepare("UPDATE notifications SET is_read = ? WHERE id = ?")->execute([$new_status, $_POST['notif_id']]);
        }
        header("Location: notifications.php"); exit;
    } catch (PDOException $e) { $errorMsg = $e->getMessage(); }
}

// --- 抓取列表 (修正了 WHERE 条件) ---
try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND role = ? ORDER BY created_at DESC");
    $stmt->execute([$aid, $current_role]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $notifications = []; }

$admin_head = $pxx_root . '/includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');
    
    .notif-container { width: 100%; box-sizing: border-box; padding: 20px; font-family: 'Plus Jakarta Sans', sans-serif; }
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
    .page-header h1 { font-size: 28px; font-weight: 900; color: #0f172a; margin: 0; }

    .notif-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; padding: 20px; margin-bottom: 15px; display: flex; gap: 18px; position: relative; }
    .unread-indicator { position: absolute; left: 0; top: 20px; bottom: 20px; width: 4px; background: #ef4444; border-radius: 0 4px 4px 0; }
    .is-read { opacity: 0.5; filter: grayscale(0.5); }

    .icon-box { width: 50px; height: 50px; border-radius: 14px; background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .notif-info { flex-grow: 1; }
    .notif-info h5 { margin: 0 0 6px 0; font-size: 17px; font-weight: 800; color: #1e293b; }
    .notif-info p { margin: 0; color: #64748b; font-size: 14px; line-height: 1.6; }
    .notif-time { display: block; margin-top: 12px; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }

    .btn-op { padding: 8px 16px; border-radius: 10px; font-weight: 800; font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.2; border: none; }
    .btn-white { background: #fff; border: 1px solid #e2e8f0; color: #475569; }
    .btn-danger { background: #fee2e2; color: #b91c1c; }
</style>

<div class="notif-container" id="notif-content-area">
    <div class="page-header">
        <h1>Notifications Center</h1>
        <div style="display: flex; gap: 12px;">
            <form method="POST">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn-op btn-white"><span>✅</span> Mark Read</button>
            </form>
            <form method="POST">
                <input type="hidden" name="action" value="mark_all_unread">
                <button type="submit" class="btn-op btn-white"><span>📩</span> Mark Unread</button>
            </form>
            <form method="POST" onsubmit="return confirm('Clear all?')">
                <input type="hidden" name="action" value="delete_all">
                <button type="submit" class="btn-op btn-danger"><span>🗑️</span> Clear All</button>
            </form>
        </div>
    </div>

    <div class="notif-list">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $n): 
                $unread = !$n['is_read'];
                $icon = (stripos($n['type'], 'order') !== false) ? '📦' : ((stripos($n['type'], 'stock') !== false) ? '⚠️' : '🔔');
            ?>
                <div class="notif-card <?= $unread ? '' : 'is-read' ?>">
                    <?php if ($unread): ?><div class="unread-indicator"></div><?php endif; ?>
                    <div class="icon-box"><?= $icon ?></div>
                    <div class="notif-info">
                        <h5><?= htmlspecialchars($n['title']) ?></h5>
                        <p><?= htmlspecialchars($n['message']) ?></p>
                        <span class="notif-time"><?= date('D, d M Y | h:i A', strtotime($n['created_at'])) ?></span>
                    </div>
                    <div class="actions" style="display:flex; align-items:center;">
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_read">
                            <input type="hidden" name="notif_id" value="<?= $n['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $n['is_read'] ?>">
                            <button type="submit" style="background:none; border:none; cursor:pointer; font-size:18px; color:#cbd5e1;">
                                <i class="fas <?= $unread ? 'fa-envelope' : 'fa-envelope-open' ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 60px; background: #fff; border-radius: 20px; border: 2px dashed #e2e8f0;">
                <h3 style="color: #1e293b;">No Data Found</h3>
                <p style="color: #64748b;">Current Filter: ID <b><?= $aid ?></b> | Role <b><?= $current_role ?></b></p>
                <p style="font-size: 12px; color: #94a3b8; margin-top: 10px;">Check if your SQL data matches exactly.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main, #content-page');
    let container = document.getElementById('notif-content-area');
    if (viewport && container) {
        viewport.innerHTML = ''; 
        viewport.appendChild(container);
    }
});
</script>

<?php 
$foot = $pxx_root . '/includes/_admin_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>