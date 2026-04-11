<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../products/listing.php');
    exit();
}

$product_id = (int)$_POST['product_id'];
$rating = (int)$_POST['rating'];
$comment = trim($_POST['comment']);
$user_id = $_SESSION['user_id'];

$errors = [];

if ($rating < 1 || $rating > 5) {
    $errors[] = "Rating must be between 1 and 5";
}
if (empty($comment)) {
    $errors[] = "Comment is required";
}

// Check if user purchased this product
$stmt = $pdo->prepare("
    SELECT 1 FROM orders o 
    JOIN order_items oi ON o.order_id = oi.order_id 
    WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'delivered'
    LIMIT 1
");
$stmt->execute([$user_id, $product_id]);
$hasPurchased = $stmt->fetch();

if (!$hasPurchased) {
    $errors[] = "You can only review products you have purchased";
}

// Check if already reviewed
$stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);
if ($stmt->fetch()) {
    $errors[] = "You have already reviewed this product";
}

if (empty($errors)) {
    $stmt = $pdo->prepare("
        INSERT INTO reviews (product_id, user_id, rating, comment) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$product_id, $user_id, $rating, $comment]);
    
    $_SESSION['review_success'] = "Thank you for your review!";
} else {
    $_SESSION['review_errors'] = $errors;
}

header("Location: ../products/detail.php?id=$product_id");
exit();
?>