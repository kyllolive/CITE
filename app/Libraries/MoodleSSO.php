<?php

namespace App\Libraries;

use App\Models\UserModel;
use Exception;

class MoodleSSO
{
    protected MoodleAPI $moodleApi;
    protected UserModel $userModel;
    protected string $moodleUrl;
    protected string $ssoSecret;
    
    public function __construct()
    {
        $this->moodleApi = new MoodleAPI();
        $this->userModel = new UserModel();
        $this->moodleUrl = getenv('MOODLE_URL') ?: 'http://localhost:8081';
        $this->ssoSecret = getenv('MOODLE_SSO_SECRET') ?: '';
    }
    
    /**
     * Generate SSO URL for user to access Moodle
     */
    public function generateSSOUrl(int $userId, string $returnUrl = ''): ?string
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Ensure user exists in Moodle
            $this->syncUserToMoodle($user);
            
            // Generate SSO token
            $timestamp = time();
            $ssoData = [
                'email' => $user['email'],
                'firstname' => explode(' ', $user['name'])[0] ?? $user['name'],
                'lastname' => explode(' ', $user['name'])[1] ?? '',
                'role' => $this->mapCiteRoleToMoodle($user['role']),
                'timestamp' => $timestamp,
                'returnurl' => $returnUrl ?: $this->moodleUrl
            ];
            
            // Create signed token
            $token = $this->createSignedToken($ssoData);
            
