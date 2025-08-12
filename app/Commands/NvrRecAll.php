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

        if (!$cameras || count($cameras) === 0) {
            CLI::error("No active cameras found.");
            return;
        }

        foreach ($cameras as $cam) {
            $id   = $cam['id'];
            $name = $cam['name'];

            CLI::write("Starting Camera {$id} ({$name})...");

            // Jalankan tiap kamera di background
            $cmd = sprintf(
                'nohup php %sspark nvr:rec %d > /var/log/nvr-rec-%d.log 2>&1 &',
                FCPATH,
                $id,
                $id
            );

            shell_exec($cmd);
        }
    }
}
