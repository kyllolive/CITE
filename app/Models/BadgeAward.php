<?php

namespace App\Models;

class BadgeAward extends BaseModel
{
    protected $table = 'badge_awards';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'badge_id',
        'user_id',
        'awarded_by',
        'moodle_issued_id',
        'awarded_at',
        'notes'
    ];

    protected $validationRules = [
        'badge_id' => 'required|is_natural_no_zero',
        'user_id' => 'required|is_natural_no_zero',
        'awarded_by' => 'required|is_natural_no_zero',
        'awarded_at' => 'required|valid_date'
    ];

    protected $validationMessages = [
        'badge_id' => [
            'required' => 'Badge ID is required.',
            'is_natural_no_zero' => 'Invalid badge ID.'
        ],
        'user_id' => [
            'required' => 'User ID is required.',
            'is_natural_no_zero' => 'Invalid user ID.'
        ]
    ];

    public function getUserBadges($userId)
    {
        return $this->select('badge_awards.*, badges.name as badge_name, badges.description, badges.image_url, courses.title as course_title')
                    ->join('badges', 'badges.id = badge_awards.badge_id')
                    ->join('courses', 'courses.id = badges.course_id')
                    ->where('badge_awards.user_id', $userId)
                    ->orderBy('badge_awards.awarded_at', 'DESC')
                    ->findAll();
    }

    public function getBadgeAwards($badgeId)
    {
        return $this->select('badge_awards.*, users.name as user_name, users.email as user_email, awarder.name as awarder_name')
                    ->join('users', 'users.id = badge_awards.user_id')
                    ->join('users awarder', 'awarder.id = badge_awards.awarded_by')
                    ->where('badge_awards.badge_id', $badgeId)
                    ->orderBy('badge_awards.awarded_at', 'DESC')
                    ->findAll();
    }

    public function isUserAwarded($badgeId, $userId)
    {
        return $this->where(['badge_id' => $badgeId, 'user_id' => $userId])->first() !== null;
    }

    public function awardBadge($badgeId, $userId, $awardedBy, $notes = '')
    {
        // Check if already awarded
        if ($this->isUserAwarded($badgeId, $userId)) {
            return false;
        }

        $data = [
            'badge_id' => $badgeId,
            'user_id' => $userId,
            'awarded_by' => $awardedBy,
            'awarded_at' => date('Y-m-d H:i:s'),
            'notes' => $notes
        ];

        return $this->save($data);
    }

    public function getUserBadgeCount($userId)
    {
        return $this->where('user_id', $userId)->countAllResults();
    }

    public function getRecentAwards($limit = 10)
    {
        return $this->select('badge_awards.*, users.name as user_name, badges.name as badge_name, courses.title as course_title')
                    ->join('users', 'users.id = badge_awards.user_id')
                    ->join('badges', 'badges.id = badge_awards.badge_id')
                    ->join('courses', 'courses.id = badges.course_id')
                    ->orderBy('badge_awards.awarded_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getCourseAwards($courseId)
    {
        return $this->select('badge_awards.*, users.name as user_name, users.email as user_email, badges.name as badge_name')
                    ->join('users', 'users.id = badge_awards.user_id')
                    ->join('badges', 'badges.id = badge_awards.badge_id')
                    ->where('badges.course_id', $courseId)
                    ->orderBy('badge_awards.awarded_at', 'DESC')
                    ->findAll();
    }

    public function syncWithMoodle($awardId, $moodleIssuedId)
    {
        return $this->update($awardId, ['moodle_issued_id' => $moodleIssuedId]);
    }
}