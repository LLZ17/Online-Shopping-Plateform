<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* 顶部导航条：增加固定效果 */
        .info-navbar {
            background-color: #FF0000;
            height: 80px;
            display: flex;
            align-items: center;
            padding: 0 40px;
            justify-content: space-between;
            
            /* 固定在顶部的核心代码 */
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000; /* 确保在内容之上 */
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); /* 可选：滑下来时有一点阴影更好看 */
        }

        /* Logo 区域 */
        .logo-section a {
            color: white;
            text-decoration: none;
            font-size: 42px;
            font-family: 'Arial Black', Gadget, sans-serif;
            font-weight: 900;
            display: flex;
            align-items: flex-start;
        }

        .logo-section span {
            font-size: 18px;
            margin-left: 2px;
            margin-top: 5px;
        }

        /* 同一行的选项菜单 */
        .info-nav-links {
            display: flex;
            list-style: none;
            align-items: center;
        }

        .info-nav-links li {
            display: flex;
            align-items: center;
        }

        /* 选项之间的白色竖线 */
        .info-nav-links li:not(:last-child)::after {
            content: "|";
            color: white;
            margin: 0 15px;
            font-size: 18px;
            opacity: 0.8;
        }

        /* 链接文字样式 */
        .info-nav-links a {
            color: white;
            text-decoration: none;
            font-family: Arial, sans-serif;
            font-size: 18px;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .info-nav-links a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        /* 响应式：如果屏幕太窄，自动换行 */
        @media (max-width: 900px) {
            .info-navbar {
                flex-direction: column;
                height: auto;
                padding: 15px;
            }
            .info-nav-links {
                margin-top: 10px;
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<nav class="info-navbar">
    <div class="logo-section">
        <a href="../info/aboutus.php">PXX Mall<span>®</span></a>
    </div>

    <ul class="info-nav-links">
        <li><a href="../info/aboutus.php">About Us</a></li>
        <li><a href="../info/policy.php">Customer Policy</a></li>
        <li><a href="../info/terms.php">Terms & Conditions</a></li>
        <li><a href="../info/disclaimer.php">Disclaimer</a></li>
        <li><a href="../info/contactus.php">Contact Us</a></li>
    </ul>
</nav>

</body>
</html>