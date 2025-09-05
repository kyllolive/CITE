<?php

namespace App\Models;

class Badge extends BaseModel
{
    protected $table = 'badges';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'description',
        'course_id',
        'moodle_badge_id',
        'image_url',
        'criteria',
        'is_active',
        'created_by'
    ];

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'description' => 'required',
        'course_id' => 'required|is_natural_no_zero',
        'created_by' => 'required|is_natural_no_zero',
    ];

    protected $validationMessages = [
        'course_id' => [
            'required' => 'A course must be selected for the badge.',
            'is_natural_no_zero' => 'Invalid course ID.'
        ]
    ];

    public function getBadgesWithCourse()
    {
        $badges = $this->select('badges.*, courses.title as course_title, users.name as creator_name')
                       ->join('courses', 'courses.id = badges.course_id')
                       ->join('users', 'users.id = badges.created_by')
                       ->findAll();
        
        // Add award count for each badge
        foreach ($badges as &$badge) {
            $badge['award_count'] = $this->getAwardCount($badge['id']);
        }
        
        return $badges;
    }

    public function getBadgeWithDetails($id)
    {
        return $this->select('badges.*, courses.title as course_title, users.name as creator_name')
                    ->join('courses', 'courses.id = badges.course_id')
                    ->join('users', 'users.id = badges.created_by')
                    ->find($id);
    }

    public function getBadgesByCourse($courseId)
    {
        return $this->where('course_id', $courseId)->findAll();
    }

    public function getActiveBadges()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function activateBadge($badgeId)
    {
        return $this->update($badgeId, ['is_active' => 1]);
    }

    public function deactivateBadge($badgeId)
    {
        return $this->update($badgeId, ['is_active' => 0]);
    }

    public function getAwardCount($badgeId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('badge_awards');
        return $builder->where('badge_id', $badgeId)->countAllResults();
    }

    public function syncWithMoodle($badgeId, $moodleBadgeId)
    {
        return $this->update($badgeId, ['moodle_badge_id' => $moodleBadgeId]);
    }
}
