<?php
class Suppliers extends CI_controller
{	
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"24";
		
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
		
		// set up the pagination
		$config						=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 		= 	site_url('/suppliers/index');
		$config['total_rows'] 		= 	$this->Supplier->count_all();
		$this						->	pagination->initialize($config);
		
		// setup output data
		$data['links']				=	$this->pagination->create_links();	
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->get_form_width();
		$create_headers				=	1;
		$data['manage_table']		=	get_supplier_manage_table($this->Supplier->get_all($config['per_page'], $this->uri->segment($config['uri_segment'])), $this, $create_headers);
		
		// show data
		$this						->	load->view('suppliers/manage', $data);
	}
	
	/*
	Returns supplier table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_supplier_manage_table($this->Supplier->search($search), $this, $create_headers);
		echo $data_rows;
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Supplier->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
	
	/*
	Loads the customer edit form
	*/
	function view($supplier_id=-1, $origin='0')
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

		// manage session
		$_SESSION['show_dialog']								=	1;
		$_SESSION['supplier_view']								=	1;

		// set data
		switch ($supplier_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']							=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']							=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Supplier->get_info($supplier_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->company_name;
						break;
						
						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->company_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("suppliers");
	}
	
	/*
	Inserts/updates a customer
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
		
		// supplier information
		$_SESSION['transaction_info']->account_number					=	$this->input->post('account_number');
		$_SESSION['transaction_info']->company_name						=	$this->input->post('company_name');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');
		
		// manage session
		switch ($_SESSION['new'] ?? 0)
		{
			// add client
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
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Supplier->save();
			
		// load pick list
		$this															->	Supplier->load_pick_list();
		
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'00330';
					$this->												view($_SESSION['transaction_info']->person_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'00340';
					$this->												view($_SESSION['transaction_info']->person_id, $_SESSION['origin']);
			break;	
		}
	}
	
	// set the flash data
	function setflash($success_or_failure, $message, $id)
	{
		$this						->	session->set_flashdata('success_or_failure', $success_or_failure);
		$this						->	session->set_flashdata('message', $message);
		
		// get origin thru flash data
		$origin						=	$this->session->flashdata('origin');
		
		// redirect to origin
		if ($origin == 1)
		{
			$this					->	sale_lib->set_customer($id);
			redirect('sales');
		}
		else
		{
			redirect('customers');
		}
		
		return;
	}
	
	/*
	This deletes supplier from the suppliers table
	*/
	function delete()
	{
		if($this->Supplier->delete())
		{
			// set success message
			$_SESSION['error_code']			=	'00450';
			$_SESSION['del']				=	1;
		}
		else
		{
			$_SESSION['error_code']			=	'00350';
		}
		
		redirect($_SESSION['controller_name']);
	}
	
	function list_deleted()
	{
		// set flag to select deleted categories
		$_SESSION['undel']					=	1;
		redirect($_SESSION['controller_name']);
	}
	
	function undelete()
	{
		if($this->Supplier->undelete())
		{
			// set success message
			$_SESSION['error_code']			=	'00490';
			unset($_SESSION['undel']);
		}
		else
		{
			$_SESSION['error_code']			=	'00350';
		}
		
		$_SESSION['$title']					=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->company_name;
		$this->									view($_SESSION['transaction_info']->person_id);
	}
	
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{			
		return 800;
	}
	
	function comment($customer_id)
	{
		$person_info 				= 	$this->Customer->get_info($customer_id);
		$this						->	load->view('sales/customer_comment_popbox', $person_info);
	}
	
	function merge()
	{
		$this						->	load->view('customers/merge');
	}
	
	function merge_do()
	{
		// get data
		$merge_from_client			=	$this->input->post('merge_from_client');
		$merge_to_client			=	$this->input->post('merge_to_client');

		// check input not blank
		if (empty($merge_from_client) OR empty($merge_to_client))
		{
			// set success indicator and message
			$this					->	session->set_flashdata('success_or_failure', 'F');
			$this					->	session->set_flashdata('message', $this->lang->line('customers_merge_customer_codes_blank'));
			$this					->	session->set_flashdata('merge_from_client', $merge_from_client);
			$this					->	session->set_flashdata('merge_to_client', $merge_to_client);
			redirect('customers/merge');
		}
		
		// check merge_from_client valid
		$customer_exists			=	0;
		$customer_exists			=	$this->Customer->exists($merge_from_client);
		if ($customer_exists == 0)
		{
			// set success indicator and message
			$this					->	session->set_flashdata('success_or_failure', 'F');
			$this					->	session->set_flashdata('message', $this->lang->line('customers_invalid_client').' => '.$merge_from_client);
			$this					->	session->set_flashdata('merge_from_client', $merge_from_client);
			$this					->	session->set_flashdata('merge_to_client', $merge_to_client);
			redirect('customers/merge');
		}
		
		// check merge_from_client valid
		$customer_exists			=	0;
		$customer_exists			=	$this->Customer->exists($merge_to_client);
		if ($customer_exists == 0)
		{
			// set success indicator and message
			$this					->	session->set_flashdata('success_or_failure', 'F');
			$this					->	session->set_flashdata('message', $this->lang->line('customers_invalid_client').' => '.$merge_to_client);
			$this					->	session->set_flashdata('merge_from_client', $merge_from_client);
			$this					->	session->set_flashdata('merge_to_client', $merge_to_client);
			redirect('customers/merge');
		}
		
		// update DB
		$update_data				=	array	(
												'customer_id'	=>	$merge_to_client
												);
												
		// 1) sales file												
		if (!$this->Sale->merge_customer($merge_from_client, $update_data))
		{
			// set success indicator and message
			$this					->	session->set_flashdata('success_or_failure', 'F');
			$this					->	session->set_flashdata('message', $this->lang->line('customers_error_adding_updating').' => '.$merge_from_client.' => '.$merge_to_client);
			$this					->	session->set_flashdata('merge_from_client', $merge_from_client);
			$this					->	session->set_flashdata('merge_to_client', $merge_to_client);
			redirect('customers/merge');
		}
		
		// 2) giftcards file
		if (!$this->Giftcard->merge_customer($merge_from_client, $update_data))
		{
			// set success indicator and message
			$this					->	session->set_flashdata('success_or_failure', 'F');
			$this					->	session->set_flashdata('message', $this->lang->line('customers_error_adding_updating').' => '.$merge_from_client.' => '.$merge_to_client);
			$this					->	session->set_flashdata('merge_from_client', $merge_from_client);
			$this					->	session->set_flashdata('merge_to_client', $merge_to_client);
			redirect('customers/merge');
		}
		
		
		// 3) sales_suspended
		if (!$this->Giftcard->merge_customer($merge_from_client, $update_data))
		{
			// set success indicator and message
			$this					->	session->set_flashdata('success_or_failure', 'F');
			$this					->	session->set_flashdata('message', $this->lang->line('customers_error_adding_updating').' => '.$merge_from_client.' => '.$merge_to_client);
			$this					->	session->set_flashdata('merge_from_client', $merge_from_client);
			$this					->	session->set_flashdata('merge_to_client', $merge_to_client);
			redirect('customers/merge');
		}
		
		// 3) reclculate sales value and number of sales for to client
		// zero the to client fields being updated
		$new_total						=	0;
		$new_total_number_of			=	0;
		$customer_data					=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of
													);
		$this							->	db->where('person_id', $merge_to_client);
		$this							->	db->where('branch_code', $this->config->item('branch_code'));
		$this							->	db->update('customers', $customer_data);
		
		// get the sales data
		$data 							=	array();
		$this							->	db->select();
		$this							->	db->from('sales');
		$this							->	db->where('sales.customer_id', $merge_to_client);
		$this							->	db->where('sales.branch_code', $this->config->item('branch_code'));
		$data 							= 	$this->db->get()->result_array();

		// read the data and update customer file
		$row							=	array();
		foreach ($data as $row)
		{
			$this						->	db->from('customers');
			$this						->	db->where('person_id', $row['customer_id']);
			$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
			$trans_data 				= 	$this->db->get()->row_array();
			
			$new_total					=	$trans_data['sales_ht'] + $row['subtotal_after_discount'];
			$new_total_number_of		=	$trans_data['sales_number_of'] + 1;
			
			$customer_data				=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of
													);
													
			$this						->	db->where('person_id', $row['customer_id']);
			$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
			$this						->	db->update('customers', $customer_data);
		}
		
		// zero the from client fields being updated
		$new_total						=	0;
		$new_total_number_of			=	0;
		$customer_data					=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of
													);
		$this							->	db->where('person_id', $merge_from_client);
		$this							->	db->where('branch_code', $this->config->item('branch_code'));
		$this							->	db->update('customers', $customer_data);
		
		// 4) delete the from client
		if (!$this->Customer->delete($merge_from_client))
		{
			// set success indicator and message
			$this					->	session->set_flashdata('success_or_failure', 'F');
			$this					->	session->set_flashdata('message', $this->lang->line('customers_unsuccessful_deleted').' => '.$merge_from_client);
			$this					->	session->set_flashdata('merge_from_client', $merge_from_client);
			$this					->	session->set_flashdata('merge_to_client', $merge_to_client);
			redirect('customers/merge');
		}
		
		// everything done ok
		$this					->	session->set_flashdata('success_or_failure', 'S');
		$this					->	session->set_flashdata('message', $this->lang->line('customers_merge_successfull').' => '.$merge_from_client.' => '.$merge_to_client);
		$this					->	session->set_flashdata('merge_from_client', $merge_from_client);
		$this					->	session->set_flashdata('merge_to_client', $merge_to_client);
		redirect('customers');
	}
	
	function verify()
	{
		$input['account_number'] = $_SESSION['transaction_info']->account_number;
		$suppliers_account_number = $this->Supplier->get_account_number($input);
		if(isset($suppliers_account_number))
		{
			if(intval($suppliers_account_number[0]['person_id']) != intval($_SESSION['transaction_info']->person_id))
			{
                // set message
                $_SESSION['error_code']			=	'07420';
                redirect($_SESSION['controller_name']);
			}
		}

		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->last_name)
			OR 	empty($_SESSION['transaction_info']->first_name)
			OR 	empty($_SESSION['transaction_info']->zip)
			OR 	empty($_SESSION['transaction_info']->company_name)
			OR 	empty($_SESSION['transaction_info']->account_number)
			OR 	empty($_SESSION['transaction_info']->email)
			)
		{
			// set message
			$_SESSION['error_code']			=	'00030';
			redirect($_SESSION['controller_name']);
		}
		
		// verify email format		
		if (!$this							->	Common_routines->check_email_format())
		{
			// set message
			$_SESSION['error_code']			=	'00020';
			redirect($_SESSION['controller_name']);
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
	}
}
?>
