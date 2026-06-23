<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'BlogApp' ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; color: #333; }
        nav { background: #2c3e50; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav .brand { color: white; font-size: 1.2rem; font-weight: bold; text-decoration: none; }
        nav .menu a { color: #ecf0f1; text-decoration: none; margin-left: 1rem; font-size: 0.9rem; }
        nav .menu a:hover { color: #3498db; }
        .container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .flash { padding: 0.8rem 1rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.9rem; }
        .flash.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-size: 0.9rem; border: none; cursor: pointer; }
        .btn-primary { background: #3498db; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .card { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card h3 { margin-bottom: 0.5rem; }
        .card .meta { color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.5rem; }
        input, textarea, select { width: 100%; padding: 0.6rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; }
        input:focus, textarea:focus { outline: none; border-color: #3498db; }
        label { display: block; margin-bottom: 0.3rem; font-weight: 600; font-size: 0.9rem; }
        .form-group { margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; }
        .text-right { text-align: right; }
        .mb-1 { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav>
        <a href="/" class="brand">BlogApp</a>
        <div class="menu">
            <a href="/">Home</a>
            <a href="/posts">Posts</a>
            <?php if (\App\Core\Session::has('user_id')): ?>
                <a href="/posts/create">Buat Post</a>
                <span style="color:#ecf0f1;font-size:0.85rem;">Halo, <?= \App\Core\Session::get('user_name') ?></span>
                <a href="/logout">Logout</a>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <?php $success = \App\Core\Session::flash('success'); ?>
        <?php if ($success): ?>
            <div class="flash success"><?= $success ?></div>
        <?php endif; ?>

        <?php $error = \App\Core\Session::flash('error'); ?>
        <?php if ($error): ?>
            <div class="flash error"><?= $error ?></div>
        <?php endif; ?>

        <?= $content ?>
    </div>
</body>
</html>
