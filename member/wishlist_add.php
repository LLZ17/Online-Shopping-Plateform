<?php
// 1. 安全检查：如果没登录，直接踢回登录页
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? $_GET['id'] : null;
echo $product_id;

if ($product_id) {
    // 2. 检查数据库：是否已经收藏过
    $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    if (!$stmt->fetch()) {
        // 3. 没收藏过，执行插入操作
        $insert = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $insert->execute([$user_id, $product_id]);
    } else {
        // (可选) 如果已经收藏过，再次点击可以设计为“取消收藏”
        $delete = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $delete->execute([$user_id, $product_id]);
    }
}

// 4. 【核心关键】处理完后，立刻重定向回点击前的那个页面
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: index.php"); // 如果找不到来源页，默认回首页
}
exit();
?>
?>