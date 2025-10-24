// set current year in footer
document.addEventListener('DOMContentLoaded', function(){
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // Attach add-to-cart handlers
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e){
            const form = e.target.closest('.add-to-cart-form');
            if (!form) return;
            const productId = form.querySelector('input[name="product_id"]').value;

            fetch('cart_api.php?action=add', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                body: 'product_id=' + encodeURIComponent(productId) + '&ajax=1'
            })
            .then(r => r.json())
            .then(json => {
                if (json && json.success) {
                    // small toast
                    const toast = document.createElement('div');
                    toast.className = 'cart-toast';
                    toast.textContent = json.message;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.classList.add('visible'), 10);
                    setTimeout(() => { toast.classList.remove('visible'); setTimeout(()=>toast.remove(),300); }, 3000);
                } else {
                    alert(json.message || 'Có lỗi khi thêm vào giỏ hàng');
                }
            }).catch(err => {
                console.error(err);
                alert('Lỗi mạng, vui lòng thử lại.');
            });
        });
    });

    // product detail progressive load (click on .product-link)
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
                // lấy nội dung của phần main-content trong trang trả về
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMain = doc.querySelector('#main-content');
                if (newMain) mainContent.innerHTML = newMain.innerHTML;

                window.history.pushState({}, '', url); // cập nhật URL mà không reload
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(() => {
                mainContent.innerHTML = '<p style="color:red;">Lỗi khi tải chi tiết sản phẩm.</p>';
            });
    });

    // back/forward
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
    const s = document.createElement('style'); s.id='cart-toast-styles'; s.textContent=`.cart-toast{position:fixed;right:20px;bottom:20px;background:#0f172a;color:#fff;padding:10px 14px;border-radius:8px;box-shadow:0 6px 18px rgba(2,6,23,0.2);opacity:0;transform:translateY(8px);transition:all .25s ease;z-index:9999}.cart-toast.visible{opacity:1;transform:translateY(0)}`; document.head.appendChild(s);
}