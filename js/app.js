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
/* --- Product Edit Form Interaction --- */
document.addEventListener("DOMContentLoaded", function() {
    // 1. 初始化显示
    const wrapper = document.getElementById('edit-wrapper');
    const vp = document.querySelector('.admin-content-viewport, .main-content, .content-wrapper, main');
    if (vp && wrapper) {
        vp.innerHTML = '';
        vp.appendChild(wrapper);
        wrapper.style.display = 'block';
    }

    // 2. 自定义 Select 交互
    const trigger = document.querySelector('.custom-select-trigger');
    const optionsBox = document.querySelector('.custom-options');
    const triggerText = document.querySelector('.trigger-text');
    const hiddenType = document.getElementById('hidden-product-type');

    if (trigger && optionsBox) {
        trigger.onclick = () => optionsBox.classList.toggle('open');
        
        document.querySelectorAll('.custom-option').forEach(opt => {
            opt.onclick = function() {
                const val = this.dataset.value;
                const text = this.innerText;
                
                triggerText.innerText = text;
                hiddenType.value = val;
                
                document.querySelectorAll('.custom-option').forEach(o => o.classList.remove('active'));
                this.classList.add('active');
                optionsBox.classList.remove('open');
            };
        });
    }

    // 3. Size Tags 交互
    const sizeInput = document.getElementById('hidden-size');
    document.querySelectorAll('#size-tags .tag-item').forEach(tag => {
        tag.onclick = function() {
            this.classList.toggle('active');
            const activeTags = Array.from(document.querySelectorAll('#size-tags .tag-item.active'))
                                   .map(t => t.dataset.value);
            sizeInput.value = activeTags.join(', ');
        };
    });

    // 4. 图片预览
    const photoInput = document.getElementById('photo-input');
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('no-photo-placeholder');
    if (photoInput) {
        photoInput.onchange = function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'inline-block';
                    if (placeholder) placeholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        };
    }

    // 点击外部关闭下拉框
    window.onclick = e => {
        if (!e.target.closest('.custom-select-wrapper')) {
            optionsBox?.classList.remove('open');
        }
    };
});



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
// js/app.js - Checkout & Member Product Functions
// Requires: jQuery, and window.appConfig set in PHP page

(function($) {
    'use strict';

    // Read PHP-injected config (define this in your PHP before including this file)
    const cfg = window.appConfig || {};
    const subtotal = parseFloat(cfg.subtotal) || 0;
    const ptDisc = parseFloat(cfg.ptDisc) || 0;
    let vDisc = 0;

    // ==================== CHECKOUT VOUCHER FUNCTIONS ====================

    function updateTotals() {
        let afterDisc = Math.max(0, subtotal - ptDisc - vDisc);
        let shipping = (afterDisc >= 100 || afterDisc <= 0) ? 0 : 8.00;
        let total = afterDisc + shipping;

        $('#v_amt_ui').text(vDisc > 0 ? '- RM ' + vDisc.toFixed(2) : 'RM 0.00');
        $('#ship_ui').text(shipping === 0 ? 'FREE' : 'RM ' + shipping.toFixed(2));
        $('#total_ui').text('RM ' + total.toFixed(2));
        $('#h_v_amt').val(vDisc.toFixed(2));
        $('#h_final').val(total.toFixed(2));
    }

    function switchTab(idx) {
        $('.v-tab-btn').removeClass('active');
        $('.v-tab-btn').eq(idx).addClass('active');
        $('.v-content').removeClass('active');
        $('#tab_' + idx).addClass('active');
        vDisc = 0;
        $('#h_v_id').val('');
        updateTotals();
    }

    function applyManualCode() {
        let code = $('#v_code_input').val().trim();
        if (!code) {
            alert('Please enter a voucher code');
            return;
        }

        $.post(cfg.ajaxUrl || 'checkout.php', {
            ajax: 'check_v',
            code: code,
            subtotal: subtotal
        })
        .done(function(data) {
            let res;
            try {
                res = (typeof data === 'object') ? data : JSON.parse(data);
            } catch (e) {
                alert('Server error. Invalid response.');
                console.error('JSON parse error:', data);
                return;
            }

            if (res.success) {
                vDisc = (res.method === '-') 
                    ? parseFloat(res.val) 
                    : subtotal * (parseFloat(res.val) / 100);
                $('#h_v_id').val(res.id);
                updateTotals();
            } else {
                alert(res.message || 'Invalid voucher code');
            }
        })
        .fail(function() {
            alert('Network error. Please try again.');
        });
    }

    function applySelectCode() {
        let opt = $('#v_select_box option:selected');
        if (String(opt.val()) === '0') {
            vDisc = 0;
        } else {
            let val = parseFloat(opt.data('val')) || 0;
            let method = opt.data('method');
            vDisc = (method === '-') ? val : subtotal * (val / 100);
            $('#h_v_id').val(opt.val());
        }
        updateTotals();
    }

    // ==================== MEMBER PRODUCT FUNCTIONS ====================

    function initMemberProducts() {
        $('#memberCategorySelect').on('change', function() {
            $('#memberSearchForm').submit();
        });

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
                            setTimeout(function() {
                                $btn.text('Add to Cart');
                            }, 1500);
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
    }

    // ==================== INITIALIZATION ====================

    $(document).ready(function() {
        updateTotals();
        initMemberProducts();
    });

    // Expose functions to global scope for inline onclick handlers
    window.switchTab = switchTab;
    window.applyManualCode = applyManualCode;
    window.applySelectCode = applySelectCode;

})(jQuery);
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
document.addEventListener('DOMContentLoaded', function() {
    // 处理图片加载失败
    const images = document.querySelectorAll('.preview-item img, .user-avatar');
    images.forEach(img => {
        img.addEventListener('error', function() {
            if (!this.src.includes('default.jpg')) {
                this.src = this.dataset.fallback || '../images/default.jpg';
            }
        });
    });

    // 搜索框简单验证
    const searchForm = document.querySelector('.search-container');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const input = this.querySelector('.search-input');
            if (!input.value.trim()) {
                // 如果搜索内容为空，可以在这里阻止提交
                // e.preventDefault();
            }
        });
    }
});
/*----------------------------------------------*/

