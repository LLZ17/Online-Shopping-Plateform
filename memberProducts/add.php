<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

$product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$selected_size = isset($_POST['selected_size']) ? trim($_POST['selected_size']) : '';
$colour = isset($_POST['colour']) ? trim($_POST['colour']) : '';

if (empty($product_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
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
        $new_qty = $existing['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $stmt->execute([$new_qty, $existing['cart_id']]);
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        $cart_id = uniqid();
        $stmt = $pdo->prepare("INSERT INTO cart (cart_id, user_id, product_id, quantity, selected_size, colour) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cart_id, $user_id, $product_id, $quantity, $selected_size, $colour]);
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB Error: ' . $e->getMessage()]);
}
?>
