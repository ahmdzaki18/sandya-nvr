<?php

require_once __DIR__ . '/NvrService.php';
require_once __DIR__ . '/CameraModel.php';

$camId = $argv[1] ?? null;
if (!$camId) {
    echo "Camera ID required\n";
    exit(1);
}

$cam = CameraModel::findById($camId);
if (!$cam) {
    echo "Camera not found\n";
    exit(1);
}

// Ambil FPS, default ke 15 kalau kosong/null/0
$fps = isset($cam['fps']) && $cam['fps'] > 0 ? (int)$cam['fps'] : 15;

// Path output
$dateFolder = date('Y-m-d');
$timeFile   = date('H-i-s'); // format [HH]-[MM]-[SS]
$outputDir  = "/CBR-NFS-VIDEO/CBR-NVR-SRV/{$cam['name']}/{$dateFolder}";
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}
$outputFile = "{$outputDir}/{$timeFile}.mp4";

// URL RTSP
$rtspUrl = sprintf(
    "%s://%s:%s@%s:%d%s",
    $cam['protocol'],
    $cam['username'],
    CameraModel::decryptPassword($cam['password_enc']),
    $cam['host'],
    $cam['port'],
    $cam['stream_path']
);

// Command ffmpeg
$cmd = sprintf(
    'ffmpeg -rtsp_transport %s -i "%s" -r %d -c:v copy -c:a copy -strftime 1 "%s"',
    escapeshellarg($cam['transport']),
    $rtspUrl,
    $fps,
    $outputFile
);

echo "Starting recording for camera {$cam['name']} ({$fps} fps)\n";
echo "Output: {$outputFile}\n";

exec($cmd . " > /dev/null 2>&1 &");

