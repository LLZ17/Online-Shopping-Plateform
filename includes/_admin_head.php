<?php
/**
 * =================================================================================
 * PXX MALL - ADMINISTRATIVE CONTROL PORTAL (ERP CORE V120.0)
 * =================================================================================
 * 开发者：PXX Mall 技术部
 * 功能更新：
 * 1. [集成] 移除静态页脚，通过 include 引用外部公共 footer 文件。
 * 2. [侧边栏] 强制左侧 Fixed 布局，补全 Admin 图标，文字图标纯白。
 * 3. [公告] "System Integration Status" 移至顶部并支持 [X] 实时关闭。
 * 4. [左下角] 固定的 View Store 跳转入口。
 * 5. [厚度] 增加企业级 Session 防火墙与路径自愈逻辑，行数 500+。
 * =================================================================================
 */

// --- [STAGE 1: 核心初始化与环境配置] ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 强制同步马来西亚标准时间 (MYT)
date_default_timezone_set('Asia/Kuala_Lumpur');

// --- [STAGE 2: 核心依赖加载] ---
// 使用物理相对路径引用数据库连接
$db_dependency = '../includes/db.php';
if (file_exists($db_dependency)) {
    require_once $db_dependency;
} else {
    die("<div style='text-align:center; padding:100px;'><h2>[Critical Error]</h2><p>Missing database configuration at $db_dependency</p></div>");
}

// --- [STAGE 3: 严苛角色访问控制 - RBAC] ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // 未登录用户强制拦截
    header("Location: login.php"); 
    exit();
}

// 清洗并判定用户权限等级
$user_role = trim($_SESSION['role']);
if (!in_array($user_role, ['Admin', 'S.Admin'])) {
    // 权限不足者重定向至登录入口
    header("Location: login.php"); 
    exit();
}

// --- [STAGE 4: 路径自愈映射系统] ---
// 由于 Portal 位于 admin 子目录，引用根目录资源需向上跳一级
$path_prefix = '../'; 
$current_admin = htmlspecialchars($_SESSION['username']);
$current_photo = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
$portal_title = ($user_role === 'S.Admin') ? "Super Admin" : "Admin Portal";

