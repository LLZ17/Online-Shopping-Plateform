<?php
// 1. 引入数据库连接 (在 includes 文件夹里)
require_once 'includes/db.php'; 

// 2. 引入头部 (注意你的文件名有下划线)
require_once 'includes/_head.php'; 

echo "<div class='container'>";
echo "<h1 style='color:#e60012;'>PXX Mall 商品目录</h1>";

try {
    // 3. 检查 $pdo 是否来自 db.php 并查询
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll();

    if ($products) {
        echo "<div style='display:grid; grid-template-columns: repeat(3, 1fr); gap:20px;'>";
        foreach ($products as $item) {
            echo "<div style='border:1px solid #ddd; padding:15px; background:white;'>";
            echo "<h3>" . htmlspecialchars($item['name']) . "</h3>";
            echo "<p style='color:#e60012; font-weight:bold;'>RM " . $item['price'] . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>数据库里还没有商品。</p>";
    }
} catch (PDOException $e) {
    echo "读取失败: " . $e->getMessage();
}

echo "</div>";

// 4. 引入底部
require_once 'includes/_foot.php'; 
?>