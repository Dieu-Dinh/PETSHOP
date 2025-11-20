document.addEventListener("DOMContentLoaded", () => {

    const grid = document.querySelector(".tips-grid");
    const btnLeft = document.querySelector(".tips-nav.left");
    const btnRight = document.querySelector(".tips-nav.right");

    if (!grid) return;

    const SCROLL_AMOUNT = grid.offsetWidth * 0.7;

    // Nhấn nút điều hướng
    btnLeft.addEventListener("click", () => {
        grid.scrollBy({ left: -SCROLL_AMOUNT, behavior: "smooth" });
    });

    btnRight.addEventListener("click", () => {
        grid.scrollBy({ left: SCROLL_AMOUNT, behavior: "smooth" });
    });

    // Drag to scroll (desktop)
    let isDown = false, startX, scrollLeft;

    grid.addEventListener("mousedown", e => {
        isDown = true;
        startX = e.pageX - grid.offsetLeft;
        scrollLeft = grid.scrollLeft;
    });

    grid.addEventListener("mouseup", () => isDown = false);
    grid.addEventListener("mouseleave", () => isDown = false);

    grid.addEventListener("mousemove", e => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - grid.offsetLeft;
        const walk = (x - startX) * 1;
        grid.scrollLeft = scrollLeft - walk;
    });

    // Ẩn nút khi ở hai đầu
    function updateButtons() {
        btnLeft.style.display = grid.scrollLeft <= 5 ? "none" : "flex";
        btnRight.style.display =
            grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 5
                ? "none"
                : "flex";
    }

    grid.addEventListener("scroll", updateButtons);
    window.addEventListener("resize", updateButtons);

    updateButtons();
});
