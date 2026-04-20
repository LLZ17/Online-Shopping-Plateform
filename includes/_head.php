<?php
/**
 * =================================================================================
 * PXX MALL - ENTERPRISE GLOBAL HEADER (VERSION 9.1 - CLEAN FIX)
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 获取当前访问的文件名，比如 'login.php' 或 'index.php'
$current_script = basename($_SERVER['PHP_SELF']);

// 修改点：如果当前已经在登录页面，绝对不要再执行任何 Location: login.php 的跳转
if ($current_script !== 'login.php') {
    // 这里放你对普通页面的保护逻辑
    // 如果 index.php 是允许游客看的，这里甚至不需要写跳转
}

// ----------------------------------------------------------------
// 1. 数据库连接 - 只尝试 ../includes/db.php (因为你说这个路径是对的)
// ----------------------------------------------------------------
if (!isset($pdo) || $pdo === null) {
    $db_path = __DIR__ . '/../includes/db.php';
    if (file_exists($db_path)) {
        require_once $db_path;
    }
}

// ----------------------------------------------------------------
// 2. 全局变量初始化
// ----------------------------------------------------------------
$isLoggedIn = false;
$username = "";
$avatar_file = "default.jpg";

$current_page_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_subfolder = ($current_page_dir === 'admin' || $current_page_dir === 'member' || $current_page_dir === 'memberProducts');
$path_prefix = $is_subfolder ? '../' : '';

// 3. 获取用户数据 - 强制从 SQL 获取头像
if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $username = $_SESSION['username'];
    
    if (isset($pdo) && $pdo !== null) {
        try {
            $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user_data && !empty($user_data['profile_photo'])) {
                $avatar_file = $user_data['profile_photo'];
                $_SESSION['profile_photo'] = $avatar_file;
            }
        } catch (PDOException $e) {
            $avatar_file = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
        }
    } else {
        $avatar_file = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
    }
}

// 4. Remember Me
if (!$isLoggedIn && isset($_COOKIE['remember_me']) && isset($pdo) && $pdo !== null) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
        $stmt->execute([$_COOKIE['remember_me']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user_id'] = $user['user_id']; 
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $avatar_file = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';
            $_SESSION['profile_photo'] = $avatar_file;
            $isLoggedIn = true;
            $username = $_SESSION['username'];
        }
    } catch (PDOException $e) {}
}

$final_avatar_path = $path_prefix . 'images/' . $avatar_file;

// =================================================================================
// 5. 动态获取搜索关键词 (根据 gender 页面切换)
// =================================================================================
$current_category = isset($_GET['category']) ? $_GET['category'] : '';
$gender_filter = '';
$dynamic_keywords = [];

if (isset($pdo) && $pdo !== null) {
    try {
        if (!empty($current_category)) {
            if (stripos($current_category, 'Men') === 0) $gender_filter = 'Men';
            elseif (stripos($current_category, 'Women') === 0) $gender_filter = 'Women';
        }
        
        if (!empty($gender_filter)) {
            $kw_stmt = $pdo->prepare("
                SELECT DISTINCT p.keywords 
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
                WHERE c.category_gender LIKE :gender AND p.keywords IS NOT NULL AND TRIM(p.keywords) != ''
                LIMIT 5
            ");
            $kw_stmt->execute(['gender' => $gender_filter . '%']);
        } else {
            $kw_stmt = $pdo->prepare("SELECT DISTINCT keywords FROM products WHERE keywords IS NOT NULL AND TRIM(keywords) != '' LIMIT 5");
            $kw_stmt->execute();
        }
        
        $fetched_keywords = $kw_stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($fetched_keywords as $kw_string) {
            $parts = explode(',', $kw_string);
            foreach ($parts as $p) {
                $p = trim($p);
                if (!empty($p) && !in_array($p, $dynamic_keywords)) {
                    $dynamic_keywords[] = $p;
                }
                if (count($dynamic_keywords) >= 5) break 2;
            }
        }
    } catch(PDOException $e) {
        $dynamic_keywords = [];
    }
}

if (empty($dynamic_keywords)) {
    if ($gender_filter == 'Women') $dynamic_keywords = ['Korean', 'Dress', 'Heels', 'Handbag', 'Skirt'];
    elseif ($gender_filter == 'Men') $dynamic_keywords = ['Streetwear', 'Sneakers', 'Suit', 'T-Shirt', 'Jeans'];
    else $dynamic_keywords = ['Korean', 'Streetwear', 'New Arrival', 'Sale', 'Trending'];
}

// =================================================================================
// 6. 获取用户的通知、愿望清单和购物车数据
// =================================================================================
$notif_count = 0; 
$recent_notifs = [];
$wishlist_count = 0; 
$recent_wishlist = [];
$cart_count = 0; 
$recent_cart = [];

if ($isLoggedIn && isset($pdo) && $pdo !== null) {
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    
    if ($uid > 0) {
        
        // --- CART 数据 ---
        try {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as total FROM cart WHERE user_id = ?");
            $stmt->execute([$uid]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $cart_count = isset($result['total']) ? (int)$result['total'] : 0;
            
            if ($cart_count > 0) {
                $stmt = $pdo->prepare("
                    SELECT p.name, p.price, c.quantity, p.product_image, 
                           COALESCE(c.size, 'N/A') as size, 
                           COALESCE(c.color, 'N/A') as color
                    FROM cart c 
                    JOIN products p ON c.product_id = p.product_id 
                    WHERE c.user_id = ? 
                    ORDER BY c.cart_id DESC LIMIT 3
                ");
                $stmt->execute([$uid]);
                $recent_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            $cart_count = 0;
        }

        // --- WISHLIST 数据 ---
        try {
            $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
            $count_stmt->execute([$uid]);
            $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
            $wishlist_count = isset($count_result['total']) ? (int)$count_result['total'] : 0;
            
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
        } catch(PDOException $e) {
            $wishlist_count = 0;
        }

        // --- NOTIFICATIONS ---
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$uid]);
            $notif_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $notif_count = isset($notif_result['total']) ? (int)$notif_result['total'] : 0;
            
            $stmt = $pdo->prepare("SELECT message, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
            $stmt->execute([$uid]);
            $recent_notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $notif_count = 0;
        }
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
        :root {
            --pxx-red: #e60012;
            --white: #ffffff;
            --gray-100: #f8f9fa;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body { margin: 0; font-family: 'Poppins', sans-serif; background-color: var(--gray-100); }
        * { box-sizing: border-box; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }

        .pxx-header { 
            background-color: var(--pxx-red); 
            color: white; 
            padding: 10px 0; 
            position: fixed; top: 0; left: 0; right: 0; 
            z-index: 10000; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header-container { 
            display: flex; justify-content: space-between; align-items: center; 
            max-width: 1400px; margin: 0 auto; padding: 0 40px; 
            height: 70px;
        }

        .logo-link { 
            font-family: 'Montserrat', sans-serif;
            font-size: 40px; font-weight: 800; letter-spacing: -1px; 
            display: flex; align-items: center;
            height: 100%;
            margin-right: 20px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .logo-link sup { font-size: 16px; font-weight: 700; margin-left: 2px; }

        .search-section { 
            flex: 1; 
            max-width: 550px; 
            min-width: 300px;
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
            height: 38px; 
        }
        .search-input { 
            width: 100%; 
            border: none; 
            padding: 8px 15px; 
            outline: none; 
            font-size: 14px; 
            color: #333; 
            height: 100%;
        }
        .search-btn { 
            background: white; 
            border: none; 
            color: var(--pxx-red); 
            cursor: pointer; 
            padding: 0 15px; 
            font-size: 16px; 
            height: 100%;
        }
        .search-container select {
            padding: 0 8px; 
            border: none; 
            background: white; 
            font-size: 12px; 
            width: 85px; 
            cursor: pointer; 
            border-left: 1px solid #eee; 
            height: 100%;
        }

        .search-keywords {
            margin-top: 4px;
            font-size: 11px;
            display: flex;
            gap: 10px;
            color: rgba(255, 255, 255, 0.8);
            margin-left: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .search-keywords span { 
            font-weight: 700; 
            color: #ffffff; 
            white-space: nowrap; 
            flex-shrink: 0;
        }
        .search-keywords a { 
            color: rgba(255, 255, 255, 0.9); 
            text-decoration: none; 
            transition: 0.2s; 
            white-space: nowrap;
        }
        .search-keywords a:hover { color: #ffffff; text-decoration: underline; }

        .nav-actions { 
            display: flex; 
            align-items: center; 
            gap: 24px; 
            height: 100%;
            flex-shrink: 0; 
        } 
        .nav-item { 
            position: relative; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            height: 100%;
        }
        .nav-trigger-link { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            cursor: pointer; 
            color: white; 
        }
        .nav-trigger-link:hover { opacity: 0.85; transform: translateY(-2px); }

        .profile-item { margin-left: 5px; } 
        .icon-wrap { 
            position: relative; 
            height: 28px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .icon-wrap i { font-size: 22px; color: white; }
        .icon-text { 
            font-size: 12px; 
            font-weight: 700; 
            color: white; 
            margin-top: 3px; 
            white-space: nowrap; 
        }

        .badge { 
            position: absolute; 
            top: -5px; 
            right: -10px; 
            background: white; 
            color: var(--pxx-red); 
            font-size: 10px; 
            font-weight: 900; 
            width: 16px; 
            height: 16px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .user-avatar { 
            width: 32px; 
            height: 32px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 2px solid white; 
        }

        .preview-box, .user-dropdown {
            position: absolute; 
            top: calc(100% + 8px); 
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

        .preview-box { 
            right: -20px; 
            width: 340px; 
            cursor: default; 
        }
        .preview-box::after { right: 35px; } 
        .preview-header { 
            padding: 12px 20px; 
            font-size: 13px; 
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
        .preview-item img { 
            width: 50px; 
            height: 50px; 
            object-fit: cover; 
            border-radius: 6px; 
            border: 1px solid #eee; 
            background: #f0f0f0; 
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
        .preview-item-meta { 
            font-size: 11px; 
            color: #888; 
            margin-top: 2px; 
        }
        .preview-item-price { 
            font-size: 12px; 
            color: var(--pxx-red); 
            font-weight: 700; 
            margin-top: 2px; 
        }
        .preview-item-qty { 
            color: #888; 
            font-weight: 400; 
            font-size: 11px; 
        }

        .preview-notif-item { 
            padding: 12px 20px; 
            border-bottom: 1px solid #f5f5f5; 
            text-align: left; 
            font-size: 13px; 
            color: #444; 
            line-height: 1.4; 
            display: flex; 
            align-items: start; 
            gap: 8px; 
        }
        .preview-notif-item:last-child { border-bottom: none; }
        .preview-notif-item.unread { 
            background: #fff8f8; 
            border-left: 3px solid var(--pxx-red); 
        }
        .preview-notif-item i { 
            margin-top: 3px; 
            font-size: 10px; 
            color: var(--pxx-red); 
        }

        .preview-empty { 
            padding: 25px 20px; 
            color: #999; 
            font-size: 13px; 
            text-align: center; 
        }
        .preview-footer { 
            display: block; 
            text-align: center; 
            padding: 10px; 
            background: white; 
            font-size: 13px; 
            color: var(--pxx-red); 
            font-weight: 700; 
            border-top: 1px solid #eee; 
        }
        .preview-footer:hover { background: #fafafa; }

        .header-spacer { height: 110px; width: 100%; display: block; }
    </style>
</head>
<body>

<header class="pxx-header">
    <div class="header-container">
        
        <a href="<?php echo $path_prefix; ?>index.php" class="logo-link">PXX Mall<sup>&reg;</sup></a>
        
        <div class="search-section">
            <form action="<?php echo $path_prefix; ?>ViewAllProduct.php" method="GET" class="search-container">
                <input type="text" name="query" class="search-input" placeholder="Search for Clothing">
                <select name="category" id="f_gender" required>
                    <option value="">All</option>
                    <option value="Men" <?php echo ($gender_filter == 'Men') ? 'selected' : ''; ?>>👔 Men</option>
                    <option value="Women" <?php echo ($gender_filter == 'Women') ? 'selected' : ''; ?>>👗 Women</option>
                </select>
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            
            <div class="search-keywords">
                <span>Trending:</span>
                <?php foreach($dynamic_keywords as $kw): ?>
                    <a href="<?php echo $path_prefix; ?>ViewAllProduct.php?query=<?php echo urlencode($kw); ?>&category=<?php echo urlencode($gender_filter ?: 'All'); ?>">
                        <?php echo htmlspecialchars($kw); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="nav-actions">
            
            <!-- Notifications -->
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
                    <div class="preview-header">Notifications (<?php echo $notif_count; ?> unread)</div>
                    <?php if (!empty($recent_notifs)): ?>
                        <?php foreach($recent_notifs as $notif): ?>
                            <div class="preview-notif-item <?php echo ($notif['is_read'] == 0) ? 'unread' : ''; ?>">
                                <i class="fa-solid fa-circle"></i>
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </div>
                        <?php endforeach; ?>
                        <a href="<?php echo $path_prefix.'member/notifications.php'; ?>" class="preview-footer">View All Notifications</a>
                    <?php else: ?>
                        <div class="preview-empty">No notifications yet.</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Wishlist -->
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
                    <?php if (!empty($recent_wishlist) && count($recent_wishlist) > 0): ?>
                        <?php foreach($recent_wishlist as $item): ?>
                            <div class="preview-item">
                                <img src="<?php echo $path_prefix; ?>images/<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'default.jpg'; ?>" 
                                     onerror="this.onerror=null; this.src='<?php echo $path_prefix; ?>images/default.jpg';" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
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

            <!-- Cart -->
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
                    <?php if (!empty($recent_cart) && count($recent_cart) > 0): ?>
                        <?php foreach($recent_cart as $item): ?>
                            <div class="preview-item">
                                <img src="<?php echo $path_prefix; ?>images/<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'default.jpg'; ?>" 
                                     onerror="this.onerror=null; this.src='<?php echo $path_prefix; ?>images/default.jpg';" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <div class="preview-item-info">
                                    <span class="preview-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="preview-item-meta">
                                        <?php if (isset($item['size']) && $item['size'] != 'N/A'): ?>
                                            Size: <?php echo htmlspecialchars($item['size']); ?> 
                                        <?php endif; ?>
                                        <?php if (isset($item['color']) && $item['color'] != 'N/A'): ?>
                                            | Color: <?php echo htmlspecialchars($item['color']); ?>
                                        <?php endif; ?>
                                    </span>
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

            <!-- Profile -->
            <div class="nav-item hover-trigger profile-item">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $path_prefix; ?>member/profile.php" class="nav-trigger-link">
                        <div class="icon-wrap">
                            <img src="<?php echo $final_avatar_path; ?>" 
                                 onerror="this.onerror=null; this.src='<?php echo $path_prefix; ?>images/default.jpg';" 
                                 class="user-avatar" 
                                 alt="User">
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
                        <div class="icon-wrap"><i class="fa-solid fa-circle-user" style="font-size:26px;"></i></div>
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