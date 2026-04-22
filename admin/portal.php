<?php
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'S.Admin'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

$_title = 'Admin | Portal';
$path_prefix = '../'; 

try {
    $sql_top = "SELECT p.name, p.price, COUNT(o.order_id) as total_sold,
                (SELECT image_path FROM product_images WHERE product_id = p.product_id LIMIT 1) as thumb
                FROM products p
                JOIN orders o ON p.product_id = o.product_id
                GROUP BY p.product_id
                ORDER BY total_sold DESC
                LIMIT 5";
    $top_products = $pdo->query($sql_top)->fetchAll();

    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders")->fetchColumn();
} catch (PDOException $e) {
    $top_products = [];
    $total_orders = 0;
    $total_revenue = 0;
}

include '../includes/_admin_head.php'; 
?>

<link rel="stylesheet" href="../css/app.css">

<div class="admin-container">
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p>Welcome back, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>. Here's your shop performance.</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?= $total_orders ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">RM <?= number_format($total_revenue ?? 0, 2) ?></div>
        </div>
    </div>

    <div class="data-section">
        <h2 class="section-title">
            <span style="color: #ffc107;">🔥</span> Top 5 Best Selling Products
        </h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Sold</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                foreach ($top_products as $tp): 
                ?>
                <tr>
                    <td><strong>#<?= $rank++ ?></strong></td>
                    <td class="product-cell">
                        <div class="img-placeholder">
                            <?php if ($tp['thumb']): ?>
                                <img src="../images/<?= $tp['thumb'] ?>">
                            <?php else: ?>
                                <i class="fa-solid fa-image" style="color: #ccc;"></i>
                            <?php endif; ?>
                        </div>
                        <span style="font-weight: 600; color: #333;"><?= $tp['name'] ?></span>
                    </td>
                    <td style="font-weight: 600;">RM <?= number_format($tp['price'], 2) ?></td>
                    <td><span class="sold-badge"><?= $tp['total_sold'] ?> Units</span></td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($top_products)): ?>
                    <tr><td colspan="4" class="empty-state">No sales data available yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php include '../includes/_foot.php'; ?>