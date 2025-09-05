<?php
/**
 * CITE SSO Login Handler
 *
 * @package    auth_cite
 * @copyright  2025 CITE Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/auth/cite/auth.php');

// This file handles SSO login from CITE
$token = optional_param('token', '', PARAM_RAW);

if (empty($token)) {
    print_error('Missing SSO token');
}

// Initialize auth plugin
$authplugin = new auth_plugin_cite();

// Handle the SSO login
try {
    // Verify and decode the SSO token
    $userData = $authplugin->verify_sso_token($token);
    
    if (!$userData) {
        print_error('Invalid or expired SSO token');
    }

    // Get or create user
    $user = $authplugin->get_or_create_user($userData);
    
    if (!$user) {
        print_error('Failed to authenticate user');
    }

    // Complete the login
    complete_user_login($user);
    
    // Redirect to return URL or dashboard
    $returnurl = $userData['returnurl'] ?? $CFG->wwwroot;
    redirect($returnurl);

} catch (Exception $e) {
    debugging('CITE SSO Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
    print_error('SSO authentication failed: ' . $e->getMessage());
}