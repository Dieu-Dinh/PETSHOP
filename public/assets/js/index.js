// set current year in footer
document.addEventListener('DOMContentLoaded', function(){
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // 🔹 Giữ phần load sản phẩm chi tiết
    const mainContent = document.getElementById('main-content');
    document.body.addEventListener('click', function (e) {
        const link = e.target.closest('.product-link');
        if (!link) return;

        e.preventDefault();
        const url = link.getAttribute('href');

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
