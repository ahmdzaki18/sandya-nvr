<?php
namespace App\Controllers;

use App\Models\CameraModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role = (string) (session('role') ?? '');

        $cams = [];
        if (in_array($role, ['admin','superadmin'], true)) {
            $cams = (new CameraModel())
                ->select('id,name,location,is_recording')
                ->orderBy('name','asc')
                ->findAll();
        }

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'role'  => $role,
            'cams'  => $cams,
        ]);
    }
}
