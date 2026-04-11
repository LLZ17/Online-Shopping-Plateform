<?php
require_once 'includes/_base.php';
require_once 'includes/db.php';

// 获取要删除的产品 ID (注意：数据库里叫 product_id)
$id = $_GET['id'] ?? '';

if ($id) {
    // 1. 先删除关联的图片记录 (保持数据库整洁，这也是你的 Part)
    $stmt_img = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt_img->execute([$id]);

    // 2. 删除产品本身
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);

    echo "<script>alert('Product deleted successfully!'); window.location='admin_product.php';</script>";
} else {
    header("Location: admin_product.php");
}
?>