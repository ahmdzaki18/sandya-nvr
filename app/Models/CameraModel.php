<?php
namespace App\Models;

use CodeIgniter\Model;

class CameraModel extends Model
{
    protected $table         = 'cameras';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes= true;
    protected $allowedFields = [
        'name','location','host','port','protocol','transport','stream_path',
        'username','password_enc','fps','audio_enabled','is_recording','notes','created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public array $rules = [
        'name'        => 'required|min_length[2]|max_length[128]|is_unique[cameras.name,id,{id}]',
        'host'        => 'required|max_length[191]',
        'port'        => 'required|is_natural_no_zero|greater_than_equal_to[1]|less_than_equal_to[65535]',
        'protocol'    => 'required|in_list[rtsp,rtmp,srt,hls,http-mjpeg,webrtc]',
        'transport'   => 'required|in_list[tcp,udp]',
        'stream_path' => 'required|max_length[255]',
        'fps'         => 'permit_empty|is_natural|less_than_equal_to[120]',
        'audio_enabled' => 'permit_empty|in_list[0,1]',
        'is_recording'  => 'permit_empty|in_list[0,1]',
    ];
}
