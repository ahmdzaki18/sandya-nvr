<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h5 class="mb-3"><?= esc($title) ?></h5>

<?php $errors = session('errors') ?? []; ?>
<?php if($errors): ?>
<div class="alert alert-danger">
  <ul class="mb-0">
    <?php foreach($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="post" action="<?= $item ? '/admin/cameras/'.$item['id'] : '/admin/cameras' ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Name*</label>
      <input name="name" class="form-control" required
             value="<?= esc(old('name', $item['name'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Location</label>
      <input name="location" class="form-control"
             value="<?= esc(old('location', $item['location'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">FPS</label>
      <input type="number" name="fps" class="form-control" min="1" max="120"
             value="<?= esc(old('fps', $item['fps'] ?? '')) ?>">
    </div>

    <div class="col-md-4">
      <label class="form-label">Host*</label>
      <input name="host" class="form-control" required
             value="<?= esc(old('host', $item['host'] ?? '')) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Port*</label>
      <input type="number" name="port" class="form-control" required min="1" max="65535"
             value="<?= esc(old('port', $item['port'] ?? 554)) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Protocol*</label>
      <select name="protocol" class="form-select" required>
        <?php
        $opts = ['rtsp','rtmp','srt','hls','http-mjpeg','webrtc'];
        $val = old('protocol', $item['protocol'] ?? 'rtsp');
        foreach($opts as $o){
          $sel = $val===$o?'selected':'';
          echo "<option $sel>$o</option>";
        } ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Transport*</label>
      <select name="transport" class="form-select" required>
        <?php $val = old('transport', $item['transport'] ?? 'tcp'); ?>
        <option <?= $val==='tcp'?'selected':'' ?>>tcp</option>
        <option <?= $val==='udp'?'selected':'' ?>>udp</option>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">Stream Path / URI*</label>
      <input name="stream_path" class="form-control" required
             placeholder="/stream1 atau full URI"
             value="<?= esc(old('stream_path', $item['stream_path'] ?? '')) ?>">
      <div class="form-text">Contoh RTSP: <code>/Streaming/Channels/101</code></div>
    </div>

    <div class="col-md-3">
      <label class="form-label">Username</label>
      <input name="username" class="form-control"
             value="<?= esc(old('username', $item['username'] ?? '')) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Password <?= $item ? '(kosong = tidak diubah)' : '' ?></label>
      <input type="password" name="password" class="form-control">
    </div>

    <div class="col-md-3">
      <label class="form-label">Audio</label>
      <?php $a = (int)old('audio_enabled', $item['audio_enabled'] ?? 0); ?>
      <select name="audio_enabled" class="form-select">
        <option value="0" <?= $a? '':'selected' ?>>No</option>
        <option value="1" <?= $a? 'selected':'' ?>>Yes</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Recording</label>
      <?php $r = (int)old('is_recording', $item['is_recording'] ?? 1); ?>
      <select name="is_recording" class="form-select">
        <option value="1" <?= $r? 'selected':'' ?>>On</option>
        <option value="0" <?= $r? '':'selected' ?>>Off</option>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="3"><?= esc(old('notes', $item['notes'] ?? '')) ?></textarea>
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary"><?= $item ? 'Update' : 'Create' ?></button>
    <a class="btn btn-secondary" href="/admin/cameras">Cancel</a>
  </div>
</form>
<?= $this->endSection() ?>
