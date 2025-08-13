<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'username',
        'provider',
        'password_hash',
        'display_name',
        'email',
        'ldap_dn',
        'is_active',
        'last_login_at',
        'created_at',
        'updated_at',
        'deleted_at',
        // kalau kamu punya 'role' atau kolom lain, tambahkan di sini
        'role',
    ];

    protected $useTimestamps = true; // pakai created_at, updated_at otomatis
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
