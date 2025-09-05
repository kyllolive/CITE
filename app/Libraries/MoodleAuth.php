<?php

namespace App\Libraries;

use App\Models\UserModel;
use Exception;

class MoodleAuth
{
    protected MoodleAPI $moodleApi;
    protected UserModel $userModel;
    
    public function __construct()
    {
        $this->moodleApi = new MoodleAPI();
        $this->userModel = new UserModel();
    }
    
    /**
     * Authenticate user with Moodle and sync data
     */
    public function authenticateAndSync(string $email, string $password): ?array
    {
        try {
            // Get Moodle user by email
            $moodleUser = $this->moodleApi->getUserByEmail($email);
            
            if (!$moodleUser) {
                return null;
            }
            
            // Verify password using Moodle's authentication
            $token = $this->getMoodleToken($email, $password);
            
            if (!$token) {
                return null;
            }
            
            // Return Moodle user data with token
            return array_merge($moodleUser, ['token' => $token]);
            
        } catch (Exception $e) {
            log_message('error', 'Moodle auth failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create user in Moodle when registering in CITE
     */
    public function createMoodleUser(array $userData): ?int
    {
        try {
            $moodleUserData = [
                'username' => strtolower(str_replace(' ', '', $userData['name'])),
                'email' => $userData['email'],
                'firstname' => explode(' ', $userData['name'])[0] ?? $userData['name'],
                'lastname' => explode(' ', $userData['name'])[1] ?? '',
                'password' => $userData['password'], // Plain password before hashing
                'auth' => 'manual',
                'lang' => 'en',
                'country' => 'US'
            ];
            
            // Check if user already exists in Moodle
            $existingUser = $this->moodleApi->getUserByEmail($userData['email']);
            if ($existingUser) {
                return $existingUser['id'];
            }
            
            // Create new Moodle user
            return $this->moodleApi->createUser($moodleUserData);
            
        } catch (Exception $e) {
            log_message('error', 'Failed to create Moodle user: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Sync user data between CITE and Moodle
     */
    public function syncUser(int $citeUserId): bool
    {
        try {
            $user = $this->userModel->find($citeUserId);
            if (!$user) {
                return false;
            }
            
            // Check if user has Moodle ID
            if (!$user['moodle_id']) {
                // Try to find user in Moodle by email
                $moodleUser = $this->moodleApi->getUserByEmail($user['email']);
                
                if ($moodleUser) {
                    // Update CITE user with Moodle ID
                    $this->userModel->update($citeUserId, [
                        'moodle_id' => $moodleUser['id']
                    ]);
                    return true;
                }
                
                // Create user in Moodle if not exists
                $moodleId = $this->createMoodleUser([
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => bin2hex(random_bytes(8)) // Generate random password
                ]);
                
                if ($moodleId) {
                    $this->userModel->update($citeUserId, [
                        'moodle_id' => $moodleId
                    ]);
                    return true;
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'User sync failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get Moodle token for user authentication
     */
    protected function getMoodleToken(string $username, string $password): ?string
    {
        try {
            $moodleUrl = getenv('MOODLE_URL') ?: 'http://moodle:8081';
            $tokenUrl = $moodleUrl . '/login/token.php';
            
            $params = [
                'username' => $username,
                'password' => $password,
                'service' => 'moodle_mobile_app' // Default service for authentication
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $tokenUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['token'])) {
                return $data['token'];
            }
            
            return null;
            
        } catch (Exception $e) {
            log_message('error', 'Failed to get Moodle token: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Enroll user in Moodle course
     */
    public function enrollInMoodleCourse(int $moodleUserId, int $moodleCourseId, string $role = 'student'): bool
    {
        try {
            $roleId = $this->getRoleId($role);
            return $this->moodleApi->enrollUser($moodleUserId, $moodleCourseId, $roleId);
        } catch (Exception $e) {
            log_message('error', 'Moodle enrollment failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get Moodle role ID by name
     */
    protected function getRoleId(string $role): int
    {
        $roles = [
            'student' => 5,
            'teacher' => 3,
            'editingteacher' => 3,
            'coursecreator' => 2,
            'manager' => 1
        ];
        
        return $roles[$role] ?? 5; // Default to student
    }
    
    /**
     * Check if Moodle integration is enabled
     */
    public function isEnabled(): bool
    {
        return getenv('MOODLE_SYNC_ENABLED') === 'true';
    }
}