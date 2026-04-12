<?php 
// 引入你做好的精美头部
include 'includes/_head.php'; 
?>

<style>
    /* 身份选择页面专属样式 */
    .role-selection-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 85vh; 
        position: relative;
        background-image: url('images/pxxbg.jpeg'); 
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        padding: 40px 20px;
    }

    .role-container {
        text-align: center;
        max-width: 800px;
        width: 100%;/* --- 插入在第 25 行之后 --- */
.role-selection-wrapper::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.45); /* 🌑 遮罩黑度，0.45 左右最合适 */
    z-index: 1;
}

.role-container {
    position: relative;
    z-index: 2; /* 确保内容在遮罩上面 */
}
    }

    .role-container h1 {
    color: #ffffff; /* 🌟 改为白色 */
    text-shadow: 0 4px 12px rgba(0,0,0,0.4); /* 增加投影防止背景太亮 */
    font-size: 44px;
    font-weight: 900;
}

.role-container p {
    color: rgba(255, 255, 255, 0.9); /* 🌟 改为浅白色 */
    text-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

    /* 选项卡片布局 */
    .role-cards {
        display: flex;
        justify-content: center;
        gap: 40px;
        flex-wrap: wrap;
        align-items: stretch; /* 强制两张卡片高度一致 */
    }

    .role-card {
        background: rgba(255, 255, 255, 0.92); /* 🌟 半透明白 */
        backdrop-filter: blur(30px); /* 🌟 磨砂模糊效果 */
        -webkit-backdrop-filter: blur(10px);
        width: 320px;
        padding: 40px 30px;
        border-radius: 16px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .role-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); /* 基础阴影 */
    }

    .role-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 35px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .role-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .role-desc {
        font-size: 14px;
        color: #888;
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1; /* 占据剩余空间 */
    }

    /* 按钮基础样式 (对齐修正保留) */
    .role-btn {
        width: 220px;            /* 强制设定固定宽度，确保大小一致 */
        height: 50px;           /* 设定固定高度 */
        display: flex;          /* 为了垂直居中 */
        align-items: center;    /* 垂直居中 */
        justify-content: center;/* 水平居中 */
        color: white;
        border-radius: 50px;
        font-weight: 600;
        font-size: 15px;
        transition: background 0.3s;
        margin-top: auto;       /* 确保在 flex 布局中弹到底部 */
    }

    /* ========================================= */
    /* --- Member 专属绿色主题 --- */
    /* ========================================= */
    
    /* 1. 默认图标背景 (淡绿) */
    .member-card .role-icon { background-color: #e0f2f1; color: #28a745; }

    /* 2. 默认按钮背景 (绿) */
    .member-card .role-btn { background-color: #28a745; }

    /* 3. 悬停时的边框 (绿) */
    .member-card:hover { border-color: #28a745; }

    /* 4. 悬停时的图标背景 (绿) */
    .member-card:hover .role-icon { background-color: #28a745; color: #ffffff; }

    /* ========================================= */
    /* --- Admin 专属红色主题 --- */
    /* ========================================= */

    /* 1. 默认图标背景 (淡红) */
    .admin-card .role-icon { background-color: #fff0f1; color: #e60012; }

    /* 2. 默认按钮背景 (红) */
    .admin-card .role-btn { background-color: #e60012; }

    /* 3. 悬停时的边框 (红) */
    .admin-card:hover { border-color: #e60012; }

    /* 4. 悬停时的图标背景 (红) */
    .admin-card:hover .role-icon { background-color: #e60012; color: #ffffff; }

</style>

<div class="role-selection-wrapper">
    <div class="role-container">
        <h1>Welcome to PXX Mal!</h1>
        <p>Please select your account type to continue</p>

        <div class="role-cards">
            <a href="member/login.php" class="role-card member-card">
                <div class="role-icon">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="role-title">Member</div>
                <div class="role-desc">Login or sign up to shop, view orders, manage your wishlist and cart.</div>
                <div class="role-btn">MEMBER LOGIN</div>
            </a>

            <a href="admin/login.php" class="role-card admin-card">
                <div class="role-icon">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="role-title">Admin</div>
                <div class="role-desc">Access the admin portal to manage products, inventory, order, member and more.</div>
                <div class="role-btn">ADMIN LOGIN</div>
            </a>
        </div>
    </div>
</div>

<?php 
// 引入底部 - 修正了你之前的报错路径，去掉了前面的斜杠 /
include 'includes/_foot.php'; 
?>