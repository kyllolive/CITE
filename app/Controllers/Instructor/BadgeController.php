<?php

namespace App\Controllers\Instructor;

use App\Controllers\BaseController;
use App\Models\Badge;
use App\Models\BadgeAward;
use App\Models\CourseModel;
use App\Libraries\MoodleAPI;

class BadgeController extends BaseController
{
    protected $badgeModel;
    protected $badgeAwardModel;
    protected $courseModel;
    protected $moodleApi;

    public function __construct()
    {
        $this->badgeModel = new Badge();
        $this->badgeAwardModel = new BadgeAward();
        $this->courseModel = new CourseModel();
        $this->moodleApi = new MoodleAPI();
    }

    public function index()
    {
        $instructorId = session()->get('user_id');
        $courses = $this->courseModel->getCoursesByInstructor($instructorId);
        $courseIds = array_column($courses, 'id');

        $allBadges = [];
        foreach ($courseIds as $courseId) {
            $badges = $this->badgeModel->getBadgesByCourse($courseId);
            $allBadges = array_merge($allBadges, $badges);
        }

        $data = [
            'badges' => $allBadges,
            'courses' => $courses,
            'title' => 'My Course Badges'
        ];
        
        return view('instructor/badges/index', $data);
    }

    public function courseBadges($courseId = null)
    {
        $instructorId = session()->get('user_id');
        
        // Verify instructor owns this course
        $course = $this->courseModel->where([
            'id' => $courseId,
            'instructor_id' => $instructorId
        ])->first();

        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found or access denied');
        }

        $badges = $this->badgeModel->getBadgesByCourse($courseId);
        $students = $this->courseModel->getEnrolledStudents($courseId);

        $data = [
            'course' => $course,
            'badges' => $badges,
            'students' => $students,
            'title' => 'Course Badges - ' . $course['title']
        ];
        
        return view('instructor/badges/course_badges', $data);
    }

    public function awardBadge($courseId = null, $badgeId = null)
    {
        $instructorId = session()->get('user_id');
        
        // Verify instructor owns this course
        $course = $this->courseModel->where([
            'id' => $courseId,
            'instructor_id' => $instructorId
        ])->first();

        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found or access denied');
        }

        $badge = $this->badgeModel->where([
            'id' => $badgeId,
            'course_id' => $courseId,
            'is_active' => 1
        ])->first();

        if (!$badge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found or not active');
        }

        $students = $this->courseModel->getEnrolledStudents($courseId);
        $awardedStudents = $this->badgeAwardModel->getBadgeAwards($badgeId);
        $awardedUserIds = array_column($awardedStudents, 'user_id');

        $data = [
            'course' => $course,
            'badge' => $badge,
            'students' => $students,
            'awardedUserIds' => $awardedUserIds,
            'title' => 'Award Badge - ' . $badge['name']
        ];
        
        return view('instructor/badges/award_badge', $data);
    }

    public function processAwardBadge($courseId = null, $badgeId = null)
    {
        $instructorId = session()->get('user_id');
        
        // Verify instructor owns this course
        $course = $this->courseModel->where([
            'id' => $courseId,
            'instructor_id' => $instructorId
        ])->first();

        if (!$course) {
            return redirect()->to('/instructor/badges')
                ->with('error', 'Course not found or access denied.');
        }

        $badge = $this->badgeModel->where([
            'id' => $badgeId,
            'course_id' => $courseId,
            'is_active' => 1
        ])->first();

        if (!$badge) {
            return redirect()->to('/instructor/badges')
                ->with('error', 'Badge not found or not active.');
        }

        $selectedStudents = $this->request->getPost('students');
        $notes = $this->request->getPost('notes');

        if (empty($selectedStudents)) {
            return redirect()->back()
                ->with('error', 'Please select at least one student.');
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($selectedStudents as $studentId) {
            $success = $this->badgeAwardModel->awardBadge(
                $badgeId, 
                $studentId, 
                $instructorId, 
                $notes
            );

            if ($success) {
                $successCount++;
                
                // Award badge in Moodle if sync enabled and badge has Moodle ID
                if (getenv('MOODLE_SYNC_ENABLED') === 'true' && $badge['moodle_badge_id']) {
                    try {
                        $this->awardBadgeInMoodle($badge['moodle_badge_id'], $studentId);
                    } catch (\Exception $e) {
                        log_message('warning', 'Moodle badge award failed but badge awarded in CITE: ' . $e->getMessage());
                    }
                }
            } else {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            $message = "Successfully awarded badge to {$successCount} student(s).";
            if ($errorCount > 0) {
                $message .= " {$errorCount} student(s) already had this badge.";
            }
            return redirect()->to("/instructor/badges/course/{$courseId}")
                ->with('success', $message);
        } else {
            return redirect()->back()
                ->with('error', 'Failed to award badge. All selected students may already have this badge.');
        }
    }

    public function studentBadges($courseId = null, $studentId = null)
    {
        $instructorId = session()->get('user_id');
        
        // Verify instructor owns this course
        $course = $this->courseModel->where([
            'id' => $courseId,
            'instructor_id' => $instructorId
        ])->first();

        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found or access denied');
        }

        // Verify student is enrolled in course
        if (!$this->courseModel->isStudentEnrolled($courseId, $studentId)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Student not found in this course');
        }

        $userModel = new \App\Models\UserModel();
        $student = $userModel->find($studentId);
        $studentBadges = $this->badgeAwardModel->getUserBadges($studentId);
        $courseBadges = array_filter($studentBadges, function($badge) use ($courseId) {
            return $badge['course_id'] == $courseId;
        });

        $data = [
            'course' => $course,
            'student' => $student,
            'badges' => $courseBadges,
            'title' => $student['name'] . ' - Course Badges'
        ];
        
        return view('instructor/badges/student_badges', $data);
    }

    public function badgeRecipients($courseId = null, $badgeId = null)
    {
        $instructorId = session()->get('user_id');
        
        // Verify instructor owns this course
        $course = $this->courseModel->where([
            'id' => $courseId,
            'instructor_id' => $instructorId
        ])->first();

        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found or access denied');
        }

        $badge = $this->badgeModel->where([
            'id' => $badgeId,
            'course_id' => $courseId
        ])->first();

        if (!$badge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found');
        }

        $awards = $this->badgeAwardModel->getBadgeAwards($badgeId);

        $data = [
            'course' => $course,
            'badge' => $badge,
            'awards' => $awards,
            'title' => 'Badge Recipients - ' . $badge['name']
        ];
        
        return view('instructor/badges/recipients', $data);
    }

    protected function awardBadgeInMoodle($moodleBadgeId, $studentId)
    {
        try {
            $userModel = new \App\Models\UserModel();
            $student = $userModel->find($studentId);
            
            if ($student && $student['moodle_id']) {
                $this->moodleApi->awardBadge($moodleBadgeId, $student['moodle_id']);
                return true;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to award badge in Moodle: ' . $e->getMessage());
        }

        return false;
    }
}