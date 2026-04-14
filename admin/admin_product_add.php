<?php
// TEE DE YONG's Part: Add Product (Updated for Multiple Photos)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    $uploaded_images = []; // 用于存放多个文件名

    try {
        $pdo->beginTransaction();

        // 1. 先把基础信息存进去，拿到新的 product_id
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $description]);
        $new_id = $pdo->lastInsertId();

        // 2. 【核心修改】：处理多图上传
        if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION);
                    // 文件名格式：ID_时间_序号.后缀
                    $new_filename = $new_id . '_' . time() . '_' . $key . '.' . $file_extension;
                    $target_file = $upload_dir . $new_filename;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $uploaded_images[] = 'uploads/products/' . $new_filename;
                    }
                }
            }

            if (!empty($uploaded_images)) {
                // 将多个路径用逗号隔开存入数据库
                $final_images_path = implode(',', $uploaded_images);
                $updateImg = $pdo->prepare("UPDATE products SET product_image = ? WHERE product_id = ?");
                $updateImg->execute([$final_images_path, $new_id]);
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
    
    /* 修改上传框以适应多图预览 */
    .file-upload-box { border: 2px dashed #cbd5e1; padding: 15px; text-align: center; border-radius: 10px; background: #f8fafc; transition: 0.3s; cursor: pointer; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 10px; min-height: 100px; }
    .file-upload-box:hover { border-color: #e60012; background: #fff; }
    .current-photo { width: 60px; height: 60px; border-radius: 8px; border: 2px solid #e2e8f0; object-fit: cover; }
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

<script>
    document.getElementById('photo-input').addEventListener('change', function(e) {
        const files = e.target.files;
        const container = document.getElementById('preview-container');
        const placeholder = document.getElementById('no-photo-placeholder');
        
        // 清除现有的预览图（保留 input 本身）
        const oldPreviews = container.querySelectorAll('.current-photo');
        oldPreviews.forEach(img => img.remove());

        if (files.length > 0) {
            placeholder.style.display = 'none';
            
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.classList.add('current-photo');
                    container.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        } else {
            placeholder.style.display = 'flex';
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
