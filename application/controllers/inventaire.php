<?php
class Inventaire extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Inventory_session');
		$this->load->model('Category');
	}

	/**
	 * List all inventory sessions
	 */
	function index()
	{
		$_SESSION['module_id'] = "15";
		$_SESSION['origin'] = "IV";

		$branch_code = $this->config->item('branch_code');

		// Check for active session
		$active_session = $this->Inventory_session->get_active_session($branch_code);

		// Get all sessions
		$sessions = $this->Inventory_session->get_all_sessions($branch_code, 50, 0);

		$data = array(
			'controller_name' => 'inventaire',
			'active_session' => $active_session,
			'sessions' => $sessions,
			'success_message' => isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '',
			'error_message' => isset($_SESSION['error_message']) ? $_SESSION['error_message'] : ''
		);

		unset($_SESSION['success_message']);
		unset($_SESSION['error_message']);

		$this->load->view('inventaire/manage', $data);
	}

	/**
	 * Create session form
	 */
	function create()
	{
		$_SESSION['module_id'] = "15";
		$_SESSION['origin'] = "IV";
		if (!isset($_SESSION['undel'])) $_SESSION['undel'] = 0;

		$branch_code = $this->config->item('branch_code');

		// Block if active session exists
		$active_session = $this->Inventory_session->get_active_session($branch_code);
		if ($active_session) {
			$_SESSION['error_message'] = $this->lang->line('inventaire_session_active_warning');
			redirect('inventaire');
			return;
		}

		// Get categories for dropdown (query object)
		$this->db->from('categories');
		$this->db->where('deleted', 0);
		$this->db->where('branch_code', $branch_code);
		$this->db->order_by('category_name', 'asc');
		$categories = $this->db->get();

		// Get suppliers for dropdown
		$suppliers = $this->Supplier->get_all();

		$data = array(
			'controller_name' => 'inventaire',
			'categories' => $categories,
			'suppliers' => $suppliers
		);

		$this->load->view('inventaire/create', $data);
	}

	/**
	 * Save new session (POST)
	 */
	function save_session()
	{
		$branch_code = $this->config->item('branch_code');

		// Block if active session exists
		$active_session = $this->Inventory_session->get_active_session($branch_code);
		if ($active_session) {
			$_SESSION['error_message'] = $this->lang->line('inventaire_session_active_warning');
			redirect('inventaire');
			return;
		}

		$session_type = $this->input->post('session_type');
		$category_id = $this->input->post('category_id');
		$cutoff_date = $this->input->post('cutoff_date');
		$supplier_id = $this->input->post('supplier_id');
		$search_term = trim($this->input->post('search_term'));
		$notes = $this->input->post('notes');

		// Validate type
		$valid_types = array('full', 'rolling', 'partial');
		if (!in_array($session_type, $valid_types)) {
			$_SESSION['error_message'] = 'Type de session invalide';
			redirect('inventaire/create');
			return;
		}

		$params = array(
			'branch_code' => $branch_code,
			'notes' => $notes
		);

		if ($session_type === 'partial') {
			if (!empty($category_id) && $category_id > 0) {
				$params['category_id'] = (int)$category_id;
			} elseif (!empty($supplier_id) && $supplier_id > 0) {
				$params['supplier_id'] = (int)$supplier_id;
			} elseif (!empty($search_term)) {
				$params['search_term'] = $search_term;
			} elseif (!empty($cutoff_date)) {
				$params['cutoff_date'] = $cutoff_date;
			} else {
				$_SESSION['error_message'] = 'Pour un inventaire partiel, veuillez spécifier un critère de filtre.';
				redirect('inventaire/create');
				return;
			}
		}

		$employee_id = isset($_SESSION['G']->login_employee_id) ? $_SESSION['G']->login_employee_id : $this->session->userdata('person_id');
		if (empty($employee_id)) {
			redirect('login');
			return;
		}

		$session_id = $this->Inventory_session->create_and_populate_session($employee_id, $session_type, $params);

		if ($session_id) {
			$_SESSION['success_message'] = $this->lang->line('inventaire_session_created');
			redirect('inventaire/count/' . $session_id);
		} else {
			$_SESSION['error_message'] = 'Erreur lors de la création de la session';
			redirect('inventaire/create');
		}
	}

	/**
	 * Counting interface
	 */
	function count($id = 0)
	{
		$_SESSION['module_id'] = "15";
		$_SESSION['origin'] = "IV";

		$id = (int)$id;
		if ($id <= 0) {
			redirect('inventaire');
			return;
		}

		$session = $this->Inventory_session->get_session($id);
		if (!$session || $session->status !== 'in_progress') {
			$_SESSION['error_message'] = 'Session invalide ou non en cours';
			redirect('inventaire');
			return;
		}

		$filter = $this->input->get('filter');
		if (!$filter || !in_array($filter, array('all', 'counted', 'uncounted'))) {
			$filter = 'all';
		}

		$search = $this->input->get('search');
		if (!$search) $search = '';

		$items = $this->Inventory_session->get_session_items_for_counting($id, $filter, $search);

		$data = array(
			'controller_name' => 'inventaire',
			'session' => $session,
			'items' => $items,
			'filter' => $filter,
			'search' => $search
		);

		$this->load->view('inventaire/count', $data);
	}

	/**
	 * AJAX: Save a single item count
	 */
	function save_count()
	{
		$session_id = (int)$this->input->post('session_id');
		$item_id = (int)$this->input->post('item_id');
		$counted_qty = floatval($this->input->post('counted_qty'));
		$comment = $this->input->post('comment');
		if ($comment === null) $comment = '';

		$employee_id = isset($_SESSION['G']->login_employee_id) ? $_SESSION['G']->login_employee_id : $this->session->userdata('person_id');
		if (empty($employee_id)) {
			header('Content-Type: application/json');
			echo json_encode(array('success' => false, 'message' => 'Session expirée'));
			return;
		}

		// Validate session is in_progress
		$session = $this->Inventory_session->get_session($session_id);
		if (!$session || $session->status !== 'in_progress') {
			header('Content-Type: application/json');
			echo json_encode(array('success' => false, 'message' => 'Session invalide'));
			return;
		}

		$result = $this->Inventory_session->record_count($session_id, $item_id, $counted_qty, $employee_id, $comment);

		// Get updated session info for progress
		$updated_session = $this->Inventory_session->get_session($session_id);

		// Get employee name for display
		$employee_name = '';
		$this->db->select('first_name');
		$this->db->where('person_id', $employee_id);
		$person_query = $this->db->get('people');
		if ($person_query && $person_query->num_rows() > 0) {
			$employee_name = $person_query->row()->first_name;
		}

		header('Content-Type: application/json');
		echo json_encode(array(
			'success' => $result,
			'message' => $result ? 'Comptage enregistré' : 'Erreur',
			'items_counted' => $updated_session ? $updated_session->items_counted : 0,
			'total_items' => $updated_session ? $updated_session->total_items : 0,
			'counted_at' => date('d/m/Y H:i'),
			'counted_by_name' => $employee_name
		));
	}

	/**
	 * Apply stock adjustments
	 */
	function apply($id = 0)
	{
		$id = (int)$id;
		if ($id <= 0) {
			redirect('inventaire');
			return;
		}

		$employee_id = isset($_SESSION['G']->login_employee_id) ? $_SESSION['G']->login_employee_id : $this->session->userdata('person_id');
		if (empty($employee_id)) {
			redirect('login');
			return;
		}

		$result = $this->Inventory_session->apply_session_adjustments($id, $employee_id);

		if ($result['success']) {
			$_SESSION['success_message'] = $this->lang->line('inventaire_adjustments_applied')
				. ' (' . $result['adjustments_made'] . ' ajustements sur ' . $result['items_processed'] . ' articles)';
		} else {
			$_SESSION['error_message'] = $result['message'];
		}

		redirect('inventaire');
	}

	/**
	 * Cancel session
	 */
	function cancel($id = 0)
	{
		$id = (int)$id;
		if ($id <= 0) {
			redirect('inventaire');
			return;
		}

		$session = $this->Inventory_session->get_session($id);
		if (!$session || $session->status !== 'in_progress') {
			$_SESSION['error_message'] = 'Session invalide';
			redirect('inventaire');
			return;
		}

		$this->Inventory_session->update_status($id, 'cancelled');
		$_SESSION['success_message'] = $this->lang->line('inventaire_session_cancelled');

		redirect('inventaire');
	}

	/**
	 * View session detail (read-only)
	 */
	function view($id = 0)
	{
		$_SESSION['module_id'] = "15";
		$_SESSION['origin'] = "IV";

		$id = (int)$id;
		if ($id <= 0) {
			redirect('inventaire');
			return;
		}

		$session = $this->Inventory_session->get_session($id);
		if (!$session) {
			$_SESSION['error_message'] = 'Session introuvable';
			redirect('inventaire');
			return;
		}

		$items = $this->Inventory_session->get_session_items_for_counting($id);

		$data = array(
			'controller_name' => 'inventaire',
			'session' => $session,
			'items' => $items
		);

		$this->load->view('inventaire/view', $data);
	}

	/**
	 * AJAX: Preview item count for session type
	 */
	function preview_count()
	{
		$branch_code = $this->config->item('branch_code');
		$session_type = $this->input->post('session_type');
		$category_id = $this->input->post('category_id');
		$cutoff_date = $this->input->post('cutoff_date');
		$supplier_id = $this->input->post('supplier_id');
		$search_term = trim($this->input->post('search_term'));

		if (!$session_type) $session_type = 'full';

		$count = $this->Inventory_session->count_items_for_session_type(
			$session_type,
			$branch_code,
			($category_id && $category_id > 0) ? (int)$category_id : null,
			(!empty($cutoff_date)) ? $cutoff_date : null,
			($supplier_id && $supplier_id > 0) ? (int)$supplier_id : null,
			(!empty($search_term)) ? $search_term : null
		);

		header('Content-Type: application/json');
		echo json_encode(array('count' => $count));
	}
}