            // Return SSO URL
            return $this->moodleUrl . '/auth/cite/login.php?token=' . urlencode($token);
            
        } catch (Exception $e) {
            log_message('error', 'SSO URL generation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate course-specific SSO URL
     */
    public function generateCourseAccessUrl(int $userId, int $courseId, string $action = 'view'): ?string
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Check if user has access to this course based on role
            if (!$this->canAccessCourse($user, $courseId, $action)) {
                throw new Exception('User does not have permission to access this course');
            }
            
            // Get Moodle course ID
            $course = model('CourseModel')->find($courseId);
            if (!$course || !$course['moodle_id']) {
                throw new Exception('Course not linked to Moodle');
            }
            
            // Generate SSO URL with course redirect
            $returnUrl = $this->moodleUrl . '/course/view.php?id=' . $course['moodle_id'];
            
            if ($action === 'edit' && in_array($user['role'], ['admin', 'instructor'])) {
                $returnUrl = $this->moodleUrl . '/course/edit.php?id=' . $course['moodle_id'];
            }
            
            return $this->generateSSOUrl($userId, $returnUrl);
            
        } catch (Exception $e) {
            log_message('error', 'Course SSO URL generation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new course in Moodle via SSO
     */
    public function createMoodleCourse(int $userId, array $courseData): ?int
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user || !in_array($user['role'], ['admin', 'instructor'])) {
                throw new Exception('User does not have permission to create courses');
            }
            
            // Prepare Moodle course data
            $moodleCourseData = [
                'fullname' => $courseData['title'],
                'shortname' => $this->generateCourseShortname($courseData['title']),
                'summary' => $courseData['description'],
                'categoryid' => 1, // Default category
                'format' => 'topics',
                'numsections' => 10,
                'startdate' => $courseData['start_date'] ? strtotime($courseData['start_date']) : time(),
                'enddate' => $courseData['end_date'] ? strtotime($courseData['end_date']) : 0,
                'visible' => $courseData['status'] === 'active' ? 1 : 0
            ];
            
            // Create course in Moodle
            $moodleCourseId = $this->moodleApi->createCourse($moodleCourseData);
            
            // Enroll instructor as teacher
            if ($courseData['instructor_id'] && $user['moodle_id']) {
                $this->moodleApi->enrollUser($user['moodle_id'], $moodleCourseId, 3); // Teacher role
            }
            
            return $moodleCourseId;
            
        } catch (Exception $e) {
            log_message('error', 'Moodle course creation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Enroll user in Moodle course based on CITE enrollment
     */
    public function enrollUserInMoodleCourse(int $userId, int $courseId): bool
    {
        try {
            $user = $this->userModel->find($userId);
            $course = model('CourseModel')->find($courseId);
            
            if (!$user || !$course || !$course['moodle_id'] || !$user['moodle_id']) {
                return false;
            }
            
            // Get appropriate role
            $roleId = $this->getMoodleRoleId($user['role']);
            
            // Enroll in Moodle
            return $this->moodleApi->enrollUser($user['moodle_id'], $course['moodle_id'], $roleId);
            
        } catch (Exception $e) {
            log_message('error', 'Moodle enrollment failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get courses user can manage in Moodle
     */
    public function getUserManagedCourses(int $userId): array
    {
        try {
            $user = $this->userModel->find($userId);
            if (!$user || !$user['moodle_id']) {
                return [];
            }
            
            // Get courses from Moodle based on user role
            $moodleCourses = $this->moodleApi->getUserCourses($user['moodle_id']);
            $managedCourses = [];
            
            foreach ($moodleCourses as $moodleCourse) {
                // Check if user has management permissions for this course
                if ($this->canManageCourse($user, $moodleCourse)) {
                    $managedCourses[] = [
                        'moodle_id' => $moodleCourse['id'],
                        'title' => $moodleCourse['fullname'],
                        'shortname' => $moodleCourse['shortname'],
                        'summary' => $moodleCourse['summary'],
                        'visible' => $moodleCourse['visible'],
                        'edit_url' => $this->generateCourseAccessUrl($userId, $moodleCourse['id'], 'edit'),
                        'view_url' => $this->generateCourseAccessUrl($userId, $moodleCourse['id'], 'view')
                    ];
                }
            }
            
            return $managedCourses;
            
        } catch (Exception $e) {
            log_message('error', 'Failed to get managed courses: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Sync course data from Moodle to CITE
     */
    public function syncCourseFromMoodle(int $moodleCourseId): ?int
    {
        try {
            $moodleCourse = $this->moodleApi->getCourse($moodleCourseId);
            if (!$moodleCourse) {
                return null;
            }
            
            $courseModel = model('CourseModel');
            
            // Check if course already exists in CITE
            $existingCourse = $courseModel->where('moodle_id', $moodleCourseId)->first();
            
            $courseData = [
                'title' => $moodleCourse['fullname'],
                'description' => $moodleCourse['summary'] ?: 'Course imported from Moodle',
                'moodle_id' => $moodleCourseId,
                'status' => $moodleCourse['visible'] ? 'active' : 'inactive',
                'start_date' => $moodleCourse['startdate'] ? date('Y-m-d', $moodleCourse['startdate']) : null,
                'end_date' => $moodleCourse['enddate'] ? date('Y-m-d', $moodleCourse['enddate']) : null
            ];
            
            if ($existingCourse) {
                $courseModel->update($existingCourse['id'], $courseData);
                return $existingCourse['id'];
            } else {
                // Find a default instructor (admin user)
                $adminUser = $this->userModel->where('role', 'admin')->where('is_active', 1)->first();
                $courseData['instructor_id'] = $adminUser['id'] ?? 1;
                
                return $courseModel->save($courseData) ? $courseModel->getInsertID() : null;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Course sync from Moodle failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create signed token for SSO
     */
    protected function createSignedToken(array $data): string
    {
        $payload = base64_encode(json_encode($data));
        $signature = hash_hmac('sha256', $payload, $this->ssoSecret);
        
        return $payload . '.' . $signature;
    }
    
    /**
     * Verify signed token
     */
    public function verifySignedToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }
        
        [$payload, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payload, $this->ssoSecret);
        
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }
        
        $data = json_decode(base64_decode($payload), true);
        
        // Check timestamp (token valid for 5 minutes)
        if (time() - $data['timestamp'] > 300) {
            return null;
        }
        
        return $data;
    }
    
    /**
     * Sync user to Moodle
     */
    protected function syncUserToMoodle(array $user): void
    {
        if (!$user['moodle_id']) {
            // Create user in Moodle
            $moodleAuth = new MoodleAuth();
            $moodleId = $moodleAuth->createMoodleUser([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => bin2hex(random_bytes(16)) // Random password for SSO users
            ]);
            
            if ($moodleId) {
                $this->userModel->update($user['id'], ['moodle_id' => $moodleId]);
            }
        }
    }
    
    /**
     * Map CITE role to Moodle role
     */
    protected function mapCiteRoleToMoodle(string $citeRole): string
    {
        $mapping = [
            'admin' => 'manager',
            'instructor' => 'editingteacher',
            'student' => 'student'
        ];
        
        return $mapping[$citeRole] ?? 'student';
    }
    
    /**
     * Get Moodle role ID
     */
    protected function getMoodleRoleId(string $citeRole): int
    {
        $mapping = [
            'admin' => 1,        // Manager
            'instructor' => 3,   // Editing teacher
            'student' => 5       // Student
        ];
        
        return $mapping[$citeRole] ?? 5;
    }
    
    /**
     * Check if user can access course
     */
    protected function canAccessCourse(array $user, int $courseId, string $action): bool
    {
        $courseModel = model('CourseModel');
        
        // Admins can access all courses
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Instructors can access courses they teach
        if ($user['role'] === 'instructor') {
            $course = $courseModel->find($courseId);
            return $course && $course['instructor_id'] == $user['id'];
        }
        
        // Students can only view courses they're enrolled in
        if ($user['role'] === 'student' && $action === 'view') {
            return $courseModel->isStudentEnrolled($courseId, $user['id']);
        }
        
        return false;
    }
    
    /**
     * Check if user can manage course in Moodle
     */
    protected function canManageCourse(array $user, array $moodleCourse): bool
    {
        // Admins can manage all courses
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Instructors can manage courses where they have editing teacher role
        if ($user['role'] === 'instructor') {
            // This would require checking the user's enrollment role in Moodle
            // For now, assume instructors can manage courses they're enrolled in
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate course shortname
     */
    protected function generateCourseShortname(string $title): string
    {
        $shortname = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $title), 0, 10));
        return $shortname . '_' . date('Y');
    }
    
    /**
     * Check if SSO is enabled
     */
    public function isEnabled(): bool
    {
        return !empty($this->ssoSecret) && getenv('MOODLE_SSO_ENABLED') === 'true';
    }
}