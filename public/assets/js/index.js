<script>
document.getElementById('year').textContent = new Date().getFullYear();

document.addEventListener('DOMContentLoaded', function () {
    const mainContent = document.getElementById('main-content');

    document.body.addEventListener('click', function (e) {
        const link = e.target.closest('.product-link');
        if (!link) return;

        e.preventDefault();
        const url = link.getAttribute('href');

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

    // Cho phép back/forward bằng nút trình duyệt
    window.addEventListener('popstate', () => {
        fetch(window.location.href)
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMain = doc.querySelector('#main-content');
                if (newMain) mainContent.innerHTML = newMain.innerHTML;
            });
    });
});
</script>