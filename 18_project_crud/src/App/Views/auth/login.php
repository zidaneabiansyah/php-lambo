<div class="card" style="max-width:400px; margin: 2rem auto;">
    <h1>Login</h1>
    <form method="POST" action="/login">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= \App\Core\Session::old('email') ?>" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
    </form>
    <p style="margin-top:1rem;font-size:0.85rem;text-align:center;">
        Belum punya akun? <a href="/register">Register</a>
    </p>
</div>
