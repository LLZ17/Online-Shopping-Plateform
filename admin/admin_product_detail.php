<?php
// TEE DE YONG's Part: Product Detail (Ultimate Premium SaaS Aesthetic)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

$id = $_GET['id'] ?? '';
if (!$id) {
    die("<script>alert('⚠️ No product ID provided!'); window.location.href='product_crud.php';</script>");
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?"); 
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("<div style='padding:50px; text-align:center; font-family:sans-serif;'><h2>⚠️ Entry not found!</h2><a href='product_crud.php' style='color:#3b82f6;'>← Go Back</a></div>");
}

// ✨ 智能分类逻辑修复
$typeLabels = [
    'MS' => 'Men - Shirt (MS)',
    'MP' => 'Men - Pants (MP)',
    'MO' => 'Men - Others (MO)',
    'WS' => 'Women - Shirt (WS)',
    'WP' => 'Women - Pants/Skirt (WP)',
    'WO' => 'Women - Others (WO)',
    'UN' => 'Unisex (UN)',
    'ACC'=> 'Accessories (ACC)'
];

// 如果数据库字段 product_type 为空，则根据 ID 前两位自动识别
$rawType = !empty($product['product_type']) ? $product['product_type'] : substr($product['product_id'], 0, 2);
$displayType = $typeLabels[$rawType] ?? $rawType;

$admin_head = __DIR__ . '/../includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css?v=1.4">

<div id="detail-wrapper" class="scroll-wrapper" style="display:none;">
    <div id="detail-product-app" class="ultimate-detail-card">
        
        <div class="hero-header">
            <div class="title-block">
                <div class="meta-id">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    <?= htmlspecialchars($product['product_id']) ?>
                </div>
                <h1 style="margin-top: 15px;"><?= htmlspecialchars($product['name'] ?? 'Unnamed Product') ?></h1>
            </div>
            
            <div class="status-indicator">
                <div class="status-dot"></div>
                <?php 
                    $statusText = $product['status'] ?? 'Unknown';
                    if (strcasecmp($statusText, 'In stock') === 0) {
                        echo 'In Stock'; 
                    } else {
                        echo htmlspecialchars($statusText);
                    }
                ?>
            </div>
        </div>

        <div class="content-body">
            
            <div class="media-gallery">
                <?php 
                    $imgData = $product['product_image'] ?? '';
                    $images = array_filter(array_map('trim', explode(',', $imgData)));
                    if (!empty($images)) {
                        foreach ($images as $img) {
                            $imgPath = (strpos($img, 'http') === 0) ? $img : ( (strpos($img, 'uploads/') === 0) ? '../' . $img : '../images/' . $img );
                            echo "<div class='gallery-item'><img src='" . htmlspecialchars($imgPath) . "' onerror=\"this.src='../images/default.png'\"></div>";
                        }
                    } else {
                        echo "<div style='color:#94a3b8; font-size:14px; font-weight:700; padding: 20px; border: 2px dashed #cbd5e1; border-radius: 16px; width: 100%; text-align: center;'>No Media Assets Uploaded</div>";
                    }
                ?>
            </div>

            <div class="data-grid">
                <div class="data-card" style="border-color: #fef08a; background: #fffbeb;">
                    <span class="data-label" style="color: #ca8a04;">Retail Value</span>
                    <div class="price-display"><span>RM</span><?= number_format($product['price'] ?? 0, 2) ?></div>
                </div>
                <div class="data-card">
                    <span class="data-label">Sales Performance</span>
                    <span class="data-value" style="font-size: 24px;"><?= htmlspecialchars($product['sales_count'] ?? '0') ?> <span style="font-size:14px; color:#64748b;">Units Sold</span></span>
                </div>
                <div class="data-card">
                    <span class="data-label">Customer Rating</span>
                    <span class="data-value" style="font-size: 24px;">⭐ <?= htmlspecialchars($product['rating'] ?? 'N/A') ?></span>
                </div>
            </div>

            <div class="data-grid">
                <div class="data-card" style="border-left: 4px solid #0f172a;">
                    <span class="data-label">Sequence Classification</span>
                    <div class="pill-group">
                         <span class="aesthetic-pill" style="background: #0f172a; color: #eab308; border: none;">
                            <?= htmlspecialchars($displayType) ?>
                         </span>
                    </div>
                </div>
                <div class="data-card">
                    <span class="data-label">Category Assignment</span>
                    <span class="data-value"><?= htmlspecialchars($product['category_id'] ?? 'Uncategorized') ?></span>
                </div>
                <div class="data-card">
                    <span class="data-label">Stock Availability</span>
                    <span class="data-value"><?= htmlspecialchars($product['stock'] ?? '0') ?> Units</span>
                </div>
            </div>

            <div class="data-grid">
                 <div class="data-card">
                    <span class="data-label">Search Keywords</span>
                    <span class="data-value"><?= htmlspecialchars($product['keywords'] ?? '-') ?></span>
                </div>
            </div>

            <div class="data-grid">
                <div class="data-card">
                    <span class="data-label">Available Sizes</span>
                    <div class="pill-group">
                        <?php 
                            $sizes = array_filter(array_map('trim', explode(',', $product['size'] ?? '')));
                            if(empty($sizes)) echo "<span class='data-value' style='font-size:14px;'>-</span>";
                            foreach($sizes as $s) echo "<span class='aesthetic-pill'>" . htmlspecialchars($s) . "</span>";
                        ?>
                    </div>
                </div>
                <div class="data-card">
                    <span class="data-label">Available Colours</span>
                    <div class="pill-group">
                        <?php 
                            $colours = array_filter(array_map('trim', explode(',', $product['colour'] ?? '')));
                            if(empty($colours)) echo "<span class='data-value' style='font-size:14px;'>-</span>";
                            foreach($colours as $c) echo "<span class='aesthetic-pill'>" . htmlspecialchars($c) . "</span>";
                        ?>
                    </div>
                </div>
            </div>

            <div class="narrative-box">
                <span class="data-label" style="margin-bottom: 12px; color: #475569;">Product Narrative</span>
                <div class="narrative-text">
                    <?= nl2br(htmlspecialchars($product['description'] ?? 'No narrative description provided for this catalog entry.')) ?>
                </div>
            </div>

            <div class="footer-actions">
                <a href="product_crud.php" class="btn-return">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    RETURN TO INVENTORY
                </a>
            </div>
            
        </div>
    </div>
</div>

<script src="../js/app.js?v=1.4"></script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
