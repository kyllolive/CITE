<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\CourseModel;
use App\Libraries\MoodleAuth;
use App\Libraries\MoodleAPI;

class MoodleSync extends BaseCommand
{
    protected $group       = 'Moodle';
    protected $name        = 'moodle:sync';
    protected $description = 'Synchronize users and courses with Moodle LMS';
    protected $usage       = 'moodle:sync [options]';
    protected $arguments   = [];
    protected $options     = [
        '--users'   => 'Sync only users',
        '--courses' => 'Sync only courses',
        '--force'   => 'Force sync even if recently synced'
    ];

    public function run(array $params)
    {
        $syncUsers = CLI::getOption('users');
        $syncCourses = CLI::getOption('courses');
        $force = CLI::getOption('force');
        
        // If no specific option, sync both
        if (!$syncUsers && !$syncCourses) {
            $syncUsers = true;
            $syncCourses = true;
        }
        
        $moodleAuth = new MoodleAuth();
        
        if (!$moodleAuth->isEnabled()) {
            CLI::error('Moodle integration is not enabled. Set MOODLE_SYNC_ENABLED=true in .env');
            return;
        }
        
        CLI::write('Starting Moodle synchronization...', 'yellow');
        
        if ($syncUsers) {
            $this->syncUsers($force);
        }
        
        if ($syncCourses) {
            $this->syncCourses($force);
        }
        
        CLI::write('Synchronization completed!', 'green');
    }
    
    protected function syncUsers($force = false)
    {
        CLI::write('Syncing users...', 'blue');
        
        $userModel = new UserModel();
        $moodleAuth = new MoodleAuth();
        
        // Get users that need syncing
        $builder = $userModel->builder();
        if (!$force) {
            // Only sync users not synced in last 24 hours
            $builder->where('last_moodle_sync IS NULL OR last_moodle_sync < DATE_SUB(NOW(), INTERVAL 24 HOUR)');
        }
        
        $users = $builder->get()->getResultArray();
        $total = count($users);
        $synced = 0;
        $failed = 0;
        
        CLI::write("Found {$total} users to sync", 'blue');
        
        $progressBar = $this->showProgress($total);
        
        foreach ($users as $user) {
            try {
                if ($moodleAuth->syncUser($user['id'])) {
                    $synced++;
                    CLI::write("✓ Synced user: {$user['email']}", 'green');
                } else {
                    $failed++;
                    CLI::write("✗ Failed to sync user: {$user['email']}", 'red');
                }
            } catch (\Exception $e) {
                $failed++;
                CLI::write("✗ Error syncing {$user['email']}: " . $e->getMessage(), 'red');
            }
            
            $progressBar->tick();
        }
        
        CLI::write("Users synced: {$synced}/{$total} (Failed: {$failed})", 'yellow');
    }
    
    protected function syncCourses($force = false)
    {
        CLI::write('Syncing courses...', 'blue');
        
        $courseModel = new CourseModel();
        $moodleApi = new MoodleAPI();
        
        try {
            // Get courses from Moodle
            $moodleCourses = $moodleApi->getCourses();
            $total = count($moodleCourses);
            $synced = 0;
            $created = 0;
            $updated = 0;
            
            CLI::write("Found {$total} courses in Moodle", 'blue');
            
            foreach ($moodleCourses as $mCourse) {
                // Skip site course (id = 1)
                if ($mCourse['id'] == 1) {
                    continue;
                }
                
                // Check if course exists in CITE
                $existingCourse = $courseModel->where('moodle_id', $mCourse['id'])->first();
                
                if ($existingCourse) {
                    // Update existing course
                    $courseModel->update($existingCourse['id'], [
                        'title' => $mCourse['fullname'] ?? $mCourse['shortname'],
                        'description' => strip_tags($mCourse['summary'] ?? ''),
                        'status' => $mCourse['visible'] ? 'active' : 'inactive'
                    ]);
                    $updated++;
                    CLI::write("↻ Updated course: {$mCourse['fullname']}", 'yellow');
                } else {
                    // Create new course
                    $courseModel->insert([
                        'title' => $mCourse['fullname'] ?? $mCourse['shortname'],
                        'description' => strip_tags($mCourse['summary'] ?? ''),
                        'moodle_id' => $mCourse['id'],
                        'instructor_id' => 1, // Default to admin, should be mapped properly
                        'status' => $mCourse['visible'] ? 'active' : 'inactive',
                        'start_date' => isset($mCourse['startdate']) ? date('Y-m-d', $mCourse['startdate']) : null,
                        'end_date' => isset($mCourse['enddate']) ? date('Y-m-d', $mCourse['enddate']) : null
                    ]);
                    $created++;
                    CLI::write("+ Created course: {$mCourse['fullname']}", 'green');
                }
                $synced++;
            }
            
            CLI::write("Courses synced: {$synced} (Created: {$created}, Updated: {$updated})", 'yellow');
            
        } catch (\Exception $e) {
            CLI::error('Failed to sync courses: ' . $e->getMessage());
        }
    }
    
    protected function showProgress($total)
    {
        return CLI::showProgress(0, $total);
    }
}