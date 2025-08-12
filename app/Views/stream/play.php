<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h5 class="mb-3">Live: <?= esc($cam['name']) ?></h5>

<video id="video" controls autoplay playsinline style="width:100%;max-height:70vh;background:#000"></video>

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
const hlsUrl = "/videos/<?= rawurlencode($cam['name']) ?>/live/index.m3u8?_="+Date.now();
const video = document.getElementById('video');
if (video.canPlayType('application/vnd.apple.mpegurl')) {
  video.src = hlsUrl;
} else if (Hls.isSupported()) {
  const hls = new Hls({lowLatencyMode:true});
  hls.loadSource(hlsUrl);
  hls.attachMedia(video);
} else {
  video.outerHTML = '<div class="alert alert-danger">Browser tidak support HLS.</div>';
}
</script>
<?= $this->endSection() ?>
