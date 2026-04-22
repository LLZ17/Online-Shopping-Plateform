<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// 强制返回 JSON，防止页面跳转到白屏
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];
// 兼容 detail.php (p_id) 和 index.php (product_id)
$product_id = $_POST['p_id'] ?? $_POST['product_id'] ?? '';
$quantity = (int)($_POST['qty'] ?? $_POST['quantity'] ?? 1);
$selected_size = $_POST['sel_size'] ?? '';
$colour = $_POST['sel_color'] ?? '';

if (empty($product_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit();
}

try {
    // 1. 验证库存
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
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

    // 2. 检查购物车是否有相同规格商品
    $check_sql = "SELECT cart_id, quantity FROM cart 
                  WHERE user_id = ? AND product_id = ? AND selected_size = ? AND colour = ?";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute([$user_id, $product_id, $selected_size, $colour]);
    $existing = $stmt->fetch();

    if ($existing) {
        // 更新数量
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $existing['cart_id']]);
    } else {
        // 插入新记录
        $insert_sql = "INSERT INTO cart (user_id, product_id, quantity, selected_size, colour) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([$user_id, $product_id, $quantity, $selected_size, $colour]);
    }

    echo json_encode(['success' => true, 'message' => 'Success! Item updated in cart.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database Error: ' . $e->getMessage()]);
}