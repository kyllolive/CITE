<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class RegisterController extends BaseController
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
        
        return view('auth/register');
    }
    
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];
        
        $messages = [
            'email' => [
                'is_unique' => 'This email is already registered.'
            ],
            'password_confirm' => [
                'matches' => 'Passwords do not match.'
            ]
        ];
        
        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role' => 'student', // Default role
            'is_active' => true
        ];
        
        $userId = $this->userModel->insert($data);
        
        if (!$userId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create account. Please try again.');
        }
        
        // Optionally, auto-login after registration
        $user = $this->userModel->find($userId);
        $this->setUserSession($user);
        
        return redirect()->to('/dashboard')
            ->with('success', 'Registration successful! Welcome to CITE.');
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
}