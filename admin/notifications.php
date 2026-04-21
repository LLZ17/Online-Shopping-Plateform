<?php
// PXX Mall - Notification Center (Session-Based Edition)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pxx_root = dirname(__DIR__);
require_once $pxx_root . '/includes/db.php'; 

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// --- 💡 核心逻辑：从 Session 动态获取 ---
// 只要你登录的是谁，这里就会自动变成谁，不用再手动改代码
$aid = $_SESSION['user_id'] ?? null; 
$current_role = $_SESSION['role'] ?? null; 

// 如果没登录，直接跳回登录页
if (!$aid) {
    header("Location: login.php");
    exit;
}

// --- 处理 Action ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    try {
        if ($action === 'mark_all_read') {
            $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$aid]);
        } 
        elseif ($action === 'mark_all_unread') {
            $pdo->prepare("UPDATE notifications SET is_read = 0 WHERE user_id = ?")->execute([$aid]);
        }
        elseif ($action === 'delete_all') {
            $pdo->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$aid]);
        }
        header("Location: notifications.php"); exit;
    } catch (PDOException $e) { $error = $e->getMessage(); }
}

// --- 抓取列表：精准匹配当前登录的用户 ---
try {
    // 根据当前登录的 user_id 来查询，确保每个人看自己的
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$aid]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $notifications = []; }

$admin_head = $pxx_root . '/includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    .notif-container { width: 100%; padding: 25px; box-sizing: border-box; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .notif-card { background: #fff; border-radius: 18px; padding: 20px; margin-bottom: 15px; border: 1px solid #f1f5f9; display: flex; gap: 15px; position: relative; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: 0.2s; }
    .notif-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
    .unread-bar { position: absolute; left: 0; top: 20px; bottom: 20px; width: 4px; background: #ff4d4d; border-radius: 0 4px 4px 0; }
    .is-read { opacity: 0.6; filter: grayscale(0.8); }
    .btn-notif { padding: 8px 16px; border-radius: 10px; border: 1px solid #e2e8f0; background: white; font-weight: 800; cursor: pointer; font-size: 13px; }
</style>

<div id="notif-content-area" class="notif-container">
    <div class="page-header">
        <h1 style="margin:0; font-weight:900;">Notification Center</h1>
        <div style="display: flex; gap: 10px;">
            <form method="POST"><input type="hidden" name="action" value="mark_all_read"><button class="btn-notif">✅ Mark Read</button></form>
            <form method="POST"><input type="hidden" name="action" value="mark_all_unread"><button class="btn-notif">📩 Mark Unread</button></form>
            <form method="POST"><input type="hidden" name="action" value="delete_all"><button class="btn-notif" style="color:#ef4444; border-color:#fee2e2;">🗑️ Clear All</button></form>
        </div>
    </div>

    <div class="notif-list">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $n): ?>
                <div class="notif-card <?= $n['is_read'] ? 'is-read' : '' ?>">
                    <?php if (!$n['is_read']): ?><div class="unread-bar"></div><?php endif; ?>
                    <div style="font-size:24px; background:#f8fafc; padding:10px; border-radius:12px;">
                        <?= strpos($n['type'], 'order') !== false ? '📦' : (strpos($n['type'], 'stock') !== false ? '⚠️' : '🔔') ?>
                    </div>
                    <div style="flex-grow:1;">
                        <h4 style="margin:0; font-size:17px; font-weight:800; color:#1e293b;"><?= htmlspecialchars($n['title']) ?></h4>
                        <p style="margin:5px 0; color:#64748b; font-size:14px;"><?= htmlspecialchars($n['message']) ?></p>
                        <small style="color:#94a3b8; font-weight:700;"><?= date('d M, h:i A', strtotime($n['created_at'])) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:100px; background:white; border-radius:24px; border:2px dashed #e2e8f0;">
                <h3 style="color:#94a3b8;">No notifications for ID: <?= htmlspecialchars($aid) ?></h3>
                <p style="color:#cbd5e1;">Your workspace is currently synced and clear.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 强制清除 "Workspace Ready" 占位符
    let vp = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    let content = document.getElementById('notif-content-area');
    if (vp && content) {
        vp.innerHTML = '';
        vp.appendChild(content);
    }
});
</script>

<?php 
$foot = $pxx_root . '/includes/_admin_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>