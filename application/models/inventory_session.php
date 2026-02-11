<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Inventory Session Model
 *
 * Manages inventory counting sessions for mobile application and web inventaire module.
 *
 * @package     Wright et Mathon POS
 * @subpackage  Models
 * @category    Inventory
 */
class Inventory_session extends CI_Model
{
    /**
     * Create a new inventory session
     */
    function create_session($employee_id, $session_type, $category_id = null, $days_threshold = null, $notes = null, $branch_code = '')
    {
        if (empty($branch_code)) {
            $CI =& get_instance();
            $branch_code = $CI->config->item('branch_code');
        }
        $data = array(
            'employee_id' => $employee_id,
            'session_type' => $session_type,
            'category_id' => $category_id,
            'days_threshold' => $days_threshold,
            'status' => 'in_progress',
            'notes' => $notes,
            'branch_code' => $branch_code,
            'started_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert('inventory_sessions', $data);

        if ($this->db->affected_rows() > 0) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Get session by ID
     */
    function get_session($session_id)
    {
        $this->db->from('inventory_sessions');
        $this->db->join('employees', 'employees.person_id = inventory_sessions.employee_id', 'left');
        $this->db->join('people', 'people.person_id = employees.person_id', 'left');
        $this->db->join('categories', 'categories.category_id = inventory_sessions.category_id', 'left');
        $this->db->select('inventory_sessions.*, people.first_name, people.last_name, categories.category_name as category_name');
        $this->db->where('inventory_sessions.id', $session_id);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            return $query->row();
        }

        return false;
    }

    /**
     * Get all sessions for an employee
     */
    function get_employee_sessions($employee_id, $status = null, $limit = 50, $offset = 0)
    {
        $sql = "SELECT s.*, c.category_name as category_name
                FROM ospos_inventory_sessions s
                LEFT JOIN ospos_categories c ON c.category_id = s.category_id
                WHERE s.employee_id = " . (int)$employee_id;

        if ($status !== null && $status !== '') {
            $sql .= " AND s.status = '" . $this->db->escape_str($status) . "'";
        }

        $sql .= " ORDER BY s.started_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        return $this->db->query($sql);
    }

    /**
     * Update session status
     */
    function update_status($session_id, $status)
    {
        $data = array('status' => $status);

        if ($status === 'completed' || $status === 'cancelled') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $session_id);
        $this->db->update('inventory_sessions', $data);

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Update session counters
     */
    function update_counters($session_id, $total_items, $items_counted)
    {
        $this->db->where('id', $session_id);
        $this->db->update('inventory_sessions', array(
            'total_items' => $total_items,
            'items_counted' => $items_counted
        ));

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Add item to session (mobile API)
     */
    function add_session_item($session_id, $item_id, $expected_quantity, $counted_quantity, $synced = true, $counted_by = null)
    {
        $session_id = (int)$session_id;
        $item_id = (int)$item_id;
        $expected_quantity = (float)$expected_quantity;
        $counted_quantity = (float)$counted_quantity;
        $synced_val = $synced ? 1 : 0;
        $now = date('Y-m-d H:i:s');
        $counted_by_sql = $counted_by ? (int)$counted_by : 'NULL';

        $sql = "SELECT id, counted_at FROM ospos_inventory_session_items WHERE session_id = $session_id AND item_id = $item_id LIMIT 1";
        $existing = $this->db->query($sql);

        if ($existing && $existing->num_rows() > 0) {
            $row = $existing->row();
            $was_uncounted = ($row->counted_at === null);

            $sql = "UPDATE ospos_inventory_session_items
                    SET expected_quantity = $expected_quantity,
                        counted_quantity = $counted_quantity,
                        counted_by = $counted_by_sql,
                        counted_at = '$now',
                        scanned_at = '$now',
                        synced = $synced_val
                    WHERE session_id = $session_id AND item_id = $item_id";
            $this->db->query($sql);

            // Recalculate items_counted from actual data
            $this->recalculate_items_counted($session_id);

            return $row->id;
        }

        $sql = "INSERT INTO ospos_inventory_session_items
                (session_id, item_id, expected_quantity, counted_quantity, counted_by, counted_at, scanned_at, synced)
                VALUES ($session_id, $item_id, $expected_quantity, $counted_quantity, $counted_by_sql, '$now', '$now', $synced_val)";
        $this->db->query($sql);

        if ($this->db->affected_rows() > 0) {
            $this->recalculate_items_counted($session_id);
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Recalculate items_counted from actual counted_at data
     */
    private function recalculate_items_counted($session_id)
    {
        $this->db->select('COUNT(*) as cnt');
        $this->db->where('session_id', $session_id);
        $this->db->where('counted_at IS NOT NULL', null, false);
        $count_query = $this->db->get('inventory_session_items');

        $items_counted = 0;
        if ($count_query && $count_query->num_rows() > 0) {
            $items_counted = $count_query->row()->cnt;
        }

        $this->db->where('id', $session_id);
        $this->db->update('inventory_sessions', array('items_counted' => $items_counted));
    }

    /**
     * Get all items in a session (mobile API)
     */
    function get_session_items($session_id)
    {
        $this->db->from('inventory_session_items');
        $this->db->join('items', 'items.item_id = inventory_session_items.item_id');
        $this->db->join('categories', 'categories.category_id = items.category_id', 'left');
        $this->db->select('inventory_session_items.*, items.name as item_name, items.item_number, items.category_id, categories.category_name as category_name');
        $this->db->where('session_id', $session_id);
        $this->db->order_by('scanned_at', 'DESC');

        return $this->db->get();
    }

    /**
     * Get session summary with variances
     */
    function get_session_summary($session_id)
    {
        $this->db->select('
            COUNT(*) as total_items,
            SUM(CASE WHEN variance != 0 THEN 1 ELSE 0 END) as items_with_variance,
            SUM(CASE WHEN variance > 0 THEN 1 ELSE 0 END) as items_over,
            SUM(CASE WHEN variance < 0 THEN 1 ELSE 0 END) as items_under,
            SUM(ABS(variance)) as total_absolute_variance
        ');
        $this->db->from('inventory_session_items');
        $this->db->where('session_id', $session_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        $empty = new stdClass();
        $empty->total_items = 0;
        $empty->items_with_variance = 0;
        $empty->items_over = 0;
        $empty->items_under = 0;
        $empty->total_absolute_variance = 0;
        return $empty;
    }

    /**
     * Complete session and apply inventory adjustments (mobile API)
     */
    function complete_session($session_id, $employee_id)
    {
        $this->db->from('inventory_session_items');
        $this->db->where('session_id', $session_id);
        $items = $this->db->get();

        $adjustments_made = 0;

        foreach ($items->result() as $item) {
            if ($item->variance != 0) {
                $inventory_data = array(
                    'trans_items' => $item->item_id,
                    'trans_user' => $employee_id,
                    'trans_date' => date('Y-m-d H:i:s'),
                    'trans_comment' => 'Inventaire Mobile - Ajustement',
                    'trans_inventory' => $item->counted_quantity
                );

                $this->db->insert('inventory', $inventory_data);

                $this->db->where('item_id', $item->item_id);
                $this->db->update('items', array('quantity' => $item->counted_quantity));

                $adjustments_made++;
            }
        }

        $this->update_status($session_id, 'completed');

        return array(
            'items_processed' => $items->num_rows(),
            'adjustments_made' => $adjustments_made
        );
    }

    /**
     * Get items for inventory based on session type (mobile API)
     */
    function get_items_for_inventory($session_type, $category_id = null, $days_threshold = null)
    {
        $this->db->from('items');
        $this->db->join('categories', 'categories.category_id = items.category_id', 'left');
        $this->db->select('items.item_id, items.name, items.item_number, items.quantity, items.category_id, categories.category_name as category_name');
        $this->db->where('items.deleted', 0);

        switch ($session_type) {
            case 'rolling_category':
                if ($category_id !== null) {
                    $this->db->where('items.category_id', $category_id);
                }
                break;

            case 'rolling_date':
                break;

            case 'full':
            default:
                break;
        }

        $this->db->order_by('items.name', 'ASC');

        return $this->db->get();
    }

    /**
     * Sync offline items (mobile API)
     */
    function sync_offline_items($session_id, $items)
    {
        $synced = 0;
        $errors = array();

        foreach ($items as $item) {
            $result = $this->add_session_item(
                $session_id,
                $item['item_id'],
                $item['expected_quantity'],
                $item['counted_quantity'],
                true
            );

            if ($result !== false) {
                $synced++;
            } else {
                $errors[] = $item['item_id'];
            }
        }

        return array(
            'synced' => $synced,
            'errors' => $errors
        );
    }

    // =========================================================================
    // WEB INVENTAIRE MODULE - New methods
    // =========================================================================

    /**
     * Get active session for the current branch
     */
    function get_active_session($branch_code)
    {
        $this->db->from('inventory_sessions');
        $this->db->join('people', 'people.person_id = inventory_sessions.employee_id', 'left');
        $this->db->select('inventory_sessions.*, people.first_name, people.last_name');
        $this->db->where('inventory_sessions.status', 'in_progress');
        $this->db->where("(ospos_inventory_sessions.branch_code = " . $this->db->escape($branch_code) . " OR ospos_inventory_sessions.branch_code = '')", null, false);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return false;
    }

    /**
     * Get all sessions with pagination and optional status filter
     */
    function get_all_sessions($branch_code, $limit = 50, $offset = 0, $status = null)
    {
        $this->db->from('inventory_sessions');
        $this->db->join('people', 'people.person_id = inventory_sessions.employee_id', 'left');
        $this->db->join('categories', 'categories.category_id = inventory_sessions.category_id', 'left');
        $this->db->join('suppliers sup', 'sup.person_id = inventory_sessions.supplier_id', 'left');
        $this->db->select('inventory_sessions.*, people.first_name, people.last_name, categories.category_name as category_name, sup.company_name as supplier_name');
        $this->db->where("(ospos_inventory_sessions.branch_code = " . $this->db->escape($branch_code) . " OR ospos_inventory_sessions.branch_code = '')", null, false);

        if ($status !== null && $status !== '') {
            $this->db->where('inventory_sessions.status', $status);
        }

        $this->db->order_by('inventory_sessions.started_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get();
    }

    /**
     * Count all sessions for pagination
     */
    function count_all_sessions($branch_code, $status = null)
    {
        $this->db->from('inventory_sessions');
        $this->db->where("(ospos_inventory_sessions.branch_code = " . $this->db->escape($branch_code) . " OR ospos_inventory_sessions.branch_code = '')", null, false);

        if ($status !== null && $status !== '') {
            $this->db->where('status', $status);
        }

        return $this->db->count_all_results();
    }

    /**
     * Create a session and populate it with items based on type
     */
    function create_and_populate_session($employee_id, $session_type, $params = array())
    {
        $branch_code = isset($params['branch_code']) ? $params['branch_code'] : '';
        // Fallback : si branch_code vide, lire depuis la config
        if (empty($branch_code)) {
            $CI =& get_instance();
            $branch_code = $CI->config->item('branch_code');
        }
        $category_id = isset($params['category_id']) ? $params['category_id'] : null;
        $cutoff_date = isset($params['cutoff_date']) ? $params['cutoff_date'] : null;
        $supplier_id = isset($params['supplier_id']) ? $params['supplier_id'] : null;
        $search_term = isset($params['search_term']) ? $params['search_term'] : null;
        $notes = isset($params['notes']) ? $params['notes'] : null;

        // Append search term to notes for display in session list
        if (!empty($search_term)) {
            $search_prefix = 'Recherche: ' . $search_term;
            $notes = !empty($notes) ? $search_prefix . ' | ' . $notes : $search_prefix;
        }

        // Create session
        $session_data = array(
            'employee_id' => $employee_id,
            'session_type' => $session_type,
            'category_id' => $category_id,
            'supplier_id' => $supplier_id,
            'cutoff_date' => $cutoff_date,
            'status' => 'in_progress',
            'notes' => $notes,
            'branch_code' => $branch_code,
            'started_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert('inventory_sessions', $session_data);

        if ($this->db->affected_rows() <= 0) {
            return false;
        }

        $session_id = $this->db->insert_id();

        // Build item selection query based on type
        $where_clause = "i.deleted = 0 AND i.branch_code = " . $this->db->escape($branch_code);
        // Exclude DEFECT category
        $where_clause .= " AND (i.category NOT LIKE '%DEFECT%' OR i.category IS NULL)";

        switch ($session_type) {
            case 'rolling':
                $where_clause .= " AND i.rolling_inventory_indicator = 0";
                break;

            case 'partial':
                if ($category_id !== null && $category_id > 0) {
                    $where_clause .= " AND i.category_id = " . (int)$category_id;
                } elseif ($supplier_id !== null && $supplier_id > 0) {
                    $where_clause .= " AND i.item_id IN (SELECT isup.item_id FROM ospos_items_suppliers isup WHERE isup.supplier_id = " . (int)$supplier_id . ")";
                } elseif ($search_term !== null && $search_term !== '') {
                    $where_clause .= $this->_build_search_where($search_term);
                } elseif ($cutoff_date !== null && $cutoff_date !== '') {
                    $items_inventoried = $this->get_item_ids_inventoried_since($cutoff_date);
                    if (!empty($items_inventoried)) {
                        $where_clause .= " AND i.item_id NOT IN (" . implode(',', $items_inventoried) . ")";
                    }
                }
                break;

            case 'full':
            default:
                // All active items
                break;
        }

        // Select items to include in session
        $sql = "SELECT DISTINCT i.item_id, i.quantity
                FROM ospos_items i
                WHERE " . $where_clause . "
                ORDER BY i.name ASC";

        $items_query = $this->db->query($sql);

        $total_items = 0;
        if ($items_query && $items_query->num_rows() > 0) {
            // Batch insert items into session
            $batch = array();
            foreach ($items_query->result() as $item) {
                $batch[] = array(
                    'session_id' => $session_id,
                    'item_id' => $item->item_id,
                    'expected_quantity' => $item->quantity
                );

                // Insert in batches of 500 to avoid memory issues
                if (count($batch) >= 500) {
                    $this->db->insert_batch('inventory_session_items', $batch);
                    $total_items += count($batch);
                    $batch = array();
                }
            }

            // Insert remaining items
            if (!empty($batch)) {
                $this->db->insert_batch('inventory_session_items', $batch);
                $total_items += count($batch);
            }
        }

        // Update session total_items counter
        $this->db->where('id', $session_id);
        $this->db->update('inventory_sessions', array('total_items' => $total_items));

        return $session_id;
    }

    /**
     * Get item IDs that have been inventoried since a given date
     */
    function get_item_ids_inventoried_since($cutoff_date)
    {
        $sql = "SELECT DISTINCT trans_items
                FROM ospos_inventory
                WHERE trans_date >= " . $this->db->escape($cutoff_date) . "
                AND (trans_comment LIKE '%Inventaire comptable%' OR trans_comment LIKE '%Stock Tournant%' OR trans_comment LIKE '%Inventaire%')";

        $query = $this->db->query($sql);
        $ids = array();

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $ids[] = (int)$row->trans_items;
            }
        }

        return $ids;
    }

    /**
     * Count items that would be included in a session (for preview)
     */
    function count_items_for_session_type($session_type, $branch_code, $category_id = null, $cutoff_date = null, $supplier_id = null, $search_term = null)
    {
        $join_clause = "";
        $where_clause = "i.deleted = 0 AND i.branch_code = " . $this->db->escape($branch_code);
        $where_clause .= " AND (i.category NOT LIKE '%DEFECT%' OR i.category IS NULL)";

        switch ($session_type) {
            case 'rolling':
                $where_clause .= " AND i.rolling_inventory_indicator = 0";
                break;
            case 'partial':
                if ($category_id !== null && $category_id > 0) {
                    $where_clause .= " AND i.category_id = " . (int)$category_id;
                } elseif ($supplier_id !== null && $supplier_id > 0) {
                    $join_clause = " INNER JOIN ospos_items_suppliers isup ON isup.item_id = i.item_id AND isup.supplier_id = " . (int)$supplier_id;
                } elseif ($search_term !== null && $search_term !== '') {
                    $where_clause .= $this->_build_search_where($search_term);
                } elseif ($cutoff_date !== null && $cutoff_date !== '') {
                    $items_inventoried = $this->get_item_ids_inventoried_since($cutoff_date);
                    if (!empty($items_inventoried)) {
                        $where_clause .= " AND i.item_id NOT IN (" . implode(',', $items_inventoried) . ")";
                    }
                }
                break;
        }

        $sql = "SELECT COUNT(DISTINCT i.item_id) as cnt FROM ospos_items i" . $join_clause . " WHERE " . $where_clause;
        $query = $this->db->query($sql);

        if ($query && $query->num_rows() > 0) {
            return (int)$query->row()->cnt;
        }

        return 0;
    }

    /**
     * Build WHERE clause for free-text search on item name/number
     * Each word must match either name or item_number (AND logic)
     */
    private function _build_search_where($search_term)
    {
        $words = preg_split('/\s+/', trim($search_term));
        $clauses = array();
        foreach ($words as $word) {
            if ($word === '') continue;
            $escaped = $this->db->escape('%' . $word . '%');
            $clauses[] = "(i.name LIKE " . $escaped . " OR i.item_number LIKE " . $escaped . ")";
        }
        if (empty($clauses)) return '';
        return " AND " . implode(" AND ", $clauses);
    }

    /**
     * Get session items for counting interface with current stock
     */
    function get_session_items_for_counting($session_id, $filter = 'all', $search = '')
    {
        $this->db->from('inventory_session_items si');
        $this->db->join('items i', 'i.item_id = si.item_id');
        $this->db->join('categories c', 'c.category_id = i.category_id', 'left');
        $this->db->select('si.*, i.name as item_name, i.item_number, i.quantity as current_stock, i.category_id, c.category_name as category_name, i.dluo_indicator');
        $this->db->where('si.session_id', $session_id);

        switch ($filter) {
            case 'counted':
                $this->db->where('si.counted_at IS NOT NULL', null, false);
                break;
            case 'uncounted':
                $this->db->where('si.counted_at IS NULL', null, false);
                break;
        }

        if ($search !== '') {
            $escaped_search = $this->db->escape_like_str($search);
            $this->db->where("(i.name LIKE '%" . $escaped_search . "%' OR i.item_number LIKE '%" . $escaped_search . "%')", null, false);
        }

        $this->db->order_by('c.category_name', 'ASC');
        $this->db->order_by('i.name', 'ASC');

        return $this->db->get();
    }

    /**
     * Record a count for an item in a session
     */
    function record_count($session_id, $item_id, $counted_qty, $employee_id, $comment = '')
    {
        // Get the current stock at count time
        $this->db->select('quantity');
        $this->db->where('item_id', $item_id);
        $item_query = $this->db->get('items');

        $stock_at_count_time = 0;
        if ($item_query && $item_query->num_rows() > 0) {
            $stock_at_count_time = $item_query->row()->quantity;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->where('session_id', $session_id);
        $this->db->where('item_id', $item_id);
        $this->db->update('inventory_session_items', array(
            'counted_quantity' => $counted_qty,
            'counted_by' => $employee_id,
            'counted_at' => $now,
            'stock_at_count_time' => $stock_at_count_time,
            'comment' => $comment
        ));

        if ($this->db->affected_rows() >= 0) {
            // Update items_counted counter on session
            $this->db->select('COUNT(*) as cnt');
            $this->db->where('session_id', $session_id);
            $this->db->where('counted_at IS NOT NULL', null, false);
            $count_query = $this->db->get('inventory_session_items');

            $items_counted = 0;
            if ($count_query && $count_query->num_rows() > 0) {
                $items_counted = $count_query->row()->cnt;
            }

            $this->db->where('id', $session_id);
            $this->db->update('inventory_sessions', array('items_counted' => $items_counted));

            return true;
        }

        return false;
    }

    /**
     * Apply session adjustments - CRITICAL algorithm
     *
     * expected_quantity    = stock snapshot at session opening
     * counted_quantity     = quantity physically counted
     * current_stock        = items.quantity at application time (includes sales/receivings in between)
     * movements_since_open = current_stock - expected_quantity
     * new_stock            = counted_quantity + movements_since_open
     * adjustment           = new_stock - current_stock
     */
    function apply_session_adjustments($session_id, $employee_id)
    {
        $session = $this->get_session($session_id);
        if (!$session || $session->status !== 'in_progress' || $session->applied == 1) {
            return array('success' => false, 'message' => 'Session invalide ou deja appliquee');
        }

        $branch_code = $session->branch_code;
        // Fallback : si branch_code vide sur la session, lire depuis la config
        if (empty($branch_code)) {
            $CI =& get_instance();
            $branch_code = $CI->config->item('branch_code');
        }

        // Get only counted items
        $this->db->from('inventory_session_items si');
        $this->db->join('items i', 'i.item_id = si.item_id');
        $this->db->select('si.*, i.quantity as current_stock, i.item_number, i.name as item_name');
        $this->db->where('si.session_id', $session_id);
        $this->db->where('si.counted_at IS NOT NULL', null, false);
        $this->db->where('si.applied', 0);
        $items = $this->db->get();

        if (!$items || $items->num_rows() == 0) {
            return array('success' => false, 'message' => 'Aucun article compte a appliquer');
        }

        $this->db->trans_start();

        $CI =& get_instance();
        $adjustments_made = 0;
        $items_processed = 0;

        foreach ($items->result() as $item) {
            $expected_quantity = (float)$item->expected_quantity;
            $counted_quantity = (float)$item->counted_quantity;
            $current_stock = (float)$item->current_stock;

            // Calculate movements since session opening
            $movements_since_open = $current_stock - $expected_quantity;
            $new_stock = $counted_quantity + $movements_since_open;
            $adjustment = $new_stock - $current_stock;

            // 1. INSERT into ospos_inventory (audit trail)
            // trans_user = celui qui a effectué le comptage (counted_by), pas celui qui applique
            $counted_by_user = !empty($item->counted_by) ? $item->counted_by : $employee_id;
            $inv_data = array(
                'trans_date' => date('Y-m-d H:i:s'),
                'trans_items' => $item->item_id,
                'trans_user' => $counted_by_user,
                'trans_comment' => 'Inventaire comptable - Session #' . $session_id,
                'trans_stock_before' => $current_stock,
                'trans_inventory' => $adjustment,
                'trans_stock_after' => $new_stock,
                'branch_code' => $branch_code
            );
            $this->db->insert('inventory', $inv_data);

            // 2. UPDATE ospos_items.quantity
            $this->db->where('item_id', $item->item_id);
            $this->db->update('items', array(
                'quantity' => $new_stock,
                'rolling_inventory_indicator' => 1
            ));

            // 3. UPDATE ospos_stock_valuation (delegate to Item model, auto-loaded)
            $item_supplier_data = $this->_get_item_supplier_cost($item->item_id);
            $cost_price = $item_supplier_data ? $item_supplier_data->supplier_cost_price : 0;

            if ($new_stock < 0) {
                $CI->Item->value_delete_item_id($item->item_id);
                $val_data = array(
                    'value_item_id' => $item->item_id,
                    'value_cost_price' => $cost_price,
                    'value_qty' => $new_stock,
                    'value_trans_id' => 0,
                    'branch_code' => $branch_code
                );
                $CI->Item->value_write($val_data);
            } else {
                $value_remaining_qty = -1 * $adjustment;
                $CI->Item->value_update($value_remaining_qty, $item->item_id, $cost_price, 0);
            }

            // 4. Mark session item as applied
            $this->db->where('id', $item->id);
            $this->db->update('inventory_session_items', array(
                'applied' => 1,
                'adjustment' => $adjustment
            ));

            $adjustments_made++;
            $items_processed++;
        }

        // Mark session as completed and applied
        $this->db->where('id', $session_id);
        $this->db->update('inventory_sessions', array(
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'applied' => 1,
            'applied_at' => date('Y-m-d H:i:s'),
            'applied_by' => $employee_id
        ));

        $this->db->trans_complete();

        $success = $this->db->trans_status();

        return array(
            'success' => $success,
            'items_processed' => $items_processed,
            'adjustments_made' => $adjustments_made,
            'message' => $success ? 'Ajustements appliques avec succes' : 'Erreur lors de l\'application'
        );
    }

    // Stock valuation helper - delegates to auto-loaded Item model
    private function _get_item_supplier_cost($item_id)
    {
        $sql = "SELECT supplier_cost_price FROM ospos_items_suppliers
                WHERE item_id = " . $this->db->escape($item_id) . "
                AND supplier_preferred = 'Y' LIMIT 1";
        $query = $this->db->query($sql);

        if ($query && $query->num_rows() > 0) {
            return $query->row();
        }

        return false;
    }

    /**
     * Get items not inventoried since a cutoff date (for partial type)
     */
    function get_items_not_inventoried_since($cutoff_date, $branch_code)
    {
        $excluded_ids = $this->get_item_ids_inventoried_since($cutoff_date);
        $exclude_clause = '';
        if (!empty($excluded_ids)) {
            $exclude_clause = " AND i.item_id NOT IN (" . implode(',', $excluded_ids) . ")";
        }

        $sql = "SELECT i.item_id, i.item_number, i.name, i.quantity, i.category_id, c.category_name
                FROM ospos_items i
                LEFT JOIN ospos_categories c ON c.category_id = i.category_id
                WHERE i.deleted = 0
                AND i.branch_code = " . $this->db->escape($branch_code) . "
                AND (i.category NOT LIKE '%DEFECT%' OR i.category IS NULL)
                " . $exclude_clause . "
                ORDER BY i.name ASC";

        return $this->db->query($sql);
    }

    /**
     * Get items for rolling inventory (rolling_inventory_indicator = 0)
     */
    function get_items_for_rolling($branch_code)
    {
        $sql = "SELECT i.item_id, i.item_number, i.name, i.quantity, i.category_id, c.category_name
                FROM ospos_items i
                LEFT JOIN ospos_categories c ON c.category_id = i.category_id
                WHERE i.deleted = 0
                AND i.branch_code = " . $this->db->escape($branch_code) . "
                AND i.rolling_inventory_indicator = 0
                AND (i.category NOT LIKE '%DEFECT%' OR i.category IS NULL)
                ORDER BY i.name ASC";

        return $this->db->query($sql);
    }

    /**
     * Count items that would be selected for a session type (preview)
     */
    function count_items_for_session($session_type, $branch_code, $category_id = null, $cutoff_date = null)
    {
        $where_clause = "deleted = 0 AND branch_code = " . $this->db->escape($branch_code);
        $where_clause .= " AND (category NOT LIKE '%DEFECT%' OR category IS NULL)";

        switch ($session_type) {
            case 'rolling':
                $where_clause .= " AND rolling_inventory_indicator = 0";
                break;
            case 'partial':
                if ($category_id !== null && $category_id > 0) {
                    $where_clause .= " AND category_id = " . (int)$category_id;
                } elseif ($cutoff_date !== null && $cutoff_date !== '') {
                    $excluded_ids = $this->get_item_ids_inventoried_since($cutoff_date);
                    if (!empty($excluded_ids)) {
                        $where_clause .= " AND item_id NOT IN (" . implode(',', $excluded_ids) . ")";
                    }
                }
                break;
        }

        $sql = "SELECT COUNT(*) as cnt FROM ospos_items WHERE " . $where_clause;
        $query = $this->db->query($sql);

        if ($query && $query->num_rows() > 0) {
            return (int)$query->row()->cnt;
        }

        return 0;
    }
}

/* End of file inventory_session.php */
/* Location: ./application/models/inventory_session.php */
