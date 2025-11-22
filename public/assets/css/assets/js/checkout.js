document.addEventListener('DOMContentLoaded', () => {
    const subtotalEl = document.getElementById('summarySubtotal');
    const shippingSelect = document.getElementById('shippingSelect');
    const taxEl = document.getElementById('summaryTax');
    const totalEl = document.getElementById('summaryTotal');
    const couponInput = document.getElementById('couponCode');
    const applyBtn = document.getElementById('applyCoupon');

    // initial subtotal (server-provided integer)
    let baseSubtotal = parseInt(window.checkoutInitialSubtotal || 0, 10);
    function formatVND(n) {
        return n.toLocaleString('vi-VN') + ' đ';
    }

    function compute() {
        const shipping = parseInt(shippingSelect.value || 0, 10);
        // simple tax rate 5%
        const tax = Math.round(baseSubtotal * 0.05);

        // coupon handling (simple demo)
        let discount = 0;
        const code = (couponInput && couponInput.value || '').trim().toUpperCase();
        if (code === 'DISCOUNT10') discount = Math.round(baseSubtotal * 0.10);
        if (code === 'SAVE50') discount = 50000; // fixed
        if (code === 'FREESHIP') { discount = 0; /* handled as shipping=0 below if applied */ }

        // if FREESHIP code used, set shipping to 0
        const freeShipApplied = code === 'FREESHIP';
        const effectiveShipping = freeShipApplied ? 0 : shipping;

        const total = Math.max(0, baseSubtotal - discount + effectiveShipping + tax);

        subtotalEl.textContent = formatVND(baseSubtotal);
        taxEl.textContent = formatVND(tax);
        totalEl.textContent = formatVND(total);
    }

    if (shippingSelect) shippingSelect.addEventListener('change', compute);
    if (applyBtn) applyBtn.addEventListener('click', (e) => { e.preventDefault(); compute(); });

    // initialize
    compute();

    // Place order -> submit to order_api.php
    const place = document.getElementById('placeOrder');
    if (place) {
        place.addEventListener('click', async (e) => {
            e.preventDefault();

            // Client-side validation for required shipping info
            const fullnameEl = document.getElementById('fullname');
            const phoneEl = document.getElementById('phone');
            const provinceEl = document.getElementById('province');
            const addressEl = document.getElementById('address');

            const fullname = fullnameEl?.value?.trim() || '';
            const phone = phoneEl?.value?.trim() || '';
            const province = provinceEl?.value?.trim() || '';
            const address = addressEl?.value?.trim() || '';

            if (!fullname || fullname.length < 2) {
                alert('Vui lòng nhập họ tên người nhận (ít nhất 2 ký tự).');
                fullnameEl?.focus();
                return;
            }
            const phoneDigits = phone.replace(/\D/g, '');
            if (!phone || phoneDigits.length < 7) {
                alert('Vui lòng nhập số điện thoại hợp lệ (ít nhất 7 chữ số).');
                phoneEl?.focus();
                return;
            }
            if (!province) {
                alert('Vui lòng chọn tỉnh / thành hoặc nhập địa chỉ đầy đủ.');
                provinceEl?.focus();
                return;
            }
            if (!address || address.length < 5) {
                alert('Vui lòng nhập địa chỉ giao hàng hợp lệ (ít nhất 5 ký tự).');
                addressEl?.focus();
                return;
            }

            place.disabled = true;
            place.textContent = 'Đang xử lý...';

            // collect address/payment fields
            // (reuse variables already defined)
            const payment = document.querySelector('input[name="payment"]:checked')?.value || 'cod';

            // collect items from cart table rows
            const rows = document.querySelectorAll('.cart-row');
            const items = [];
            rows.forEach(r => {
                const pid = r.dataset.id || r.dataset.productId;
                const qty = parseInt(r.dataset.qty || r.querySelector('.prod-qty')?.textContent || '1', 10);
                const price = parseFloat(r.dataset.price || r.querySelector('.prod-price')?.textContent.replace(/[^\d]/g, '') || '0') / 1;
                const total_price = (isNaN(qty) ? 1 : qty) * (isNaN(price) ? 0 : price);
                items.push({ product_id: pid, name: r.querySelector('.prod-name')?.textContent?.trim() || '', quantity: qty, unit_price: price, total_price });
            });

            // compute values consistent with UI
            const shipping_fee = parseFloat(document.getElementById('shippingSelect')?.value || 0);
            const tax = Math.round(baseSubtotal * 0.05);
            const code = (couponInput && couponInput.value || '').trim().toUpperCase();
            let discount = 0;
            if (code === 'DISCOUNT10') discount = Math.round(baseSubtotal * 0.10);
            if (code === 'SAVE50') discount = 50000;
            const total = Math.max(0, baseSubtotal - discount + (code === 'FREESHIP' ? 0 : shipping_fee) + tax);

            const payload = {
                fullname, phone, province, address,
                payment_method: payment,
                items, subtotal: baseSubtotal, shipping_fee, tax, discount, total
            };

            try {
                const res = await fetch('/PETSHOP/app/api/order_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.success) {
                    // redirect to success page
                    window.location.href = `index.php?page=order_success&id=${json.order_id}`;
                    return;
                } else {
                    alert('Không thể tạo đơn: ' + (json.message || 'Lỗi'));
                }
            } catch (err) {
                console.error(err);
                alert('Lỗi kết nối. Vui lòng thử lại.');
            } finally {
                place.disabled = false;
                place.textContent = 'Đặt hàng';
            }
        });
    }
});
