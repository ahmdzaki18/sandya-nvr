<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CameraModel;

class NvrRec extends BaseCommand
{
    protected $group       = 'nvr';
    protected $name        = 'nvr:rec';
    protected $description = 'Record specific camera stream to MP4 & HLS.';
    protected $usage       = 'nvr:rec <camera_id>';
    protected $arguments   = ['camera_id' => 'Camera ID from database'];

    public function run(array $params)
    {
        NvrService::tz();

        $id = (int)($params[0] ?? 0);
        if ($id <= 0) {
            CLI::error('Camera ID is required. Usage: spark nvr:rec <id>');
            return;
        }

        $cam = NvrService::getCamera($id);
        if (!$cam) {
            CLI::error("Camera {$id} not found.");
            return;
        }

        // Kalau is_recording = 0, hentikan
        if ((int)($cam['is_recording'] ?? 0) !== 1) {
            CLI::write('Camera is_recording=0. Nothing to do.');
            return;
        }

        [$inputUrl, $rtspOpt] = NvrService::buildInput($cam);
        [, $liveDir, $dayDir] = NvrService::ensureDirs($cam['name']);

        // Nama file: HH-MM-SS.mp4 (folder sudah YYYY-MM-DD)
        $timeFile   = date('H-i-s');
        $outputFile = "{$dayDir}/{$timeFile}.mp4";

        // HLS & preview
        $hlsIndex   = "{$liveDir}/index.m3u8";
        $previewJpg = "{$liveDir}/preview.jpg";

        $ffmpeg = '/usr/bin/ffmpeg';
        if (!is_file($ffmpeg)) {
            CLI::error('ffmpeg tidak ditemukan di /usr/bin/ffmpeg');
            return;
        }

        $cmd = [
            $ffmpeg, '-hide_banner', '-nostdin',
            // Opsional: FPS output bila ada di DB (kecilkan ukuran)
            ...(is_numeric($cam['fps'] ?? null) && (int)$cam['fps'] > 0 ? ['-r', (string)(int)$cam['fps']] : []),
            ...$rtspOpt,
            '-i', $inputUrl,

            // Output 1: file MP4 per segmen (900 detik = 15 menit)
            '-map', '0', '-c', 'copy', '-vsync', '1', '-copyts',
            '-f', 'segment', '-segment_time', '900', '-reset_timestamps', '1', $outputFile,

            // Output 2: HLS (rolling 60 detik = list 30 x 2s)
            '-map', '0', '-c', 'copy', '-vsync', '1',
            '-f', 'hls', '-hls_time', '2', '-hls_list_size', '30',
            '-hls_flags', 'delete_segments+append_list', $hlsIndex,

            // Output 3: preview.jpg (update setiap ~10 detik)
            '-vf', 'fps=1/10', '-update', '1', $previewJpg,
        ];

        CLI::write("Starting Camera {$id} ({$cam['name']})...");
        // Jalankan ffmpeg; bila kamera dimatikan (is_recording=0) service/cron sebelah yang ubah status
        $descriptor = [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ];
        $process = proc_open($cmd, $descriptor, $pipes);

        if (is_resource($process)) {
            proc_close($process);
        }
    }
}
