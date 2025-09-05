<?php
/**
 * CITE Badges - Library functions
 *
 * @package    local_citebadges
 * @copyright  2025 CITE Team  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/badgeslib.php');

/**
 * Serve plugin files
 */
function local_citebadges_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_SYSTEM && $context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'badgeimage') {
        return false;
    }

    // Make sure the user is logged in and has access to the module
    require_login();

    // The args is an array containing [itemid, path]
    $itemid = array_shift($args);

    // Use the itemid to retrieve any relevant data records and perform any security checks.
    
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_citebadges', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Create a badge for a course
 */
function local_citebadges_create_course_badge($courseid, $badgedata) {
    global $DB, $USER;

    $context = context_course::instance($courseid);
    
    // Create badge record
    $badge = new stdClass();
    $badge->name = $badgedata['name'];
    $badge->description = $badgedata['description'];
    $badge->timecreated = time();
    $badge->timemodified = time();
    $badge->usercreated = $USER->id;
    $badge->usermodified = $USER->id;
    $badge->issuername = $badgedata['issuername'] ?? 'CITE System';
    $badge->issuerurl = $badgedata['issuerurl'] ?? '';
    $badge->issuercontact = $badgedata['issuercontact'] ?? '';
    $badge->expiredate = $badgedata['expiredate'] ?? null;
    $badge->expireperiod = $badgedata['expireperiod'] ?? null;
    $badge->type = BADGE_TYPE_COURSE;
    $badge->courseid = $courseid;
    $badge->status = BADGE_STATUS_INACTIVE;
    $badge->version = 1;
    $badge->language = 'en';
    $badge->imageauthorname = $badgedata['imageauthorname'] ?? '';
    $badge->imageauthoremail = $badgedata['imageauthoremail'] ?? '';
    $badge->imageauthorurl = $badgedata['imageauthorurl'] ?? '';
    $badge->imagecaption = $badgedata['imagecaption'] ?? '';

    $badgeid = $DB->insert_record('badge', $badge);

    if ($badgeid) {
        // Handle image if provided
        if (isset($badgedata['image']) && !empty($badgedata['image'])) {
            local_citebadges_save_badge_image($badgeid, $context, $badgedata['image']);
        }

        // Add manual criteria by default
        local_citebadges_add_manual_criteria($badgeid);

        return $badgeid;
    }

    return false;
}

/**
 * Save badge image
 */
function local_citebadges_save_badge_image($badgeid, $context, $imagedata) {
    $fs = get_file_storage();
    
    // Prepare file record
    $filerecord = array(
        'contextid' => $context->id,
        'component' => 'badges',
        'filearea'  => 'badgeimage',
        'itemid'    => $badgeid,
        'filepath'  => '/',
        'filename'  => 'badge_image.png',
        'userid'    => null
    );

    // Create file from image data
    if (is_string($imagedata) && strpos($imagedata, 'data:image') === 0) {
        // Handle base64 encoded image
        list($type, $data) = explode(';', $imagedata);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        $fs->create_file_from_string($filerecord, $data);
    } else if (is_array($imagedata) && isset($imagedata['tmp_name'])) {
        // Handle uploaded file
        $fs->create_file_from_pathname($filerecord, $imagedata['tmp_name']);
    }
}

/**
 * Add manual award criteria to badge
 */
function local_citebadges_add_manual_criteria($badgeid) {
    global $DB;

    $criteria = new stdClass();
    $criteria->badgeid = $badgeid;
    $criteria->criteriatype = BADGE_CRITERIA_TYPE_MANUAL;
    $criteria->method = BADGE_CRITERIA_AGGREGATION_ALL;
    $criteria->description = 'Awarded manually by instructor upon course completion';
    $criteria->descriptionformat = FORMAT_HTML;

    return $DB->insert_record('badge_criteria', $criteria);
}

/**
 * Award badge to user
 */
function local_citebadges_award_badge($badgeid, $userid, $issuerid = null) {
    global $USER, $DB;
    
    if (!$issuerid) {
        $issuerid = $USER->id;
    }

    $badge = new badge($badgeid);
    
    // Check if user already has this badge
    if ($badge->is_issued($userid)) {
        return false;
    }

    // Issue the badge
    $badge->issue($userid, true, $issuerid);
    
    return true;
}

/**
 * Get all badges for a course
 */
function local_citebadges_get_course_badges($courseid) {
    global $DB;
    
    return $DB->get_records('badge', array('courseid' => $courseid, 'type' => BADGE_TYPE_COURSE));
}

/**
 * Get user's badges for a course
 */
function local_citebadges_get_user_course_badges($userid, $courseid) {
    global $DB;
    
    $sql = "SELECT b.*, bi.dateissued, bi.id as issueid 
            FROM {badge} b 
            JOIN {badge_issued} bi ON b.id = bi.badgeid 
            WHERE bi.userid = :userid 
            AND b.courseid = :courseid 
            AND b.type = :type
            ORDER BY bi.dateissued DESC";
    
    return $DB->get_records_sql($sql, [
        'userid' => $userid,
        'courseid' => $courseid,
        'type' => BADGE_TYPE_COURSE
    ]);
}

/**
 * Get all user badges
 */
function local_citebadges_get_user_badges($userid) {
    global $DB;
    
    $sql = "SELECT b.*, bi.dateissued, bi.id as issueid, c.fullname as coursename
            FROM {badge} b 
            JOIN {badge_issued} bi ON b.id = bi.badgeid 
            LEFT JOIN {course} c ON b.courseid = c.id
            WHERE bi.userid = :userid 
            ORDER BY bi.dateissued DESC";
    
    return $DB->get_records_sql($sql, ['userid' => $userid]);
}

/**
 * Activate a badge
 */
function local_citebadges_activate_badge($badgeid) {
    global $DB;
    
    $badge = new badge($badgeid);
    
    if ($badge->status == BADGE_STATUS_INACTIVE) {
        $badge->set_status(BADGE_STATUS_ACTIVE);
        return true;
    }
    
    return false;
}

/**
 * Deactivate a badge
 */
function local_citebadges_deactivate_badge($badgeid) {
    global $DB;
    
    $badge = new badge($badgeid);
    
    if ($badge->status == BADGE_STATUS_ACTIVE) {
        $badge->set_status(BADGE_STATUS_INACTIVE);
        return true;
    }
    
    return false;
}