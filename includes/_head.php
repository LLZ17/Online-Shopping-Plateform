<?php
/**
 * =================================================================================
 * PXX MALL - ENTERPRISE GLOBAL HEADER (STABLE VERSION 8.2 - DYNAMIC KEYWORDS)
 * =================================================================================
 * 开发者：PXX Mall 技术部
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
$is_subfolder = ($current_page_dir === 'admin' || $current_page_dir === 'member'|| $current_page_dir === 'memberProducts');
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

// =================================================================================
// 4. 动态获取搜索关键词 (联表查询 categories 和 products)
// =================================================================================
$current_category = isset($_GET['category']) ? $_GET['category'] : '';
$dynamic_keywords = [];

if (isset($pdo)) {
    try {
        if (!empty($current_category) && $current_category !== 'All') {
            // 如果指定了分类，先找到对应的 category_id，再去 products 里找 keywords
            $kw_stmt = $pdo->prepare("
                SELECT DISTINCT p.keywords 
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
                WHERE c.category_gender = :cat AND p.keywords IS NOT NULL AND p.keywords != ''
                LIMIT 4
            ");
            $kw_stmt->execute(['cat' => $current_category]);
        } else {
            // 如果没有指定，随机抓取全站的 keywords
            $kw_stmt = $pdo->prepare("SELECT DISTINCT keywords FROM products WHERE keywords IS NOT NULL AND keywords != '' LIMIT 4");
            $kw_stmt->execute();
        }
        
        $fetched_keywords = $kw_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // 如果关键词是用逗号分隔存的，比如 "korean, dress"，需要拆分开来
        foreach ($fetched_keywords as $kw_string) {
            $parts = explode(',', $kw_string);
            foreach ($parts as $p) {
                $p = trim($p);
                if (!empty($p) && !in_array($p, $dynamic_keywords)) {
                    $dynamic_keywords[] = $p;
                }
                if (count($dynamic_keywords) >= 4) break 2; // 最多保留4个
            }
        }
    } catch(PDOException $e) {}
}

// 如果数据库里完全没设定 keywords，给个好看的默认值
if (empty($dynamic_keywords)) {
    if ($current_category == 'Women') $dynamic_keywords = ['Korean', 'Dress', 'Heels', 'Handbag'];
    elseif ($current_category == 'Men') $dynamic_keywords = ['Streetwear', 'Sneakers', 'Suit', 'T-Shirt'];
    else $dynamic_keywords = ['Korean', 'Streetwear', 'New Arrival', 'Sale'];
}

// =================================================================================
// 5. 获取用户的通知、愿望清单和购物车数据 (用于动态 Badge 和 Preview)
// =================================================================================
$notif_count = 0; $recent_notifs = [];
$wishlist_count = 0; $recent_wishlist = [];
$cart_count = 0; $recent_cart = [];

if ($isLoggedIn && isset($pdo)) {
    $uid = $_SESSION['user_id'] ?? 0;
    
    if ($uid > 0) {
        // --- 购物车数据 ---
        try {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
            $stmt->execute([$uid]);
            $cart_count = $stmt->fetchColumn();

            if ($cart_count > 0) {
                $stmt = $pdo->prepare("
                    SELECT p.name, p.price, c.quantity, p.product_image 
                    FROM cart c 
                    JOIN products p ON c.product_id = p.product_id 
                    WHERE c.user_id = ? 
                    ORDER BY c.cart_id DESC LIMIT 3
                ");
                $stmt->execute([$uid]);
                $recent_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {}

        // --- 愿望清单数据 ---
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
            $stmt->execute([$uid]);
            $wishlist_count = $stmt->fetchColumn();

            if ($wishlist_count > 0) {
                $stmt = $pdo->prepare("
                    SELECT p.name, p.price, p.product_image 
                    FROM wishlist w 
                    JOIN products p ON w.product_id = p.product_id 
                    WHERE w.user_id = ? 
                    ORDER BY w.wishlist_id DESC LIMIT 3
                ");
                $stmt->execute([$uid]);
                $recent_wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {}

        // --- 通知数据 (只抓取 is_read = 0 的数量) ---
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$uid]);
            $notif_count = $stmt->fetchColumn();

            // 预览展示最新的3条
            $stmt = $pdo->prepare("SELECT message FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
            $stmt->execute([$uid]);
            $recent_notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {}
    }
}
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
           CSS 系统设置 
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
            height: 60px; /* 固定高度确保子元素对齐 */
        }

        /* Logo 样式 - 修正走位 */
        .logo-link { 
            font-family: 'Montserrat', sans-serif;
            font-size: 40px; font-weight: 800; letter-spacing: -1px; 
            display: flex; align-items: center; /* 垂直居中对齐 */
            height: 100%;
            margin-right: 30px;
        }
        .logo-link sup { font-size: 16px; font-weight: 700; margin-left: 2px; } /* 使用 sup 代替 span 更自然 */

        /* 搜索栏样式 */
        .search-section { flex: 1; max-width: 600px; display: flex; flex-direction: column; justify-content: center;}
        .search-container { display: flex; background: white; border-radius: 6px; overflow: hidden; width: 100%; }
        .search-input { width: 100%; border: none; padding: 12px 20px; outline: none; font-size: 15px; color: #333; }
        .search-btn { background: white; border: none; color: var(--pxx-red); cursor: pointer; padding: 0 20px; font-size: 18px; }

        /* 关键词样式 */
        .search-keywords {
            margin-top: 6px;
            font-size: 12px;
            display: flex;
            gap: 15px;
            color: rgba(255, 255, 255, 0.8);
            margin-left: 5px;
        }
        .search-keywords span { font-weight: 700; color: #ffffff; }
        .search-keywords a { color: rgba(255, 255, 255, 0.9); text-decoration: none; transition: 0.2s; }
        .search-keywords a:hover { color: #ffffff; text-decoration: underline; }

        /* 图标动作区 */
        .nav-actions { display: flex; align-items: center; gap: 28px; height: 100%;} 
        .nav-item { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;}
        .nav-trigger-link { display: flex; flex-direction: column; align-items: center; cursor: pointer; color: white; }
        .nav-trigger-link:hover { opacity: 0.85; transform: translateY(-2px); }

        .profile-item { margin-left: 10px; } 
        .icon-wrap { position: relative; height: 32px; display: flex; align-items: center; justify-content: center; }
        .icon-wrap i { font-size: 24px; color: white; }
        .icon-text { font-size: 13px; font-weight: 700; color: white; margin-top: 4px; }

        /* 徽章样式 */
        .badge { 
            position: absolute; top: -6px; right: -12px; 
            background: white; color: var(--pxx-red); 
            font-size: 11px; font-weight: 900; 
            width: 18px; height: 18px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* 头像圆形 */
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 2px solid white; }

        /* =========================================
           悬停预览与下拉菜单样式
           ========================================= */
        .preview-box, .user-dropdown {
            position: absolute; top: calc(100% + 5px); background: white; border-radius: 8px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 1px solid #eee; 
            visibility: hidden; opacity: 0; transform: translateY(10px); 
            transition: var(--transition); color: #333; z-index: 10001;
            overflow: hidden;
        }

        .preview-box::after, .user-dropdown::after { 
            content: ''; position: absolute; top: -8px; 
            border-left: 8px solid transparent; border-right: 8px solid transparent; border-bottom: 8px solid white; 
        }

        .nav-item.hover-trigger:hover .preview-box, 
        .nav-item.hover-trigger:hover .user-dropdown { 
            visibility: visible; opacity: 1; transform: translateY(0); 
        }

        .user-dropdown { right: 0; min-width: 220px; display: flex; flex-direction: column; padding: 5px 0; }
        .user-dropdown::after { right: 25px; } 

        .guest-dropdown { width: 260px !important; padding: 15px !important; text-align: center; }
        .guest-dropdown a { 
            padding: 10px 15px !important; display: inline-flex !important; 
            align-items: center; justify-content: center; width: auto !important;
            font-size: 15px !important; border-bottom: none !important;
        }

        .user-dropdown a { 
            padding: 12px 20px; font-size: 14px; color: #444; font-weight: 600; 
            text-align: left; display: flex; align-items: center; gap: 12px; 
            border-bottom: 1px solid #f9f9f9; 
        }
        .user-dropdown a:last-child { border-bottom: none; }
        .user-dropdown i { width: 20px; text-align: center; color: #888; font-size: 16px; }
        .user-dropdown a:hover { color: #28a745; background: #fafafa; }
        .user-dropdown a.logout-btn:hover, .user-dropdown a.logout-btn:hover i { color: var(--pxx-red); }

        /* 预览框详情样式 (用于 Cart & Wishlist 动态渲染) */
        .preview-box { right: -20px; width: 340px; cursor: default; }
        .preview-box::after { right: 35px; } 
        .preview-header { padding: 15px 20px; font-size: 14px; font-weight: 700; border-bottom: 1px solid #eee; text-align: left; background: #fafafa; color: #333;}
        
        .preview-item { display: flex; align-items: center; gap: 12px; padding: 12px 20px; border-bottom: 1px solid #f5f5f5; text-align: left; }
        .preview-item:last-child { border-bottom: none; }
        .preview-item img { width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
        .preview-item-info { display: flex; flex-direction: column; flex: 1; overflow: hidden; }
        .preview-item-name { font-size: 13px; font-weight: 600; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .preview-item-price { font-size: 12px; color: var(--pxx-red); font-weight: 700; margin-top: 3px; }
        .preview-item-qty { color: #888; font-weight: 400; font-size: 11px; }

        .preview-notif-item { padding: 15px 20px; border-bottom: 1px solid #f5f5f5; text-align: left; font-size: 13px; color: #444; line-height: 1.4; }
        .preview-notif-item:last-child { border-bottom: none; }

        .preview-empty { padding: 30px 20px; color: #999; font-size: 13px; text-align: center; }
        .preview-footer { display: block; text-align: center; padding: 12px; background: white; font-size: 13px; color: var(--pxx-red); font-weight: 700; border-top: 1px solid #eee; }
        .preview-footer:hover { background: #fafafa; }

        .header-spacer { height: 110px; width: 100%; display: block; }
    </style>
</head>
<body>

<header class="pxx-header">
    <div class="header-container">
        
        <a href="<?php echo $path_prefix; ?>index.php" class="logo-link">PXX Mall<sup>&reg;</sup></a>
        
        <div class="search-section">
            <form action="<?php echo $path_prefix; ?>ViewAllProduct.php" method="GET" class="search-container" style="display: flex; gap: 10px;">
                <input type="text" name="query" class="search-input" placeholder="Search for Clothing">
                <select name="category" style="padding: 0 8px; border: none; background: white; font-size: 13px; width: 90px; cursor: pointer; border-left: 1px solid #eee;">
                    <option value="">All</option>
                    <option value="Men" <?php echo $current_category == 'Men' ? 'selected' : ''; ?>>👔 Men</option>
                    <option value="Women" <?php echo $current_category == 'Women' ? 'selected' : ''; ?>>👗 Women</option>
                </select>
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            
            <div class="search-keywords">
                <span>Trending:</span>
                <?php foreach($dynamic_keywords as $kw): ?>
                    <a href="<?php echo $path_prefix; ?>ViewAllProduct.php?query=<?php echo urlencode($kw); ?><?php echo !empty($current_category) ? '&category='.urlencode($current_category) : ''; ?>">
                        <?php echo htmlspecialchars($kw); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="nav-actions">
            
            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'member/notifications.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($isLoggedIn && $notif_count > 0): ?>
                            <span class="badge"><?php echo $notif_count; ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-text">Notifications</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Recent Notifications</div>
                    <?php if (!empty($recent_notifs)): ?>
                        <?php foreach($recent_notifs as $notif): ?>
                            <div class="preview-notif-item">
                                <i class="fa-solid fa-circle-info" style="color:var(--pxx-red); margin-right:5px;"></i> 
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </div>
                        <?php endforeach; ?>
                        <a href="<?php echo $path_prefix.'member/notifications.php'; ?>" class="preview-footer">View All Notifications</a>
                    <?php else: ?>
                        <div class="preview-empty">No new notifications.</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'member/wishlist.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-heart"></i>
                        <?php if ($isLoggedIn && $wishlist_count > 0): ?>
                            <span class="badge"><?php echo $wishlist_count; ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-text">Wishlist</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Your Wishlist (<?php echo $wishlist_count; ?>)</div>
                    <?php if (!empty($recent_wishlist)): ?>
                        <?php foreach($recent_wishlist as $item): ?>
                            <div class="preview-item">
                                <img src="<?php echo $path_prefix; ?>images/<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'default.jpg'; ?>" onerror="this.src='<?php echo $path_prefix; ?>images/default.jpg'">
                                <div class="preview-item-info">
                                    <span class="preview-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="preview-item-price">RM <?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="<?php echo $path_prefix.'member/wishlist.php'; ?>" class="preview-footer">View Full Wishlist</a>
                    <?php else: ?>
                        <div class="preview-empty">Your wishlist is empty.<br>Start adding your favorite items!</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'memberProducts/unified_cart.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($isLoggedIn && $cart_count > 0): ?>
                            <span class="badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-text">Cart</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Shopping Cart (<?php echo $cart_count; ?> Items)</div>
                    <?php if (!empty($recent_cart)): ?>
                        <?php foreach($recent_cart as $item): ?>
                            <div class="preview-item">
                                <img src="<?php echo $path_prefix; ?>images/<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'default.jpg'; ?>" onerror="this.src='<?php echo $path_prefix; ?>images/default.jpg'">
                                <div class="preview-item-info">
                                    <span class="preview-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="preview-item-price">RM <?php echo number_format($item['price'], 2); ?> <span class="preview-item-qty">x <?php echo $item['quantity']; ?></span></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="<?php echo $path_prefix.'memberProducts/unified_cart.php'; ?>" class="preview-footer">Go to Checkout</a>
                    <?php else: ?>
                        <div class="preview-empty">Your shopping cart is empty.<br>Let's go shopping!</div>
                    <?php endif; ?>
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
                        <a href="<?php echo $path_prefix; ?>member/orders_history.php"><i class="fa-solid fa-box"></i> My Orders</a>
                        <a href="<?php echo $path_prefix; ?>member/settings.php"><i class="fa-solid fa-gear"></i> My Settings</a>
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
                            <a href="<?php echo $path_prefix; ?>member/signup.php" style="border:1.5px solid #eee; border-radius:4px;">
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