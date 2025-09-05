<?php
/**
 * CITE Badges - Web services functions
 *
 * @package    local_citebadges
 * @copyright  2025 CITE Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_citebadges_create_course_badge' => [
        'classname'   => 'local_citebadges_external',
        'methodname'  => 'create_course_badge',
        'classpath'   => 'local/citebadges/classes/external.php',
        'description' => 'Create a badge for a course',
        'type'        => 'write',
        'capabilities' => 'moodle/badges:createbadge',
        'ajax'        => true,
        'loginrequired' => true,
    ],
    'local_citebadges_award_badge' => [
        'classname'   => 'local_citebadges_external',
        'methodname'  => 'award_badge',
        'classpath'   => 'local/citebadges/classes/external.php',
        'description' => 'Award a badge to a user',
        'type'        => 'write',
        'capabilities' => 'moodle/badges:awardbadge',
        'ajax'        => true,
        'loginrequired' => true,
    ],
    'local_citebadges_get_course_badges' => [
        'classname'   => 'local_citebadges_external',
        'methodname'  => 'get_course_badges',
        'classpath'   => 'local/citebadges/classes/external.php',
        'description' => 'Get all badges for a course',
        'type'        => 'read',
        'capabilities' => 'moodle/badges:viewbadges',
        'ajax'        => true,
        'loginrequired' => true,
    ],
    'local_citebadges_get_user_badges' => [
        'classname'   => 'local_citebadges_external',
        'methodname'  => 'get_user_badges',
        'classpath'   => 'local/citebadges/classes/external.php',
        'description' => 'Get all badges for a user',
        'type'        => 'read',
        'capabilities' => 'moodle/badges:viewotherbadges',
        'ajax'        => true,
        'loginrequired' => true,''
    ],
];

$services = [
    'CITE Badge Service' => [
        'functions' => [
            'local_citebadges_create_course_badge',
            'local_citebadges_award_badge',
            'local_citebadges_get_course_badges',
            'local_citebadges_get_user_badges',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];