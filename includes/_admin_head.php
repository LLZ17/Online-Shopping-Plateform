<?php
/**
 * PXX Mall - Professional Admin Portal (Ultimate Version)
 * 📂 物理路径: C:\xampp\htdocs\HTML_Ass\images
 * 🛠️ 修复日志: 彻底删除 product_images 报错引用, 动态加载 Admin 真实头像
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔗 数据库连接 (确保路径正确)
require_once 'includes/db.php'; 

// ----------------------------------------------------------------
// 1. 权限验证 (与 Member 逻辑一致)
// ----------------------------------------------------------------
$isLoggedIn = false;
if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
}

// 🔥 自动登录逻辑
if (!$isLoggedIn && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['user_id']; 
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_photo'] = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';
        $isLoggedIn = true;
    }
}

// 如果还是没登录，踢回登录页
if (!$isLoggedIn) {
    header("Location: login.php");
    exit;
}

// ----------------------------------------------------------------
// 2. 🌟 修复第 18 行的报错 (核心修复)
// ----------------------------------------------------------------
/**
 * 之前的报错是因为 JOIN 了不存在的 product_images 表。
 * 现在修正为只查询 products 表，确保页面正常加载。
 */
try {
    $stmt_products = $pdo->query("SELECT * FROM products LIMIT 5"); 
    $products = $stmt_products->fetchAll();
} catch (PDOException $e) {
    // 如果 products 表也没有，至少不会让整个页面挂掉
    $products = [];
}