/* 26. memeber/edit_bank.php (LIM LI ZHE part) */
// 使用 URLSearchParams 判断是否需要弹出提示
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('status') === 'success') {
    alert('✅ Bank preference updated successfully!');
    // 清理 URL 里的参数（可选，防止刷新页面重复弹出）
    window.history.replaceState({}, document.title, window.location.pathname + "?page=bank");
}
/*----------------------------------------------*/


/* 27. memeber/edit_email.php (LEONG HONG JUN part) */
$(document).ready(function() {
    // 获取 URL 中的参数
    const urlParams = new URLSearchParams(window.location.search);
    
    // 检查是否有 status=updated
    if (urlParams.get('status') === 'updated') {
        alert('✅ Email has been updated. Please verify your new email.');
        
        // 可选：为了美观，弹出后清除 URL 中的参数，防止刷新页面再次弹出
        const newUrl = window.location.pathname + '?page=security';
        window.history.replaceState({}, document.title, newUrl);
    }
});
/*----------------------------------------------*/


/* 28. memeber/edit_email.php (LEONG HONG JUN part) */
/**
 * PXX Mall - 全局消息处理器
 * 放在 $(document).ready 里面确保 HTML 加载完后执行
 */
$(document).ready(function() {
    // 1. 获取 URL 中的查询参数
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    // 2. 根据不同的信号弹出不同的对话框
    if (status) {
        switch(status) {
            case 'phone_updated':
                alert('✅ Verification Successful! Phone number has been updated.');
                break;
            case 'email_updated':
                alert('✅ Email has been updated. Please verify your new email.');
                break;
            // 你可以继续增加更多分支...
        }

        // 3. 强迫症必备：弹出后清理 URL（把 ?status=xxx 删掉）
        // 这样用户刷新页面时，不会再次看到弹窗
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + "?page=security";
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
    }
});
/*----------------------------------------------*/

/*member/email-verify.php lim li zhe*/
(function() {
    let timeLeft = 30; // 固定倒计时 30s
    let timerInterval = null;
    
    const timerDisplay = document.getElementById('timer-val');
    const countdownBox = document.getElementById('countdown-ui');
    const resendTrigger = document.getElementById('resend-btn-ui');

    /**
     * 初始化倒计时循环逻辑
     */
    function startTimerLoop(seconds) {
        clearInterval(timerInterval); 
        timeLeft = seconds;
        
        if(countdownBox) countdownBox.style.display = 'block'; 
        if(resendTrigger) resendTrigger.style.display = 'none';
        
        timerInterval = setInterval(() => {
            timeLeft--; 
            if(timerDisplay) timerDisplay.innerText = timeLeft;
            
            if (timeLeft <= 0) {
                // 🔥 时间到，停止倒计时并显示按钮供用户“自主”重发
                clearInterval(timerInterval); 
                if(countdownBox) countdownBox.style.display = 'none';
                if(resendTrigger) {
                    resendTrigger.style.display = 'block';
                    resendTrigger.innerText = "Resent a new code";
                    resendTrigger.style.pointerEvents = "auto";
                }
            }
        }, 1000);
    }

    /**
     * 🔥 重发流程：由用户按，按完之后开启 30s 倒计时
     */
    window.runResendProcess = function() {
        if(resendTrigger) {
            resendTrigger.innerText = "Code Requesting...";
            resendTrigger.style.pointerEvents = "none";
        }
        
        // 调用后端发信服务
        fetch('email_verify.php?service=trigger_resend')
            .then(res => res.text())
            .then(status => {
                if (status.trim() === 'success') { 
                    alert("Success: A new verification code has been sent to your email!"); 
                    // 只有成功重发后，才重新开启 30s 倒计时
                    startTimerLoop(30); 
                } else { 
                    alert("Transmission Error: " + status); 
                    if(resendTrigger) {
                        resendTrigger.innerText = "Resent a new code";
                        resendTrigger.style.pointerEvents = "auto";
                    }
                }
            })
            .catch(err => {
                alert("Network latency detected.");
                if(resendTrigger) {
                    resendTrigger.innerText = "Resent a new code";
                    resendTrigger.style.pointerEvents = "auto";
                }
            });
    };

    // 页面初次进入时，因为系统自动发了一次，所以自动开启第一次倒计时
    startTimerLoop(30);
})();
/*---------------------------------------------------*/

