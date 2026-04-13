<?php
/**
 * =================================================================================
 * PXX MALL - ADMINISTRATIVE PORTAL (PORTAL.PHP)
 * =================================================================================
 */
session_start();

// 1. 权限守卫：如果不是管理员，直接踢出去，防止无限跳转
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'S.Admin'])) {
    header("Location: login.php"); // 确保这里指向 admin/login.php
    exit();
}

require_once '../includes/db.php';

// 2. 预设变量，确保 _admin_head.php 不会报错
$_title = 'Admin | Portal';
$path_prefix = '../'; // 🌟 关键：从 admin 文件夹跳出去

// --- 数据统计逻辑 ---
try {
    // 统计前 5 名畅销产品
    $sql_top = "SELECT p.name, p.price, COUNT(o.order_id) as total_sold,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id LIMIT 1) as thumb
                FROM products p
                JOIN orders o ON p.product_id = o.product_id
                GROUP BY p.product_id
                ORDER BY total_sold DESC
                LIMIT 5";
    $top_products = $pdo->query($sql_top)->fetchAll();

    // 统计总览
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders")->fetchColumn();
} catch (PDOException $e) {
    $top_products = [];
    $total_orders = 0;
    $total_revenue = 0;
}

// 🌟 3. 引入 Admin 专用头部
// 请确保 _admin_head.php 里的图片路径使用了 $path_prefix . 'images/'
include '../includes/_admin_head.php'; 
?>

<div class="container" style="padding: 40px 20px; max-width: 1200px; margin: 0 auto;">
    <div style="margin-bottom: 30px;">
        <h1 style="font-weight: 800; color: #333;">Admin Dashboard</h1>
        <p style="color: #888;">Welcome back, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>. Here's your shop performance.</p>
    </div>
    
    <div style="display: flex; gap: 25px; margin-bottom: 40px;">
        <div style="flex: 1; padding: 30px; background: linear-gradient(135deg, #007bff, #0056b3); color: white; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,123,255,0.2);">
            <div style="font-size: 14px; opacity: 0.8; text-transform: uppercase; font-weight: 700;">Total Orders</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 10px;"><?= $total_orders ?></div>
        </div>
        <div style="flex: 1; padding: 30px; background: linear-gradient(135deg, #28a745, #1e7e34); color: white; border-radius: 12px; box-shadow: 0 10px 20px rgba(40,167,69,0.2);">
            <div style="font-size: 14px; opacity: 0.8; text-transform: uppercase; font-weight: 700;">Total Revenue</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 10px;">RM <?= number_format($total_revenue ?? 0, 2) ?></div>
        </div>
    </div>

    <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: 0 5px 25px rgba(0,0,0,0.05);">
        <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span style="color: #ffc107;">🔥</span> Top 5 Best Selling Products
        </h2>
        <table width="100%" style="border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid #f8f9fa; color: #888; font-size: 13px; text-transform: uppercase;">
                    <th style="padding: 15px;">Rank</th>
                    <th style="padding: 15px;">Product</th>
                    <th style="padding: 15px;">Price</th>
                    <th style="padding: 15px;">Sold</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                foreach ($top_products as $tp): 
                ?>
                <tr style="border-bottom: 1px solid #f8f9fa; transition: 0.2s;">
                    <td style="padding: 15px;"><strong>#<?= $rank++ ?></strong></td>
                    <td style="padding: 15px; display: flex; align-items: center; gap: 15px;">
                        <div style="width: 50px; height: 50px; background: #f4f4f4; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            <?php if ($tp['thumb']): ?>
                                <img src="../images/<?= $tp['thumb'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fa-solid fa-image" style="color: #ccc;"></i>
                            <?php endif; ?>
                        </div>
                        <span style="font-weight: 600; color: #333;"><?= $tp['name'] ?></span>
                    </td>
                    <td style="padding: 15px; font-weight: 600;">RM <?= number_format($tp['price'], 2) ?></td>
                    <td style="padding: 15px;"><span style="background: #fff5f5; color: #e60012; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 700;"><?= $tp['total_sold'] ?> Units</span></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($top_products)): ?>
                    <tr><td colspan="4" style="padding: 40px; text-align: center; color: #ccc;">No sales data available yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/_foot.php'; ?>