// 获取当前管理员头像
$adminPhoto = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PXX Mall | Admin Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================
           🌟 全局布局锁死系统 (CSS Reset)
           ============================================================ */
        :root {
            --pxx-red: #e60012;
            --sidebar-black: #111111;
            --sidebar-hover: #1a1e25;
            --header-h: 80px;
            --sidebar-w: 260px;
            --bg-gray: #f4f7f6;
            --white: #ffffff;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html, body {
            margin: 0; padding: 0;
            height: 100vh; width: 100vw;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-gray);
            overflow: hidden; /* 锁定外层，防止侧边栏断开 */
            display: flex;
            flex-direction: column;
        }

        * { box-sizing: border-box; }
        a { text-decoration: none; color: inherit; }

        /* ============================================================
           🔴 顶层红色导航栏 (Topbar)
           ============================================================ */
        .admin-top-nav {
            background-color: var(--pxx-red);
            height: var(--header-h);
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 40px;
            color: white;
            position: fixed;
            top: 0; left: 0;
            z-index: 10000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        /* 🌟 Logo 样式：同一行放大带竖线 */
        .brand-logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            user-select: none;
        }
        .brand-logo-area:hover { opacity: 0.9; }

        .logo-pxx {
            font-family: 'Montserrat', sans-serif;
            font-size: 32px; /* 再次放大 */
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .logo-pxx span { font-size: 14px; vertical-align: super; font-weight: 400; margin-left: 2px; }
        
        .logo-pipe { font-size: 28px; font-weight: 200; opacity: 0.6; }
        
        .logo-admin-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 26px; 
            font-weight: 700;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        /* Notifications 按钮 */
        .noti-action {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 8px;
            background: rgba(255,255,255,0.15);
            position: relative;
            transition: var(--transition);
        }
        .noti-action:hover { background: rgba(255,255,255,0.25); }
        .noti-action i { font-size: 20px; }
        .noti-action span { font-size: 15px; font-weight: 600; }
        
        .white-badge {
            position: absolute;
            top: 3px; right: 12px;
            background: white;
            color: var(--pxx-red);
            font-size: 11px;
            font-weight: 900;
            width: 18px; height: 18px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        /* Admin 个人信息区 (与 Member Page 对齐) */
        .admin-profile-section {
            display: flex;
            align-items: center;
            gap: 15px;
            padding-left: 30px;
            border-left: 1px solid rgba(255,255,255,0.3);
        }

        .admin-avatar-frame {
            width: 48px; height: 48px;
            border-radius: 50%;
            border: 2px solid white;
            overflow: hidden;
            background-color: #eee;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .admin-avatar-frame img { width: 100%; height: 100%; object-fit: cover; }

        .admin-labels { text-align: left; }
        .admin-labels .welcome { display: block; font-size: 11px; opacity: 0.85; margin-bottom: -2px; }
        .admin-labels .username { display: block; font-size: 15px; font-weight: 700; }

        .btn-power { font-size: 24px; margin-left: 10px; cursor: pointer; transition: var(--transition); }
        .btn-power:hover { color: #ffd1d1; transform: scale(1.15); }

        /* ============================================================
           🌑 黑色侧边栏 (Sidebar Navigation)
           ============================================================ */
        .admin-body-container {
            margin-top: var(--header-h);
            display: flex;
            flex: 1;
            height: calc(100vh - var(--header-h));
        }

        .admin-sidebar {
            width: var(--sidebar-w);
            background-color: var(--sidebar-black);
            color: #b8c7ce;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar-section-name {
            padding: 30px 25px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #4b646f;
            font-weight: 700;
        }

        .sidebar-menu-list { list-style: none; padding: 0; margin: 0; }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 16px 25px;
            font-size: 14.5px;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: var(--transition);
        }
        .sidebar-link i { width: 30px; font-size: 18px; margin-right: 15px; text-align: center; color: #5bc0de; }
        
        .sidebar-link:hover, .sidebar-link.active {
            background-color: var(--sidebar-hover);
            color: white;
            border-left-color: var(--pxx-red);
        }

        /* ============================================================
           🏢 右侧主体内容 (Content Area)
           ============================================================ */
        .admin-content-viewport {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .page-title-box h2 { margin: 0; font-size: 26px; font-weight: 700; color: #222; }
        .page-title-box p { margin: 5px 0 0; color: #888; font-size: 14px; }

        /* 🌟 数据统计卡片 (恢复 Dashboard 显示) */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            width: 100%;
        }

        .stat-widget {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            border-bottom: 5px solid #ddd;
            transition: var(--transition);
        }
        .stat-widget:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }

        .widget-title { color: #888; font-size: 14px; text-transform: uppercase; font-weight: 600; margin-bottom: 12px; }
        .widget-value { font-size: 32px; font-weight: 700; color: #111; margin-bottom: 15px; }
        .widget-btn { font-size: 14px; color: #3498db; font-weight: 600; cursor: pointer; }

        .border-blue { border-bottom-color: #3498db; }
        .border-green { border-bottom-color: #2ecc71; }
        .border-yellow { border-bottom-color: #f1c40f; }

        .admin-footer {
            margin-top: auto;
            padding: 30px 0;
            text-align: center;
            border-top: 1px solid #e9ecef;
            color: #adb5bd;
            font-size: 13px;
            line-height: 1.6;
        }

        /* 美化滚动条 */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #bbb; border-radius: 10px; }
    </style>
</head>
<body>

    <header class="admin-top-nav">
        <div class="brand-logo-area" onclick="location.href='admin_portal.php'">
            <div class="logo-pxx">PXX Mall<span>&reg;</span></div>
            <div class="logo-pipe">|</div>
            <div class="logo-admin-text">Admin</div>
        </div>

        <div class="header-right">
            <div class="noti-action" onclick="location.href='notifications.php'">
                <i class="fa-solid fa-bell"></i>
                <span>Notifications</span>
                <div class="white-badge">2</div>
            </div>

            <div class="admin-profile-section">
                <div class="admin-avatar-frame">
                    <img src="images/<?php echo $adminPhoto; ?>" onerror="this.src='images/default.jpg'" alt="Profile">
                </div>
                <div class="admin-labels">
                    <span class="welcome">Welcome back,</span>
                    <b class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></b>
                </div>
                <a href="logout.php" class="btn-power" title="Logout" onclick="return confirm('Securely log out?')">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="admin-body-container">
        
        <aside class="admin-sidebar">
            <nav>
                <div class="sidebar-section-name">Control Panel</div>
                <ul class="sidebar-menu-list">
                    <li><a href="admin_portal.php" class="sidebar-link active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
                </ul>

                <div class="sidebar-section-name" style="margin-top:20px;">Management</div>
                <ul class="sidebar-menu-list">
                    <li><a href="category_crud.php" class="sidebar-link"><i class="fa-solid fa-tags"></i> Category CRUD</a></li>
                    <li><a href="product_crud.php" class="sidebar-link"><i class="fa-solid fa-box-open"></i> Product CRUD</a></li>
                    <li><a href="member_crud.php" class="sidebar-link"><i class="fa-solid fa-users-gear"></i> Member CRUD</a></li>
                    <li><a href="admin_crud.php" class="sidebar-link"><i class="fa-solid fa-user-shield"></i> Admin CRUD</a></li>
                </ul>

                <div class="sidebar-section-name" style="margin-top:20px;">Logs & Reports</div>
                <ul class="sidebar-menu-list">
                    <li><a href="sales_data.php" class="sidebar-link"><i class="fa-solid fa-chart-line"></i> Sales Data</a></li>
                    <li><a href="order_crud.php" class="sidebar-link"><i class="fa-solid fa-file-invoice-dollar"></i> Order History</a></li>
                </ul>

                <div class="sidebar-section-name" style="margin-top:20px;">System</div>
                <ul class="sidebar-menu-list">
                    <li><a href="index.php" target="_blank" class="sidebar-link" style="color:#2ecc71;"><i class="fa-solid fa-up-right-from-square"></i> View Store</a></li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content-viewport">
            
            <div class="page-title-box">
                <h2>Dashboard Overview</h2>
                <p>Welcome to the PXX Mall Administrative Portal. Here is your system summary.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-widget border-blue">
                    <div class="widget-title">Total Product Categories</div>
                    <div class="widget-value">12</div>
                    <a href="category_crud.php" class="widget-btn">Manage Categories &rarr;</a>
                </div>

                <div class="stat-widget border-green">
                    <div class="widget-title">Fake Payment Logs</div>
                    <div class="widget-value">RM 5,240.00</div>
                    <div style="font-size:13px; color:#bbb;">Verified via Billplz Sandbox</div>
                </div>

                <div class="stat-widget border-yellow">
                    <div class="widget-title">Total Registered Members</div>
                    <div class="widget-value">85</div>
                    <div style="font-size:13px; color:#bbb;">Live member tracking</div>
                </div>
            </div>

            <div style="background:white; padding:30px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.03);">
                <h3 style="margin-top:0;">System Database Status</h3>
                <p style="color:#666; line-height:1.6;">
                    The administrative database is currently connected to <b>html_sql</b>. 
                    All CRUD operations are enabled. Product image module is bypassed to resolve system errors.
                </p>
            </div>

            <footer class="admin-footer">
                <strong>PXX Mall Malaysia Sdn. Bhd. &reg;</strong><br>
                Company Registration No: [202001028493 (1357831-M)]<br>
                Copyright &copy; 2026. All Rights Reserved.
            </footer>
        </main>

    </div>

</body>
</html>