<?php
// TEE DE YONG's Part: Add Product (Fixed Multi-Size Selection)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $keywords = $_POST['keywords'] ?? '';
    $colour = $_POST['colour'] ?? '';
    
    // 💡 处理多选 Size：将数组转为 "S,M,L" 字符串
    $sizes_array = $_POST['sizes'] ?? [];
    $size = !empty($sizes_array) ? implode(',', $sizes_array) : 'Free Size';

    // 自动判断 ID 前缀 (MS, WS, MO, MP, WP)
    $prefix = 'PRD'; 
    if (preg_match('/CAT-([A-Z]{2})-/', $category_id, $m)) { $prefix = $m[1]; }

    try {
        $pdo->beginTransaction();

        // 自动生成 ID
        $stmtId = $pdo->prepare("SELECT product_id FROM products WHERE product_id LIKE ? ORDER BY product_id DESC LIMIT 1");
        $stmtId->execute([$prefix . '%']);
        $lastId = $stmtId->fetchColumn();
        $new_product_id = ($lastId) ? $prefix . str_pad(intval(substr($lastId, 2)) + 1, 3, '0', STR_PAD_LEFT) : $prefix . '001';

        // 执行插入
        $sql = "INSERT INTO products (product_id, category_id, name, keywords, description, price, size, colour, rating, stock, low_stock_threshold, sales_count, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 5.0, 100, 5, 0, 'In stock', NOW())";
        $pdo->prepare($sql)->execute([$new_product_id, $category_id, $name, $keywords, $description, $price, $size, $colour]);

        // 多图上传逻辑 (保持不变)
        if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
            $upload_dir = __DIR__ . '/../images/' . $new_product_id . '/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $paths = [];
            foreach ($_FILES['photos']['tmp_name'] as $k => $tmp) {
                $ext = pathinfo($_FILES['photos']['name'][$k], PATHINFO_EXTENSION);
                $fn = ($k === 0 ? "main" : "detail_$k") . ".$ext";
                if (move_uploaded_file($tmp, $upload_dir . $fn)) $paths[] = "$new_product_id/$fn";
            }
            if ($paths) $pdo->prepare("UPDATE products SET product_image=? WHERE product_id=?")->execute([implode(',', $paths), $new_product_id]);
        }

        $pdo->commit();
        echo "<script>alert('✨ Product Added!'); window.location.href='product_crud.php';</script>";
        exit;
    } catch (Exception $e) { $pdo->rollBack(); die("Error: " . $e->getMessage()); }
}

include __DIR__ . '/../includes/_admin_head.php';
?>

<link rel="stylesheet" href="../css/app.css?v=1.26">

<div id="add-wrapper" style="display:none;">
    <div class="premium-form-card">
        <div class="form-header">
            <h2>New Catalog Entry</h2>
            <p>ADD A NEW PRODUCT TO INVENTORY (MULTI-PHOTO)</p>
        </div>
        <div class="form-body">
            <form method="POST" enctype="multipart/form-data" action="admin_product_add.php">
                
                <div class="grid-row">
                    <div class="form-group">
                        <label>Inventory Name</label>
                        <input type="text" name="name" class="input-style" placeholder="e.g. HYPE Jersey" required>
                    </div>
                    <div class="form-group">
                        <label>Price (RM)</label>
                        <input type="number" step="0.01" name="price" class="input-style" placeholder="0.00" required>
                    </div>
                </div>

                <div class="grid-row" style="grid-template-columns: 1.2fr 1fr 1fr;">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="input-style" required>
                            <option value="CAT-MS-01">Men Shirts (MS)</option>
                            <option value="CAT-WS-01">Women Shirts (WS)</option>
                            <option value="CAT-MO-01">Cosplay / Others (MO)</option>
                            <option value="CAT-MP-01">Men Pants (MP)</option>
                            <option value="CAT-WP-01">Women Pants (WP)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Keywords</label>
                        <input type="text" name="keywords" class="input-style" placeholder="e.g. HYPE">
                    </div>
                    <div class="form-group">
                        <label>Colour</label>
                        <input type="text" name="colour" class="input-style" placeholder="e.g. Black, Navy">
                    </div>
                </div>

                <div class="form-group">
                    <label>Size Options (Select Multiple)</label>
                    <div class="size-selector-grid">
                        <?php 
                        $common_sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'Free Size'];
                        foreach($common_sizes as $sz): ?>
                            <label class="size-chip">
                                <input type="checkbox" name="sizes[]" value="<?= $sz ?>">
                                <span><?= $sz ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Product Narrative</label>
                    <textarea name="description" class="input-style" rows="2" placeholder="Briefly describe..."></textarea>
                </div>

                <div class="form-group">
                    <label>Product Photos</label>
                    <div class="file-upload-box" id="preview-container">
                        <div id="no-photo-placeholder" style="display: flex; align-items: center; gap: 10px;">
                            <div class="no-photo-icon">📷</div>
                            <div style="text-align: left;">
                                <span style="font-weight: 800; color: #1e293b; display: block; font-size: 14px;">Upload product photos</span>
                                <span style="font-size: 11px; color: #64748b;">JPG, PNG, WEBP</span>
                            </div>
                        </div>
                        <input type="file" name="photos[]" id="photo-input" accept="image/*" style="display: none;" multiple>
                    </div>
                </div>

                <div class="btn-row">
                    <a href="product_crud.php" class="btn-back">← CANCEL</a>
                    <button type="submit" class="btn-submit">CONFIRM ENTRY</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/app.js?v=1.26"></script>
<?php include __DIR__ . '/../includes/_foot.php'; ?>
