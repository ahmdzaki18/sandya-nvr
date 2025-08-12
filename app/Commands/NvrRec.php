<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CameraModel;

class NvrRec extends BaseCommand
{
    protected $group       = 'nvr';
    protected $name        = 'nvr:rec';
    protected $description = 'Record specific camera stream.';
    protected $usage       = 'nvr:rec [camera_id]';
    protected $arguments   = ['camera_id' => 'Camera ID from database'];

    public function run(array $params)
    {
        $id = (int)($params[0] ?? 0);
        if (!$id) {
            CLI::error('Camera ID is required.');
            return;
        }

        $camera = model(CameraModel::class)->find($id);
        if (!$camera || $camera->deleted_at) {
            CLI::error("Camera $id not found");
            return;
        }

        $base = "/CBR-NFS-VIDEO/CBR-NVR-SRVR/{$camera->name}";
        $live = "$base/live";
        $today = date('Y-m-d');
        $daydir = "$base/$today";

        @mkdir($live, 0775, true);
        @mkdir($daydir, 0775, true);

        $username = rawurlencode($camera->username ?? '');
        $password = '';
        if (!empty($camera->password_enc)) {
            $password = rawurlencode(service('encrypter')->decrypt(base64_decode($camera->password_enc)));
        }

        $auth = ($username && $password) ? "$username:$password@" : (($username) ? "$username@" : '');

        $input = match ($camera->protocol) {
            'rtsp' => "rtsp://{$auth}{$camera->host}:{$camera->port}{$camera->stream_path}",
            'rtmp' => "rtmp://{$camera->host}:{$camera->port}{$camera->stream_path}",
            'hls'  => "http://{$camera->host}:{$camera->port}{$camera->stream_path}",
            default => "rtsp://{$camera->host}:{$camera->port}{$camera->stream_path}",
        };

        CLI::write("Starting recording for Camera {$id} ({$camera->name})");

        while (true) {
            // cek status di DB
            $status = model(CameraModel::class)->select('is_recording')->find($id)->is_recording;
            if (!$status) {
                CLI::write("Recording stopped for Camera {$id}");
                break;
            }

            $cmd = [
                'ffmpeg',
                '-hide_banner', '-nostdin',
                '-rtsp_transport', $camera->transport ?? 'tcp',
                '-i', $input,
                '-map', '0', '-c', 'copy', '-vsync', '1', '-copyts',
                '-f', 'segment', '-segment_time', '900', '-reset_timestamps', '1', '-strftime', '1', "$daydir/%H%M%S.mp4",
                '-map', '0', '-c', 'copy', '-vsync', '1',
                '-f', 'hls', '-hls_time', '2', '-hls_list_size', '30',
                '-hls_flags', 'delete_segments+append_list', "$live/index.m3u8",
                '-vf', 'fps=1/10', '-update', '1', "$live/preview.jpg"
            ];

            $process = proc_open($cmd, [STDIN, STDOUT, STDERR], $pipes);
            if (is_resource($process)) {
                proc_close($process);
            }

            sleep(2); // biar ga looping terlalu cepet
        }
    }
}
