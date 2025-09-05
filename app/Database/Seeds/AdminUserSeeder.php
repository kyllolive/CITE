<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'name'              => 'Admin User',
            'email'             => 'admin@example.com',
            'password'          => password_hash('password123', PASSWORD_DEFAULT),
            'role'              => 'admin',
            'is_active'         => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        // Check if admin user already exists
        $userModel = new \App\Models\UserModel();
        $existingAdmin = $userModel->where('email', $data['email'])->first();
        
        if (!$existingAdmin) {
            $userModel->insert($data);
            echo "Admin user created: {$data['email']} / password123" . PHP_EOL;
        } else {
            echo "Admin user already exists: {$data['email']}" . PHP_EOL;
        }

        // Create a few test users
        $testUsers = [
            [
                'name'              => 'John Instructor',
                'email'             => 'instructor@example.com',
                'password'          => password_hash('password123', PASSWORD_DEFAULT),
                'role'              => 'instructor',
                'is_active'         => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ],
            [
                'name'              => 'Jane Student',
                'email'             => 'student@example.com',
                'password'          => password_hash('password123', PASSWORD_DEFAULT),
                'role'              => 'student',
                'is_active'         => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ],
            [
                'name'              => 'Bob Student',
                'email'             => 'student2@example.com',
                'password'          => password_hash('password123', PASSWORD_DEFAULT),
                'role'              => 'student',
                'is_active'         => 0, // Inactive user for testing
                'email_verified_at' => date('Y-m-d H:i:s'),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]
        ];

        foreach ($testUsers as $userData) {
            $existingUser = $userModel->where('email', $userData['email'])->first();
            if (!$existingUser) {
                $userModel->insert($userData);
                echo "Test user created: {$userData['email']} / password123" . PHP_EOL;
            } else {
                echo "Test user already exists: {$userData['email']}" . PHP_EOL;
            }
        }
    }
}
