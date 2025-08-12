<?php

namespace App\Services;

use CodeIgniter\Files\File;
use Config\Database;

class NvrService
{
    public function record(int $cameraId)
    {
        $db = Database::connect();

        $query = $db->query("
            SELECT name, host, port, protocol, transport, stream_path,
                   COALESCE(username, '') AS username,
                   COALESCE(password_enc, '') AS password_enc,
                   audio_enabled, is_recording
            FROM cameras
            WHERE id = ? AND deleted_at IS NULL
        ", [$cameraId]);

        $cam = $query->getRowArray();

        if (!$cam) {
            echo "Camera {$cameraId} not found\n";
            return;
        }

        // Direktori target
        $baseDir = "/CBR-NFS-VIDEO/CBR-NVR-SRVR/{$cam['name']}";
        $liveDir = "{$baseDir}/live";
        $dayDir  = "{$baseDir}/" . date('Y-m-d');

        // Buat folder jika belum ada
        @mkdir($liveDir, 0775, true);
        @mkdir($dayDir, 0775, true);

        // Decrypt password
        $plainPwd = '';
        if (!empty($cam['password_enc'])) {
            $enc = service('encrypter');
            try {
                $plainPwd = $enc->decrypt(base64_decode($cam['password_enc'], true));
            } catch (\Throwable $e) {
                $plainPwd = '';
            }
        }

        // Encode untuk URL
        $uenc = rawurlencode($cam['username']);
        $penc = rawurlencode($plainPwd);

        // Bangun URL input
        switch ($cam['protocol']) {
            case 'rtsp':
                $auth = $uenc ? "{$uenc}" : '';
                $auth = $penc ? "{$auth}:{$penc}" : $auth;
                $auth = $auth ? "{$auth}@" : '';
                $input = "rtsp://{$auth}{$cam['host']}:{$cam['port']}{$cam['stream_path']}";
                $rtspOpt = ['-rtsp_transport', $cam['transport']];
                break;

            case 'rtmp':
                $input = "rtmp://{$cam['host']}:{$cam['port']}{$cam['stream_path']}";
                $rtspOpt = [];
                break;

            case 'hls':
                $input = "http://{$cam['host']}:{$cam['port']}{$cam['stream_path']}";
                $rtspOpt = [];
                break;

            default:
                $input = "rtsp://{$cam['host']}:{$cam['port']}{$cam['stream_path']}";
                $rtspOpt = ['-rtsp_transport', $cam['transport']];
                break;
        }

        // Jalankan ffmpeg
        $ffmpeg = '/usr/bin/ffmpeg';

        $cmd = array_merge(
            [$ffmpeg, '-hide_banner', '-nostdin'],
            $rtspOpt,
            ['-i', $input],
            ['-map', '0', '-c', 'copy', '-vsync', '1', '-copyts',
             '-f', 'segment', '-segment_time', '900', '-reset_timestamps', '1', '-strftime', '1', "{$dayDir}/%H%M%S.mp4"],
            ['-map', '0', '-c', 'copy', '-vsync', '1',
             '-f', 'hls', '-hls_time', '2', '-hls_list_size', '30', '-hls_flags', 'delete_segments+append_list', "{$liveDir}/index.m3u8"],
            ['-vf', 'fps=1/10', '-update', '1', "{$liveDir}/preview.jpg"]
        );

        echo "Starting recording for Camera {$cameraId} ({$cam['name']})...\n";

        // Replace process dengan ffmpeg
        pcntl_exec($cmd[0], array_slice($cmd, 1));
    }
}
