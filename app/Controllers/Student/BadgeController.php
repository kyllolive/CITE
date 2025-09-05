<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\Badge;
use App\Models\BadgeAward;
use App\Models\CourseModel;

class BadgeController extends BaseController
{
    protected $badgeModel;
    protected $badgeAwardModel;
    protected $courseModel;

    public function __construct()
    {
        $this->badgeModel = new Badge();
        $this->badgeAwardModel = new BadgeAward();
        $this->courseModel = new CourseModel();
    }

    public function index()
    {
        $studentId = session()->get('user_id');
        $badges = $this->badgeAwardModel->getUserBadges($studentId);

        $data = [
            'badges' => $badges,
            'badgeCount' => count($badges),
            'title' => 'My Badges'
        ];
        
        return view('student/badges/index', $data);
    }

    public function show($badgeId = null)
    {
        $studentId = session()->get('user_id');
        
        // Check if student has this badge
        $award = $this->badgeAwardModel->where([
            'badge_id' => $badgeId,
            'user_id' => $studentId
        ])->first();

        if (!$award) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found or not earned');
        }

        $badge = $this->badgeModel->getBadgeWithDetails($badgeId);
        
        if (!$badge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found');
        }

        $data = [
            'badge' => $badge,
            'award' => $award,
            'title' => 'Badge Details - ' . $badge['name']
        ];
        
        return view('student/badges/show', $data);
    }

    public function course($courseId = null)
    {
        $studentId = session()->get('user_id');
        
        // Verify student is enrolled in course
        if (!$this->courseModel->isStudentEnrolled($courseId, $studentId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found or not enrolled');
        }

        $course = $this->courseModel->find($courseId);
        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found');
        }

        // Get all badges for this course
        $allCourseBadges = $this->badgeModel->getBadgesByCourse($courseId);
        
        // Get badges earned by student for this course
        $earnedBadges = $this->badgeAwardModel->select('badge_awards.*, badges.name as badge_name, badges.description, badges.image_url')
                                            ->join('badges', 'badges.id = badge_awards.badge_id')
                                            ->where('badge_awards.user_id', $studentId)
                                            ->where('badges.course_id', $courseId)
                                            ->findAll();

        $earnedBadgeIds = array_column($earnedBadges, 'badge_id');
        
        // Get available badges (not yet earned)
        $availableBadges = array_filter($allCourseBadges, function($badge) use ($earnedBadgeIds) {
            return $badge['is_active'] && !in_array($badge['id'], $earnedBadgeIds);
        });

        $data = [
            'course' => $course,
            'earnedBadges' => $earnedBadges,
            'availableBadges' => $availableBadges,
            'title' => 'Course Badges - ' . $course['title']
        ];
        
        return view('student/badges/course', $data);
    }

    public function certificate($awardId = null)
    {
        $studentId = session()->get('user_id');
        
        $award = $this->badgeAwardModel->select('badge_awards.*, badges.name as badge_name, badges.description, badges.image_url, courses.title as course_title, users.name as awarder_name')
                                     ->join('badges', 'badges.id = badge_awards.badge_id')
                                     ->join('courses', 'courses.id = badges.course_id')
                                     ->join('users', 'users.id = badge_awards.awarded_by')
                                     ->where('badge_awards.id', $awardId)
                                     ->where('badge_awards.user_id', $studentId)
                                     ->first();

        if (!$award) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge award not found');
        }

        $userModel = new \App\Models\UserModel();
        $student = $userModel->find($studentId);

        $data = [
            'award' => $award,
            'student' => $student,
            'title' => 'Badge Certificate - ' . $award['badge_name']
        ];
        
        return view('student/badges/certificate', $data);
    }
}