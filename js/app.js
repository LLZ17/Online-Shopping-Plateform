/* 1. admin/admin_crud.php js LIM LI ZHE part*/
function handleRefresh() {
    alert("✅ Admin records synchronized and refreshed!");
    window.location.reload();
}

function handleDelete(id) {
    if(confirm("🚨 CRITICAL WARNING: Are you sure you want to permanently delete Admin #" + id + "?\nThis action cannot be undone!")) {
        window.location.href = "admin_crud.php?delete=" + id;
    }
}

function openModal(mode, data, fullAddr = "") {
    const modal = document.getElementById('adminModal');
    const footer = document.getElementById('modalFooter');
    modal.style.display = 'flex';
    
    document.getElementById('field_id').value = data.user_id;
    document.getElementById('field_user').value = data.username;
    document.getElementById('field_real').value = data.real_name || '';
    document.getElementById('field_role').value = data.role;
    document.getElementById('field_status').value = data.status;
    document.getElementById('field_created').value = data.created_at || 'N/A';
    document.getElementById('field_login').value = data.last_login || 'No Session Found';
    document.getElementById('field_addr').value = fullAddr;

    const inputs = document.querySelectorAll('#modalForm input, #modalForm select');
    
    if(mode === 'detail') {
        document.getElementById('modalTitle').innerText = "Admin Detail Profile";
        inputs.forEach(i => i.disabled = true);
        footer.innerHTML = '<button type="button" class="btn-save" style="background:#64748b;" onclick="closeModal()">Close Window</button>';
    } else {
        document.getElementById('modalTitle').innerText = "Modify Administrator Data";
        inputs.forEach(i => { if(i.id !== 'field_created' && i.id !== 'field_login') i.disabled = false; });
        footer.innerHTML = '<button type="submit" class="btn-save">Commit Changes</button><button type="button" onclick="closeModal()" style="background:none; color:#94a3b8; border:none; cursor:pointer; font-weight:700;">Discard Changes</button>';
    }
}

function closeModal() { document.getElementById('adminModal').style.display = 'none'; }

document.addEventListener("DOMContentLoaded", function() {
    let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    let container = document.querySelector('.admin-container');
    if (viewport && container && viewport !== container.parentNode) {
        viewport.innerHTML = '';
        viewport.appendChild(container);
    }
});
/* ------------------------------------------------*/

/* 2. admin/category_crud.php js LIM LI ZHE part*/
/**
 * PXX Mall - Category CRUD Interaction Logic
 */

// 处理删除确认
function handleDelete(id) {
    if(confirm("🚨 Delete Category #" + id + "?")) {
        window.location.href = "category_crud.php?delete=" + id;
    }
}

// 打开 Modal 逻辑
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
        document.getElementById('saveBtnText').innerText = "Save Category";
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
            document.getElementById('modalTitle').innerText = "Edit Category";
            document.getElementById('detailExtra').style.display = 'none';
            document.getElementById('modalFooter').style.display = 'block';
            document.getElementById('saveBtnText').innerText = "Update Category";
            inputs.forEach(i => i.disabled = false);
        }
    }
}

// 关闭 Modal
function closeModal() { 
    document.getElementById('catModal').style.display = 'none'; 
}

// 强制嵌入 Viewport 逻辑 (保持你的代码逻辑不变)
document.addEventListener("DOMContentLoaded", function() {
    let vp = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    let ct = document.getElementById('pxxMain');
    if (vp && ct && vp !== ct.parentNode) {
        vp.innerHTML = ''; 
        vp.appendChild(ct);
    }
});
/* ---------------------------------------------*/

/* 3. admin/login.php js LIM LI ZHE part*/
//

/* 4. admin/logout.php js LIM LI ZHE part*/





/* 5. admin/product_crud.php js Tee De Yong part*/
function triggerSyncRefresh() {
    alert("✅ Admin records synchronized and refreshed!");
    window.location.href = 'product_crud.php';
}

(function() {
    const templateId = 'final_product_layout';
    
    function performInjection() {
        const template = document.getElementById(templateId);
        if (!template) return;

        const viewport = document.querySelector('.admin-content-viewport') || 
                         document.querySelector('.main-content') || 
                         document.querySelector('#layout-content') ||
                         document.querySelector('.content-wrapper') ||
                         document.querySelector('main');

        if (viewport) {
            if (!viewport.querySelector('.inventory-container')) {
                viewport.innerHTML = ''; 
                viewport.appendChild(template.content.cloneNode(true));
            }
        }
    }

    const heartBeat = setInterval(performInjection, 300); // 稍微调快一点点，防止空白时间过长
    
    const observer = new MutationObserver((mutations) => { 
        performInjection(); 
    });

    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });

    window.addEventListener('load', performInjection);
    performInjection();
})();





