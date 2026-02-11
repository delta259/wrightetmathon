<?php
class Employees extends CI_controller
{	
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"12";
		
		// set data array
		$data = array();
		
		// manage session
		$_SESSION['controller_name']=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);
		
		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']	=	$this->lang->line('common_undelete');
			break;
				
			default:
					$data['title']	=	'';
			break;
		}
		
		// setup output data (no pagination — employees list is small)
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->get_form_width();
		$data['manage_table_data']	=	$this->Employee->get_all(10000, 0);
		
		// show data
		$this						->	load->view('employees/manage', $data);
	}
	
	/*
	Returns employee table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_employee_manage_table($this->Employee->search($search), $this, $create_headers);
		echo $data_rows;
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Employee->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
	
	/*
	Loads the customer edit form
	*/
	function view($employee_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']		=	new stdClass();

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
			break;
			
			default:
					$_SESSION['origin']		=	$origin;
			break;
		}

		// manage session depending on permissions flag
		if (($_SESSION['show_permissions'] ?? 0) == 1)
		{
			$_SESSION['show_dialog']		=	2;
		}
		else
		{
			$_SESSION['show_dialog']		=	1;
		}
		
		// set data
		switch ($employee_id)
		{
			// create new
			case	-1:
					$_SESSION['$title']							=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']							=	1;
					$_SESSION['transaction_info']->first_name	=	'';
					$_SESSION['transaction_info']->last_name	=	'';
					$_SESSION['transaction_info']->email		=	'';
					$_SESSION['transaction_info']->phone_number	=	'';
					$_SESSION['transaction_info']->address_1	=	'';
					$_SESSION['transaction_info']->address_2	=	'';
					$_SESSION['transaction_info']->city			=	'';
					$_SESSION['transaction_info']->state		=	'';
					$_SESSION['transaction_info']->zip			=	'';
					$_SESSION['transaction_info']->country_id	=	'';
					$_SESSION['transaction_info']->comments		=	'';
					$_SESSION['transaction_info']->sex			=	'';
					$_SESSION['transaction_info']->dob_day		=	'';
					$_SESSION['transaction_info']->dob_month	=	'';
					$_SESSION['transaction_info']->dob_year		=	'';
					$_SESSION['transaction_info']->username		=	'';
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Employee->get_info($employee_id);
					$_SESSION['full_name_out']					=	$this->Common_routines->format_full_name($_SESSION['transaction_info']->last_name, $_SESSION['transaction_info']->first_name);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['full_name_out'];
						break;
						
						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'  '.$_SESSION['full_name_out'];
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("employees");
	}
	
	/*
	Inserts/updates an employee
	*/
	function	save	()
	{			
		// load person data
		$_SESSION['transaction_info']->first_name						=	$this->input->post('first_name');
		$_SESSION['transaction_info']->last_name						=	$this->input->post('last_name');
		$_SESSION['transaction_info']->email							=	$this->input->post('email');
		$_SESSION['transaction_info']->phone_number						=	$this->input->post('phone_number');
		$_SESSION['transaction_info']->address_1						=	$this->input->post('address_1');
		$_SESSION['transaction_info']->address_2						=	$this->input->post('address_2');			
		$_SESSION['transaction_info']->city								=	$this->input->post('city');
		$_SESSION['transaction_info']->state							=	$this->input->post('state');
		$_SESSION['transaction_info']->zip								=	$this->input->post('zip');
		$_SESSION['transaction_info']->country_id						=	$this->input->post('country_id');
		$_SESSION['transaction_info']->comments							=	$this->input->post('comments');
		$_SESSION['transaction_info']->sex								=	$this->input->post('sex');
		
		// explode and load dob
/*		$pieces 														=	explode("-", $this->input->post('dob_0'));
		$_SESSION['transaction_info']->dob_day							=	$pieces[2];
		$_SESSION['transaction_info']->dob_month						=	$pieces[1];
		$_SESSION['transaction_info']->dob_year							=	$pieces[0];
//*/
		$pieces 														=	explode("/", $this->input->post('dob'));
		$_SESSION['transaction_info']->dob_day							=	$pieces[0];
		$_SESSION['transaction_info']->dob_month						=	$pieces[1];
		$_SESSION['transaction_info']->dob_year							=	$pieces[2];
		
		// load employee data
		$_SESSION['transaction_info']->username							=	$this->input->post('username');
		if (!empty($this->input->post('password')))
		{
			$_SESSION['transaction_info']->password						=	$this->input->post('password');
			$_SESSION['transaction_info']->repeat_password				=	$this->input->post('repeat_password');
		}
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');
		
		
		// manage session
		switch ($_SESSION['new'] ?? 0)
		{
			// add employee
			case	1:
					// load id
					$_SESSION['transaction_info']->person_id			=	NULL;
			break;
			
			// update client
			default:
					// load id
					$_SESSION['transaction_info']->person_id			=	$_SESSION['transaction_info']->person_id;
			break;
		}
									
		// do data verifications
		$this->															verify();
		
		// create employee data set for employees file
		$_SESSION['employee_data_set']									=	new stdClass();
		$_SESSION['employee_data_set']->person_id						=	$_SESSION['transaction_info']->person_id;
		if (!empty($this->input->post('password')))
		{
			$_SESSION['employee_data_set']->username					=	$_SESSION['transaction_info']->username;
			$_SESSION['employee_data_set']->password					=	md5($_SESSION['transaction_info']->password);
		}
		$_SESSION['employee_data_set']->branch_code						=	$_SESSION['transaction_info']->branch_code;
		
		// if here then all checks succeeded so do the update
		$this->															Employee->save();

		// set appropriate message and return to list
		$_SESSION['error_code'] = (($_SESSION['new'] ?? 0) == 1) ? '00290' : '00300';
		unset($_SESSION['new']);
		unset($_SESSION['show_dialog']);
		unset($_SESSION['first_time']);
		redirect("employees");
	}

	/*
	Inserts/updates an employee permissions
	*/
	function	save_permissions	()
	{
		// load permissions data from data entry
		$_SESSION['permission_data']			 						=	$this->input->post("permissions")!=false ? $this->input->post("permissions"):array();

		//First lets clear out any permissions the employee currently has.
		$success														=	$this->Employee->delete_permissions();

		//Second, we'll insert all the new permissions
		if($success)
		{
			$this->Employee->insert_permissions();
		}

		// reset $_SESSION
		unset($_SESSION['show_permissions']);
		unset($_SESSION['all_modules']);
		unset($_SESSION['permission_data']);

		// set appropriate message and return to list
		$_SESSION['error_code'] = (($_SESSION['new'] ?? 0) == 1) ? '00290' : '00300';
		unset($_SESSION['new']);
		unset($_SESSION['show_dialog']);
		redirect("employees");
	}
	
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{			
		return 800;
	}
	
	function verify()
	{			
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->last_name)
			OR 	empty($_SESSION['transaction_info']->first_name)
			OR 	empty($_SESSION['transaction_info']->zip)
			OR 	empty($_SESSION['transaction_info']->username)
			)
		{
			// set message
			$_SESSION['error_code']			=	'00030';
			redirect($_SESSION['controller_name']);
		}
		
		// verify email, if entered
		if (!empty($_SESSION['transaction_info']->email))
		{
			// check email format			
			if (!$this							->	Common_routines->check_email_format())
			{
				// set message
				$_SESSION['error_code']			=	'00020';
				redirect($_SESSION['controller_name']);
			}
		}
		
		// verify dob correct date
		if (!checkdate($_SESSION['transaction_info']->dob_month, $_SESSION['transaction_info']->dob_day, $_SESSION['transaction_info']->dob_year))
		{
			// set message
			$_SESSION['error_code']										=	'05100';
			redirect($_SESSION['controller_name']);
		}
		
		// verify dob not in future
