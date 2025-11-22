// Show a simple confirmation before logging out
document.addEventListener('click', (e) => {
  const btn = e.target.closest('#btnLogout');
  if (!btn) return;
  // Prevent default navigation and ask user
  e.preventDefault();
  const ok = window.confirm('Bạn có chắc muốn đăng xuất không?');
  if (ok) {
    // use location.href to perform the logout
    window.location.href = btn.href;
  }
});
