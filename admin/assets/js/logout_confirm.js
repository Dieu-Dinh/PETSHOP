// Admin logout confirmation
document.addEventListener('click', (e) => {
  const btn = e.target.closest('#btnLogoutAdmin');
  if (!btn) return;
  e.preventDefault();
  const ok = window.confirm('Bạn có chắc muốn đăng xuất khỏi trang quản trị không?');
  if (ok) window.location.href = btn.href;
});
