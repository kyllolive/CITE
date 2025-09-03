<?php

namespace App\Models;

class CourseModel extends BaseModel
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title',
        'description',
        'moodle_id',
        'instructor_id',
        'status',
        'start_date',
        'end_date'
    ];
    
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'description' => 'required',
        'instructor_id' => 'required|is_natural_no_zero',
        'status' => 'in_list[active,inactive,archived]'
    ];
    
    protected $validationMessages = [
        'instructor_id' => [
            'required' => 'An instructor must be assigned to the course.',
            'is_natural_no_zero' => 'Invalid instructor ID.'
        ]
    ];
    
    public function enrollStudent($courseId, $userId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('enrollments');
        
        $enrollment = [
            'course_id' => $courseId,
            'user_id' => $userId,
            'enrollment_date' => date('Y-m-d H:i:s'),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $builder->insert($enrollment);
    }
    
    public function unenrollStudent($courseId, $userId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('enrollments');
        
        return $builder->where([
            'course_id' => $courseId,
            'user_id' => $userId
        ])->update(['status' => 'dropped', 'updated_at' => date('Y-m-d H:i:s')]);
    }
    
    public function getWithInstructor($courseId)
    {
        return $this->select('courses.*, users.name as instructor_name, users.email as instructor_email')
                    ->join('users', 'users.id = courses.instructor_id')
                    ->find($courseId);
    }
    
    public function getActiveCourses()
    {
        return $this->where('status', 'active')->findAll();
    }
    
    public function getCoursesByInstructor($instructorId)
    {
        return $this->where('instructor_id', $instructorId)->findAll();
    }
    
    public function getEnrolledStudents($courseId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('enrollments');
        
        return $builder->select('users.*, enrollments.enrollment_date, enrollments.status as enrollment_status')
                       ->join('users', 'users.id = enrollments.user_id')
                       ->where('enrollments.course_id', $courseId)
                       ->where('enrollments.status', 'active')
                       ->get()
                       ->getResultArray();
    }
    
    public function isStudentEnrolled($courseId, $userId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('enrollments');
        
        $enrollment = $builder->where([
            'course_id' => $courseId,
            'user_id' => $userId,
            'status' => 'active'
        ])->get()->getRow();
        
        return $enrollment !== null;
    }
    
    public function getEnrollmentCount($courseId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('enrollments');
        
        return $builder->where([
            'course_id' => $courseId,
            'status' => 'active'
        ])->countAllResults();
    }
    
    public function syncWithMoodle($courseId)
    {
        $moodleApi = new \App\Libraries\MoodleApi();
        $course = $this->find($courseId);
        
        if (!$course || !$course['moodle_id']) {
            return false;
        }
        
        $moodleData = $moodleApi->getCourse($course['moodle_id']);
        
        if (!$moodleData) {
            return false;
        }
        
        return $this->update($courseId, [
            'title' => $moodleData['fullname'],
            'description' => $moodleData['summary']
        ]);
    }
}