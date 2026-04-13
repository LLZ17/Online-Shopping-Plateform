<?php
session_start();
require_once '../includes/db.php';

// 设置 JSON 响应头
header('Content-Type: application/json');

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

// 获取 POST 数据
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$selected_size = isset($_POST['selected_size']) ? trim($_POST['selected_size']) : '';

// 验证商品 ID
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit();
}

// 验证数量
if ($quantity <= 0) {
    $quantity = 1;
}

$user_id = $_SESSION['user_id'];

try {
    // 检查商品是否存在
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit();
    }
    
    // 检查库存
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'error' => 'Insufficient stock. Only ' . $product['stock'] . ' left.']);
        exit();
    }
    
    // 检查购物车是否已有相同商品（包括尺码）
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND selected_size = ?");
    $stmt->execute([$user_id, $product_id, $selected_size]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // 更新数量
        $new_quantity = $existing['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $stmt->execute([$new_quantity, $existing['cart_id']]);
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        // 新增到购物车
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, selected_size) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity, $selected_size]);
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>