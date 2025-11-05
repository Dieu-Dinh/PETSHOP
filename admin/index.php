<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /PETSHOP/public/login.php");
    exit();
}
$admin = $_SESSION['user']['email'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang quản trị | PetShop</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- 🧭 Sidebar -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <!-- 📄 Main -->
        <main class="main-content" id="main-content">
            <?php include __DIR__ . '/partials/header.php'; ?>

            <!-- Nội dung trang -->
            <div id="page-content" class="p-3"></div>

            <?php include __DIR__ . '/partials/footer.php'; ?>
        </main>
    </div>

    <script>
        /**
         * 🔄 Hàm tải trang con
         * @param {string} page - tên trang (ví dụ: manage_user)
         */
        async function loadPage(page) {
            try {
                const res = await fetch(`views/${page}.php`);
                if (!res.ok) throw new Error("Trang không tồn tại!");

                const html = await res.text();
                const container = document.getElementById("page-content");
                container.innerHTML = html;

                // 🔥 Sau khi load xong HTML → nạp script tương ứng (nếu có)
                loadPageScript(page);
            } catch (err) {
                document.getElementById("page-content").innerHTML = `<p>❌ Lỗi tải trang: ${err.message}</p>`;
            }
        }

        /**
         * 📜 Hàm nạp JS riêng của từng module (nếu tồn tại)
         * @param {string} page - tên trang (ví dụ: manage_user)
         */
        function loadPageScript(page) {
            const scriptPath = `assets/js/${page}.js`;

            // Xóa script cũ nếu có
            document.querySelectorAll("script[data-dynamic]").forEach(s => s.remove());

            // Tạo thẻ script mới
            const script = document.createElement("script");
            script.src = scriptPath + "?v=" + Date.now(); // tránh cache
            script.dataset.dynamic = "true";
            script.defer = true;
            document.body.appendChild(script);

            // Log ra console để debug
            console.log(`📦 Loaded JS: ${scriptPath}`);
        }

        // 🧭 Xử lý click menu
        document.querySelectorAll(".menu-link").forEach(link => {
            link.addEventListener("click", e => {
                e.preventDefault();
                const page = e.target.dataset.page;

                // Tải nội dung trang
                loadPage(page);

                // Đổi trạng thái active
                document.querySelectorAll(".menu-link").forEach(l => l.classList.remove("active"));
                e.target.classList.add("active");
            });
        });

        // 🏁 Mặc định load Dashboard
        window.addEventListener("DOMContentLoaded", () => loadPage("dashboard"));
    </script>
</body>
</html>