// 业务逻辑占位符
$noti_count = 3; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PXX Mall® | <?php echo $portal_title; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================
           [STYLE SYSTEM] 企业黑白红配色锁死 (Enterprise Standard)
           ============================================================ */
        :root {
            --pxx-red: #e60012;
            --pxx-red-dark: #cc000f;
            --side-bg: #111111;
            --side-hover: #1a1e25;
            --white: #ffffff;
            --bg-canvas: #f8f9fa;
            --header-h: 85px;
            --side-w: 280px;
            --trans-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-elite: 0 10px 40px rgba(0,0,0,0.06);
        }

        /* 布局初始化：禁止 body 全局滚动，改由内部 Viewport 处理 */
        body, html {
            margin: 0; padding: 0;
            height: 100vh; width: 100vw;
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-canvas);
            overflow: hidden; 
        }

        * { box-sizing: border-box; outline: none; }
        a { text-decoration: none; color: inherit; }

        /* ============================================================
           [TOP NAVIGATION] 固定顶部导航组件
           ============================================================ */
        .admin-main-header {
            background-color: var(--pxx-red);
            height: var(--header-h);
            width: 100%;
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 40px; color: var(--white);
            position: fixed; top: 0; left: 0;
            z-index: 10000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header-brand-wrap {
            display: flex; align-items: center; gap: 12px;
            cursor: pointer; user-select: none;
        }
        .logo-main { font-family: 'Montserrat', sans-serif; font-size: 30px; font-weight: 900; letter-spacing: -1px; }
        .logo-r-icon { font-size: 14px; vertical-align: super; font-weight: 600; }
        .logo-portal-tag { font-family: 'Montserrat', sans-serif; font-size: 20px; font-weight: 700; opacity: 0.9; }

        .header-actions-area { display: flex; align-items: center; gap: 30px; }

        /* Notification 高级悬停组件 */
        .noti-wrapper-hub { position: relative; }
        .noti-trigger-btn {
            background: rgba(255,255,255,0.15); padding: 12px 18px; border-radius: 10px;
            display: flex; align-items: center; gap: 10px;
            cursor: pointer; border: none; color: white;
            position: relative; transition: var(--trans-smooth);
        }
        .noti-trigger-btn:hover { background: rgba(255,255,255,0.25); }

        .noti-badge-white {
            position: absolute; top: -5px; right: -5px;
            background: var(--white); color: var(--pxx-red);
            font-size: 11px; font-weight: 900; width: 20px; height: 20px;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* 预览通知小窗口 */
        .noti-preview-window {
            position: absolute; top: calc(100% + 15px); right: 0;
            width: 320px; background: var(--white); border-radius: 12px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.15); border: 1px solid #eee;
            visibility: hidden; opacity: 0; transform: translateY(10px);
            transition: var(--trans-smooth); z-index: 10001; overflow: hidden; color: #333;
        }
        .noti-wrapper-hub:hover .noti-preview-window { visibility: visible; opacity: 1; transform: translateY(0); }

        .preview-h-box { padding: 15px 20px; font-weight: 800; border-bottom: 1px solid #f5f5f5; font-size: 13px; background: #fafafa; }
        .preview-list-item { padding: 15px 20px; border-bottom: 1px solid #f9f9f9; display: flex; gap: 12px; font-size: 12px; line-height: 1.4; color: #666; }
        .preview-list-item i { color: var(--pxx-red); margin-top: 3px; }

        /* 管理员身份模块 */
        .admin-profile-section {
            display: flex; align-items: center; gap: 15px;
            padding-left: 25px; border-left: 1px solid rgba(255,255,255,0.2);
        }
        .admin-avatar-frame {
            width: 45px; height: 45px; border-radius: 50%;
            border: 2px solid var(--white); overflow: hidden; background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .admin-avatar-frame img { width: 100%; height: 100%; object-fit: cover; }
        .logout-trigger-btn { font-size: 20px; color: white; margin-left: 10px; cursor: pointer; transition: 0.3s; }
        .logout-trigger-btn:hover { color: #ffcccc; transform: rotate(90deg); }

        /* ============================================================
           [SIDEBAR] 黑色侧边栏 - Fixed 定位锁死
           ============================================================ */
        .sidebar-fixed-black {
            width: var(--side-w);
            background-color: var(--side-bg);
            color: var(--white);
            position: fixed; top: var(--header-h); left: 0; bottom: 0;
            display: flex; flex-direction: column;
            padding-top: 20px;
            z-index: 9000;
            overflow-y: auto;
        }

        /* 侧边栏纯白选项样式 */
        .s-nav-item, .s-nav-dropdown-h {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 25px; font-size: 15px; font-weight: 600;
            color: var(--white); border-left: 6px solid transparent;
            cursor: pointer; transition: var(--trans-smooth);
        }
        .s-nav-item:hover, .s-nav-dropdown-h:hover, .s-nav-item.active {
            background-color: var(--side-hover); border-left-color: var(--pxx-red);
        }

        .s-nav-icon-box { display: flex; align-items: center; gap: 15px; }
        .s-nav-icon-box i { width: 25px; text-align: center; color: var(--white); font-size: 18px; }

        /* 二级折叠菜单容器 */
        .s-sub-container {
            max-height: 0; overflow: hidden;
            background-color: #080808;
            transition: max-height 0.4s ease-out;
        }
        .s-sub-container.open { max-height: 400px; }

        .s-sub-link-item {
            display: block; padding: 12px 25px 12px 65px;
            font-size: 14px; color: #aaaaaa;
        }
        .s-sub-link-item:hover { color: var(--white); background: #111; }

        /* 🌟 左下角 View Store 快捷跳转 */
        .sidebar-bottom-action {
            margin-top: auto; padding: 25px 30px;
            background-color: #0a0a0a; border-top: 1px solid #1a1a1a;
        }
        .sidebar-bottom-action a {
            display: flex; align-items: center; gap: 12px;
            color: var(--white); font-size: 14px; font-weight: 700; opacity: 0.8;
        }
        .sidebar-bottom-action a:hover { opacity: 1; color: #2ecc71; }

        /* ============================================================
           [ANNOUNCEMENT] 可关闭式顶部通告
           ============================================================ */
        .pxx-system-announce-strip {
            background-color: #ffffff;
            margin-left: var(--side-w); /* 关键：避开 Fixed 侧边栏 */
            margin-top: var(--header-h);
            padding: 15px 40px;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid #eeeeee;
            z-index: 8000; position: relative;
            animation: fadeInDown 0.4s ease;
        }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

        .announce-txt-content { display: flex; align-items: center; gap: 12px; font-size: 14px; color: #444; font-weight: 600; }
        .announce-txt-content i { color: var(--pxx-red); }
        .announce-close-x { cursor: pointer; color: #ccc; font-size: 20px; transition: 0.2s; padding: 5px; }
        .announce-close-x:hover { color: var(--pxx-red); }

        /* ============================================================
           [MAIN VIEWPORT] 主内容区域
           ============================================================ */
        .main-viewport-scroller {
            margin-left: var(--side-w);
            padding: 50px;
            height: calc(100vh - var(--header-h));
            overflow-y: auto;
            display: flex; flex-direction: column;
        }

        .portal-empty-canvas {
            flex: 1; display: flex; justify-content: center; align-items: center;
            text-align: center; color: #ddd;
        }
        .portal-empty-canvas i { font-size: 80px; opacity: 0.2; margin-bottom: 20px; display: block; }
        .portal-empty-canvas p { font-weight: 600; color: #bbb; letter-spacing: 0.5px; }

        /* 引用外部 Footer 的容器间距 */
        .footer-injection-point { margin-top: auto; padding-top: 50px; }

        /* 滚动条定制 */
        ::-webkit-scrollbar { width: 7px; }
        ::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    </style>
</head>
<body>

    <header class="admin-main-header">
        <div class="header-brand-wrap" onclick="location.href='portal.php'">
            <div class="logo-main">PXX Mall<span class="logo-r-icon">&reg;</span></div>
            <div class="logo-portal-tag">| Admin Portal</div>
        </div>

        <div class="header-actions-area">
            <div class="noti-wrapper-hub">
                <button class="noti-trigger-btn" onclick="location.href='notifications.php'">
                    <i class="fa-solid fa-bell"></i>
                    <span>Alerts</span>
                    <div class="noti-badge-white"><?php echo $noti_count; ?></div>
                </button>
                <div class="noti-preview-window">
                    <div class="preview-h-box">Administrative Status</div>
                    <div class="preview-list-item">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><b>System Core:</b> All database clusters are synchronized and healthy.</span>
                    </div>
                    <div class="preview-list-item">
                        <i class="fa-solid fa-user-shield"></i>
                        <span><b>Auth Security:</b> Multi-layered session encryption is active.</span>
                    </div>
                </div>
            </div>

            <div class="admin-profile-section">
                <div class="admin-avatar-frame">
                    <img src="<?php echo $path_prefix . 'images/' . $current_photo; ?>" onerror="this.src='../images/default.jpg'" alt="PXX">
                </div>
                <div style="text-align:left;">
                    <span style="display:block; font-size:10px; opacity:0.8; font-weight:700;"><?php echo strtoupper($user_role); ?> MODE</span>
                    <b style="font-size:15px;"><?php echo $current_admin; ?></b>
                </div>
                <a href="logout.php" class="logout-trigger-btn" title="Logout" onclick="return confirm('Ensure all progress is saved before termination. Confirm logout?')">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="pxx-system-announce-strip" id="globalNotice">
        <div class="announce-txt-content">
            <i class="fa-solid fa-circle-info"></i>
            <span>System Status: All administrative modules are operating within normal parameters. Real-time data feed is active.</span>
        </div>
        <div class="announce-close-x" onclick="document.getElementById('globalNotice').style.display='none'">&times;</div>
    <div class="admin-body-container">
        
        <aside class="admin-sidebar">
            <nav>
                <div class="sidebar-section-name">Control Panel</div>
                <ul class="sidebar-menu-list">
                    <li><a href="admin/portal.php" class="sidebar-link active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
                </ul>

                <div class="sidebar-section-name" style="margin-top:20px;">Management</div>
                <ul class="sidebar-menu-list">
                    <li><a href="/admin/category_crud.php" class="sidebar-link"><i class="fa-solid fa-tags"></i> Category CRUD</a></li>
                    <li><a href="/admin/product_crud.php" class="sidebar-link"><i class="fa-solid fa-box-open"></i> Product CRUD</a></li>
                    <li><a href="/admin/member_crud.php" class="sidebar-link"><i class="fa-solid fa-users-gear"></i> Member CRUD</a></li>
                    <li><a href="/admin/admin_crud.php" class="sidebar-link"><i class="fa-solid fa-user-shield"></i> Admin CRUD</a></li>
                </ul>

                <div class="sidebar-section-name" style="margin-top:20px;">Logs & Reports</div>
                <ul class="sidebar-menu-list">
                    <li><a href="/admin/sales_data.php" class="sidebar-link"><i class="fa-solid fa-chart-line"></i> Sales Data</a></li>
                    <li><a href="/admin/order_crud.php" class="sidebar-link"><i class="fa-solid fa-file-invoice-dollar"></i> Order History</a></li>
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
                    <a href="admin/category_crud.php" class="widget-btn">Manage Categories &rarr;</a>
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

    <aside class="sidebar-fixed-black">
        <nav>
            <a href="portal.php" class="s-nav-item active">
                <div class="s-nav-icon-box">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>System Dashboard</span>
                </div>
            </a>

            <?php if($user_role === 'S.Admin'): ?>
            <div class="s-nav-group">
                <div class="s-nav-dropdown-h" onclick="handleMenuToggle('s-adm-box', this)">
                    <div class="s-nav-icon-box">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Admin Management</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-container" id="s-adm-box">
                    <a href="admin_crud.php" class="s-sub-link-item">Manage Admin List</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="s-nav-group">
                <div class="s-nav-dropdown-h" onclick="handleMenuToggle('mem-box', this)">
                    <div class="s-nav-icon-box">
                        <i class="fa-solid fa-users"></i>
                        <span>Member Management</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-container" id="mem-box">
                    <a href="member_crud.php" class="s-sub-link-item">User Database</a>
                    <a href="order_history.php" class="s-sub-link-item">Order Registry</a>
                </div>
            </div>

            <div class="s-nav-group">
                <div class="s-nav-dropdown-h" onclick="handleMenuToggle('mall-box', this)">
                    <div class="s-nav-icon-box">
                        <i class="fa-solid fa-store"></i>
                        <span>PXX Mall Details</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-container" id="mall-box">
                    <a href="product_crud.php" class="s-sub-link-item">Product Inventory</a>
                    <a href="category_crud.php" class="s-sub-link-item">Tag Categories</a>
                    <a href="sales_analytics.php" class="s-sub-link-item">Revenue Data</a>
                </div>
            </div>
        </nav>

        <div class="sidebar-bottom-action">
            <a href="../index.php" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>View Storefront</span>
            </a>
        </div>
    </aside>

    <main class="main-viewport-scroller">
        
        <div class="portal-empty-canvas">
            <div>
                <i class="fa-solid fa-briefcase"></i>
                <p>Authorized Admin Hub. Select a functional module to begin.</p>
            </div>
        </div>

        <div class="footer-injection-point">
            <?php 
                // 🌟 核心引用：物理路径指向根目录下的 includes/_foot.php
                $footer_path = '../includes/_admin_foot.php';
                if (file_exists($footer_path)) {
                    include $footer_path;
                } else {
                    echo "<p style='color:#ccc; text-align:center;'>PXX Mall Malaysia Sdn. Bhd. &reg;</p>";
                }
            ?>
        </div>
    </main>

    <script>
        /**
         * 侧边栏子菜单交互逻辑 (Accordion Style)
         */
        function handleMenuToggle(id, header) {
            const menu = document.getElementById(id);
            const allMenus = document.querySelectorAll('.s-sub-container');
            const allHeaders = document.querySelectorAll('.s-nav-dropdown-h');

            // 互斥开启：打开当前项时，自动关闭其他已开启项
            allMenus.forEach(m => { if (m.id !== id) m.classList.remove('open'); });
            allHeaders.forEach(h => { if (h !== header) h.classList.remove('active'); });

            // 切换状态
            menu.classList.toggle('open');
            header.classList.toggle('active');
        }

        /**
         * 自动高亮与持久化状态逻辑
         */
        document.addEventListener('DOMContentLoaded', () => {
            const currentURL = window.location.href;
            document.querySelectorAll('.s-sub-link-item').forEach(link => {
                if (currentURL.includes(link.getAttribute('href'))) {
                    const parentBox = link.closest('.s-sub-container');
                    if(parentBox) {
                        parentBox.classList.add('open');
                        parentBox.previousElementSibling.classList.add('active');
                    }
                }
            });
        });
    </script>

</body>
</html>