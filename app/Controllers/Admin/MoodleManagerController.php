<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MoodleSSO;
use App\Libraries\MoodleAPI;
use App\Models\CourseModel;
use App\Models\UserModel;

class MoodleManagerController extends BaseController
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
     * Main Moodle management dashboard
     */
    public function index()
    {
        $data = [
            'title' => 'Moodle Course Management',
            'sso_enabled' => $this->moodleSSO->isEnabled(),
            'moodle_url' => getenv('MOODLE_URL'),
            'sync_enabled' => getenv('MOODLE_SYNC_ENABLED') === 'true'
        ];

        // Get integration stats
        $data['stats'] = $this->getIntegrationStats();

        return view('admin/moodle/index', $data);
    }

    /**
     * View all courses from Moodle database
     */
    public function courses()
    {
        try {
            $moodleCourses = $this->moodleAPI->getCourses();
            $citeCourses = $this->courseModel->select('id, title, moodle_id, status, instructor_id')
                                           ->whereNotNull('moodle_id')
                                           ->findAll();

            // Create lookup array for CITE courses
            $citeCourseLookup = [];
            foreach ($citeCourses as $course) {
                $citeCourseLookup[$course['moodle_id']] = $course;
            }

            // Enhance Moodle courses with CITE data
            $enhancedCourses = [];
            foreach ($moodleCourses as $moodleCourse) {
                $enhancedCourse = [
                    'moodle_id' => $moodleCourse['id'],
                    'shortname' => $moodleCourse['shortname'],
                    'fullname' => $moodleCourse['fullname'],
                    'summary' => $moodleCourse['summary'] ?? '',
                    'visible' => $moodleCourse['visible'] ?? 1,
                    'categoryid' => $moodleCourse['categoryid'] ?? 1,
                    'startdate' => $moodleCourse['startdate'] ?? 0,
                    'enddate' => $moodleCourse['enddate'] ?? 0,
                    'timecreated' => $moodleCourse['timecreated'] ?? 0,
                    'cite_course' => $citeCourseLookup[$moodleCourse['id']] ?? null,
                    'is_synced' => isset($citeCourseLookup[$moodleCourse['id']])
                ];
                $enhancedCourses[] = $enhancedCourse;
            }

            $data = [
                'title' => 'Moodle Courses Database',
                'courses' => $enhancedCourses,
                'total_moodle_courses' => count($moodleCourses),
                'synced_courses' => count($citeCourses),
                'unsynced_courses' => count($moodleCourses) - count($citeCourses)
            ];

            return view('admin/moodle/courses', $data);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch Moodle courses: ' . $e->getMessage());
            return view('admin/moodle/courses', [
                'title' => 'Moodle Courses Database',
                'courses' => [],
                'error' => 'Failed to connect to Moodle: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync specific course from Moodle
     */
    public function syncCourse()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $moodleCourseId = $this->request->getPost('moodle_course_id');
        
        if (!$moodleCourseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Course ID required']);
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
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Bulk sync multiple courses
     */
    public function bulkSyncCourses()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $courseIds = $this->request->getPost('course_ids');
        
        if (empty($courseIds) || !is_array($courseIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No courses selected']);
        }

        $results = [
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($courseIds as $moodleCourseId) {
            try {
                $citeCourseId = $this->moodleSSO->syncCourseFromMoodle($moodleCourseId);
                
                if ($citeCourseId) {
                    $results['success']++;
                    $results['details'][] = [
                        'moodle_id' => $moodleCourseId,
                        'cite_id' => $citeCourseId,
                        'status' => 'success'
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'moodle_id' => $moodleCourseId,
                        'status' => 'failed',
                        'error' => 'Sync failed'
                    ];
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'moodle_id' => $moodleCourseId,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Synced {$results['success']} courses, {$results['failed']} failed",
            'results' => $results
        ]);
    }

    /**
     * View course categories from Moodle
     */
    public function categories()
    {
        try {
            $categories = $this->moodleAPI->getCourseCategories();

            $data = [
                'title' => 'Moodle Course Categories',
                'categories' => $categories
            ];

            return view('admin/moodle/categories', $data);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch Moodle categories: ' . $e->getMessage());
            return view('admin/moodle/categories', [
                'title' => 'Moodle Course Categories',
                'categories' => [],
                'error' => 'Failed to connect to Moodle: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new category in Moodle
     */
    public function createCategory()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty',
            'parent' => 'permit_empty|is_natural'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $categoryData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description') ?: '',
            'parent' => $this->request->getPost('parent') ?: 0
        ];

        try {
            $categoryId = $this->moodleAPI->createCourseCategory($categoryData);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category created successfully',
                'category_id' => $categoryId
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Category creation failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * View enrolled users for a specific Moodle course
     */
    public function courseUsers($moodleCourseId = null)
    {
        if (!$moodleCourseId) {
            return redirect()->back()->with('error', 'Course ID required');
        }

        try {
            $enrolledUsers = $this->moodleAPI->getCourseEnrolledUsers($moodleCourseId);
            $moodleCourse = $this->moodleAPI->getCourse($moodleCourseId);

            $data = [
                'title' => 'Course Users - ' . ($moodleCourse['fullname'] ?? 'Unknown Course'),
                'course' => $moodleCourse,
                'users' => $enrolledUsers,
                'moodle_course_id' => $moodleCourseId
            ];

            return view('admin/moodle/course_users', $data);

        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch course users: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load course users: ' . $e->getMessage());
        }
    }

    /**
     * Test Moodle connection and API
     */
    public function testConnection()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $tests = [
            'connection' => false,
            'authentication' => false,
            'webservices' => false,
            'sso' => false
        ];

        $results = [];

        try {
            // Test basic connection
            $siteInfo = $this->moodleAPI->call('core_webservice_get_site_info');
            $tests['connection'] = true;
            $tests['authentication'] = true;
            $results['site_info'] = [
                'sitename' => $siteInfo['sitename'] ?? 'Unknown',
                'release' => $siteInfo['release'] ?? 'Unknown',
                'version' => $siteInfo['version'] ?? 'Unknown'
            ];

            // Test web services
            $courses = $this->moodleAPI->getCourses();
            $tests['webservices'] = true;
            $results['courses_count'] = count($courses);

            // Test SSO
            $tests['sso'] = $this->moodleSSO->isEnabled();

        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return $this->response->setJSON([
            'tests' => $tests,
            'results' => $results
        ]);
    }

    /**
     * Get integration statistics
     */
    protected function getIntegrationStats()
    {
        $stats = [
            'total_cite_courses' => 0,
            'total_moodle_courses' => 0,
            'synced_courses' => 0,
            'cite_users' => 0,
            'users_with_moodle_id' => 0,
            'last_sync' => null
        ];

        try {
            // CITE statistics
            $stats['total_cite_courses'] = $this->courseModel->countAllResults();
            $stats['synced_courses'] = $this->courseModel->where('moodle_id IS NOT NULL')->countAllResults();
            $stats['cite_users'] = $this->userModel->countAllResults();
            $stats['users_with_moodle_id'] = $this->userModel->where('moodle_id IS NOT NULL')->countAllResults();

            // Moodle statistics
            if ($this->moodleSSO->isEnabled()) {
                $moodleCourses = $this->moodleAPI->getCourses();
                $stats['total_moodle_courses'] = count($moodleCourses);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to get integration stats: ' . $e->getMessage());
        }

        return $stats;
    }
}