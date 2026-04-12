<?php
/**
 * =================================================================================
 * PXX MALL - ENTERPRISE GLOBAL HEADER (STABLE VERSION 8.0)
 * =================================================================================
 * 开发者：PXX Mall 技术部
 * 物理路径参考: C:\xampp\htdocs\HTML_Ass\images
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------------------------------------------------------------
// 1. 全局变量初始化与权限判定
// ----------------------------------------------------------------
$isLoggedIn = false;
$username = "";
$avatar_file = "default.jpg";

// 🌟 核心：判定当前运行的文件夹层级 (解决相对路径报错)
$current_page_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_subfolder = ($current_page_dir === 'admin' || $current_page_dir === 'member');
$path_prefix = $is_subfolder ? '../' : '';

// 2. 获取用户数据：强制连接 SQL 获取最新头像
if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $username = $_SESSION['username'];
    
    // 🔗 引入数据库连接 (锁定路径)
    $db_file = __DIR__ . '/db.php';
    if (file_exists($db_file)) {
        require_once $db_file;
        if (isset($pdo)) {
            try {
                // 实时查询数据库中的 profile_photo 字段，确保头像同步
                $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user_data = $stmt->fetch();
                
                if ($user_data && !empty($user_data['profile_photo'])) {
                    $avatar_file = $user_data['profile_photo'];
                    $_SESSION['profile_photo'] = $avatar_file; // 同步给 Session
                }
            } catch (PDOException $e) {
                // 数据库报错时回退到 Session 或 默认图
                $avatar_file = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
            }
        }
    }
}

// 3. Remember Me 自动登录
if (!$isLoggedIn && isset($_COOKIE['remember_me'])) {
    $db_file = __DIR__ . '/db.php';
    if (file_exists($db_file)) {
        require_once $db_file;
        if (isset($pdo)) {
            $token = $_COOKIE['remember_me'];
            $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['user_id']; 
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $db_avatar = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';
                $_SESSION['profile_photo'] = $db_avatar;
                
                $isLoggedIn = true;
                $username = $_SESSION['username'];
                $avatar_file = $db_avatar;
            }
        }
    }
}

// 最终合成图片物理路径
$final_avatar_path = $path_prefix . 'images/' . $avatar_file;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================================
           CSS 系统设置 (不偷工减料，完整保留所有 UI 细节)
           ============================================================ */
        :root {
            --pxx-red: #e60012;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body { margin: 0; font-family: 'Poppins', sans-serif; background-color: var(--gray-100); }
        * { box-sizing: border-box; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }

        /* 顶部导航容器 */
        .pxx-header { 
            background-color: var(--pxx-red); 
            color: white; 
            padding: 12px 0; 
            position: fixed; top: 0; left: 0; right: 0; 
            z-index: 10000; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header-container { 
            display: flex; justify-content: space-between; align-items: center; 
            max-width: 1400px; margin: 0 auto; padding: 0 40px; 
        }

        /* Logo 样式 */
        .logo-link { 
            font-family: 'Montserrat', sans-serif;
            font-size: 42px; font-weight: 800; letter-spacing: -1px; 
            display: flex; align-items: flex-start;
        }
        .logo-link span { font-size: 14px; font-weight: 700; margin-top: 5px; margin-left: 2px; }

        /* 搜索栏样式 */
        .search-section { flex: 1; margin: 0 60px; max-width: 700px; }
        .search-container { display: flex; background: white; border-radius: 6px; overflow: hidden; }
        .search-input { width: 100%; border: none; padding: 12px 20px; outline: none; font-size: 15px; color: #333; }
        .search-btn { background: white; border: none; color: var(--pxx-red); cursor: pointer; padding: 0 20px; font-size: 18px; }

        /* 图标动作区 */
        .nav-actions { display: flex; align-items: center; gap: 28px; } 
        .nav-item { position: relative; display: flex; flex-direction: column; align-items: center; }
        .nav-trigger-link { display: flex; flex-direction: column; align-items: center; cursor: pointer; color: white; }
        .nav-trigger-link:hover { opacity: 0.85; transform: translateY(-2px); }

        .profile-item { margin-left: 20px; } 
        .icon-wrap { position: relative; height: 32px; display: flex; align-items: center; justify-content: center; }
        .icon-wrap i { font-size: 24px; color: white; }
        .icon-text { font-size: 13px; font-weight: 700; color: white; margin-top: 4px; }

        /* 徽章样式 */
        .badge { 
            position: absolute; top: -6px; right: -12px; 
            background: white; color: black; 
            font-size: 12px; font-weight: 900; 
            width: 20px; height: 20px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* 头像圆形 */
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 2px solid white; }

        /* =========================================
           悬停预览与下拉菜单样式
           ========================================= */
        .preview-box, .user-dropdown {
            position: absolute; top: calc(100% + 15px); background: white; border-radius: 6px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 1px solid #eee; 
            visibility: hidden; opacity: 0; transform: translateY(10px); 
            transition: var(--transition); color: #333; z-index: 10001;
        }

        /* 指向上方的小三角 */
        .preview-box::after, .user-dropdown::after { 
            content: ''; position: absolute; top: -8px; 
            border-left: 8px solid transparent; border-right: 8px solid transparent; border-bottom: 8px solid white; 
        }

        /* 鼠标悬停激活 */
        .nav-item.hover-trigger:hover .preview-box, 
        .nav-item.hover-trigger:hover .user-dropdown { 
            visibility: visible; opacity: 1; transform: translateY(-5px); 
        }

        /* 用户下拉菜单宽度调节 */
        .user-dropdown { right: 0; min-width: 220px; display: flex; flex-direction: column; padding: 5px 0; }
        .user-dropdown::after { right: 25px; } 

        /* 🔥 新增：未登录时的宽大菜单样式 (解决 Signup/Login 同一行问题) */
        .guest-dropdown { width: 260px !important; padding: 15px !important; text-align: center; }
        .guest-dropdown a { 
            padding: 10px 15px !important; 
            display: inline-flex !important; 
            align-items: center; 
            justify-content: center;
            width: auto !important;
            font-size: 15px !important;
            border-bottom: none !important;
        }

        .user-dropdown a { 
            padding: 12px 20px; font-size: 14px; color: #444; font-weight: 600; 
            text-align: left; display: flex; align-items: center; gap: 12px; 
            border-bottom: 1px solid #f9f9f9; 
        }
        .user-dropdown a:last-child { border-bottom: none; }
        .user-dropdown i { width: 20px; text-align: center; color: #888; font-size: 16px; }
        .user-dropdown a:hover { color: #00cba9; background: #fafafa; }
        .user-dropdown a.logout-btn:hover, .user-dropdown a.logout-btn:hover i { color: var(--pxx-red); }

        /* 预览框详情样式 */
        .preview-box { right: -20px; width: 320px; cursor: default; }
        .preview-box::after { right: 35px; } 
        .preview-header { padding: 15px 20px; font-size: 15px; font-weight: 700; border-bottom: 1px solid #eee; text-align: left; }
        .preview-content { padding: 20px; color: #888; font-size: 13px; text-align: center; }

        .header-spacer { height: 95px; width: 100%; display: block; }
    </style>
</head>
<body>

<header class="pxx-header">
    <div class="header-container">
        
        <a href="<?php echo $path_prefix; ?>index.php" class="logo-link">PXX Mall<span>&reg;</span></a>
        
        <div class="search-section">
            <form action="<?php echo $path_prefix; ?>search.php" method="GET" class="search-container">
                <input type="text" name="query" class="search-input" placeholder="Search for Men and Women chlotes......">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>

        <div class="nav-actions">
            
            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'member/notifications.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($isLoggedIn): ?><span class="badge">2</span><?php endif; ?>
                    </div>
                    <span class="icon-text">Notifications</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Recent Notifications</div>
                    <div class="preview-content">Your order #PXX-882 has been shipped.</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'member/wishlist.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-heart"></i>
                        <?php if ($isLoggedIn): ?><span class="badge">5</span><?php endif; ?>
                    </div>
                    <span class="icon-text">Wishlist</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Wishlist Items</div>
                    <div class="preview-content">No items added to wishlist recently.</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'member/cart.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($isLoggedIn): ?><span class="badge">12</span><?php endif; ?>
                    </div>
                    <span class="icon-text">Cart</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Cart Summary</div>
                    <div class="preview-content">12 items in your shopping cart.</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="nav-item hover-trigger profile-item">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $path_prefix; ?>member/profile.php" class="nav-trigger-link">
                        <div class="icon-wrap">
                            <img src="<?php echo $final_avatar_path; ?>" onerror="this.src='<?php echo $path_prefix; ?>images/default.jpg'" class="user-avatar" alt="User">
                        </div>
                        <span class="icon-text"><?php echo htmlspecialchars($username); ?></span>
                    </a>
                    <div class="user-dropdown">
                        <a href="<?php echo $path_prefix; ?>member/profile.php"><i class="fa-solid fa-user-circle"></i> My Account</a>
                        <a href="<?php echo $path_prefix; ?>member/order_history.php"><i class="fa-solid fa-box"></i> My Orders</a>
                        <a href="<?php echo $path_prefix; ?>member/settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
                        <div style="height:1px; background:#eee; margin:5px 0;"></div>
                        <a href="<?php echo $path_prefix; ?>member/logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $path_prefix; ?>login_selection.php" class="nav-trigger-link">
                        <div class="icon-wrap"><i class="fa-solid fa-circle-user" style="font-size:28px;"></i></div>
                        <span class="icon-text">Login/Sign-up</span>
                    </a>
                    <div class="user-dropdown guest-dropdown">
                        <div style="font-weight:700; color:#888; font-size:12px; margin-bottom:10px;">WELCOME TO PXX Mall!</div>
                        <div style="display:flex; justify-content:center; gap:10px;">
                            <a href="<?php echo $path_prefix; ?>login_selection.php" style="background:var(--pxx-red); color:white; border-radius:4px; font-weight:800;">
                                <i class="fa-solid fa-key" style="color:white; font-size:12px;"></i> Login
                            </a>
                            <a href="<?php echo $path_prefix; ?>signup.php" style="border:1.5px solid #eee; border-radius:4px;">
                                <i class="fa-solid fa-user-plus" style="font-size:12px;"></i> Sign Up
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</header>

<div class="header-spacer"></div>

</body>
</html>