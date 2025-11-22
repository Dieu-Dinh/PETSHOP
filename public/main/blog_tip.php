<link rel="stylesheet" href="/PETSHOP/public/assets/css/blog.css">
<script defer src="/PETSHOP/public/assets/js/blog.js"></script>

<section class="blog-tips">
    <h2>Chia sẻ kiến thức & tips</h2>

    <div class="tips-grid-wrapper">

        <!-- Nút điều hướng -->
        <button class="tips-nav left" aria-label="Xem mục trước">&lsaquo;</button>

        <div class="tips-grid">
            <?php
            // Lấy 3–5 bài viết mới nhất
            $stmt = $pdo->query("
                SELECT id, title, slug, excerpt, featured_image, published_at, content, source_url
                FROM blog_posts
                WHERE status='published'
                ORDER BY published_at DESC
                LIMIT 5
            ");
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // helper to normalize blog featured image values into public URLs
            function normalize_blog_image($val) {
                $placeholder = '/PETSHOP/public/assets/images/placeholder.png';
                if ($val === null || $val === '' || empty($val)) return $placeholder;
                $v = trim((string)$val);
                if ($v === '') return $placeholder;
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

            foreach ($posts as $post):

                $contentRaw = $post['content'] ?? '';
                $contentTrim = trim((string)$contentRaw);
                if ($contentTrim === '' && !empty($post['source_url'])) {
                    $link = htmlspecialchars($post['source_url']);
                    $target = 'target="_blank"';
                } else {
                    $link = "blog_detail.php?id=" . $post['id'];
                    $target = '';
                }
            ?>
                <div class="tip-card">
                    <a href="<?= $link ?>" <?= $target ?>>
                        <?php if ($post['featured_image']): ?>
                            <img src="<?= htmlspecialchars(normalize_blog_image($post['featured_image'] ?? '')) ?>"
                                 alt="<?= htmlspecialchars($post['title']) ?>"
                                 loading="lazy" decoding="async">
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <p><?= htmlspecialchars($post['excerpt']) ?></p>
                        <span class="date"><?= date('d/m/Y', strtotime($post['published_at'])) ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="tips-nav right" aria-label="Xem mục kế tiếp">&rsaquo;</button>
    </div>
</section>
