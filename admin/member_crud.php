<?php
// TEE DE YONG's Part: Member Management List (Ultimate Auto-Fit Screen Edition)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 💡 处理状态修改的请求 (Active/Blocked)
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

// 💡 抓取会员列表
try {
    $sql = "SELECT * FROM users WHERE role = 'Member' ORDER BY user_id ASC";
    $members = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $members = [];
    $errorMsg = ($errorMsg ?? "") . " Database Error: " . $e->getMessage();
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    .page-title-box { display: none !important; }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* 容器自适应 */
    .member-container { 
        width: 100%; box-sizing: border-box; padding: 20px; 
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif; 
        animation: fadeInUp 0.5s ease-out forwards;
    }

    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .page-header h1 { font-size: 32px; font-weight: 900; color: #0f172a; letter-spacing: -1px; margin: 0; }
    
    .stat-badge { 
        background: #ffffff; padding: 10px 20px; border-radius: 12px; 
        box-shadow: 0 4px 10px -2px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; 
        font-weight: 700; color: #64748b; font-size: 14px; white-space: nowrap;
    }
    .stat-badge span { color: #3b82f6; font-size: 18px; margin-left: 8px; font-weight: 900; }

    .btn-refresh {
        background: #ffffff; color: #334155;
        padding: 10px 16px; border-radius: 12px; text-decoration: none; font-weight: 800;
        font-size: 13px; transition: all 0.3s ease; border: 1px solid #cbd5e1;
        display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        white-space: nowrap; cursor: pointer;
    }
    .btn-refresh:hover {
        background: #f1f5f9; color: #0f172a; border-color: #94a3b8;
        transform: translateY(-2px); box-shadow: 0 8px 15px -3px rgba(0,0,0,0.08);
    }
    .icon-spin { transition: transform 0.5s ease; display: inline-block; }
    .btn-refresh:hover .icon-spin { transform: rotate(180deg); }

    .btn-add {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #ffffff;
        padding: 10px 20px; border-radius: 12px; text-decoration: none; font-weight: 800;
        font-size: 13px; transition: all 0.3s ease; border: 1px solid #2563eb;
        display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 15px -4px rgba(37, 99, 235, 0.4);
        white-space: nowrap;
    }
    .btn-add:hover { transform: translateY(-2px); box-shadow: 0 12px 20px -4px rgba(37, 99, 235, 0.5); color: #ffffff; }

    .table-card {
        background: #ffffff; border-radius: 20px;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
        border: 1px solid rgba(226, 232, 240, 0.8); 
        width: 100%; 
        overflow-x: auto; 
    }
    
    table { width: 100%; border-collapse: collapse; text-align: left; min-width: 900px; }
    th { background: #f8fafc; padding: 15px 12px; color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9; white-space: nowrap; }
    td { padding: 15px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #1e293b; font-size: 14px; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #fcfdfe; }

    .profile-info { display: flex; align-items: center; gap: 12px; }
    .profile-img { 
        width: 44px; height: 44px; border-radius: 12px; object-fit: cover; 
        border: 2px solid #f1f5f9; background: #f8fafc; flex-shrink: 0;
    }
    .user-names { display: flex; flex-direction: column; white-space: nowrap; }
    .u-realname { font-size: 15px; font-weight: 800; color: #0f172a; }
    .u-username { font-size: 12px; color: #94a3b8; font-weight: 600; }

    /* 联系方式 (如第2张截图所示) */
    .contact-item { font-size: 13px; color: #475569; display: flex; align-items: flex-start; gap: 6px; margin-bottom: 4px; }
    .contact-icon { font-size: 14px; opacity: 0.7; margin-top: 1px; }
    .address-text { line-height: 1.4; max-width: 220px; white-space: normal; word-wrap: break-word; font-size: 12px; }

    .badge-container { display: flex; flex-direction: column; gap: 6px; }
    .badge { 
        display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 8px; 
        font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; width: fit-content; white-space: nowrap;
    }
    .badge-select {
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        border: 1px solid transparent; outline: none; cursor: pointer;
        font-family: inherit; transition: all 0.2s ease; padding-right: 20px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 4px center; background-size: 10px;
    }
    .badge-select:hover { filter: brightness(0.95); transform: scale(1.02); }
    
    .bg-active { background-color: #dcfce7; color: #15803d; }
    .bg-blocked { background-color: #fee2e2; color: #b91c1c; }
    .bg-verified { background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .bg-unverified { background-color: #f1f5f9; color: #64748b; }

    .points-text { font-size: 16px; font-weight: 900; color: #f59e0b; display: flex; align-items: center; gap: 4px; white-space: nowrap; }

    .action-group { display: flex; flex-direction: column; gap: 8px; } 
    
    /* 💡 新增的 Profile Detail (详情) 按钮样式 - 金黄色 */
    .btn-profile-detail {
        background: #fefce8; color: #ca8a04; padding: 8px 12px; border-radius: 10px;
        text-decoration: none; font-weight: 800; font-size: 11px; transition: all 0.3s ease;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 1px solid #fef08a; white-space: nowrap; width: 110px; box-sizing: border-box;
    }
    .btn-profile-detail:hover { background: #ca8a04; color: #ffffff; border-color: #ca8a04; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(202, 138, 4, 0.3); }

    /* 💡 改名后的 Edit (编辑) 按钮样式 - 蓝色 */
    .btn-edit {
        background: #eff6ff; color: #3b82f6; padding: 8px 12px; border-radius: 10px;
        text-decoration: none; font-weight: 800; font-size: 11px; transition: all 0.3s ease;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 1px solid #bfdbfe; white-space: nowrap; width: 110px; box-sizing: border-box;
    }
    .btn-edit:hover { background: #3b82f6; color: #ffffff; border-color: #3b82f6; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); }

    .btn-delete {
        background: #fef2f2; color: #ef4444; padding: 8px 12px; border-radius: 10px;
        text-decoration: none; font-weight: 800; font-size: 11px; transition: all 0.3s ease;
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: 1px solid #fee2e2; white-space: nowrap; width: 110px; box-sizing: border-box;
    }
    .btn-delete:hover { background: #ef4444; color: #ffffff; border-color: #ef4444; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); }
</style>

<div class="member-container">
    <div class="page-header">
        <h1>Member CRUD</h1>
        
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
                                <div style="font-size:16px; font-weight:700;">No members found.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function triggerSyncRefresh() {
        alert("✅ Admin records synchronized and refreshed!");
        window.location.reload();
    }

    document.addEventListener("DOMContentLoaded", function() {
        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
        let container = document.querySelector('.member-container');
        if (viewport && container && viewport !== container.parentNode) {
            viewport.innerHTML = '';
            viewport.appendChild(container);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>
