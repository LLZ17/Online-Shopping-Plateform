<?php
session_start();
require_once '../includes/db.php';

// 1. 权限检查：未登录则处理
if (!isset($_SESSION['user_id'])) {
    // 如果是 AJAX 请求返回 JSON，如果是表单请求跳去登录页
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'not_logged_in']);
    } else {
        header("Location: login.php");
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. 核心修复：兼容 POST (来自 Form) 和 GET (来自 AJAX/URL)
// 你的 product_detail 传的是 p_id
$product_id = $_POST['p_id'] ?? $_GET['id'] ?? '';

if (empty($product_id)) {
    die("Invalid Product ID.");
}

try {
    // 3. 检测是否已存在
    $check = $pdo->prepare("SELECT favourite_id FROM favourites WHERE user_id = ? AND product_id = ?");
    $check->execute([$user_id, $product_id]);
    $fav = $check->fetch();

    if ($fav) {
        // 已存在则删除（取消收藏）
        $del = $pdo->prepare("DELETE FROM favourites WHERE user_id = ? AND product_id = ?");
        $del->execute([$user_id, $product_id]);
        $status = 'removed';
    } else {
        // 不存在则添加
        $fav_id = substr(uniqid('FAV', true), 0, 20); 
        $ins = $pdo->prepare("INSERT INTO favourites (favourite_id, user_id, product_id) VALUES (?, ?, ?)");
        $ins->execute([$fav_id, $user_id, $product_id]);
        $status = 'added';
    }

    // 4. 关键：根据请求类型决定返回方式
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // 如果是 AJAX 请求，返回 JSON
        header('Content-Type: application/json');
        echo json_encode(['status' => $status]);
    } else {
        // 如果是表单提交，跳回产品页并带上状态提示
        header("Location: " . $_SERVER['HTTP_REFERER'] . "&wishlist=" . $status);
    }
    exit();

} catch (PDOException $e) {
    // 错误处理
    die("Database Error: " . $e->getMessage());
}