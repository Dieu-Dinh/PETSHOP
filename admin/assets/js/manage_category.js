(function initCategory() {

    const checkReady = setInterval(() => {
        const btnAdd = document.getElementById("btn-add");
        if (btnAdd) {
            clearInterval(checkReady);
            initEvents();
        }
    }, 50);

    function initEvents() {

        const API_URL = "/PETSHOP/admin/api/api_category.php";

        const btnAdd = document.getElementById("btn-add");
        const btnSave = document.getElementById("btn-save");
        const btnClose = document.getElementById("btn-close");
        const modal = document.getElementById("modal-category");

        loadCategories();

        // ============================
        // THÊM
        // ============================
        btnAdd.onclick = () => {
            document.getElementById("modal-title").innerText = "Thêm danh mục";
            document.getElementById("cat-id").value = "";
            document.getElementById("cat-name").value = "";
            document.getElementById("cat-description").value = "";
            document.getElementById("cat-active").value = 1;

            modal.style.display = "flex";
        };

        // ============================
        // LƯU (THÊM / SỬA)
        // ============================
        btnSave.onclick = async () => {

            const id = document.getElementById("cat-id").value;
            const name = document.getElementById("cat-name").value;
            const desc = document.getElementById("cat-description").value;
            const active = document.getElementById("cat-active").value;

            if (name.trim() === "") {
                alert("Tên danh mục không được để trống!");
                return;
            }

            const form = new FormData();
            form.append("name", name);
            form.append("description", desc);
            form.append("is_active", active);

            let action = "add";
            if (id) {
                action = "update";
                form.append("id", id);
            }

            const res = await fetch(`${API_URL}?action=${action}`, {
                method: "POST",
                body: form
            });

            const data = await res.json();
            alert(data.msg);

            if (data.status) {
                modal.style.display = "none";
                loadCategories();
            }
        };

        btnClose.onclick = () => modal.style.display = "none";

        // ============================
        // LOAD DANH MỤC
        // ============================
        async function loadCategories() {
            const res = await fetch(`${API_URL}?action=list`);
            const json = await res.json();

            const tbody = document.querySelector("#table-category tbody");
            tbody.innerHTML = "";

            json.data.forEach(cat => {
                const tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${cat.id}</td>
                    <td>${cat.name}</td>
                    <td>${cat.description || ''}</td>
                    <td>
                        <span class="status ${cat.is_active == 1 ? "active" : "inactive"}">
                            ${cat.is_active == 1 ? "Active" : "Inactive"}
                        </span>
                    </td>
                    <td>
                        <button class="btn-edit"
                            data-id="${cat.id}"
                            data-name="${cat.name}"
                            data-description="${cat.description}"
                            data-active="${cat.is_active}">
                            Sửa
                        </button>

                        <button class="btn-delete" data-id="${cat.id}">
                            Xóa
                        </button>
                    </td>
                `;

                tbody.appendChild(tr);
            });

            attachRowEvents();
        }

        // ============================
        // SỬA / XÓA
        // ============================
        function attachRowEvents() {

            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.onclick = () => {
                    document.getElementById("modal-title").innerText = "Cập nhật danh mục";

                    document.getElementById("cat-id").value = btn.dataset.id;
                    document.getElementById("cat-name").value = btn.dataset.name;
                    document.getElementById("cat-description").value = btn.dataset.description;
                    document.getElementById("cat-active").value = btn.dataset.active;

                    modal.style.display = "flex";
                };
            });

            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.onclick = async () => {

                    if (!confirm("Xóa danh mục này?")) return;

                    const form = new FormData();
                    form.append("id", btn.dataset.id);

                    const res = await fetch(`${API_URL}?action=delete`, {
                        method: "POST",
                        body: form
                    });

                    const data = await res.json();

                    alert(data.msg);

                    if (data.status) loadCategories();
                };
            });
        }
    }

})();
