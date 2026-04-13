<?php
// TEE DE YONG's Part: Add New Product Form (Premium Layout - Fixed)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/_base.php'; 
require_once '../includes/db.php';     

// 处理表单提交 (插入新数据，不需要验证 ID)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    // 写入数据库
    $stmt = $pdo->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $price, $description])) {
        echo "<script>alert('✨ New product added successfully!'); window.location.href='product_crud.php';</script>";
        exit;
    }
}

// 引用头文件
include '../includes/_admin_head.php'; 
?>

<style>
    /* 清场代码：隐藏 Dashboard 多余内容 */
    .admin-content-viewport > .page-title-box, 
    .admin-content-viewport > .stats-grid,
    .admin-content-viewport > div[style*="background:white"] {
        display: none !important;
    }
    
    html, body { 
        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%) !important; 
        overflow: auto !important; 
    }

    .add-container { 
        max-width: 1000px; 
        margin: 50px auto 100px auto; 
        background: white; 
        border-radius: 20px; 
        box-shadow: 0 15px 40px rgba(0,0,0,0.06); 
    }
    
    .add-header { 
        background: #e60012; /* 红色头部，和编辑页面的黑色区分开 */
        color: white; padding: 30px 50px; 
        display: flex; justify-content: space-between; align-items: center;
        border-radius: 20px 20px 0 0; 
    }
    .add-header h2 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: 1px; }
    .add-header .badge { background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 20px; font-size: 14px; font-weight: 600; }
    
    .form-body { padding: 40px 50px 50px 50px; } 
    
    .form-row { display: flex; gap: 30px; margin-bottom: 30px; }
    .form-row .form-group { flex: 1; margin-bottom: 0; }
    
    .form-group { margin-bottom: 30px; }
    .form-group label { display: block; font-weight: 700; color: #1a202c; margin-bottom: 10px; font-size: 14px; text-transform: uppercase; }
    
    .form-group input, .form-group textarea { 
        width: 100%; padding: 18px 20px; border-radius: 12px; 
        border: 2px solid transparent; font-size: 16px; font-weight: 500;
        color: #333; background: #f4f7f6; font-family: inherit; transition: 0.3s;
        box-sizing: border-box; 
    }
    .form-group input:focus, .form-group textarea:focus { border-color: #e60012; background: white; outline: none; box-shadow: 0 0 0 4px rgba(230,0,18,0.1); }
    
    .action-row {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 40px; padding-top: 30px; border-top: 1px solid #eee;
    }
    
    .btn-submit { 
        background: #111; /* Add 按钮设为纯黑，显得很酷 */
        color: white; padding: 18px 50px; 
        border: none; border-radius: 12px; font-size: 16px; font-weight: 800; 
        cursor: pointer; transition: 0.3s;
    }
    .btn-submit:hover { background: #333; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
    
    .btn-cancel { color: #888; text-decoration: none; font-weight: 600; font-size: 15px; transition: 0.2s; }
    .btn-cancel:hover { color: #111; }
</style>

<div id="horizontal-add-form" class="add-container">
    <div class="add-header">
        <h2>Add New Product</h2>
        <div class="badge">New Entry</div>
    </div>
    
    <div class="form-body">
        <form method="post" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" required placeholder="What are you selling?">
                </div>
                <div class="form-group">
                    <label>Price (RM)</label>
                    <input type="number" step="0.01" name="price" required placeholder="0.00">
                </div>
            </div>
            
            <div class="form-group">
                <label>Detailed Description</label>
                <textarea name="description" rows="5" placeholder="Enter the product details here..."></textarea>
            </div>
            
            <div class="action-row">
                <a href="product_crud.php" class="btn-cancel">← Cancel & Return</a>
                <button type="submit" class="btn-submit">+ CREATE PRODUCT</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const viewport = document.querySelector('.admin-content-viewport');
        const content = document.getElementById('horizontal-add-form');
        if (viewport && content) {
            viewport.innerHTML = ''; 
            viewport.appendChild(content);
        }
    });
</script>

</main>
<?php include '../includes/_foot.php'; ?>