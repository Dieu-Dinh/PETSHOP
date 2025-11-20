<?php
require_once __DIR__ . '/../app/config/database.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id=? AND status='published'");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "Bài viết không tồn tại.";
    exit;
}

// Nếu content rỗng → chuyển hướng hoặc mở tab mới
if (empty(trim($post['content']))) {
    // Nếu có source_url thì mở tab mới
    if (!empty($post['source_url'])) {
        echo "<script>window.open('" . htmlspecialchars($post['source_url']) . "', '_blank');</script>";
        echo "Đang chuyển đến trang nguồn...";
        exit;
    } else {
        echo "Bài viết không có nội dung & không có đường dẫn nguồn.";
        exit;
    }
}
?>

<article class="blog-detail">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p class="date">Ngày đăng: <?= date('d/m/Y', strtotime($post['published_at'])) ?></p>

    <?php
    function normalize_blog_image($val) {
        $placeholder = '/PETSHOP/public/assets/images/placeholder.png';
        if (empty($val)) return $placeholder;
        $v = trim($val);
        if (preg_match('#^https?://#i', $v) || strpos($v, 'data:') === 0) return $v;
        if (strpos($v, '/') === 0) {
            if (stripos($v, '/public/') === 0 || stripos($v, '/PETSHOP/public/') === 0) return $v;
            if (stripos($v, '/images/') === 0) return '/PETSHOP/public' . $v;
            return $v;
        }
        if (stripos($v, 'images/') !== false) return '/PETSHOP/public/' . ltrim($v, '/');
        $base = basename($v);
        $fs = realpath(__DIR__ . '/../../public/images/blog/' . $base);
        if ($fs && file_exists($fs)) return '/PETSHOP/public/images/blog/' . $base;
        return $placeholder;
    }

    $blogImg = normalize_blog_image($post['featured_image'] ?? '');
    if ($blogImg): ?>
        <img src="<?= htmlspecialchars($blogImg) ?>" alt="<?= htmlspecialchars($post['title']) ?>" />
    <?php endif; ?>

    <div class="content">
        <?= $post['content'] ?>
    </div>
</article>
