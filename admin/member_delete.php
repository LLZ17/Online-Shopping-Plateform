<?php
// TEE DE YONG's Part: Product Delete (Functional Logic)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. 引用数据库连接
require_once __DIR__ . '/../includes/db.php'; 

// 2. 获取 URL 传过来的 ID (例如 MP013)
$id = $_GET['id'] ?? '';

// 如果 ID 是空的，说明列表页传参失败
if (empty($id)) {
    echo "<script>alert('⚠️ No Product ID received! Check your list page links.'); window.location.href='product_crud.php';</script>";
    exit;
}

try {
    // 3. 💡 使用 product_id 执行删除逻辑
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);

    // 4. 成功后直接弹窗并跳回
    echo "<script>alert('🗑️ Product [#$id] deleted successfully!'); window.location.href='product_crud.php';</script>";
    exit;

} catch (PDOException $e) {
    // 5. 如果发生错误（例如商品被订单引用导致无法删除）
    die("<div style='padding:50px; font-family:sans-serif; text-align:center;'>
            <h2 style='color:#ef4444;'>Delete Error 🚨</h2>
            <p style='color:#64748b;'>Reason: " . htmlspecialchars($e->getMessage()) . "</p>
            <br>
            <a href='product_crud.php' style='color:#3b82f6; font-weight:bold; text-decoration:none;'>← Back to Inventory</a>
         </div>");
}
?>
