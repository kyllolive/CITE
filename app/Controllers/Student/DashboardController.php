<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\Badge;
use App\Models\BadgeAward;
use App\Models\CourseModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    protected $badgeModel;
    protected $badgeAwardModel;
    protected $courseModel;
    protected $userModel;

    public function __construct()
    {
        $this->badgeModel = new Badge();
        $this->badgeAwardModel = new BadgeAward();
        $this->courseModel = new CourseModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $studentId = session()->get('user_id');
        
        // Get user information
        $user = $this->userModel->find($studentId);
        
        // Get earned badges
        $earnedBadges = $this->badgeAwardModel->getUserBadges($studentId);
        
        // Get enrolled courses
        $enrolledCourses = $this->courseModel->getStudentCourses($studentId);
        
        // Get available courses (not enrolled)
        $allActiveCourses = $this->courseModel->where('status', 'active')->findAll();
        $enrolledCourseIds = array_column($enrolledCourses, 'id');
        $availableCourses = array_filter($allActiveCourses, function($course) use ($enrolledCourseIds) {
            return !in_array($course['id'], $enrolledCourseIds);
        });
        
        // Get recent badges (last 5)
        $recentBadges = $this->badgeAwardModel->select('badge_awards.*, badges.name as badge_name, badges.description, badges.image_url, courses.title as course_title')
                                           ->join('badges', 'badges.id = badge_awards.badge_id')
                                           ->join('courses', 'courses.id = badges.course_id')
                                           ->where('badge_awards.user_id', $studentId)
                                           ->orderBy('badge_awards.created_at', 'DESC')
                                           ->limit(5)
                                           ->findAll();
        
        // Calculate progress statistics
        $totalBadges = count($earnedBadges);
        $totalCourses = count($enrolledCourses);
        $completedCourses = 0; // You can implement course completion logic later
        
        // Group badges by course for statistics
        $badgesByCourse = [];
        foreach ($earnedBadges as $badge) {
            if (!isset($badgesByCourse[$badge['course_id']])) {
                $badgesByCourse[$badge['course_id']] = 0;
            }
            $badgesByCourse[$badge['course_id']]++;
        }

        $data = [
            'user' => $user,
            'earnedBadges' => $earnedBadges,
            'recentBadges' => $recentBadges,
            'enrolledCourses' => $enrolledCourses,
            'availableCourses' => array_slice($availableCourses, 0, 6), // Show first 6 available courses
            'totalBadges' => $totalBadges,
            'totalCourses' => $totalCourses,
            'completedCourses' => $completedCourses,
            'badgesByCourse' => $badgesByCourse,
            'title' => 'Student Dashboard'
        ];
        
        return view('student/dashboard', $data);
    }
}