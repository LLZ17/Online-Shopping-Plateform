<?php
session_start();
require_once '../includes/db.php';

// 开启错误显示（调试用）
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    $_SESSION['review_error'] = 'Please login first';
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

$product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$user_id = $_SESSION['user_id'];

$errors = [];

if ($product_id === '') {
    $errors[] = "Product ID is missing";
}
if ($rating < 1 || $rating > 5) {
    $errors[] = "Rating must be between 1 and 5";
}
if (empty($comment)) {
    $errors[] = "Comment is required";
}

// 检查是否已购买（订单状态为 Delivered）
if (empty($errors)) {
    $stmt = $pdo->prepare("
        SELECT 1 FROM orders 
        WHERE user_id = ? AND product_id = ? AND order_status = 'Delivered' 
        LIMIT 1
    ");
    $stmt->execute([$user_id, $product_id]);
    if (!$stmt->fetch()) {
        $errors[] = "You can only review products you have purchased and received";
    }
}

// 检查是否已经评论过
if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = "You have already reviewed this product";
    }
}

if (empty($errors)) {
    try {
        // 生成唯一的 review_id
        $review_id = 'REV_' . date('YmdHis') . '_' . rand(1000, 9999);
        
        $stmt = $pdo->prepare("
            INSERT INTO reviews (reviews_id, product_id, user_id, rating, comment, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $result = $stmt->execute([$review_id, $product_id, $user_id, $rating, $comment]);
        
        if ($result) {
            $_SESSION['review_success'] = "Thank you for your review!";
        } else {
            $_SESSION['review_error'] = "Failed to save review";
        }
    } catch (PDOException $e) {
        $_SESSION['review_error'] = "Database error: " . $e->getMessage();
    }
} else {
    $_SESSION['review_error'] = implode(", ", $errors);
}

// 重定向回商品详情页
header("Location: ../memberProducts/detail.php?id=" . urlencode($product_id));
exit();
?>
