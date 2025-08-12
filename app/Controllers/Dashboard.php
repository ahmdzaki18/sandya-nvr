<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CameraModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $role = (string)(session('role') ?? '');

        /** @var CameraModel $cams */
        $camsModel = model(CameraModel::class);

        if ($role === 'superadmin') {
            $cams = $camsModel->asArray()->orderBy('name','asc')->findAll();
        } elseif ($role === 'admin') {
            // kalau mau limit ke kamera yg dia buat, pakai created_by
            $uid  = (int)(session('user_id') ?? 0);
            $cams = $camsModel->asArray()
                ->when($uid > 0, fn($b) => $b->where('created_by', $uid))
                ->orderBy('name','asc')
                ->findAll();
        } else {
            // user biasa: sementara kosong (nantinya bakal pakai tabel assignment)
            $cams = [];
        }

        return view('dashboard/index', [
            'title' => 'Dashboard',
            'role'  => $role,
            'cams'  => $cams,
        ]);
    }
}
