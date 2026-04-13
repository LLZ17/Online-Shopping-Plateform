<?php
// TEE DE YONG's Part: Add Product (Grid Layout & Luxury UI)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $price, $description])) {
        echo "<script>alert('✨ Product successfully added!'); window.location.href='product_crud.php';</script>";
        exit;
    }
}

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    /* 强力接管页面高度，防止被截断 */
    .admin-content-viewport { 
        height: auto !important; 
        min-height: 100vh !important; 
        padding-bottom: 60px !important; 
        overflow-y: visible !important; 
    }
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > div[style*="background:white"] { display: none !important; }

    /* 奢华卡片主体 */
    .premium-form-card {
        max-width: 650px; 
        margin: 20px auto; 
        background: #ffffff; 
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.08); 
        border: 1px solid rgba(0,0,0,0.04);
        overflow: hidden;
    }

    /* 顶部高级黑金 */
    .form-header { 
        background: #111; 
        padding: 25px 35px; 
        border-bottom: 4px solid #e60012;
    }
    .form-header h2 { margin: 0; color: white; font-size: 24px; font-weight: 900; letter-spacing: -0.5px; }

    /* 表单内容区 */
    .form-body { padding: 30px 35px; }
    
    /* 💡 核心优化：网格布局（左右分栏） */
    .grid-row {
        display: grid;
        grid-template-columns: 2fr 1fr; /* 名字占2份，价格占1份 */
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group { margin-bottom: 20px; }
    .grid-row .form-group { margin-bottom: 0; /* 在网格内不需要额外的底部边距 */ }
    
    .form-group label { 
        display: block; font-weight: 800; color: #64748b; 
        margin-bottom: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
    }
    
    /* 输入框设计 */
    .input-style {
        width: 100%; padding: 14px 18px; border-radius: 12px; border: 2px solid #f1f5f9;
        font-size: 14px; font-weight: 600; color: #1e293b; background: #f8fafc;
        transition: all 0.3s ease; box-sizing: border-box;
    }
    .input-style:focus { border-color: #e60012; background: #fff; outline: none; box-shadow: 0 0 0 3px rgba(230,0,18,0.08); }

    /* 💡 底部操作区：距离拉开 */
    .btn-row { 
        display: flex; 
        justify-content: space-between; /* 完美分隔 Cancel 和 Confirm */
        align-items: center;
        margin-top: 10px; 
        padding-top: 25px; 
        border-top: 1px solid #f1f5f9; 
    }
    
    /* 提交按钮：保持红色的视觉冲击力 */
    .btn-submit { 
        background: linear-gradient(135deg, #e60012 0%, #ff1a2d 100%); 
        color: white; padding: 14px 35px; border: none; border-radius: 12px; 
        font-size: 14px; font-weight: 900; cursor: pointer; 
        box-shadow: 0 8px 20px rgba(230,0,18,0.2);
        transition: all 0.3s ease;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(230,0,18,0.3); }

    .btn-back { color: #94a3b8; text-decoration: none; font-size: 13px; font-weight: 800; padding-left: 5px; transition: 0.3s; }
    .btn-back:hover { color: #1e293b; }
</style>

<div id="add-product-optimized" class="premium-form-card">
    <div class="form-header">
        <h2>New Catalog Entry</h2>
    </div>
    <div class="form-body">
        <form method="POST">
            
            <div class="grid-row">
                <div class="form-group">
                    <label>Inventory Name</label>
                    <input type="text" name="name" class="input-style" required placeholder="e.g. Signature Shirt">
                </div>
                <div class="form-group">
                    <label>Price (RM)</label>
                    <input type="number" step="0.01" name="price" class="input-style" required placeholder="0.00">
                </div>
            </div>

            <div class="form-group">
                <label>Product Narrative</label>
                <textarea name="description" class="input-style" rows="2" placeholder="Briefly describe the product..."></textarea>
            </div>

            <div class="btn-row">
                <a href="product_crud.php" class="btn-back">← CANCEL</a>
                <button type="submit" class="btn-submit">CONFIRM ENTRY</button>
            </div>
            
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('add-product-optimized');
        if (viewport && content) {
            viewport.innerHTML = ''; 
            viewport.appendChild(content);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>