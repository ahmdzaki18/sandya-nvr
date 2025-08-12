<?php namespace App\Models;

use CodeIgniter\Model;

class CameraModel extends Model
{
    protected $table            = 'cameras';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';     // <<— penting buat view kamu
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'name','location','host','port','protocol','transport','stream_path',
        'username','password_enc','fps','audio_enabled','is_recording',
        'notes','created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
