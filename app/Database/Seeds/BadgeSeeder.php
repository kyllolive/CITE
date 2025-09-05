<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        $badgeModel = new \App\Models\Badge();
        $courseModel = new \App\Models\CourseModel();
        $userModel = new \App\Models\UserModel();

        // Get some test data
        $instructor = $userModel->where('role', 'instructor')->first();
        $courses = $courseModel->findAll();

        if (!$instructor || empty($courses)) {
            echo "Please ensure you have instructor users and courses before running badge seeder." . PHP_EOL;
            return;
        }

        $badges = [
            [
                'name' => 'Course Completion',
                'description' => 'Awarded upon successful completion of the entire course with passing grades.',
                'course_id' => $courses[0]['id'],
                'criteria' => 'Complete all course modules and achieve a passing grade of 70% or higher.',
                'is_active' => 1,
                'created_by' => $instructor['id']
            ],
            [
                'name' => 'Excellence in Learning',
                'description' => 'Awarded to students who demonstrate exceptional performance and engagement throughout the course.',
                'course_id' => $courses[0]['id'],
                'criteria' => 'Achieve a grade of 90% or higher and actively participate in discussions.',
                'is_active' => 1,
                'created_by' => $instructor['id']
            ],
            [
                'name' => 'Perfect Attendance',
                'description' => 'Awarded to students who attend all course sessions and complete all assignments on time.',
                'course_id' => $courses[0]['id'],
                'criteria' => '100% attendance and all assignments submitted on time.',
                'is_active' => 1,
                'created_by' => $instructor['id']
            ]
        ];

        // Add badges for other courses if available
        if (count($courses) > 1) {
            $badges[] = [
                'name' => 'Advanced Learner',
                'description' => 'Awarded for completing an advanced level course with distinction.',
                'course_id' => $courses[1]['id'],
                'criteria' => 'Successfully complete all advanced modules and final project.',
                'is_active' => 1,
                'created_by' => $instructor['id']
            ];
        }

        foreach ($badges as $badge) {
            $existingBadge = $badgeModel->where('name', $badge['name'])
                                      ->where('course_id', $badge['course_id'])
                                      ->first();
            
            if (!$existingBadge) {
                $badgeModel->save($badge);
                echo "Created badge: {$badge['name']}" . PHP_EOL;
            } else {
                echo "Badge already exists: {$badge['name']}" . PHP_EOL;
            }
        }
    }
}
