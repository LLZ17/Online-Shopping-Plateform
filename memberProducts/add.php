<?php
session_start();
// 确保路径正确，根据你的目录结构指向 db.php
require_once '../includes/db.php';

// 1. 设置 JSON 响应头，防止乱码和解析错误
header('Content-Type: application/json');

// 2. 检查用户登录状态
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

// 3. 接收并清理输入数据
$user_id      = $_SESSION['user_id'];
$product_id   = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
$quantity     = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$selected_size = isset($_POST['selected_size']) ? trim($_POST['selected_size']) : '';
// 如果你的表里有 colour 字段，可以根据需要接收，这里演示设为 NULL
$colour       = isset($_POST['colour']) ? trim($_POST['colour']) : null;

// 4. 基础验证
if (empty($product_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit();
}
if ($quantity <= 0) $quantity = 1;

try {
    // 5. 检查商品是否存在及库存
    $stmt = $pdo->prepare("SELECT name, stock FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit();
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'error' => 'Insufficient stock. Only ' . $product['stock'] . ' left.']);
        exit();
    }
    
    // 6. 核心逻辑：检查购物车是否已有相同项（产品ID + 尺码）
    // 注意：这里一定要匹配 user_id, product_id 和 selected_size
    $stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND selected_size = ?");
    $stmt->execute([$user_id, $product_id, $selected_size]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // 情况 A: 已存在 -> 执行 UPDATE
        $new_quantity = $existing['quantity'] + $quantity;
        $update_stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $update_stmt->execute([$new_quantity, $existing['cart_id']]);
        
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        // 情况 B: 不存在 -> 执行 INSERT
        // 注意：这里绝对不写 cart_id 字段，让数据库 AUTO_INCREMENT 自动处理
        $insert_sql = "INSERT INTO cart (user_id, product_id, quantity, selected_size, colour, added_at) 
                       VALUES (?, ?, ?, ?, ?, NOW())";
        
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([
            $user_id, 
            $product_id, 
            $quantity, 
            $selected_size, 
            $colour
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    }
    
} catch (PDOException $e) {
    // 如果还是报 1062 错误，这里会捕获并返回具体的数据库信息
    $error_msg = $e->getMessage();
    
    // 如果是因为自增 ID 冲突，尝试给用户一个更友好的提示
    if (strpos($error_msg, '1062') !== false) {
        echo json_encode([
            'success' => false, 
            'error' => 'Database Sync Error (1062). Please try again or contact admin.'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'DB Error: ' . $error_msg]);
    }
}
?>