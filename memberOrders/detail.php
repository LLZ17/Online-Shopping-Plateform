<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/_head.php';

$order_date = $_GET['date'] ?? '';
$user_id = $_SESSION['user_id'];

if (!$order_date) { header('Location: history.php'); exit(); }

$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.product_image
    FROM orders o 
    JOIN products p ON o.product_id = p.product_id 
    WHERE o.order_date = ? AND o.user_id = ?
");
$stmt->execute([$order_date, $user_id]);
$items = $stmt->fetchAll();

if (empty($items)) { header('Location: history.php'); exit(); }
?>

<div class="product-order-detail-container" style="max-width:800px; margin:40px auto; padding:20px;">
    <h1>Order Details</h1>
    <?php foreach ($items as $item): 
        // 核心修复：处理多图字段，只取第一张图
        $img_array = explode(',', $item['product_image']);
        $img_to_show = trim($img_array[0]);
    ?>
        <div style="display:flex; gap:20px; border-bottom:1px solid #eee; padding:15px 0; align-items:center;">
            <img src="../images/<?php echo $img_to_show; ?>" style="width:70px; border-radius:8px;" onerror="this.src='../images/default.jpg'">
            <div>
                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                Size: <?php echo $item['selected_size']; ?> | Qty: <?php echo $item['quantity']; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <div style="text-align:right; margin-top:20px;">
        <span style="font-size:20px; font-weight:bold; color:#e60012;">Total: RM <?php echo number_format($items[0]['total_price'], 2); ?></span>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/_foot.php'; ?>