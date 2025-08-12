<?php
namespace App\Controllers;

use App\Models\CameraModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role = session('role') ?? 'user';
        $cams = (new CameraModel())
            ->select('id,name,location,is_recording')
            ->orderBy('name','asc')
            ->findAll(); // TODO: filter by user dashboards/ACL

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'cams'  => $cams,
        ]);
    }
}
