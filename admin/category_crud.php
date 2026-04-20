<?php
/**
 * PXX Mall - Category CRUD (AJAX Refresh with Toast Notification)
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pxx_root = dirname(__DIR__);
require_once $pxx_root . '/includes/db.php'; 
if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// --- [AJAX 数据核心逻辑] ---
if (isset($_GET['fetch_ajax'])) {
    $searchTerm = trim($_GET['search'] ?? '');
    $filterGender = trim($_GET['filter_gender'] ?? '');

    $sql = "SELECT * FROM categories WHERE 1=1";
    $params = [];

    if ($searchTerm !== '') {
        $sql .= " AND (category_id LIKE ? OR category_name LIKE ?)";
        $params[] = "%$searchTerm%"; $params[] = "%$searchTerm%";
    }
    if ($filterGender !== '') {
        $sql .= " AND category_gender = ?";
        $params[] = $filterGender;
    }

    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($categories) {
        foreach ($categories as $c) {
            $isWomen = (stripos($c['category_gender'], 'Women') !== false);
            $pillClass = $isWomen ? 'pill-women' : 'pill-men';
            $jsData = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8');
            
            echo "<tr>
                    <td style='font-weight:800; color:#94a3b8;'>#{$c['category_id']}</td>
                    <td style='font-weight:800;'>".htmlspecialchars($c['category_name'])."</td>
                    <td><span class='pill $pillClass'>".strtoupper($c['category_gender'])."</span></td>
                    <td style='font-size:13px; color:#64748b;'>".date('d M Y, H:i', strtotime($c['created_at']))."</td>
                    <td>
                        <div style='display: flex; gap: 8px;'>
                            <button onclick='openModal(\"detail\", $jsData)' class='btn-i btn-blue'>Detail</button>
                            <button onclick='openModal(\"edit\", $jsData)' class='btn-i btn-green'>Edit</button>
                            <button onclick='handleDelete(\"{$c['category_id']}\")' class='btn-i btn-red'>Delete</button>
                        </div>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5' style='text-align:center; padding:50px; color:#94a3b8;'>No categories found.</td></tr>";
    }
    exit;
}

// --- [保存与删除逻辑] ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $cid = $_POST['category_id'];
    $cname = $_POST['category_name'];
    $gender = $_POST['category_gender'];
    $desc = $_POST['description'];
    if ($_POST['is_new'] === 'true') {
        $stmt = $pdo->prepare("INSERT INTO categories (category_id, category_name, category_gender, description, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$cid, $cname, $gender, $desc]);
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET category_name=?, category_gender=?, description=? WHERE category_id=?");
        $stmt->execute([$cname, $gender, $desc, $cid]);
    }
    header("Location: category_crud.php?msg=success"); exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE category_id = ?")->execute([$_GET['delete']]);
    header("Location: category_crud.php?msg=deleted"); exit;
}
include $pxx_root . '/includes/_admin_head.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');
    #pxxMain { font-family: 'Plus Jakarta Sans', sans-serif; padding: 20px; display: none; }
    .cat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .stat-badge { background: #0f172a; color: #fff; padding: 10px 18px; border-radius: 12px; font-weight: 800; }
    .filter-bar { background: #fff; padding: 20px; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; }
    .filter-grid { display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px; align-items: flex-end; }
    .cat-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; overflow: hidden; position: relative; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f8fafc; padding: 18px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; }
    td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; }
    .pill { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; }
    .pill-men { background: #e0f2fe; color: #0369a1; }
    .pill-women { background: #fce7f3; color: #be185d; }
    .btn-i { padding: 10px 16px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; border: none; transition: 0.2s; }
    .btn-blue { background: #eff6ff; color: #1d4ed8; }
    .btn-green { background: #dcfce7; color: #15803d; }
    .btn-red { background: #fee2e2; color: #b91c1c; }

    /* Toast Notification Style */
    #toastBox {
        position: fixed; top: 30px; right: 30px; z-index: 99999;
        background: #0f172a; color: #fff; padding: 16px 24px;
        border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        display: none; align-items: center; gap: 12px; font-weight: 800;
        animation: slideIn 0.4s ease forwards;
    }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    .modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); display:none; justify-content:center; align-items:center; z-index:9999; backdrop-filter:blur(4px); }
    .modal-box { background:#fff; width:550px; border-radius:24px; padding:35px; }
    .f-group { margin-bottom: 15px; }
    .f-group label { display:block; font-size:12px; font-weight:800; color:#64748b; margin-bottom:5px; }
    .f-group input, .f-group select, .f-group textarea { width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0; font-family:inherit; }
    .loading-overlay { display: none; position: absolute; width: 100%; height: 100%; background: rgba(255,255,255,0.7); z-index: 5; justify-content: center; align-items: center; font-weight: 800; }
</style>

<div id="toastBox">
    <span style="background: #22c55e; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 14px;">✓</span>
    <span id="toastMsg">Data Refreshed Successfully!</span>
</div>

<div id="pxxMain">
    <div class="cat-header">
        <h1>MyCategory CRUD</h1>
        <div style="display: flex; gap: 12px; align-items: center;">
            <div class="stat-badge">Total: <span id="totalCount" style="color:#22c55e;">0</span></div>
            <button onclick="refreshData(true)" class="btn-i" style="background:#fff; border:1px solid #e2e8f0;">🔄 Refresh</button>
            <button onclick="openModal('add')" class="btn-i" style="background:#0f172a; color:#fff;">➕ New Category</button>
        </div>
    </div>

    <div class="filter-bar">
        <form id="filterForm" class="filter-grid" onsubmit="return false;">
            <div>
                <label style="font-size:11px; font-weight:800; color:#64748b;">SEARCH</label>
                <input type="text" id="searchInp" placeholder="Search..." onkeyup="refreshData(false)" style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0;">
            </div>
            <div>
                <label style="font-size:11px; font-weight:800; color:#64748b;">FILTER TYPE</label>
                <select id="filterGen" onchange="refreshData(false)" style="width:100%; padding:12px; border-radius:12px; border:1px solid #e2e8f0;">
                    <option value="">All Categories</option>
                    <option value="MEN SHIRT">MEN SHIRT</option>
                    <option value="MEN PANTS">MEN PANTS</option>
                    <option value="MEN OTHERS">MEN OTHERS</option>
                    <option value="WOMEN SHIRT">WOMEN SHIRT</option>
                    <option value="WOMEN PANTS AND SKIRT">WOMEN PANTS AND SKIRT</option>
                    <option value="WOMEN OTHERS">WOMEN OTHERS</option>
                </select>
            </div>
            <button onclick="document.getElementById('filterForm').reset(); refreshData(true);" class="btn-i" style="background:#f1f5f9; height:45px;">Reset</button>
        </form>
    </div>

    <div class="cat-card">
        <div id="loader" class="loading-overlay">Updating...</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Gender & Type</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="catTableBody"></tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="catModal">
    <div class="modal-box">
        <h2 id="modalTitle" style="margin-top:0; font-weight:900;">Category Detail</h2>
        <form method="POST" id="modalForm">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="is_new" id="f_is_new" value="false">
            <div class="f-group"><label>Category ID</label><input type="text" name="category_id" id="f_id" required></div>
            <div class="f-group"><label>Name</label><input type="text" name="category_name" id="f_name" required></div>
            <div class="f-group">
                <label>Gender & Type</label>
                <select name="category_gender" id="f_gender" required>
                    <option value="MEN SHIRT">MEN SHIRT</option>
                    <option value="MEN PANTS">MEN PANTS</option>
                    <option value="MEN OTHERS">MEN OTHERS</option>
                    <option value="WOMEN SHIRT">WOMEN SHIRT</option>
                    <option value="WOMEN PANTS AND SKIRT">WOMEN PANTS AND SKIRT</option>
                    <option value="WOMEN OTHERS">WOMEN OTHERS</option>
                </select>
            </div>
            <div class="f-group"><label>Description</label><textarea name="description" id="f_desc" rows="3"></textarea></div>
            <div id="modalFooter" style="margin-top:25px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <button type="submit" class="btn-i" style="background:#10b981; color:#fff;">Save</button>
                <button type="button" onclick="closeModal()" class="btn-i" style="background:#f1f5f9;">Cancel</button>
            </div>
            <button id="closeDetailBtn" type="button" onclick="closeModal()" class="btn-i" style="display:none; width:100%; background:#0f172a; color:#fff; margin-top:20px;">Close</button>
        </form>
    </div>
</div>

<script>
// --- [增强版 AJAX 刷新] ---
async function refreshData(showToast = false) {
    const loader = document.getElementById('loader');
    const search = document.getElementById('searchInp').value;
    const filter = document.getElementById('filterGen').value;
    
    loader.style.display = 'flex';
    
    try {
        const res = await fetch(`category_crud.php?fetch_ajax=1&search=${encodeURIComponent(search)}&filter_gender=${encodeURIComponent(filter)}`);
        const html = await res.text();
        document.getElementById('catTableBody').innerHTML = html;
        
        const count = document.querySelectorAll('#catTableBody tr').length;
        document.getElementById('totalCount').innerText = html.includes('No categories') ? 0 : count;

        // 如果需要弹窗通知
        if(showToast) {
            const toast = document.getElementById('toastBox');
            toast.style.display = 'flex';
            setTimeout(() => { toast.style.display = 'none'; }, 2500); // 2.5秒后消失
        }
    } finally { 
        loader.style.display = 'none'; 
    }
}

function openModal(mode, data = null) {
    const modal = document.getElementById('catModal');
    const form = document.getElementById('modalForm');
    modal.style.display = 'flex';
    form.reset();
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(i => i.disabled = false);

    if(mode === 'add') {
        document.getElementById('modalTitle').innerText = "New Category";
        document.getElementById('f_is_new').value = "true";
        document.getElementById('f_id').readOnly = false;
        document.getElementById('modalFooter').style.display = 'grid';
        document.getElementById('closeDetailBtn').style.display = 'none';
    } else {
        document.getElementById('f_id').value = data.category_id;
        document.getElementById('f_id').readOnly = true;
        document.getElementById('f_name').value = data.category_name;
        document.getElementById('f_gender').value = data.category_gender;
        document.getElementById('f_desc').value = data.description;
        if(mode === 'detail') {
            document.getElementById('modalTitle').innerText = "Category Detail";
            inputs.forEach(i => i.disabled = true);
            document.getElementById('modalFooter').style.display = 'none';
            document.getElementById('closeDetailBtn').style.display = 'block';
        } else {
            document.getElementById('modalTitle').innerText = "Edit Category";
            document.getElementById('f_is_new').value = "false";
            document.getElementById('modalFooter').style.display = 'grid';
            document.getElementById('closeDetailBtn').style.display = 'none';
        }
    }
}

function closeModal() { document.getElementById('catModal').style.display = 'none'; }
function handleDelete(id) { if(confirm("Confirm delete #" + id + "?")) window.location.href="category_crud.php?delete="+id; }

document.addEventListener("DOMContentLoaded", function() {
    const vp = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    const main = document.getElementById('pxxMain');
    if(vp && main) { vp.innerHTML=''; vp.appendChild(main); main.style.display='block'; }
    refreshData(false);
});
</script>

<?php include $pxx_root . '/includes/_admin_foot.php'; ?>