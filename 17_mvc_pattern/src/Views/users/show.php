<h1>Detail User</h1>

<table border="1" cellpadding="8" cellspacing="0">
    <tr><th>ID</th><td><?= $user['id'] ?></td></tr>
    <tr><th>Name</th><td><?= $user['name'] ?></td></tr>
    <tr><th>Email</th><td><?= $user['email'] ?></td></tr>
    <tr><th>Role</th><td><?= $user['role'] ?></td></tr>
    <tr><th>Bio</th><td><?= $user['bio'] ?? '-' ?></td></tr>
</table>

<p>
    <a href="/users">Kembali</a>
    <a href="/users/<?= $user['id'] ?>/edit">Edit</a>
</p>
