<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🛠️ 判断登录状态
$isLoggedIn = false;
if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
}

// 🔥 自动登录逻辑
if (!$isLoggedIn && isset($_COOKIE['remember_me'])) {
    require_once 'includes/db.php'; 
    $token = $_COOKIE['remember_me'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['user_id']; 
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_image'] = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';
        $isLoggedIn = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        * { box-sizing: border-box; }
        a { text-decoration: none; color: inherit; }

        /* --- Header 固定样式 --- */
        .pxx-header { 
            background-color: #e60012; 
            color: white; 
            padding: 15px 0; 
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            z-index: 10000; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-container { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 0 30px; 
        }

        .logo-link { font-size: 50px; font-weight: 700; letter-spacing: 1px; line-height: 1; flex-shrink: 0; }

        /* 🌟 放大搜索栏：加宽 max-width，增加内边距和字体大小 */
        .search-section { flex: 1; margin: 0 40px 0 60px; max-width: 650px; }
        .search-container { display: flex; background-color: white; border-radius: 6px; overflow: hidden; border: 2px solid white; }
        .search-input { width: 100%; border: none; padding: 14px 24px; outline: none; font-size: 16px; color: #333; }
        .search-btn { background: white; border: none; color: #e60012; cursor: pointer; padding: 0 22px; font-size: 19px; }

        /* --- Icon 严格对齐排版 --- */
        .icon-section { 
    display: flex; 
    align-items: center; 
    gap: 5px; 
}

.dropdown-wrapper { 
    position: relative; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: center;
    min-width: 70px; 
    padding: 5px 0;
    cursor: pointer;
}

.icon-link { 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    gap: 4px; 
    font-size: 12px; 
    font-weight: 600; 
    width: 100%; 
    text-align: center;
    position: relative;
}

.icon-wrapper {
    position: relative;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    height: 28px; 
    width: 28px;
}

.icon-wrapper i { 
    font-size: 22px; 
}

.icon-badge {
    position: absolute;
    top: -6px; 
    right: -8px;
    background-color: #ffffff; 
    color: #e60012;
    font-size: 10px;
    font-weight: 800;
    width: 16px; 
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.user-avatar { 
    width: 28px; 
    height: 28px; 
    border-radius: 50%; 
    object-fit: cover; 
    border: 2px solid #fff; 
}

        /* --- 下拉框（加入滤镜阴影和向上箭头） --- */
        .dropdown-box {
            display: none;
            position: absolute;
            top: calc(100% + 15px); 
            background: white;
            color: #333;
            border-radius: 6px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.15));
            z-index: 10001;
            cursor: default;
        }
        .dropdown-wrapper:hover .dropdown-box { display: block; }

        /* 向上箭头 */
        .dropdown-box::before {
            content: "";
            position: absolute;
            top: -8px;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid transparent;
            z-index: 2;
        }

        /* 隐形桥梁 */
        .dropdown-box::after {
            content: "";
            position: absolute;
            top: -15px; 
            left: 0;
            right: 0;
            height: 15px;
            background: transparent;
            z-index: 1;
        }

        .center-box { width: 320px; right: 50%; transform: translateX(50%); }
        .center-box::before { right: 50%; transform: translateX(50%); }

        .account-box { width: 200px; right: 0; padding: 10px 0; }
        .account-box::before { right: 26px; } 

        .menu-link { 
            display: block; 
            padding: 12px 20px; 
            font-size: 15px; 
            color: #333; 
            text-align: left; 
            font-weight: 500;
            transition: all 0.2s ease; 
            white-space: nowrap; 
        }
        .menu-link:hover { 
            background-color: #f5f5f5; 
            color: #e60012; 
        }

        /* 🌟 预览框标题样式更新：加入 flex 排版以对齐图标和文字 */
        .box-header { 
            padding: 15px; 
            font-weight: bold; 
            border-bottom: 1px solid #eee; 
            text-align: left; 
            font-size: 15px; 
            color: #333; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        /* 给 box-header 里的 icon 加一点主题红 */
        .box-header i { color: #e60012; font-size: 16px; }

        .box-content { padding: 10px; max-height: 350px; overflow-y: auto; }
        .box-footer { padding: 12px; background: #fcfcfc; border-top: 1px solid #eee; }
        .box-btn { display: block; background: #e60012; color: white; text-align: center; padding: 10px; border-radius: 4px; font-weight: bold; font-size: 14px; }

        .preview-item { display: flex; gap: 12px; padding: 10px; align-items: center; border-bottom: 1px solid #f9f9f9; }
        .preview-item:hover { background-color: #fcfcfc; }
        .preview-img { width: 45px; height: 45px; background: #eee; border-radius: 4px; flex-shrink: 0; object-fit: cover; }
        .preview-info { flex: 1; text-align: left; }
        .preview-info .p-name { display: block; font-size: 13px; color: #333; margin-bottom: 4px; line-height: 1.3; }
        .preview-info .p-price { display: block; font-size: 14px; font-weight: bold; color: #e60012; }

        /* 隐形垫片 */
        .header-spacer { height: 98px; width: 100%; display: block; background: transparent; }
    </style>
</head>
<body>

<header class="pxx-header">
    <div class="header-container">
        <a href="index.php" class="logo-link">PXX Mall<span style="font-size: 12px; vertical-align: 28px;">&reg;</span></a>
        
        <div class="search-section">
    <form action="ViewAllProduct.php" method="GET" class="search-container" style="display: flex; gap: 10px;">
        <input type="text" name="query" class="search-input" placeholder="Search for products...">
        <select name="category" style="padding: 0 15px; border: none; background: white; border-radius: 6px; font-size: 14px;">
            <option value="">All</option>
            <option value="Men">👔 Men</option>
            <option value="Women">👗 Women</option>
        </select>
        <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
</div>

       <div class="icon-section">
    
    <!-- Notifications -->
    <div class="dropdown-wrapper">
        <a href="notifications.php" class="icon-link">
            <div class="icon-wrapper">
                <i class="fa-solid fa-bell"></i>
                <span class="icon-badge">2</span>
            </div>
            <span>Notifications</span>
        </a>
    </div>

    <!-- Wishlist -->
    <div class="dropdown-wrapper">
        <a href="wishlist.php" class="icon-link">
            <div class="icon-wrapper">
                <i class="fa-solid fa-heart"></i>
                <span class="icon-badge">5</span>
            </div>
            <span>Wishlist</span>
        </a>
    </div>

<!-- Your Cart Link -->
<div class="dropdown-wrapper">
    <a href="../member_cart/unified_cart.php" class="icon-link">
        <div class="icon-wrapper">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="icon-badge">0</span>
        </div>
        <span>Cart</span>
    </a>
</div>

<!-- Your Orders Link -->
<div class="dropdown-wrapper">
    <a href="../memberOrders/history.php" class="icon-link">
        <div class="icon-wrapper">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <span>Orders</span>
    </a>
</div>
    <!-- User -->
    <div class="dropdown-wrapper">
        <?php if ($isLoggedIn): ?>
            <a href="profile.php" class="icon-link">
                <div class="icon-wrapper">
                    <img src="images/<?php echo $avatar; ?>" class="user-avatar" alt="Avatar">
                </div>
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </a>
        <?php else: ?>
            <a href="login.php" class="icon-link">
                <div class="icon-wrapper">
                    <i class="fa-solid fa-user"></i>
                </div>
                <span>Login/signup</span>
            </a>
        <?php endif; ?>
    </div>

</div>

        </div>
    </div>
</header>

<div class="header-spacer"></div>

</body>
</html>