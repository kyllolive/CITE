<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;

class DashboardController extends BaseController
{
    protected $userModel;
    protected $courseModel;
    protected $enrollmentModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->courseModel = new CourseModel();
        $this->enrollmentModel = new EnrollmentModel();
    }
    
    public function index()
    {
        $role = $this->session->get('role');
        
        switch ($role) {
            case 'admin':
                return $this->adminDashboard();
            case 'instructor':
                return $this->instructorDashboard();
            case 'student':
                return $this->studentDashboard();
            default:
                return redirect()->to('/auth/login');
        }
    }
    
    protected function adminDashboard()
    {
        $data = [
            'totalUsers' => $this->userModel->countAll(),
            'totalCourses' => $this->courseModel->countAll(),
            'totalEnrollments' => $this->enrollmentModel->where('status', 'active')->countAllResults(),
            'totalInstructors' => $this->userModel->where('role', 'instructor')->countAllResults()
        ];
        
        return view('dashboard/admin', $data);
    }
    
    protected function instructorDashboard()
    {
        $instructorId = $this->session->get('user_id');
        
        $data = [
            'myCourses' => $this->courseModel->getCoursesByInstructor($instructorId),
            'totalStudents' => $this->getInstructorTotalStudents($instructorId)
        ];
        
        return view('dashboard/instructor', $data);
    }
    
    protected function studentDashboard()
    {
        $userId = $this->session->get('user_id');
        
        $data = [
            'enrolledCourses' => $this->enrollmentModel->getActiveEnrollments($userId),
            'availableCourses' => $this->getAvailableCoursesForStudent($userId)
        ];
        
        return view('dashboard/student', $data);
    }
    
    private function getInstructorTotalStudents($instructorId)
    {
        $courses = $this->courseModel->getCoursesByInstructor($instructorId);
        $totalStudents = 0;
        
        foreach ($courses as $course) {
            $totalStudents += $this->courseModel->getEnrollmentCount($course['id']);
        }
        
        return $totalStudents;
    }
    
    private function getAvailableCoursesForStudent($userId)
    {
        $allCourses = $this->courseModel->getActiveCourses();
        $enrolledCourseIds = array_column($this->enrollmentModel->getStudentEnrollments($userId), 'course_id');
        
        return array_filter($allCourses, function($course) use ($enrolledCourseIds) {
            return !in_array($course['id'], $enrolledCourseIds);
        });
    }
}