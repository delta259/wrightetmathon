<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * JWT Configuration for Mobile API
 *
 * This file contains configuration settings for JSON Web Token authentication
 * used by the mobile inventory application.
 */

// Read JWT secret from INI file (priority) or use fallback
$jwt_secret = '16648ecabd2769eeb6349cd1ff79961b4a27b2e2886b7c776720fb1bec1ff675';
$ini_path = '/var/www/html/wrightetmathon.ini';
if (file_exists($ini_path)) {
    $ini = file_get_contents($ini_path);
    if (preg_match("/jwt_secret='([^']+)'/", $ini, $m)) {
        $jwt_secret = $m[1];
    }
}
$config['jwt_secret_key'] = $jwt_secret;

// Token expiration time in seconds (24 hours = 86400)
$config['jwt_expiration'] = 86400;

// Issuer claim
$config['jwt_issuer'] = 'wrightetmathon-pos';

// Algorithm for signing
$config['jwt_algorithm'] = 'HS256';

// Refresh token expiration (7 days)
$config['jwt_refresh_expiration'] = 604800;

/* End of file jwt.php */
/* Location: ./application/config/jwt.php */
