<h1>Daftar Users</h1>
<p><a href="/users/create">+ Tambah User</a></p>

<table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
    <tr style="background: #f0f0f0;">
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user['id'] ?></td>
        <td><?= $user['name'] ?></td>
        <td><?= $user['email'] ?></td>
        <td><?= $user['role'] ?></td>
        <td>
            <a href="/users/<?= $user['id'] ?>">Detail</a>
            <a href="/users/<?= $user['id'] ?>/edit">Edit</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
