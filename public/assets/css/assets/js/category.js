document.addEventListener("DOMContentLoaded", () => {
    const categoryList = document.getElementById("category-list");
    const mainContent = document.getElementById("main-content");

    // 🟢 B1: Gọi API lấy danh mục
    fetch("../app/api/category_api.php")
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data)) {
                data.forEach(cat => {
                    const li = document.createElement("li");
                    const link = document.createElement("a");
                    link.href = "#";
                    link.textContent = cat.name;
                    link.dataset.id = cat.id;
                    li.appendChild(link);
                    categoryList.appendChild(li);
                });
            } else {
                categoryList.innerHTML = "<li>Không có danh mục nào</li>";
            }
        })
        .catch(err => {
            console.error("Lỗi khi tải danh mục:", err);
            categoryList.innerHTML = "<li>Lỗi tải danh mục</li>";
        });

    // 🟢 B2: Lắng nghe click vào danh mục
    categoryList.addEventListener("click", async (e) => {
        const link = e.target.closest("a");
        if (!link) return;

        e.preventDefault();
        const categoryId = link.dataset.id;

        try {
            const response = await fetch(`../app/api/get_products_by_category.php?category_id=${categoryId}`);
            const data = await response.json();

            if (!data.success) {
                mainContent.innerHTML = `<p class="error">${data.message}</p>`;
                return;
            }

            const productsHTML = data.products.map(p => `
                <div class="product-card" data-id="${p.id}">
                    <img src="${p.image}" alt="${p.name}">
                    <h3>${p.name}</h3>
                    <p class="price">${Number(p.price).toLocaleString()} ₫</p>
                    <div class="btn-group">
                        <button class="add-to-cart" data-id="${p.id}">🛒 Thêm vào giỏ</button>
                        <button class="buy-now" data-id="${p.id}">⚡ Mua ngay</button>
                    </div>
                </div>
            `).join("");

            mainContent.innerHTML = `
                <h2>${data.category.name}</h2>
                <div class="product-grid">${productsHTML}</div>
            `;
        } catch (error) {
            console.error("Lỗi khi tải sản phẩm:", error);
            mainContent.innerHTML = `<p class="error">Không thể tải sản phẩm.</p>`;
        }
    });
});
