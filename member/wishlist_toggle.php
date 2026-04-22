<?php
session_start();
// 使用 __DIR__ 确保路径绝对安全
require_once __DIR__ . '/../includes/db.php'; 

header('Content-Type: application/json'); // 强制声明返回 JSON

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'not_logged_in']);
    exit();
}

$user_id = $_SESSION['user_id'];
// 接收 p_id (来自 AJAX) 或 id (来自普通链接)
$product_id = $_POST['p_id'] ?? $_GET['id'] ?? '';

if (empty($product_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Product ID']);
    exit();
}

try {
    // 检查是否存在
    $check = $pdo->prepare("SELECT favourite_id FROM favourites WHERE user_id = ? AND product_id = ?");
    $check->execute([$user_id, $product_id]);
    $fav = $check->fetch();

    if ($fav) {
        // 取消收藏
        $del = $pdo->prepare("DELETE FROM favourites WHERE user_id = ? AND product_id = ?");
        $del->execute([$user_id, $product_id]);
        $status = 'removed';
    } else {
        // 添加收藏
        // 根据你的 User Summary，主键可能是 UUID 或特定的格式
        $fav_id = substr(uniqid('FAV', true), 0, 20); 
        $ins = $pdo->prepare("INSERT INTO favourites (favourite_id, user_id, product_id) VALUES (?, ?, ?)");
        $ins->execute([$fav_id, $user_id, $product_id]);
        $status = 'added';
    }

    echo json_encode(['status' => $status]);
    exit();

} catch (PDOException $e) {
    // 捕获数据库错误并以 JSON 返回，方便前端调试
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}