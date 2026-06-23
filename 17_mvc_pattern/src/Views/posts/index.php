<h1>Daftar Posts</h1>
<p><a href="/posts/create">+ Tambah Post</a></p>

<?php foreach ($posts as $post): ?>
<article style="border:1px solid #ddd; padding:15px; margin-bottom:10px; border-radius:5px;">
    <h3><a href="/posts/<?= $post['id'] ?>"><?= $post['title'] ?></a></h3>
    <p style="color:#666;">By <?= $post['author'] ?> | <?= $post['created_at'] ?></p>
    <p><?= substr($post['content'], 0, 150) ?>...</p>
</article>
<?php endforeach; ?>
