<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CameraModel;
use CodeIgniter\I18n\Time;

class Dashboard extends BaseController
{
    public function index()
    {
        $role = session('role') ?? '';
        $userId = session('id') ?? 0;

        $camModel = new CameraModel();

        if ($role === 'superadmin') {
            // Ambil semua kamera yang belum dihapus
            $cams = $camModel
                ->where('deleted_at', null)
                ->orderBy('name', 'ASC')
                ->findAll();
        } elseif ($role === 'admin') {
            // Ambil kamera yang dimiliki admin ini
            $cams = $camModel
                ->where('deleted_at', null)
                ->where('user_id', $userId)
                ->orderBy('name', 'ASC')
                ->findAll();
        } else {
            // User biasa: ambil kamera yang di-assign
            $cams = $camModel
                ->select('cameras.*')
                ->join('camera_user', 'camera_user.camera_id = cameras.id')
                ->where('camera_user.user_id', $userId)
                ->where('cameras.deleted_at', null)
                ->orderBy('cameras.name', 'ASC')
                ->findAll();
        }

        return view('admin/dashboard', [
            'role' => $role,
            'cams' => $cams
        ]);
    }
}
