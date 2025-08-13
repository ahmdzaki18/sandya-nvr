<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table         = 'user_roles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'role_id', 'assigned_by', 'assigned_at'];

    /**
     * Ambil NAMA role untuk user tertentu.
     * Return: string|null  (mis. 'superadmin' | 'admin' | 'user' | null)
     */
    public function getRoleNameByUserId(int $userId): ?string
    {
        $row = $this->db->table($this->table . ' ur')
            ->select('r.name AS role')
            ->join('roles r', 'r.id = ur.role_id', 'inner')
            ->where('ur.user_id', $userId)
            ->orderBy('ur.assigned_at', 'DESC')
            ->get(1)
            ->getRowArray();

        return $row['role'] ?? null;
    }
}
