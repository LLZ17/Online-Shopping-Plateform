<?php
// TEE DE_YONG's Part: Edit Product (Strictly Matched to Database & Premium UI)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$id = $_GET['id'] ?? ''; 
if (!$id) {
    die("<script>alert('⚠️ No product ID provided!'); window.location.href='product_crud.php';</script>");
}

// 查询数据
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?"); 
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("<div style='padding:50px; text-align:center;'><h2>⚠️ Entry not found!</h2><a href='product_crud.php'>Go Back</a></div>");
}

// ✨ 新增逻辑：根据 category_id (例如 CAT-MS-04) 自动提取类型前缀 (MS)
$current_prefix = '';
if (!empty($product['category_id'])) {
    $parts = explode('-', $product['category_id']);
    if (count($parts) >= 2) {
        $current_prefix = $parts[1]; // 拿到 MS, MP, WP 等
    }
}

// 表单提交处理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $keywords = $_POST['keywords'] ?? '';
    $size = $_POST['size'] ?? '';
    $colour = $_POST['colour'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $stock = $_POST['stock'] ?? 0;
    $sales_count = $_POST['sales_count'] ?? 0;
    $status = $_POST['status'] ?? 'In stock';

    $final_image_path = $product['product_image'] ?? ''; 

    try {
        $pdo->beginTransaction();

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../images/' . $id . '/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            $tmp_name = $_FILES['photo']['tmp_name'];
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $new_filename = $id . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $final_image_path = $id . '/' . $new_filename; 
            }
        }

        $updateStmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, product_image = ?, category_id = ?, keywords = ?, size = ?, colour = ?, rating = ?, stock = ?, sales_count = ?, status = ? WHERE product_id = ?");
        $updateStmt->execute([$name, $price, $description, $final_image_path, $category_id, $keywords, $size, $colour, $rating, $stock, $sales_count, $status, $id]);

        $pdo->commit();
        echo "<script>alert('✨ Product [$id] updated successfully!'); window.location.href='product_crud.php';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='padding:20px; color:red;'>Update Error: " . $e->getMessage() . "</div>");
    }
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.5">

<div id="edit-wrapper" class="scroll-wrapper" style="display:none;">
    <div id="edit-product-app" class="premium-form-card">
        <div class="form-header">
            <h2>PXX Mall | Edit Product</h2>
            <p>DATABASE SEQUENCE ID: #<?= htmlspecialchars($id) ?></p>
        </div>
        <div class="form-body">
            <form method="POST" action="" enctype="multipart/form-data" id="productForm">
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Target Gender & Category Type</label>
                    <div class="custom-select-wrapper" id="type-select-wrapper">
                        <div class="custom-select-trigger input-style">
                            <span class="trigger-text">Select Gender & Type...</span>
                            <span style="font-size: 10px;">▼</span>
                        </div>
                        <div class="custom-options">
                            <div class="options-inner">
                                <div class="option-group-title">Men's Section</div>
                                <div class="custom-option <?= $current_prefix === 'MS' ? 'active' : '' ?>" data-value="MS">Men - Shirt (MS)</div>
                                <div class="custom-option <?= $current_prefix === 'MP' ? 'active' : '' ?>" data-value="MP">Men - Pants (MP)</div>
                                <div class="custom-option <?= $current_prefix === 'MO' ? 'active' : '' ?>" data-value="MO">Men - Others (MO)</div>
                                
                                <div class="option-group-title">Women's Section</div>
                                <div class="custom-option <?= $current_prefix === 'WS' ? 'active' : '' ?>" data-value="WS">Women - Shirt (WS)</div>
                                <div class="custom-option <?= $current_prefix === 'WP' ? 'active' : '' ?>" data-value="WP">Women - Pants and Skirt (WP)</div>
                                <div class="custom-option <?= $current_prefix === 'WO' ? 'active' : '' ?>" data-value="WO">Women - Others (WO)</div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="category_id_prefix" id="hidden-product-type" value="<?= htmlspecialchars($current_prefix) ?>">
                </div>

                <div class="grid-row-large">
                    <div class="form-group">
                        <label>Inventory Name</label>
                        <input type="text" name="name" class="input-style" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Price (RM)</label>
                        <input type="number" step="0.01" name="price" class="input-style" value="<?= htmlspecialchars($product['price'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="grid-row">
                    <div class="form-group">
                        <label>Category ID</label>
                        <input type="text" name="category_id" class="input-style" value="<?= htmlspecialchars($product['category_id'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="input-style">
                            <option value="In stock" <?= ($product['status'] ?? '') == 'In stock' ? 'selected' : '' ?>>In stock</option>
                            <option value="Out of stock" <?= ($product['status'] ?? '') == 'Out of stock' ? 'selected' : '' ?>>Out of stock</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Select Size</label>
                    <div class="tag-container" id="size-tags">
                        <?php 
                            $savedSizes = array_map('trim', explode(',', $product['size'] ?? ''));
                            foreach (['XS','S','M','L','XL','2XL','3XL','4XL','5XL','Free Size'] as $s): 
                        ?>
                            <div class="tag-item <?= in_array($s, $savedSizes) ? 'active' : '' ?>" data-value="<?= $s ?>"><?= $s ?></div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="size" id="hidden-size" value="<?= htmlspecialchars($product['size'] ?? '') ?>">
                </div>

                <div class="grid-row">
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock" class="input-style" value="<?= htmlspecialchars($product['stock'] ?? '0') ?>">
                    </div>
                    <div class="form-group">
                        <label>Sales Count</label>
                        <input type="number" name="sales_count" class="input-style" value="<?= htmlspecialchars($product['sales_count'] ?? '0') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Product Narrative (Description)</label>
                    <textarea name="description" class="input-style" rows="2"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Product Photo</label>
                    <div class="file-upload-box" onclick="document.getElementById('photo-input').click();">
                        <img id="image-preview" class="current-photo" src="<?= !empty($product['product_image']) ? '../images/' . htmlspecialchars($product['product_image']) : '' ?>" style="<?= empty($product['product_image']) ? 'display:none;' : '' ?>">
                        <div id="no-photo-placeholder" <?= !empty($product['product_image']) ? 'style="display:none;"' : '' ?>>📷 Upload Photo</div>
                        <input type="file" name="photo" id="photo-input" accept="image/*" style="display: none;">
                    </div>
                </div>

                <div class="btn-row">
                    <a href="product_crud.php" class="btn-back">← CANCEL</a>
                    <button type="submit" class="btn-submit">CONFIRM UPDATE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ✨ 在页面加载时，自动同步下拉框显示的文本
document.addEventListener('DOMContentLoaded', function() {
    const activeOption = document.querySelector('.custom-option.active');
    const triggerText = document.querySelector('.trigger-text');
    
    if (activeOption && triggerText) {
        triggerText.innerText = activeOption.innerText;
    }
});
</script>

<script src="../js/app.js?v=1.5"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
