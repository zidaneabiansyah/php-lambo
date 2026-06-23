<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'Belajar MVC' ?></title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        nav { background: #333; padding: 10px; margin-bottom: 20px; }
        nav a { color: white; text-decoration: none; margin-right: 15px; }
        .flash { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .flash.success { background: #d4edda; color: #155724; }
        .flash.error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/users">Users</a>
        <a href="/posts">Posts</a>
    </nav>

    <?php if (isset($flash)): ?>
        <div class="flash <?= $flash['type'] ?>"><?= $flash['message'] ?></div>
    <?php endif; ?>

    <main>
        <?= $content ?>
    </main>

    <footer style="margin-top: 40px; color: #666; font-size: 12px;">
        <p>Belajar MVC Pattern - PHP</p>
    </footer>
</body>
</html>
