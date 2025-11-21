// Admin dashboard JS: fetch KPIs and recent orders
(function(){
    console.log('admin/assets/js/dashboard.js loaded');
    function formatCurrency(v){
        return Number(v).toLocaleString(undefined, {minimumFractionDigits:0, maximumFractionDigits:0});
    }

    function loadScript(src){
        return new Promise((resolve,reject)=>{
            const s = document.createElement('script');
            s.src = src; s.async = true;
            s.onload = () => resolve();
            s.onerror = () => reject(new Error('Failed to load '+src));
            document.head.appendChild(s);
        });
    }

    async function ensureChart(){
        if (typeof Chart !== 'undefined') return;
        try {
            await loadScript('https://cdn.jsdelivr.net/npm/chart.js');
            console.log('Chart.js loaded dynamically');
        } catch (err) {
            console.warn('Could not load Chart.js from CDN', err);
        }
    }

    async function loadDashboard(){
        console.log('loadDashboard start');
        try{
            const resp = await fetch('/PETSHOP/app/api/admin_dashboard_api.php');
            if (!resp.ok) throw new Error('Network response was not ok');
            const j = await resp.json();
            if (!j.success) throw new Error(j.message || 'API error');

            const k = j.data.kpis;
            document.querySelector('#kpi-rev7 .kpi-value').textContent = formatCurrency(k.revenue_7) + ' đ';
            document.querySelector('#kpi-rev30 .kpi-value').textContent = formatCurrency(k.revenue_30) + ' đ';
            document.querySelector('#kpi-orders-today .kpi-value').textContent = k.orders_today;
            document.querySelector('#kpi-new-customers .kpi-value').textContent = k.new_customers;

            const tbody = document.getElementById('recent-orders-body');
            tbody.innerHTML = '';
            (j.data.recent_orders || []).forEach((o, idx)=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${idx+1}</td>
                    <td>${o.order_number}</td>
                    <td>${o.first_name || o.email || '—'}</td>
                    <td>${formatCurrency(o.total_amount)} đ</td>
                    <td>${o.status}</td>
                    <td>${o.placed_at}</td>
                `;
                tbody.appendChild(tr);
            });

            // Render combined revenue (area) + orders (columns) chart (last 7 days)
            try {
                await ensureChart();
                const rev = j.data.revenue_series_7 || [];
                const ord = j.data.orders_series_7 || [];
                // Ensure labels align (both series should have same dates)
                const labels = rev.map(s => s.date);
                const revenueData = rev.map(s => Number(s.total || 0));
                const ordersData = ord.map(s => Number(s.orders || 0));
                const canvas = document.getElementById('revenueChart');
                if (!canvas) { console.warn('#revenueChart not found in DOM'); }
                const ctx = canvas && canvas.getContext ? canvas.getContext('2d') : null;
                if (ctx && typeof Chart !== 'undefined') {
                    if (canvas._chartInstance) { canvas._chartInstance.destroy(); }
                    canvas._chartInstance = new Chart(ctx, {
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    type: 'bar',
                                    label: 'Orders',
                                    data: ordersData,
                                    backgroundColor: 'rgba(33,150,243,0.6)',
                                    borderColor: 'rgba(33,150,243,0.9)',
                                    yAxisID: 'y1'
                                },
                                {
                                    type: 'line',
                                    label: 'Revenue',
                                    data: revenueData,
                                    borderColor: '#4caf50',
                                    backgroundColor: 'rgba(76,175,80,0.15)',
                                    fill: true,
                                    tension: 0.2,
                                    yAxisID: 'y'
                                }
                            ]
                        },
                        options: {
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    position: 'left',
                                    ticks: { callback: v => Number(v).toLocaleString() + ' đ' }
                                },
                                y1: {
                                    beginAtZero: true,
                                    position: 'right',
                                    grid: { drawOnChartArea: false },
                                    ticks: { callback: v => Number(v).toLocaleString() }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const dsLabel = context.dataset.label || '';
                                            const value = context.raw;
                                            if (dsLabel === 'Revenue') return dsLabel + ': ' + Number(value).toLocaleString() + ' đ';
                                            return dsLabel + ': ' + Number(value).toLocaleString();
                                        }
                                    }
                                },
                                legend: { display: true }
                            }
                        }
                    });
                    console.log('Combined chart rendered');
                } else {
                    console.warn('Chart.js not available or canvas context missing');
                }
            } catch (err) { console.warn('Combined chart render failed', err); }

        }catch(err){
            console.error('Dashboard load failed', err);
        }
    }

    // Run immediately if loaded dynamically
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadDashboard);
    } else {
        loadDashboard();
    }

})();
