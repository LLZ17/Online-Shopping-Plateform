<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PXX MALL | Men & Women Fashion</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    
    <style>
        /* --- 核心布局 --- */
        html, body { height: 100%; margin: 0; }
        body { font-family: 'Helvetica', Arial, sans-serif; background: #f4f4f4; display: flex; flex-direction: column; }
        .main-content { flex: 1; } 

        :root { --main-red: #e60012; }
        .container { width: 90%; max-width: 1200px; margin: auto; }

        /* --- Header 顶部导航栏 --- */
        header { background: var(--main-red); color: white; padding: 12px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .nav-bar { display: flex; justify-content: space-between; align-items: center; gap: 20px; }

        /* 左侧 Logo */
        .nav-left { display: flex; align-items: center; gap: 15px; }
        .logo { font-size: 26px; font-weight: bold; text-decoration: none; color: white; letter-spacing: 1px; }
        .btn-main { color: white; text-decoration: none; font-size: 13px; border: 1px solid rgba(255,255,255,0.6); padding: 5px 12px; border-radius: 4px; transition: 0.3s; }
        .btn-main:hover { background: white; color: var(--main-red); }

        /* --- 中间搜索框 (加了放大镜) --- */
        .nav-center { flex: 1; max-width: 500px; }
        .search-container { 
            display: flex; 
            background: white; 
            border-radius: 4px; 
            overflow: hidden; /* 让里面的东西不出界 */
        }
        .search-input { 
            flex: 1; 
            padding: 10px 15px; 
            border: none; 
            outline: none; 
            font-size: 14px; 
        }
        .search-btn { 
            background: white; 
            border: none; 
            padding: 0 15px; 
            cursor: pointer; 
            color: var(--main-red); /* 放大镜颜色 */
            font-size: 16px;
            transition: 0.3s;
        }
        .search-btn:hover { background: #f0f0f0; }

        /* --- 右侧功能区 --- */
        .nav-right { display: flex; align-items: center; gap: 25px; }
        .nav-right a { color: white; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px; transition: 0.3s; }
        .nav-right i { font-size: 18px; }
        
        /* 鼠标悬停特效 */
        .nav-right a:hover { color: #ffd700; } 
        .wishlist-icon:hover i { color: #ffffff !important; transform: scale(1.3); } 
        .cart-icon:hover i { transform: translateY(-3px); } 
    </style>
</head>
<body>

<header>
    <div class="container nav-bar">
        <div class="nav-left">
            <a href="index.php" class="logo" title="Back to Homepage">PXXMALL</a>
            <a href="index.php" class="btn-main" title="Go to Main Page">Main Page</a>
        </div>

        <div class="nav-center">
            <form action="search.php" method="GET" class="search-container">
                <input type="text" name="query" class="search-input" placeholder="Search for Men or Women Clothing..." title="Type to search">
                <button type="submit" class="search-btn" title="Search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>

        <div class="nav-right">
            <a href="wishlist.php" class="wishlist-icon" title="View your saved Wishlist">
                <i class="fa-solid fa-heart"></i> Wishlist
            </a>
            <a href="cart.php" class="cart-icon" title="View your Shopping Cart">
                <i class="fa-solid fa-cart-shopping"></i> Cart
            </a>
            <a href="login.php" title="Login or Manage your account">
                <i class="fa-solid fa-user"></i> Login/Acc
            </a>
        </div>
    </div>
</header>

<div class="main-content">