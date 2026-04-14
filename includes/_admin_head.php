<?php
/**
 * =================================================================================
 * PXX MALL - ADMINISTRATIVE CONTROL PORTAL (V110.2 - ALERT REMOVED)
 * =================================================================================
 * 核心逻辑：
 * 1. [新增] 在 View Store 上方加入了 View Database 链接，直连 phpMyAdmin。
 * 2. [移除] 彻底移除了顶部的 System Status 弹窗通知栏。
 * 3. [侧边栏] 补全白色图标，文字纯白，固定左侧不走位。
 * 4. [权限] 包含完整的安全架构与权限判定。
 * =================================================================================
 */

// --- [STAGE 1: 核心初始化] ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [STAGE 2: 数据库连接] ---
require_once '../includes/db.php'; 

// --- [STAGE 3: 严格权限检查] ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../admin/login.php"); exit();
}

$user_role = trim($_SESSION['role']);
if (!in_array($user_role, ['Admin', 'S.Admin'])) {
    header("Location: ../admin/login.php"); exit();
}

// --- [STAGE 4: 路径定义与用户信息] ---
$path_prefix = '../'; 
$current_admin = htmlspecialchars($_SESSION['username']);
$current_photo = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
$portal_title = ($user_role === 'S.Admin') ? "Super Admin" : "Admin Portal";

