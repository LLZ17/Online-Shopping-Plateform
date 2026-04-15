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

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    .page-title-box { display: none !important; }
    html, body { background: #f1f5f9 !important; overflow: hidden !important; }
    
    .scroll-wrapper {
        height: 100vh;
        overflow-y: auto;
        padding-bottom: 150px;
        box-sizing: border-box;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    @keyframes smoothReveal {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .ultimate-detail-card { 
        max-width: 780px; 
        margin: 30px auto; 
        background: #ffffff; 
        border-radius: 32px; 
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(226, 232, 240, 0.5);
        overflow: hidden; 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        animation: smoothReveal 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    /* 顶部标题区 (深蓝系，适合会员管理) */
    .hero-header { 
        padding: 45px 40px; 
        display: flex; 
        justify-content: space-between; 
        align-items: flex-start; 
        gap: 20px;
        background: #0f172a;
        position: relative;
    }
    .hero-header::after {
        content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa); 
    }

    .profile-master { display: flex; align-items: center; gap: 25px; }
    .hero-avatar {
        width: 110px; height: 110px; border-radius: 50%; object-fit: cover;
        border: 4px solid #1e293b; box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        background: #ffffff;
    }
    
    .title-block h1 { 
        margin: 0 0 8px 0; color: #ffffff; font-size: 30px; font-weight: 900; letter-spacing: -0.5px; line-height: 1.2;
    }
    .title-block .meta-id { 
        display: inline-flex; align-items: center; gap: 6px; color: #94a3b8; font-size: 13px; font-weight: 700; letter-spacing: 0.5px; 
    }
    
    /* 状态指示器 */
    .status-indicator { 
        display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 100px; font-size: 13px; font-weight: 800; text-transform: uppercase;
    }
    .status-active { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
    .status-blocked { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; }
    .dot-active { background: #4ade80; box-shadow: 0 0 8px #4ade80; }
    .dot-blocked { background: #f87171; box-shadow: 0 0 8px #f87171; }

    .content-body { padding: 40px; }

    /* 数据卡片网格 */
    .data-grid { 
        display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; 
    }
    .data-card { 
        background: #ffffff; padding: 22px; border-radius: 20px; border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .data-card:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -8px rgba(0,0,0,0.08); border-color: #e2e8f0; }
    
    .data-label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; display: block; }
    .data-value { font-size: 16px; font-weight: 700; color: #0f172a; word-break: break-word; line-height: 1.5; display: flex; align-items: center; gap: 10px;}
    
    /* 积分显示 */
    .points-display { 
        font-size: 34px; font-weight: 900; letter-spacing: -1px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .points-display span { font-size: 18px; font-weight: 800; vertical-align: top; margin-right: 2px; }

    /* 高级药丸标签 (验证状态) */
    .pill-group { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px;}
    .verify-pill { 
        padding: 6px 14px; border-radius: 100px; font-size: 12px; font-weight: 800; user-select: none; display: inline-flex; align-items: center; gap: 6px;
    }
    .pill-verified { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .pill-unverified { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    /* 地址区块 */
    .address-box { background: #f8fafc; padding: 25px; border-radius: 20px; margin-top: 10px; border: 1px dashed #cbd5e1; }
    .address-text { font-size: 15px; font-weight: 600; color: #334155; line-height: 1.8; display: flex; gap: 12px; align-items: flex-start;}

    /* 底部操作区 */
    .footer-actions { display: flex; justify-content: flex-start; margin-top: 35px; padding-top: 30px; border-top: 1px solid #f1f5f9; }
    .btn-return { 
        display: inline-flex; align-items: center; gap: 10px; background: #0f172a; color: #ffffff; 
        padding: 14px 32px; border-radius: 14px; font-weight: 800; font-size: 14px; text-decoration: none; 
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.3);
    }
    .btn-return:hover { background: #3b82f6; transform: translateY(-3px); box-shadow: 0 15px 25px -5px rgba(59, 130, 246, 0.4); }
</style>

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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const myContent = document.getElementById('detail-wrapper');
        if (!myContent) return;
        myContent.style.display = 'block';
        
        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
        if (!viewport) {
            const allElements = document.body.getElementsByTagName('*');
            for (let el of allElements) {
                if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                    viewport = el.parentElement;
                    break;
                }
            }
        }
        
        if (viewport) {
            viewport.innerHTML = ''; 
            viewport.appendChild(myContent);
        } else {
            myContent.style.position = 'fixed';
            myContent.style.top = '60px'; 
            myContent.style.right = '0';
            myContent.style.width = 'calc(100vw - 250px)'; 
            myContent.style.height = 'calc(100vh - 60px)';
            myContent.style.background = '#f1f5f9';
            myContent.style.zIndex = '9999';
            document.body.appendChild(myContent);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
