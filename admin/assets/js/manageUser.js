document.addEventListener("DOMContentLoaded", () => {
    const API_URL = "../api/api_user.php";

    const btnAdd = document.getElementById("btn-add");
    const btnRefresh = document.getElementById("btn-refresh");
    const searchInput = document.getElementById("user-search");
    const tableBody = document.querySelector(".admin-table tbody");

    // 🟢 Nút thêm người dùng
    btnAdd.addEventListener("click", () => {
        window.location.href = "../views/form_add_user.php";
    });

    // 🔄 Nút làm mới
    btnRefresh.addEventListener("click", loadUsers);

    // 🟣 Tìm kiếm theo tên/email (lọc client-side)
    searchInput.addEventListener("input", () => {
        const filter = searchInput.value.toLowerCase();
        const rows = tableBody.querySelectorAll("tr");
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });

    // 🧾 Hàm tải danh sách người dùng (GET)
    async function loadUsers() {
        try {
            const res = await fetch(API_URL);
            const users = await res.json();

            tableBody.innerHTML = "";
            users.forEach(user => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.email}</td>
                    <td>${user.first_name || ""}</td>
                    <td>${user.last_name || ""}</td>
                    <td>${user.role}</td>
                    <td>${user.is_active ? "✅" : "❌"}</td>
                    <td>
                        <button class="btn-edit" data-id="${user.id}">Sửa</button>
                        <button class="btn-delete" data-id="${user.id}">Xóa</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });

            attachRowEvents();
        } catch (error) {
            console.error("Lỗi tải danh sách:", error);
            alert("Không thể tải danh sách người dùng!");
        }
    }

    //  Gắn sự kiện cho các nút Sửa / Xóa
    function attachRowEvents() {
        document.querySelectorAll(".btn-edit").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = btn.dataset.id;
                window.location.href = `../views/form_edit_user.php?id=${id}`;
            });
        });

        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;
                if (confirm("Bạn có chắc chắn muốn xóa người dùng này không?")) {
                    try {
                        const res = await fetch(`${API_URL}?id=${id}`, {
                            method: "DELETE",
                        });
                        const data = await res.json();
                        if (data.success) {
                            alert("✅ Xóa thành công!");
                            loadUsers();
                        } else {
                            alert("❌ Xóa thất bại!");
                        }
                    } catch (err) {
                        console.error("Lỗi xóa:", err);
                        alert("Đã xảy ra lỗi khi xóa!");
                    }
                }
            });
        });
    }

    // 🚀 Gọi loadUsers() khi trang load
    loadUsers();
});
