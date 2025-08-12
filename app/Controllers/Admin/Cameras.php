<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CameraModel;

class Cameras extends BaseController
{
    private CameraModel $cams;

    /** rules & messages utk validasi form */
    private array $rules = [
        'id'            => 'permit_empty|is_natural', // penting utk placeholder {id}
        'name'          => 'required|min_length[2]|max_length[128]|is_unique[cameras.name,id,{id}]',
        'location'      => 'permit_empty|max_length[191]',
        'host'          => 'required|max_length[191]',
        'port'          => 'required|is_natural_no_zero|greater_than_equal_to[1]|less_than_equal_to[65535]',
        'protocol'      => 'required|in_list[rtsp,rtmp,srt,hls,http-mjpeg,webrtc]',
        'transport'     => 'required|in_list[tcp,udp]',
        'stream_path'   => 'required|max_length[255]',
        'username'      => 'permit_empty|max_length[128]',
        'password'      => 'permit_empty|max_length[255]',
        'fps'           => 'permit_empty|is_natural|less_than_equal_to[120]',
        'audio_enabled' => 'permit_empty|in_list[0,1]',
        'is_recording'  => 'permit_empty|in_list[0,1]',
        'notes'         => 'permit_empty|max_length[5000]',
    ];

    private array $messages = [
        'name' => [
            'required'  => 'Camera name wajib diisi.',
            'is_unique' => 'Nama kamera sudah dipakai.',
        ],
        'host'        => ['required' => 'Host/IP wajib diisi.'],
        'stream_path' => ['required' => 'Stream path/URI wajib diisi.'],
        'port'        => ['required' => 'Port wajib diisi.'],
        'protocol'    => ['in_list'  => 'Protocol tidak valid.'],
        'transport'   => ['in_list'  => 'Transport tidak valid.'],
    ];

    public function __construct()
    {
        $this->cams = new CameraModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $q = trim((string)$this->request->getGet('q'));
        $builder = $this->cams->orderBy('id', 'desc');

        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('location', $q)
                ->orLike('host', $q)
            ->groupEnd();
        }

        $data = [
            'title' => 'Cameras',
            'q'     => $q,
            'list'  => $builder->paginate(15),
            'pager' => $this->cams->pager,
        ];
        return view('admin/cameras/index', $data);
    }

    public function create()
    {
        return view('admin/cameras/form', [
            'title'      => 'Add Camera',
            'item'       => null,
            'validation' => service('validation'),
        ]);
    }

    public function store()
    {
        $post = $this->request->getPost();
        $post['id'] = 0; // untuk placeholder {id}

        if (! $this->validateData($post, $this->rules, $this->messages)) {
            return redirect()->back()->with('error', 'Please fix the form.')->withInput();
        }

        try {
            $payload = $this->payloadFromRequest($post);
            $payload['created_by'] = (int) session('user_id');

            $this->cams->insert($payload, true);
            return redirect()->to('/admin/cameras')->with('success', 'Camera added.');
        } catch (\Throwable $e) {
            log_message('error', 'camera.store: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save camera.')->withInput();
        }
    }

    public function edit($id)
    {
        $item = $this->cams->find($id);
        if (! $item) {
            return redirect()->to('/admin/cameras')->with('error', 'Not found');
        }

        return view('admin/cameras/form', [
            'title'      => 'Edit Camera',
            'item'       => $item,
            'validation' => service('validation'),
        ]);
    }

    public function update($id)
    {
        $item = $this->cams->find($id);
        if (! $item) {
            return redirect()->to('/admin/cameras')->with('error', 'Not found');
        }

        $post = $this->request->getPost();
        $post['id'] = (string) $id; // penting utk is_unique ignore diri sendiri

        if (! $this->validateData($post, $this->rules, $this->messages)) {
            return redirect()->back()->with('error', 'Please fix the form.')->withInput();
        }

        try {
            $payload = $this->payloadFromRequest($post, $item);
            $this->cams->update($id, $payload);
            return redirect()->to('/admin/cameras')->with('success', 'Camera updated.');
        } catch (\Throwable $e) {
            log_message('error', 'camera.update: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update camera.')->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $this->cams->delete($id);
            return redirect()->to('/admin/cameras')->with('success', 'Camera deleted.');
        } catch (\Throwable $e) {
            log_message('error', 'camera.delete: {msg}', ['msg' => $e->getMessage()]);
            return redirect()->to('/admin/cameras')->with('error', 'Delete failed.');
        }
    }

    public function toggle($id)
    {
        $item = $this->cams->find($id);
        if ($item) {
            $this->cams->update($id, ['is_recording' => $item['is_recording'] ? 0 : 1]);
            return redirect()->back()->with('success', 'Recording toggled.');
        }
        return redirect()->back()->with('error', 'Camera not found.');
    }

    // ================== helpers ==================

    private function payloadFromRequest(array $data, ?array $existing = null): array
    {
        $payload = [
            'name'          => trim($data['name'] ?? ''),
            'location'      => $data['location'] ?? null,
            'host'          => trim($data['host'] ?? ''),
            'port'          => (int) ($data['port'] ?? 554),
            'protocol'      => $data['protocol'] ?? 'rtsp',
            'transport'     => $data['transport'] ?? 'tcp',
            'stream_path'   => trim($data['stream_path'] ?? ''),
            'username'      => $data['username'] ?? null,
            'fps'           => ($data['fps'] ?? '') !== '' ? (int) $data['fps'] : null,
            'audio_enabled' => (int) ($data['audio_enabled'] ?? 0),
            'is_recording'  => (int) ($data['is_recording'] ?? 1),
            'notes'         => $data['notes'] ?? null,
        ];

        // password: isi baru overwrite; kosong = keep lama saat edit
        $pwd = (string) ($data['password'] ?? '');
        if ($pwd !== '') {
            $payload['password_enc'] = $this->encrypt($pwd);
        } elseif ($existing) {
            $payload['password_enc'] = $existing['password_enc'];
        }

        return $payload;
    }

    private function encrypt(string $plain): string
    {
        // Enkripsi pakai encrypter (wajib set encryption.key di .env)
        try {
            $enc = service('encrypter');
            return base64_encode($enc->encrypt($plain));
        } catch (\Throwable $e) {
            // fallback (lebih baik hindari; set key saja)
            return base64_encode($plain);
        }
    }

    public static function decrypt(?string $enc): ?string
    {
        if (!$enc) return null;
        $raw = base64_decode($enc, true);
        if ($raw === false) return null;

        try {
            return service('encrypter')->decrypt($raw);
        } catch (\Throwable $e) {
            return $raw; // fallback
        }
    }
}
