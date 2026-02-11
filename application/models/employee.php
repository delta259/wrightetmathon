<?php
class Employee extends Person
{	
	/*
	Determines if a given person_id is a customer
	*/
	function	exists				($person_id)
	{
		$this						->	db->from('employees');	
		$this						->	db->join('people', 'people.person_id = employees.person_id');
		$this						->	db->where('employees.person_id', $person_id);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		
		return 						($query->num_rows()==1);
	}
	
	/*
	Returns all the employees
	*/
	function	get_all				($limit=10000, $offset=0, $order_by='last_name', $sequence='asc')
	{
		// select records based on whether normal or undelete mode
		switch ($_SESSION['undel'])
		{
			case	1:
					$this						->	db->where('employees.deleted', 1);
			break;
			
			default:
					$this						->	db->where('employees.deleted', 0);
			break;
		}
		
		$this						->	db->from('employees');
		$this						->	db->join('people','employees.person_id=people.person_id');			
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by($order_by, $sequence);
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;		
	}
	
	function	get_all_deleted		($limit=10000, $offset=0, $order_by='last_name', $sequence='asc')
	{
		$this						->	db->from('employees');
		$this						->	db->join('people','employees.person_id=people.person_id');			
		$this						->	db->where('employees.deleted', 1);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by($order_by, $sequence);
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();
		return 						$data;		
	}
	
	function	count_all			()
	{
		$this						->	db->from('employees');
		$this						->	db->where('deleted',0);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	function	count_all_deleted	()
	{
		$this						->	db->from('employees');
		$this						->	db->where('deleted',1);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Gets information about a particular customer
	*/
	function	get_info			($employee_id)
	{
		$this						->	db->from('employees');	
		$this						->	db->join('people', 'people.person_id = employees.person_id');
		$this						->	db->where('employees.person_id',$employee_id);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();

		if($query->num_rows()	==	1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $customer_id is NOT a customer
			$person_obj				=	parent::get_info(-1);
			
			//Get all the fields from customer table
			$fields 				= 	$this->db->list_fields('employees');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field	=	'';
			}
			
			return 					$person_obj;
		}
	}

	function get_load_data_set_vendeur($pseudo)
	{
		$this						->	db->from('employees');	
		$this						->	db->join('people', 'people.person_id = employees.person_id');
		$this						->	db->where('employees.username',$pseudo);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		return $query->row();
	}
	
	/*
	Gets information about a particular customer
	*/
	function	get_info_by_name	($employee_name)
	{
		$this						->	db->from('employees');	
		$this						->	db->join('people', 'people.person_id = employees.person_id');
		$this						->	db->where('employees.person_id',$employee_id);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		
		if($query->num_rows()	==	1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $customer_id is NOT a customer
			$person_obj				=	parent::get_info(-1);
			
			//Get all the fields from customer table
			$fields 				= 	$this->db->list_fields('employees');
			
			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field)
			{
				$person_obj->$field	=	'';
			}
			
			return 					$person_obj;
		}
	}
	
	/*
	Gets information about multiple employees
	*/
	function	get_multiple_info	($customer_ids)
	{
		$this						->	db->from('employees');
		$this						->	db->join('people', 'people.person_id = employees.person_id');		
		$this						->	db->where_in('employees.person_id',$customer_ids);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("last_name", "asc");
		return 						$this->db->get();		
	}
	
	/*
	updates sales counts for existing employee
	*/
	function	save_counts			()
	{
		$this															->	db->where('person_id', $_SESSION['transaction_info']->person_id);
		$this															->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this															->	db->update('employees', $_SESSION['transaction_info']);
	}

	//save_counts for vs
	function save_counts_vs_sale($update_employee_counts)
	{
		$this															->	db->where('person_id', $update_employee_counts['person_id']);
		$this															->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this															->	db->update('employees', $update_employee_counts);
	}
		
