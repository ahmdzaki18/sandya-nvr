<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
	{
		$session = session();
	
		if (!$session->get('isLoggedIn')) {
			// Kalau bukan request AJAX, redirect ke login
			if (!$request->isAJAX()) {
				return redirect()->to('/login');
			}
			// Kalau AJAX, bisa kasih JSON error
			return Services::response()
				->setStatusCode(401)
				->setJSON(['error' => 'Unauthorized']);
		}
	}
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
