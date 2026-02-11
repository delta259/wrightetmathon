<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * JWT Authentication Library
 *
 * Simple JWT implementation for mobile API authentication.
 * Uses HS256 (HMAC-SHA256) for token signing.
 *
 * @package     Wright et Mathon POS
 * @subpackage  Libraries
 * @category    Authentication
 */
class Jwt_auth
{
    private $CI;
    private $secret_key;
    private $expiration;
    private $issuer;
    private $algorithm;

    /**
     * Constructor - Load configuration
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('jwt', TRUE);

        $this->secret_key = $this->CI->config->item('jwt_secret_key', 'jwt');
        $this->expiration = $this->CI->config->item('jwt_expiration', 'jwt');
        $this->issuer = $this->CI->config->item('jwt_issuer', 'jwt');
        $this->algorithm = $this->CI->config->item('jwt_algorithm', 'jwt');
    }

    /**
     * Generate a JWT token for an employee
     *
     * @param int $employee_id Employee ID
     * @param string $username Employee username
     * @param array $extra_claims Additional claims to include
     * @return array Token data with token string and expiration
     */
    public function generate_token($employee_id, $username, $extra_claims = array())
    {
        $issued_at = time();
        $expiration_time = $issued_at + $this->expiration;

        // Header
        $header = array(
            'typ' => 'JWT',
            'alg' => $this->algorithm
        );

        // Payload
        $payload = array(
            'iss' => $this->issuer,
            'iat' => $issued_at,
            'exp' => $expiration_time,
            'sub' => $employee_id,
            'username' => $username
        );

        // Merge extra claims
        if (!empty($extra_claims)) {
            $payload = array_merge($payload, $extra_claims);
        }

        // Encode segments
        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));

        // Create signature
        $signature = $this->sign($header_encoded . '.' . $payload_encoded);
        $signature_encoded = $this->base64url_encode($signature);

        // Complete token
        $token = $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;

        return array(
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', $expiration_time),
            'expires_in' => $this->expiration
        );
    }

    /**
     * Validate a JWT token
     *
     * @param string $token JWT token string
     * @return array|false Decoded payload on success, false on failure
     */
    public function validate_token($token)
    {
        if (empty($token)) {
            return false;
        }

        // Split token into parts
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;

        // Verify signature
        $expected_signature = $this->base64url_encode(
            $this->sign($header_encoded . '.' . $payload_encoded)
        );

        if (!hash_equals($expected_signature, $signature_encoded)) {
            return false;
        }

        // Decode payload
        $payload = json_decode($this->base64url_decode($payload_encoded), true);
        if (!$payload) {
            return false;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        // Verify issuer
        if (isset($payload['iss']) && $payload['iss'] !== $this->issuer) {
            return false;
        }

        return $payload;
    }

    /**
     * Extract token from Authorization header
     *
     * @return string|false Token string or false if not found
     */
    public function get_token_from_header()
    {
        $headers = $this->get_authorization_header();

        if (empty($headers)) {
            return false;
        }

        // Check for Bearer token
        if (preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
            return $matches[1];
        }

        return false;
    }

    /**
     * Get Authorization header from various server variables
     *
     * @return string|null Authorization header value
     */
    private function get_authorization_header()
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $request_headers = apache_request_headers();
            $request_headers = array_combine(
                array_map('ucwords', array_keys($request_headers)),
                array_values($request_headers)
            );
            if (isset($request_headers['Authorization'])) {
                $headers = trim($request_headers['Authorization']);
            }
        }

        return $headers;
    }

    /**
     * Sign data using HMAC-SHA256
     *
     * @param string $data Data to sign
     * @return string Raw signature
     */
    private function sign($data)
    {
        return hash_hmac('sha256', $data, $this->secret_key, true);
    }

    /**
     * Base64 URL-safe encode
     *
     * @param string $data Data to encode
     * @return string Encoded string
     */
    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL-safe decode
     *
     * @param string $data Data to decode
     * @return string Decoded string
     */
    private function base64url_decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    /**
     * Get employee ID from valid token
     *
     * @param string $token JWT token
     * @return int|false Employee ID or false
     */
    public function get_employee_id($token = null)
    {
        if ($token === null) {
            $token = $this->get_token_from_header();
        }

        $payload = $this->validate_token($token);

        if ($payload && isset($payload['sub'])) {
            return (int)$payload['sub'];
        }

        return false;
    }

    /**
     * Check if current request has valid authentication
     *
     * @return bool True if authenticated
     */
    public function is_authenticated()
    {
        $token = $this->get_token_from_header();
        return $this->validate_token($token) !== false;
    }
}

/* End of file Jwt_auth.php */
/* Location: ./application/libraries/Jwt_auth.php */
