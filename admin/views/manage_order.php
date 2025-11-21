<?php // loaded inside admin/index.php via fetch (server returns HTML) ?>
<link rel="stylesheet" href="assets/css/manage_order.css">

<div class="admin-page">
    <h1>Quản lý đơn hàng</h1>

    <div class="orders-toolbar">
        <input id="orderSearch" placeholder="Tìm theo mã đơn / khách hàng" />
        <button id="refreshOrders">Tải lại</button>
    </div>

    <div id="ordersContainer">
        <table class="admin-orders-table">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="ordersBody"></tbody>
        </table>
    </div>

    <!-- detail modal placeholder -->
    <div id="orderDetailModal" class="admin-modal" style="display:none">
        <div class="admin-modal-content">
            <button id="closeOrderDetail" class="close">×</button>
            <div id="orderDetailContent">Đang tải...</div>
        </div>
    </div>
</div>

<script src="assets/js/manage_order.js"></script>

<!-- Inline debug: attempt to fetch admin_order_api.php immediately to ensure API is reachable -->
<script>
    console.log('manage_order view loaded — running inline API check');
    fetch('/PETSHOP/app/api/admin_order_api.php')
        .then(r => { console.log('admin_order_api GET status', r.status); return r.json(); })
        .then(j => console.log('admin_order_api inline response', j))
        .catch(e => console.error('admin_order_api inline error', e));
</script>
