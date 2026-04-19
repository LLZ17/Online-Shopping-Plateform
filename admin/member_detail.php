<?php
// TEE DE YONG's Part: Member Profile Detail (Ultimate Premium Read-Only UI)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 💡 1. 抓取会员 ID
$id = $_GET['id'] ?? '';
if (!$id) {
    die("<script>alert('⚠️ No member ID provided!'); window.location.href='member_crud.php';</script>");
}

// 💡 2. 从数据库获取会员信息
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'Member'"); 
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$member) {
    die("<div style='padding:50px; text-align:center; font-family:sans-serif;'><h2>⚠️ Member not found!</h2><a href='member_crud.php' style='color:#3b82f6; text-decoration:none; font-weight:bold;'>← Go Back to List</a></div>");
}

// 💡 3. 处理头像路径
$photoRaw = !empty($member['profile_photo']) ? $member['profile_photo'] : 'default.jpg';
if (strpos($photoRaw, 'http') === 0 || strpos($photoRaw, '/') === 0) {
    $photoPath = htmlspecialchars($photoRaw);
} else {
    $photoPath = "../images/" . htmlspecialchars($photoRaw);
}

// 💡 4. 处理地址拼接
$addrArr = [];
if(!empty($member['address']) && $member['address'] !== 'N/A') $addrArr[] = htmlspecialchars($member['address']);
$postcodeCity = trim(
    (!empty($member['Postcode']) && $member['Postcode'] !== 'N/A' ? $member['Postcode'] : '') . " " . 
    (!empty($member['City']) && $member['City'] !== 'N/A' ? $member['City'] : '')
);
if(!empty($postcodeCity)) $addrArr[] = htmlspecialchars($postcodeCity);
if(!empty($member['State']) && $member['State'] !== 'N/A') $addrArr[] = htmlspecialchars($member['State']);
$fullAddress = !empty($addrArr) ? implode(", ", $addrArr) : 'Address not provided';

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.11">

<div id="detail-wrapper" class="scroll-wrapper" style="display:none;">
    <div id="detail-product-app" class="ultimate-detail-card">
        
        <div class="hero-header">
            <div class="profile-master">
                <img src="<?= $photoPath ?>" class="hero-avatar" onerror="this.src='../images/default.jpg'">
                <div class="title-block">
                    <div class="meta-id">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        MEMBER ID: #<?= htmlspecialchars($member['user_id']) ?>
                    </div>
                    <h1 style="margin-top: 12px;"><?= htmlspecialchars($member['real_name'] !== 'N/A' ? $member['real_name'] : 'Unknown Name') ?></h1>
                    <div style="color: #cbd5e1; font-weight: 600; font-size: 15px;">@<?= htmlspecialchars($member['username']) ?></div>
                </div>
            </div>
            
            <?php 
                $status = $member['status'] ?? 'Active';
                $statusClass = $status === 'Blocked' ? 'status-blocked' : 'status-active';
                $dotClass = $status === 'Blocked' ? 'dot-blocked' : 'dot-active';
            ?>
            <div class="status-indicator <?= $statusClass ?>">
                <div class="status-dot <?= $dotClass ?>"></div>
                <?= htmlspecialchars($status) ?>
            </div>
        </div>

        <div class="content-body">
            
            <div class="data-grid">
                <div class="data-card">
                    <span class="data-label">Email Address</span>
                    <span class="data-value">
                        <span style="opacity:0.5; font-size: 20px;">📧</span> 
                        <?= htmlspecialchars($member['email'] !== 'N/A' ? $member['email'] : 'Not Provided') ?>
                    </span>
                </div>
                <div class="data-card">
                    <span class="data-label">Phone Number</span>
                    <span class="data-value">
                        <span style="opacity:0.5; font-size: 20px;">📱</span> 
                        <?= htmlspecialchars($member['phone_num'] !== 'N/A' ? $member['phone_num'] : 'Not Provided') ?>
                    </span>
                </div>
            </div>

            <div class="data-grid">
                <div class="data-card">
                    <span class="data-label">Verification Status</span>
                    <div class="pill-group">
                        <?php 
                            $emailStatus = $member['email_status'] ?? 'Unverified';
                            $emailClass = $emailStatus === 'Verified' ? 'pill-verified' : 'pill-unverified';
                        ?>
                        <span class="verify-pill <?= $emailClass ?>">
                            ✉️ Email <?= $emailStatus ?>
                        </span>

                        <?php 
                            $phoneStatus = $member['phone_status'] ?? 'Unverified';
                            $phoneClass = $phoneStatus === 'Verified' ? 'pill-verified' : 'pill-unverified';
                        ?>
                        <span class="verify-pill <?= $phoneClass ?>">
                            📞 Phone <?= $phoneStatus ?>
                        </span>
                    </div>
                </div>
                <div class="data-card" style="border-color: #fde68a; background: #fffbeb;">
                    <span class="data-label" style="color: #d97706;">Reward Points Balance</span>
                    <div class="points-display"><span>★</span><?= number_format($member['points'] ?? 0) ?></div>
                </div>
            </div>

            <div class="data-grid">
                <div class="data-card">
                    <span class="data-label">Account Creation Date</span>
                    <span class="data-value">
                        <span style="opacity:0.5; font-size: 20px;">📅</span> 
                        <?= !empty($member['created_at']) ? date('F j, Y, g:i A', strtotime($member['created_at'])) : 'Unknown' ?>
                    </span>
                </div>
            </div>

            <div class="address-box">
                <span class="data-label" style="margin-bottom: 12px;">Registered Delivery Address</span>
                <div class="address-text">
                    <span style="color:#ef4444; font-size:22px;">📍</span> 
                    <span><?= $fullAddress ?></span>
                </div>
            </div>

            <div class="footer-actions">
                <a href="member_crud.php" class="btn-return">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    BACK TO MEMBERS LIST
                </a>
            </div>
            
        </div>
    </div>
</div>

<script src="../js/app.js?v=1.11"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
