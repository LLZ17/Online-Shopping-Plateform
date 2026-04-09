<?php
session_start();
// 安全检查
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// 模拟从数据库/Session获取头像，如果没有则显示默认头像
$user_avatar = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'assets/images/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PXX Mall | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --sidebar-width: 260px; 
            --primary-color: #e60012; 
            --dark-bg: #1a1a1a; 
            --light-bg: #f4f7f6;
        }

        body { 
            margin: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            display: flex; 
            min-height: 100vh;
            background: var(--light-bg); 
            overflow-x: hidden;
        }
        
        /* --- 侧边栏 --- */
        .sidebar { 
            width: var(--sidebar-width); 
            background: var(--dark-bg); 
            color: white; 
            height: 100vh; 
            position: fixed; 
            left: 0; top: 0;
            z-index: 1000;
        }

        .sidebar-header { 
            padding: 30px 20px; 
            text-align: center; 
            background: #000; 
            border-bottom: 1px solid #333; 
        }

        /* 🔥 修正：PXX Mall 换行显示 */
        .sidebar-header h2 { 
            margin: 0; 
            color: var(--primary-color); 
            font-size: 24px;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .sidebar-header span { 
            display: block; 
            font-size: 14px; 
            color: #888; 
            letter-spacing: 4px;
            margin-top: 5px;
        }
        
        .nav-menu { padding: 20px 0; }
        .nav-item { padding: 15px 25px; display: block; color: #adb5bd; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-item:hover, .nav-item.active { background: #2d2d2d; color: white; border-left-color: var(--primary-color); }
        .nav-item i { margin-right: 10px; width: 20px; }

        /* --- 主内容区 --- */
        .main-content { 
            margin-left: var(--sidebar-width); 
            flex: 1; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }

        /* --- 顶栏优化 --- */
        .top-bar { 
            background: white; 
            padding: 0 30px; 
            height: 70px;
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 25px; /* 图标与头像之间的间距 */
        }

        /* 🔥 通知图标样式 */
        .notification-box {
            position: relative;
            color: #666;
            font-size: 20px;
            cursor: pointer;
            transition: 0.3s;
        }
        .notification-box:hover { color: var(--primary-color); }
        .notification-box .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-color);
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* 🔥 管理员头像样式 */
        .admin-profile { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            padding-left: 20px;
            border-left: 1px solid #eee;
        }
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #eee;
        }
        .admin-info { line-height: 1.2; }
        .admin-info span { display: block; font-size: 12px; color: #888; }
        .admin-info strong { font-size: 14px; color: #333; }

        .logout-btn { 
            margin-left: 10px;
            color: #666; 
            text-decoration: none; 
            font-size: 18px;
            transition: 0.3s;
        }
        .logout-btn:hover { color: var(--primary-color); }
        
        .container { padding: 30px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>PXX Mall</h2>
        <span>ADMIN</span>
    </div>
    <div class="nav-menu">
        <a href="admin_dashboard.php" class="nav-item active"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="category_maintenance.php" class="nav-item"><i class="fas fa-tags"></i> Category CRUD</a>
        <a href="admin_maintenance.php" class="nav-item"><i class="fas fa-user-shield"></i> Admin CRUD</a>
        <a href="order_list.php" class="nav-item"><i class="fas fa-shopping-cart"></i> Sales Data</a>
        <a href="index.php" class="nav-item" target="_blank"><i class="fas fa-external-link-alt"></i> View Store</a>
        <a href="admin_product.php" class="nav-item">
    <i class="fas fa-box"></i>  Product CRUD 
</a>
<a href="admin_member_list.php" class="nav-item">
    <i class="fas fa-users"></i> Member CRUD
</a>
<a href="admin_order.php" class="nav-item">
    <i class="fas git add includes/_admin_head.phpfa-shopping-bag"></i> Order CRUD
</a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="page-title">
            <h4 style="margin:0; color: #444;">Dashboard Overview</h4>
        </div>
        
        <div class="top-bar-right">
            <div class="notification-box">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </div>

            <div class="admin-profile">
                <img src="<?php echo $user_avatar; ?>" alt="Admin" class="admin-avatar">
                <div class="admin-info">
                    <span>Welcome back,</span>
                    <strong><?php echo $_SESSION['username']; ?></strong>
                </div>
                <a href="logout.php" class="logout-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        </div>
</div>

</body>
</html>