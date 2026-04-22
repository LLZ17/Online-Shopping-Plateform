/* 1. admin/admin_crud.php js LIM LI ZHE part*/
// 等待DOM完全加载
document.addEventListener('DOMContentLoaded', function() {
    console.log('页面加载完成，初始化事件监听...');
    
    // 绑定刷新按钮
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', handleRefresh);
        console.log('刷新按钮已绑定');
    }
    
    // 绑定创建按钮
    const createBtn = document.getElementById('createAdminBtn');
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            openModal('add');
        });
        console.log('创建按钮已绑定');
    }
    
    // 使用事件委托绑定所有操作按钮（推荐方式）
    const container = document.querySelector('.admin-container');
    if (container) {
        container.addEventListener('click', function(event) {
            // 查找被点击的按钮
            const button = event.target.closest('.btn-op');
            if (!button) return;
            
            // 阻止事件冒泡
            event.stopPropagation();
            
            console.log('按钮被点击:', button.className);
            
            // 判断按钮类型
            if (button.id === 'refreshBtn') {
                handleRefresh();
                return;
            }
            
            if (button.id === 'createAdminBtn') {
                openModal('add');
                return;
            }
            
            // 处理 Detail, Edit, Delete 按钮
            const action = button.getAttribute('data-action');
            
            if (action === 'detail') {
                const userData = button.getAttribute('data-user');
                const addr = button.getAttribute('data-addr') || '';
                if (userData) {
                    try {
                        const user = JSON.parse(userData);
                        openModal('detail', user, addr);
                    } catch(e) {
                        console.error('解析用户数据失败:', e);
                    }
                }
            } 
            else if (action === 'edit') {
                const userData = button.getAttribute('data-user');
                if (userData) {
                    try {
                        const user = JSON.parse(userData);
                        openModal('edit', user);
                    } catch(e) {
                        console.error('解析用户数据失败:', e);
                    }
                }
            }
            else if (action === 'delete') {
                const userId = button.getAttribute('data-user-id');
                if (userId) {
                    handleDelete(userId);
                }
            }
        });
        console.log('事件委托已绑定到容器');
    } else {
        console.error('找不到 .admin-container 容器');
    }
    
    // 绑定模态框关闭按钮
    const modalClose = document.querySelector('.modal-close');
    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }
    
    // 点击模态框背景关闭
    const modalOverlay = document.getElementById('adminModal');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(event) {
            if (event.target === modalOverlay) {
                closeModal();
            }
        });
    }
});

// 刷新功能
function handleRefresh() {
    console.log('执行刷新');
    const btn = document.getElementById('refreshBtn');
    if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = "<span>⏳</span> Syncing...";
        btn.disabled = true;
        
        setTimeout(() => {
            const toast = document.getElementById('toastBox');
            if (toast) {
                toast.style.display = 'flex';
                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                window.location.reload();
            }
        }, 400);
    }
}

// 删除功能
function handleDelete(id) {
    console.log('删除用户:', id);
    if (confirm("🚨 Are you sure you want to delete this account?")) {
        window.location.href = "admin_crud.php?delete=" + id;
    }
}

