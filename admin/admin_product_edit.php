<?php
// TEE DE YONG's Part: Edit Product (Compact Vertical & Dual-Column Fix)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$id = $_GET['id'] ?? '';
if (!$id) {
    die("<script>alert('⚠️ No product ID provided!'); window.location.href='product_crud.php';</script>");
}

// 💡 确保这里是 product_id
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?"); 
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("<div style='padding:50px; text-align:center;'><h2>⚠️ Entry not found!</h2><a href='product_crud.php'>Go Back</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    $final_image_path = $product['image_path'] ?? ''; 

    try {
        $pdo->beginTransaction();

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            $tmp_name = $_FILES['photo']['tmp_name'];
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $new_filename = $id . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $final_image_path = 'uploads/products/' . $new_filename;
            }
        }

        // 💡 确保这里是 product_id
        $updateStmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image_path = ? WHERE product_id = ?");
        $updateStmt->execute([$name, $price, $description, $final_image_path, $id]);

        $pdo->commit();
        echo "<script>alert('✨ Product updated successfully!'); window.location.href='product_crud.php';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='padding:20px; color:red;'>Update Error: " . $e->getMessage() . "</div>");
    }
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    .page-title-box { display: none !important; }
    html, body { background: #f8fafc !important; overflow: hidden !important; /* 防止外部双滚动条 */ }
    
    /* 强力接管区域：让这块区域变成独立的可滚动视口 */
    .scroll-wrapper {
        height: 100vh;
        overflow-y: auto;
        padding-bottom: 150px;
        box-sizing: border-box;
    }

    .premium-form-card { max-width: 650px; margin: 20px auto; background: #ffffff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04); overflow: hidden; font-family: 'Inter', sans-serif; }
    .form-header { background: #111; padding: 20px 30px; border-bottom: 4px solid #eab308; }
    .form-header h2 { margin: 0; color: white; font-size: 22px; font-weight: 900; letter-spacing: -0.5px; }
    .form-header p { margin: 5px 0 0; color: #94a3b8; font-size: 11px; font-weight: 600; }
    
    .form-body { padding: 25px 30px; }
    
    /* 💡 网格布局：将名字和价格放同一行，省下大量空间 */
    .grid-row { display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 15px; }
    .form-group { margin-bottom: 15px; }
    .grid-row .form-group { margin-bottom: 0; }

    .form-group label { display: block; font-weight: 800; color: #64748b; margin-bottom: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
    .input-style { width: 100%; padding: 12px 16px; border-radius: 10px; border: 2px solid #f1f5f9; font-size: 14px; font-weight: 600; color: #1e293b; background: #f8fafc; transition: all 0.3s ease; box-sizing: border-box; }
    .input-style:focus { border-color: #eab308; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(234,179,8,0.1); }
    
    /* 压缩版图片上传框 */
    .file-upload-box { border: 2px dashed #cbd5e1; padding: 15px; text-align: center; border-radius: 10px; background: #f8fafc; transition: 0.3s; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 15px; }
    .file-upload-box:hover { border-color: #eab308; background: #fff; }
    .current-photo { width: 80px; height: 80px; border-radius: 8px; border: 2px solid #e2e8f0; object-fit: cover; }
    .no-photo-icon { font-size: 30px; color: #cbd5e1; }

    .btn-row { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
    .btn-submit { background: #111; color: #eab308; padding: 14px 30px; border: 2px solid #eab308; border-radius: 10px; font-size: 13px; font-weight: 900; cursor: pointer; transition: all 0.3s ease; }
    .btn-submit:hover { background: #eab308; color: #111; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(234,179,8,0.2); }
    .btn-back { color: #94a3b8; text-decoration: none; font-size: 12px; font-weight: 800; padding-left: 5px; transition: 0.3s; }
    .btn-back:hover { color: #1e293b; }
</style>

<div id="edit-wrapper" class="scroll-wrapper" style="display:none;">
    <div id="edit-product-app" class="premium-form-card">
        <div class="form-header">
            <h2>Edit Product Entry</h2>
            <p>UPDATING ID #<?= htmlspecialchars($id) ?></p>
        </div>
        <div class="form-body">
            <form method="POST" action="" enctype="multipart/form-data">
                
                <div class="grid-row">
                    <div class="form-group">
                        <label>Inventory Name</label>
                        <input type="text" name="name" class="input-style" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Price (RM)</label>
                        <input type="number" step="0.01" name="price" class="input-style" value="<?= $product['price'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Product Narrative</label>
                    <textarea name="description" class="input-style" rows="2"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Product Photo (Single Image)</label>
                    <div class="file-upload-box" onclick="document.getElementById('photo-input').click();">
                        
                        <img id="image-preview" class="current-photo" 
                             src="<?= !empty($product['image_path']) ? '../' . htmlspecialchars($product['image_path']) : '' ?>" 
                             style="<?= empty($product['image_path']) ? 'display:none;' : '' ?>">
                        
                        <div id="no-photo-placeholder" style="text-align: left; <?= !empty($product['image_path']) ? 'display:none;' : '' ?>">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="no-photo-icon">📷</div>
                                <div>
                                    <span style="font-weight: 700; color: #1e293b; display: block; font-size: 14px;">Upload new photo</span>
                                    <span style="font-size: 11px; color: #64748b;">JPG, PNG, WEBP</span>
                                </div>
                            </div>
                        </div>

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
    document.getElementById('photo-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const previewImg = document.getElementById('image-preview');
                const placeholder = document.getElementById('no-photo-placeholder');
                previewImg.src = event.target.result;
                previewImg.style.display = 'block'; 
                if(placeholder) placeholder.style.display = 'none'; 
            }
            reader.readAsDataURL(file);
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        const myContent = document.getElementById('edit-wrapper');
        if (!myContent) return;
        myContent.style.display = 'block';
        
        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
        if (!viewport) {
            const allElements = document.body.getElementsByTagName('*');
            for (let el of allElements) {
                if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                    viewport = el.parentElement;
                    break;
                }
            }
        }
        
        if (viewport) {
            viewport.innerHTML = ''; 
            viewport.appendChild(myContent);
        } else {
            myContent.style.position = 'fixed';
            myContent.style.top = '60px'; 
            myContent.style.right = '0';
            myContent.style.width = 'calc(100vw - 250px)'; 
            myContent.style.height = 'calc(100vh - 60px)';
            myContent.style.background = '#f8fafc';
            myContent.style.zIndex = '9999';
            document.body.appendChild(myContent);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>

