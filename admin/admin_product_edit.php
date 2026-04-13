<?php
// TEE DE YONG's Part: Edit Product Form (Horizontal Layout - Fixed Buttons)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/_base.php'; 
require_once '../includes/db.php';     

$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ? WHERE product_id = ?");
    if ($stmt->execute([$name, $price, $description, $id])) {
        echo "<script>alert('✨ Product updated successfully!'); window.location.href='product_crud.php';</script>";
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: product_crud.php");
    exit;
}

include '../includes/_admin_head.php'; 
?>

<style>
    /* 清场代码 */
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid,
    .admin-content-viewport > div[style*="background:white"] {
        display: none !important;
    }
    
    html, body { 
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%) !important; 
        overflow: auto !important; 
    }

    .edit-container { 
        max-width: 1000px; 
        margin: 50px auto 100px auto; /* 底部预留充足空间 */
        background: white; 
        border-radius: 20px; 
        box-shadow: 0 15px 40px rgba(0,0,0,0.06); 
        /* 删除了 overflow: hidden，防止底部按钮被吃掉 */
    }
    
    .edit-header { 
        background: #111; color: white; padding: 30px 50px; 
        display: flex; justify-content: space-between; align-items: center;
        border-radius: 20px 20px 0 0; /* 手动给头部加圆角 */
    }
    .edit-header h2 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: 1px; }
    .edit-header .sys-id { background: rgba(255,255,255,0.1); padding: 8px 15px; border-radius: 20px; font-size: 14px; font-weight: 600; }
    
    .form-body { padding: 40px 50px 50px 50px; } 
    
    .form-row { display: flex; gap: 30px; margin-bottom: 30px; }
    .form-row .form-group { flex: 1; margin-bottom: 0; }
    
    .form-group { margin-bottom: 30px; }
    .form-group label { display: block; font-weight: 700; color: #1a202c; margin-bottom: 10px; font-size: 14px; text-transform: uppercase; }
    
    .form-group input, .form-group textarea { 
        width: 100%; padding: 18px 20px; border-radius: 12px; 
        border: 2px solid transparent; font-size: 16px; font-weight: 500;
        color: #333; background: #f4f7f6; font-family: inherit; transition: 0.3s;
        box-sizing: border-box; /* 确保 padding 不会撑破容器 */
    }
    .form-group input:focus, .form-group textarea:focus { border-color: #e60012; background: white; outline: none; box-shadow: 0 0 0 4px rgba(230,0,18,0.1); }
    
    /* 关键的按钮区样式 */
    .action-row {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee;
    }
    
    .btn-submit { 
        background: #e60012; color: white; padding: 18px 50px; 
        border: none; border-radius: 12px; font-size: 16px; font-weight: 800; 
        cursor: pointer; transition: 0.3s;
    }
    .btn-submit:hover { background: #c5000f; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(230,0,18,0.2); }
    
    .btn-cancel { color: #888; text-decoration: none; font-weight: 600; font-size: 15px; transition: 0.2s; }
    .btn-cancel:hover { color: #111; }
</style>

<div id="horizontal-edit-form" class="edit-container">
    <div class="edit-header">
        <h2>Edit Product</h2>
        <div class="sys-id">System ID: #<?= $product['product_id'] ?></div>
    </div>
    
    <div class="form-body">
        <form method="post" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Price (RM)</label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Detailed Description</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            
            <div class="action-row">
                <a href="product_crud.php" class="btn-cancel">← Cancel & Return</a>
                <button type="submit" class="btn-submit">SAVE CHANGES</button>
            </div>
            </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('horizontal-edit-form');
        if (viewport && content) {
            viewport.innerHTML = ''; 
            viewport.appendChild(content);
        }
    });
</script>

</main>
<?php include '../includes/_foot.php'; ?>