// 打开模态框
function openModal(mode, data = null, addr = "") {
    console.log('打开模态框:', mode, data);
    
    const modal = document.getElementById('adminModal');
    const footer = document.getElementById('modalFooter');
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('form_action');
    const passGroup = document.getElementById('pass_group');
    const infoOnlys = document.querySelectorAll('.info-only');
    
    if (!modal) {
        console.error('找不到模态框元素');
        return;
    }
    
    modal.style.display = 'flex';
    
    // 重置表单
    const modalForm = document.getElementById('modalForm');
    if (modalForm) modalForm.reset();
    
    if (mode === 'add') {
        modalTitle.innerText = "Create Admin";
        formAction.value = "add_admin";
        if (passGroup) passGroup.style.display = 'block';
        infoOnlys.forEach(e => e.style.display = 'none');
        
        // 启用所有输入框
        document.querySelectorAll('#modalForm input, #modalForm select').forEach(e => {
            if (e.id !== 'field_id') e.disabled = false;
        });
        
        if (footer) {
            footer.innerHTML = '<button type="submit" class="btn-op" style="width:100%; justify-content:center; background:#0f172a; color:#fff;">Create Now</button>';
        }
    } 
    else {
        formAction.value = "edit_admin";
        if (passGroup) passGroup.style.display = 'none';
        infoOnlys.forEach(e => e.style.display = 'block');
        
        // 填充数据
        if (data) {
            document.getElementById('field_id').value = data.user_id || '';
            document.getElementById('field_user').value = data.username || '';
            document.getElementById('field_real').value = data.real_name || '';
            document.getElementById('field_role').value = data.role || 'Admin';
            document.getElementById('field_status').value = data.status || 'Active';
            document.getElementById('field_created').value = data.created_at || '';
        }
        
        const isDetail = mode === 'detail';
        modalTitle.innerText = isDetail ? "Admin Detail" : "Edit Admin";
        
        // 禁用/启用输入框
        document.querySelectorAll('#modalForm input, #modalForm select').forEach(e => {
            if (e.id !== 'field_id') e.disabled = isDetail;
        });
        
        if (footer) {
            if (isDetail) {
                footer.innerHTML = '<button type="button" onclick="closeModal()" class="btn-op btn-detail" style="width:100%; justify-content:center;">Close</button>';
            } else {
                footer.innerHTML = '<button type="submit" class="btn-op" style="width:100%; justify-content:center; background:#10b981; color:#fff;">Update Profile</button>';
            }
        }
    }
}

// 关闭模态框
function closeModal() {
    console.log('关闭模态框');
    const modal = document.getElementById('adminModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// 显示Toast消息
function showToast(message) {
    const toast = document.getElementById('toastBox');
    if (toast) {
        const toastSpan = toast.querySelector('span:last-child');
        if (toastSpan) toastSpan.innerText = message;
        toast.style.display = 'flex';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
}

// 导出函数供全局使用（如果需要）
window.handleRefresh = handleRefresh;
window.handleDelete = handleDelete;
window.openModal = openModal;
window.closeModal = closeModal;
window.showToast = showToast;

console.log('admin_crud.js 加载完成');
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
function toggleModal(show) { 
    const modal = document.getElementById('helpModal');
    if(modal) { modal.style.display = show ? 'block' : 'none'; }
}

function togglePassword(id, icon) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('helpModal');
    if (event.target == modal) { toggleModal(false); }
}
/*--------------------------------------------------------*/


/* 4. admin/product_crud.php js Tee De Yong part*/
/* --- TEE DE YONG: Product CRUD Logic --- */
function triggerSyncRefresh() {
    alert("✅ Admin records synchronized and refreshed!");
    window.location.href = 'product_crud.php';
}

(function() {
    const templateId = 'final_product_layout';
    function performInjection() {
        const template = document.getElementById(templateId);
        if (!template) return;
        const viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
        if (viewport && !viewport.querySelector('.inventory-container')) {
            viewport.innerHTML = ''; 
            viewport.appendChild(template.content.cloneNode(true));
        }
    }
    setInterval(performInjection, 300);
    window.addEventListener('load', performInjection);
    performInjection();
})();

/* 5. admin/product_detail.php js Tee De Yong part*/
/* --- Product Detail JS Start (TEE DE YONG's Part) --- */
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
/* --- Product Detail JS End --- */



/* 5. admin/product_edit.php js Tee De Yong part*/
/* --- Product Edit JS Start (TEE DE YONG's Part) --- */
/* --- Admin Product Edit JS Start (TEE DE_YONG's Part) --- */
// Tag Logic
function setupTags(boxId, inputId) {
    const box = document.getElementById(boxId);
    const input = document.getElementById(inputId);
    if(!box) return;
    box.addEventListener('click', function(e) {
        if (e.target.classList.contains('tag-item')) {
            e.target.classList.toggle('active');
            let vals = [];
            box.querySelectorAll('.tag-item.active').forEach(t => vals.push(t.dataset.value));
            input.value = vals.join(',');
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    // 初始化 Tags
    setupTags('size-tags', 'hidden-size');

    // Custom Select Logic
    const wrapper = document.getElementById('type-select-wrapper');
    if (wrapper) {
        const hiddenType = document.getElementById('hidden-product-type');
        const triggerText = wrapper.querySelector('.trigger-text');
        
        if(hiddenType && hiddenType.value) {
            const opt = wrapper.querySelector(`.custom-option[data-value="${hiddenType.value}"]`);
            if(opt) {
                triggerText.innerText = opt.innerText;
                opt.classList.add('selected');
            }
        }

        wrapper.addEventListener('click', function(e) {
            const opt = e.target.closest('.custom-option');
            if (opt) {
                hiddenType.value = opt.dataset.value;
                triggerText.innerText = opt.innerText;
                wrapper.querySelectorAll('.custom-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                this.classList.remove('open');
            } else {
                this.classList.toggle('open');
            }
        });
    }

    // Image Preview
    const photoInput = document.getElementById('photo-input');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.getElementById('image-preview');
                img.src = event.target.result;
                img.style.display = 'block';
                const placeholder = document.getElementById('no-photo-placeholder');
                if(placeholder) placeholder.style.display = 'none';
            }
            if(this.files[0]) reader.readAsDataURL(this.files[0]);
        });
    }

    // Viewport Loader (For Edit Page)
    const myEditContent = document.getElementById('edit-wrapper');
    if (myEditContent) {
        myEditContent.style.display = 'block';
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
            viewport.appendChild(myEditContent);
        }
    }
});
/* --- Admin Product Edit JS End --- */



