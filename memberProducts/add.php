<?php
session_start();
require_once '../includes/db.php';

// 如果是通过 Form 提交而非 AJAX，我们可能不需要强制返回 JSON，
// 但为了保持你原来的逻辑，这里保留 JSON 返回。
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

// 接收前端对应的变量名
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
    // 1. 验证产品是否存在及库存
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
    
    // 2. 检查购物车是否已有【相同规格】的商品
    // 注意：必须同时匹配颜色和尺寸
    $stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND selected_size = ? AND colour = ?");
    $stmt->execute([$user_id, $product_id, $selected_size, $colour]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // 更新已有项的数量
        $new_qty = $existing['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $stmt->execute([$new_qty, $existing['cart_id']]);
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        // 插入新项：cart_id 是 AUTO_INCREMENT，所以不需要手动插入
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, selected_size, colour) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity, $selected_size, $colour]);
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'DB Error: ' . $e->getMessage()]);
}
?>