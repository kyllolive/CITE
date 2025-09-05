<?php
/**
 * CITE SSO Authentication Plugin
 *
 * @package    auth_cite
 * @copyright  2025 CITE Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

/**
 * CITE SSO authentication plugin
 */
class auth_plugin_cite extends auth_plugin_base {

    /**
     * Constructor
     */
    public function __construct() {
        $this->authtype = 'cite';
        $this->config = get_config('auth_cite');
    }

    /**
     * Old syntax of class constructor
     */
    public function auth_plugin_cite() {
        $this->__construct();
    }

    /**
     * Returns true if the username and password work and false if they are wrong or don't exist
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure
     */
    function user_login($username, $password) {
        // This plugin doesn't do direct username/password auth
        // SSO tokens are handled separately
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's password
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Returns the URL for changing the user's password, or empty if the default URL can be used
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password
     *
     * @return bool
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Prints a form for configuring this authentication plugin
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin
     *
     * @param array $page An object containing all the data for this page
     */
    function config_form($config, $err, $user_fields) {
        include 'config.html';
    }

    /**
     * Processes and stores configuration data for this authentication plugin
     */
    function process_config($config) {
        // Set to defaults if undefined
        if (!isset($config->cite_url)) {
            $config->cite_url = '';
        }
        if (!isset($config->sso_secret)) {
            $config->sso_secret = '';
        }
        if (!isset($config->auto_create_users)) {
            $config->auto_create_users = 0;
        }
        if (!isset($config->auto_update_users)) {
            $config->auto_update_users = 0;
        }

        // Save settings
        set_config('cite_url', $config->cite_url, 'auth_cite');
        set_config('sso_secret', $config->sso_secret, 'auth_cite');
        set_config('auto_create_users', $config->auto_create_users, 'auth_cite');
        set_config('auto_update_users', $config->auto_update_users, 'auth_cite');

        return true;
    }

    /**
     * A chance to validate form data, and last chance to do stuff before it gets inserted in config_plugin
     * @param object $form with submitted configuration settings (without system magic quotes)
     * @param array $err array of error messages
     */
    function validate_form($form, &$err) {
        if (empty($form->cite_url)) {
            $err['cite_url'] = get_string('err_cite_url', 'auth_cite');
        }
        if (empty($form->sso_secret)) {
            $err['sso_secret'] = get_string('err_sso_secret', 'auth_cite');
        }
    }

    /**
     * Hook called before logging in a user
     * This is where we handle the SSO token verification
     */
    function pre_loginpage_hook() {
        global $SESSION, $CFG;
        
        $token = optional_param('token', '', PARAM_RAW);
        
        if (!empty($token)) {
            $this->handle_sso_login($token);
        }
    }

    /**
     * Handle SSO login with token
     */
    private function handle_sso_login($token) {
        global $CFG, $DB, $SESSION;

        try {
            // Verify and decode the SSO token
            $userData = $this->verify_sso_token($token);
            
            if (!$userData) {
                print_error('Invalid SSO token');
                return;
            }

            // Get or create user
            $user = $this->get_or_create_user($userData);
            
            if (!$user) {
                print_error('Failed to authenticate user');
                return;
            }

            // Complete the login
            complete_user_login($user);
            
            // Redirect to return URL or dashboard
            $returnurl = $userData['returnurl'] ?? $CFG->wwwroot;
            redirect($returnurl);

        } catch (Exception $e) {
            debugging('CITE SSO Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            print_error('SSO authentication failed');
        }
    }

    /**
     * Verify SSO token
     */
    private function verify_sso_token($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        list($payload, $signature) = $parts;
        
        // Verify signature
        $expectedSignature = hash_hmac('sha256', $payload, $this->config->sso_secret);
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        // Decode payload
        $data = json_decode(base64_decode($payload), true);
        if (!$data) {
            return null;
        }

        // Check timestamp (5 minute validity)
        if (time() - $data['timestamp'] > 300) {
            return null;
        }

        return $data;
    }

    /**
     * Get or create user based on SSO data
     */
    private function get_or_create_user($userData) {
        global $DB, $CFG;

        // Try to find existing user
        $user = $DB->get_record('user', array('email' => $userData['email'], 'deleted' => 0));

        if (!$user && $this->config->auto_create_users) {
            // Create new user
            $user = new stdClass();
            $user->auth = 'cite';
            $user->confirmed = 1;
            $user->username = strtolower(str_replace(' ', '', $userData['firstname'] . $userData['lastname']));
            $user->email = $userData['email'];
            $user->firstname = $userData['firstname'];
            $user->lastname = $userData['lastname'];
            $user->password = AUTH_PASSWORD_NOT_CACHED;
            $user->timecreated = time();
            $user->timemodified = time();

            // Ensure unique username
            $baseUsername = $user->username;
            $counter = 1;
            while ($DB->record_exists('user', array('username' => $user->username))) {
                $user->username = $baseUsername . $counter;
                $counter++;
            }

            $user->id = $DB->insert_record('user', $user);
            $user = $DB->get_record('user', array('id' => $user->id));

        } else if ($user && $this->config->auto_update_users) {
            // Update existing user
            $update = new stdClass();
            $update->id = $user->id;
            $update->firstname = $userData['firstname'];
            $update->lastname = $userData['lastname'];
            $update->timemodified = time();
            
            $DB->update_record('user', $update);
            $user = $DB->get_record('user', array('id' => $user->id));
        }

        return $user;
    }

    /**
     * Logout hook - redirect to CITE
     */
    function logoutpage_hook() {
        global $CFG;
        
        if (!empty($this->config->cite_url)) {
            redirect($this->config->cite_url . '/auth/logout');
        }
    }
}