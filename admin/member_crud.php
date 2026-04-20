<?php
// TEE DE YONG's Part: Member Management List (Ultimate Auto-Fit Screen Edition)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 💡 1. 处理状态修改的请求 (Active/Blocked)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    try {
        $update_id = $_POST['user_id'];
        $new_status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
        $stmt->execute([$new_status, $update_id]);
        
        header("Location: member_crud.php");
        exit;
    } catch (PDOException $e) {
        $errorMsg = "Update Failed: " . $e->getMessage();
    }
}

// 💡 2. 核心修改：接收搜索词并构建查询逻辑
$searchQuery = $_GET['search'] ?? '';
$whereClause = "role = 'Member'";
$queryParams = [];

if (!empty($searchQuery)) {
    // 允许搜索 ID, 真实姓名 或 用户名
    $whereClause .= " AND (user_id LIKE ? OR real_name LIKE ? OR username LIKE ?)";
    $likeTerm = "%" . $searchQuery . "%";
    $queryParams = [$likeTerm, $likeTerm, $likeTerm];
}

// 💡 3. 抓取会员列表 (加入搜索条件)
try {
    $sql = "SELECT * FROM users WHERE $whereClause ORDER BY user_id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($queryParams);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $members = [];
    $errorMsg = ($errorMsg ?? "") . " Database Error: " . $e->getMessage();
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.10">

<div class="member-container">
    <div class="page-header">
        <h1>My Member CRUD</h1>
        
        <form method="GET" action="member_crud.php" class="search-box">
            <input type="text" name="search" class="search-input" placeholder="Search by Name or ID..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit" class="btn-search">SEARCH</button>
            <?php if (!empty($searchQuery)): ?>
                <a href="member_crud.php" class="btn-clear">✖</a>
            <?php endif; ?>
        </form>
        
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div class="stat-badge">Total Members <span><?= count($members) ?></span></div>
            
            <a href="javascript:void(0);" onclick="triggerSyncRefresh();" class="btn-refresh">
                <span class="icon-spin">🔄</span> Refresh
            </a>

            <a href="member_add.php" class="btn-add">
                <span>➕</span> NEW MEMBER
            </a>
        </div>
    </div>

    <?php if (isset($errorMsg)): ?>
        <div style="background:#fee2e2; color:#b91c1c; padding:15px; border-radius:10px; margin-bottom:15px; border: 1px solid #fecaca; font-size: 14px;">
            <strong>⚠️ 提示:</strong> <?= $errorMsg ?>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No.</th>
                        <th>ID</th>
                        <th>Member Profile</th>
                        <th>Contact & Address</th>
                        <th>Status</th>
                        <th>Points</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($members)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($members as $m): 
                            // 处理头像
                            $photoRaw = !empty($m['profile_photo']) ? $m['profile_photo'] : 'default.jpg';
                            if (strpos($photoRaw, 'http') === 0) {
                                $photoPath = $photoRaw;
                            } elseif (strpos($photoRaw, '/') === 0) {
                                $photoPath = $photoRaw;
                            } else {
                                $photoPath = "../images/" . htmlspecialchars($photoRaw);
                            }

                            // 拼接地址
                            $addrArr = [];
                            if(!empty($m['address']) && $m['address'] !== 'N/A') $addrArr[] = htmlspecialchars($m['address']);
                            $postcodeCity = trim(
                                (!empty($m['Postcode']) && $m['Postcode'] !== 'N/A' ? $m['Postcode'] : '') . " " . 
                                (!empty($m['City']) && $m['City'] !== 'N/A' ? $m['City'] : '')
                            );
                            if(!empty($postcodeCity)) $addrArr[] = htmlspecialchars($postcodeCity);
                            if(!empty($m['State']) && $m['State'] !== 'N/A') $addrArr[] = htmlspecialchars($m['State']);
                            $fullAddress = !empty($addrArr) ? implode(", ", $addrArr) : 'Address not provided';
                        ?>
                        <tr>
                            <td style="text-align: center; font-weight: 700; color: #64748b;"><?= $counter++ ?></td>
                            
                            <td style="font-weight: 800; color: #94a3b8;">#<?= htmlspecialchars($m['user_id']) ?></td>

                            <td>
                                <div class="profile-info">
                                    <img src="<?= $photoPath ?>" class="profile-img" onerror="this.src='../images/default.jpg'">
                                    <div class="user-names">
                                        <span class="u-realname"><?= htmlspecialchars($m['real_name'] !== 'N/A' ? $m['real_name'] : 'No Name') ?></span>
                                        <span class="u-username">@<?= htmlspecialchars($m['username']) ?></span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="contact-item">
                                    <span class="contact-icon">📧</span> 
                                    <span><?= htmlspecialchars($m['email'] !== 'N/A' ? $m['email'] : '-') ?></span>
                                </div>
                                <div class="contact-item">
                                    <span class="contact-icon">📱</span> 
                                    <span><?= htmlspecialchars($m['phone_num'] !== 'N/A' ? $m['phone_num'] : '-') ?></span>
                                </div>
                                <div class="contact-item" style="margin-top: 8px;">
                                    <span class="contact-icon" style="color:#ef4444;">📍</span> 
                                    <span class="address-text"><?= $fullAddress ?></span>
                                </div>
                            </td>

                            <td>
                                <div class="badge-container">
                                    <?php 
                                        $currentStatus = $m['status'] ?? 'Active';
                                        $statusClass = ($currentStatus === 'Blocked') ? 'bg-blocked' : 'bg-active'; 
                                    ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($m['user_id']) ?>">
                                        <select name="status" class="badge badge-select <?= $statusClass ?>" onchange="this.form.submit()">
                                            <option value="Active" class="bg-active" <?= ($currentStatus === 'Active') ? 'selected' : '' ?>>👤 ACTIVE</option>
                                            <option value="Blocked" class="bg-blocked" <?= ($currentStatus === 'Blocked') ? 'selected' : '' ?>>🚫 BLOCKED</option>
                                        </select>
                                    </form>
                                    
                                    <?php if(isset($m['email_status'])): ?>
                                        <span class="badge <?= ($m['email_status'] === 'Verified') ? 'bg-verified' : 'bg-unverified' ?>">
                                            ✉️ <?= $m['email_status'] ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($m['phone_status'])): ?>
                                        <span class="badge <?= ($m['phone_status'] === 'Verified') ? 'bg-verified' : 'bg-unverified' ?>">
                                            📞 <?= $m['phone_status'] ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <div class="points-text">
                                    <span>★</span> <?= number_format($m['points'] ?? 0) ?>
                                </div>
                            </td>

                            <td>
                                <div class="action-group">
                                    <a href="member_detail.php?id=<?= urlencode($m['user_id']) ?>" class="btn-profile-detail">
                                        <span>👁️</span> Profile Detail
                                    </a>
                                    
                                    <a href="member_edit.php?id=<?= urlencode($m['user_id']) ?>" class="btn-edit">
                                        <span>✏️</span> Edit
                                    </a>
                                    
                                    <a href="member_delete.php?id=<?= urlencode($m['user_id']) ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this member?');">
                                        <span>🗑️</span> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:60px; color:#94a3b8;">
                                <div style="font-size:40px; margin-bottom:10px;">👥</div>
                                <div style="font-size:16px; font-weight:700;">No members found matching your search.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../js/app.js?v=1.10"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