// 模拟通知数量
$noti_count = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PXX Mall® | Admin Portal <?php echo $portal_title; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================
           [STYLE SYSTEM] 严格配色逻辑
           ============================================================ */
        :root {
            --pxx-red: #e60012;
            --side-bg: #111111;
            --side-h: #1a1e25;
            --white: #ffffff;
            --bg-canvas: #f4f7f6;
            --header-h: 85px;
            --side-w: 280px;
            --trans: all 0.3s ease;
        }

        body, html {
            margin: 0; padding: 0; height: 100vh; width: 100vw;
            font-family: 'Poppins', sans-serif; background-color: var(--bg-canvas);
            overflow: hidden; 
        }

        * { box-sizing: border-box; }
        a { text-decoration: none; color: inherit; }

        /* ============================================================
           顶部红色导航 (Header)
           ============================================================ */
        .admin-master-header {
            background-color: var(--pxx-red);
            height: var(--header-h); width: 100%;
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; color: var(--white);
            position: fixed; top: 0; left: 0; z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header-brand { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .logo-main { font-family: 'Montserrat', sans-serif; font-size: 30px; font-weight: 900; letter-spacing: -1px; }
        .logo-r { font-size: 12px; vertical-align: super; font-weight: 600; }
        .logo-portal { font-family: 'Montserrat', sans-serif; font-size: 20px; font-weight: 700; opacity: 0.95; }

        .header-actions { display: flex; align-items: center; gap: 30px; }

        /* Notification */
        .noti-wrapper { position: relative; }
        .noti-btn {
            background: rgba(255,255,255,0.15); padding: 12px 18px; border-radius: 10px;
            display: flex; align-items: center; gap: 8px; cursor: pointer; border: none; color: white;
            position: relative; transition: var(--trans);
        }
        .noti-badge {
            position: absolute; top: -5px; right: -5px; background: white; color: var(--pxx-red);
            font-size: 11px; font-weight: 900; width: 20px; height: 20px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }

        .noti-preview {
            position: absolute; top: calc(100% + 15px); right: 0;
            width: 300px; background: white; border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1); border: 1px solid #eee;
            visibility: hidden; opacity: 0; transform: translateY(10px);
            transition: var(--trans); z-index: 10001; overflow: hidden; color: #333;
        }
        .noti-wrapper:hover .noti-preview { visibility: visible; opacity: 1; transform: translateY(0); }
        .preview-h { padding: 15px 20px; font-weight: 800; border-bottom: 1px solid #f5f5f5; font-size: 13px; }
        .preview-item { padding: 15px 20px; border-bottom: 1px solid #f9f9f9; display: flex; gap: 12px; font-size: 12px; color: #666; }
        .preview-item i { color: var(--pxx-red); }

        .profile-wrap { display: flex; align-items: center; gap: 15px; padding-left: 25px; border-left: 1px solid rgba(255,255,255,0.2); }
        .admin-avatar { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #fff; object-fit: cover; }
        .logout-icon { font-size: 20px; color: white; margin-left: 10px; cursor: pointer; }

        /* ============================================================
           黑色固定侧边栏 (Fixed Sidebar)
           ============================================================ */
        .sidebar-fixed {
            width: var(--side-w); background-color: var(--side-bg);
            position: fixed; top: var(--header-h); left: 0; bottom: 0;
            display: flex; flex-direction: column; padding-top: 15px; z-index: 9000;
            overflow-y: auto;
        }

        .s-item, .s-drop-h {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 25px; font-size: 15px; font-weight: 600;
            color: var(--white); border-left: 6px solid transparent; cursor: pointer;
        }
        .s-item:hover, .s-drop-h:hover, .s-item.active { background-color: var(--side-h); border-left-color: var(--pxx-red); }

        .s-icon-box { display: flex; align-items: center; gap: 15px; }
        .s-icon-box i { width: 25px; text-align: center; color: var(--white); font-size: 18px; }

        .s-sub-box { max-height: 0; overflow: hidden; background-color: #080808; transition: max-height 0.4s ease; }
        .s-sub-box.open { max-height: 400px; }
        .sub-nav-link { display: block; padding: 12px 25px 12px 65px; font-size: 14px; color: #aaa; }
        .sub-nav-link:hover { color: white; background: #111; }

        /* 左下角底部链接区域 */
        .sidebar-bottom { margin-top: auto; padding: 20px 25px; border-top: 1px solid #222; }
        .sidebar-bottom a { 
            display: flex; align-items: center; gap: 12px; 
            color: white; font-size: 14px; font-weight: 700; 
            opacity: 0.8; margin-bottom: 15px; 
            transition: var(--trans);
        }
        .sidebar-bottom a:last-child { margin-bottom: 0; }
        .sidebar-bottom a:hover { opacity: 1; color: #2ecc71; }
        .sidebar-bottom a.db-link:hover { color: #3498db; }

        /* ============================================================
           主内容区域
           ============================================================ */
        .main-content-view {
            margin-top: var(--header-h); /* 🔥 新增：补偿移除弹窗后的高度，防止内容被遮挡 */
            margin-left: var(--side-w);
            padding: 50px;
            height: calc(100vh - var(--header-h));
            overflow-y: auto;
            display: flex; flex-direction: column;
        }

        .empty-canvas {
            flex: 1; display: flex; justify-content: center; align-items: center;
            color: #ddd; text-align: center;
        }
        .empty-canvas i { font-size: 100px; opacity: 0.2; }
    </style>
</head>
<body>

    <header class="admin-master-header">
        <div class="header-brand" onclick="location.href='../admin/portal.php'">
            <div class="logo-main">PXX Mall<span class="logo-r">&reg;</span></div>
            <div class="logo-portal">| Admin Portal</div>
        </div>

        <div class="header-actions">
            <div class="noti-wrapper">
                <button class="noti-btn" onclick="location.href='../admin/notifications.php'">
                    <i class="fa-solid fa-bell"></i>
                    <span>Alerts</span>
                    <div class="noti-badge"><?php echo $noti_count; ?></div>
                </button>
                <div class="noti-preview">
                    <div class="preview-h">Recent Updates</div>
                    <div class="preview-item"><i class="fa-solid fa-circle-check"></i> System modules synchronized.</div>
                    <div class="preview-item"><i class="fa-solid fa-user-shield"></i> Security tunnel verified.</div>
                </div>
            </div>

            <div class="profile-wrap">
                <img src="<?php echo $path_prefix.'images/'.$current_photo; ?>" onerror="this.src='../images/default.jpg'" class="admin-avatar">
                <div style="text-align:left;">
                    <span style="display:block; font-size:11px; opacity:0.8; font-weight:700;"><?php echo strtoupper($user_role); ?></span>
                    <b style="font-size:15px;"><?php echo $current_admin; ?></b>
                </div>
                <a href="../admin/logout.php" onclick="return confirm('Logout current session?')"><i class="fa-solid fa-power-off logout-icon"></i></a>
            </div>
        </div>
    </header>

    <aside class="sidebar-fixed">
        <nav>
            <a href="../admin/portal.php" class="s-item active">
                <div class="s-icon-box">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </div>
            </a>

            <?php if($user_role === 'S.Admin'): ?>
            <div class="s-group">
                <div class="s-drop-h" onclick="this.nextElementSibling.classList.toggle('open')">
                    <div class="s-icon-box">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Admin Management</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-box">
                    <a href="../admin/admin_crud.php" class="sub-nav-link">Admin CRUD</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="s-group">
                <div class="s-drop-h" onclick="this.nextElementSibling.classList.toggle('open')">
                    <div class="s-icon-box">
                        <i class="fa-solid fa-users"></i>
                        <span>Member Management</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-box">
                    <a href="../admin/member_crud.php" class="sub-nav-link">Member CRUD</a>
                    <a href="../admin/order_history.php" class="sub-nav-link">Order History</a>
                </div>
            </div>

            <div class="s-group">
                <div class="s-drop-h" onclick="this.nextElementSibling.classList.toggle('open')">
                    <div class="s-icon-box">
                        <i class="fa-solid fa-store"></i>
                        <span>PXX Mall Details</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-box">
                    <a href="../admin/product_crud.php" class="sub-nav-link">Product CRUD</a>
                    <a href="../admin/category_crud.php" class="sub-nav-link">Category CRUD</a>
                    <a href="../admin/order-crud.php" class="sub-nav-link">Sales Data</a>
                </div>
            </div>
        </nav>

        <div class="sidebar-bottom">
            <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=html_sql" target="_blank" class="db-link">
                <i class="fa-solid fa-database"></i>
                <span>View Database</span>
            </a>
            
            <a href="../index.php" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>View Store</span>
            </a>
        </div>
    </aside>

    <main class="main-content-view">
        <div class="empty-canvas">
            <div>
                <i class="fa-solid fa-briefcase"></i>
                <p style="color:#aaa; font-weight:600; margin-top:20px;">Administrative Workspace Ready. Select a module to begin.</p>
            </div>
        </div>

    <?php
    $path1 = __DIR__ . '/includes/_admin_foot.php';
    $path2 = __DIR__ . '/../includes/_admin_foot.php';

    if (file_exists($path1)) {
         include_once $path1;
    } elseif (file_exists($path2)) {
         include_once $path2;
    }
    ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentPath = window.location.href;
            document.querySelectorAll('.sub-nav-link').forEach(link => {
                if (currentPath.includes(link.getAttribute('href'))) {
                    const box = link.closest('.s-sub-box');
                    if(box) {
                        box.classList.add('open');
                        box.previousElementSibling.classList.add('active');
                    }
                }
            });
        });
    </script>

</body>
</html>