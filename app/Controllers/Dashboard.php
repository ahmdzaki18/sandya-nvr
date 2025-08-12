<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CameraModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role   = (string) (session('role') ?? '');
        $userId = (int) (session('user_id') ?? 0);

        $cam = model(CameraModel::class);

        if ($role === 'superadmin') {
            // semua kamera, termasuk yang is_recording = 0
            $cams = $cam->asArray()
                        ->where('deleted_at', null)
                        ->orderBy('name','asc')
                        ->findAll();
        } elseif ($role === 'admin') {
            // kalau mau dibatasi: hanya kamera yang dibuat oleh admin tsb
            // kalau mau semua kamera juga, tinggal samain seperti superadmin
            $cams = $cam->asArray()
                        ->where('deleted_at', null)
                        ->groupStart()
                            ->where('created_by', $userId) // boleh dihapus kalau mau lihat semuanya
                            ->orWhere('created_by', null)
                        ->groupEnd()
                        ->orderBy('name','asc')
                        ->findAll();
        } else {
            // user biasa (nanti join ke tabel assignment)
            $cams = [];
        }

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'role'  => $role,
            'cams'  => $cams,
        ]);
    }
}
