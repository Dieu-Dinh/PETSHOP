// ...existing code...
document.addEventListener("DOMContentLoaded", () => {
    const totalPriceEl = document.getElementById("total-price");
    const table = document.querySelector(".cart-table");

    if (!totalPriceEl || !table) {
        console.warn("cart.js: missing totalPriceEl or table");
        return;
    }

    // Robust parser: remove non-digit characters, including NBSP
    function parseAmountFromText(text) {
        if (!text) return 0;
        // Replace non-breaking spaces, normal spaces, dots, commas, currency symbols
        const cleaned = String(text)
            .replace(/\u00A0/g, "")      // NBSP
            .replace(/[,.\sđ₫€$£¥]/g, "")// punctuation, spaces, common currency chars
            .replace(/[^\d-]/g, "");     // keep digits and minus if any
        return Number(cleaned) || 0;
    }

    function getRowSubtotal(row) {
        // prefer dataset.total (server-side numeric) if present
        if (row.dataset && row.dataset.total) {
            const n = Number(row.dataset.total);
            if (!isNaN(n)) return n;
        }
        // fallback: try to read the 'Tổng' cell (last numeric cell)
        const totalCell = row.querySelector("td:last-child") || row.querySelector("td:nth-child(6)");
        return parseAmountFromText(totalCell ? totalCell.textContent : "0");
    }

    function updateTotal() {
        let total = 0;
        const rows = table.querySelectorAll(".cart-row");
        rows.forEach(row => {
            const checkbox = row.querySelector(".select-item");
            if (checkbox && (checkbox.checked || checkbox.getAttribute("data-checked") === "1")) {
                total += getRowSubtotal(row);
            }
        });
        // show formatted number inside span (put currency symbol outside if you prefer)
        totalPriceEl.textContent = total.toLocaleString("vi-VN");
        // If page previously had " đ" after span, no need to add here.
        console.debug("cart.js: updateTotal ->", total);
    }

    // initial compute
    updateTotal();

    // Listen for change and click to ensure all UIs are captured
    table.addEventListener("change", (e) => {
        if (e.target && e.target.matches(".select-item")) {
            updateTotal();
        }
    });

    // also catch clicks on custom styled checkbox wrappers (some themes use label or div)
    table.addEventListener("click", (e) => {
        if (e.target && (e.target.matches(".select-item") || e.target.closest(".select-item-wrapper"))) {
            // small timeout to allow native checkbox state to update
            setTimeout(updateTotal, 10);
        }
    });

    // If quantities can change and affect subtotal, observe mutations or listen to input events
    table.addEventListener("input", (e) => {
        if (e.target && (e.target.matches(".qty-input") || e.target.matches(".select-item"))) {
            setTimeout(updateTotal, 10);
        }
    });

    // Checkout button behavior
    const checkoutBtn = document.querySelector(".btn-checkout");
    if (checkoutBtn) {
        checkoutBtn.addEventListener("click", () => {
            const selected = Array.from(table.querySelectorAll(".select-item:checked"))
                .map(chk => chk.closest(".cart-row").dataset.id)
                .filter(Boolean);
            if (selected.length === 0) {
                alert("⚠️ Vui lòng chọn ít nhất một sản phẩm để thanh toán!");
                return;
            }
            window.location.href = "index.php?page=checkout&ids=" + selected.join(",");
        });
    }
});