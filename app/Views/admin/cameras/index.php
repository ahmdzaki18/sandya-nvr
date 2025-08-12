<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Cameras</h5>
  <a class="btn btn-primary btn-sm" href="/admin/cameras/create">Add Camera</a>
</div>

<?php if(session('msg')): ?><div class="alert alert-success"><?= esc(session('msg')) ?></div><?php endif; ?>
<?php if(session('err')): ?><div class="alert alert-danger"><?= esc(session('err')) ?></div><?php endif; ?>

<form class="row g-2 mb-3" method="get">
  <div class="col-auto"><input class="form-control form-control-sm" name="q" placeholder="Search name/host/location" value="<?= esc($q) ?>"></div>
  <div class="col-auto"><button class="btn btn-outline-secondary btn-sm">Search</button></div>
</form>

<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead>
    <tr>
      <th>ID</th><th>Name</th><th>Host</th><th>Proto</th><th>Path</th><th>Rec</th><th>Updated</th><th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($list as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><strong><?= esc($r['name']) ?></strong><br><small class="text-muted"><?= esc($r['location'] ?? '-') ?></small></td>
      <td><?= esc($r['host']) ?>:<?= esc($r['port']) ?></td>
      <td><?= esc($r['protocol']) ?>/<?= esc($r['transport']) ?></td>
      <td><code><?= esc($r['stream_path']) ?></code></td>
      <td><?= $r['is_recording'] ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>' ?></td>
      <td><small><?= esc($r['updated_at'] ?? $r['created_at']) ?></small></td>
      <td class="text-end">
        <form class="d-inline" method="post" action="/admin/cameras/<?= $r['id'] ?>/toggle"><?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-warning">Toggle Rec</button>
        </form>
        <a class="btn btn-sm btn-outline-primary" href="/admin/cameras/<?= $r['id'] ?>/edit">Edit</a>
        <form class="d-inline" method="post" action="/admin/cameras/<?= $r['id'] ?>/del" onsubmit="return confirm('Delete this camera?')">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>
