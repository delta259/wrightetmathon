<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Mobile API Controller
 *
 * RESTful API for the mobile inventory application.
 * All endpoints except login require JWT authentication.
 *
 * @package     Wright et Mathon POS
 * @subpackage  Controllers
 * @category    API
 */
class Api_mobile extends CI_Controller
{
    private $employee_id = null;

    public function __construct()
    {
        parent::__construct();

        // For API: Clear any existing session to avoid cookie mismatch errors
        // This ensures each API request is stateless
        if (isset($this->session)) {
            $this->session->sess_destroy();
        }

        // Load required libraries and models
        $this->load->library('Jwt_auth');
        $this->load->model('Api_token');
        $this->load->model('Inventory_session');
        $this->load->model('Inventory');

        // Set JSON content type for all responses
        header('Content-Type: application/json; charset=utf-8');

        // Handle CORS for mobile app
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');

        // Remove session cookies from response
        header_remove('Set-Cookie');

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Authenticate user and return JWT token
     * POST /api_mobile/login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->_respond(405, array('error' => 'Method not allowed'));
            return;
        }

        $input = $this->_get_json_input();

        if (empty($input['username']) || empty($input['password'])) {
            $this->_respond(400, array('error' => 'Username and password required'));
            return;
        }

        // Validate credentials using Employee model
        $query = $this->db->get_where('employees', array(
            'username' => $input['username'],
            'password' => md5($input['password']),
            'deleted' => 0,
            'branch_code' => $this->config->item('branch_code')
        ), 1);

        if ($query->num_rows() !== 1) {
            $this->_respond(401, array('error' => 'Invalid credentials'));
            return;
        }

        $employee = $query->row();

        // Get employee info
        $employee_info = $this->Employee->get_info($employee->person_id);

        // Generate JWT token
        $token_data = $this->jwt_auth->generate_token(
            $employee->person_id,
            $input['username'],
            array('branch_code' => $this->config->item('branch_code'))
        );

        // Store token in database
        $device_info = isset($input['device_info']) ? $input['device_info'] : null;
        $this->Api_token->save_token(
            $employee->person_id,
            $token_data['token'],
            $token_data['expires_at'],
            $device_info
        );

        // Clean up expired tokens
        $this->Api_token->cleanup_expired();

