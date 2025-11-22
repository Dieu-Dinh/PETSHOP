// public/assets/js/productRenderer.js
// Không dùng export nếu bạn không load bằng type="module" — mình đăng ký global function renderProducts

(function(global){
  function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function productCardHTML(p, options = {}) {
    const id = escapeHtml(p.id);
    const name = escapeHtml(p.name);
    const price = (p.price !== undefined && p.price !== null) ? Number(p.price).toLocaleString() + ' ₫' : 'Liên hệ';
    const img = escapeHtml(p.image || 'assets/images/no-image.png');
    const stock = escapeHtml(p.stock_status || '');

    return `
      <div class="product-card" data-id="${id}">
        <a class="product-link" href="product.php?id=${id}" title="${name}">
          <div class="product-thumb"><img src="${img}" alt="${name}"></div>
        </a>
        <div class="product-body">
          <h3 class="product-title">${name}</h3>
          <div class="product-meta">
            <span class="product-price">${price}</span>
            ${stock ? `<span class="product-stock">${stock}</span>` : ''}
          </div>
          <div class="btn-group">
            <button class="btn add-to-cart" data-id="${id}">🛒 Thêm vào giỏ</button>
            <button class="btn buy-now" data-id="${id}">⚡ Mua ngay</button>
          </div>
        </div>
      </div>
    `;
  }

  function renderProducts(products, container, options = {}) {
    if (!container) return;
    if (!Array.isArray(products) || products.length === 0) {
      container.innerHTML = '<p class="empty">Không có sản phẩm</p>';
      return;
    }
    const cards = products.map(p => productCardHTML(p, options)).join('');
    container.innerHTML = `<div class="product-grid">${cards}</div>`;
    // Sau khi render, productActions sẽ lắng nghe bằng event delegation (recommended)
  }

  // expose globally
  global.renderProducts = renderProducts;
})(window);
