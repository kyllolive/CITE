<?php

namespace App\Controllers;

use App\Libraries\MoodleSSO;
use App\Libraries\MoodleAPI;
use App\Models\CourseModel;
use App\Models\UserModel;

class MoodleController extends BaseController
{
    protected MoodleSSO $moodleSSO;
    protected MoodleAPI $moodleAPI;
    protected CourseModel $courseModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->moodleSSO = new MoodleSSO();
        $this->moodleAPI = new MoodleAPI();
        $this->courseModel = new CourseModel();
        $this->userModel = new UserModel();
    }

    /**
     * Generate SSO URL and redirect to Moodle
     */
    public function ssoLogin($courseId = null)
    {
        if (!$this->moodleSSO->isEnabled()) {
            return redirect()->back()->with('error', 'Moodle SSO is not enabled');
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/auth/login');
        }

        try {
            if ($courseId) {
                $ssoUrl = $this->moodleSSO->generateCourseAccessUrl($userId, $courseId);
            } else {
                $ssoUrl = $this->moodleSSO->generateSSOUrl($userId);
            }

            if ($ssoUrl) {
                return redirect()->to($ssoUrl);
            } else {
                return redirect()->back()->with('error', 'Failed to generate Moodle access URL');
            }
        } catch (\Exception $e) {
            log_message('error', 'Moodle SSO failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Moodle access failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user's Moodle courses (AJAX endpoint)
     */
    public function getMoodleCourses()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        try {
            $user = $this->userModel->find($userId);
            if (!$user || !$user['moodle_id']) {
                return $this->response->setJSON(['courses' => []]);
            }

            $moodleCourses = $this->moodleAPI->getUserCourses($user['moodle_id']);
            
            // Enhance courses with CITE data
            $enhancedCourses = [];
            foreach ($moodleCourses as $moodleCourse) {
                $citeCourse = $this->courseModel->where('moodle_id', $moodleCourse['id'])->first();
                
                $enhancedCourses[] = [
                    'id' => $moodleCourse['id'],
                    'fullname' => $moodleCourse['fullname'],
                    'shortname' => $moodleCourse['shortname'],
                    'summary' => $moodleCourse['summary'] ?? '',
                    'visible' => $moodleCourse['visible'] ?? 1,
                    'cite_id' => $citeCourse['id'] ?? null,
                    'sso_url' => $this->moodleSSO->generateCourseAccessUrl($userId, $citeCourse['id'] ?? $moodleCourse['id']),
                    'can_manage' => $this->canManageCourse($user, $moodleCourse)
                ];
            }

            return $this->response->setJSON(['courses' => $enhancedCourses]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get Moodle courses: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to retrieve courses']);
        }
    }

    /**
     * Sync course from Moodle to CITE
     */
    public function syncCourse($moodleCourseId = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user || !in_array($user['role'], ['admin', 'instructor'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Insufficient permissions']);
        }

        if (!$moodleCourseId) {
            $moodleCourseId = $this->request->getPost('moodle_course_id');
        }

        try {
            $citeCourseId = $this->moodleSSO->syncCourseFromMoodle($moodleCourseId);
            
            if ($citeCourseId) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Course synced successfully',
                    'cite_course_id' => $citeCourseId
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to sync course'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Course sync failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Course sync failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create course in both CITE and Moodle
     */
    public function createCourse()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user || !in_array($user['role'], ['admin', 'instructor'])) {
            return redirect()->back()->with('error', 'Insufficient permissions');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'description' => 'required',
            'instructor_id' => 'required|is_natural_no_zero',
            'status' => 'required|in_list[active,inactive,archived]',
            'create_in_moodle' => 'permit_empty|in_list[1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $courseData = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'instructor_id' => $this->request->getPost('instructor_id'),
            'status' => $this->request->getPost('status'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null
        ];

        try {
            // Create course in CITE first
            if ($this->courseModel->save($courseData)) {
                $citeCourseId = $this->courseModel->getInsertID();
                
                // Create in Moodle if requested
                if ($this->request->getPost('create_in_moodle')) {
                    $moodleCourseId = $this->moodleSSO->createMoodleCourse($userId, $courseData);
                    
                    if ($moodleCourseId) {
                        $this->courseModel->update($citeCourseId, ['moodle_id' => $moodleCourseId]);
                        
                        return redirect()->to('/admin/courses/' . $citeCourseId)
                            ->with('success', 'Course created successfully in both CITE and Moodle');
                    } else {
                        return redirect()->to('/admin/courses/' . $citeCourseId)
                            ->with('warning', 'Course created in CITE, but failed to create in Moodle');
                    }
                } else {
                    return redirect()->to('/admin/courses/' . $citeCourseId)
                        ->with('success', 'Course created successfully');
                }
            } else {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to create course');
            }

        } catch (\Exception $e) {
            log_message('error', 'Course creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Course creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Enroll user in Moodle course
     */
    public function enrollInMoodle($courseId = null, $userId = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $currentUserId = session()->get('user_id');
        $currentUser = $this->userModel->find($currentUserId);

        if (!$currentUser || !in_array($currentUser['role'], ['admin', 'instructor'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Insufficient permissions']);
        }

        if (!$courseId) {
            $courseId = $this->request->getPost('course_id');
        }
        if (!$userId) {
            $userId = $this->request->getPost('user_id');
        }

        try {
            $success = $this->moodleSSO->enrollUserInMoodleCourse($userId, $courseId);
            
            if ($success) {
                // Also enroll in CITE
                $this->courseModel->enrollStudent($courseId, $userId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'User enrolled in both CITE and Moodle'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to enroll in Moodle'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Moodle enrollment failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Enrollment failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get course badges from Moodle
     */
    public function getCourseBadges($courseId = null)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        if (!$courseId) {
            $courseId = $this->request->getPost('course_id');
        }

        try {
            $course = $this->courseModel->find($courseId);
            if (!$course || !$course['moodle_id']) {
                return $this->response->setJSON(['badges' => []]);
            }

            $badges = $this->moodleAPI->getCourseBadges($course['moodle_id']);
            
            return $this->response->setJSON(['badges' => $badges]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to get course badges: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to retrieve badges']);
        }
    }

    /**
     * Award badge to user in Moodle
     */
    public function awardBadge()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $currentUserId = session()->get('user_id');
        $currentUser = $this->userModel->find($currentUserId);

        if (!$currentUser || !in_array($currentUser['role'], ['admin', 'instructor'])) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Insufficient permissions']);
        }

        $badgeId = $this->request->getPost('badge_id');
        $userId = $this->request->getPost('user_id');

        if (!$badgeId || !$userId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Missing required parameters']);
        }

        try {
            $user = $this->userModel->find($userId);
            if (!$user || !$user['moodle_id']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'User not found or not linked to Moodle'
                ]);
            }

            $success = $this->moodleAPI->awardBadge($badgeId, $user['moodle_id']);
            
            return $this->response->setJSON([
                'success' => $success,
                'message' => $success ? 'Badge awarded successfully' : 'Failed to award badge'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Badge award failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Badge award failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Dashboard view showing Moodle integration
     */
    public function dashboard()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Moodle Integration Dashboard',
            'user' => $user,
            'sso_enabled' => $this->moodleSSO->isEnabled(),
            'moodle_url' => getenv('MOODLE_URL') ?: 'http://localhost:8081'
        ];

        // Get user's Moodle courses if they have Moodle ID
        if ($user['moodle_id']) {
            try {
                $data['moodle_courses'] = $this->moodleSSO->getUserManagedCourses($userId);
                $data['user_badges'] = $this->moodleAPI->getUserBadges($user['moodle_id']);
            } catch (\Exception $e) {
                log_message('error', 'Failed to get Moodle data: ' . $e->getMessage());
                $data['moodle_courses'] = [];
                $data['user_badges'] = [];
            }
        } else {
            $data['moodle_courses'] = [];
            $data['user_badges'] = [];
        }

        return view('moodle/dashboard', $data);
    }

    /**
     * Check if user can manage a Moodle course
     */
    protected function canManageCourse(array $user, array $moodleCourse): bool
    {
        // Admins can manage all courses
        if ($user['role'] === 'admin') {
            return true;
        }

        // Instructors can manage courses they teach
        if ($user['role'] === 'instructor') {
            // Check if user is assigned as instructor in CITE course
            $citeCourse = $this->courseModel->where('moodle_id', $moodleCourse['id'])->first();
            if ($citeCourse && $citeCourse['instructor_id'] == $user['id']) {
                return true;
            }

            // Additional logic could check Moodle course enrollment roles
            return true; // For now, allow all instructors
        }

        return false;
    }
}