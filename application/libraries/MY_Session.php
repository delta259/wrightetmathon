<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extended Session Library
 *
 * Extends the CI Session library to allow API routes to bypass session validation.
 * This prevents "session cookie data did not match" errors for stateless API requests.
 */
class MY_Session extends CI_Session
{
    /**
     * Constructor
     *
     * Check if this is an API request and skip session validation if so.
     */
    public function __construct($params = array())
    {
        // Check if this is an API request by looking at the URI
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        // If this is an API route, don't load/validate session
        if (strpos($uri, '/api_mobile') !== false || strpos($uri, 'api_mobile') !== false) {
            // Initialize minimal session data without validation
            $this->CI =& get_instance();

            // Set sess_cookie_name from config
            $this->sess_cookie_name = $this->CI->config->item('sess_cookie_name');
            if ($this->sess_cookie_name === FALSE) {
                $this->sess_cookie_name = 'ci_session';
            }

            // Initialize empty userdata
            $this->userdata = array(
                'session_id' => '',
                'ip_address' => '',
                'user_agent' => '',
                'last_activity' => time()
            );

            return; // Skip parent constructor entirely
        }

        // Normal session handling for non-API requests
        parent::__construct($params);
    }

    /**
     * Override sess_read to handle API requests
     */
    function sess_read()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        if (strpos($uri, '/api_mobile') !== false || strpos($uri, 'api_mobile') !== false) {
            // Return empty session data for API requests
            return false;
        }

        return parent::sess_read();
    }

    /**
     * Override sess_write to skip for API requests
     */
    function sess_write()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        if (strpos($uri, '/api_mobile') !== false || strpos($uri, 'api_mobile') !== false) {
            return;
        }

        parent::sess_write();
    }

    /**
     * Override sess_destroy for API requests
     */
    function sess_destroy()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        if (strpos($uri, '/api_mobile') !== false || strpos($uri, 'api_mobile') !== false) {
            // Clear userdata
            $this->userdata = array();
            return;
        }

        parent::sess_destroy();
    }
}

/* End of file MY_Session.php */
/* Location: ./application/libraries/MY_Session.php */
