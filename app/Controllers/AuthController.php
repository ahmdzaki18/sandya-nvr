<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login', ['title' => 'Login']);
    }

    public function attemptLogin()
    {
        $session  = session();
        $request  = $this->request;

        $username = trim((string) $request->getPost('username'));
        $password = (string) $request->getPost('password');

        if ($username === '' || $password === '') {
            return redirect()->back()->with('error', 'Username / password wajib diisi.');
        }

        $users = new UserModel();
        $user  = $users->where('username', $username)
                       ->where('is_active', 1)
                       ->first();

        // 1) Coba "local" dulu (jika ada dan provider=local)
        if ($user && ($user['provider'] ?? 'local') === 'local') {
            if (!password_verify($password, $user['password_hash'])) {
                return redirect()->back()->with('error', 'Password salah.');
            }
            return $this->loginSuccess($user);
        }

        // 2) Coba LDAP
        $ldapOK = false;
        $ldapInfo = null;

        // config dari .env (silakan sesuaikan)
        $host     = env('ldap.host', 'ldap://your-ldap-host'); // ex: ldap://10.0.0.5
        $port     = (int) env('ldap.port', 389);
        $baseDN   = env('ldap.base_dn', 'dc=example,dc=com');
        $userAttr = env('ldap.user_attr', 'uid'); // atau 'sAMAccountName' untuk AD
        $bindTpl  = env('ldap.bind_dn_tpl', '%s@yourdomain.local'); // untuk AD, atau kosong kalau pakai bind DN hasil search
        $useTLS   = (bool) env('ldap.start_tls', false);

        if (function_exists('ldap_connect')) {
            $conn = @ldap_connect($host, $port);
            if ($conn) {
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

                if ($useTLS) {
                    @ldap_start_tls($conn);
                }

                // ==== BIND: 2 skenario
                // A) AD: langsung bind pakai user@domain
                // B) Generic: cari DN dulu, lalu bind pakai DN
                $bindRdn = $bindTpl ? sprintf($bindTpl, $username) : null;
                $bindOK  = false;

                if ($bindRdn) {
                    $bindOK = @ldap_bind($conn, $bindRdn, $password);
                } else {
                    // cari DN
                    $filter = sprintf("(%s=%s)", $userAttr, ldap_escape($username, '', LDAP_ESCAPE_FILTER));
                    $search = @ldap_search($conn, $baseDN, $filter, ['cn','displayName','mail','userPrincipalName','givenName','sn']);
                    if ($search) {
                        $entries = ldap_get_entries($conn, $search);
                        if ($entries && $entries['count'] > 0) {
                            $dn = $entries[0]['dn'];
                            $ldapInfo = [
                                'displayName' => $entries[0]['displayname'][0] ?? ($entries[0]['cn'][0] ?? $username),
                                'email'       => $entries[0]['mail'][0] ?? ($entries[0]['userprincipalname'][0] ?? null),
                                'dn'          => $dn,
                            ];
                            $bindOK = @ldap_bind($conn, $dn, $password);
                        }
                    }
                }

                if ($bindOK) {
                    $ldapOK = true;
                    // Kalau belum punya display/email (mode AD bind langsung), coba search ambil info
                    if ($ldapInfo === null) {
                        $filter = sprintf("(%s=%s)", $userAttr, ldap_escape($username, '', LDAP_ESCAPE_FILTER));
                        $search = @ldap_search($conn, $baseDN, $filter, ['cn','displayName','mail','userPrincipalName']);
                        if ($search) {
                            $entries = ldap_get_entries($conn, $search);
                            if ($entries && $entries['count'] > 0) {
                                $ldapInfo = [
                                    'displayName' => $entries[0]['displayname'][0] ?? ($entries[0]['cn'][0] ?? $username),
                                    'email'       => $entries[0]['mail'][0] ?? ($entries[0]['userprincipalname'][0] ?? null),
                                    'dn'          => $entries[0]['dn'],
                                ];
                            }
                        }
                    }
                }

                @ldap_unbind($conn);
            }
        }

        if (!$ldapOK) {
            return redirect()->back()->with('error', 'Login gagal. (Local/LDAP)');
        }

        // Upsert user LDAP ke DB
        $payload = [
            'username'     => $username,
            'provider'     => 'ldap',
            'display_name' => $ldapInfo['displayName'] ?? $username,
            'email'        => $ldapInfo['email'] ?? null,
            'ldap_dn'      => $ldapInfo['dn'] ?? null,
            'is_active'    => 1,
        ];

        if ($user) {
            $users->update($user['id'], $payload);
            $user = $users->find($user['id']);
        } else {
            $id   = $users->insert($payload, true);
            $user = $users->find($id);
        }

        return $this->loginSuccess($user);
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }

    private function loginSuccess(array $user)
    {
        $users = new UserModel();

        // update last_login_at
        $users->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        // set session
        session()->regenerate(true);
        session()->set([
            'isLoggedIn'  => true,
            'user_id'     => $user['id'],
            'username'    => $user['username'],
            'display'     => $user['display_name'] ?? $user['username'],
            'email'       => $user['email'] ?? null,
            'role'        => $user['role'] ?? 'user', // kalau ada kolom role; kalau tidak, aman
        ]);

        // redirect ke intended kalau ada
        $intended = session('intended');
        if ($intended) {
            session()->remove('intended');
            return redirect()->to($intended);
        }
        return redirect()->to('/dashboard');
    }
}
