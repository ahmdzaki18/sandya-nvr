<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Dashboard</h4>
<div class="alert alert-success">
  Halo, <b><?= esc(session('display') ?? session('username')) ?></b>! Role: <code><?= esc(session('role') ?? '-') ?></code>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="card-title mb-2">Cameras</h6>
        <p class="text-muted small mb-3">Kelola kamera & assign ke dashboard.</p>
        <a class="btn btn-primary btn-sm" href="/admin/cameras">Open</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="card-title mb-2">Recordings</h6>
        <p class="text-muted small mb-3">Cari & putar rekaman 15‑menit.</p>
        <a class="btn btn-primary btn-sm" href="/recordings">Open</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="card-title mb-2">Dashboards</h6>
        <p class="text-muted small mb-3">Atur dashboard custom untuk user.</p>
        <a class="btn btn-primary btn-sm" href="/admin/dashboards">Open</a>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
