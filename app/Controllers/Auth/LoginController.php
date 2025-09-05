<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Libraries\MoodleAuth;
use App\Libraries\MoodleSSO;

class LoginController extends BaseController
{
    protected $userModel;
    protected $moodleAuth;
    protected $moodleSSO;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->moodleAuth = new MoodleAuth();
        $this->moodleSSO = new MoodleSSO();
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
        
        // Sync with Moodle if enabled
        if ($this->moodleAuth->isEnabled()) {
            try {
                // Authenticate with Moodle and get token
                $moodleData = $this->moodleAuth->authenticateAndSync($email, $password);
                
                if ($moodleData) {
                    // Update user with Moodle data
                    $this->userModel->update($user['id'], [
                        'moodle_id' => $moodleData['id'] ?? $user['moodle_id'],
                        'moodle_token' => $moodleData['token'] ?? null,
                        'moodle_username' => $moodleData['username'] ?? null,
                        'last_moodle_sync' => date('Y-m-d H:i:s')
                    ]);
                    
                    // Add Moodle data to user array for session
                    $user['moodle_id'] = $moodleData['id'] ?? $user['moodle_id'];
                    $user['moodle_token'] = $moodleData['token'] ?? null;
                } else {
                    // If Moodle auth fails but CITE auth succeeds, sync user to Moodle
                    $this->moodleAuth->syncUser($user['id']);
                }
            } catch (\Exception $e) {
                // Log error but don't fail login if Moodle is down
                log_message('error', 'Moodle sync failed during login: ' . $e->getMessage());
            }
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
    
    /**
     * Initiate Moodle SSO login
     */
    public function moodle()
    {
        if (!$this->moodleSSO->isEnabled()) {
            return redirect()->to('/auth/login')
                ->with('error', 'Moodle SSO is not enabled.');
        }
        
        // Store redirect URL in session if provided
        $redirectUrl = $this->request->getGet('redirect') ?: '/dashboard';
        $this->session->set('moodle_sso_redirect', $redirectUrl);
        
        // Redirect to Moodle for authentication
        $moodleUrl = getenv('MOODLE_URL') ?: 'http://localhost:8081';
        $callbackUrl = base_url('/auth/moodle/callback');
        
        // Build Moodle SSO URL with callback
        $ssoUrl = $moodleUrl . '/auth/cite/login.php?' . http_build_query([
            'callback' => $callbackUrl,
            'service' => 'cite_sso'
        ]);
        
        return redirect()->to($ssoUrl);
    }
    
    /**
     * Handle Moodle SSO callback
     */
    public function moodleCallback()
    {
        if (!$this->moodleSSO->isEnabled()) {
            return redirect()->to('/auth/login')
                ->with('error', 'Moodle SSO is not enabled.');
        }
        
        $token = $this->request->getGet('token');
        $error = $this->request->getGet('error');
        
        if ($error) {
            return redirect()->to('/auth/login')
                ->with('error', 'Moodle authentication failed: ' . $error);
        }
        
        if (!$token) {
            return redirect()->to('/auth/login')
                ->with('error', 'No authentication token received from Moodle.');
        }
        
        // Verify the token
        $userData = $this->moodleSSO->verifySignedToken($token);
        
        if (!$userData) {
            return redirect()->to('/auth/login')
                ->with('error', 'Invalid or expired authentication token.');
        }
        
        try {
            // Find or create user based on Moodle data
            $user = $this->userModel->where('email', $userData['email'])->first();
            
            if (!$user) {
                // Create new user from Moodle data
                $newUserData = [
                    'name' => trim($userData['firstname'] . ' ' . $userData['lastname']),
                    'email' => $userData['email'],
                    'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    'role' => $this->mapMoodleRoleToLocal($userData['role'] ?? 'student'),
                    'is_active' => true,
                    'email_verified_at' => date('Y-m-d H:i:s')
                ];
                
                $userId = $this->userModel->save($newUserData);
                if (!$userId) {
                    throw new \Exception('Failed to create user account');
                }
                
                $user = $this->userModel->find($this->userModel->getInsertID());
            }
            
            if (!$user['is_active']) {
                return redirect()->to('/auth/login')
                    ->with('error', 'Your account is not active. Please contact administrator.');
            }
            
            // Update user with Moodle sync data
            $this->userModel->update($user['id'], [
                'last_moodle_sync' => date('Y-m-d H:i:s'),
                'moodle_id' => $userData['moodle_id'] ?? $user['moodle_id']
            ]);
            
            // Set session
            $this->setUserSession($user);
            
            // Get redirect URL and clear from session
            $redirectUrl = $this->session->get('moodle_sso_redirect') ?: '/dashboard';
            $this->session->remove('moodle_sso_redirect');
            
            return redirect()->to($redirectUrl)
                ->with('success', 'Successfully logged in with Moodle!');
                
        } catch (\Exception $e) {
            log_message('error', 'Moodle SSO callback failed: ' . $e->getMessage());
            
            return redirect()->to('/auth/login')
                ->with('error', 'Authentication failed. Please try again.');
        }
    }
    
    /**
     * Map Moodle role to local application role
     */
    protected function mapMoodleRoleToLocal(string $moodleRole): string
    {
        $roleMap = [
            'manager' => 'admin',
            'editingteacher' => 'instructor',
            'teacher' => 'instructor',
            'student' => 'student'
        ];
        
        return $roleMap[$moodleRole] ?? 'student';
    }
}