/* 6. admin/product_add.php js Tee De Yong part*/
/* --- Add Product JS Start (TEE DE YONG's Part) --- */
document.addEventListener("DOMContentLoaded", function() {
    // 注入逻辑
    const addWrapper = document.getElementById('add-wrapper');
    if (addWrapper) {
        addWrapper.style.display = 'block';
        const viewport = document.querySelector('.admin-content-viewport, .main-content, main');
        if (viewport) {
            viewport.innerHTML = ''; 
            viewport.appendChild(addWrapper);
        }
    }

    // 图片预览
    const photoInput = document.getElementById('photo-input');
    const container = document.getElementById('preview-container');
    if (photoInput && container) {
        container.onclick = () => photoInput.click();
        photoInput.onchange = (e) => {
            const files = e.target.files;
            container.querySelectorAll('.current-photo').forEach(img => img.remove());
            if (files.length > 0) {
                document.getElementById('no-photo-placeholder').style.display = 'none';
                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        const img = document.createElement('img');
                        img.src = ev.target.result;
                        img.className = 'current-photo';
                        container.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        };
    }
});


/* 7. admin/member_crud.php js Tee De Yong part*/
/* --- Member Management JS Start (TEE DE YONG's Part) --- */
/* --- Member Management JS Start (TEE DE YONG's Part) --- */
/* --- Member Management JS Start (TEE DE YONG's Part) --- */
function triggerSyncRefresh() {
    alert("✅ Admin records synchronized and refreshed!");
    window.location.href = "member_crud.php"; 
}

document.addEventListener("DOMContentLoaded", function() {
    let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    let container = document.querySelector('.member-container');
    if (viewport && container && viewport !== container.parentNode) {
        viewport.innerHTML = '';
        viewport.appendChild(container);
    }
});
/* --- Member Management JS End --- */

/* 7. admin/member_detail.php js Tee De Yong part*/
/* --- Member Profile Detail JS Start (TEE DE YONG's Part) --- */
function initMemberDetailInjection() {
    const myContent = document.getElementById('detail-wrapper');
    if (!myContent) return;
    myContent.style.display = 'block';
    
    let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
    if (!viewport) {
        const allElements = document.body.getElementsByTagName('*');
        for (let el of allElements) {
            if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                viewport = el.parentElement;
                break;
            }
        }
    }
    
    if (viewport && viewport !== myContent.parentNode) {
        viewport.innerHTML = ''; 
        viewport.appendChild(myContent);
    } else if (!viewport) {
        // 保底逻辑
        myContent.style.position = 'fixed';
        myContent.style.top = '60px'; 
        myContent.style.right = '0';
        myContent.style.width = 'calc(100vw - 250px)'; 
        myContent.style.height = 'calc(100vh - 60px)';
        myContent.style.background = '#f1f5f9';
        myContent.style.zIndex = '9999';
        if (myContent.parentNode !== document.body) {
            document.body.appendChild(myContent);
        }
    }
}

