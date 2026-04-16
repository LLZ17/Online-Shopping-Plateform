<?php
// TEE DE_YONG's Part: Edit Product (Strictly Matched to Database & Premium UI)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$id = $_GET['id'] ?? ''; // 接收如 MP013 的字符串 ID
if (!$id) {
    die("<script>alert('⚠️ No product ID provided!'); window.location.href='product_crud.php';</script>");
}

// 跟着你的数据库查询
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
    $category_id = $_POST['category_id'] ?? '';
    $keywords = $_POST['keywords'] ?? '';
    $size = $_POST['size'] ?? '';
    $colour = $_POST['colour'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $stock = $_POST['stock'] ?? 0;
    $sales_count = $_POST['sales_count'] ?? 0;
    $status = $_POST['status'] ?? 'In stock';
    $product_type = $_POST['product_type'] ?? '';

    // 跟着你的数据库：图片字段是 product_image
    $final_image_path = $product['product_image'] ?? ''; 

    try {
        $pdo->beginTransaction();

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

            $tmp_name = $_FILES['photo']['tmp_name'];
            $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            
            // 保持你数据库中的格式: ID/文件名
            $new_filename = $id . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $final_image_path = $id . '/' . $new_filename; 
            }
        }

        // 严格跟着你的数据库字段进行更新
        $updateStmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, product_image = ?, category_id = ?, keywords = ?, size = ?, colour = ?, rating = ?, stock = ?, sales_count = ?, status = ?, product_type = ? WHERE product_id = ?");
        $updateStmt->execute([$name, $price, $description, $final_image_path, $category_id, $keywords, $size, $colour, $rating, $stock, $sales_count, $status, $product_type, $id]);

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

