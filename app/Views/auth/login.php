<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<h5 class="mb-3 text-center">Login</h5>

<?php if (session('error')): ?>
  <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

<form method="post" action="/login" class="mx-auto" style="max-width:420px">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Username</label>
    <input type="text" name="username" class="form-control" autofocus required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <button class="btn btn-primary w-100" type="submit">Login</button>
</form>

<?= $this->endSection() ?>
