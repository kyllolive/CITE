<?php

namespace App\Libraries;

use Exception;

class MoodleAPI
{
    protected string $moodleUrl;
    protected string $token;
    protected int $timeout = 30;

    public function __construct()
    {
        $moodleBaseUrl = getenv('MOODLE_URL') ?: 'http://moodle_lms:80';
        $this->moodleUrl = $moodleBaseUrl . '/webservice/rest/server.php';
        $this->token = getenv('MOODLE_TOKEN') ?: '';
    }

    /**
     * Call a Moodle web service function
     */
    public function call(string $function, array $params = []): array
    {
        if (empty($this->token)) {
            throw new Exception('Moodle API token not configured. Please see setup-moodle-integration.md for instructions.');
        }

        $params['wstoken'] = $this->token;
        $params['wsfunction'] = $function;
        $params['moodlewsrestformat'] = 'json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->moodleUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        // Handle Moodle redirects manually to fix Docker network issues
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Moodle API request failed: ' . $error);
        }

        // Handle redirects manually to fix Docker network issues
        if ($httpCode === 303 || $httpCode === 302) {
            // Split headers and body
            $parts = explode("\r\n\r\n", $response, 2);
            $headers = $parts[0];
            
            if (preg_match('/Location: (.+)/i', $headers, $matches)) {
                $redirectUrl = trim($matches[1]);
                
                // Fix localhost redirects for Docker environment
                $redirectUrl = str_replace('http://localhost:8081', 'http://172.22.0.1:8081', $redirectUrl);
                
                // Make the redirected request
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $redirectUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error) {
                    throw new Exception('Moodle API redirect request failed: ' . $error);
                }
            }
        } else {
            // Remove headers if present (when CURLOPT_HEADER was true)
            if (strpos($response, "\r\n\r\n") !== false) {
                $parts = explode("\r\n\r\n", $response, 2);
                $response = $parts[1] ?? $response;
            }
        }

        if ($httpCode !== 200) {
            throw new Exception('Moodle API returned HTTP ' . $httpCode);
        }

        $data = json_decode($response, true);
        
        if (isset($data['exception'])) {
            throw new Exception('Moodle API error: ' . $data['message']);
        }

        return $data;
    }

    /**
     * Get user information by email
     */
    public function getUserByEmail(string $email): ?array
    {
        $result = $this->call('core_user_get_users', [
            'criteria' => [
                ['key' => 'email', 'value' => $email]
            ]
        ]);

        return $result['users'][0] ?? null;
    }

    /**
     * Create a new user in Moodle
     */
    public function createUser(array $userData): int
    {
        $result = $this->call('core_user_create_users', [
            'users' => [$userData]
        ]);

        if (isset($result[0]['id'])) {
            return $result[0]['id'];
        }

        throw new Exception('Failed to create Moodle user');
    }

    /**
     * Enroll a user in a course
     */
    public function enrollUser(int $userId, int $courseId, int $roleId = 5): bool
    {
        $result = $this->call('enrol_manual_enrol_users', [
            'enrolments' => [
                [
                    'userid' => $userId,
                    'courseid' => $courseId,
                    'roleid' => $roleId  // 5 = student
                ]
            ]
        ]);

        return true;
    }

    /**
     * Get user's enrolled courses
     */
    public function getUserCourses(int $userId): array
    {
        return $this->call('core_enrol_get_users_courses', [
            'userid' => $userId
        ]);
    }

    /**
     * Get course grades for a user
     */
    public function getUserGrades(int $courseId, int $userId): array
    {
        return $this->call('gradereport_user_get_grade_items', [
            'courseid' => $courseId,
            'userid' => $userId
        ]);
    }

    /**
     * Get all courses
     */
    public function getCourses(): array
    {
        return $this->call('core_course_get_courses');
    }

    /**
     * Create a new course
     */
    public function createCourse(array $courseData): int
    {
        $result = $this->call('core_course_create_courses', [
            'courses' => [$courseData]
        ]);

        if (isset($result[0]['id'])) {
            return $result[0]['id'];
        }

        throw new Exception('Failed to create Moodle course');
    }
    
    /**
     * Get a specific course by ID
     */
    public function getCourse(int $courseId): ?array
    {
        $result = $this->call('core_course_get_courses_by_field', [
            'field' => 'id',
            'value' => $courseId
        ]);
        
        return $result['courses'][0] ?? null;
    }
    
    /**
     * Update a course
     */
    public function updateCourse(int $courseId, array $courseData): bool
    {
        $courseData['id'] = $courseId;
        
        $result = $this->call('core_course_update_courses', [
            'courses' => [$courseData]
        ]);
        
        return true;
    }
    
    /**
     * Get enrolled users in a course
     */
    public function getCourseEnrolledUsers(int $courseId, array $options = []): array
    {
        $defaultOptions = [
            'withcapability' => '',
            'groupid' => 0,
            'onlyactive' => 1,
            'userfields' => 'id,username,firstname,lastname,email,roles'
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        return $this->call('core_enrol_get_enrolled_users', [
            'courseid' => $courseId,
            'options' => [$options]
        ]);
    }
    
    /**
     * Get user role assignments in a course
     */
    public function getUserRoleAssignments(int $userId, int $courseId): array
    {
        $context = $this->getCourseContext($courseId);
        
        return $this->call('core_role_get_assignable_roles', [
            'contextid' => $context['id'],
            'rolenamedisplay' => 'both',
            'returncontextname' => false
        ]);
    }
    
    /**
     * Get course context
     */
    public function getCourseContext(int $courseId): ?array
    {
        $result = $this->call('core_webservice_get_site_info');
        
        // This is a simplified approach - in practice you'd need to get the actual context
        return [
            'id' => $courseId * 1000, // Simplified context ID calculation
            'contextlevel' => 50, // CONTEXT_COURSE
            'instanceid' => $courseId
        ];
    }
    
    /**
     * Assign role to user in course
     */
    public function assignRoleToUser(int $userId, int $roleId, int $contextId): bool
    {
        $result = $this->call('core_role_assign_roles', [
            'assignments' => [
                [
                    'roleid' => $roleId,
                    'userid' => $userId,
                    'contextid' => $contextId
                ]
            ]
        ]);
        
        return true;
    }
    
    /**
     * Unenroll user from course
     */
    public function unenrollUser(int $userId, int $courseId): bool
    {
        $result = $this->call('enrol_manual_unenrol_users', [
            'enrolments' => [
                [
                    'userid' => $userId,
                    'courseid' => $courseId
                ]
            ]
        ]);
        
        return true;
    }
    
    /**
     * Get course categories
     */
    public function getCourseCategories(): array
    {
        return $this->call('core_course_get_categories');
    }
    
    /**
     * Create course category
     */
    public function createCourseCategory(array $categoryData): int
    {
        $result = $this->call('core_course_create_categories', [
            'categories' => [$categoryData]
        ]);
        
        if (isset($result[0]['id'])) {
            return $result[0]['id'];
        }
        
        throw new Exception('Failed to create course category');
    }
    
    /**
     * Get badges for a course
     */
    public function getCourseBadges(int $courseId): array
    {
        try {
            return $this->call('local_citebadges_get_course_badges', [
                'courseid' => $courseId
            ]);
        } catch (Exception $e) {
            // Plugin might not be installed, return empty array
            log_message('info', 'CITE badges plugin not available: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create course badge
     */
    public function createCourseBadge(int $courseId, array $badgeData): ?int
    {
        try {
            $result = $this->call('local_citebadges_create_course_badge', 
                array_merge($badgeData, ['courseid' => $courseId])
            );
            
            return $result['badgeid'] ?? null;
        } catch (Exception $e) {
            log_message('error', 'Failed to create badge: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Award badge to user
     */
    public function awardBadge(int $badgeId, int $userId): bool
    {
        try {
            $result = $this->call('local_citebadges_award_badge', [
                'badgeid' => $badgeId,
                'userid' => $userId
            ]);
            
            return $result['success'] ?? false;
        } catch (Exception $e) {
            log_message('error', 'Failed to award badge: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's badges
     */
    public function getUserBadges(int $userId): array
    {
        try {
            return $this->call('local_citebadges_get_user_badges', [
                'userid' => $userId
            ]);
        } catch (Exception $e) {
            log_message('info', 'CITE badges plugin not available: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Set user authentication token for API calls
     */
    public function setUserToken(string $token): void
    {
        $this->token = $token;
    }
    
    /**
     * Check if user has capability in context
     */
    public function userHasCapability(int $userId, string $capability, int $contextId): bool
    {
        try {
            $result = $this->call('core_role_check_capabilities', [
                'userid' => $userId,
                'capabilities' => [
                    [
                        'capability' => $capability,
                        'contextid' => $contextId
                    ]
                ]
            ]);
            
            return $result[0]['hascapability'] ?? false;
        } catch (Exception $e) {
            log_message('error', 'Capability check failed: ' . $e->getMessage());
            return false;
        }
    }
}