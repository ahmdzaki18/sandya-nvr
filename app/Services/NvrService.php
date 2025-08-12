<?php

namespace App\Commands;

use App\Models\CameraModel;

class NvrService
{
    public static function tz(): void
    {
        // Pastikan zona waktu lokal
        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set('Asia/Jakarta');
        }
    }

    public static function getCamera(int $id): ?array
    {
        $cam = model(CameraModel::class)->find($id);
        if (!$cam || !empty($cam['deleted_at'])) {
            return null;
        }
        return $cam;
    }

    public static function buildInput(array $cam): array
    {
        $user = trim((string)($cam['username'] ?? ''));
        $pass = '';

        if (!empty($cam['password_enc'])) {
            try {
                $raw = base64_decode($cam['password_enc'], true);
                if ($raw !== false) {
                    $pass = service('encrypter')->decrypt($raw);
                }
            } catch (\Throwable $e) {
                $pass = '';
            }
        }

        $auth = '';
        if ($user !== '' || $pass !== '') {
            $u = rawurlencode($user);
            $p = rawurlencode($pass);
            $auth = $u . (($p !== '') ? (':' . $p) : '') . '@';
        }

        $proto = strtolower((string)($cam['protocol'] ?? 'rtsp'));
        $host  = (string)($cam['host'] ?? '');
        $port  = (int)($cam['port'] ?? 554);
        $path  = (string)($cam['stream_path'] ?? '/');
        $trans = strtolower((string)($cam['transport'] ?? 'tcp'));

        $rtspOpt = [];
        $input   = '';

        switch ($proto) {
            case 'rtsp':
                $input   = "rtsp://{$auth}{$host}:{$port}{$path}";
                $rtspOpt = ['-rtsp_transport', $trans === 'udp' ? 'udp' : 'tcp'];
                break;
            case 'rtmp':
                $input = "rtmp://{$host}:{$port}{$path}";
                break;
            case 'hls':
                $input = "http://{$host}:{$port}{$path}";
                break;
            default:
                $input   = "rtsp://{$auth}{$host}:{$port}{$path}";
                $rtspOpt = ['-rtsp_transport', $trans === 'udp' ? 'udp' : 'tcp'];
                break;
        }

        return [$input, $rtspOpt];
    }

    public static function ensureDirs(string $baseName): array
    {
        // /CBR-NFS-VIDEO/CBR-NVR-SRVR/{CAMERA}/YYYY-MM-DD
        $base = "/CBR-NFS-VIDEO/CBR-NVR-SRVR/{$baseName}";
        $live = "{$base}/live";
        $dateFolder = date('Y-m-d');
        $dayDir = "{$base}/{$dateFolder}";

        foreach ([$base, $live, $dayDir] as $d) {
            if (!is_dir($d)) {
                @mkdir($d, 0775, true);
            }
        }
        return [$base, $live, $dayDir];
    }
}
