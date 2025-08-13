<?php $role = session('role') ?? ''; ?>
<nav class="navbar navbar-dark bg-dark px-3">
  <a class="navbar-brand" href="/">Sandya NVR</a>

  <ul class="navbar-nav flex-row gap-3">
    <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>

    <?php if ($role === 'admin' || $role === 'superadmin'): ?>
      <li class="nav-item"><a class="nav-link" href="/admin/cameras">Cameras</a></li>
      <li class="nav-item"><a class="nav-link" href="/admin/users">Users</a></li>
    <?php endif; ?>

    <li class="nav-item">
      <span class="nav-link text-white-50">
        <?= esc( session('display') ) ?> (<?= esc($role ?: '-') ?>)
      </span>
    </li>

    <li class="nav-item">
      <a class="btn btn-outline-light btn-sm" href="/logout">Logout</a>
    </li>
  </ul>
</nav>
