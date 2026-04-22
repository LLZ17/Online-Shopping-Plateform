<?php
/**
 * =================================================================================
 * PXX MALL - ENTERPRISE GLOBAL HEADER (VERSION 8.8 - FULL RESTORE)
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. 全局变量初始化
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

// 4. Remember Me 逻辑
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

// 5. 动态搜索关键词
$current_category = isset($_GET['category']) ? $_GET['category'] : '';
$dynamic_keywords = [];
if (isset($pdo)) {
    try {
        $kw_stmt = $pdo->prepare("SELECT DISTINCT keywords FROM products WHERE keywords IS NOT NULL AND keywords != '' ORDER BY RAND() LIMIT 5");
        $kw_stmt->execute();
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
    } catch(PDOException $e) {}
}
if (empty($dynamic_keywords)) $dynamic_keywords = ['T-Shirt', 'Jeans', 'Sneakers', 'Dress'];

// 6. 获取通知、愿望清单、购物车详细数据
$notif_count = 0; $recent_notifs = [];
$wishlist_count = 0; $recent_wishlist = [];
$cart_count = 0; $recent_cart = [];

if ($isLoggedIn && isset($pdo)) {
    $uid = $_SESSION['user_id'];
    try {
        // 购物车详情
        $stmt = $pdo->prepare("SELECT p.name, p.price, c.quantity, p.product_image FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ? ORDER BY c.cart_id DESC LIMIT 3");
        $stmt->execute([$uid]);
        $recent_cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cart_count = array_sum(array_column($recent_cart, 'quantity'));

        // 愿望清单详情
        $stmt = $pdo->prepare("SELECT p.name, p.price, p.product_image FROM favourites f JOIN products p ON f.product_id = p.product_id WHERE f.user_id = ? LIMIT 3");
        $stmt->execute([$uid]);
        $recent_wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favourites WHERE user_id = ?");
        $stmt->execute([$uid]);
        $wishlist_count = (int)$stmt->fetchColumn();

        // 通知详情
        $stmt = $pdo->prepare("SELECT message FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$uid]);
        $recent_notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $notif_count = count($recent_notifs);
    } catch(PDOException $e) {}
}

function badge_number($num) { return $num > 99 ? '99+' : $num; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $path_prefix; ?>../css/app.css">
    <style>
        /* 强制修复滚动补丁：防止被 Header 锁死 */
        html, body { overflow-y: auto !important; height: auto !important; }
        body { padding-top: 90px !important; }
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
                    <a href="<?php echo $path_prefix; ?>ViewAllProduct.php?query=<?php echo urlencode($kw); ?>">
                        <?php echo htmlspecialchars($kw); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="nav-actions">
            
            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $path_prefix; ?>member/notifications.php" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($isLoggedIn && $notif_count > 0): ?>
                            <span class="badge"><?php echo badge_number($notif_count); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-text">Notifications</span>
                </a>
                <?php if ($isLoggedIn): ?>
                <div class="preview-box">
                    <div class="preview-header">Recent Notifications</div>
                    <?php if (!empty($recent_notifs)): ?>
                        <?php foreach($recent_notifs as $notif): ?>
                            <div class="preview-notif-item"><i class="fa-solid fa-circle-info"></i> <?php echo htmlspecialchars($notif['message']); ?></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="preview-empty">No new notifications.</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $path_prefix; ?>member/wishlist.php" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-heart"></i>
                        <?php if ($isLoggedIn && $wishlist_count > 0): ?>
                            <span class="badge"><?php echo badge_number($wishlist_count); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-text">Wishlist</span>
                </a>
                <?php if ($isLoggedIn && !empty($recent_wishlist)): ?>
                <div class="preview-box">
                    <div class="preview-header">Wishlist (<?php echo $wishlist_count; ?>)</div>
                    <?php foreach($recent_wishlist as $item): ?>
                        <div class="preview-item">
                            <img src="<?php echo $path_prefix; ?>images/<?php echo $item['product_image']; ?>">
                            <div class="preview-item-info">
                                <span class="preview-item-name"><?php echo $item['name']; ?></span>
                                <span class="preview-item-price">RM <?php echo number_format($item['price'], 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="nav-item <?php echo $isLoggedIn ? 'hover-trigger' : ''; ?>">
                <a href="<?php echo $path_prefix; ?>memberProducts/unified_cart.php" class="nav-trigger-link">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if ($isLoggedIn && $cart_count > 0): ?>
                            <span class="badge"><?php echo badge_number($cart_count); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="icon-text">Cart</span>
                </a>
                <?php if ($isLoggedIn && !empty($recent_cart)): ?>
                <div class="preview-box">
                    <div class="preview-header">Cart (<?php echo $cart_count; ?>)</div>
                    <?php foreach($recent_cart as $item): ?>
                        <div class="preview-item">
                            <img src="<?php echo $path_prefix; ?>images/<?php echo $item['product_image']; ?>">
                            <div class="preview-item-info">
                                <span class="preview-item-name"><?php echo $item['name']; ?></span>
                                <span class="preview-item-qty">x <?php echo $item['quantity']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="nav-item hover-trigger profile-item">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $path_prefix; ?>member/profile.php" class="nav-trigger-link">
                        <div class="icon-wrap">
                            <img src="<?php echo $final_avatar_path; ?>" onerror="this.src='<?php echo $path_prefix; ?>images/default.jpg'" class="user-avatar">
                        </div>
                        <span class="icon-text"><?php echo htmlspecialchars($username); ?></span>
                    </a>
                    <div class="user-dropdown">
                        <a href="<?php echo $path_prefix; ?>member/profile.php"><i class="fa-solid fa-user-circle"></i> Account</a>
                        <a href="<?php echo $path_prefix; ?>memberOrders/history.php"><i class="fa-solid fa-box"></i> Orders</a>
                        <a href="<?php echo $path_prefix; ?>member/logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $path_prefix; ?>login_selection.php" class="nav-trigger-link">
                        <div class="icon-wrap"><i class="fa-solid fa-circle-user" style="font-size:28px;"></i></div>
                        <span class="icon-text">Login/Sign-up</span>
                    </a>
                <?php endif; ?>
            </div>

        </div> </div> </header>

</body>
</html>