        $this->_respond(200, array(
            'success' => true,
            'token' => $token_data['token'],
            'expires_at' => $token_data['expires_at'],
            'expires_in' => $token_data['expires_in'],
            'employee' => array(
                'id' => $employee->person_id,
                'username' => $input['username'],
                'first_name' => $employee_info->first_name,
                'last_name' => $employee_info->last_name
            ),
            'branch' => array(
                'code' => $this->config->item('branch_code'),
                'name' => $this->config->item('company')
            )
        ));
    }

    /**
     * Health check endpoint
     * GET /api_mobile/ping
     */
    public function ping()
    {
        echo json_encode(array('status' => 'ok', 'time' => date('Y-m-d H:i:s'), 'version' => '1.0.0'));
        exit;
    }

    /**
     * Logout - Revoke token
     * POST /api_mobile/logout
     */
    public function logout()
    {
        if (!$this->_authenticate()) return;

        $token = $this->jwt_auth->get_token_from_header();
        $this->Api_token->revoke_token($token);

        $this->_respond(200, array('success' => true, 'message' => 'Logged out successfully'));
    }

    /**
     * Get all categories
     * GET /api_mobile/categories
     */
    public function categories()
    {
        if (!$this->_authenticate()) return;

        $branch_code = $this->config->item('branch_code');

        $this->db->from('categories');
        $this->db->where('deleted', 0);
        $this->db->where('branch_code', $branch_code);
        $this->db->where('category_name !=', '');
        $this->db->order_by('category_name', 'ASC');
        $query = $this->db->get();

        $categories = array();
        foreach ($query->result() as $row) {
            $categories[] = array(
                'id' => (int)$row->category_id,
                'name' => $row->category_name
            );
        }

        echo json_encode(array('categories' => $categories, 'count' => count($categories)), JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Get items for inventory
     * GET /api_mobile/items?type=full|rolling_category|rolling_date&category_id=X&days=X
     */
    public function items()
    {
        if (!$this->_authenticate()) return;

        $type = $this->input->get('type') ?: 'full';
        $category_id = $this->input->get('category_id');
        $days = $this->input->get('days') ?: 30;
        $search = $this->input->get('search');
        $session_id = $this->input->get('session_id');
        $limit = (int)($this->input->get('limit') ?: 100);
        $offset = (int)($this->input->get('offset') ?: 0);

        // Build SQL query
        $sql = "SELECT i.item_id, i.name, i.item_number, i.quantity, i.category_id, c.category_name as category_name";

        // If filtering by session, include counted info
        if ($session_id) {
            $sql .= ", si.counted_quantity, si.counted_at";
        }

        $sql .= " FROM ospos_items i
                LEFT JOIN ospos_categories c ON c.category_id = i.category_id";

        // Join with session items to filter only items belonging to the session
        if ($session_id) {
            $sql .= " INNER JOIN ospos_inventory_session_items si ON si.item_id = i.item_id AND si.session_id = ?";
        }

        $sql .= " WHERE i.deleted = 0";
        $params = array();

        if ($session_id) {
            $params[] = (int)$session_id;
        }

        // Apply filters based on type
        if ($type === 'rolling_category' && $category_id) {
            $sql .= " AND i.category_id = ?";
            $params[] = $category_id;
        }

        // Search filter
        if ($search) {
            $sql .= " AND (i.name LIKE ? OR i.item_number LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY i.name ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $query = $this->db->query($sql, $params);

        $items = array();
        foreach ($query->result() as $row) {
            $item = array(
                'id' => (int)$row->item_id,
                'name' => $row->name,
                'item_number' => $row->item_number,
                'quantity' => (float)$row->quantity,
                'category_id' => (int)$row->category_id,
                'category_name' => $row->category_name
            );

            // Include counted info when filtering by session
            if ($session_id && isset($row->counted_quantity)) {
                $item['counted_quantity'] = (float)$row->counted_quantity;
                $item['counted'] = $row->counted_at !== null;
            }

            // For rolling_date, get last inventory date
            if ($type === 'rolling_date') {
                $last_inventory = $this->_get_last_inventory_date($row->item_id);
                $item['last_inventory_date'] = $last_inventory;

                // Filter by days threshold
                if ($last_inventory) {
                    $last_date = strtotime($last_inventory);
                    $threshold_date = strtotime("-{$days} days");
                    if ($last_date > $threshold_date) {
                        continue; // Skip items inventoried recently
                    }
                }
            }

            $items[] = $item;
        }

        $this->_respond(200, array(
            'items' => $items,
            'count' => count($items),
            'limit' => (int)$limit,
            'offset' => (int)$offset
        ));
    }

    /**
     * Get single item by ID
     * GET /api_mobile/item/{id}
     */
    public function item($item_id = null)
    {
        if (!$this->_authenticate()) return;

        if (!$item_id) {
            $this->_respond(400, array('error' => 'Item ID required'));
            return;
        }

        $item = $this->Item->get_info($item_id);

        if (!$item || $item->item_id === -1) {
            $this->_respond(404, array('error' => 'Item not found'));
            return;
        }

        $this->_respond(200, array(
            'item' => array(
                'id' => (int)$item->item_id,
                'name' => $item->name,
                'item_number' => $item->item_number,
                'quantity' => (float)$item->quantity,
                'category_id' => (int)$item->category_id,
                'cost_price' => (float)$item->cost_price,
                'unit_price' => (float)$item->unit_price,
                'reorder_level' => (float)$item->reorder_level,
                'last_inventory_date' => $this->_get_last_inventory_date($item->item_id)
            )
        ));
    }

    /**
     * Get item by barcode/UPC
     * GET /api_mobile/item_by_barcode/{upc}
     */
    public function item_by_barcode($upc = null)
    {
        if (!$this->_authenticate()) return;

        if (!$upc) {
            $this->_respond(400, array('error' => 'Barcode/UPC required'));
            return;
        }

        // Search in barcode, item_number, or supplier_bar_code
        $sql = "SELECT i.*, c.category_name as category_name
                FROM ospos_items i
                LEFT JOIN ospos_categories c ON c.category_id = i.category_id
                LEFT JOIN ospos_items_suppliers s ON s.item_id = i.item_id
                WHERE (i.barcode = ? OR i.item_number = ? OR s.supplier_bar_code = ?)
                AND i.deleted = 0
                LIMIT 1";
        $query = $this->db->query($sql, array($upc, $upc, $upc));

        if ($query->num_rows() === 0) {
            $this->_respond(404, array('error' => 'Item not found', 'barcode' => $upc));
            return;
        }

        $item = $query->row();

        $this->_respond(200, array(
            'item' => array(
                'id' => (int)$item->item_id,
                'name' => $item->name,
                'item_number' => $item->item_number,
                'quantity' => (float)$item->quantity,
                'category_id' => (int)$item->category_id,
                'category_name' => $item->category_name,
                'cost_price' => (float)$item->cost_price,
                'unit_price' => (float)$item->unit_price,
                'last_inventory_date' => $this->_get_last_inventory_date($item->item_id)
            )
        ));
    }

    /**
     * Get active session for the branch
     * GET /api_mobile/active_session
     * Returns the current in_progress session (created by web or mobile)
     */
    public function active_session()
    {
        if (!$this->_authenticate()) return;

        $branch_code = $this->config->item('branch_code');

        $sql = "SELECT s.*, c.category_name, p.first_name, p.last_name
                FROM ospos_inventory_sessions s
                LEFT JOIN ospos_categories c ON c.category_id = s.category_id
                LEFT JOIN ospos_people p ON p.person_id = s.employee_id
                WHERE s.branch_code = ?
                AND s.status = 'in_progress'
                ORDER BY s.started_at DESC
                LIMIT 1";
        $query = $this->db->query($sql, array($branch_code));

        if ($query->num_rows() === 0) {
            $this->_respond(200, array('session' => null, 'message' => 'Aucune session en cours'));
            return;
        }

        $session = $query->row();

        // Get summary
        $summary = $this->Inventory_session->get_session_summary($session->id);

        $this->_respond(200, array(
            'session' => array(
                'id' => (int)$session->id,
                'type' => $session->session_type,
                'category_id' => $session->category_id ? (int)$session->category_id : null,
                'category_name' => $session->category_name,
                'status' => $session->status,
                'total_items' => (int)$session->total_items,
                'items_counted' => (int)$session->items_counted,
                'started_at' => $session->started_at,
                'completed_at' => $session->completed_at,
                'notes' => $session->notes,
                'created_by' => trim($session->first_name . ' ' . $session->last_name)
            ),
            'summary' => array(
                'items_scanned' => (int)$summary->total_items,
                'items_with_variance' => (int)$summary->items_with_variance,
                'items_over' => (int)$summary->items_over,
                'items_under' => (int)$summary->items_under,
                'total_absolute_variance' => (float)$summary->total_absolute_variance
            )
        ));
    }

    /**
     * List sessions or create new session
     * GET /api_mobile/sessions - List sessions (all branch sessions)
     * POST /api_mobile/sessions - Create session
     */
    public function sessions($session_id = null)
    {
        if (!$this->_authenticate()) return;

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // List all sessions for this branch
            $status = $this->input->get('status');
            $branch_code = $this->config->item('branch_code');

            $sql = "SELECT s.*, c.category_name, p.first_name, p.last_name
                    FROM ospos_inventory_sessions s
                    LEFT JOIN ospos_categories c ON c.category_id = s.category_id
                    LEFT JOIN ospos_people p ON p.person_id = s.employee_id
                    WHERE s.branch_code = ?";
            $params = array($branch_code);

            if ($status !== null && $status !== '') {
                $sql .= " AND s.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY s.started_at DESC LIMIT 50";
            $query = $this->db->query($sql, $params);

            $result = array();
            foreach ($query->result() as $row) {
                $result[] = array(
                    'id' => (int)$row->id,
                    'type' => $row->session_type,
                    'category_id' => $row->category_id ? (int)$row->category_id : null,
                    'category_name' => $row->category_name,
                    'status' => $row->status,
                    'total_items' => (int)$row->total_items,
                    'items_counted' => (int)$row->items_counted,
                    'started_at' => $row->started_at,
                    'completed_at' => $row->completed_at,
                    'created_by' => trim($row->first_name . ' ' . $row->last_name)
                );
            }

            $this->_respond(200, array('sessions' => $result));
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Create new session
            $input = $this->_get_json_input();

            if (empty($input['type'])) {
                $this->_respond(400, array('error' => 'Session type required'));
                return;
            }

            $valid_types = array('full', 'rolling_category', 'rolling_date');
            if (!in_array($input['type'], $valid_types)) {
                $this->_respond(400, array('error' => 'Invalid session type'));
                return;
            }

            $category_id = isset($input['category_id']) ? $input['category_id'] : null;
            $days_threshold = isset($input['days_threshold']) ? $input['days_threshold'] : null;
            $notes = isset($input['notes']) ? $input['notes'] : null;

            $session_id = $this->Inventory_session->create_session(
                $this->employee_id,
                $input['type'],
                $category_id,
                $days_threshold,
                $notes
            );

            if (!$session_id) {
                $this->_respond(500, array('error' => 'Failed to create session'));
                return;
            }

            // Get total items count for this session
            $total_items = $this->_count_items_for_session($input['type'], $category_id, $days_threshold);
            $this->Inventory_session->update_counters($session_id, $total_items, 0);

            $session = $this->Inventory_session->get_session($session_id);

            $this->_respond(201, array(
                'success' => true,
                'session' => array(
                    'id' => (int)$session->id,
                    'type' => $session->session_type,
                    'category_id' => $session->category_id ? (int)$session->category_id : null,
                    'category_name' => $session->category_name,
                    'status' => $session->status,
                    'total_items' => (int)$session->total_items,
                    'items_counted' => (int)$session->items_counted,
                    'started_at' => $session->started_at
                )
            ));
            return;
        }

        $this->_respond(405, array('error' => 'Method not allowed'));
    }

    /**
     * Get session details
     * GET /api_mobile/session/{id}
     */
    public function session($session_id = null)
    {
        if (!$this->_authenticate()) return;

        if (!$session_id) {
            $this->_respond(400, array('error' => 'Session ID required'));
            return;
        }

        $session = $this->Inventory_session->get_session($session_id);

        if (!$session) {
            $this->_respond(404, array('error' => 'Session not found'));
            return;
        }

        // Verify same branch
        $branch_code = $this->config->item('branch_code');
        if ($session->branch_code !== $branch_code) {
            $this->_respond(403, array('error' => 'Session belongs to another branch'));
            return;
        }

        $summary = $this->Inventory_session->get_session_summary($session_id);
        $items = $this->Inventory_session->get_session_items($session_id);

        $items_list = array();
        foreach ($items->result() as $row) {
            $items_list[] = array(
                'id' => (int)$row->id,
                'item_id' => (int)$row->item_id,
                'item_name' => $row->item_name,
                'item_number' => $row->item_number,
                'expected_quantity' => (float)$row->expected_quantity,
                'counted_quantity' => (float)$row->counted_quantity,
                'variance' => (float)$row->variance,
                'counted_at' => isset($row->counted_at) ? $row->counted_at : null,
                'scanned_at' => $row->scanned_at
            );
        }

        $this->_respond(200, array(
            'session' => array(
                'id' => (int)$session->id,
                'type' => $session->session_type,
                'category_id' => $session->category_id ? (int)$session->category_id : null,
                'category_name' => $session->category_name,
                'status' => $session->status,
                'total_items' => (int)$session->total_items,
                'items_counted' => (int)$session->items_counted,
                'started_at' => $session->started_at,
                'completed_at' => $session->completed_at,
                'notes' => $session->notes
            ),
            'summary' => array(
                'items_scanned' => (int)$summary->total_items,
                'items_with_variance' => (int)$summary->items_with_variance,
                'items_over' => (int)$summary->items_over,
                'items_under' => (int)$summary->items_under,
                'total_absolute_variance' => (float)$summary->total_absolute_variance
            ),
            'items' => $items_list
        ));
    }

    /**
     * Add item to session
     * POST /api_mobile/session/{id}/item
     */
    public function session_item($session_id = null)
    {
        if (!$this->_authenticate()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->_respond(405, array('error' => 'Method not allowed'));
            return;
        }

        if (!$session_id) {
            $this->_respond(400, array('error' => 'Session ID required'));
            return;
        }

        $session = $this->Inventory_session->get_session($session_id);

        if (!$session) {
            $this->_respond(404, array('error' => 'Session not found'));
            return;
        }

        // Verify same branch
        $branch_code = $this->config->item('branch_code');
        if ($session->branch_code !== $branch_code) {
            $this->_respond(403, array('error' => 'Session belongs to another branch'));
            return;
        }

        if ($session->status !== 'in_progress') {
            $this->_respond(400, array('error' => 'Session is not in progress'));
            return;
        }

        $input = $this->_get_json_input();

        if (empty($input['item_id']) || !isset($input['counted_quantity'])) {
            $this->_respond(400, array('error' => 'Item ID and counted quantity required'));
            return;
        }

        // Get current item quantity
        $item = $this->Item->get_info($input['item_id']);
        if (!$item || $item->item_id === -1) {
            $this->_respond(404, array('error' => 'Item not found'));
            return;
        }

        $expected_quantity = (float)$item->quantity;
        $counted_quantity = (float)$input['counted_quantity'];

        $result = $this->Inventory_session->add_session_item(
            $session_id,
            $input['item_id'],
            $expected_quantity,
            $counted_quantity,
            true,
            $this->employee_id
        );

        if (!$result) {
            $this->_respond(500, array('error' => 'Failed to add item'));
            return;
        }

        $this->_respond(200, array(
            'success' => true,
            'item' => array(
                'item_id' => (int)$input['item_id'],
                'item_name' => $item->name,
                'expected_quantity' => $expected_quantity,
                'counted_quantity' => $counted_quantity,
                'variance' => $counted_quantity - $expected_quantity
            )
        ));
    }

    /**
     * Complete/cancel session
     * POST /api_mobile/session/{id}/complete
     * POST /api_mobile/session/{id}/cancel
     */
    public function session_action($session_id = null, $action = null)
    {
        if (!$this->_authenticate()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->_respond(405, array('error' => 'Method not allowed'));
            return;
        }

        if (!$session_id || !$action) {
            $this->_respond(400, array('error' => 'Session ID and action required'));
            return;
        }

        $session = $this->Inventory_session->get_session($session_id);

        if (!$session || $session->employee_id != $this->employee_id) {
            $this->_respond(404, array('error' => 'Session not found'));
            return;
        }

        if ($session->status !== 'in_progress') {
            $this->_respond(400, array('error' => 'Session is not in progress'));
            return;
        }

        if ($action === 'complete') {
            $result = $this->Inventory_session->complete_session($session_id, $this->employee_id);
            $this->_respond(200, array(
                'success' => true,
                'message' => 'Session completed',
                'items_processed' => $result['items_processed'],
                'adjustments_made' => $result['adjustments_made']
            ));
        } elseif ($action === 'cancel') {
            $this->Inventory_session->update_status($session_id, 'cancelled');
            $this->_respond(200, array(
                'success' => true,
                'message' => 'Session cancelled'
            ));
        } else {
            $this->_respond(400, array('error' => 'Invalid action'));
        }
    }

    /**
     * Sync offline data
     * POST /api_mobile/sync
     */
    public function sync()
    {
        if (!$this->_authenticate()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->_respond(405, array('error' => 'Method not allowed'));
            return;
        }

        $input = $this->_get_json_input();

        if (empty($input['session_id']) || empty($input['items'])) {
            $this->_respond(400, array('error' => 'Session ID and items required'));
            return;
        }

        $session = $this->Inventory_session->get_session($input['session_id']);

        if (!$session || $session->employee_id != $this->employee_id) {
            $this->_respond(404, array('error' => 'Session not found'));
            return;
        }

        $result = $this->Inventory_session->sync_offline_items($input['session_id'], $input['items']);

        $this->_respond(200, array(
            'success' => true,
            'synced' => $result['synced'],
            'errors' => $result['errors']
        ));
    }

    // ==================== Private Helper Methods ====================

    /**
     * Authenticate request using JWT
     *
     * @return bool True if authenticated
     */
    private function _authenticate()
    {
        $employee_id = $this->jwt_auth->get_employee_id();

        if (!$employee_id) {
            echo json_encode(array('error' => 'Unauthorized'));
            http_response_code(401);
            exit;
        }

        // Verify token exists in database (not revoked)
        $token = $this->jwt_auth->get_token_from_header();
        $token_record = $this->Api_token->get_token($token);

        if (!$token_record) {
            echo json_encode(array('error' => 'Token revoked or expired'));
            http_response_code(401);
            exit;
        }

        $this->employee_id = $employee_id;
        return true;
    }

    /**
     * Get JSON input from request body
     *
     * @return array Decoded JSON or empty array
     */
    private function _get_json_input()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        return is_array($data) ? $data : array();
    }

    /**
     * Send JSON response
     *
     * @param int $status HTTP status code
     * @param array $data Response data
     */
    private function _respond($status, $data)
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Get last inventory date for an item
     *
     * @param int $item_id Item ID
     * @return string|null Date or null
     */
    private function _get_last_inventory_date($item_id)
    {
        // Use raw query to avoid Active Record state issues
        $sql = "SELECT MAX(trans_date) as last_date
                FROM ospos_inventory
                WHERE trans_items = ?
                AND (trans_comment LIKE '%Inventaire comptable%'
                     OR trans_comment LIKE '%Stock Tournant%'
                     OR trans_comment LIKE '%Inventaire Mobile%')";

        $query = $this->db->query($sql, array($item_id));

        if ($query && $query->num_rows() > 0) {
            $row = $query->row();
            return $row->last_date;
        }

        return null;
    }

    /**
     * Count items for a session type
     *
     * @param string $type Session type
     * @param int|null $category_id Category ID
     * @param int|null $days Days threshold
     * @return int Count
     */
    private function _count_items_for_session($type, $category_id = null, $days = null)
    {
        $this->db->from('items');
        $this->db->where('deleted', 0);

        if ($type === 'rolling_category' && $category_id) {
            $this->db->where('category_id', $category_id);
        }

        return $this->db->count_all_results();
    }
}

/* End of file api_mobile.php */
/* Location: ./application/controllers/api_mobile.php */
