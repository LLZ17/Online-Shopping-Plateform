<?php
// TEE DE YONG's Part: Delete Product Logic
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. 修复了这里的路径，加上了 ../
require_once '../includes/_base.php'; 
require_once '../includes/db.php';     

$id = $_GET['id'] ?? '';

if ($id) {
    // 2. 执行删除操作 (在 products 表里删除对应的 product_id)
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    
    if ($stmt->execute([$id])) {
        // 删除成功后，弹窗提示并跳回产品列表
        echo "<script>alert('🗑️ Product deleted successfully!'); window.location.href='product_crud.php';</script>";
        exit;
    }
}

// 如果没有拿到 ID 或者删除失败，直接静默退回列表
header("Location: product_crud.php");
exit;
?>