// 统一监听执行
document.addEventListener("DOMContentLoaded", initMemberDetailInjection);
setInterval(initMemberDetailInjection, 500); 
/* --- Member Profile Detail JS End --- */


/* 8. admin/member_add.php js Tee De Yong part*/
/* --- Member Registration JS Start (TEE DE YONG's Part) --- */
document.addEventListener("DOMContentLoaded", function() {
    const addMemberWrapper = document.getElementById('add-wrapper');
    if (!addMemberWrapper) return;
    addMemberWrapper.style.display = 'block';

    let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
    
    if (!viewport) {
        const allElements = document.body.getElementsByTagName('*');
        for (let el of allElements) {
            if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                viewport = el.parentElement;
                break;
            }
        }
    }

    if (viewport) {
        viewport.innerHTML = ''; 
        viewport.appendChild(addMemberWrapper);
    } else {
        addMemberWrapper.style.position = 'fixed';
        addMemberWrapper.style.top = '60px'; 
        addMemberWrapper.style.right = '0';
        addMemberWrapper.style.width = 'calc(100vw - 250px)'; 
        addMemberWrapper.style.height = 'calc(100vh - 60px)';
        addMemberWrapper.style.background = '#f8fafc';
        addMemberWrapper.style.zIndex = '9999';
        document.body.appendChild(addMemberWrapper);
    }
});
/* --- Member Registration JS End --- */



/* 10. admin/member_edit.php js Tee De Yong part*/
/* --- Member Edit Profile JS Start (TEE DE YONG's Part) --- */
function initMemberEditInjection() {
    const editWrapper = document.getElementById('edit-wrapper');
    if (!editWrapper) return;
    editWrapper.style.display = 'block';

    // 图片上传实时预览
    const photoInput = document.getElementById('photo-input');
    const imagePreview = document.getElementById('image-preview');
    if (photoInput && imagePreview) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // 视口注入保护代码
    let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, .page-content, main');
    if (!viewport) {
        const allElements = document.body.getElementsByTagName('*');
        for (let el of allElements) {
            if (el.textContent && el.textContent.includes("Administrative Workspace Ready")) {
                viewport = el.parentElement;
                break;
            }
        }
    }

    if (viewport && viewport !== editWrapper.parentNode) {
        viewport.innerHTML = ''; 
        viewport.appendChild(editWrapper);
    } else if (!viewport) {
        editWrapper.style.position = 'fixed';
        editWrapper.style.top = '60px'; 
        editWrapper.style.right = '0';
        editWrapper.style.width = 'calc(100vw - 250px)'; 
        editWrapper.style.height = 'calc(100vh - 60px)';
        editWrapper.style.background = '#f8fafc';
        editWrapper.style.zIndex = '9999';
        if (editWrapper.parentNode !== document.body) {
            document.body.appendChild(editWrapper);
        }
    }
}

// 统一监听执行
document.addEventListener("DOMContentLoaded", initMemberEditInjection);
setInterval(initMemberEditInjection, 500); 
/* --- Member Edit Profile JS End --- */


/* 11. admin/order_crud.php (Tee De Yong) CONFLICT CHECKED*/
/* --- Sales Analytics JS Start (TEE DE YONG's Part) --- */
function initSalesAnalytics() {
    const salesApp = document.getElementById('sales-app');
    if (!salesApp) return;
    
    salesApp.style.display = 'block';
    
    let viewport = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    if (viewport && viewport !== salesApp.parentNode) {
        viewport.innerHTML = '';
        viewport.appendChild(salesApp);
    }
}

// 统一监听执行
window.addEventListener('load', initSalesAnalytics);
setInterval(initSalesAnalytics, 500); 
/* --- Sales Analytics JS End --- */


/* 12. admin/order_history.php (Tee De Yong) CONFLICT CHECKED*/
/* --- Order History JS Start (TEE DE YONG's Part) --- */

// 💡 修复：补回你消失的刷新弹窗函数
function triggerSyncRefresh() {
    alert("✅ Admin records synchronized and refreshed!");
    window.location.href = "order_history.php"; 
}

