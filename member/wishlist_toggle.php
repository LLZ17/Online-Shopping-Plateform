<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'not_logged_in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? '';

try {
    // 检测是否已存在
    $check = $pdo->prepare("SELECT favourite_id FROM favourites WHERE user_id = ? AND product_id = ?");
    $check->execute([$user_id, $product_id]);
    $fav = $check->fetch();

    if ($fav) {
        // 已存在则删除（取消收藏）
        $del = $pdo->prepare("DELETE FROM favourites WHERE user_id = ? AND product_id = ?");
        $del->execute([$user_id, $product_id]);
        echo json_encode(['status' => 'removed']);
    } else {
        // 不存在则添加
        $fav_id = substr(uniqid('FAV', true), 0, 20); // 生成符合 varchar(20) 的主键
        $ins = $pdo->prepare("INSERT INTO favourites (favourite_id, user_id, product_id) VALUES (?, ?, ?)");
        $ins->execute([$fav_id, $user_id, $product_id]);
        echo json_encode(['status' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}