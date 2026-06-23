<div class="card" style="max-width:600px;">
    <h1>Buat Post Baru</h1>
    <form method="POST" action="/posts">
        <div class="form-group">
            <label>Judul</label>
            <input type="text" name="title" value="<?= \App\Core\Session::old('title') ?>" required>
        </div>
        <div class="form-group">
            <label>Konten</label>
            <textarea name="content" rows="8" required><?= \App\Core\Session::old('content') ?></textarea>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="/posts" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
