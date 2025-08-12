<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Sandya NVR</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navs">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navs">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= uri_string()===''?'active':'' ?>" href="/">Dashboard</a></li>
        <?php if(session('role')==='admin' || session('role')==='superadmin'): ?>
          <li class="nav-item"><a class="nav-link" href="/admin/cameras">Cameras</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/dashboards">Dashboards</a></li>
          <?php if(session('role')==='superadmin'): ?>
            <li class="nav-item"><a class="nav-link" href="/admin/users">Users</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <span class="navbar-text me-3"><?= esc(session('display') ?? '') ?> (<?= esc(session('role') ?? '-') ?>)</span>
      <?php if(session('logged_in')): ?>
        <a class="btn btn-outline-light btn-sm" href="/logout">Logout</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
