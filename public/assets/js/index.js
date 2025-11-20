// set current year in footer
document.addEventListener('DOMContentLoaded', function(){
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // 🔹 Giữ phần load sản phẩm chi tiết
    const mainContent = document.getElementById('main-content');

    // Intercept header search form to load results into #main-content via AJAX
    const headerSearch = document.querySelector('.search-form');
    if (headerSearch && mainContent) {
        headerSearch.addEventListener('submit', function (ev) {
            ev.preventDefault();
            const q = (this.q && this.q.value) ? this.q.value.trim() : '';
            // Build URL to load: ensure we request the full index page with the products view
            const url = q ? `index.php?page=products&q=${encodeURIComponent(q)}` : 'index.php?page=products';
            mainContent.innerHTML = '<p style="text-align:center;">Đang tải sản phẩm...</p>';

            fetch(url)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMain = doc.querySelector('#main-content');
                    if (newMain) mainContent.innerHTML = newMain.innerHTML;
                    // update URL in address bar so users can bookmark/share
                    window.history.pushState({}, '', url.replace(/^index\.php/, 'index.php'));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                })
                .catch(() => {
                    mainContent.innerHTML = '<p style="color:red;">Lỗi khi tải sản phẩm.</p>';
                });
        });
    }
    document.body.addEventListener('click', function (e) {
        // If the user clicked a regular anchor that points to the product listing
        const anchor = e.target.closest('a');
        if (anchor && mainContent) {
            const href = anchor.getAttribute('href') || '';
            // Only handle links explicitly marked for AJAX navigation
            if (anchor.classList && anchor.classList.contains('ajax-nav')) {
                e.preventDefault();
                mainContent.innerHTML = '<p style="text-align:center;">Đang tải sản phẩm...</p>';

                fetch(href)
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        // prefer #main-content from full pages; fallback to <main>
                        const newMain = doc.querySelector('#main-content') || doc.querySelector('main');
                        if (newMain) mainContent.innerHTML = newMain.innerHTML;
                        window.history.pushState({}, '', href);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    })
                    .catch(() => {
                        mainContent.innerHTML = '<p style="color:red;">Lỗi khi tải sản phẩm.</p>';
                    });

                return;
            }
        }

        // Existing product link behavior (product cards)
        const productLink = e.target.closest('.product-link');
        if (!productLink) return;

        e.preventDefault();
        const url = productLink.getAttribute('href');

        if (!mainContent) return;
        mainContent.innerHTML = '<p style="text-align:center;">Đang tải chi tiết sản phẩm...</p>';

        fetch('index.php' + url)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMain = doc.querySelector('#main-content');
                if (newMain) mainContent.innerHTML = newMain.innerHTML;

                window.history.pushState({}, '', url);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(() => {
                mainContent.innerHTML = '<p style="color:red;">Lỗi khi tải chi tiết sản phẩm.</p>';
            });
    });

    // Back/Forward navigation
    window.addEventListener('popstate', () => {
        fetch(window.location.href)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMain = doc.querySelector('#main-content');
                if (newMain && mainContent) mainContent.innerHTML = newMain.innerHTML;
            });
    });
});

/* small toast styles injected if not present */
if (!document.getElementById('cart-toast-styles')){
    const s = document.createElement('style');
    s.id='cart-toast-styles';
    s.textContent=`
        .cart-toast{
            position:fixed;right:20px;bottom:20px;
            background:#0f172a;color:#fff;
            padding:10px 14px;border-radius:8px;
            box-shadow:0 6px 18px rgba(2,6,23,0.2);
            opacity:0;transform:translateY(8px);
            transition:all .25s ease;z-index:9999
        }
        .cart-toast.visible{opacity:1;transform:translateY(0)}
    `;
    document.head.appendChild(s);
}
