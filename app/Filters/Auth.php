<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Check if user is logged in
        if (!$session->has('user_id') || !$session->get('is_logged_in')) {
            // Check for remember token
            $rememberToken = $request->getCookie('remember_token');
            
            if ($rememberToken) {
                $userModel = new \App\Models\UserModel();
                $user = $userModel->findByRememberToken($rememberToken);
                
                if ($user && $user['is_active']) {
                    // Restore session
                    $this->setUserSession($user);
                    return;
                }
            }
            
            // Store intended URL for redirect after login
            if ($request->getMethod() === 'get') {
                $session->set('intended_url', current_url());
            }
            
            return redirect()->to('/auth/login')->with('error', 'Please login to continue.');
        }
        
        // Check for role-based access if arguments provided
        if ($arguments) {
            $userRole = $session->get('role');
            $allowedRoles = is_array($arguments) ? $arguments : [$arguments];
            
            if (!in_array($userRole, $allowedRoles)) {
                return redirect()->to('/unauthorized')->with('error', 'You do not have permission to access this resource.');
            }
        }
    }
    
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
    
    protected function setUserSession($user)
    {
        $session = session();
        $sessionData = [
            'user_id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_logged_in' => true
        ];
        
        $session->set($sessionData);
    }
}