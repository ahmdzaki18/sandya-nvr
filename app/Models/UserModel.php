<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'username','provider','password_hash','display_name','email','ldap_dn',
        'is_active','last_login_at','created_at','updated_at','deleted_at'
    ];
    protected $useTimestamps = false;
    protected $returnType = 'array';
}
