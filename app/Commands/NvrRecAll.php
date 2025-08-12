<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CameraModel;

class NvrRecAll extends BaseCommand
{
    protected $group       = 'nvr';
    protected $name        = 'nvr:rec-all';
    protected $description = 'Record all active cameras.';
    protected $usage       = 'nvr:rec-all';

    public function run(array $params)
    {
        $cameras = model(CameraModel::class)
            ->where('deleted_at', null)
            ->where('is_recording', 1)
            ->findAll();

        if (!$cameras) {
            CLI::error("No active cameras found.");
            return;
        }

        foreach ($cameras as $cam) {
            $cmd = sprintf(
                'php %s spark nvr:rec %d > /var/log/nvr-rec-%d.log 2>&1 &',
                FCPATH,
                $cam->id,
                $cam->id
            );
            CLI::write("Starting Camera {$cam->id} ({$cam->name})...");
            shell_exec($cmd);
        }
    }
}
