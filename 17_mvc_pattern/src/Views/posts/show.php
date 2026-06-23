<h1><?= $post['title'] ?></h1>
<p style="color:#666;">
    By <strong><?= $post['author'] ?></strong> |
    <?= $post['created_at'] ?>
</p>
<hr>
<p><?= nl2br($post['content']) ?></p>
<hr>
<p><a href="/posts">Kembali ke daftar</a></p>
