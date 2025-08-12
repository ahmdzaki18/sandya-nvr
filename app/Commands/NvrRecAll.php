<?php namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CameraModel;

class NvrRecAll extends BaseCommand
{
    protected $group       = 'nvr';
    protected $name        = 'nvr:rec-all';
    protected $description = 'Start recorders for all cameras with is_recording=1 (parallel).';

    public function run(array $params)
    {
        $cams = model(CameraModel::class)
            ->asArray()
            ->where('is_recording', 1)
            ->findAll();

        if (!$cams) {
            CLI::write('No active cameras found.');
            return 0;
        }

        $procs = [];
        foreach ($cams as $c) {
            $cmd = sprintf('/usr/bin/php %s/spark nvr:rec %d',
                ROOTPATH, (int)$c['id']
            );

            // jalankan background process, simpan resource
            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['file', '/dev/null', 'a'],
                2 => ['file', '/dev/null', 'a'],
            ];
            $procs[(int)$c['id']] = proc_open($cmd, $descriptors, $pipes);
        }

        // tunggu sampai semua child selesai
        while (!empty($procs)) {
            foreach ($procs as $id => $proc) {
                if (!\is_resource($proc)) {
                    unset($procs[$id]);
                    continue;
                }
                $status = proc_get_status($proc);
                if ($status === false || $status['running'] === false) {
                    proc_close($proc);
                    unset($procs[$id]);
                }
            }
            sleep(2);
        }

        return 0;
    }
}
