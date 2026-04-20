<?php
/**
 * =================================================================================
 * PXX MALL - ADMINISTRATIVE CONTROL PORTAL (V110.6 - BADGE POSITION FIXED)
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once '../includes/db.php'; 

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../admin/login.php"); exit();
}

$user_role = trim($_SESSION['role']);
if (!in_array($user_role, ['Admin', 'S.Admin'])) {
    header("Location: ../admin/login.php"); exit();
}

$path_prefix = '../'; 
$current_admin = htmlspecialchars($_SESSION['username']);
$current_photo = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
$portal_title = ($user_role === 'S.Admin') ? "Super Admin" : "Admin Portal";

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
        :root {
            --pxx-red: #e60012;
            --side-bg: #111111;
            --side-h: #1a1e25;
            --white: #ffffff;
            --bg-canvas: #f4f7f6;
            --header-h: 90px;
            --side-w: 280px;
            --trans: all 0.3s ease;
            --toyyib-orange: #f39c12;
        }

        body, html {
            margin: 0; padding: 0; height: 100vh; width: 100vw;
            font-family: 'Poppins', sans-serif; background-color: var(--bg-canvas);
            overflow: hidden; 
        }
        * { box-sizing: border-box; }
        a { text-decoration: none; color: inherit; }

        /* --- Header --- */
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

        /* --- Notifications 按钮修正版 --- */
        .header-actions { display: flex; align-items: center; gap: 30px; }

        .noti-wrapper { position: relative; }
        .noti-btn {
            background: rgba(255,255,255,0.2); 
            padding: 8px 20px; 
            border-radius: 12px;
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center;
            cursor: pointer; 
            border: none; 
            color: white;
            transition: var(--trans);
            min-width: 130px;
        }
        .noti-btn:hover { background: rgba(255,255,255,0.3); }

        /* 图标容器，用于精准定位数字 */
        .icon-relative {
            position: relative;
            display: inline-block;
            line-height: 1;
        }
        .noti-btn i { font-size: 28px; margin-top: 5px; } 

        .noti-btn span { 
            font-family: 'Montserrat', sans-serif; 
            font-weight: 900; 
            font-size: 14px; 
            text-transform: uppercase;
            margin-top: 2px;
        }
        
        /* 数字角标：现在相对于 .icon-relative 定位 */
        .noti-badge {
            position: absolute; 
            top: -2px;      /* 相对铃铛顶部 */
            right: -8px;    /* 相对铃铛右侧 */
            background: white; 
            color: var(--pxx-red);
            font-size: 11px; 
            font-weight: 900; 
            width: 18px; 
            height: 18px; 
            border-radius: 50%;
            display: flex; 
            align-items: center; 
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
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
        .preview-item { padding: 12px 20px; border-bottom: 1px solid #f9f9f9; display: flex; gap: 12px; font-size: 12px; color: #666; }

        /* --- Profile --- */
        .profile-wrap { display: flex; align-items: center; gap: 15px; padding-left: 25px; border-left: 1px solid rgba(255,255,255,0.2); }
        .admin-avatar { width: 48px; height: 48px; border-radius: 50%; border: 2px solid #fff; object-fit: cover; }
        .logout-icon { font-size: 22px; color: white; margin-left: 10px; cursor: pointer; }

        /* --- Sidebar (全部保留) --- */
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

        .sidebar-bottom { margin-top: auto; padding: 20px 25px; border-top: 1px solid #222; }
        .sidebar-bottom a { display: flex; align-items: center; gap: 12px; color: white; font-size: 14px; font-weight: 700; opacity: 0.8; margin-bottom: 15px; transition: var(--trans); }
        .sidebar-bottom a.tp-link:hover { color: var(--toyyib-orange); opacity: 1; }
        .sidebar-bottom a.db-link:hover { color: #3498db; opacity: 1; }
        .sidebar-bottom a.store-link:hover { color: #2ecc71; opacity: 1; }

        /* Main View */
        .main-content-view {
            margin-top: var(--header-h); margin-left: var(--side-w);
            padding: 50px; height: calc(100vh - var(--header-h));
            overflow-y: auto; display: flex; flex-direction: column;
        }
        .empty-canvas { flex: 1; display: flex; justify-content: center; align-items: center; color: #ddd; text-align: center; }
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
                    <div class="icon-relative">
                        <i class="fa-solid fa-bell"></i>
                        <div class="noti-badge"><?php echo $noti_count; ?></div>
                    </div>
                    <span>Notifications</span>
                </button>
                <div class="noti-preview">
                    <div class="preview-h">System Updates</div>
                    <div class="preview-item"><i class="fa-solid fa-circle-check" style="color:var(--pxx-red)"></i> Database connected.</div>
                    <div class="preview-item"><i class="fa-solid fa-bolt" style="color:var(--pxx-red)"></i> ToyyibPay status: OK.</div>
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
                        <span>My Admin</span>
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
                        <span>My Member</span>
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
                        <span>My PXX Mall</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-box">
                    <a href="../admin/product_crud.php" class="sub-nav-link">Product CRUD</a>
                    <a href="../admin/category_crud.php" class="sub-nav-link">Category CRUD</a>
                    <a href="../admin/order_crud.php" class="sub-nav-link">Sales Data</a>
                </div>
            </div>
        </nav>

        <div class="sidebar-bottom">
            <a href="https://dev.toyyibpay.com/dashboard" target="_blank" class="tp-link">
                <i class="fa-solid fa-credit-card"></i>
                <span>My ToyyibPay System</span>
            </a>
            <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=html_sql" target="_blank" class="db-link">
                <i class="fa-solid fa-database"></i>
                <span>My Database</span>
            </a>
            <a href="../index.php" target="_blank" class="store-link">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>My Store</span>
            </a>
        </div>
    </aside>

    <main class="main-content-view">
        <div class="empty-canvas">
            <div>
                <i class="fa-solid fa-briefcase"></i>
                <p style="color:#aaa; font-weight:600; margin-top:20px;">Workspace Ready.</p>
            </div>
        </div>
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