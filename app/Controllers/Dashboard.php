<?php
namespace App\Controllers;

use App\Models\CameraModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $cams = (new CameraModel())
            ->select('id,name,location,is_recording')
            ->orderBy('name','asc')
            ->findAll(); // TODO: nanti filter per user

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'cams'  => $cams,
        ]);
    }
}
