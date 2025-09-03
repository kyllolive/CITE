<?php

namespace App\Models;

class EnrollmentModel extends BaseModel
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'course_id',
        'user_id',
        'enrollment_date',
        'completion_date',
        'status',
        'grade'
    ];
    
    protected $validationRules = [
        'course_id' => 'required|is_natural_no_zero',
        'user_id' => 'required|is_natural_no_zero',
        'status' => 'in_list[active,completed,dropped]',
        'grade' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
    ];
    
    public function getWithCourseAndUser($enrollmentId)
    {
        return $this->select('enrollments.*, courses.title as course_title, users.name as student_name')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->join('users', 'users.id = enrollments.user_id')
                    ->find($enrollmentId);
    }
    
    public function getStudentEnrollments($userId)
    {
        return $this->select('enrollments.*, courses.title, courses.description, courses.instructor_id')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $userId)
                    ->findAll();
    }
    
    public function getActiveEnrollments($userId)
    {
        return $this->select('enrollments.*, courses.title, courses.description')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $userId)
                    ->where('enrollments.status', 'active')
                    ->findAll();
    }
    
    public function completeEnrollment($enrollmentId, $grade = null)
    {
        $data = [
            'status' => 'completed',
            'completion_date' => date('Y-m-d H:i:s')
        ];
        
        if ($grade !== null) {
            $data['grade'] = $grade;
        }
        
        return $this->update($enrollmentId, $data);
    }
    
    public function dropEnrollment($enrollmentId)
    {
        return $this->update($enrollmentId, [
            'status' => 'dropped',
            'completion_date' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function updateGrade($enrollmentId, $grade)
    {
        return $this->update($enrollmentId, ['grade' => $grade]);
    }
    
    public function getEnrollmentByCourseAndUser($courseId, $userId)
    {
        return $this->where([
            'course_id' => $courseId,
            'user_id' => $userId
        ])->first();
    }
}