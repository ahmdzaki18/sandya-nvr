<?php
namespace App\Controllers;

use App\Models\CameraModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role = session('role') ?? 'user';

        // superadmin & admin: semua kamera
        if ($role === 'superadmin' || $role === 'admin') {
            $cams = (new CameraModel())
                ->select('id,name,location,is_recording')
                ->orderBy('name','asc')
                ->findAll();
        } else {
            // user biasa: kosong (nanti based on assignment)
            $cams = [];
        }

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'cams'  => $cams,
            'role'  => $role,
        ]);
    }
}
