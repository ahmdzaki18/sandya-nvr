<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!$arguments) return;
        $need = $arguments[0]; // 'superadmin' | 'admin' | 'user'
        $role = session('role') ?? '';
        $order = ['user'=>1,'admin'=>2,'superadmin'=>3];

        if (!isset($order[$role]) || $order[$role] < $order[$need]) {
            return service('response')->setStatusCode(403)->setBody('Forbidden');
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
