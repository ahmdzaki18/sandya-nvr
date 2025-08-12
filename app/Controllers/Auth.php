<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Libraries\LdapLib;

class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function doLogin()
    {
        $username = trim($this->request->getPost('username') ?? '');
        $password = (string)($this->request->getPost('password') ?? '');

        $users = new UserModel();
        $user  = $users->where('username',$username)->where('is_active',1)->first();

        // Try LOCAL first if exists as local
        if ($user && $user['provider']==='local') {
            if (password_verify($password, $user['password_hash'] ?? '')) {
                $this->setSession($user);
                return redirect()->to('/');
            }
        }

        // Try LDAP
        $ldap = new LdapLib();
        $info = $ldap->login($username, $password);
        if ($info !== false) {
            // upsert user as ldap provider (cache display_name/email)
            if (!$user) {
                $uid = $users->insert([
                    'username'     => $username,
                    'provider'     => 'ldap',
                    'display_name' => $info['nama_lengkap'],
                    'email'        => $info['email'],
                    'ldap_dn'      => $info['dn'],
                    'is_active'    => 1,
                ], true);
                $user = $users->find($uid);
            } else {
                // refresh cache silently
                $users->update($user['id'], [
                    'provider'=>'ldap',
                    'display_name'=>$info['nama_lengkap'],
                    'email'=>$info['email'],
                    'ldap_dn'=>$info['dn'],
                ]);
                $user = $users->find($user['id']);
            }
            $this->setSession($user);
            return redirect()->to('/');
        }

        return redirect()->back()->with('error','Invalid credentials')->withInput();
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }

    private function setSession(array $user): void
    {
        // fetch role name
        $db = db_connect();
        $role = $db->table('user_roles ur')
                   ->select('r.name')
                   ->join('roles r','r.id=ur.role_id','left')
                   ->where('ur.user_id',$user['id'])->get()->getRowArray()['name'] ?? null;

        session()->set([
            'user_id'   => $user['id'],
            'username'  => $user['username'],
            'display'   => $user['display_name'] ?? $user['username'],
            'role'      => $role,
            'provider'  => $user['provider'],
            'logged_in' => true,
        ]);

        $users = new UserModel();
        $users->update($user['id'], ['last_login_at'=>date('Y-m-d H:i:s')]);
    }
}
