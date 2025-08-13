<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserRoleModel;

class Auth extends BaseController
{
    /** halaman login (GET) */
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login', ['title' => 'Login']);
    }

    /** proses login (POST) */
    public function attemptLogin()
    {
        $req = $this->request;

        $username = trim((string) $req->getPost('username'));
        $password = (string) $req->getPost('password');

        if ($username === '' || $password === '') {
            return redirect()->back()->with('error', 'Username / password wajib diisi.')->withInput();
        }

        $users = new UserModel();
        $user  = $users->where('username', $username)
                       ->where('deleted_at', null)
                       ->first();

        // user tidak ada
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.')->withInput();
        }

        // nonaktif
        if (empty($user['is_active'])) {
            return redirect()->back()->with('error', 'Akun nonaktif.')->withInput();
        }

        // local password check (untuk provider=local). LDAP akan kita garap terpisah.
        $ok = password_verify($password, (string) ($user['password_hash'] ?? ''));
        if (!$ok) {
            return redirect()->back()->with('error', 'Password salah.')->withInput();
        }

        // AMBIL ROLE dari tabel pivot user_roles -> roles
        $roleName = (new UserRoleModel())->getRoleNameByUserId((int) $user['id']) ?? 'user';

        // set session
        session()->set([
            'isLoggedIn' => true,
            'user_id'    => (int) $user['id'],
            'username'   => $user['username'],
            'display'    => $user['display_name'] ?? $user['username'],
            'email'      => $user['email'] ?? '',
            'role'       => $roleName, // <— hanya dari tabel roles
        ]);

        // update last_login_at
        $users->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/dashboard');
    }

    /** logout (GET/POST) */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
