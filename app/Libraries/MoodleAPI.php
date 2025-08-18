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
        $this->moodleUrl = getenv('MOODLE_URL') ?: 'http://moodle:8080/webservice/rest/server.php';
        $this->token = getenv('MOODLE_TOKEN') ?: '';
    }

    /**
     * Call a Moodle web service function
     */
    public function call(string $function, array $params = []): array
    {
        if (empty($this->token)) {
            throw new Exception('Moodle API token not configured. Set MOODLE_TOKEN in .env');
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
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Moodle API request failed: ' . $error);
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
}