<h1>Tambah User Baru</h1>

<form method="POST" action="/users">
    <div style="margin-bottom: 10px;">
        <label>Nama:</label><br>
        <input type="text" name="name" value="<?= $old['name'] ?? '' ?>" required>
    </div>
    <div style="margin-bottom: 10px;">
        <label>Email:</label><br>
        <input type="email" name="email" value="<?= $old['email'] ?? '' ?>" required>
    </div>
    <div style="margin-bottom: 10px;">
        <label>Password:</label><br>
        <input type="password" name="password" required>
    </div>
    <div style="margin-bottom: 10px;">
        <label>Role:</label><br>
        <select name="role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
    </div>
    <button type="submit">Simpan</button>
    <a href="/users">Batal</a>
</form>
