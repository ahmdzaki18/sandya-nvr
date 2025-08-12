<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\NvrService;

class NvrRec extends BaseCommand
{
    protected $group       = 'nvr';
    protected $name        = 'nvr:rec';
    protected $description = 'Record a camera stream';
    protected $usage       = 'nvr:rec <camera_id>';
    protected $arguments   = [
        'camera_id' => 'ID of the camera to record'
    ];

    public function run(array $params)
    {
        $cameraId = $params[0] ?? null;

        if (!$cameraId) {
            CLI::error('Camera ID is required.');
            return;
        }

        $service = new NvrService();
        $service->record((int) $cameraId);
    }
}
