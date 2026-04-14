<?php
// TEE DE YONG's Part: Product Delete (Fixed & Direct)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

// 1. 获取 ID
$id = $_GET['id'] ?? '';

// 如果 ID 是空的，说明列表页传参失败
if (empty($id)) {
    echo "<script>alert('⚠️ No Product ID received! Check your list page links.'); window.location.href='product_crud.php';</script>";
    exit;
}

try {
    // 💡 使用 product_id 执行删除
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);

    // 2. 成功后直接跳回
    echo "<script>alert('🗑️ Product deleted successfully!'); window.location.href='product_crud.php';</script>";
    exit;

} catch (PDOException $e) {
    die("<div style='padding:50px; font-family:sans-serif;'>
            <h2 style='color:red;'>Delete Error 🚨</h2>
            <p>Reason: " . $e->getMessage() . "<?p>
            <a href='product_crud.php'>Go Back</a>
         </div>");
}
?>
