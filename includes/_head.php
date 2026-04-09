<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🛠️ 核心修正：判断是否登录（同时兼容 username 和 user_id）
$isLoggedIn = false;
if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
}

// 🔥 新增：自动登录逻辑 (检查 SQL 记录)
if (!$isLoggedIn && isset($_COOKIE['remember_me'])) {
    require_once 'includes/db.php'; 
    $token = $_COOKIE['remember_me'];
    
    // 查找 Token 匹配的用户
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // 恢复 Session
        $_SESSION['user_id'] = $user['user_id']; 
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_image'] = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';
        
        // 更新最后登录时间
        $updateTime = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $updateTime->execute([$user['user_id']]);
        
        $isLoggedIn = true; // 状态设为已登录
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PXX Mall | Your Best Online Shopping Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background-color: #ffffff; display: flex; flex-direction: column; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        * { box-sizing: border-box; }
        .main-content { flex: 1; padding: 20px; }

        header { background-color: #e60012; color: white; padding: 15px 0; position: relative; z-index: 1000; }
        .header-container { display: flex; justify-content: space-between; align-items: center; max-width: 1400px; margin: 0 auto; padding: 0 30px; }

        .logo-link { font-size: 50px; font-weight: 700; letter-spacing: 1px; line-height: 1; flex-shrink: 0; }

        .search-section { flex: 1; margin: 0 60px; }
        .search-container { display: flex; background-color: white; border-radius: 6px; overflow: hidden; border: 2px solid white; }
        .search-input { width: 100%; border: none; padding: 12px 20px; outline: none; font-size: 15px; color: #333; }
        .search-btn { background: white; border: none; color: #e60012; cursor: pointer; padding: 0 20px; font-size: 18px; }

        .icon-section { display: flex; align-items: center; gap: 5px; } 

        .dropdown-wrapper { 
            position: relative; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center;
            min-width: 100px; 
            padding: 5px 0;
            cursor: pointer;
        }

        .icon-link { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            gap: 5px; 
            font-size: 12px; 
            font-weight: 600; 
            width: 100%; 
            text-align: center;
            position: relative;
        }
        .icon-link i { font-size: 24px; transition: all 0.3s ease; }

        /* 🔥 头像样式 🔥 */
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            margin-bottom: 2px;
        }

        .icon-badge {
            position: absolute;
            top: -6px; 
            right: 32px;
            background-color: #ffffff; 
            color: #000000;
            font-size: 9px;
            font-weight: 600;
            width: 15px; 
            height: 15px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2); 
            pointer-events: none;
            z-index: 10;
        }

        .wishlist:hover i { color: #ffffff; transform: scale(1.1); }
        .cart:hover i { transform: translateY(-3px); }
        .login:hover { color: #ffffff; }

        .dropdown-box {
            display: none;
            position: absolute;
            top: 58px;
            right: 0;
            width: 350px;
            background: white;
            color: #333;
            border-radius: 8px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.25);
            border: 1px solid #ddd;
            z-index: 9999;
            cursor: default;
        }

        .dropdown-wrapper:hover .dropdown-box { display: block; }

        .box-header { padding: 15px; font-weight: bold; border-bottom: 2px solid #f4f4f4; font-size: 15px; text-align: left; }
        .box-content { max-height: 480px; overflow-y: auto; padding: 10px 15px; }

        .preview-item { display: flex; gap: 12px; margin-bottom: 15px; align-items: center; border-bottom: 1px solid #f9f9f9; padding-bottom: 10px; }
        .preview-img { width: 55px; height: 65px; background: #eee; border-radius: 4px; flex-shrink: 0; }
        .preview-info { flex: 1; font-size: 13px; text-align: left; }
        .preview-info .p-name { display: block; font-weight: bold; color: #222; margin-bottom: 4px; line-height: 1.2; }
        .preview-info .p-price { color: #e60012; font-weight: bold; font-size: 14px; }

        .box-footer { padding: 15px; background: #fcfcfc; border-top: 1px solid #eee; }
        .box-btn { display: block; background: #e60012; color: white; text-align: center; padding: 12px; border-radius: 4px; font-weight: bold; }
        
        .box-content::-webkit-scrollbar { width: 6px; }
        .box-content::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <a href="index.php" class="logo-link">PXX Mall<span style="font-size: 12px; vertical-align: 28px; line-height: 0; font-weight: 400; margin-left: 2px;">&reg;</span> </a>
        <div class="search-section">
            <form action="search.php" method="GET" class="search-container">
                <input type="text" name="query" class="search-input" placeholder="Search for men or women clothes...">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>

        <div class="icon-section">
            <div class="dropdown-wrapper">
                <a href="wishlist.php" class="icon-link wishlist">
                    <i class="fa-solid fa-heart"></i>
                    <span class="icon-badge">5</span>
                    <span>Wishlist</span>
                </a>
                <div class="dropdown-box">
                    <div class="box-header">❤️ Wishlist Preview</div>
                    <div class="box-content">
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Classic Leather Watch</span><span class="p-price">RM 199.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Retro Sunglasses</span><span class="p-price">RM 45.00</span></div></div>
                    </div>
                    <div class="box-footer"><a href="wishlist.php" class="box-btn">Go to Wishlist</a></div>
                </div>
            </div>

            <div class="dropdown-wrapper">
                <a href="cart.php" class="icon-link cart">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="icon-badge">12</span>
                    <span>Cart</span>
                </a>
                <div class="dropdown-box">
                    <div class="box-header"> <i class="fa-solid fa-cart-shopping"></i> Shopping Cart Preview</div>
                    <div class="box-content">
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Premium White T-Shirt</span><span class="p-price">RM 49.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Slim Fit Denim Jeans</span><span class="p-price">RM 129.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Nike Air Max Sneakers</span><span class="p-price">RM 450.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Adidas Shorts</span><span class="p-price">RM 89.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Puma Sports Cap</span><span class="p-price">RM 59.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Cotton Hoodie</span><span class="p-price">RM 119.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Leather Wallet</span><span class="p-price">RM 75.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Bluetooth Earbuds</span><span class="p-price">RM 210.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Gym Shaker</span><span class="p-price">RM 35.00</span></div></div>
                        <div class="preview-item"><div class="preview-img"></div><div class="preview-info"><span class="p-name">Oxford Shirt</span><span class="p-price">RM 95.00</span></div></div>
                    </div>
                    <div class="box-footer"><a href="cart.php" class="box-btn">Check Out Now</a></div>
                </div>
            </div>

            <div class="dropdown-wrapper">
                <?php if ($isLoggedIn): ?>
                    <a href="profile.php" class="icon-link login">
                        <?php 
                            $avatar = (isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])) ? $_SESSION['profile_image'] : 'default.jpg';
                            $displayUname = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';
                        ?>
                        <img src="images/<?php echo $avatar; ?>" class="user-avatar" alt="Avatar">
                        <span><?php echo htmlspecialchars($displayUname); ?></span>
                    </a>
                    <div class="dropdown-box" style="width: 150px;">
                        <div class="box-header"><a href="profile.php" style="color:black; display:block; width: 100%;">My Account</a></div>
                        <div class="box-content" style="padding: 10px;">
                            <a href="logout.php" style="color: #e60012; font-weight: bold; font-size: 14px; display: block;">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="icon-link login">
                        <i class="fa-solid fa-user"></i>
                        <span>Login/Sign-up</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>