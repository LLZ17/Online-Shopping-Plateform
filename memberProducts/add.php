<?php
session_start();
require_once '../includes/db.php';

// 设置返回头为 JSON 格式
header('Content-Type: application/json');

// 1. 检查用户登录状态
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

/**
 * 注意：这里移除了 (int) 强制转换
 * 因为你的 product_id 是字符串（如 M1001），(int)"M1001" 会变成 0
 */
$product_id = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$selected_size = isset($_POST['selected_size']) ? trim($_POST['selected_size']) : '';

// 2. 验证 ID 是否为空
if (empty($product_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 3. 检查商品是否存在以及库存
    // 使用 prepare 配合字符串类型的 product_id
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found: ' . $product_id]);
        exit();
    }

    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'error' => 'Insufficient stock. Available: ' . $product['stock']]);
        exit();
    }

    // 4. 检查购物车中是否已存在该商品（同 ID 且 同 尺寸）
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND selected_size = ?");
    $stmt->execute([$user_id, $product_id, $selected_size]);
    $existing = $stmt->fetch();

    if ($existing) {
        // 如果已存在，更新数量
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $existing['cart_id']]);
    } else {
        // 如果不存在，插入新记录
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, selected_size) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity, $selected_size]);
    }

    // 5. 返回成功响应
    echo json_encode([
        'success' => true, 
        'message' => 'Successfully added to cart!',
        'debug_id' => $product_id // 调试用，确认接收到的 ID 正确
    ]);
    
} catch (PDOException $e) {
    // 如果发生数据库错误，输出具体的错误信息协助排查
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>