<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\MoodleAuth;

class RegisterController extends BaseController
{
    protected $userModel;
    protected $moodleAuth;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->moodleAuth = new MoodleAuth();
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
        
        $plainPassword = $this->request->getPost('password');
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $plainPassword,
            'role' => 'student', // Default role
            'is_active' => true
        ];
        
        // Create user in Moodle if integration is enabled
        if ($this->moodleAuth->isEnabled()) {
            try {
                $moodleId = $this->moodleAuth->createMoodleUser([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $plainPassword // Pass plain password for Moodle
                ]);
                
                if ($moodleId) {
                    $data['moodle_id'] = $moodleId;
                    $data['last_moodle_sync'] = date('Y-m-d H:i:s');
                    
                    // Generate username for Moodle
                    $username = strtolower(str_replace(' ', '', $data['name']));
                    $data['moodle_username'] = $username;
                }
            } catch (\Exception $e) {
                // Log error but don't fail registration if Moodle is down
                log_message('error', 'Failed to create Moodle user during registration: ' . $e->getMessage());
            }
        }
        
        $userId = $this->userModel->insert($data);
        
        if (!$userId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create account. Please try again.');
        }
        
        // Auto-login after registration
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