	/*
	Inserts or updates a customer
	*/
	function	save				()
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->														trans_start();
		
		// save the data to the people file
		$this->															Person->save();
		$_SESSION['employee_data_set']->person_id						=	$_SESSION['transaction_info']->person_id;
			
		// test for new record
		switch ($_SESSION['new'])
		{
			case	1:
					// add record
					$this->db->											insert('employees', $_SESSION['employee_data_set']);
			break;
					
			default:
					// update record
					$this->db->											where('person_id', $_SESSION['employee_data_set']->person_id);
					$this->db->											where('employees.branch_code', $this->config->item('branch_code'));
					$this->db->											update('employees', $_SESSION['employee_data_set']);
			break;	
		}
		
		$this->db->														trans_complete();		
		return;
	}
 	
 	/*
	Get search suggestions to find employees
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();

		// set search on deleted or not items
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('employees.deleted', 1);
			break;
			
			default:
					$this			->	db->where('employees.deleted', 0);
			break;
		}
		
		// search on names
		$this						->	db->from('employees');
		$this						->	db->join('people','employees.person_id=people.person_id');	
		$this						->	db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%')");
		$this						->	db->order_by("last_name", "asc");
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));	
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));	
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$this->Common_routines->format_full_name($row->last_name, $row->first_name);
		}
		
		// search on emails
		$this						->	db->from('employees');
		$this						->	db->join('people','employees.person_id=people.person_id');	
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));		
		$this						->	db->like("email",$search);
		$this						->	db->order_by("email", "asc");		
		$by_email					=	$this->db->get();
		foreach($by_email->result() as $row)
		{
			$suggestions[]=$row->email;		
		}
		
		// search on phone number
		$this						->	db->from('employees');
		$this						->	db->join('people','employees.person_id=people.person_id');	
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));	
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));	
		$this						->	db->like("phone_number",$search);
		$this						->	db->order_by("phone_number", "asc");		
		$by_phone					=	$this->db->get();
		foreach($by_phone->result() as $row)
		{
			$suggestions[]=$row->phone_number;		
		}
		
		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Get search suggestions to find employees
	*/
	function	get_employee_search_suggestions($search,$limit=25)
	{
		// initialise
		$suggestions = array();
		
		// set search on deleted or not items
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('employees.deleted', 1);
			break;
			
			default:
					$this			->	db->where('employees.deleted', 0);
			break;
		}
		
		
		// search on names
		$this						->	db->from('employees');
		$this						->	db->join('people','employees.person_id=people.person_id');	
		$this						->	db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										CONCAT(`first_name`,' ',`last_name`) LIKE '%".$this->db->escape_like_str($search)."%')");
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("last_name", "asc");
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$this->Common_routines->format_full_name($row->last_name, $row->first_name);		
		}
		
		// search on account number
		$this						->	db->from('employees');
		$this						->	db->join('people','employees.person_id=people.person_id');	
		$this						->	db->where('deleted',0);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));		
		$this						->	db->like("account_number",$search);
		$this						->	db->order_by("account_number", "asc");		
		$by_account_number			=	$this->db->get();
		foreach($by_account_number->result() as $row)
		{
			$suggestions[]=$row->person_id.'|'.$row->account_number;
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0,$limit);
		}
		
		return						$suggestions;

	}
	/*
	Perform a search on employees
	*/
	function	search				($search)
	{
		// set search on deleted or not items
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('employees.deleted', 1);
			break;
			
			default:
					$this			->	db->where('employees.deleted', 0);
			break;
		}
		
		// search by everything
		$this						->	db->from('employees');
		$this						->	db->join('people','employees.person_id=people.person_id');		
		$this						->	db->where("(first_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										last_name LIKE '%".$this->db->escape_like_str($search)."%' or 
										email LIKE '%".$this->db->escape_like_str($search)."%' or 
										phone_number LIKE '%".$this->db->escape_like_str($search)."%' or
										CONCAT(`last_name`,', ',`first_name`) LIKE '%".$this->db->escape_like_str($search)."%')");
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('people.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("last_name", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Attempts to login employee and set session. Returns boolean based on outcome.
	*/
	function	login				($username, $password)
	{	
		$query 						=	$this->db->get_where('employees', 	array	(
																					'username'		=>	$username,
																					'password'		=>	md5($password),
																					'deleted'		=>	0,
																					'branch_code'	=>	$this->config->item('branch_code')
																					),
																			1);
		// test if record found
		if ($query->num_rows() == 1)
		{
			$row					=	$query->row();

			// set global variables
			$_SESSION['G']->login_employee_id			=	$row->person_id;
			$_SESSION['G']->login_employee_username		=	$username;
			
			// leave this for now, so as not to break application - but remove later
			$this					->	session->set_userdata('person_id', $row->person_id);
			$this					->	session->set_userdata('username', $username);
			return 					true;
		}
		return 						false;
	}
	
	/*
	Logs out a user by destroying all session data and redirect to login
	*/
	function	logout				()
	{			
		// write log
		$this						->	load->model('Common_routines');
		$action						=	'logout';
		$username					=	$this->session->userdata('username');
		$this						->	Common_routines->write_log($action, $username);
		
		// destroy the session
		$this						->	session->sess_destroy();
		session_unset();
		
		// destroy temp pid file
		array_map('unlink', glob("/home/wrightetmathon/.app_running.txt"));
		
		// redirect to login screen
		$_SESSION['exit']												=	'YES';
		redirect														('login/exit');
	}
	
	/*
	Determine if a employee is logged in
	*/
	function	is_logged_in		()
	{
		return 						$this->session->userdata('person_id') != false;
	}
	
	/*
	Gets information about the currently logged in employee.
	*/
	function	get_logged_in_employee_info()
	{
		return 						$this->get_info($_SESSION['G']->login_employee_id);
	}
	
	/*
	Determins whether the employee specified employee has access the specific module.
	*/
	function	has_permission		($module_id, $person_id)
	{		
		//if no module_id is null, allow access
		if($module_id == NULL)
		{
			return 														true;
		}
		
		$this															->	db->from('permissions');
		$this															->	db->where("module_id", $module_id);
		$this															->	db->where("person_id", $person_id);
		$query															=	$this->db->get();		
		
		if ($query->num_rows() == 1)
		{
			return														true;
		}
		else
		{
			return 														false;
		}
	}
	
	/*
	Determins whether the employee specified employee has access the specific module.
	*/
	function	delete_permissions		()
	{		
		$success	=	$this->db->delete('permissions', array('person_id' => $_SESSION['transaction_info']->person_id));
		return			$success;
	}
	
	function	insert_permissions		()
	{		
		foreach($_SESSION['permission_data'] as $allowed_module)
			{
				$this->db->insert('permissions',
															array	(
																	'module_id'		=>	$allowed_module,
																	'person_id'		=>	$_SESSION['transaction_info']->person_id,
																	));
			}
		return;
	}
	
	function 	check_duplicate_username			()
	{			
		// set up the where select depending on adding or updating
		switch ($_SESSION['new'])
		{
			case	1:
					unset($_SESSION['where_select']);
			break;
					
			default:
					$_SESSION['where_select']	=	'person_id !='.$_SESSION['transaction_info']->person_id;
					$this						->	db->where($_SESSION['where_select']);
			break;	
		}		
		
		// get the data		
		$this						->	db->from('employees');
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('username', $_SESSION['transaction_info']->username);
		$results					=	array();
		$results					=	$this->db->get()->result_array();	
			
		// if there is a record then email is duplicate
		if (count($results) 		> 0)
		{
			$success				=	FALSE;
		}
		else
		{
			$success				=	TRUE;
		}
		
		// return the search results
		return 						$success;	
	}
}
?>
