<?php
/**
 * PXX Mall - Category CRUD (Advanced Conditional Logic Edition)
 * 📂 Fields: category_id, category_name, category_gender, description, created_at
 * 📂 Colors: Detail(Blue), Edit(Green), Delete(Red)
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$pxx_root = dirname(__DIR__);
require_once $pxx_root . '/includes/db.php'; 
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// ============================================================
// 🌟 1. AJAX 联动逻辑：根据 Gender 返回对应的类型选项
// ============================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_types') {
    ob_clean();
    $gender = $_GET['gender'] ?? '';
    // 💡 严格遵循你的要求：男士(Shirt/Pants/Others)，女士(Shirt/Pants and Skirt/Others)
    $typeMap = [
        'Men' => [
            'Shirt' => 'Shirt',
            'Pants' => 'Pants',
            'Others' => 'Others'
        ],
        'Women' => [
            'Shirt' => 'Shirt',
            'Pants and Skirt' => 'Women Pants and Skirt',
            'Others' => 'Others'
        ]
    ];
    
    $html = '<option value="" disabled selected>Select Category Type...</option>';
    if (isset($typeMap[$gender])) {
        foreach ($typeMap[$gender] as $val => $label) {
            $html .= "<option value=\"$val\">$label</option>";
        }
    }
    echo $html;
    exit;
}

// ============================================================
// 🌟 2. 处理 CRUD 提交
// ============================================================
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

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE category_id = ?")->execute([$_GET['delete']]);
    header("Location: category_crud.php?msg=deleted"); exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// 引入头部
$admin_head = $pxx_root . '/includes/_admin_head.php';
if (file_exists($admin_head)) { include $admin_head; }
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');
    .page-title-box { display: none !important; }
    .cat-container { width: 100%; box-sizing: border-box; padding: 25px; font-family: 'Plus Jakarta Sans', sans-serif; animation: fadeInUp 0.4s ease-out forwards; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    
    .cat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .cat-header h1 { font-size: 32px; font-weight: 900; color: #0f172a; margin: 0; }
    
    .stat-badge { background: #fff; padding: 12px 20px; border-radius: 14px; border: 1px solid #e2e8f0; font-weight: 700; color: #64748b; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .stat-badge span { color: #3b82f6; font-size: 18px; margin-left: 8px; font-weight: 900; }

    /* 表格 UI */
    .cat-card { background: #ffffff; border-radius: 24px; box-shadow: 0 10px 35px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; overflow: hidden; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { background: #f8fafc; padding: 20px; color: #64748b; font-size: 12px; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
    td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

    .pill { padding: 6px 14px; border-radius: 10px; font-size: 11px; font-weight: 800; }
    .pill-men { background: #e0f2fe; color: #0369a1; }
    .pill-women { background: #fce7f3; color: #be185d; }

    /* 按钮组 */
    .act-btns { display: flex; gap: 8px; }
    .btn-i { padding: 10px 16px; border-radius: 12px; font-weight: 800; font-size: 12px; border: 1px solid #e2e8f0; transition: 0.2s; cursor: pointer; background: #fff; }
    .btn-blue { color: #3b82f6; border-color: #3b82f6; }
    .btn-blue:hover { background: #3b82f6; color: #fff; }
    .btn-green { color: #15803d; border-color: #15803d; background: #dcfce7; }
    .btn-green:hover { background: #15803d; color: #fff; }
    .btn-red { color: #ef4444; border-color: #ef4444; background: #fee2e2; }
    .btn-red:hover { background: #ef4444; color: #fff; }

    /* Modal */
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); display: none; justify-content: center; align-items: center; z-index: 10000; backdrop-filter: blur(5px); }
    .modal-box { background: #fff; width: 500px; border-radius: 28px; padding: 40px; position: relative; box-shadow: 0 25px 50px rgba(0,0,0,0.1); }
    .modal-close { position: absolute; top: 25px; right: 25px; font-size: 28px; cursor: pointer; color: #cbd5e1; }
    .f-group { margin-bottom: 20px; }
    .f-group label { display: block; font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase; }
    .f-group input, .f-group select, .f-group textarea { width: 100%; padding: 14px; border-radius: 14px; border: 1px solid #e2e8f0; font-family: inherit; font-size: 14px; font-weight: 600; box-sizing: border-box; }

    .btn-save { width: 100%; background: #10b981; color: #fff; padding: 16px; border-radius: 14px; font-weight: 800; border: none; cursor: pointer; font-size: 15px; }
</style>

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
                    <th>Gender</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($categories): $idx = 1; foreach ($categories as $c): 
                    $gClass = ($c['category_gender'] === 'Men') ? 'pill-men' : 'pill-women';
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
        <h2 id="modalTitle" style="margin-top:0; font-weight:900;">Category Detail Info</h2>
        <hr style="border:0; border-top:1px solid #f1f5f9; margin:20px 0;">

        <form method="POST" action="category_crud.php" id="modalForm">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="is_new" id="f_is_new" value="false">
            
            <div class="f-group">
                <label>Category ID</label>
                <input type="text" name="category_id" id="f_id" required>
            </div>

            <div class="f-group">
                <label>Category Name</label>
                <input type="text" name="category_name" id="f_name" required>
            </div>

            <div class="f-group">
                <label>Category Gender</label>
                <select name="category_gender" id="f_gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Men">Men</option>
                    <option value="Women">Women</option>
                </select>
            </div>

            <div class="f-group">
                <label>Description</label>
                <textarea name="description" id="f_desc" rows="3"></textarea>
            </div>

            <div id="detailExtra" style="display:none; background:#f8fafc; padding:15px; border-radius:14px; margin-bottom:20px;">
                <label style="font-size:10px; font-weight:800; color:#94a3b8;">Creation Timestamp</label>
                <div id="f_created" style="font-weight:700; color:#1e293b;">-</div>
            </div>

            <div id="modalFooter">
                <button type="submit" class="btn-save">Save Database Record</button>
            </div>
        </form>
    </div>
</div>

<script>
function handleDelete(id) {
    if(confirm("🚨 CRITICAL: Are you sure you want to permanently delete Category #" + id + "?")) {
        window.location.href = "category_crud.php?delete=" + id;
    }
}

function openModal(mode, data = null) {
    const modal = document.getElementById('catModal');
    modal.style.display = 'flex';
    document.getElementById('modalForm').reset();
    
    const fId = document.getElementById('f_id');
    const inputs = modal.querySelectorAll('input, select, textarea');

    if(mode === 'add') {
        document.getElementById('modalTitle').innerText = "New Category Entry";
        document.getElementById('f_is_new').value = "true";
        fId.readOnly = false;
        fId.style.background = "#fff";
        document.getElementById('detailExtra').style.display = 'none';
        document.getElementById('modalFooter').style.display = 'block';
        inputs.forEach(i => i.disabled = false);
    } else {
        document.getElementById('f_is_new').value = "false";
        fId.value = data.category_id;
        fId.readOnly = true;
        fId.style.background = "#f8fafc";
        document.getElementById('f_name').value = data.category_name;
        document.getElementById('f_gender').value = data.category_gender;
        document.getElementById('f_desc').value = data.description;

        if(mode === 'detail') {
            document.getElementById('modalTitle').innerText = "Category Detail Info";
            document.getElementById('detailExtra').style.display = 'block';
            document.getElementById('f_created').innerText = data.created_at;
            document.getElementById('modalFooter').style.display = 'none';
            inputs.forEach(i => i.disabled = true);
        } else {
            document.getElementById('modalTitle').innerText = "Modify Category Data";
            document.getElementById('detailExtra').style.display = 'none';
            document.getElementById('modalFooter').style.display = 'block';
            inputs.forEach(i => i.disabled = false);
        }
    }
}

function closeModal() { document.getElementById('catModal').style.display = 'none'; }

document.addEventListener("DOMContentLoaded", function() {
    let vp = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    let ct = document.getElementById('pxxMain');
    if (vp && ct && vp !== ct.parentNode) {
        vp.innerHTML = ''; vp.appendChild(ct);
    }
});
</script>

<?php 
$foot = $pxx_root . '/includes/_admin_foot.php'; 
if (file_exists($foot)) { include $foot; } 
?>