(function() {
    function runOrderHistoryInjection() {
        const template = document.getElementById('order_premium_layout');
        if (!template) return;

        const viewport = document.querySelector('.admin-content-viewport') || 
                         document.querySelector('.main-content') || 
                         document.querySelector('main');
        
        if (viewport && !viewport.querySelector('.order-container')) {
            viewport.innerHTML = ''; 
            viewport.appendChild(template.content.cloneNode(true));
        }
    }

    const observer = new MutationObserver(runOrderHistoryInjection);
    observer.observe(document.body, { childList: true, subtree: true });
    window.addEventListener('load', runOrderHistoryInjection);
    setInterval(runOrderHistoryInjection, 1000);
})();
/* --- Order History JS End --- */

/* 3. member_cart/cart.php (WONG YONG FENG) */
document.addEventListener('DOMContentLoaded', function() {
    // Update Cart 按钮验证
    const updateBtn = document.getElementById('cartUpdateBtn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function(e) {
            let isValid = true;
            document.querySelectorAll('.cart-qty-input').forEach(function(input) {
                let val = parseInt(input.value);
                if (isNaN(val) || val < 1) {
                    alert('Quantity must be at least 1');
                    input.value = 1;
                    isValid = false;
                }
            });
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Remove 链接确认框
    document.querySelectorAll('.cart-remove-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Remove this item?')) {
                e.preventDefault();
            }
        });
    });
});
/* member/checkout.php (WONG YONG FENG) */
$(document).ready(function() {
    var subtotal = typeof memberSubtotal !== 'undefined' ? memberSubtotal : 0;
    var maxPoints = typeof memberMaxPoints !== 'undefined' ? memberMaxPoints : 0;
    var currentVoucherDiscount = typeof memberCurrentVoucherDiscount !== 'undefined' ? memberCurrentVoucherDiscount : 0;
    
    function updateMemberTotals() {
        var points = parseInt($('#memberPointsInput').val()) || 0;
        if (points > maxPoints) { 
            points = maxPoints; 
            $('#memberPointsInput').val(points); 
        }
        if (points < 0) { 
            points = 0; 
            $('#memberPointsInput').val(0); 
        }
        
        // 确保 points 是 100 的倍数
        points = Math.floor(points / 100) * 100;
        $('#memberPointsInput').val(points);
        
        var pDiscount = points / 100;
        var afterDiscount = subtotal - pDiscount - currentVoucherDiscount;
        if (afterDiscount < 0) afterDiscount = 0;
        
        var shipping = (afterDiscount < 100 && afterDiscount > 0) ? 8.00 : 0;
        var total = afterDiscount + shipping;
        
        $('#memberDiscountAmount').text('RM ' + pDiscount.toFixed(2));
        $('#memberVoucherDiscountDisplay').text('RM ' + currentVoucherDiscount.toFixed(2));
        $('#memberShippingAmount').text(shipping > 0 ? 'RM ' + shipping.toFixed(2) : 'FREE');
        $('#memberTotalAmount').text('RM ' + total.toFixed(2));
        
        if (afterDiscount >= 100) {
            $('#memberShippingNote').html('✅ Congratulations! You qualify for <strong>FREE shipping</strong>');
        } else {
            var need = (100 - afterDiscount).toFixed(2);
            $('#memberShippingNote').html('🚚 Add RM ' + need + ' more to get FREE shipping');
        }
    }

    
    $('#memberApplyVoucherBtn').on('click', function() {
        var code = $('#memberVoucherInput').val();
        if (!code) { 
            alert('Please enter a voucher code.'); 
            return; 
        }

        $.ajax({
            url: 'checkout.php',
            type: 'GET',
            data: { action: 'validate_voucher', code: code, subtotal: subtotal },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    currentVoucherDiscount = parseFloat(res.discount_value);
                    $('#memberVoucherMsg').css('color', '#4caf50').text('✅ Voucher applied: -RM ' + currentVoucherDiscount.toFixed(2));
                } else {
                    currentVoucherDiscount = 0;
                    $('#memberVoucherMsg').css('color', '#e60012').text('❌ ' + res.message);
                }
                updateMemberTotals();
            },
            error: function() {
                alert('Voucher code error.');
            }
        });
    });
    
    $('#memberPointsInput').on('input change', function() { 
        updateMemberTotals(); 
    });
    
    updateMemberTotals();
});
/* 5. memberProducts/listing.php (WONG YONG FENG) */
$(document).ready(function() {
    
    $('#memberCategorySelect').on('change', function() {
        $('#memberSearchForm').submit();
    });
    
    // 添加到购物车
    $('.member-add-to-cart').click(function() {
        var productId = $(this).data('id');
        var $btn = $(this);
        $.ajax({
            url: '../member_cart/add.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (confirm('✓ Added to cart! Go to cart now?')) {
                        window.location.href = '../member_cart/unified_cart.php';
                    } else {
                        $btn.text('Added!');
                        setTimeout(function() { $btn.text('Add to Cart'); }, 1500);
                    }
                } else {
                    alert(response.error);
                    if (response.error === 'Please login first') {
                        window.location.href = '../login.php';
                    }
                }
            },
            error: function() {
                alert('Error adding to cart');
            }
        });
    });
});
/* 6. member/order_detail.php (WONG YONG FENG) */
$(document).ready(function() {
    // 取消订单按钮确认框
    $('#orderDetailCancelBtn').on('click', function(e) {
        if (!confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});
/* memberProducts/checkout.php (WONG YONG FENG) -*/
$(document).ready(function() {
    var subtotal = typeof productSubtotal !== 'undefined' ? productSubtotal : 0;
    var maxPoints = typeof productMaxPoints !== 'undefined' ? productMaxPoints : 0;
    var currentVoucherDiscount = typeof productCurrentVoucherDiscount !== 'undefined' ? productCurrentVoucherDiscount : 0;
    
    function updateProductTotals() {
        var points = parseInt($('#productPointsInput').val()) || 0;
        if (points > maxPoints) { 
            points = maxPoints; 
            $('#productPointsInput').val(points); 
        }
        if (points < 0) { 
            points = 0; 
            $('#productPointsInput').val(0); 
        }
        
        
        var pDiscount = points / 100;
        var afterDiscount = subtotal - pDiscount - currentVoucherDiscount;
        if (afterDiscount < 0) afterDiscount = 0;
        
        var shipping = (afterDiscount < 100 && afterDiscount > 0) ? 8.00 : 0;
        var total = afterDiscount + shipping;
        
        $('#productDiscountAmount').text('RM ' + pDiscount.toFixed(2));
        $('#productVoucherDiscountDisplay').text('RM ' + currentVoucherDiscount.toFixed(2));
        $('#productShippingAmount').text(shipping > 0 ? 'RM ' + shipping.toFixed(2) : 'FREE');
        $('#productTotalAmount').text('RM ' + total.toFixed(2));
        
        if (afterDiscount >= 100) {
            $('#productShippingNote').html('✅ Congratulations! You qualify for <strong>FREE shipping</strong>');
        } else {
            var need = (100 - afterDiscount).toFixed(2);
            $('#productShippingNote').html('🚚 Add RM ' + need + ' more to get FREE shipping');
        }
    }
    
    $('#productApplyVoucherBtn').on('click', function() {
        var code = $('#productVoucherInput').val();
        if (!code) { 
            alert('Please enter a voucher code.'); 
            return; 
        }

        $.ajax({
            url: 'checkout.php',
            type: 'GET',
            data: { action: 'validate_voucher', code: code, subtotal: subtotal },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    currentVoucherDiscount = parseFloat(res.discount_value);
                    $('#productVoucherMsg').css('color', '#4caf50').text('✅ Voucher applied: -RM ' + currentVoucherDiscount.toFixed(2));
                } else {
                    currentVoucherDiscount = 0;
                    $('#productVoucherMsg').css('color', '#e60012').text('❌ ' + res.message);
                }
                updateProductTotals();
            },
            error: function() {
                alert('Voucher code error.');
            }
        });
    });
    
    $('#productPointsInput').on('input change', function() { 
        updateProductTotals(); 
    });
    
    updateProductTotals();
});


/* 14. memberProducts/detail.php (WONG YONG FENG) */
$(document).ready(function() {
    // 返回按钮
    $('#productDetailBackBtn').on('click', function(e) {
        e.preventDefault();
        window.history.back();
    });
    
    // 颜色选择
    $('.colour-btn').on('click', function() {
        $('.colour-btn').removeClass('active');
        $(this).addClass('active');
        $('#productDetailSelectedColour').val($(this).data('value'));
    });
    
    // 尺码选择
    $('.size-btn').on('click', function() {
        $('.size-btn').removeClass('active');
        $(this).addClass('active');
        $('#productDetailSelectedSize').val($(this).data('value'));
    });
    
    // 数量减少
    $('#productDetailQtyMinus').on('click', function() {
        var input = $('#productDetailQuantity');
        var current = parseInt(input.val()) || 1;
        var min = 1;
        if (current > min) {
            input.val(current - 1);
        }
    });
    
    // 数量增加
    $('#productDetailQtyPlus').on('click', function() {
        var input = $('#productDetailQuantity');
        var current = parseInt(input.val()) || 1;
        var max = productStock;
        if (current < max) {
            input.val(current + 1);
        }
    });
    
    // 数量输入框变化
    $('#productDetailQuantity').on('change', function() {
        var val = parseInt($(this).val()) || 1;
        var min = 1;
        var max = productStock;
        if (val < min) val = min;
        if (val > max) val = max;
        $(this).val(val);
    });
    
    // 评分星星
    $('.product-detail-rating-star').on('click', function() {
        var rating = $(this).data('rating');
        $('#productDetailRatingValue').val(rating);
        $('.product-detail-rating-star').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).html('★').addClass('selected');
            } else {
                $(this).html('☆').removeClass('selected');
            }
        });
    });
    
    // 加购按钮
    $('#productDetailAddToCartBtn').on('click', function() {
        var qty = $('#productDetailQuantity').val();
        var size = $('#productDetailSelectedSize').val();
        var colour = $('#productDetailSelectedColour').val();
        
        if (hasSizes && size === '') {
            alert('Please select a size');
            return;
        }
        
        $.ajax({
            url: 'add.php',
            method: 'POST',
            data: { 
                product_id: productId, 
                quantity: qty, 
                selected_size: size,
                colour: colour
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (confirm('✓ Added to cart! Go to cart now?')) {
                        window.location.href = 'unified_cart.php';
                    }
                } else {
                    alert(response.error);
                    if (response.error === 'Please login first') {
                        window.location.href = '../login.php';
                    }
                }
            },
            error: function() {
                alert('Error adding to cart');
            }
        });
    });
});
/* 15. memberProducts/listing.php (WONG YONG FENG) */
$(document).ready(function() {
    $('.products-listing-add-to-cart').click(function() {
        var productId = $(this).data('id');
        var $btn = $(this);
        $.ajax({
            url: 'add.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (confirm('✓ Added to cart! Go to cart now?')) {
                        window.location.href = 'unified_cart.php';
                    } else {
                        $btn.text('Added!');
                        setTimeout(function() { $btn.text('Add to Cart'); }, 1500);
                    }
                } else {
                    alert(response.error);
                    if (response.error === 'Please login first') {
                        window.location.href = '../login.php';
                    }
                }
            },
            error: function() {
                alert('Error adding to cart');
            }
        });
    });
});
/* 19. member_cart/unified_cart.php (WONG YONG FENG) - 修复版 */
$(document).ready(function() {
    // Update Cart 按钮验证
    $('#unifiedCartUpdateBtn').on('click', function(e) {
        var isValid = true;
        $('.unified-cart-qty-input').each(function() {
            var val = parseInt($(this).val());
            if (isNaN(val) || val < 1) {
                alert('Quantity must be at least 1');
                $(this).val(1);
                isValid = false;
                return false;
            }
        });
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Remove 按钮 - 使用 POST 表单提交
    $('.unified-cart-remove-btn').on('click', function(e) {
        e.preventDefault();
        var cartId = $(this).data('id');
        if (confirm('Are you sure you want to remove this item?')) {
            $('#removeCartId').val(cartId);
            $('#removeForm').submit();
        }
    });
    
    // 数量输入框验证
    $('.unified-cart-qty-input').on('change', function() {
        var val = parseInt($(this).val());
        if (isNaN(val) || val < 1) {
            $(this).val(1);
        }
    });
});
/* 21. index.php (WONG YONG FENG) - 轮播图 */
$(document).ready(function() {
    // ========== 轮播图逻辑 ==========
    let currentSlide = 0;
    const slides = $('.slide');
    const dots = $('.dot');
    const totalSlides = slides.length;
    let autoSlideInterval;
    
    function showSlide(index) {
        if (index < 0) index = totalSlides - 1;
        if (index >= totalSlides) index = 0;
        
        slides.removeClass('active');
        dots.removeClass('active');
        
        $(slides[index]).addClass('active');
        $(dots[index]).addClass('active');
        
        currentSlide = index;
    }
    
    function nextSlide() {
        showSlide(currentSlide + 1);
    }
    
    function prevSlide() {
        showSlide(currentSlide - 1);
    }
    
    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, 5000);
    }
    
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }
    
    // 绑定按钮事件
    $('#sliderPrev').on('click', function() {
        stopAutoSlide();
        prevSlide();
        startAutoSlide();
    });
    
    $('#sliderNext').on('click', function() {
        stopAutoSlide();
        nextSlide();
        startAutoSlide();
    });
    
    // 绑定圆点事件
    $('.dot').on('click', function() {
        stopAutoSlide();
        const index = $(this).data('index');
        showSlide(index);
        startAutoSlide();
    });
    
    // 鼠标悬停时暂停轮播
    $('.hero-slider-container').hover(
        function() { stopAutoSlide(); },
        function() { startAutoSlide(); }
    );
    
    // 启动自动轮播
    startAutoSlide();
    
    // ========== 加购按钮事件 ==========
    $('.add-to-cart-btn').click(function() {
        var productId = $(this).data('id');
        addToCart(productId);
    });
    
    // ========== 心愿单按钮事件 ==========
    $('.quick-wishlist').click(function(e) {
        var productId = $(this).data('id');
        var $btn = $(this);
        var $card = $btn.closest('.product-card');
        
        $.ajax({
            url: 'member/wishlist_toggle.php?id=' + productId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'not_logged_in') {
                    window.location.href = 'login_selection.php';
                } else if (response.status === 'added') {
                    $btn.addClass('active');
                    $card.addClass('is-fav-module');
                    $btn.html('♥ Wishlisted');
                } else if (response.status === 'removed') {
                    $btn.removeClass('active');
                    $card.removeClass('is-fav-module');
                    $btn.html('♡ Wishlist');
                }
            },
            error: function() {
                alert('Error toggling wishlist');
            }
        });
    });
});

