<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php $role = $role ?? (session('role') ?? ''); $cams = $cams ?? []; ?>

<h5 class="mb-3 text-center">All Cameras</h5>

<?php if (session('error')): ?>
  <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

<?php if (empty($cams)): ?>
  <?php if (($role ?? '') === 'admin' || ($role ?? '') === 'superadmin'): ?>
    <div class="text-center text-muted py-5">
      <div class="mb-3">Belum ada kamera atau belum di‑index.</div>
      <a class="btn btn-primary btn-sm" href="/admin/cameras">Tambah Camera</a>
    </div>
  <?php else: ?>
    <div class="text-center text-muted py-5">
      Dashboard kamu belum di‑assign kamera.
    </div>
  <?php endif; ?>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($cams as $c): ?>
      <?php $thumb = '/videos/'.rawurlencode($c['name']).'/live/preview.jpg?ts='.time(); ?>
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card shadow-sm h-100">
          <img src="<?= $thumb ?>" class="card-img-top" alt="preview"
               onerror="this.src='data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-semibold"><?= esc($c['name']) ?></div>
                <div class="text-muted small"><?= esc($c['location'] ?? '-') ?></div>
              </div>
              <?= $c['is_recording']
                    ? '<span class="badge bg-success">REC</span>'
                    : '<span class="badge bg-secondary">OFF</span>' ?>
            </div>
            <div class="mt-2 d-flex gap-2">
              <a class="btn btn-sm btn-primary" href="/camera/<?= $c['id'] ?>" target="_blank">Play</a>
              <?php if (($role ?? '') === 'admin' || ($role ?? '') === 'superadmin'): ?>
                <a class="btn btn-sm btn-outline-secondary" href="/admin/cameras/<?= $c['id'] ?>/edit">Edit</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
