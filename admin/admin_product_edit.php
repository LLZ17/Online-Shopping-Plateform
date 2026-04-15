<?php
// TEE DE YONG's Part: Edit Product (Premium UI Tag-Select Edition)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$id = $_GET['id'] ?? '';
if (!$id) {
    die("<script>alert('⚠️ No product ID provided!'); window.location.href='product_crud.php';</script>");
}

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
    
    // 💡 接收隐藏的 input 传来的逗号分隔字符串
    $size = $_POST['size'] ?? '';
    $colour = $_POST['colour'] ?? '';

    $rating = $_POST['rating'] ?? '';
    $stock = $_POST['stock'] ?? 0;
    $sales_count = $_POST['sales_count'] ?? 0;
    $status = $_POST['status'] ?? 'In stock';
    
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

        $updateStmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image_path = ?, category_id = ?, keywords = ?, size = ?, colour = ?, rating = ?, stock = ?, sales_count = ?, status = ? WHERE product_id = ?");
        $updateStmt->execute([$name, $price, $description, $final_image_path, $category_id, $keywords, $size, $colour, $rating, $stock, $sales_count, $status, $id]);

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
    html, body { background: #f8fafc !important; overflow: hidden !important; }
    
    .scroll-wrapper { height: 100vh; overflow-y: auto; padding-bottom: 150px; box-sizing: border-box; }

    .premium-form-card { max-width: 650px; margin: 20px auto; background: #ffffff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid rgba(0,0,0,0.04); overflow: hidden; font-family: 'Inter', sans-serif; }
    .form-header { background: #111; padding: 20px 30px; border-bottom: 4px solid #eab308; }
    .form-header h2 { margin: 0; color: white; font-size: 22px; font-weight: 900; letter-spacing: -0.5px; }
    .form-header p { margin: 5px 0 0; color: #94a3b8; font-size: 11px; font-weight: 600; }
    
    .form-body { padding: 25px 30px; }
    
    .grid-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
    .grid-row-large { display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 15px; }
    .form-group { margin-bottom: 15px; }
    .grid-row .form-group, .grid-row-large .form-group { margin-bottom: 0; }

    .form-group label { display: block; font-weight: 800; color: #64748b; margin-bottom: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
    .input-style { width: 100%; padding: 12px 16px; border-radius: 10px; border: 2px solid #f1f5f9; font-size: 14px; font-weight: 600; color: #1e293b; background: #f8fafc; transition: all 0.3s ease; box-sizing: border-box; }
    .input-style:focus { border-color: #eab308; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(234,179,8,0.1); }
    select.input-style { cursor: pointer; appearance: auto; }
    
    /* ✨ 超高级美化版 Tag 样式 */
    .tag-container { display: flex; flex-wrap: wrap; gap: 10px; padding: 5px 0; }
    .tag-item { 
        padding: 8px 16px; 
        border-radius: 12px; /* 更圆润的果冻感 */
        font-size: 13px; 
        font-weight: 800; 
        color: #64748b; 
        background: #ffffff; 
        border: 2px solid #e2e8f0;
        cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* 贝塞尔曲线，拥有丝滑弹跳感 */
        user-select: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 悬浮时的微微浮起感 */
    .tag-item:hover { 
        border-color: #cbd5e1; 
        color: #1e293b;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    
    /* 选中的高级黑金状态 */
    .tag-item.active { 
        background: #0f172a; /* 高级深灰黑 */
        color: #eab308; /* 主题金黄色 */
        border-color: #0f172a; 
        transform: translateY(-2px);
        box-shadow: 0 8px 15px -3px rgba(15, 23, 42, 0.3);
    }

    /* 点击按下的物理反馈 */
    .tag-item:active {
        transform: scale(0.95) translateY(0);
    }

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
            <form method="POST" action="" enctype="multipart/form-data" id="productForm">
                
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
                        <select name="category_id" class="input-style">
                            <option value="">-- Select Category --</option>
                            <option value="Man" <?= ($product['category_id'] ?? '') === 'Man' ? 'selected' : '' ?>>Man</option>
                            <option value="Women" <?= ($product['category_id'] ?? '') === 'Women' ? 'selected' : '' ?>>Women</option>
                            <option value="CAT-MP-09" <?= ($product['category_id'] ?? '') === 'CAT-MP-09' ? 'selected' : '' ?>>CAT-MP-09</option>
                            <option value="CAT-WP-14" <?= ($product['category_id'] ?? '') === 'CAT-WP-14' ? 'selected' : '' ?>>CAT-WP-14</option>
                            
                            <?php 
                                $existingCat = $product['category_id'] ?? '';
                                if(!empty($existingCat) && !in_array($existingCat, ['Man', 'Women', 'CAT-MP-09', 'CAT-WP-14'])): 
                            ?>
                                <option value="<?= htmlspecialchars($existingCat) ?>" selected><?= htmlspecialchars($existingCat) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="input-style">
                            <option value="In stock" <?= ($product['status'] ?? '') == 'In stock' ? 'selected' : '' ?>>In stock</option>
                            <option value="Low in stock" <?= ($product['status'] ?? '') == 'Low in stock' ? 'selected' : '' ?>>Low in stock</option>
                            <option value="Out of stock" <?= ($product['status'] ?? '') == 'Out of stock' ? 'selected' : '' ?>>Out of stock</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Select Size</label>
                    <div class="tag-container" id="size-tags">
                        <?php 
                            $savedSizes = array_map('trim', explode(',', $product['size'] ?? ''));
                            $sizeList = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'Free Size'];
                            foreach ($savedSizes as $s) { if (!empty($s) && !in_array($s, $sizeList)) $sizeList[] = $s; }
                            
                            foreach ($sizeList as $sOption): 
                                $isActive = in_array($sOption, $savedSizes) ? 'active' : '';
                        ?>
                            <div class="tag-item <?= $isActive ?>" data-value="<?= htmlspecialchars($sOption) ?>"><?= htmlspecialchars($sOption) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="size" id="hidden-size" value="<?= htmlspecialchars($product['size'] ?? '') ?>">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Select Colour</label>
                    <div class="tag-container" id="colour-tags">
                        <?php 
                            $savedColours = array_map('trim', explode(',', $product['colour'] ?? ''));
                            $colourList = ['Black', 'White', 'Red', 'Blue', 'Grey', 'Navy'];
                            foreach ($savedColours as $c) { if (!empty($c) && !in_array($c, $colourList)) $colourList[] = $c; }
                            
                            foreach ($colourList as $cOption): 
                                $isActive = in_array($cOption, $savedColours) ? 'active' : '';
                        ?>
                            <div class="tag-item <?= $isActive ?>" data-value="<?= htmlspecialchars($cOption) ?>"><?= htmlspecialchars($cOption) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="colour" id="hidden-colour" value="<?= htmlspecialchars($product['colour'] ?? '') ?>">
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

                <div class="grid-row">
                    <div class="form-group">
                        <label>Rating</label>
                        <input type="text" name="rating" class="input-style" value="<?= htmlspecialchars($product['rating'] ?? '') ?>" placeholder="e.g. 5.0">
                    </div>
                    <div class="form-group">
                        <label>Keywords</label>
                        <input type="text" name="keywords" class="input-style" value="<?= htmlspecialchars($product['keywords'] ?? '') ?>" placeholder="e.g. casual, summer">
                    </div>
                </div>

                <div class="form-group">
                    <label>Product Narrative (Description)</label>
                    <textarea name="description" class="input-style" rows="2"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
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
    // 💡 处理高级 Tag 点击交互和隐藏域数据更新
    function setupTags(containerId, hiddenInputId) {
        const container = document.getElementById(containerId);
        const hiddenInput = document.getElementById(hiddenInputId);
        
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('tag-item')) {
                // 切换选中状态
                e.target.classList.toggle('active');
                
                // 收集所有被选中的 tag
                let selectedValues = [];
                container.querySelectorAll('.tag-item.active').forEach(tag => {
                    selectedValues.push(tag.getAttribute('data-value'));
                });
                
                // 用逗号拼接并放入隐藏输入框
                hiddenInput.value = selectedValues.join(',');
            }
        });
    }

    // 初始化 Size 和 Colour 的 Tags
    setupTags('size-tags', 'hidden-size');
    setupTags('colour-tags', 'hidden-colour');


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
