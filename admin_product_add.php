<?php
require_once 'includes/_base.php';
require_once 'includes/db.php';

$_title = 'Admin | Add Product';
include 'includes/_head.php';

// 检查是否提交了表单
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. 获取输入并进行服务器端验证 [cite: 52]
    $name  = trim($_POST['name']);
    $price = $_POST['price'];

    if (empty($name)) {
        echo "<p style='color:red;'>Error: Name is required!</p>";
    } elseif ($price <= 0) {
        echo "<p style='color:red;'>Error: Price must be more than 0!</p>";
    } else {
        // 2. 使用 PDO 插入产品 [cite: 18]
        $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
        $stmt->execute([$name, $price]);
        $product_id = $pdo->lastInsertId();

        // 3. 处理多图上传 (你的加分项) [cite: 94, 95]
        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp) {
                $filename = time() . "_" . $_FILES['photos']['name'][$key];
                if (move_uploaded_file($tmp, "images/" . $filename)) {
                    // 存入关联表
                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$product_id, $filename]);
                }
            }
        }
        echo "<script>alert('Added successfully!'); window.location='admin_product.php';</script>";
    }
}
?>

<div class="container">
    <h1>Add New Product (Admin)</h1>
    <form method="post" enctype="multipart/form-data">
        <p>Name: <input type="text" name="name"></p>
        <p>Price: <input type="number" step="0.01" name="price"></p>
        <p>Photos: <input type="file" name="photos[]" multiple accept="image/*"></p>
        <button type="submit">Submit</button>
    </form>
</div>

<?php include 'includes/_foot.php'; ?>