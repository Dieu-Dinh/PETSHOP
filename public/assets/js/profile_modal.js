document.addEventListener("DOMContentLoaded", () => {
    const openBtn = document.getElementById("btnOpenProfile");
    const modal = document.getElementById("profileModal");
    const closeBtn = document.getElementById("closeProfile");

    if (!modal) return;

    if (openBtn) {
        openBtn.addEventListener("click", (e) => {
            e.preventDefault();
            modal.style.display = "block";
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    window.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
    });
});
