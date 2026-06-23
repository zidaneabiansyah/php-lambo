<div class="card">
    <h1>Selamat Datang di BlogApp</h1>
    <p style="color:#666; margin-top: 0.5rem;">Aplikasi CRUD sederhana sebagai final project belajar PHP.</p>
</div>

<div class="card">
    <h2>Posts Terbaru</h2>
    <?php if (empty($posts)): ?>
        <p style="color:#666;">Belum ada posts.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
        <div style="border-bottom:1px solid #eee; padding: 1rem 0;">
            <h3><a href="/posts/<?= $post['id'] ?>" style="text-decoration:none;color:#2c3e50;">
                <?= $post['title'] ?>
            </a></h3>
            <div class="meta">Oleh <?= $post['author'] ?> | <?= $post['created_at'] ?></div>
            <p style="color:#555;"><?= substr($post['content'], 0, 150) ?>...</p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
