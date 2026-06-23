<div class="card">
    <h1><?= $post['title'] ?></h1>
    <div class="meta">Oleh <?= $post['author'] ?> | <?= $post['created_at'] ?></div>
    <hr style="margin:1rem 0;">
    <p style="line-height:1.8; white-space: pre-wrap;"><?= $post['content'] ?></p>
    <hr style="margin:1rem 0;">
    <a href="/posts" class="btn btn-secondary">Kembali</a>
    <a href="/posts/<?= $post['id'] ?>/edit" class="btn btn-primary">Edit</a>
</div>
