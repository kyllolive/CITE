<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home route - redirect to login if not authenticated
$routes->get('/', function() {
    if (session()->get('is_logged_in')) {
        $role = session()->get('role');
        switch($role) {
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
    return redirect()->to('/auth/login');
});

// Authentication routes (guest only)
$routes->group('auth', ['filter' => 'guest'], function($routes) {
    $routes->get('login', 'Auth\LoginController::index');
    $routes->post('login', 'Auth\LoginController::authenticate');
    $routes->get('register', 'Auth\RegisterController::index');
    $routes->post('register', 'Auth\RegisterController::store');
    
    // Moodle SSO routes
    $routes->get('moodle', 'Auth\LoginController::moodle');
    $routes->get('moodle/callback', 'Auth\LoginController::moodleCallback');
});

// Logout route (authenticated only)
$routes->get('auth/logout', 'Auth\LoginController::logout', ['filter' => 'auth']);

// Protected routes (require authentication)
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Dashboard routes
    $routes->get('dashboard', 'DashboardController::index');
    
    // Admin routes
    $routes->group('admin', ['filter' => 'auth:admin'], function($routes) {
        $routes->get('dashboard', 'DashboardController::index');
        $routes->resource('users', ['controller' => 'Admin\UserController']);
        $routes->get('users/(:num)/activate', 'Admin\UserController::activate/$1');
        $routes->get('users/(:num)/deactivate', 'Admin\UserController::deactivate/$1');
        $routes->resource('courses', ['controller' => 'Admin\CourseController']);
        $routes->get('courses/(:num)/activate', 'Admin\CourseController::activate/$1');
        $routes->get('courses/(:num)/archive', 'Admin\CourseController::archive/$1');
        $routes->get('courses/(:num)/enrollments', 'Admin\CourseController::enrollments/$1');
        $routes->post('courses/(:num)/enroll', 'Admin\CourseController::enroll/$1');
        $routes->get('courses/(:num)/unenroll/(:num)', 'Admin\CourseController::unenroll/$1/$2');
        $routes->resource('badges', ['controller' => 'Admin\BadgeController']);
        $routes->get('badges/(:num)/activate', 'Admin\BadgeController::activate/$1');
        $routes->get('badges/(:num)/deactivate', 'Admin\BadgeController::deactivate/$1');
        $routes->get('badges/(:num)/awards', 'Admin\BadgeController::awards/$1');
        
        // Moodle Management routes
        $routes->group('moodle', function($routes) {
            $routes->get('/', 'Admin\MoodleManagerController::index');
            $routes->get('courses', 'Admin\MoodleManagerController::courses');
            $routes->post('sync-course', 'Admin\MoodleManagerController::syncCourse');
            $routes->post('bulk-sync-courses', 'Admin\MoodleManagerController::bulkSyncCourses');
            $routes->get('categories', 'Admin\MoodleManagerController::categories');
            $routes->post('create-category', 'Admin\MoodleManagerController::createCategory');
            $routes->get('course-users/(:num)', 'Admin\MoodleManagerController::courseUsers/$1');
            $routes->post('test-connection', 'Admin\MoodleManagerController::testConnection');
        });
    });
    
    // Instructor routes
    $routes->group('instructor', ['filter' => 'auth:instructor'], function($routes) {
        $routes->get('dashboard', 'DashboardController::index');
        $routes->get('courses', 'Instructor\CourseController::index');
        $routes->get('badges', 'Instructor\BadgeController::index');
        $routes->get('badges/course/(:num)', 'Instructor\BadgeController::courseBadges/$1');
        $routes->get('badges/course/(:num)/badge/(:num)/award', 'Instructor\BadgeController::awardBadge/$1/$2');
        $routes->post('badges/course/(:num)/badge/(:num)/award', 'Instructor\BadgeController::processAwardBadge/$1/$2');
        $routes->get('badges/course/(:num)/student/(:num)', 'Instructor\BadgeController::studentBadges/$1/$2');
        $routes->get('badges/course/(:num)/badge/(:num)/recipients', 'Instructor\BadgeController::badgeRecipients/$1/$2');
    });
    
    // Student routes
    $routes->group('student', ['filter' => 'auth:student'], function($routes) {
        $routes->get('dashboard', 'Student\DashboardController::index');
        $routes->get('courses', 'Student\CourseController::index');
        $routes->get('enrollments', 'Student\EnrollmentController::index');
        $routes->get('badges', 'Student\BadgeController::index');
        $routes->get('badges/(:num)', 'Student\BadgeController::show/$1');
        $routes->get('badges/course/(:num)', 'Student\BadgeController::course/$1');
        $routes->get('badges/certificate/(:num)', 'Student\BadgeController::certificate/$1');
    });
    
    // Moodle SSO routes
    $routes->group('moodle', function($routes) {
        $routes->get('dashboard', 'MoodleController::dashboard');
        $routes->get('sso-login/(:num)', 'MoodleController::ssoLogin/$1');
        $routes->get('sso-login', 'MoodleController::ssoLogin');
        $routes->get('get-moodle-courses', 'MoodleController::getMoodleCourses');
        $routes->post('sync-course/(:num)', 'MoodleController::syncCourse/$1');
        $routes->post('sync-course', 'MoodleController::syncCourse');
        $routes->post('create-course', 'MoodleController::createCourse');
        $routes->post('enroll-moodle/(:num)/(:num)', 'MoodleController::enrollInMoodle/$1/$2');
        $routes->post('enroll-moodle', 'MoodleController::enrollInMoodle');
        $routes->get('course-badges/(:num)', 'MoodleController::getCourseBadges/$1');
        $routes->get('course-badges', 'MoodleController::getCourseBadges');
        $routes->post('award-badge', 'MoodleController::awardBadge');
    });
    
    // Common authenticated routes
    $routes->resource('courses', ['controller' => 'CourseController']);
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile', 'ProfileController::update');
});

// Unauthorized page
$routes->get('unauthorized', function() {
    return view('errors/unauthorized');
});
