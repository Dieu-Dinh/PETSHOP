<?php
// Admin dashboard view (fragment)
if (session_status() === PHP_SESSION_NONE) session_start();
// Minimal guard: the admin loader should already ensure access
?>
<div class="admin-dashboard">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <h2>Dashboard</h2>
    <div class="kpi-row">
        <div class="kpi-card" id="kpi-rev7">
            <div class="kpi-label">Revenue (7d)</div>
            <div class="kpi-value">Loading...</div>
        </div>
        <div class="kpi-card" id="kpi-rev30">
            <div class="kpi-label">Revenue (30d)</div>
            <div class="kpi-value">Loading...</div>
        </div>
        <div class="kpi-card" id="kpi-orders-today">
            <div class="kpi-label">Orders Today</div>
            <div class="kpi-value">Loading...</div>
        </div>
        <div class="kpi-card" id="kpi-new-customers">
            <div class="kpi-label">New Customers (30d)</div>
            <div class="kpi-value">Loading...</div>
        </div>
    </div>

    <section class="recent-orders">
        <h3>Recent Orders</h3>
        <table class="recent-orders-table">
            <thead>
                <tr><th>#</th><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Placed</th></tr>
            </thead>
            <tbody id="recent-orders-body">
                <tr><td colspan="6">Loading...</td></tr>
            </tbody>
        </table>
    </section>
    
    <section class="revenue-chart-section" style="margin-top:18px">
        <h3>Revenue (last 7 days)</h3>
        <canvas id="revenueChart" width="600" height="200"></canvas>
    </section>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/dashboard.js?v=1" data-dynamic></script>
</div>
