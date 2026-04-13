<?php
// TEE DE YONG's Part: Product Delete (Fixed db connection and product_id)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 💡 修复点 1：改成正确的 db.php
require_once __DIR__ . '/../includes/db.php'; 

// 获取要删除的商品 ID
$id = $_GET['id'] ?? '';

if (!$id) {
    die("<script>alert('⚠️ No Product ID provided!'); window.location.href='product_crud.php';</script>");
}

try {
    // 💡 修复点 2：使用我们在 Edit 页面确认过的 product_id 进行精确删除
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$id]);

    // 删除成功后，弹窗提示并立刻自动跳回列表页
    echo "<script>alert('🗑️ Product successfully deleted!'); window.location.href='product_crud.php';</script>";
    exit;

} catch (PDOException $e) {
    // 如果发生错误（比如有顾客的订单里还包含这个商品，数据库防误删机制拦截），会提示错误
    die("<div style='padding:20px; color:red; font-family:sans-serif;'>
            <h3>Delete Error</h3>
            <p>" . $e->getMessage() . "</p>
            <a href='product_crud.php'>Go Back</a>
         </div>");
}    
?>