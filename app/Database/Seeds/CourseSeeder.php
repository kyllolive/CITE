<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        echo "Starting course synchronization from Moodle..." . PHP_EOL;
        
        // Get instructor user (fallback to first instructor found)
        $userModel = new \App\Models\UserModel();
        $instructor = $userModel->where('role', 'instructor')->first();
        
        if (!$instructor) {
            echo "Error: No instructor found. Please ensure you have instructor users." . PHP_EOL;
            return;
        }
        
        $instructorId = $instructor['id'];
        echo "Using instructor: " . $instructor['name'] . " (ID: {$instructorId})" . PHP_EOL;
        
        // Connect to Moodle database
        $moodleDb = \Config\Database::connect('moodle');
        
        // Get courses from Moodle (excluding site course with id=1)
        $moodleCourses = $moodleDb->table('mdl_course')
            ->select('id, fullname, shortname, summary, startdate, enddate, visible, timecreated')
            ->where('id >', 1)
            ->where('visible', 1)
            ->orderBy('timecreated', 'ASC')
            ->get()
            ->getResultArray();
        
        if (empty($moodleCourses)) {
            echo "No courses found in Moodle database." . PHP_EOL;
            return;
        }
        
        echo "Found " . count($moodleCourses) . " courses in Moodle." . PHP_EOL;
        
        $courseModel = new \App\Models\CourseModel();
        $citeCourseIds = [];
        
        foreach ($moodleCourses as $moodleCourse) {
            // Check if course already exists in CITE
            $existingCourse = $courseModel->where('moodle_id', $moodleCourse['id'])->first();
            
            $courseData = [
                'title' => $moodleCourse['fullname'],
                'description' => !empty($moodleCourse['summary']) ? $moodleCourse['summary'] : 'Course synchronized from Moodle.',
                'moodle_id' => $moodleCourse['id'],
                'instructor_id' => $instructorId,
                'status' => 'active',
                'start_date' => $moodleCourse['startdate'] > 0 ? date('Y-m-d', $moodleCourse['startdate']) : date('Y-m-d'),
                'end_date' => $moodleCourse['enddate'] > 0 ? date('Y-m-d', $moodleCourse['enddate']) : date('Y-m-d', strtotime('+6 months')),
                'created_at' => date('Y-m-d H:i:s', $moodleCourse['timecreated']),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existingCourse) {
                // Update existing course
                $courseModel->update($existingCourse['id'], $courseData);
                $citeCourseId = $existingCourse['id'];
                echo "Updated course: {$moodleCourse['fullname']} (Moodle ID: {$moodleCourse['id']})" . PHP_EOL;
            } else {
                // Create new course
                $courseModel->save($courseData);
                $citeCourseId = $courseModel->getInsertID();
                echo "Created course: {$moodleCourse['fullname']} (Moodle ID: {$moodleCourse['id']})" . PHP_EOL;
            }
            
            $citeCourseIds[] = $citeCourseId;
        }
        
        // Create sample enrollments for students
        $this->createSampleEnrollments($citeCourseIds);
        
        echo "Course synchronization completed successfully!" . PHP_EOL;
    }
    
    private function createSampleEnrollments(array $citeCourseIds)
    {
        if (empty($citeCourseIds)) {
            return;
        }
        
        $userModel = new \App\Models\UserModel();
        $students = $userModel->where('role', 'student')->findAll();
        
        if (empty($students)) {
            echo "No students found for enrollment." . PHP_EOL;
            return;
        }
        
        echo "Creating sample enrollments..." . PHP_EOL;
        
        $enrollments = [];
        $enrollmentTime = date('Y-m-d H:i:s');
        
        // Enroll each student in first 2-3 courses
        foreach ($students as $student) {
            $coursesToEnroll = array_slice($citeCourseIds, 0, min(3, count($citeCourseIds)));
            
            foreach ($coursesToEnroll as $courseId) {
                // Check if enrollment already exists
                $existingEnrollment = $this->db->table('enrollments')
                    ->where(['course_id' => $courseId, 'user_id' => $student['id']])
                    ->get()
                    ->getRow();
                
                if (!$existingEnrollment) {
                    $enrollments[] = [
                        'course_id' => $courseId,
                        'user_id' => $student['id'],
                        'enrollment_date' => $enrollmentTime,
                        'status' => 'active',
                        'created_at' => $enrollmentTime,
                        'updated_at' => $enrollmentTime,
                    ];
                }
            }
        }
        
        if (!empty($enrollments)) {
            $this->db->table('enrollments')->insertBatch($enrollments);
            echo "Created " . count($enrollments) . " sample enrollments." . PHP_EOL;
        } else {
            echo "All students are already enrolled in courses." . PHP_EOL;
        }
    }
}