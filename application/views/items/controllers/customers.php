<?php
class Customers extends CI_controller
{	
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"9";
		
		// set data array
		$data = array();
		
		// manage session
		$_SESSION['controller_name']=	strtolower(get_class($this));
		$_SESSION['module_info']	=	$this->Module->get_module_info($_SESSION['controller_name'])->row_array();
		unset($_SESSION['report_controller']);
		
		// set list title if undelete
		switch ($_SESSION['undel'])
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
		$config['base_url'] 		= 	site_url('/customers/index');
		$config['total_rows'] 		= 	$this->Customer->count_all();
		$this						->	pagination->initialize($config);
		
		// setup output data
		$data['links']				=	$this->pagination->create_links();
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->get_form_width();
		$create_headers				=	1;
		$data['manage_table']		=	get_people_manage_table($this->Customer->get_all($config['per_page'], $this->uri->segment($config['uri_segment'])), $this, $create_headers);

		// show data
		$this						->	load->view('people/manage', $data);
	}
	
	/*
	Returns customer table data rows. This will be called with AJAX.
	*/
	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_people_manage_table($this->Customer->search($search), $this, $create_headers);
		echo $data_rows;
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Customer->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
	
	/*
	Loads the customer edit form
	*/
	function	view													($customer_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		//$_SESSION['customer_id']										=	$customer_id;

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
			break;
			
			default:
					$_SESSION['origin']									=	$origin;
			break;
		}

		// manage session
		$_SESSION['show_dialog']										=	1;
		
		// set data
		switch ($customer_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
					$_SESSION['selected_on_stop_indicator']				=	'N';
					$_SESSION['selected_taxable']						=	'Y';
					$_SESSION['selected_fidelity_flag']					=	'Y';
					$_SESSION['selected_profile_id']					=	$this->config->item('profile_id');
					$_SESSION['transaction_info']->fidelity_points		=	0;
					$_SESSION['transaction_info']->fidelity_value		=	0;
					$_SESSION['transaction_info']->dob_day				=	01;
					$_SESSION['transaction_info']->dob_month			=	01;
					$_SESSION['transaction_info']->dob_year				=	1970;
					
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Customer->get_info($customer_id);
					$_SESSION['selected_on_stop_indicator']				=	$_SESSION['transaction_info']->on_stop_indicator;
					$_SESSION['selected_taxable']						=	$_SESSION['transaction_info']->taxable;
					$_SESSION['selected_fidelity_flag']					=	$_SESSION['transaction_info']->fidelity_flag;
					$_SESSION['transaction_info']->fidelity_value		=	$_SESSION['transaction_info']->fidelity_points * $this->config->item('fidelity_value');	
					
					if (empty($_SESSION['transaction_info']->profile_id))
					{
						$_SESSION['selected_profile_id']				=	$this->config->item('profile_id');
					}
					else
					{
						$_SESSION['selected_profile_id']				=	$_SESSION['transaction_info']->profile_id;
					}
					
					$_SESSION['full_name_out']							=	$this->Common_routines->format_full_name($_SESSION['transaction_info']->last_name, $_SESSION['transaction_info']->first_name);

					switch ($_SESSION['undel'])
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['full_name_out'];
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['full_name_out'];
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("customers");
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
		$pieces 														=	explode("/", $this->input->post('dob'));
		$_SESSION['transaction_info']->dob_day							=	$pieces[0];
		$_SESSION['transaction_info']->dob_month						=	$pieces[1];
		$_SESSION['transaction_info']->dob_year							=	$pieces[2];
		
		// load customer data
		$_SESSION['transaction_info']->account_number					=	$this->input->post('account_number');
		$_SESSION['transaction_info']->taxable							=	$this->input->post('taxable');
		$_SESSION['transaction_info']->on_stop_indicator				=	$this->input->post('on_stop_indicator');
		$_SESSION['transaction_info']->on_stop_amount					=	$this->input->post('on_stop_amount');
		$_SESSION['transaction_info']->on_stop_reason					=	$this->input->post('on_stop_reason');
		$_SESSION['transaction_info']->pricelist_id						=	$this->input->post('pricelist_id');
		$_SESSION['transaction_info']->profile_id						=	$this->input->post('profile_id');
		$_SESSION['transaction_info']->profile_reference				=	$this->input->post('profile_reference');
		$_SESSION['transaction_info']->fidelity_flag					=	$this->input->post('fidelity_flag');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');

		// manage session
		switch ($_SESSION['new'])
		{
			// add client
			case	1:
					// zero fidelity_points
					$_SESSION['transaction_info']->fidelity_points		=	0;
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
		
		// if here then all checks succeeded so do the update
		$this->															Customer->save();
			
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'])
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'00080';
					$this->												view($_SESSION['transaction_info']->person_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'00090';
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
	This deletes customers from the customers table
	*/
	function delete()
	{
		// check this is not the default client
		if ($_SESSION['transaction_info']->person_id == $this->config->item('default_client_id'))
		{
			// set error
			$_SESSION['error_code']										=	'00075';
			redirect($_SESSION['controller_name']);
		}
		
		if($this->Customer->delete())
		{
			// set success message
			$_SESSION['error_code']			=	'00440';
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
		// set flag to select deleted customers
		$_SESSION['undel']					=	1;
		redirect($_SESSION['controller_name']);
	}
	
	function undelete()
	{
		if($this->Customer->undelete())
		{
			// set success message
			$_SESSION['error_code']			=	'00480';
			unset($_SESSION['undel']);
		}
		else
		{
			$_SESSION['error_code']			=	'00350';
		}
		
		$_SESSION['full_name_out']			=	$this->Common_routines->format_full_name($_SESSION['transaction_info']->last_name, $_SESSION['transaction_info']->first_name);
		$_SESSION['$title']					=	$this->lang->line('common_edit').'  '.$_SESSION['full_name_out'];
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
	
	function merge_form($merge_step=0)
	{			
		// function not available at this time							
		// redirect("customers");
		
		// set session data
		unset($_SESSION['transaction_info']->merge_from_id);
		unset($_SESSION['transaction_info']->merge_to_id);
		unset($_SESSION['merge_from_id']);
		unset($_SESSION['merge_to_id']);
		unset($_SESSION['new']);
		unset($_SESSION['clone']);
		unset($_SESSION['merge_ok']);
		$_SESSION['merge']					=	1;	
		$_SESSION['$title']					=	$this->lang->line($_SESSION['controller_name'].'_merge');
		
		// set dialog switch
		$_SESSION['show_dialog']			=	5;
		
		// show the data entry							
		redirect("customers");
	}
	
	function merge_do()
	{	
		// test confirm
		if ($_SESSION['merge_ok'] != 2)
		{
			// intialise
			$_SESSION['transaction_info']		=	new stdClass();
			$_SESSION['transaction_from']		=	new stdClass();
			$_SESSION['transaction_to']			=	new stdClass();
			
			// get data
			$_SESSION['transaction_info']->merge_from_id	=	$this->input->post('merge_from_id');
			$_SESSION['transaction_info']->merge_to_id		=	$this->input->post('merge_to_id');

			// verify input
			$this->verify_merge();
	
			// verifications are ok, so ask for confirmation
			$_SESSION['merge_ok']				=	1;
			redirect("customers");
		}

		// 	if here merge is confirmed so do updates
		
		// update DB
		$update_data				=	array	(
												'customer_id'	=>	$_SESSION['transaction_info']->merge_to_id
												);
												
		// 1) sales file												
		if (!$this->Sale->merge_customer($_SESSION['transaction_info']->merge_from_id, $update_data))
		{
			// set message
			$_SESSION['error_code']		=	'00580';
			redirect("customers/merge_form");
		}
		
		// 2) giftcards file
		if (!$this->Giftcard->merge_customer($_SESSION['transaction_info']->merge_from_id, $update_data))
		{
			// set message
			$_SESSION['error_code']		=	'00590';
			redirect("customers/merge_form");
		}
		
		
		// 3) sales_suspended
		if (!$this->Sale_suspended->merge_customer($_SESSION['transaction_info']->merge_from_id, $update_data))
		{
			// set message
			$_SESSION['error_code']		=	'00600';
			redirect("customers/merge_form");
		}
		
		// 3) reclculate sales value and number of sales for to client
		// zero the to client fields being updated
		$new_total						=	0;
		$new_total_number_of			=	0;
		$customer_data					=	array	(
													'sales_ht'			=>	$new_total,
													'sales_number_of'	=>	$new_total_number_of
													);
		$this							->	db->where('person_id', $_SESSION['transaction_info']->merge_to_id);
		$this							->	db->where('branch_code', $this->config->item('branch_code'));
		$this							->	db->update('customers', $customer_data);
		
		// get the sales data
		$data 							=	array();
		$this							->	db->select();
		$this							->	db->from('sales');
		$this							->	db->where('sales.customer_id', $_SESSION['transaction_info']->merge_to_id);
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
		$this							->	db->where('person_id', $_SESSION['transaction_info']->merge_from_id);
		$this							->	db->where('branch_code', $this->config->item('branch_code'));
		$this							->	db->update('customers', $customer_data);
		
		// 4) delete the from client
		$_SESSION['transaction_info']->person_id =	$_SESSION['transaction_info']->merge_from_id;
		if (!$this->Customer->delete())
		{
			// set message
			$_SESSION['error_code']		=	'00640';
			redirect("customers/merge_form");
		}
		
		// set message
			$_SESSION['error_code']		=	'00650';
			redirect("customers/merge_form");
	}
	
	function verify()
	{			
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->last_name)
			OR 	empty($_SESSION['transaction_info']->first_name)
			OR 	empty($_SESSION['transaction_info']->zip)
			OR 	empty($_SESSION['transaction_info']->sex)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
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
		
		// verify input if on stop indicator is Y
		if ($_SESSION['transaction_info']->on_stop_indicator == 'Y')
		{
			// test on stop amount is numeric
			if (!is_numeric($_SESSION['transaction_info']->on_stop_amount))
			{
				// set message
				$_SESSION['error_code']			=	'00050';
				redirect($_SESSION['controller_name']);
			}
			
			// test on stop amount is not zero
			if ($_SESSION['transaction_info']->on_stop_amount == 0)
			{
				// set message
				$_SESSION['error_code']			=	'00055';
				redirect($_SESSION['controller_name']);
			}
			
			// test on stop reason is entered
			if (empty($_SESSION['transaction_info']->on_stop_reason))
			{
				// set message
				$_SESSION['error_code']			=	'00060';
				redirect($_SESSION['controller_name']);
			}
		}
		
		// set fidelity points to 0 if fidelity flag 'N'
		if ($_SESSION['transaction_info']->fidelity_flag == 'N')
		{
			$_SESSION['transaction_info']->fidelity_points	=	0;
		}
		
		// if fidelity is Y then either the telphone or email must be entered
		if ($_SESSION['transaction_info']->fidelity_flag == 'Y')
		{
			if (empty($_SESSION['transaction_info']->phone_number) AND empty($_SESSION['transaction_info']->email))
			{
				// set message
				$_SESSION['error_code']									=	'05350';
				redirect($_SESSION['controller_name']);
			}
		}
		
		// set on stop fields if on stop indicator = N
		if ($_SESSION['transaction_info']->on_stop_indicator == 'N')
		{
			$_SESSION['transaction_info']->on_stop_amount	=	0;
			$_SESSION['transaction_info']->on_stop_reason	=	NULL;
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

			// check email duplicate
			$_SESSION['check_email_dup']		=	1;			
			if (!$this							->	Common_routines->common_check_duplicate())
			{
				// set message
				$_SESSION['error_code']			=	'00070';
				redirect($_SESSION['controller_name']);
			}
		}
		
		// check profile data
		if ($_SESSION['transaction_info']->profile_id != $this->config->item('profile_id'))
		{
			if (empty($_SESSION['transaction_info']->profile_reference))
			{
				// set message
				$_SESSION['error_code']									=	'05340';
				redirect($_SESSION['controller_name']);
			}
			
			if ($_SESSION['transaction_info']->fidelity_flag == 'Y')
			{
				// set message
				$_SESSION['error_code']									=	'05360';
				redirect($_SESSION['controller_name']);
			}
		}
		else
		{
			$_SESSION['transaction_info']->profile_reference			=	NULL;
		}
		
	}
	
	function verify_merge()
	{
		// check input not blank
		if (empty($_SESSION['transaction_info']->merge_from_id) OR empty($_SESSION['transaction_info']->merge_to_id))
		{
			// set message
			$_SESSION['error_code']		=	'00030';
			redirect("customers");
		}
		
		// check input not same
		if ($_SESSION['transaction_info']->merge_from_id == $_SESSION['transaction_info']->merge_to_id)
		{
			// set message
			$_SESSION['error_code']		=	'00555';
			redirect("customers");
		}
		
		// check merge_from_customer valid
		if (!$this->Customer->exists($_SESSION['transaction_info']->merge_from_id))
		{
			// set message
			$_SESSION['error_code']		=	'00560';
			redirect("customers");
		}
		
		// check merge_from_customer not deleted		
		// get item info
		$_SESSION['transaction_from']	=	$this->Customer->get_info($_SESSION['transaction_info']->merge_from_id);
		
		// test deleted
		if ($_SESSION['transaction_from']->deleted == 1)
		{
			// set message
			$_SESSION['error_code']		=	'00565';
			redirect("customers");
		}
		
		// check merge_to_item valid
		if (!$this->Customer->exists($_SESSION['transaction_info']->merge_to_id))
		{
			// set message
			$_SESSION['error_code']		=	'00570';
			redirect("customers");
		}
		
		// check merge_to_customer not deleted		
		// get customer info
		$_SESSION['transaction_to']		=	$this->Customer->get_info($_SESSION['transaction_info']->merge_to_id);
		
		// test deleted
		if ($_SESSION['transaction_to']->deleted == 1)
		{
			// set message
			$_SESSION['error_code']		=	'00575';
			redirect("customers");
		}
	}
}
?>
