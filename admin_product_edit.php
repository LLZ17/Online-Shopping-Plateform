<?php
require_once 'includes/_base.php';
require_once 'includes/db.php';

$_title = 'Admin | Edit Product';
include 'includes/_head.php';

// 1. 获取要编辑的产品 ID
$id = $_GET['id'] ?? '';

// 2. 处理表单提交 (修正点：统一变量名和参数数量)
if (isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];

    // 2.1 准备 UPDATE 语句 (修正点：明确变量名为 $stmt_update)
    $stmt_update = $pdo->prepare("UPDATE products SET name = ?, price = ? WHERE product_id = ?");
    // 2.2 执行 UPDATE (修正点：传入正确的 3 个参数)
    $stmt_update->execute([$name, $price, $id]);

    // 2.3 处理新增图片（如果选择了图片文件）
    if ($_FILES['photos']['name'][0] != "") {
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            $photo_name = $_FILES['photos']['name'][$key];
            move_uploaded_file($tmp_name, "images/$photo_name");
            
            // 修正点：这里的表名也必须是复数 product_images
            $stmt_img = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
            $stmt_img->execute([$id, $photo_name]);
        }
    }

    echo "<script>alert('Product updated successfully!'); window.location='admin_product.php';</script>";
}

// 3. 读取该产品的旧数据
$stmt_get = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt_get->execute([$id]);
$p = $stmt_get->fetch();

if (!$p) {
    die("Product not found!");
}
?>

<div class="container" style="padding: 20px;">
    <h1>Edit Product</h1>
    <form method="post" enctype="multipart/form-data">
        <p>Product Name: <br>
           <input type="text" name="name" value="<?= $p['name'] ?>" required></p>
        
        <p>Price (RM): <br>
           <input type="number" step="0.01" name="price" value="<?= $p['price'] ?>" required></p>

        <p>Add More Photos: (Optional)<br>
           <input type="file" name="photos[]" multiple></p>
        
        <button type="submit" name="update_product">Save Changes</button>
        <a href="admin_product.php">Cancel</a>
    </form>
</div>

<?php include 'includes/_foot.php'; ?>