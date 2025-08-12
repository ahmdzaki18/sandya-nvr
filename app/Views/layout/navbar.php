<?php
// app/Views/layout/navbar.php

$uri    = uri_string(); // '' untuk home
$role   = session('role') ?? '';
$logged = (bool) session('logged_in');

$active = function (string $path) use ($uri): string {
    // path: '/' untuk home, lainnya pakai prefix match (admin/cameras*)
    if ($path === '/') {
        return $uri === '' ? 'active' : '';
    }
    $path = ltrim($path, '/');
    return str_starts_with($uri, $path) ? 'active' : '';
};
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Sandya NVR</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navs" aria-controls="navs" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navs">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= $active('/') ?>" href="/">Dashboard</a>
        </li>

        <?php if ($role === 'admin' || $role === 'superadmin'): ?>
          <li class="nav-item">
            <a class="nav-link <?= $active('admin/cameras') ?>" href="/admin/cameras">Cameras</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $active('admin/dashboards') ?>" href="/admin/dashboards">Dashboards</a>
          </li>
        <?php endif; ?>

        <?php if ($role === 'superadmin'): ?>
          <li class="nav-item">
            <a class="nav-link <?= $active('admin/users') ?>" href="/admin/users">Users</a>
          </li>
        <?php endif; ?>
      </ul>

      <?php if ($logged): ?>
        <span class="navbar-text me-3">
          <?= esc(session('display') ?? session('username')) ?>
          <span class="text-secondary">(
            <?= esc($role ?: '-') ?>
          )</span>
        </span>
        <a class="btn btn-outline-light btn-sm" href="/logout">Logout</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
