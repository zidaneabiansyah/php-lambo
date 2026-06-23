<div class="mb-1">
    <h1>Posts</h1>
    <a href="/posts/create" class="btn btn-primary">+ Buat Post</a>
</div>

<?php if (empty($posts)): ?>
    <div class="card"><p style="color:#666;">Belum ada posts.</p></div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Date</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td><a href="/posts/<?= $post['id'] ?>"><?= $post['title'] ?></a></td>
                <td><?= $post['author'] ?></td>
                <td><?= $post['created_at'] ?></td>
                <td>
                    <a href="/posts/<?= $post['id'] ?>/edit" class="btn btn-primary" style="padding:0.3rem 0.6rem;">Edit</a>
                    <form method="POST" action="/posts/<?= $post['id'] ?>/delete" style="display:inline;">
                        <button type="submit" class="btn btn-danger" style="padding:0.3rem 0.6rem;"
                            onclick="return confirm('Hapus post ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