function addToCart(productId) {
    $.ajax({
        url: 'memberProducts/add.php',
        method: 'POST',
        data: { product_id: productId, quantity: 1 },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (confirm('✓ Added to cart! Go to cart now?')) {
                    window.location.href = 'memberProducts/unified_cart.php';
                }
            } else {
                alert(response.error);
                if (response.error === 'Please login first') {
                    window.location.href = 'login.php';
                }
            }
        },
        error: function() {
            alert('Error adding to cart');
        }
    });
}
/* 24. ViewAllProduct.php (WONG YONG FENG) */
$(document).ready(function() {
    $('.viewall-add-to-cart').click(function() {
        var productId = $(this).data('id');
        var $btn = $(this);
        $.ajax({
            url: 'memberProducts/add.php',
            method: 'POST',
            data: { product_id: productId, quantity: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (confirm('✓ Added to cart! Go to cart now?')) {
                        window.location.href = 'memberProducts/unified_cart.php';
                    } else {
                        $btn.text('Added!');
                        setTimeout(function() { $btn.text('Add to Cart'); }, 1500);
                    }
                } else {
                    alert(response.error);
                    if (response.error === 'Please login first') {
                        window.location.href = 'login.php';
                    }
                }
            },
            error: function() {
                alert('Error adding to cart');
            }
        });
    });
});

/* 25. includes/_admin_head.php (LIM LI ZHE part) */
document.addEventListener('DOMContentLoaded', () => {
    const currentPath = window.location.href;
    document.querySelectorAll('.sub-nav-link').forEach(link => {
        if (currentPath.includes(link.getAttribute('href'))) {
            const box = link.closest('.s-sub-box');
            if(box) {
                box.classList.add('open');
                box.previousElementSibling.classList.add('active');
            }
        }
    });
});
/*----------------------------------------------*/
