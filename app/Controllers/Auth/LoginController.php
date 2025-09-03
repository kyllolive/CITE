<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class LoginController extends BaseController
{
    protected $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function index()
    {
        if ($this->session->has('user_id')) {
            return redirect()->to('/dashboard');
        }
        
        return view('auth/login');
    }
    
    public function authenticate()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }
        
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember') ? true : false;
        
        $user = $this->userModel->verifyPassword($email, $password);
        
        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid email or password.');
        }
        
        if (!$user['is_active']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Your account is not active. Please contact administrator.');
        }
        
        // Set session data
        $this->setUserSession($user);
        
        // Handle remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->updateRememberToken($user['id'], $token);
            
            // Set remember me cookie for 30 days
            $this->response->setCookie(
                'remember_token',
                $token,
                60 * 60 * 24 * 30
            );
        }
        
        // Redirect based on role
        return $this->redirectBasedOnRole($user['role']);
    }
    
    protected function setUserSession($user)
    {
        $sessionData = [
            'user_id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_logged_in' => true
        ];
        
        $this->session->set($sessionData);
    }
    
    protected function redirectBasedOnRole($role)
    {
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
    
    public function logout()
    {
        // Clear remember token
        if ($this->session->has('user_id')) {
            $this->userModel->updateRememberToken($this->session->get('user_id'), null);
        }
        
        // Destroy session
        $this->session->destroy();
        
        // Delete remember cookie
        $this->response->deleteCookie('remember_token');
        
        return redirect()->to('/auth/login')->with('success', 'You have been logged out successfully.');
    }
}