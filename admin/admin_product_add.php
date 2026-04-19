<?php
// TEE DE YONG's Part: Add Product (Updated for Multiple Photos & Auto ID)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    $uploaded_images = []; 

    try {
        $pdo->beginTransaction();

        $idQuery = $pdo->query("SELECT product_id FROM products ORDER BY created_at DESC, product_id DESC LIMIT 1");
        $lastId = $idQuery->fetchColumn();
        
        if ($lastId && preg_match('/^([a-zA-Z]+)(\d+)$/', $lastId, $matches)) {
            $prefix = $matches[1]; 
            $number = intval($matches[2]) + 1; 
            $new_product_id = $prefix . str_pad($number, strlen($matches[2]), '0', STR_PAD_LEFT);
        } else {
            $new_product_id = 'PRD001'; 
        }

        $stmt = $pdo->prepare("INSERT INTO products (product_id, name, price, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$new_product_id, $name, $price, $description]);
        
        $new_id = $new_product_id; 

        if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                    $new_filename = $new_id . '_' . time() . '_' . $key . '.' . $file_extension;
                    $target_file = $upload_dir . $new_filename;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $uploaded_images[] = 'uploads/products/' . $new_filename;
                    }
                }
            }

            if (!empty($uploaded_images)) {
                $final_images_path = implode(',', $uploaded_images);
                $updateImg = $pdo->prepare("UPDATE products SET product_image = ? WHERE product_id = ?");
                $updateImg->execute([$final_images_path, $new_id]);
            }
        }

        $pdo->commit();
        echo "<script>alert('✨ Product [{$new_product_id}] successfully added!'); window.location.href='product_crud.php';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='padding:20px; color:red;'>Database Error: " . $e->getMessage() . "</div>");
    }
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.6">

<div id="add-wrapper" class="scroll-wrapper" style="display:none;">
    <div id="add-product-app" class="premium-form-card">
        <div class="form-header">
            <h2>New Catalog Entry</h2>
            <p>ADD A NEW PRODUCT TO INVENTORY (MULTI-PHOTO)</p>
        </div>
        <div class="form-body">
            <form method="POST" action="" enctype="multipart/form-data">
                
                <div class="grid-row">
                    <div class="form-group">
                        <label>Inventory Name</label>
                        <input type="text" name="name" class="input-style" placeholder="e.g. Signature Shirt" required>
                    </div>
                    <div class="form-group">
                        <label>Price (RM)</label>
                        <input type="number" step="0.01" name="price" class="input-style" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Product Narrative</label>
                    <textarea name="description" class="input-style" rows="2" placeholder="Briefly describe the product..."></textarea>
                </div>

                <div class="form-group">
                    <label>Product Photos (Multiple Images)</label>
                    <div class="file-upload-box" onclick="document.getElementById('photo-input').click();" id="preview-container">
                        
                        <div id="no-photo-placeholder" style="display: flex; align-items: center; gap: 10px;">
                            <div class="no-photo-icon">📷</div>
                            <div style="text-align: left;">
                                <span style="font-weight: 700; color: #1e293b; display: block; font-size: 14px;">Upload product photos</span>
                                <span style="font-size: 11px; color: #64748b;">JPG, PNG, WEBP (Multiple allowed)</span>
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

<script src="../js/app.js?v=1.6"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
