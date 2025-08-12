<?php
namespace App\Controllers;

use App\Models\CameraModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role = (string)(session('role') ?? '');
        $cams = [];

        if (in_array($role, ['admin', 'superadmin'], true)) {
            $cameraModel = new CameraModel();

            if ($role === 'superadmin') {
                // Superadmin lihat semua kamera
                $cams = $cameraModel
                    ->select('id, name, location, is_recording')
                    ->orderBy('name', 'asc')
                    ->asArray() // <── penting supaya sesuai view
                    ->findAll();
            } else {
                // Admin lihat kamera yang di-assign
                $cams = $cameraModel
                    ->select('id, name, location, is_recording')
                    ->where('created_by', session('user_id'))
                    ->orderBy('name', 'asc')
                    ->asArray() // <── ini juga
                    ->findAll();
            }
        }

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'role'  => $role,
            'cams'  => $cams,
        ]);
    }
}
