<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * API Token Model
 *
 * Manages JWT tokens for mobile API authentication.
 *
 * @package     Wright et Mathon POS
 * @subpackage  Models
 * @category    Authentication
 */
class Api_token extends CI_Model
{
    /**
     * Store a new token in the database
     *
     * @param int $employee_id Employee ID
     * @param string $token JWT token string
     * @param string $expires_at Expiration datetime
     * @param string $device_info Optional device information
     * @return int|false Inserted ID or false on failure
     */
    function save_token($employee_id, $token, $expires_at, $device_info = null)
    {
        $data = array(
            'employee_id' => $employee_id,
            'token' => $token,
            'expires_at' => $expires_at,
            'device_info' => $device_info,
            'created_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert('api_tokens', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Check if a token exists and is valid
     *
     * @param string $token JWT token string
     * @return object|false Token record or false
     */
    function get_token($token)
    {
        $sql = "SELECT * FROM ospos_api_tokens WHERE token = ? AND expires_at > ? LIMIT 1";
        $query = $this->db->query($sql, array($token, date('Y-m-d H:i:s')));

        if ($query && $query->num_rows() == 1) {
            return $query->row();
        }

        return false;
    }

    /**
     * Get all active tokens for an employee
     *
     * @param int $employee_id Employee ID
     * @return object Database result
     */
    function get_employee_tokens($employee_id)
    {
        $this->db->from('api_tokens');
        $this->db->where('employee_id', $employee_id);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $this->db->order_by('created_at', 'DESC');

        return $this->db->get();
    }

    /**
     * Revoke a specific token
     *
     * @param string $token JWT token string
     * @return bool Success
     */
    function revoke_token($token)
    {
        $this->db->where('token', $token);
        $this->db->delete('api_tokens');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Revoke all tokens for an employee
     *
     * @param int $employee_id Employee ID
     * @return int Number of tokens revoked
     */
    function revoke_all_employee_tokens($employee_id)
    {
        $this->db->where('employee_id', $employee_id);
        $this->db->delete('api_tokens');

        return $this->db->affected_rows();
    }

    /**
     * Clean up expired tokens
     *
     * @return int Number of tokens deleted
     */
    function cleanup_expired()
    {
        $this->db->where('expires_at <', date('Y-m-d H:i:s'));
        $this->db->delete('api_tokens');

        return $this->db->affected_rows();
    }

    /**
     * Update token expiration (for refresh)
     *
     * @param string $token JWT token string
     * @param string $new_expires_at New expiration datetime
     * @return bool Success
     */
    function extend_token($token, $new_expires_at)
    {
        $this->db->where('token', $token);
        $this->db->update('api_tokens', array('expires_at' => $new_expires_at));

        return $this->db->affected_rows() > 0;
    }
}

/* End of file api_token.php */
/* Location: ./application/models/api_token.php */
