(function initManageUser() {
    const checkReady = setInterval(() => {
        const btnAdd = document.getElementById("btn-add");
        if (btnAdd) {
            clearInterval(checkReady);
            initEvents();
        }
    }, 50);

    function initEvents() {
        const API_URL = "api/api_user.php";
        const btnAdd = document.getElementById("btn-add");
        const btnRefresh = document.getElementById("btn-refresh");
        const searchInput = document.getElementById("user-search");
        const tableBody = document.querySelector(".admin-table tbody");

        // 🟢 Hiển thị form thêm
        btnAdd.addEventListener("click", () => showForm("add"));

        // 🔄 Làm mới
        btnRefresh.addEventListener("click", loadUsers);

        // 🔎 Tìm kiếm
        searchInput.addEventListener("input", () => {
            const filter = searchInput.value.toLowerCase();
            const rows = tableBody.querySelectorAll("tr");
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });

        // 🧾 Load danh sách
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
                        </td>`;
                    tableBody.appendChild(row);
                });
                attachRowEvents();
            } catch (err) {
                console.error("Lỗi tải danh sách:", err);
                alert("Không thể tải danh sách người dùng!");
            }
        }

        // 🎯 Gắn sự kiện cho từng dòng
        function attachRowEvents() {
            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", async () => {
                    const id = btn.dataset.id;
                    const res = await fetch(`${API_URL}?id=${id}`);
                    const user = await res.json();
                    showForm("edit", user);
                });
            });

            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.dataset.id;
                    showDeleteConfirm(id);
                });
            });
        }

        // 🧮 Hiển thị form thêm/sửa
        function showForm(mode, user = {}) {
            const formHTML = `
                <div class="modal" id="user-form">
                    <div class="modal-content">
                        <h3>${mode === "add" ? "➕ Thêm người dùng" : "✏️ Sửa người dùng"}</h3>
                        <label>Email</label>
                        <input type="email" id="email" value="${user.email || ""}">
                        <label>Họ</label>
                        <input type="text" id="first_name" value="${user.first_name || ""}">
                        <label>Tên</label>
                        <input type="text" id="last_name" value="${user.last_name || ""}">
                        <label>SĐT</label>
                        <input type="text" id="phone" value="${user.phone || ""}">
                        <label>Mật Khẩu</label>
                        <input type="password" id="password" value="${user.password || ""}">
                        <label>Vai trò</label>
                        <select id="role">
                            <option value="customer" ${user.role === "customer" ? "selected" : ""}>Khách</option>
                            <option value="staff" ${user.role === "staff" ? "selected" : ""}>Nhân viên</option>
                            <option value="admin" ${user.role === "admin" ? "selected" : ""}>Admin</option>
                        </select>

                        <div class="modal-actions">
                            <button id="btn-cancel">Hủy</button>
                            <button id="btn-save">${mode === "add" ? "Thêm" : "Cập nhật"}</button>
                        </div>
                    </div>
                </div>`;
            
            document.body.insertAdjacentHTML("beforeend", formHTML);
            const modal = document.getElementById("user-form");

            document.getElementById("btn-cancel").onclick = () => modal.remove();
            document.getElementById("btn-save").onclick = async () => {
                const data = {
                    email: document.getElementById("email").value.trim(),
                    password: document.getElementById("password").value.trim(),
                    first_name: document.getElementById("first_name").value.trim(),
                    last_name: document.getElementById("last_name").value.trim(),
                    phone: document.getElementById("phone").value.trim(),
                    role: document.getElementById("role").value
                };

                // ⚠️ Kiểm tra nhập liệu
                if (!data.email) {
                    alert("⚠️ Vui lòng nhập email!");
                    return;
                }
                if (mode === "add" && !data.password) {
                    alert("⚠️ Vui lòng nhập mật khẩu!");
                    return;
                }

                try {
                    // 🔹 Nếu là chế độ thêm thì kiểm tra email trùng
                    if (mode === "add") {
                        const checkRes = await fetch(`${API_URL}?check_email=${encodeURIComponent(data.email)}`);
                        const check = await checkRes.json();
                        if (check.exists) {
                            alert("❌ Email đã tồn tại trong hệ thống!");
                            return;
                        }
                    }

                    const method = mode === "add" ? "POST" : "PUT";
                    const res = await fetch(API_URL + (mode === "edit" ? `?id=${user.id}` : ""), {
                        method,
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(data)
                    });

                    const result = await res.json();
                    if (result.success) {
                        alert("✅ Lưu thành công!");
                        modal.remove();
                        loadUsers();
                    } else {
                        alert("❌ Lỗi khi lưu người dùng!");
                        console.error(result);
                    }
                } catch (err) {
                    console.error("Lỗi lưu:", err);
                    alert("Đã xảy ra lỗi!");
                }
            };

        }

        // ❌ Xóa có xác nhận
        function showDeleteConfirm(id) {
            const confirmHTML = `
                <div class="modal" id="confirm-delete">
                    <div class="modal-content">
                        <h3>⚠️ Xác nhận xóa</h3>
                        <p>Bạn có chắc chắn muốn xóa người dùng ID ${id}?</p>
                        <div class="modal-actions">
                            <button id="cancel-delete">Hủy</button>
                            <button id="confirm-delete-btn">Xóa</button>
                        </div>
                    </div>
                </div>`;
            document.body.insertAdjacentHTML("beforeend", confirmHTML);

            document.getElementById("cancel-delete").onclick = () =>
                document.getElementById("confirm-delete").remove();

            document.getElementById("confirm-delete-btn").onclick = async () => {
                try {
                    const res = await fetch(`${API_URL}?id=${id}`, { method: "DELETE" });
                    const data = await res.json();
                    if (data.success) {
                        alert("✅ Đã xóa!");
                        document.getElementById("confirm-delete").remove();
                        loadUsers();
                    } else {
                        alert("❌ Xóa thất bại!");
                    }
                } catch (err) {
                    console.error("Lỗi xóa:", err);
                    alert("Lỗi khi xóa người dùng!");
                }
            };
        }

        loadUsers();
    }
})();
