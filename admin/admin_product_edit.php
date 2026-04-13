<?php
// TEE DE YONG's Part: Edit Product (Premium UI - Final Working Version)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$id = $_GET['id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { die("<div style='padding:50px; text-align:center;'><h2>⚠️ Entry not found!</h2></div>"); }

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

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    .admin-content-viewport > .page-title-box, .admin-content-viewport > div[style*="background:white"] { display: none !important; }
    html, body { background: #f8fafc !important; }

    .premium-form-card {
        max-width: 800px; margin: 40px auto; background: white; border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #edf2f7;
    }
    .form-header { background: #111; color: white; padding: 40px; border-bottom: 6px solid #eab308; }
    .form-header h2 { margin: 0; font-size: 28px; font-weight: 800; color: #fff; }

    .form-body { padding: 40px; }
    .form-group { margin-bottom: 30px; }
    .form-group label { display: block; font-weight: 800; color: #1e293b; margin-bottom: 12px; font-size: 14px; text-transform: uppercase; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 18px 22px; border-radius: 14px; border: 2px solid #f3f4f6;
        font-size: 16px; font-weight: 600; color: #333; background: #f9fafb; transition: 0.3s; box-sizing: border-box;
    }
    .form-group input:focus, .form-group textarea:focus { border-color: #eab308; background: white; outline: none; }
    
    .btn-row { display: flex; align-items: center; justify-content: space-between; margin-top: 40px; padding-top: 30px; border-top: 1px solid #f1f5f9; }
    .btn-submit { background: #111; color: #eab308; padding: 18px 50px; border: 2px solid #eab308; border-radius: 15px; font-size: 16px; font-weight: 900; cursor: pointer; transition: 0.3s; }
    .btn-submit:hover { background: #eab308; color: #111; transform: translateY(-3px); box-shadow: 0 12px 25px rgba(234,179,8,0.3); }
    .btn-back { color: #94a3b8; text-decoration: none; font-weight: 700; }
</style>

<div id="edit-product-premium" class="premium-form-card">
    <div class="form-header">
        <h2>Update Entry #<?= htmlspecialchars($id) ?></h2>
        <p>PREMIUM CATALOG EDITOR</p>
    </div>
    <div class="form-body">
        <form method="post" action="">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Market Price (RM)</label>
                <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>
            </div>
            <div class="form-group">
                <label>Technical Description</label>
                <textarea name="description" rows="6"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            <div class="btn-row">
                <a href="product_crud.php" class="btn-back">← Cancel Changes</a>
                <button type="submit" class="btn-submit">SAVE MODIFICATIONS</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('edit-product-premium');
        if (viewport && content) { viewport.innerHTML = ''; viewport.appendChild(content); }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>