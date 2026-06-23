<h1><?= $title ?></h1>
<p>Selamat datang di aplikasi MVC dengan PHP.</p>
<p>Aplikasi ini mendemonstrasikan konsep MVC:</p>
<ul>
    <li><strong>Model</strong> - Mengelola data dan logika bisnis</li>
    <li><strong>View</strong> - Menampilkan data ke user (template)</li>
    <li><strong>Controller</strong> - Menghubungkan Model dan View</li>
</ul>

<h2>Stats</h2>
<ul>
    <li>Total users: <?= $stats['users'] ?? 0 ?></li>
    <li>Total posts: <?= $stats['posts'] ?? 0 ?></li>
</ul>

<p>
    <a href="/users">Lihat Users</a> |
    <a href="/posts">Lihat Posts</a>
</p>
