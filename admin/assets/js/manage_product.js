(function initManageProduct() {
    const checkReady = setInterval(() => {
        const btnAdd = document.getElementById("btn-add");
        if (btnAdd) {
            clearInterval(checkReady);
            initEvents();
        }
    }, 50);

    function initEvents() {
        const API_URL = "/PETSHOP/admin/api/api_product.php";
        const btnAdd = document.getElementById("btn-add");
        const btnRefresh = document.getElementById("btn-refresh");
        const searchInput = document.getElementById("product-search");
        const tableBody = document.querySelector(".admin-table tbody");

        btnAdd.addEventListener("click", () => showForm("add"));
        btnRefresh.addEventListener("click", loadProducts);

        searchInput.addEventListener("input", handleSearch);

        // ============================================
        // 🔍 SEARCH FILTER
        // ============================================
        function handleSearch() {
            const filter = searchInput.value.toLowerCase();
            const rows = tableBody.querySelectorAll("tr");
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
            });
        }

        // ============================================
        // 📌 LOAD PRODUCT LIST
        // ============================================
        async function loadProducts() {
            try {
                const res = await fetch(`${API_URL}?action=list`);
                const result = await res.json();

                tableBody.innerHTML = "";

                if (result.status !== "success" || !result.data.length) {
                    tableBody.innerHTML = `<tr><td colspan="8">Không có sản phẩm.</td></tr>`;
                    return;
                }

                result.data.forEach(item => renderRow(item));

                attachRowEvents();

            } catch (err) {
                tableBody.innerHTML = `<tr><td colspan="8">Không thể tải dữ liệu!</td></tr>`;
            }
        }

        // ============================================
        // 🧩 IMAGE URL HELPERS
        // Build a usable image URL for admin UI from DB value which may be:
        // - a full URL (https://...)
        // - a root-relative path (/public/...)
        // - a repo-relative value like "images/products/tenfile"
        function buildImageUrl(value) {
            if (!value) return '/PETSHOP/public/images/no_image.png';
            const v = String(value).trim();
            if (/^https?:\/\//i.test(v)) return v; // full URL
            if (v.startsWith('/')) return v; // root-relative
            // repo-relative -> prepend project root
            return '/PETSHOP/' + v.replace(/^\/+/, '');
        }

        // ============================================
        // 🧩 RENDER TABLE ROW
        // ============================================
        function renderRow(item) {

            const img = buildImageUrl(item.image);

            const row = document.createElement("tr");
            row.innerHTML = `
                <td><img src="${img}" class="product-img"></td>
                <td>${item.name}</td>
                <td>${item.sku}</td>
                <td>${item.category_name ?? ""}</td>
                <td>${Number(item.price).toLocaleString()} đ</td>
                <td>${item.stock_quantity}</td>

                <!-- ⭐ Chỉ 1 trạng thái -->
                <td>
                    <span class="status-badge ${item.status}">
                        ${item.status === "active" ? "🟢 Active" : "🔴 Disabled"}
                    </span>
                </td>

                <!-- Các nút hành động -->
                <td>
                    <button class="btn-edit" data-id="${item.id}">Sửa</button>
                    <button class="btn-delete" data-id="${item.id}">Xóa</button>
                </td>
            `;

            tableBody.appendChild(row);
        }

        // ============================================
        // 🔗 ATTACH EVENTS TO ROW BUTTONS
        // ============================================
        function attachRowEvents() {
            document.querySelectorAll(".btn-edit").forEach(btn => {
                btn.addEventListener("click", () => handleEdit(btn.dataset.id));
            });

            document.querySelectorAll(".btn-delete").forEach(btn => {
                btn.addEventListener("click", () => showDeleteConfirm(btn.dataset.id));
            });

            document.querySelectorAll(".btn-status").forEach(btn => {
                btn.addEventListener("click", () => toggleStatus(btn.dataset.id, btn.dataset.status));
            });
        }

        async function handleEdit(id) {
            try {
                const res = await fetch(`${API_URL}?action=detail&id=${id}`);
                const result = await res.json();
                if (result.status === "success") {
                    showForm("edit", result.data);
                } else {
                    alert("Không tải được sản phẩm: " + result.message);
                }
            } catch (err) {
                alert("Lỗi kết nối API");
            }
        }


        async function toggleStatus(id, status) {
            const formData = new FormData();
            formData.append("id", id);
            formData.append("status", status);

            const res = await fetch(`${API_URL}?action=status`, {
                method: "POST",
                body: formData
            });
            const result = await res.json();
            if (result.status === "success") loadProducts();
        }


        // ============================================
        // 🧾 POPUP FORM: ADD / EDIT PRODUCT
        // ============================================
        function showForm(mode, product = {}) {
            const formHTML = `
                <div class="modal" id="product-form">
                    <div class="modal-content">
                        <h3>${mode === "add" ? "➕ Thêm sản phẩm" : "✏️ Sửa sản phẩm"}</h3>

                        ${renderInputs(product)}

                        <div class="modal-actions">
                            <button id="btn-cancel">Hủy</button>
                            <button id="btn-save">${mode === "add" ? "Thêm" : "Cập nhật"}</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML("beforeend", formHTML);

            // === IMAGE PREVIEW ===
            document.getElementById("image").onchange = previewImage;

            // CLOSE FORM
            document.getElementById("btn-cancel").onclick = () =>
                document.getElementById("product-form").remove();

            // SAVE
            document.getElementById("btn-save").onclick = () =>
                saveProduct(mode, product);
        }

        // FORM INPUT HTML (clean, tách riêng)
        function renderInputs(p) {
            return `
                <label>Tên sản phẩm</label>
                <input type="text" id="name" value="${p.name || ""}" 
                       oninput="slug.value=this.value.toLowerCase().replace(/\\s+/g,'-')">

                <label>Slug</label>
                <input type="text" id="slug" value="${p.slug || ""}">

                <label>SKU</label>
                <input type="text" id="sku" value="${p.sku || ""}">

                 <label>Hình ảnh</label>
                 <input type="file" id="image">
                 <img id="preview" src="${buildImageUrl(p.image)}" 
                     style="width:100px;margin-top:8px;display:${p.image ? "block" : "none"}">

                <label>Brand ID</label>
                <input type="number" id="brand_id" value="${p.brand_id || ""}">

                <label>Category ID</label>
                <input type="number" id="category_id" value="${p.category_id || ""}">

                <label>Mô tả ngắn</label>
                <textarea id="short_description">${p.short_description || ""}</textarea>

                <label>Mô tả dài</label>
                <textarea id="long_description">${p.long_description || ""}</textarea>

                <label>Giá gốc</label>
                <input type="number" id="base_price" value="${p.base_price || 0}">

                <label>Giá bán</label>
                <input type="number" id="price" value="${p.price || 0}">

                <label>Tiền tệ</label>
                <input type="text" id="currency" value="${p.currency || "VND"}">

                <label>Khối lượng (kg)</label>
                <input type="number" id="weight" value="${p.weight || 0}">

                <label>Kích thước (Dài - Rộng - Cao)</label>
                <div style="display:flex;gap:10px;">
                    <input type="number" id="length" placeholder="Dài" value="${p.length || 0}">
                    <input type="number" id="width" placeholder="Rộng" value="${p.width || 0}">
                    <input type="number" id="height" placeholder="Cao" value="${p.height || 0}">
                </div>

                <label>Số lượng tồn kho</label>
                <input type="number" id="stock_quantity" value="${p.stock_quantity || 0}">

                <label>Trạng thái kho</label>
                <select id="stock_status">
                    <option value="in_stock" ${p.stock_status === "in_stock" ? "selected" : ""}>Còn hàng</option>
                    <option value="out_of_stock" ${p.stock_status === "out_of_stock" ? "selected" : ""}>Hết hàng</option>
                    <option value="preorder" ${p.stock_status === "preorder" ? "selected" : ""}>Đặt trước</option>
                </select>

                <label>Trạng thái</label>
                <select id="status">
                    <option value="active" ${p.status === "active" ? "selected" : ""}>Active</option>
                    <option value="disabled" ${p.status === "disabled" ? "selected" : ""}>Disabled</option>
                </select>

                <label>
                    <input type="checkbox" id="featured" ${p.featured ? "checked" : ""}>
                    Sản phẩm nổi bật
                </label>
            `;
        }

        function previewImage(e) {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById("preview");
                preview.src = URL.createObjectURL(file);
                preview.style.display = "block";
            }
        }

        // ============================================
        // 💾 SAVE PRODUCT (ADD / UPDATE)
        // ============================================
        async function saveProduct(mode, product = {}) {
            const formData = new FormData();

            const fields = [
                "name", "slug", "sku", "brand_id", "category_id",
                "short_description", "long_description",
                "base_price", "price", "currency",
                "weight", "length", "width", "height",
                "stock_quantity", "stock_status", "status"
            ];

            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el) formData.append(id, el.value);
            });

            formData.append("featured", document.getElementById("featured").checked ? 1 : 0);

            // Ảnh
            const file = document.getElementById("image").files[0];
            if (file) formData.append("image", file);

            // Gửi ID khi edit
            if (mode === "edit") formData.append("id", product.id);

            // URL không cần ?id=...
            const url = mode === "add"
                ? `${API_URL}?action=create`
                : `${API_URL}?action=update`;

            try {
                const res = await fetch(url, { method: "POST", body: formData });

                // If server returned non-2xx, show status + body for debugging
                if (!res.ok) {
                    const text = await res.text();
                    console.error('API error', res.status, text);
                    alert(`Lỗi kết nối API (status ${res.status})\n${text}`);
                    return;
                }

                // Try to parse JSON, but handle non-JSON gracefully
                const contentType = res.headers.get('content-type') || '';
                let result;
                if (contentType.indexOf('application/json') !== -1) {
                    result = await res.json();
                } else {
                    const text = await res.text();
                    console.error('API returned non-JSON:', text);
                    alert('Lỗi kết nối API (server trả về không phải JSON)\n' + text);
                    return;
                }

                if (result.status === "success") {
                    alert("Lưu thành công!");
                    document.getElementById("product-form").remove();
                    loadProducts();
                } else {
                    console.error('API result error', result);
                    alert("Lỗi khi lưu sản phẩm: " + (result.message || JSON.stringify(result)));
                }
            } catch (err) {
                console.error('Fetch error', err);
                alert("Lỗi kết nối API (network)");
            }
        }



        // ============================================
        // 🗑 DELETE
        // ============================================
        function showDeleteConfirm(id) {
            const confirmHTML = `
                <div class="modal" id="confirm-delete">
                    <div class="modal-content">
                        <h3>⚠️ Xác nhận xóa</h3>
                        <p>Bạn có chắc chắn muốn xóa sản phẩm ID ${id}?</p>
                        <div class="modal-actions">
                            <button id="cancel-delete">Hủy</button>
                            <button id="confirm-delete-btn">Xóa</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML("beforeend", confirmHTML);

            document.getElementById("cancel-delete").onclick =
                () => document.getElementById("confirm-delete").remove();

            document.getElementById("confirm-delete-btn").onclick =
                () => deleteProduct(id);
        }

        async function deleteProduct(id) {
            const formData = new FormData();
            formData.append("id", id);

            try {
                const res = await fetch(`${API_URL}?action=delete`, {
                    method: "POST",
                    body: formData
                });
                const result = await res.json();

                if (result.status === "success") {
                    alert("Đã xóa!");
                    document.getElementById("confirm-delete").remove();
                    loadProducts();
                } else {
                    alert("Xóa thất bại: " + result.message);
                }
            } catch (err) {
                alert("Lỗi kết nối API");
            }
    }


        // First load
        loadProducts();
    }
})();