/*		$dobstr															=	str_replace('/', '-', $this->input->post('dob_0'));
//*/
		$dobstr															=	str_replace('/', '-', $this->input->post('dob'));
		$dob															=	strtotime($dobstr);
		$now															=	time();
		
		if ($dob > $now)
		{
			// set message
			$_SESSION['error_code']										=	'05110';
			redirect($_SESSION['controller_name']);
		}
		
		// verify dob > 18 years - under age client
		$underage														=	strtotime('-18 years');
		if ($dob > $underage)
		{
			// set message
			$_SESSION['error_code']										=	'05120';
			redirect($_SESSION['controller_name']);
		}
		
		// check username length	
		if (strlen($_SESSION['transaction_info']->username) < 5)
		{
			// set message
			$_SESSION['error_code']				=	'00120';
			redirect($_SESSION['controller_name']);
		}
		
		// check username duplicate		
		if (!$this								->	Employee->check_duplicate_username())
		{
			// set message
			$_SESSION['error_code']				=	'00130';
			redirect($_SESSION['controller_name']);
		}
		
		// check password
		if (!empty($this->input->post('password')))
		{	
			// length gt 5?
			if (strlen($_SESSION['transaction_info']->password) < 8)
			{
				// set message
				$_SESSION['error_code']				=	'00140';
				redirect($_SESSION['controller_name']);
			}
			
			// passwords not same
			if ($_SESSION['transaction_info']->password != $_SESSION['transaction_info']->repeat_password)
			{
				// set message
				$_SESSION['error_code']				=	'00150';
				redirect($_SESSION['controller_name']);
			}
		}
		
		// if new and password is blank = error
		if (($_SESSION['new'] ?? 0) == 1 AND empty($this->input->post('password')))
		{	
			// set message
			$_SESSION['error_code']				=	'00160';
			redirect($_SESSION['controller_name']);
		}
	}
}
?>
