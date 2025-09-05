<?php
/**
 * CITE Badges - External API functions
 *
 * @package    local_citebadges
 * @copyright  2025 CITE Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/citebadges/lib.php');

/**
 * CITE badges external API
 */
class local_citebadges_external extends external_api {

    /**
     * Create course badge parameters
     */
    public static function create_course_badge_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'name' => new external_value(PARAM_TEXT, 'Badge name'),
            'description' => new external_value(PARAM_RAW, 'Badge description'),
            'imagedata' => new external_value(PARAM_RAW, 'Badge image as base64', VALUE_OPTIONAL),
            'issuername' => new external_value(PARAM_TEXT, 'Issuer name', VALUE_OPTIONAL),
            'issuerurl' => new external_value(PARAM_URL, 'Issuer URL', VALUE_OPTIONAL),
            'issuercontact' => new external_value(PARAM_EMAIL, 'Issuer contact', VALUE_OPTIONAL),
        ]);
    }

    /**
     * Create a course badge
     */
    public static function create_course_badge($courseid, $name, $description, $imagedata = '', 
                                              $issuername = '', $issuerurl = '', $issuercontact = '') {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::create_course_badge_parameters(), [
            'courseid' => $courseid,
            'name' => $name,
            'description' => $description,
            'imagedata' => $imagedata,
            'issuername' => $issuername,
            'issuerurl' => $issuerurl,
            'issuercontact' => $issuercontact
        ]);

        // Check capability
        $context = context_course::instance($params['courseid']);
        require_capability('moodle/badges:createbadge', $context);

        // Verify course exists
        if (!$DB->record_exists('course', ['id' => $params['courseid']])) {
            throw new invalid_parameter_exception('Course does not exist');
        }

        $badgedata = [
            'name' => $params['name'],
            'description' => $params['description'],
            'image' => $params['imagedata'],
            'issuername' => $params['issuername'] ?: 'CITE System',
            'issuerurl' => $params['issuerurl'],
            'issuercontact' => $params['issuercontact']
        ];

        $badgeid = local_citebadges_create_course_badge($params['courseid'], $badgedata);

        if ($badgeid) {
            return [
                'badgeid' => $badgeid,
                'success' => true,
                'message' => 'Badge created successfully'
            ];
        } else {
            return [
                'badgeid' => 0,
                'success' => false,
                'message' => 'Failed to create badge'
            ];
        }
    }

    /**
     * Create course badge return values
     */
    public static function create_course_badge_returns() {
        return new external_single_structure([
            'badgeid' => new external_value(PARAM_INT, 'Badge ID'),
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Result message')
        ]);
    }

    /**
     * Award badge parameters
     */
    public static function award_badge_parameters() {
        return new external_function_parameters([
            'badgeid' => new external_value(PARAM_INT, 'Badge ID'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
        ]);
    }

    /**
     * Award badge to user
     */
    public static function award_badge($badgeid, $userid) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::award_badge_parameters(), [
            'badgeid' => $badgeid,
            'userid' => $userid
        ]);

        // Get badge and check context
        $badge = $DB->get_record('badge', ['id' => $params['badgeid']], '*', MUST_EXIST);
        
        if ($badge->type == BADGE_TYPE_COURSE) {
            $context = context_course::instance($badge->courseid);
        } else {
            $context = context_system::instance();
        }

        // Check capability
        require_capability('moodle/badges:awardbadge', $context);

        // Verify user exists
        if (!$DB->record_exists('user', ['id' => $params['userid']])) {
            throw new invalid_parameter_exception('User does not exist');
        }

        $success = local_citebadges_award_badge($params['badgeid'], $params['userid']);

        return [
            'success' => $success,
            'message' => $success ? 'Badge awarded successfully' : 'Badge already awarded or failed to award'
        ];
    }

    /**
     * Award badge return values
     */
    public static function award_badge_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Result message')
        ]);
    }

    /**
     * Get course badges parameters
     */
    public static function get_course_badges_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    /**
     * Get course badges
     */
    public static function get_course_badges($courseid) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_course_badges_parameters(), [
            'courseid' => $courseid
        ]);

        // Check context and capability
        $context = context_course::instance($params['courseid']);
        require_capability('moodle/badges:viewbadges', $context);

        $badges = local_citebadges_get_course_badges($params['courseid']);
        $result = [];

        foreach ($badges as $badge) {
            $badgeobj = new badge($badge->id);
            $result[] = [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'status' => $badge->status,
                'timecreated' => $badge->timecreated,
                'timemodified' => $badge->timemodified,
                'issuecount' => $DB->count_records('badge_issued', ['badgeid' => $badge->id]),
                'imageurl' => moodle_url::make_pluginfile_url(
                    $context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1'
                )->out(false)
            ];
        }

        return $result;
    }

    /**
     * Get course badges return values
     */
    public static function get_course_badges_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Badge ID'),
                'name' => new external_value(PARAM_TEXT, 'Badge name'),
                'description' => new external_value(PARAM_RAW, 'Badge description'),
                'status' => new external_value(PARAM_INT, 'Badge status'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'issuecount' => new external_value(PARAM_INT, 'Number of issues'),
                'imageurl' => new external_value(PARAM_URL, 'Badge image URL', VALUE_OPTIONAL)
            ])
        );
    }

    /**
     * Get user badges parameters
     */
    public static function get_user_badges_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT, 'User ID'),
        ]);
    }

    /**
     * Get user badges
     */
    public static function get_user_badges($userid) {
        global $DB;

        // Validate parameters
        $params = self::validate_parameters(self::get_user_badges_parameters(), [
            'userid' => $userid
        ]);

        // Check if current user can view this user's badges
        $context = context_user::instance($params['userid']);
        require_capability('moodle/badges:viewotherbadges', $context);

        $badges = local_citebadges_get_user_badges($params['userid']);
        $result = [];

        foreach ($badges as $badge) {
            $context = $badge->courseid ? context_course::instance($badge->courseid) : context_system::instance();
            
            $result[] = [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'coursename' => $badge->coursename ?: 'Site Badge',
                'dateissued' => $badge->dateissued,
                'imageurl' => moodle_url::make_pluginfile_url(
                    $context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1'
                )->out(false)
            ];
        }

        return $result;
    }

    /**
     * Get user badges return values
     */
    public static function get_user_badges_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Badge ID'),
                'name' => new external_value(PARAM_TEXT, 'Badge name'),
                'description' => new external_value(PARAM_RAW, 'Badge description'),
                'coursename' => new external_value(PARAM_TEXT, 'Course name'),
                'dateissued' => new external_value(PARAM_INT, 'Date issued'),
                'imageurl' => new external_value(PARAM_URL, 'Badge image URL', VALUE_OPTIONAL)
            ])
        );
    }
}