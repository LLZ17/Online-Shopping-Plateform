<?php
// TEE DE YONG's Part: Product Delete (Functional Logic)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. 引用数据库连接
require_once __DIR__ . '/../includes/db.php'; 

// 2. 获取 URL 参数中的 ID (如 MP013)
$id = $_GET['id'] ?? '';

if (!$id) {
    echo "<script>alert('⚠️ No Product ID provided!'); window.location.href='product_crud.php';</script>";
    exit;
}

try {
    // 3. 执行删除指令
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);

    // 4. 删除成功后的反馈
    echo "<script>
        alert('🗑️ Product [#$id] has been permanently removed.');
        window.location.href='product_crud.php';
    </script>";
    exit;

} catch (PDOException $e) {
    // 5. 错误处理 (例如：由于外键约束导致无法删除)
    die("<div style='padding:40px; text-align:center; font-family:sans-serif; background:#fff5f5; color:#e11d48; min-height:100vh;'>
            <h2 style='font-size:32px;'>Delete Restricted</h2>
            <p style='color:#4b5563; margin-bottom:20px;'>This product cannot be deleted because it is linked to existing orders or other records.</p>
            <code style='background:#fee2e2; padding:10px; border-radius:8px; display:block; max-width:600px; margin:0 auto; overflow-x:auto;'>" . $e->getMessage() . "</code>
            <br><br>
            <a href='product_crud.php' style='color:#2563eb; font-weight:bold; text-decoration:none;'>← Return to Inventory</a>
         </div>");
}
?>