/*member login php limlizhe*/
function toggleModal(show) { 
    const modal = document.getElementById('helpModal');
    if(modal) {
        modal.style.display = show ? 'block' : 'none';
    }
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
    if (event.target == modal) {
        toggleModal(false);
    }
}
/*---------------------------------------*/ 

/*member profile lim li zhe*/
document.addEventListener('DOMContentLoaded', function() {
    const stateSelect = document.getElementById('m_state');
    const citySelect = document.getElementById('m_city');

    if (!stateSelect || !citySelect) return;

    const currentSQLState = "<?php echo d($u['State']); ?>";
    const currentSQLCity = "<?php echo d($u['City']); ?>";

    fetch('../includes/state.json') 
        .then(response => {
            if (!response.ok) throw new Error('State JSON file not found');
            return response.json();
        })
        .then(data => {
            const malaysiaRegions = data;

            stateSelect.innerHTML = '<option value="">Select State</option>';
            
            // 1. 加载州属 (States)
            Object.keys(malaysiaRegions).sort().forEach(stateName => {
                let option = new Option(stateName, stateName);
                if (stateName === currentSQLState) {
                    option.selected = true;
                }
                stateSelect.add(option);
            });

            // 2. 如果已有州属数据，加载对应的城市 (Cities)
            if (currentSQLState && malaysiaRegions[currentSQLState]) {
                // 注意：这里传入的是 Object.keys，因为城市名现在是对象的 Key
                fillCities(Object.keys(malaysiaRegions[currentSQLState]), currentSQLCity);
            }

            // 3. 联动事件
            stateSelect.addEventListener('change', function() {
                const selectedState = this.value;
                // 获取选中州属下的所有城市名（Key）
                const cityData = malaysiaRegions[selectedState] ? Object.keys(malaysiaRegions[selectedState]) : [];
                fillCities(cityData);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            stateSelect.innerHTML = '<option value="">Error loading data</option>';
        });

    function fillCities(cityNames, preselectedCity = "") {
        citySelect.innerHTML = '<option value="">Select City</option>';
        cityNames.sort().forEach(cityName => {
            let option = new Option(cityName, cityName);
            if (cityName === preselectedCity) {
                option.selected = true;
            }
            citySelect.add(option);
        });
    }
});

function triggerPhotoDelete() {
    if(confirm('Warning: Remove your current photo and use default?')) {
        document.getElementById('displayAvatarImg').src = '../images/default.jpg';
        document.getElementById('avatarDelFlagInput').value = '1';
    }
}
/*--------------------------------------------------*/

/*member signup limlizhe*/
let isUserValid = false;
    let isPassValid = false;

    // Modal Controls
    function openTnC() { document.getElementById('tncModal').style.display = 'flex'; }
    function acceptTnC() {
        document.getElementById('f_tnc').checked = true;
        document.getElementById('f_tnc').disabled = false;
        document.getElementById('tncModal').style.display = 'none';
        validateForm();
    }

    // Security Validation (Length & Match)
    function validateSecurity() {
        const p1 = document.getElementById('f_pass').value;
        const p2 = document.getElementById('f_conf').value;
        const lenMsg = document.getElementById('pass-len-msg');
        const matchMsg = document.getElementById('pass-match-msg');
        const input2 = document.getElementById('f_conf');

        // Length Check
        lenMsg.style.display = (p1.length > 0 && p1.length < 6) ? 'block' : 'none';

        // Match Check
        if (p2.length > 0) {
            if (p1 !== p2) {
                matchMsg.style.display = 'block';
                input2.classList.add('input-error');
                isPassValid = false;
            } else {
                matchMsg.style.display = 'none';
                input2.classList.remove('input-error');
                isPassValid = (p1.length >= 6);
            }
        } else {
            matchMsg.style.display = 'none';
        }
        
        validateForm();
    }

    // AJAX Username Verification
    let timer;
    document.getElementById('f_user').addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const u = document.getElementById('f_user').value.trim();
            const f = document.getElementById('user-feedback');
            if (u.length < 3) { f.style.display = 'none'; isUserValid = false; validateForm(); return; }

            const res = await fetch('?check_user=' + encodeURIComponent(u));
            const status = await res.text();
            f.style.display = 'block';
            f.innerText = (status === 'taken') ? '✖ Username already exists' : '✔ Username is available';
            f.className = (status === 'taken') ? 'msg-hint err-text' : 'msg-hint ok-text';
            isUserValid = (status !== 'taken');
            validateForm();
        }, 500);
    });

    // Final Enable/Disable Button
    function validateForm() {
        const tnc = document.getElementById('f_tnc').checked;
        const btn = document.getElementById('btnS');
        btn.disabled = !(isUserValid && isPassValid && tnc);
    }
