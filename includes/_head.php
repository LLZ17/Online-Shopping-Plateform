<?php
/**
 * =================================================================================
 * PXX MALL - ENTERPRISE GLOBAL HEADER (VERSION 8.8)
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------------------------------------------------------------
// 1. 全局变量初始化
// ----------------------------------------------------------------
$isLoggedIn = false;
$username = "";
$avatar_file = "default.jpg";

$current_page_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_subfolder = ($current_page_dir === 'admin' || $current_page_dir === 'member' || $current_page_dir === 'memberProducts');
$path_prefix = $is_subfolder ? '../' : '';

// 2. 数据库连接
$db_file = __DIR__ . '/db.php';
if (file_exists($db_file)) {
    require_once $db_file;
}

// 3. 登录状态检查
if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $username = $_SESSION['username'];
    
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user_data = $stmt->fetch();
            
            if ($user_data && !empty($user_data['profile_photo'])) {
                $avatar_file = $user_data['profile_photo'];
                $_SESSION['profile_photo'] = $avatar_file;
            }
        } catch (PDOException $e) {
            $avatar_file = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
        }
    }
}

// 4. Remember Me
if (!$isLoggedIn && isset($_COOKIE['remember_me']) && isset($pdo)) {
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

$final_avatar_path = $path_prefix . 'images/' . $avatar_file;

// =================================================================================
// 5. 动态搜索关键词 - 从 products 表实时获取
// =================================================================================
$current_category = isset($_GET['category']) ? $_GET['category'] : '';
$dynamic_keywords = [];

if (isset($pdo)) {
    try {
        if (!empty($current_category) && $current_category !== 'All') {
            $kw_stmt = $pdo->prepare("
                SELECT DISTINCT p.keywords 
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
                WHERE c.category_gender = :cat 
                AND p.keywords IS NOT NULL 
                AND p.keywords != ''
                ORDER BY RAND()
                LIMIT 10
            ");
            $kw_stmt->execute(['cat' => $current_category]);
        } else {
            $kw_stmt = $pdo->prepare("
                SELECT DISTINCT keywords 
                FROM products 
                WHERE keywords IS NOT NULL 
                AND keywords != ''
                ORDER BY RAND()
                LIMIT 10
            ");
            $kw_stmt->execute();
        }
        
        $fetched_keywords = $kw_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($fetched_keywords as $kw_string) {
            $parts = explode(',', $kw_string);
            foreach ($parts as $p) {
                $p = trim($p);
                if (!empty($p) && strlen($p) > 1 && !in_array($p, $dynamic_keywords)) {
                    $dynamic_keywords[] = $p;
                }
                if (count($dynamic_keywords) >= 4) break 2;
            }
        }
    } catch(PDOException $e) {}
}

if (empty($dynamic_keywords)) {
    if ($current_category == 'Women') $dynamic_keywords = ['Dress', 'Skirt', 'Blouse', 'Heels'];
    elseif ($current_category == 'Men') $dynamic_keywords = ['Shirt', 'Trousers', 'Sneakers', 'Jacket'];
    else $dynamic_keywords = ['T-Shirt', 'Jeans', 'Sneakers', 'Dress'];
}

// =================================================================================
// 6. 获取通知、愿望清单、购物车数据
// =================================================================================
$notif_count = 0; 
$recent_notifs = [];
$wishlist_count = 0; 
$recent_wishlist = [];
$cart_count = 0; 
$recent_cart = [];

$uid = '';
if ($isLoggedIn) {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $uid = (string)$_SESSION['user_id'];
    } elseif (isset($_SESSION['username']) && !empty($_SESSION['username']) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$_SESSION['username']]);
            $uid = (string)$stmt->fetchColumn();
            $_SESSION['user_id'] = $uid;
        } catch(PDOException $e) {}
    }
}

if (!empty($uid) && isset($pdo)) {
    
    // --- 购物车 ---
    try {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
        $stmt->execute([$uid]);
        $cart_count = (int)$stmt->fetchColumn();

        if ($cart_count > 0) {
            $stmt = $pdo->prepare("
                SELECT p.name, p.price, c.quantity, p.product_image, c.selected_size, c.colour
                FROM cart c 
                JOIN products p ON c.product_id = p.product_id 
                WHERE c.user_id = ? 
                ORDER BY c.cart_id DESC LIMIT 3
            ");
            $stmt->execute([$uid]);
            $recent_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {}

    // --- 愿望清单 (favourites 表) ---
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favourites WHERE user_id = ?");
        $stmt->execute([$uid]);
        $wishlist_count = (int)$stmt->fetchColumn();

        if ($wishlist_count > 0) {
            $stmt = $pdo->prepare("
                SELECT p.name, p.price, p.product_image, f.size, f.colour
                FROM favourites f 
                JOIN products p ON f.product_id = p.product_id 
                WHERE f.user_id = ? 
                ORDER BY f.created_at DESC LIMIT 3
            ");
            $stmt->execute([$uid]);
            $recent_wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {}

    // --- 通知 ---
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$uid]);
        $notif_count = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT message FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$uid]);
        $recent_notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {}
}

function badge_number($num) {
    return $num > 99 ? '99+' : $num;
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
        :root {
            --pxx-red: #e60012;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body { 
            margin: 0; 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--gray-100);
            padding-top: 100px;
        }
        * { box-sizing: border-box; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }

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
            height: 60px;
        }

        .logo-link { 
            font-family: 'Montserrat', sans-serif;
            font-size: 40px; font-weight: 800; letter-spacing: -1px; 
            display: flex; align-items: center;
            height: 100%;
            margin-right: 30px;
        }
        .logo-link sup { font-size: 16px; font-weight: 700; margin-left: 2px; }

        .search-section { 
            flex: 1;
            max-width: 700px;
            min-width: 400px;
            display: flex; 
            flex-direction: column; 
            justify-content: center;
            margin: 0 20px;
        }
        
        .search-container { 
            display: flex; 
            background: white; 
            border-radius: 6px; 
            overflow: hidden; 
            width: 100%; 
        }
        .search-input { 
            flex: 1;
            border: none; 
            padding: 12px 20px;
            outline: none; 
            font-size: 15px;
            color: #333; 
        }
        .search-btn { 
            background: white; 
            border: none; 
            color: var(--pxx-red); 
            cursor: pointer; 
            padding: 0 20px;
            font-size: 18px;
        }

        .search-keywords {
            margin-top: 6px;
            font-size: 12px;
            display: flex;
            gap: 15px;
            color: rgba(255, 255, 255, 0.8);
            margin-left: 5px;
            white-space: nowrap;
            overflow: visible;
        }
        .search-keywords span { 
            font-weight: 700; 
            color: #ffffff; 
            flex-shrink: 0;
        }
        .search-keywords a { 
            color: rgba(255, 255, 255, 0.9); 
            text-decoration: none; 
            transition: 0.2s; 
            flex-shrink: 0;
        }
        .search-keywords a:hover { 
            color: #ffffff; 
            text-decoration: underline; 
        }

        .nav-actions { display: flex; align-items: center; gap: 28px; height: 100%;} 
        .nav-item { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;}
        .nav-trigger-link { display: flex; flex-direction: column; align-items: center; cursor: pointer; color: white; }
        .nav-trigger-link:hover { opacity: 0.85; transform: translateY(-2px); }

        .profile-item { margin-left: 10px; } 
        .icon-wrap { 
            position: relative; 
            width: 32px;
            height: 32px;
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .icon-wrap i { font-size: 24px; color: white; }
        .icon-text { font-size: 13px; font-weight: 700; color: white; margin-top: 4px; }

        .badge { 
            position: absolute; 
            top: -6px; 
            right: -10px; 
            background: white; 
            color: var(--pxx-red); 
            font-size: 10px; 
            font-weight: 900; 
            width: 20px;
            height: 20px;
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            line-height: 1;
        }
        .badge.wide {
            width: 28px;
            border-radius: 10px;
        }

        /* ============================================
           🌟 修复：用户头像 - 固定尺寸 + 占位背景
           ============================================ */
        .user-avatar { 
            width: 34px; 
            height: 34px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 2px solid white; 
            background-color: #ddd;        /* 加载前的占位灰色 */
            display: block;               /* 消除 inline 间隙 */
        }

        .preview-box, .user-dropdown {
            position: absolute; 
            top: calc(100% + 5px); 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15); 
            border: 1px solid #eee; 
            visibility: hidden; 
            opacity: 0; 
            transform: translateY(10px); 
            transition: var(--transition); 
            color: #333; 
            z-index: 10001;
            overflow: hidden;
        }

        .preview-box::after, .user-dropdown::after { 
            content: ''; 
            position: absolute; 
            top: -8px; 
            border-left: 8px solid transparent; 
            border-right: 8px solid transparent; 
            border-bottom: 8px solid white; 
        }

        .nav-item.hover-trigger:hover .preview-box, 
        .nav-item.hover-trigger:hover .user-dropdown { 
            visibility: visible; 
            opacity: 1; 
            transform: translateY(0); 
        }

        .user-dropdown { right: 0; min-width: 220px; display: flex; flex-direction: column; padding: 5px 0; }
        .user-dropdown::after { right: 25px; } 

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
            padding: 12px 20px; 
            font-size: 14px; 
            color: #444; 
            font-weight: 600; 
            text-align: left; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            border-bottom: 1px solid #f9f9f9; 
        }
        .user-dropdown a:last-child { border-bottom: none; }
        .user-dropdown i { width: 20px; text-align: center; color: #888; font-size: 16px; }
        .user-dropdown a:hover { color: #28a745; background: #fafafa; }
        .user-dropdown a.logout-btn:hover, .user-dropdown a.logout-btn:hover i { color: var(--pxx-red); }

        .preview-box { right: -20px; width: 340px; cursor: default; }
        .preview-box::after { right: 35px; } 
        .preview-header { 
            padding: 15px 20px; 
            font-size: 14px; 
            font-weight: 700; 
            border-bottom: 1px solid #eee; 
            text-align: left; 
            background: #fafafa; 
            color: #333;
        }
        
        .preview-item { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            padding: 12px 20px; 
            border-bottom: 1px solid #f5f5f5; 
            text-align: left; 
        }
        .preview-item:last-child { border-bottom: none; }

        /* ============================================
           🌟 修复：预览商品图 - 固定尺寸 + 占位背景
           ============================================ */
        .preview-item img { 
            width: 45px; 
            height: 45px; 
            object-fit: cover; 
            border-radius: 6px; 
            border: 1px solid #eee; 
            background-color: #f0f0f0;    /* 加载前的占位色 */
            flex-shrink: 0;               /* 防止被压缩 */
            display: block;
        }
        
        .preview-item-info { 
            display: flex; 
            flex-direction: column; 
            flex: 1; 
            overflow: hidden; 
        }
        .preview-item-name { 
            font-size: 13px; 
            font-weight: 600; 
            color: #333; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        .preview-item-price { 
            font-size: 12px; 
            color: var(--pxx-red); 
            font-weight: 700; 
            margin-top: 3px; 
        }
        .preview-item-qty { 
            color: #888; 
            font-weight: 400; 
            font-size: 11px; 
        }

        .preview-notif-item { 
            padding: 15px 20px; 
            border-bottom: 1px solid #f5f5f5; 
            text-align: left; 
            font-size: 13px; 
            color: #444; 
            line-height: 1.4; 
        }
        .preview-notif-item:last-child { border-bottom: none; }

        .preview-empty { 
            padding: 30px 20px; 
            color: #999; 
            font-size: 13px; 
            text-align: center; 
        }
        .preview-footer { 
            display: block; 
            text-align: center; 
            padding: 12px; 
            background: white; 
            font-size: 13px; 
            color: var(--pxx-red); 
            font-weight: 700; 
            border-top: 1px solid #eee; 
        }
        .preview-footer:hover { background: #fafafa; }

        .preview-item-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        .meta-tag {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            line-height: 1;
        }
        .size-tag {
            background: #f0f0f0;
            color: #666;
            border: 1px solid #e0e0e0;
        }
        .colour-tag {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #ddd;
            padding: 0;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.3);
        }
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
            
            <!-- 🔔 Notifications -->
            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'member/notifications.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($isLoggedIn && $notif_count > 0): ?>
                            <span class="badge <?php echo $notif_count > 99 ? 'wide' : ''; ?>"><?php echo badge_number($notif_count); ?></span>
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

            <!-- ❤️ Wishlist (Favourites) -->
            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'member/wishlist.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-heart"></i>
                        <?php if ($isLoggedIn && $wishlist_count > 0): ?>
                            <span class="badge <?php echo $wishlist_count > 99 ? 'wide' : ''; ?>"><?php echo badge_number($wishlist_count); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-text">Wishlist</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Your Wishlist (<?php echo $wishlist_count; ?>)</div>
                    <?php if (!empty($recent_wishlist)): ?>
                        <?php foreach($recent_wishlist as $item): 
                            $wl_imgs = !empty($item['product_image']) ? explode(',', $item['product_image']) : [];
                            $wl_display_img = !empty($wl_imgs[0]) ? trim($wl_imgs[0]) : 'default.jpg';
                        ?>
                            <div class="preview-item">
                                <img src="<?php echo $path_prefix; ?>images/<?php echo htmlspecialchars($wl_display_img); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     onerror="this.onerror=null; this.src='<?php echo $path_prefix; ?>images/default.jpg'">
                                <div class="preview-item-info">
                                    <span class="preview-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="preview-item-meta">
                                        <?php if(!empty($item['size'])): ?>
                                            <span class="meta-tag size-tag"><?php echo htmlspecialchars($item['size']); ?></span>
                                        <?php endif; ?>
                                        <?php if(!empty($item['colour'])): ?>
                                            <span class="meta-tag colour-tag" style="background-color: <?php echo htmlspecialchars($item['colour']); ?>"></span>
                                            <span style="font-size:11px; color:#666;"><?php echo htmlspecialchars($item['colour']); ?></span>
                                        <?php endif; ?>
                                    </span>
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

            <!-- 🛒 Cart -->
            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $isLoggedIn ? $path_prefix.'memberProducts/unified_cart.php' : $path_prefix.'login_selection.php'; ?>" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($isLoggedIn && $cart_count > 0): ?>
                            <span class="badge <?php echo $cart_count > 99 ? 'wide' : ''; ?>"><?php echo badge_number($cart_count); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-text">Cart</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Shopping Cart (<?php echo $cart_count; ?> Items)</div>
                    <?php if (!empty($recent_cart)): ?>
                        <?php foreach($recent_cart as $item): 
                            $cart_imgs = !empty($item['product_image']) ? explode(',', $item['product_image']) : [];
                            $cart_display_img = !empty($cart_imgs[0]) ? trim($cart_imgs[0]) : 'default.jpg';
                            $cart_user_color = strtolower(trim($item['colour'])); 
                            foreach ($cart_imgs as $img) {
                                if ($cart_user_color !== '' && strpos(strtolower(trim($img)), $cart_user_color) !== false) {
                                    $cart_display_img = trim($img); break;
                                }
                            }
                        ?>
                            <div class="preview-item">
                                <img src="<?php echo $path_prefix; ?>images/<?php echo htmlspecialchars($cart_display_img); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     onerror="this.onerror=null; this.src='<?php echo $path_prefix; ?>images/default.jpg'">
                                <div class="preview-item-info">
                                    <span class="preview-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="preview-item-meta">
                                        <?php if(!empty($item['selected_size'])): ?>
                                            <span class="meta-tag size-tag"><?php echo htmlspecialchars($item['selected_size']); ?></span>
                                        <?php endif; ?>
                                        <?php if(!empty($item['colour'])): ?>
                                            <span class="meta-tag colour-tag" style="background-color: <?php echo htmlspecialchars($item['colour']); ?>"></span>
                                            <span style="font-size:11px; color:#666;"><?php echo htmlspecialchars($item['colour']); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="preview-item-price">
                                        RM <?php echo number_format($item['price'], 2); ?> 
                                        <span class="preview-item-qty">x <?php echo $item['quantity']; ?></span>
                                    </span>
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

            <!-- 👤 Profile -->
            <div class="nav-item hover-trigger profile-item">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $path_prefix; ?>member/profile.php" class="nav-trigger-link">
                        <div class="icon-wrap">
                            <img src="<?php echo $final_avatar_path; ?>" 
                                 onerror="this.onerror=null; this.src='<?php echo $path_prefix; ?>images/default.jpg'" 
                                 class="user-avatar" 
                                 alt="User">
                        </div>
                        <span class="icon-text"><?php echo htmlspecialchars($username); ?></span>
                    </a>
                    <div class="user-dropdown">
                        <a href="<?php echo $path_prefix; ?>member/profile.php"><i class="fa-solid fa-user-circle"></i> My Account</a>
                        <a href="<?php echo $path_prefix; ?>memberOrders/history.php"><i class="fa-solid fa-box"></i> My Orders</a>
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

</body>
</html>