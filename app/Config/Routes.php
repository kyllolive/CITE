<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home route - redirect to login if not authenticated
$routes->get('/', function() {
    if (session()->get('is_logged_in')) {
        return redirect()->to('/dashboard');
    }
    return redirect()->to('/auth/login');
});

// Authentication routes (guest only)
$routes->group('auth', ['filter' => 'guest'], function($routes) {
    $routes->get('login', 'Auth\LoginController::index');
    $routes->post('login', 'Auth\LoginController::authenticate');
    $routes->get('register', 'Auth\RegisterController::index');
    $routes->post('register', 'Auth\RegisterController::store');
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
        $routes->resource('courses', ['controller' => 'Admin\CourseController']);
    });
    
    // Instructor routes
    $routes->group('instructor', ['filter' => 'auth:instructor'], function($routes) {
        $routes->get('dashboard', 'DashboardController::index');
        $routes->get('courses', 'Instructor\CourseController::index');
    });
    
    // Student routes
    $routes->group('student', ['filter' => 'auth:student'], function($routes) {
        $routes->get('dashboard', 'DashboardController::index');
        $routes->get('courses', 'Student\CourseController::index');
        $routes->get('enrollments', 'Student\EnrollmentController::index');
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
