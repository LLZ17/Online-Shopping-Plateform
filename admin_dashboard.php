<?php
require_once 'includes/_base.php';
require_once 'includes/db.php';

$_title = 'Admin | Dashboard';
include 'includes/_head.php';

// 1. 核心 SQL 逻辑：从 orders 表里统计每个产品出现的次数，取出前 5 名
// 这里的逻辑是：统计 orders 表中 product_id 出现的频率
$sql_top = "SELECT p.name, p.price, COUNT(o.order_id) as total_sold,
            (SELECT image_path FROM product_images WHERE product_id = p.product_id LIMIT 1) as thumb
            FROM products p
            JOIN orders o ON p.product_id = o.product_id
            GROUP BY p.product_id
            ORDER BY total_sold DESC
            LIMIT 5";

$top_products = $pdo->query($sql_top)->fetchAll();

// 2. 额外统计：总订单数和总销售额 (顺便展示你的技术)
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders")->fetchColumn();
?>

<div class="container" style="padding: 20px;">
    <h1>Admin Dashboard</h1>
    
    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
        <div style="flex: 1; padding: 20px; background: #007bff; color: white; border-radius: 8px;">
            <h3>Total Orders</h3>
            <p style="font-size: 24px; font-weight: bold;"><?= $total_orders ?></p>
        </div>
        <div style="flex: 1; padding: 20px; background: #28a745; color: white; border-radius: 8px;">
            <h3>Total Revenue</h3>
            <p style="font-size: 24px; font-weight: bold;">RM <?= number_format($total_revenue ?? 0, 2) ?></p>
        </div>
    </div>

    <hr>

    <h2>🔥 Top 5 Best Selling Products</h2>
    <table border="1" width="100%" style="border-collapse: collapse; margin-top: 15px; text-align: center;">
        <tr style="background-color: #f8f9fa;">
            <th>Rank</th>
            <th>Photo</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Total Sold</th>
        </tr>

        <?php 
        $rank = 1;
        foreach ($top_products as $tp): 
        ?>
        <tr>
            <td><strong>#<?= $rank++ ?></strong></td>
            <td>
                <?php if ($tp['thumb']): ?>
                    <img src="images/<?= $tp['thumb'] ?>" style="height: 50px; width: auto; padding: 5px;">
                <?php else: ?>
                    <span style="color: #ccc;">No Photo</span>
                <?php endif; ?>
            </td>
            <td><?= $tp['name'] ?></td>
            <td>RM <?= number_format($tp['price'], 2) ?></td>
            <td style="color: red; font-weight: bold;"><?= $tp['total_sold'] ?> Units</td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($top_products)): ?>
            <tr><td colspan="5" style="padding: 20px;">No sales data available yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php include 'includes/_foot.php'; ?>