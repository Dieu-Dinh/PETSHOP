console.log('admin/assets/js/manage_order.js loaded');

function initManageOrder() {
    const ordersBody = document.getElementById('ordersBody');
    const refreshBtn = document.getElementById('refreshOrders');
    const searchInput = document.getElementById('orderSearch');
    const detailModal = document.getElementById('orderDetailModal');
    const detailContent = document.getElementById('orderDetailContent');
    const closeDetail = document.getElementById('closeOrderDetail');

    if (!ordersBody) return;

    async function loadOrders() {
        ordersBody.innerHTML = '<tr><td colspan="7">Đang tải...</td></tr>';
        try {
            const res = await fetch('/PETSHOP/app/api/admin_order_api.php');
            const json = await res.json();
            if (!json.success) throw new Error(json.message || 'Lỗi');
            renderOrders(json.data);
        } catch (err) {
            ordersBody.innerHTML = `<tr><td colspan="7">Lỗi tải: ${err.message}</td></tr>`;
        }
    }

    function renderOrders(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            ordersBody.innerHTML = '<tr><td colspan="7">Không có đơn hàng.</td></tr>';
            return;
        }
        const q = (searchInput && searchInput.value || '').trim().toLowerCase();
        const filtered = rows.filter(r => {
            if (!q) return true;
            return (r.order_number || '').toLowerCase().includes(q)
                || (r.first_name || '').toLowerCase().includes(q)
                || (r.email || '').toLowerCase().includes(q);
        });

        ordersBody.innerHTML = filtered.map(r => {
            return `<tr data-id="${r.id}" data-status="${r.status}">
                <td>${escapeHtml(r.order_number || ('#'+r.id))}</td>
                <td>${escapeHtml((r.first_name || '') + (r.email ? ' ('+r.email+')':''))}</td>
                <td>${formatMoney(r.total_amount || 0)}</td>
                <td>${escapeHtml(r.payment_status || '')}</td>
                <td>
                    <select class="status-select">${statusOptions(r.status)}</select>
                </td>
                <td>${escapeHtml(r.placed_at || '')}</td>
                <td>
                    <button class="btn-view">Xem</button>
                    <button class="btn-cancel">Hủy</button>
                </td>
            </tr>`;
        }).join('');

        // attach listeners for selects and buttons
        document.querySelectorAll('#ordersBody tr').forEach(tr => {
            const id = tr.dataset.id;
            const viewBtn = tr.querySelector('.btn-view');
            const cancelBtn = tr.querySelector('.btn-cancel');
            const sel = tr.querySelector('.status-select');
            if (viewBtn) viewBtn.addEventListener('click', () => viewOrder(id));
            if (cancelBtn) cancelBtn.addEventListener('click', () => cancelOrder(id));
            if (sel) {
                sel.value = tr.dataset.status || sel.value;
                sel.addEventListener('change', () => updateStatus(id, sel.value));
            }
        });
    }

    function statusOptions(current) {
        const opts = ['pending','confirmed','packing','shipped','delivered','cancelled','returned'];
        return opts.map(o => `<option value="${o}" ${o===current? 'selected':''}>${o}</option>`).join('');
    }

    function formatMoney(n) { return (Number(n)||0).toLocaleString('vi-VN') + ' ₫'; }
    function escapeHtml(s){ return String(s||'').replace(/[&<>\\\"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

    async function viewOrder(id) {
        detailContent.innerHTML = 'Đang tải...';
        detailModal.style.display = 'block';
        try {
            const res = await fetch('/PETSHOP/app/api/admin_order_api.php?id='+encodeURIComponent(id));
            const json = await res.json();
            if (!json.success) throw new Error(json.message||'Lỗi');
            const o = json.data.order;
            const items = json.data.items || [];
            detailContent.innerHTML = `<h3>Đơn ${escapeHtml(o.order_number || o.id)}</h3>
                <p>Khách: ${escapeHtml(o.first_name||o.user_email||'Khách')}</p>
                <p>Tổng: ${formatMoney(o.total_amount)}</p>
                <h4>Items</h4>
                <ul>${items.map(it=>`<li>${escapeHtml(it.product_name_snapshot||it.product_name)} x ${it.quantity} — ${formatMoney(it.total_price)}</li>`).join('')}</ul>`;
        } catch (err) {
            detailContent.innerHTML = 'Lỗi tải: ' + err.message;
        }
    }

    if (closeDetail) closeDetail.addEventListener('click', () => detailModal.style.display = 'none');
    if (detailModal) detailModal.addEventListener('click', (e) => { if (e.target === detailModal) detailModal.style.display = 'none'; });

    async function updateStatus(orderId, status) {
        if (!confirm('Cập nhật trạng thái thành "'+status+'"?')) return;
        try {
            const res = await fetch('/PETSHOP/app/api/admin_order_api.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ action:'update_status', order_id: orderId, status })
            });
            const j = await res.json();
            if (!j.success) throw new Error(j.message||'Lỗi');
            alert('Cập nhật thành công');
            loadOrders();
        } catch (err) { alert('Lỗi: '+err.message); }
    }

    async function cancelOrder(orderId){
        if (!confirm('Bạn có chắc muốn hủy đơn này?')) return;
        try {
            const res = await fetch('/PETSHOP/app/api/admin_order_api.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ action:'cancel', order_id: orderId })
            });
            const j = await res.json();
            if (!j.success) throw new Error(j.message||'Lỗi');
            alert('Đã hủy đơn'); loadOrders();
        } catch (err) { alert('Lỗi: '+err.message); }
    }

    if (refreshBtn) refreshBtn.addEventListener('click', loadOrders);
    if (searchInput) searchInput.addEventListener('input', () => loadOrders());

    // initial
    loadOrders();
}

// If document already loaded (script injected dynamically), run init immediately
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initManageOrder);
} else {
    initManageOrder();
}
