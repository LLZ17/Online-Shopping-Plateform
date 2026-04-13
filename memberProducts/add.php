<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$selected_size = isset($_POST['selected_size']) ? trim($_POST['selected_size']) : '';

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid product']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit();
    }

    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'error' => 'Insufficient stock']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND selected_size = ?");
    $stmt->execute([$user_id, $product_id, $selected_size]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $existing['cart_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, selected_size) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity, $selected_size]);
    }

    echo json_encode(['success' => true, 'message' => 'Added to cart']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
