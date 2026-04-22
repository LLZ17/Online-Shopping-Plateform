<?php
/**
 * =================================================================================
 * PXX MALL - ADMINISTRATIVE CONTROL PORTAL (V110.10 - PDO FULL INTEGRATION)
 * =================================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once '../includes/db.php'; 

if (isset($pdo) && !isset($conn)) {
    $conn = $pdo;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../admin/login.php"); exit();
}

$user_role = trim($_SESSION['role']);
if (!in_array($user_role, ['Admin', 'S.Admin'])) {
    header("Location: ../admin/login.php"); exit();
}

$path_prefix = '../'; 
$current_admin = htmlspecialchars($_SESSION['username']);
$current_photo = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';
$portal_title = ($user_role === 'S.Admin') ? "Super Admin" : "Admin Portal";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PXX Mall® | Admin Portal <?php echo $portal_title; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../css/app.css">
</head>
<body>

    <header class="admin-master-header">
        <div class="header-brand" onclick="location.href='../admin/portal.php'">
            <div class="logo-main">PXX Mall<span class="logo-r">&reg;</span></div>
            <div class="logo-portal">| Admin Portal</div>
        </div>
            
        <div class="profile-wrap">
            <img src="<?php echo $path_prefix.'images/'.$current_photo; ?>" onerror="this.src='../images/default.jpg'" class="admin-avatar">
            <div style="text-align:left;">
                <span style="display:block; font-size:11px; opacity:0.8; font-weight:700;"><?php echo strtoupper($user_role); ?></span>
                <b style="font-size:15px;"><?php echo $current_admin; ?></b>
            </div>
            <a href="../admin/logout.php" onclick="return confirm('Logout current session?')"><i class="fa-solid fa-power-off logout-icon"></i></a>
        </div>
    </header>

    <aside class="sidebar-fixed">
        <nav>
            <a href="../admin/portal.php" class="s-item active">
                <div class="s-icon-box">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </div>
            </a>

            <?php if($user_role === 'S.Admin'): ?>
            <div class="s-group">
                <div class="s-drop-h" onclick="this.nextElementSibling.classList.toggle('open')">
                    <div class="s-icon-box">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>My Admin</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-box">
                    <a href="../admin/admin_crud.php" class="sub-nav-link">Admin CRUD</a>
                </div>
            </div>
            <?php endif; ?>

            <div class="s-group">
                <div class="s-drop-h" onclick="this.nextElementSibling.classList.toggle('open')">
                    <div class="s-icon-box">
                        <i class="fa-solid fa-users"></i>
                        <span>My Member</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-box">
                    <a href="../admin/member_crud.php" class="sub-nav-link">Member CRUD</a>
                    <a href="../admin/order_history.php" class="sub-nav-link">Order History</a>
                </div>
            </div>

            <div class="s-group">
                <div class="s-drop-h" onclick="this.nextElementSibling.classList.toggle('open')">
                    <div class="s-icon-box">
                        <i class="fa-solid fa-store"></i>
                        <span>My PXX Mall</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="s-sub-box">
                    <a href="../admin/product_crud.php" class="sub-nav-link">Product CRUD</a>
                    <a href="../admin/category_crud.php" class="sub-nav-link">Category CRUD</a>
                    <a href="../admin/order_crud.php" class="sub-nav-link">Sales Data</a>
                </div>
            </div>
        </nav>

        <div class="sidebar-bottom">
            <a href="https://dev.toyyibpay.com/dashboard" target="_blank" class="tp-link">
                <i class="fa-solid fa-credit-card"></i>
                <span>My ToyyibPay System</span>
            </a>
            <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=html_sql" target="_blank" class="db-link">
                <i class="fa-solid fa-database"></i>
                <span>My Database</span>
            </a>
            <a href="../index.php" target="_blank" class="store-link">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>My Store</span>
            </a>
        </div>
    </aside>

    <main class="main-content-view">
        <div class="empty-canvas">
            <div>
                <i class="fa-solid fa-briefcase"></i>
                <p style="color:#aaa; font-weight:600; margin-top:20px;">Workspace Ready.</p>
            </div>
        </div>
    </main>

    <script src="../js/app.js"></script>
</body>
</html>