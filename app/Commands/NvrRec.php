<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CameraModel;

class NvrRec extends BaseCommand
{
    protected $group       = 'NVR';
    protected $name        = 'nvr:rec';
    protected $description = 'Record camera stream and generate preview/live HLS';

    public function run(array $params)
    {
        date_default_timezone_set('Asia/Jakarta');

        $cameraId = $params[0] ?? null;
        if (!$cameraId) {
            CLI::error('Camera ID is required.');
            return;
        }

        $cameraModel = new CameraModel();
        $cam = $cameraModel->find($cameraId);

        if (!$cam) {
            CLI::error("Camera ID {$cameraId} not found.");
            return;
        }

        $ffmpeg = '/usr/bin/ffmpeg'; // path ffmpeg
        $rtspOpt = ['-rtsp_transport', $cam['transport'] ?? 'tcp'];
        $input = sprintf(
            'rtsp://%s:%s@%s:%d%s',
            $cam['username'],
            $this->decryptPassword($cam['password_enc']),
            $cam['host'],
            $cam['port'],
            $cam['stream_path']
        );

        $baseDir = WRITEPATH . 'videos/' . $cam['name'];
        $dayDir  = $baseDir . '/' . date('Y-m-d');
        $liveDir = $baseDir . '/live';

        if (!is_dir($dayDir)) {
            mkdir($dayDir, 0777, true);
        }
        if (!is_dir($liveDir)) {
            mkdir($liveDir, 0777, true);
        }

        $filename = sprintf(
            "%s-%s.mp4",
            $cam['name'],
            date('Y-m-d-H-i-s')
        );

        $cmd = array_merge(
            [$ffmpeg, '-hide_banner', '-nostdin'],
            $rtspOpt,
            ['-i', $input],

            // Segment recording
            [
                '-map', '0',
                '-c', 'copy',
                '-vsync', '1',
                '-copyts',
                '-f', 'segment',
                '-segment_time', '900',
                '-reset_timestamps', '1',
                "{$dayDir}/{$filename}"
            ],

            // Live HLS
            [
                '-map', '0',
                '-c', 'copy',
                '-vsync', '1',
                '-f', 'hls',
                '-hls_time', '2',
                '-hls_list_size', '30',
                '-hls_flags', 'delete_segments+append_list',
                "{$liveDir}/index.m3u8"
            ],

            // Preview image
            [
                '-vf', 'fps=1/10',
                '-update', '1',
                "{$liveDir}/preview.jpg"
            ]
        );

        CLI::write('Running: ' . implode(' ', $cmd));

        $proc = proc_open($cmd, [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR
        ], $pipes);

        if (is_resource($proc)) {
            proc_close($proc);
        }
    }

    private function decryptPassword($enc)
    {
        // sesuaikan sama metode enkripsi lu
        return $enc; 
    }
}
