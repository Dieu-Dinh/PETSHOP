// public/assets/js/productAction.js
document.addEventListener('click', async function (e) {
  // 🟢 Nút thêm vào giỏ
  const addBtn = e.target.closest('.add-to-cart');
  if (addBtn) {
    const id = addBtn.dataset.id;
    try {
      const res = await fetch('api.php?api=cart&action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ product_id: id, quantity: 1 }).toString()
      });
      const data = await res.json();
      if (data.status === 'success') {
        showToast(data.message || 'Đã thêm vào giỏ hàng');
      } else {
        showToast(data.message || 'Lỗi khi thêm vào giỏ', '#e74c3c');
      }
    } catch (err) {
      console.error(err);
      showToast('Lỗi kết nối server', '#e74c3c');
    }
    return;
  }

  // 🟡 Nút mua ngay
  const buyBtn = e.target.closest('.buy-now');
  if (buyBtn) {
    const id = buyBtn.dataset.id;
    try {
      await fetch('api.php?api=cart&action=add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ product_id: id, quantity: 1 }).toString()
      });
      window.location.href = 'index.php?page=checkout';
    } catch (err) {
      showToast('Lỗi khi xử lý mua ngay', '#e74c3c');
    }
    return;
  }

  // 🔵 Click vào sản phẩm để xem chi tiết
  const productCard = e.target.closest('.product-card');
  if (productCard && !e.target.closest('.add-to-cart') && !e.target.closest('.buy-now')) {
    const id = productCard.dataset.id;
    if (id) {
      window.location.href = `index.php?id=${encodeURIComponent(id)}`;
    }
    return;
  }
});

// 🧩 Hàm thông báo nhỏ
function showToast(msg, bg = '#4caf50') {
  const toast = document.createElement('div');
  toast.className = 'cart-toast';
  toast.textContent = msg;
  Object.assign(toast.style, {
    position: 'fixed',
    right: '20px',
    bottom: '20px',
    padding: '10px 14px',
    borderRadius: '8px',
    background: bg,
    color: '#fff',
    zIndex: 9999,
    boxShadow: '0 6px 18px rgba(2,6,23,0.2)',
    transition: 'opacity 0.3s'
  });
  toast.style.opacity = '0';
  document.body.appendChild(toast);
  setTimeout(() => (toast.style.opacity = '1'), 10);
  setTimeout(() => toast.remove(), 3000);
}
