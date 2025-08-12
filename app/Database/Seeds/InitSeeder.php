<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitSeeder extends Seeder
{
    public function run()
    {
        // roles
        $this->db->table('roles')->ignore(true)->insertBatch([
            ['id'=>1,'name'=>'superadmin','description'=>'full access'],
            ['id'=>2,'name'=>'admin','description'=>'manage assignments, view'],
            ['id'=>3,'name'=>'user','description'=>'view-only'],
        ]);

        // superadmin (local) - ganti password setelah login
        $hash = password_hash('changeme123', PASSWORD_BCRYPT);
        $this->db->table('users')->ignore(true)->insert([
            'id' => 1,
            'username' => 'superadmin',
            'provider' => 'local',
            'password_hash' => $hash,
            'display_name' => 'Super Admin',
            'email' => 'superadmin@sandya.net',
            'is_active' => 1,
        ]);

        // map role
        $this->db->table('user_roles')->ignore(true)->insert([
            'user_id'=>1,
            'role_id'=>1,
            'assigned_at'=>date('Y-m-d H:i:s'),
        ]);
    }
}
