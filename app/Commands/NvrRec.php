<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CameraModel;

class NvrRec extends BaseCommand
{
    protected $group       = 'nvr';
    protected $name        = 'nvr:rec';
    protected $description = 'Record a single camera (blocking).';
    protected $usage       = 'nvr:rec <camera_id>';
    protected $arguments   = ['camera_id' => 'ID kamera'];

    public function run(array $params)
    {
        $id = (int)($params[0] ?? 0);
        if ($id <= 0) {
            CLI::error('Camera ID is required.');
            return 1;
        }

        /** @var CameraModel $cams */
        $cams   = model(CameraModel::class);
        $camera = $cams->asArray()->find($id);
        if (!$camera) {
            CLI::error("Camera {$id} not found.");
            return 1;
        }

        $base   = "/CBR-NFS-VIDEO/CBR-NVR-SRVR/{$camera['name']}";
        $live   = "{$base}/live";
        $today  = date('Y-m-d');
        $dayDir = "{$base}/{$today}";

        @is_dir($live)  || @mkdir($live, 0775, true);
        @is_dir($dayDir) || @mkdir($dayDir, 0775, true);

        $user = rawurlencode((string)($camera['username'] ?? ''));
        $pass = '';
        if (!empty($camera['password_enc'])) {
            try {
                $pass = rawurlencode(
                    service('encrypter')->decrypt(base64_decode($camera['password_enc']))
                );
            } catch (\Throwable $e) {
                $pass = '';
            }
        }
        $auth = $user !== '' ? ($pass !== '' ? "{$user}:{$pass}@" : "{$user}@") : '';

        // Build input & RTSP options
        $proto   = strtolower($camera['protocol'] ?? 'rtsp');
        $trans   = strtolower($camera['transport'] ?? 'tcp');
        $path    = ltrim((string)($camera['stream_path'] ?? ''), '/');
        $host    = (string)$camera['host'];
        $port    = (int)($camera['port'] ?? 554);

        if ($proto === 'rtsp') {
            $input  = "rtsp://{$auth}{$host}:{$port}/{$path}";
            $rtspOpt = ['-rtsp_transport', $trans];
        } elseif ($proto === 'rtmp') {
            $input  = "rtmp://{$host}:{$port}/{$path}";
            $rtspOpt = [];
        } elseif ($proto === 'hls') {
            $input  = "http://{$host}:{$port}/{$path}";
            $rtspOpt = [];
        } else {
            $input  = "rtsp://{$auth}{$host}:{$port}/{$path}";
            $rtspOpt = ['-rtsp_transport', $trans];
        }

        CLI::write("Starting recording for Camera {$id} ({$camera['name']})...");

        // loop: kalau ffmpeg exit tapi is_recording masih 1, start ulang
        while (true) {
            $row = $cams->select('is_recording')->asArray()->find($id);
            if (!$row || (int)$row['is_recording'] !== 1) {
                CLI::write("Recording stopped for Camera {$id}");
                break;
            }

            $ffmpeg = '/usr/bin/ffmpeg';
            $cmd = array_merge(
                [$ffmpeg, '-hide_banner', '-nostdin', '-nostats', '-loglevel', 'error'],
                $rtspOpt,
                ['-i', $input],

                // --- Output 1: MP4 15‑menit, copy video + transcode audio ke AAC ---
                [
                    '-map', '0',
                    '-c:v', 'copy',
                    '-c:a', 'aac', '-b:a', '128k',
                    '-movflags', '+faststart',
                    '-f', 'segment', '-segment_time', '900',
                    '-reset_timestamps', '1', '-strftime', '1',
                    "{$dayDir}/%H%M%S.mp4",
                ],

                // --- Output 2: HLS live, delete ts lama ---
                [
                    '-map', '0',
                    '-c:v', 'copy',
                    '-c:a', 'aac', '-b:a', '128k',
                    '-f', 'hls',
                    '-hls_time', '2',
                    '-hls_list_size', '30',
                    '-hls_flags', 'delete_segments+append_list+independent_segments',
                    "{$live}/index.m3u8",
                ],

                // --- Output 3: preview jpg tiap 10 detik ---
                ['-vf', 'fps=1/10', '-update', '1', "{$live}/preview.jpg"]
            );

            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            $proc = proc_open($cmd, $descriptors, $pipes);
            if (\is_resource($proc)) {
                // Biarkan ffmpeg jalan; blok sampai exit
                fclose($pipes[0]);
                stream_get_contents($pipes[1]); fclose($pipes[1]);
                stream_get_contents($pipes[2]); fclose($pipes[2]);
                proc_close($proc);
            }

            // jeda pendek agar tidak tight-loop
            sleep(2);
        }

        return 0;
    }
}
