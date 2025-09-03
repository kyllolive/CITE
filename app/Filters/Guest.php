<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Guest implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // If user is already logged in, redirect to dashboard
        if ($session->has('user_id') && $session->get('is_logged_in')) {
            $role = $session->get('role');
            
            switch ($role) {
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                case 'instructor':
                    return redirect()->to('/instructor/dashboard');
                case 'student':
                    return redirect()->to('/student/dashboard');
                default:
                    return redirect()->to('/dashboard');
            }
        }
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}