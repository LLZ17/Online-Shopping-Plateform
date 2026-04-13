<?php
// TEE DE YONG's Part: Delete Member Logic
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/_base.php'; 
require_once '../includes/db.php';     

$id = $_GET['id'] ?? '';

if ($id) {
    // 🔴 关键点：请确认 WHERE 后面的列名（比如 user_id）和你刚才在 Edit 里用的一模一样！
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    
    if ($stmt->execute([$id])) {
        // 删除成功后，弹窗提示并跳回列表
        echo "<script>alert('🗑️ Member deleted successfully!'); window.location.href='member_crud.php';</script>";
        exit;
    }
}

// 如果没有拿到 ID 或者删除失败，直接静默退回列表
header("Location: member_crud.php");
exit;
?>