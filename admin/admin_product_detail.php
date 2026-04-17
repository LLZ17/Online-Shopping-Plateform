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

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    .page-title-box { display: none !important; }
    html, body { background: #f1f5f9 !important; overflow: hidden !important; }
    
    .scroll-wrapper {
        height: 100vh;
        overflow-y: auto;
        padding-bottom: 150px;
        box-sizing: border-box;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    @keyframes smoothReveal {
        0% { opacity: 0; transform: translateY(25px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .ultimate-detail-card { 
        max-width: 780px; 
        margin: 30px auto; 
        background: #ffffff; 
        border-radius: 32px; 
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(226, 232, 240, 0.5);
        overflow: hidden; 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        animation: smoothReveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    .hero-header { 
        padding: 40px 40px 20px 40px; 
        display: flex; 
        justify-content: space-between; 
        align-items: flex-start; 
    }
    .title-block h1 { 
        margin: 0 0 8px 0; 
        color: #0f172a; 
        font-size: 28px; 
        font-weight: 900; 
        letter-spacing: -0.5px; 
    }
    .title-block .meta-id { 
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #64748b; 
        font-size: 13px; 
        font-weight: 700; 
        background: #f1f5f9;
        padding: 6px 12px;
        border-radius: 8px;
    }
    
    /* ✨ 改成像第2张图这样的 ACTIVE 绿色发光样式 */
    .status-indicator { 
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(16, 185, 129, 0.1) !important; /* 绿色背景 */
        color: #10b981 !important; /* 绿色文字 */
        padding: 8px 16px; 
        border-radius: 100px; 
        font-size: 13px; 
        font-weight: 800; 
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15) !important; /* 绿色发光 */
        border: 1px solid rgba(16, 185, 129, 0.5) !important; /* 绿色边框 */
    }
    /* ✨ 绿色发光圆点 */
    .status-dot { 
        width: 8px; 
        height: 8px; 
        background: #10b981 !important; /* 绿色圆点 */
        border-radius: 50%; 
        box-shadow: 0 0 10px #10b981 !important; /* 绿色发光 */
    }

    .content-body { padding: 0 40px 40px 40px; }
    
    .media-gallery { 
        display: flex; 
        gap: 16px; 
        overflow-x: auto; 
        padding: 10px 10px 25px 10px; 
        margin: 0 -10px;
        scrollbar-width: none;
    }
    .media-gallery::-webkit-scrollbar { display: none; }
    
    .gallery-item { 
        flex: 0 0 auto;
        width: 180px; 
        height: 180px; 
        border-radius: 20px; 
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); 
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .gallery-item:hover img { transform: scale(1.08); }

    .data-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px; 
        margin-bottom: 25px; 
    }
    .data-card { 
        background: #ffffff; 
        padding: 20px; 
        border-radius: 20px; 
        border: 1px solid #f1f5f9;
        transition: 0.2s;
    }
    .data-card:hover { transform: translateY(-3px); box-shadow: 0 12px 20px -8px rgba(0,0,0,0.08); }
    
    .data-label { 
        font-size: 11px; 
        font-weight: 800; 
        color: #94a3b8; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        margin-bottom: 10px; 
        display: block; 
    }
    .data-value { font-size: 16px; font-weight: 700; color: #0f172a; }
    
    .price-display { 
        font-size: 34px; 
        font-weight: 900; 
        background: linear-gradient(135deg, #0f172a 0%, #b45309 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .price-display span { font-size: 18px; font-weight: 800; vertical-align: top; }

    .pill-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .aesthetic-pill { 
        background: #f8fafc; 
        color: #334155; 
        border: 1px solid #e2e8f0;
        padding: 6px 16px; 
        border-radius: 100px; 
        font-size: 13px; 
        font-weight: 700; 
        transition: 0.2s;
    }
    .aesthetic-pill:hover { background: #0f172a; color: #eab308; border-color: #0f172a; }

    .narrative-box { 
        background: #f8fafc; 
        padding: 25px; 
        border-radius: 20px; 
        border: 1px dashed #cbd5e1; 
    }
    .narrative-text { font-size: 15px; color: #475569; line-height: 1.8; }

    .footer-actions { 
        display: flex; 
        margin-top: 35px; 
        padding-top: 30px; 
        border-top: 1px solid #f1f5f9; 
    }
    .btn-return { 
        display: inline-flex; 
        align-items: center; 
        gap: 10px; 
        background: #0f172a; 
        color: #ffffff; 
        padding: 14px 32px; 
        border-radius: 14px; 
        font-weight: 800; 
        text-decoration: none; 
        transition: 0.3s;
    }
    .btn-return:hover { background: #eab308; color: #0f172a; transform: translateY(-3px); }
</style>

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
                    // 如果状态是 In stock，我们可以将其显示为 ACTIVE，以符合第二张图的意境
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const myContent = document.getElementById('detail-wrapper');
        if (!myContent) return;
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
            myContent.style.position = 'fixed';
            myContent.style.top = '60px'; 
            myContent.style.right = '0';
            myContent.style.width = 'calc(100vw - 250px)'; 
            myContent.style.height = 'calc(100vh - 60px)';
            myContent.style.background = '#f1f5f9';
            myContent.style.zIndex = '9999';
            document.body.appendChild(myContent);
        }
    });
</script>

<?php 
$foot = __DIR__ . '/../includes/_foot.php';
if (file_exists($foot)) { include $foot; } 
?>
