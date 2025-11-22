// Ensure this script only initializes once (prevents double-binding if loaded multiple times)
if (!window.__productFilterInitialized) {
    window.__productFilterInitialized = true;

    // Helper: build URL for product listing with query & sort
    const buildUrl = (q, sort, category) => {
        const params = new URLSearchParams();
        params.set('page', 'products');
        if (q) params.set('q', q);
        if (sort && sort !== 'default') params.set('sort', sort);
        if (category) params.set('category', category);
        return 'index.php?' + params.toString();
    };

    // Replace existing product grid with HTML from fetched page
    const replaceProductGrid = (doc) => {
        const newMain = doc.querySelector('#main-content') || doc.querySelector('main') || doc;
        const newGrid = newMain ? newMain.querySelector('.product-grid') : null;
        const existingGrid = document.querySelector('.product-grid');
        if (newGrid && existingGrid) {
            existingGrid.innerHTML = newGrid.innerHTML;
            document.dispatchEvent(new CustomEvent('ajax:contentUpdated', { detail: { area: 'product-grid' } }));
            return true;
        }
        const existingMain = document.querySelector('#main-content');
        if (newMain && existingMain) {
            existingMain.innerHTML = newMain.innerHTML;
            document.dispatchEvent(new CustomEvent('ajax:contentUpdated', { detail: { area: 'main-content' } }));
            return true;
        }
        return false;
    };

    // Intercept change events for the sort select using event delegation
    document.addEventListener('change', (e) => {
        const t = e.target;
        if (t && t.id === 'sortSelect') {
            const form = t.closest('#filterForm');
            if (form) form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    });

    // Intercept submit events for the filter form using event delegation
    document.addEventListener('submit', (ev) => {
        const form = ev.target;
        if (!form || form.id !== 'filterForm') return;
        ev.preventDefault();

        const formData = new FormData(form);
        const q = (formData.get('q') || '').toString().trim();
        const sort = (formData.get('sort') || 'default').toString();
        const category = (formData.get('category') || '').toString();
        const url = buildUrl(q, sort, category);

        const productGrid = document.querySelector('.product-grid');
        if (productGrid) productGrid.innerHTML = '<p style="text-align:center;">Đang tải...</p>';

        fetch(url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const ok = replaceProductGrid(doc);
                if (!ok) {
                    if (productGrid) productGrid.innerHTML = '<p style="color:red;">Lỗi khi tải kết quả.</p>';
                }
                window.history.pushState({}, '', url);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(() => {
                if (productGrid) productGrid.innerHTML = '<p style="color:red;">Lỗi khi tải kết quả.</p>';
            });
    });

}
