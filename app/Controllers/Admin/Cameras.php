<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CameraModel;

class Cameras extends BaseController
{
    private CameraModel $cams;

    public function __construct()
    {
        $this->cams = new CameraModel();
    }

    public function index()
    {
        $q = trim((string)$this->request->getGet('q'));
        $builder = $this->cams->orderBy('id','desc');
        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)->orLike('location', $q)->orLike('host', $q)
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
            'title' => 'Add Camera',
            'item'  => null,
            'validation' => service('validation'),
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $validation = service('validation')->setRules($this->cams->rules);

        if (!$validation->run($data)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $payload = $this->payloadFromRequest($data);
        $payload['created_by'] = (int)session('user_id');

        $this->cams->insert($payload);
        return redirect()->to('/admin/cameras')->with('msg','Camera added.');
    }

    public function edit($id)
    {
        $item = $this->cams->find($id);
        if (!$item) return redirect()->to('/admin/cameras')->with('err','Not found');
        return view('admin/cameras/form', [
            'title' => 'Edit Camera',
            'item'  => $item,
            'validation' => service('validation'),
        ]);
    }

    public function update($id)
    {
        $item = $this->cams->find($id);
        if (!$item) return redirect()->to('/admin/cameras')->with('err','Not found');

        $data = $this->request->getPost();
        $data['id'] = $id; // for unique[name] rule ignore itself
        $validation = service('validation')->setRules($this->cams->rules);

        if (!$validation->run($data)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $payload = $this->payloadFromRequest($data, $item);
        $this->cams->update($id, $payload);
        return redirect()->to('/admin/cameras')->with('msg','Camera updated.');
    }

    public function delete($id)
    {
        $this->cams->delete($id);
        return redirect()->to('/admin/cameras')->with('msg','Camera deleted.');
    }

    public function toggle($id)
    {
        $item = $this->cams->find($id);
        if ($item) {
            $this->cams->update($id, ['is_recording' => $item['is_recording'] ? 0 : 1]);
        }
        return redirect()->back();
    }

    // ------- helpers -------

    private function payloadFromRequest(array $data, ?array $existing = null): array
    {
        $payload = [
            'name'          => trim($data['name'] ?? ''),
            'location'      => $data['location'] ?? null,
            'host'          => trim($data['host'] ?? ''),
            'port'          => (int)($data['port'] ?? 554),
            'protocol'      => $data['protocol'] ?? 'rtsp',
            'transport'     => $data['transport'] ?? 'tcp',
            'stream_path'   => trim($data['stream_path'] ?? ''),
            'username'      => $data['username'] ?? null,
            'fps'           => $data['fps'] !== '' ? (int)$data['fps'] : null,
            'audio_enabled' => (int)($data['audio_enabled'] ?? 0),
            'is_recording'  => (int)($data['is_recording'] ?? 1),
            'notes'         => $data['notes'] ?? null,
        ];

        // password: isi baru akan overwrite; kosong = keep lama
        $pwd = (string)($data['password'] ?? '');
        if ($pwd !== '') {
            $payload['password_enc'] = $this->encrypt($pwd);
        } elseif ($existing) {
            // keep existing
            $payload['password_enc'] = $existing['password_enc'];
        }

        return $payload;
    }

    private function encrypt(string $plain): string
    {
        // gunakan CI4 encrypter jika key diset; kalau tidak, fallback base64 (lebih baik set key!)
        try {
            $enc = service('encrypter');
            return base64_encode($enc->encrypt($plain));
        } catch (\Throwable $e) {
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
