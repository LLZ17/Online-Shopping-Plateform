<?php 
session_start(); 
require_once __DIR__ . '/../includes/db.php';

// 1. 安全拦截
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_selection.php");
    exit();
}
$current_user = $_SESSION['user_id'];

// ==========================================
// 2. 移除逻辑 (单个删除 + 批量删除)
// ==========================================
// 处理批量删除 (POST)
if (isset($_POST['batch_remove']) && !empty($_POST['wishlist_ids'])) {
    $ids_to_remove = $_POST['wishlist_ids'];
    $placeholders = implode(',', array_fill(0, count($ids_to_remove), '?'));
    
    try {
        // 注意：这里删除的是符合 product_id 且属于当前用户的收藏记录
        $sql = "DELETE FROM `favourites` WHERE `user_id` = ? AND `product_id` IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge([$current_user], $ids_to_remove));
        
        header("Location: wishlist.php?msg=deleted");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// 处理单个删除 (GET) - 保持你原有的逻辑
if (isset($_GET['remove_id'])) {
    try {
        $delete_sql = "DELETE FROM `favourites` WHERE `product_id` = ? AND `user_id` = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$_GET['remove_id'], $current_user]);
        header("Location: wishlist.php");
        exit();
    } catch (PDOException $e) {
        die("Error removing item: " . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/_head.php';
?>

<head>
    <link rel="stylesheet" href="../css/app.css">
    <style>
        .wishlist-container { max-width: 1100px; margin: 30px auto; padding-bottom: 80px; }
        .wishlist-card { background: white; border-radius: 4px; box-shadow: 0 1px 5px rgba(0,0,0,0.1); }
        .wishlist-header-title { padding: 20px; font-size: 18px; border-bottom: 1px solid #eee; color: #333; font-weight: bold; }
        
        /* 表格样式优化 */
        .wishlist-table { width: 100%; border-collapse: collapse; }
        .wishlist-table th { background: #fafafa; padding: 15px; text-align: left; color: #888; font-weight: 400; border-bottom: 1px solid #eee; }
        .wishlist-table td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        /* 复选框样式 */
        input[type="checkbox"] { width: 18px; height: 18px; accent-color: #e60012; cursor: pointer; }

        .product-box { display: flex; align-items: center; gap: 15px; }
        .product-img { width: 70px; height: 90px; object-fit: cover; border: 1px solid #f2f2f2; }
        .variant-info { font-size: 13px; color: #999; margin-top: 4px; display: block; }
        .price-tag { color: #e60012; font-weight: bold; }

        /* 底部操作栏 - 模仿 Cart */
        .wishlist-footer { 
            position: fixed; bottom: 0; width: 1100px; left: 50%; transform: translateX(-50%);
            background: white; display: flex; align-items: center; padding: 15px 30px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1); z-index: 100;
        }
        .footer-left { display: flex; align-items: center; gap: 20px; flex-grow: 1; }
        .batch-del-btn { background: none; border: none; color: #333; cursor: pointer; font-size: 14px; }
        .batch-del-btn:hover { color: #e60012; }
        .batch-del-btn:disabled { color: #ccc; cursor: not-allowed; }
    </style>
</head>

<?php
// 3. 获取当前用户的愿望清单数据
$sql = "SELECT p.* FROM products p JOIN favourites f ON p.product_id = f.product_id WHERE f.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$current_user]);
$result = $stmt->fetchAll();

if (!$result): ?>
    <div style="text-align: center; padding: 100px 20px;">
        <p style="color: #666; font-size: 16px;">Your wishlist is empty.</p>
        <a href="../index.php" style="color: #e60012; text-decoration: none;">Go Shopping →</a>
    </div>
<?php else: ?>

<div class="wishlist-container">
    <form id="wishlistForm" method="POST" action="">
        <div class="wishlist-card">
            <div class="wishlist-header-title">
                <i class="fa-solid fa-heart" style="color: #e60012;"></i> My Wishlist
            </div>

            <table class="wishlist-table">
                <thead>
                    <tr>
                        <th style="width: 50px;"><input type="checkbox" id="selectAll"></th>
                        <th style="width: 40%;">Products</th>
                        <th>Unit Price</th>
                        <th>Stock Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($result as $r): 
                    $imgs = explode(',', $r['product_image']);
                    $display_img = trim($imgs[0]);
                ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="wishlist_ids[]" value="<?php echo $r['product_id']; ?>" class="item-checkbox">
                        </td>
                        <td>
                            <div class="product-box">
                                <img src="../images/<?php echo htmlspecialchars($display_img); ?>" class="product-img" onerror="this.src='../images/default.jpg'">
                                <div class="product-info">
                                    <b><a href="../memberProducts/detail.php?id=<?php echo $r['product_id']; ?>" style="text-decoration:none; color: #333;"><?php echo htmlspecialchars($r['name']); ?></a></b>
                                    <span class="variant-info">Size: <?php echo $r['size'] ?: 'Free'; ?> | Color: <?php echo $r['colour']; ?></span>
                                </div>
                            </div>
                        </td>
                        <td><span class="price-tag">RM <?php echo number_format($r['price'], 2); ?></span></td>
                        <td><span style="color: #28a745;"><?php echo $r['status'] ?: 'In Stock'; ?></span></td>
                        <td>
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <a href="../memberProducts/detail.php?id=<?php echo $r['product_id']; ?>" style="color: #007bff; text-decoration: none; font-size: 14px;">View Details</a>
                                <a href="wishlist.php?remove_id=<?php echo $r['product_id']; ?>" onclick="return confirm('Remove this item?')">
                                    <i class="fa-solid fa-trash-can" style="color: #999;"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="wishlist-footer">
            <div class="footer-left">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="selectAllBottom"> Select All
                </label>
                <button type="submit" name="batch_remove" id="deleteBtn" class="batch-del-btn" disabled onclick="return confirm('Remove selected items from wishlist?')">
                    Delete Selected
                </button>
            </div>
            <div class="footer-right">
                <span style="color: #666;">Total: <strong id="selectedCount" style="color: #e60012;">0</strong> items selected</span>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // 全选逻辑 (顶部 + 底部同步)
    $('#selectAll, #selectAllBottom').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.item-checkbox, #selectAll, #selectAllBottom').prop('checked', isChecked);
        updateUI();
    });

    // 单个勾选逻辑
    $('.item-checkbox').on('change', function() {
        let allCount = $('.item-checkbox').length;
        let checkedCount = $('.item-checkbox:checked').length;
        
        $('#selectAll, #selectAllBottom').prop('checked', allCount === checkedCount);
        updateUI();
    });

    function updateUI() {
        let count = $('.item-checkbox:checked').length;
        $('#selectedCount').text(count);
        // 如果没选中任何东西，禁用删除按钮
        $('#deleteBtn').prop('disabled', count === 0);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/_foot.php'; ?>