<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title' => 'Introduction to Web Development',
                'description' => 'Learn the fundamentals of web development including HTML, CSS, and JavaScript.',
                'instructor_id' => 2, // John Instructor
                'status' => 'active',
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+3 months')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Database Management Systems',
                'description' => 'Comprehensive course on database design, SQL, and database administration.',
                'instructor_id' => 2,
                'status' => 'active',
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+3 months')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Advanced PHP Programming',
                'description' => 'Deep dive into PHP with focus on OOP, design patterns, and modern frameworks.',
                'instructor_id' => 2,
                'status' => 'active',
                'start_date' => date('Y-m-d', strtotime('+1 month')),
                'end_date' => date('Y-m-d', strtotime('+4 months')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];

        $this->db->table('courses')->insertBatch($data);
        
        // Enroll students in courses
        $enrollments = [
            [
                'course_id' => 1,
                'user_id' => 3, // Jane Student
                'enrollment_date' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'course_id' => 1,
                'user_id' => 4, // Bob Student
                'enrollment_date' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'course_id' => 2,
                'user_id' => 3,
                'enrollment_date' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];
        
        $this->db->table('enrollments')->insertBatch($enrollments);
    }
}