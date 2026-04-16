<?php
/**
 * PXX Mall - Category CRUD (Separated Edition)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$pxx_root = dirname(__DIR__);
require_once $pxx_root . '/includes/db.php'; 
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// --- 处理保存逻辑 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    try {
        $cid    = $_POST['category_id'] ?? '';
        $cname  = $_POST['category_name'] ?? '';
        $gender = $_POST['category_gender'] ?? ''; 
        $desc   = $_POST['description'] ?? '';

        if ($_POST['is_new'] === 'true') {
            $stmt = $pdo->prepare("INSERT INTO categories (category_id, category_name, category_gender, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cid, $cname, $gender, $desc]);
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET category_name=?, category_gender=?, description=? WHERE category_id=?");
            $stmt->execute([$cname, $gender, $desc, $cid]);
        }
        header("Location: category_crud.php?msg=success"); exit;
    } catch (PDOException $e) { $errorMsg = $e->getMessage(); }
}

// --- 处理删除 ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE category_id = ?")->execute([$_GET['delete']]);
    header("Location: category_crud.php?msg=deleted"); exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// 引入头部
$admin_head = $pxx_root . '/includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<link rel="stylesheet" href="../css/app.css">
<div class="cat-container" id="pxxMain">
    <div class="cat-header">
        <h1>Category CRUD</h1>
        <div style="display: flex; gap: 12px; align-items: center;">
            <div class="stat-badge">Record Count <span><?= count($categories) ?></span></div>
            <button onclick="location.reload()" class="btn-i"><span>🔄</span> Refresh</button>
            <button onclick="openModal('add')" class="btn-i" style="background:#0f172a; color:#fff; border:none; padding:12px 20px;"><span>➕</span> NEW CATEGORY</button>
        </div>
    </div>

    <div class="cat-card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Target Gender & Type</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($categories): $idx = 1; foreach ($categories as $c): 
                    $isWomen = (strpos($c['category_gender'], 'Women') !== false);
                    $gClass = $isWomen ? 'pill-women' : 'pill-men';
                ?>
                <tr>
                    <td style="font-weight:800; color:#cbd5e1;"><?= $idx++ ?></td>
                    <td style="font-weight:700; color:#94a3b8;">#<?= htmlspecialchars($c['category_id']) ?></td>
                    <td style="font-weight:800;"><?= htmlspecialchars($c['category_name']) ?></td>
                    <td><span class="pill <?= $gClass ?>"><?= strtoupper($c['category_gender']) ?></span></td>
                    <td style="font-size:13px; color:#64748b;"><?= date('d M Y, H:i', strtotime($c['created_at'])) ?></td>
                    <td>
                        <div class="act-btns">
                            <button onclick='openModal("detail", <?= json_encode($c) ?>)' class="btn-i btn-blue">Detail</button>
                            <button onclick='openModal("edit", <?= json_encode($c) ?>)' class="btn-i btn-green">Edit</button>
                            <button onclick='handleDelete("<?= $c['category_id'] ?>")' class="btn-i btn-red">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6" style="text-align:center; padding:50px;">No categories found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="catModal">
    <div class="modal-box">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle" style="margin-top:0; font-weight:900;">Category Detail</h2>
        <hr style="border:0; border-top:1px solid #f1f5f9; margin:20px 0;">

        <form method="POST" action="category_crud.php" id="modalForm">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="is_new" id="f_is_new" value="false">
            
            <div class="f-group">
                <label>Category ID (Unique Code)</label>
                <input type="text" name="category_id" id="f_id" required>
            </div>

            <div class="f-group">
                <label>Category Name</label>
                <input type="text" name="category_name" id="f_name" required>
            </div>

            <div class="f-group">
                <label>Target Gender & Category Type</label>
                <select name="category_gender" id="f_gender" required>
                    <option value="" disabled selected>Select Gender & Type...</option>
                    <optgroup label="Men's Section">
                        <option value="Men - Shirt">Men - Shirt</option>
                        <option value="Men - Pants">Men - Pants</option>
                        <option value="Men - Others">Men - Others</option>
                    </optgroup>
                    <optgroup label="Women's Section">
                        <option value="Women - Shirt">Women - Shirt</option>
                        <option value="Women - Pants and Skirt">Women - Pants and Skirt</option>
                        <option value="Women - Others">Women - Others</option>
                    </optgroup>
                </select>
            </div>

            <div class="f-group">
                <label>Description</label>
                <textarea name="description" id="f_desc" rows="3"></textarea>
            </div>

            <div id="detailExtra" style="display:none; background:#f8fafc; padding:15px; border-radius:14px; margin-bottom:20px;">
                <label style="font-size:10px; font-weight:800; color:#94a3b8;">Creation Date</label>
                <div id="f_created" style="font-weight:700; color:#1e293b;">-</div>
            </div>

            <div id="modalFooter">
                <button type="submit" class="btn-save" id="saveBtnText">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script src="../js/app.js"></script>

<?php 
$foot = $pxx_root . '/includes/_admin_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>