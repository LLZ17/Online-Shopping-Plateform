/* admin/admin_crud.php jc LIM LI ZHE part*/

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
        inputs.forEach(i => { 
            if(i.id !== 'field_created' && i.id !== 'field_login') i.disabled = false; 
        });
        footer.innerHTML = `
            <button type="submit" class="btn-save">Commit Changes</button>
            <button type="button" onclick="closeModal()" style="background:none; color:#94a3b8; border:none; cursor:pointer; font-weight:700;">Discard Changes</button>
        `;
    }
}

function closeModal() { 
    document.getElementById('adminModal').style.display = 'none'; 
}

/**
 * Viewport injection logic to ensure consistency with Member CRUD
 */
document.addEventListener("DOMContentLoaded", function() {
    let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    let container = document.querySelector('.admin-container');
    if (viewport && container && viewport !== container.parentNode) {
        viewport.innerHTML = '';
        viewport.appendChild(container);
    }
});
/* ------------------------------------------------*/

/* admin/admin_crud.php css  xxx part*/
