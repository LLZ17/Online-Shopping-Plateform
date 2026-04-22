<?php 
// 引入你做好的精美头部
include 'includes/_head.php'; 
?>

    <link rel="stylesheet" href="../css/app.css">



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