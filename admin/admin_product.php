<?php
require_once 'includes/_base.php';
require_once 'includes/db.php';

$_title = 'Admin | Product Management';
include 'includes/_admin_head.php';

$search = $_GET['search'] ?? '';
$sql = "SELECT p.*, (SELECT image_path FROM product_images WHERE product_id = p.product_id LIMIT 1) as thumb FROM products p";
if ($search != "") {
    $sql .= " WHERE p.name LIKE " . $pdo->quote("%$search%");
}
$sql .= " ORDER BY p.product_id DESC";
$products = $pdo->query($sql)->fetchAll();
?>

<style>
    body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
    .content-wrapper { padding: 30px; max-width: 1200px; margin: 0 auto; }
    .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn-primary { background: #e74c3c; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: 0.3s; }
    .btn-primary:hover { background: #c0392b; box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3); }
    .search-card { background: white; padding: 15px 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; display: flex; gap: 10px; }
    .search-card input { border: none; outline: none; flex: 1; font-size: 15px; }
    .data-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
    .data-table th { color: #888; font-size: 13px; text-transform: uppercase; padding: 0 15px; text-align: left; }
    .data-table tr { background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: 0.2s; }
    .data-table tr:hover { transform: scale(1.01); }
    .data-table td { padding: 15px; vertical-align: middle; border: none; }
    .data-table td:first-child { border-radius: 12px 0 0 12px; }
    .data-table td:last-child { border-radius: 0 12px 12px 0; }
    .product-img { width: 55px; height: 55px; object-fit: cover; border-radius: 10px; border: 1px solid #f0f0f0; }
    .badge-price { background: #fdf2f0; color: #e74c3c; padding: 6px 12px; border-radius: 20px; font-weight: 700; }
    .action-links a { text-decoration: none; font-size: 14px; margin-left: 10px; font-weight: 600; }
</style>

<div class="content-wrapper">
    <div class="header-flex">
        <h1 style="color: #2d3436; font-size: 28px;">Product Inventory</h1>
        <a href="admin_product_add.php" class="btn-primary">+ Add New Item</a>
    </div>

    <form method="get" class="search-card">
        <span style="color: #ccc;">🔍</span>
        <input type="text" name="search" placeholder="Search by product name..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" style="background: none; border: none; color: #e74c3c; font-weight: 600; cursor: pointer;">Search</button>
    </form>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Preview</th>
                <th>Product Name</th>
                <th>Price (RM)</th>
                <th style="text-align: right;">Management</th>
            </tr>
</thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td style="color: #b2bec3;">#<?= $p['product_id'] ?></td>
                <td><img src="images/<?= $p['thumb'] ?: 'nophoto.jpg' ?>" class="product-img"></td>
                <td style="font-weight: 600; color: #2d3436;"><?= htmlspecialchars($p['name']) ?></td>
                <td><span class="badge-price">RM <?= number_format($p['price'], 2) ?></span></td>
                <td class="action-links" style="text-align: right;">
                    <a href="admin_product_edit.php?id=<?= $p['product_id'] ?>" style="color: #0984e3;">Edit</a>
                    <a href="admin_product_delete.php?id=<?= $p['product_id'] ?>" style="color: #d63031;" onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/_foot.php'; ?>