<style>
    .page-title-box { display: none !important; }
    html, body { background: #f8fafc !important; }
    
    .scroll-wrapper { height: 100vh; overflow-y: auto; padding-bottom: 150px; box-sizing: border-box; }

    .premium-form-card { max-width: 650px; margin: 20px auto; background: #ffffff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04); overflow: hidden; font-family: 'Inter', sans-serif; }
    .form-header { background: #111; padding: 20px 30px; border-bottom: 4px solid #eab308; }
    .form-header h2 { margin: 0; color: white; font-size: 22px; font-weight: 900; letter-spacing: -0.5px; }
    .form-header p { margin: 5px 0 0; color: #94a3b8; font-size: 11px; font-weight: 600; }
    
    .form-body { padding: 25px 30px; }
    
    .grid-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
    .grid-row-large { display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 15px; }
    .form-group { margin-bottom: 15px; }

    .form-group label { display: block; font-weight: 800; color: #64748b; margin-bottom: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
    .input-style { width: 100%; padding: 12px 16px; border-radius: 10px; border: 2px solid #f1f5f9; font-size: 14px; font-weight: 600; color: #1e293b; background: #f8fafc; transition: all 0.3s ease; box-sizing: border-box; }
    .input-style:focus { border-color: #eab308; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(234,179,8,0.1); }
    
    /* ✨ 自定义选择器样式 */
    .custom-select-wrapper { position: relative; cursor: pointer; }
    .custom-select-trigger { display: flex; align-items: center; justify-content: space-between; border: 2px solid #eab308; background: #fff; }
    .custom-options { 
        position: absolute; top: 105%; left: 0; right: 0; 
        background: #fff; border: 2px solid #eab308; border-radius: 12px; 
        opacity: 0; visibility: hidden; transform: translateY(-10px); 
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 1000;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden;
    }
    .custom-select-wrapper.open .custom-options { opacity: 1; visibility: visible; transform: translateY(0); }
    .options-inner { max-height: 350px; overflow-y: auto; padding: 8px; }
    .option-group-title { font-size: 11px; font-weight: 900; color: #111; background: #fdfaf3; padding: 8px 12px; border-radius: 6px; margin: 8px 0 4px; text-transform: uppercase; }
    .custom-option { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; color: #64748b; transition: 0.2s; }
    .custom-option:hover { background: #0f172a; color: #eab308; }
    .custom-option.selected { background: #fdfaf3; color: #854d0e; }

    /* Tags 样式 */
    .tag-container { display: flex; flex-wrap: wrap; gap: 10px; padding: 5px 0; }
    .tag-item { padding: 8px 16px; border-radius: 12px; font-size: 13px; font-weight: 800; color: #64748b; background: #ffffff; border: 2px solid #e2e8f0; cursor: pointer; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); user-select: none; }
    .tag-item:hover { border-color: #cbd5e1; transform: translateY(-2px); }
    .tag-item.active { background: #0f172a; color: #eab308; border-color: #0f172a; transform: translateY(-2px); box-shadow: 0 8px 15px -3px rgba(15, 23, 42, 0.3); }

    .file-upload-box { border: 2px dashed #cbd5e1; padding: 15px; text-align: center; border-radius: 10px; background: #f8fafc; transition: 0.3s; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 15px; }
    .current-photo { width: 80px; height: 80px; border-radius: 8px; border: 2px solid #e2e8f0; object-fit: cover; }

    .btn-row { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
    .btn-submit { background: #111; color: #eab308; padding: 14px 30px; border: 2px solid #eab308; border-radius: 10px; font-size: 13px; font-weight: 900; cursor: pointer; transition: 0.3s; }
    .btn-submit:hover { background: #eab308; color: #111; transform: translateY(-2px); }
    .btn-back { color: #94a3b8; text-decoration: none; font-size: 12px; font-weight: 800; }
</style>

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
                                <div class="custom-option" data-value="MS">Men - Shirt (MS)</div>
                                <div class="custom-option" data-value="MP">Men - Pants (MP)</div>
                                <div class="custom-option" data-value="MO">Men - Others (MO)</div>
                                <div class="option-group-title">Women's Section</div>
                                <div class="custom-option" data-value="WS">Women - Shirt (WS)</div>
                                <div class="custom-option" data-value="WP">Women - Pants and Skirt (WP)</div>
                                <div class="custom-option" data-value="WO">Women - Others (WO)</div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="product_type" id="hidden-product-type" value="<?= htmlspecialchars($product['product_type'] ?? '') ?>">
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
                        <img id="image-preview" class="current-photo" src="<?= !empty($product['product_image']) ? '../' . htmlspecialchars($product['product_image']) : '' ?>" style="<?= empty($product['product_image']) ? 'display:none;' : '' ?>">
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
    // Tag Logic
    function setupTags(boxId, inputId) {
        const box = document.getElementById(boxId);
        const input = document.getElementById(inputId);
        if(!box) return;
        box.addEventListener('click', function(e) {
            if (e.target.classList.contains('tag-item')) {
                e.target.classList.toggle('active');
                let vals = [];
                box.querySelectorAll('.tag-item.active').forEach(t => vals.push(t.dataset.value));
                input.value = vals.join(',');
            }
        });
    }
    setupTags('size-tags', 'hidden-size');

    // Custom Select Logic
    const wrapper = document.getElementById('type-select-wrapper');
    const hiddenType = document.getElementById('hidden-product-type');
    const triggerText = wrapper.querySelector('.trigger-text');
    
    if(hiddenType.value) {
        const opt = wrapper.querySelector(`.custom-option[data-value="${hiddenType.value}"]`);
        if(opt) {
            triggerText.innerText = opt.innerText;
            opt.classList.add('selected');
        }
    }

    wrapper.addEventListener('click', function(e) {
        const opt = e.target.closest('.custom-option');
        if (opt) {
            hiddenType.value = opt.dataset.value;
            triggerText.innerText = opt.innerText;
            wrapper.querySelectorAll('.custom-option').forEach(o => o.classList.remove('selected'));
            opt.classList.add('selected');
            this.classList.remove('open');
        } else {
            this.classList.toggle('open');
        }
    });

    // Image Preview
    document.getElementById('photo-input').addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.getElementById('image-preview');
            img.src = event.target.result;
            img.style.display = 'block';
            document.getElementById('no-photo-placeholder').style.display = 'none';
        }
        reader.readAsDataURL(this.files[0]);
    });

    // Viewport Loader
    document.addEventListener("DOMContentLoaded", function() {
        const myContent = document.getElementById('edit-wrapper');
        myContent.style.display = 'block';
        let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
        if (!viewport) {
            const allElements = document.body.getElementsByTagName('*');
            for (let el of allElements) {
                if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                    viewport = el.parentElement; break;
                }
            }
        }
        if (viewport) {
            viewport.innerHTML = ''; 
            viewport.appendChild(myContent);
        } else {
            document.body.appendChild(myContent);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
