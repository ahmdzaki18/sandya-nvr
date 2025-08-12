<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Login</h5>
        <?php if(session('error')): ?>
          <div class="alert alert-danger small"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <form method="post" action="/login">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" class="form-control" required value="<?= old('username') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">Sign In</button>
          <div class="form-text mt-2">
            Local user: verifikasi password DB.<br>
            LDAP user: otomatis bind ke AD (<code>dc.sandya.net</code>).
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
