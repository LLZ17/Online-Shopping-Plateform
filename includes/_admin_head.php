<?php
session_start();
// 安全检查：如果不是 Admin，直接踢回首页
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
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

        /* 🔥 【修正重点 1】让 body 成为 Flex 容器，强制子元素并排 */
        body { 
            margin: 0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            display: flex; 
            min-height: 100vh;
            background: var(--light-bg); 
            overflow-x: hidden; /* 防止出现横向滚动条 */
        }
        
        /* --- 侧边栏 (Fixed定位) --- */
        .sidebar { 
            width: var(--sidebar-width); 
            background: var(--dark-bg); 
            color: white; 
            height: 100vh; 
            position: fixed; /* 🔥 【修正重点 2】侧边栏固定不动 */
            left: 0;
            top: 0;
            transition: 0.3s; 
            z-index: 1000; /* 确保它在最上面 */
        }

        .sidebar-header { 
            padding: 25px; 
            text-align: center; 
            background: #000; 
            border-bottom: 1px solid #333; 
        }

        .sidebar-header h2 { margin: 0; color: var(--primary-color); letter-spacing: 2px; }
        
        .nav-menu { padding: 20px 0; }
        .nav-item { padding: 15px 25px; display: block; color: #adb5bd; text-decoration: none; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-item:hover, .nav-item.active { background: #2d2d2d; color: white; border-left-color: var(--primary-color); }
        .nav-item i { margin-right: 10px; width: 20px; }

        /* --- 主内容区 (关键区域) --- */
        .main-content { 
            /* 🔥 【修正重点 3】必须设置这个 margin，给固定的侧边栏留出空间 */
            margin-left: var(--sidebar-width); 
            flex: 1; /* 撑开剩余空间 */
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            transition: 0.3s;
        }

        .top-bar { 
            background: white; 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        }

        .admin-profile { display: flex; align-items: center; gap: 10px; }
        .logout-btn { color: #666; text-decoration: none; font-size: 14px; border: 1px solid #ddd; padding: 5px 12px; border-radius: 4px; }
        .logout-btn:hover { background: #fee; color: var(--primary-color); border-color: var(--primary-color); }
        
        /* --- 容器布局 --- */
        .container { 
            padding: 30px; 
        }

        /* 修复之前导致 VS Code 报错的括号，并让卡片横向排开 */
        .stats-grid { 
            display: grid; 
            /* 自动填满，最小250px一个框 */
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }

        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: 12px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.02); 
            border-bottom: 4px solid #eee; 
            transition: transform 0.3s;
        }

        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>PXX ADMIN</h2>
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
    <i class="fas fa-shopping-bag"></i> Order CRUD
</a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="page-title">
            <h4 style="margin:0;">Dashboard Overview</h4>
        </div>
        <div class="admin-profile">
            <span>Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="container">