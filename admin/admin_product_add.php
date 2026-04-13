<?php
// TEE DE YONG's Part: Add Product (Compact Vertical & Single Photo Upload)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ⚠️ 注意这里：请确保你的 includes 文件夹里有 db.php。
// 如果你朋友把名字改成了 _base.php，请把下面的 db.php 改成 _base.php 并且确保文件存在！
require_once __DIR__ . '/../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    $final_image_path = ''; 

    try {
        $pdo->beginTransaction();

        // 1. 先把基础信息存进去，拿到新的 product_id
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $description]);
        $new_id = $pdo->lastInsertId();

        // 2. 如果上传了图片，处理图片并更新刚刚那条记录
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            $tmp_name = $_FILES['photo']['tmp_name'];
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $new_filename = $new_id . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $final_image_path = 'uploads/products/' . $new_filename;
                // ⚠️ 注意：如果你数据库里存图片的字段不是 image_path，请修改下面的字段名
                $updateImg = $pdo->prepare("UPDATE products SET image_path = ? WHERE id = ?");
                $updateImg->execute([$final_image_path, $new_id]);
            }
        }

        $pdo->commit();
        echo "<script>alert('✨ Product successfully added!'); window.location.href='product_crud.php';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("<div style='padding:20px; color:red;'>Database Error: " . $e->getMessage() . "</div>");
    }
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    .page-title-box { display: none !important; }
    html, body { background: #f8fafc !important; overflow: hidden !important; }
    
    /* 强力接管区域：让这块区域变成独立的可滚动视口 */
    .scroll-wrapper { height: 100vh; overflow-y: auto; padding-bottom: 150px; box-sizing: border-box; }

    .premium-form-card { max-width: 650px; margin: 20px auto; background: #ffffff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04); overflow: hidden; font-family: 'Inter', sans-serif; }
    .form-header { background: #111; padding: 20px 30px; border-bottom: 4px solid #e60012; }
    .form-header h2 { margin: 0; color: white; font-size: 22px; font-weight: 900; letter-spacing: -0.5px; }
    .form-header p { margin: 5px 0 0; color: #94a3b8; font-size: 11px; font-weight: 600; }
    
    .form-body { padding: 25px 30px; }
    
    .grid-row { display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 15px; }
    .form-group { margin-bottom: 15px; }
    .grid-row .form-group { margin-bottom: 0; }

    .form-group label { display: block; font-weight: 800; color: #64748b; margin-bottom: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
    .input-style { width: 100%; padding: 12px 16px; border-radius: 10px; border: 2px solid #f1f5f9; font-size: 14px; font-weight: 600; color: #1e293b; background: #f8fafc; transition: all 0.3s ease; box-sizing: border-box; }
    .input-style:focus { border-color: #e60012; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(230,0,18,0.1); }
    
    .file-upload-box { border: 2px dashed #cbd5e1; padding: 15px; text-align: center; border-radius: 10px; background: #f8fafc; transition: 0.3s; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 15px; }
    .file-upload-box:hover { border-color: #e60012; background: #fff; }
    .current-photo { width: 80px; height: 80px; border-radius: 8px; border: 2px solid #e2e8f0; object-fit: cover; }
    .no-photo-icon { font-size: 30px; color: #cbd5e1; }

    .btn-row { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
    .btn-submit { background: linear-gradient(135deg, #e60012 0%, #ff1a2d 100%); color: white; padding: 14px 30px; border: none; border-radius: 10px; font-size: 13px; font-weight: 900; cursor: pointer; transition: all 0.3s ease; }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(230,0,18,0.3); }
    .btn-back { color: #94a3b8; text-decoration: none; font-size: 12px; font-weight: 800; padding-left: 5px; transition: 0.3s; }
    .btn-back:hover { color: #1e293b; }
</style>

<div id="add-wrapper" class="scroll-wrapper" style="display:none;">
    <div id="add-product-app" class="premium-form-card">
        <div class="form-header">
            <h2>New Catalog Entry</h2>
            <p>ADD A NEW PRODUCT TO INVENTORY</p>
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
                    <label>Product Photo (Single Image)</label>
                    <div class="file-upload-box" onclick="document.getElementById('photo-input').click();">
                        
                        <img id="image-preview" class="current-photo" style="display:none;" src="">
                        
                        <div id="no-photo-placeholder" style="text-align: left;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="no-photo-icon">📷</div>
                                <div>
                                    <span style="font-weight: 700; color: #1e293b; display: block; font-size: 14px;">Upload product photo</span>
                                    <span style="font-size: 11px; color: #64748b;">JPG, PNG, WEBP</span>
                                </div>
                            </div>
                        </div>

                        <input type="file" name="photo" id="photo-input" accept="image/*" style="display: none;">
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
        const myContent = document.getElementById('add-wrapper');
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