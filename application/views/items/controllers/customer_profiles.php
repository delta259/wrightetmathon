<?php
class Customer_profiles extends CI_Controller
{
	function index()
	{	
		// set module id
		$_SESSION['module_id']											=	"10";
		
		// set data array
		$data 															= array();
		
		// manage session
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);
		
		// set list title if undelete
		switch ($_SESSION['undel'])
		{
			case	1:
					$data['title']										=	$this->lang->line('common_undelete');
			break;
				
			default:
					$data['title']										=	'';
			break;
		}
		
		// set up the pagination
		$config															=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 											= 	site_url('/customer_profiles/index');
		$config['total_rows'] 											= 	$this->Customer_profile->count_all();
		$this															->	pagination->initialize($config);
		
		// setup output data
		$data['links']													=	$this->pagination->create_links();	
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$create_headers													=	1;
		$data['manage_table']											=	get_customer_profiles_manage_table($this->Customer_profile->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);

		$this->load->view('customer_profiles/manage',$data);
	}

	function search()
	{
		$search															=	$this->input->post('search');
		$create_headers													=	0;
		$data_rows														=	get_customer_profiles_manage_table($this->Customer_profile->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													= $this->Customer_profile->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($profile_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$profile_id;
		
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
		switch ($profile_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Customer_profile->get_info($_SESSION['transaction_id']);

					switch ($_SESSION['undel'])
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->profile_name;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->profile_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect															("customer_profiles");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if ($_SESSION['first_time'] != 1)
		{
			unset($_SESSION['original_profile_name']);
			$_SESSION['original_profile_name']							=	$_SESSION['transaction_info']->profile_name;
			
			$_SESSION['first_time']										=	1;
		}
		
		
		// load input data
		$_SESSION['transaction_info']->profile_name						=	$this->input->post('profile_name');
		$_SESSION['transaction_info']->profile_description				=	$this->input->post('profile_description');												
		$_SESSION['transaction_info']->profile_discount					=	$this->input->post('profile_discount');
		$_SESSION['transaction_info']->profile_fidelity					=	$this->input->post('profile_fidelity');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');		
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Customer_profile->save();
		
		// load pick list
		$this															->	Customer_profile->load_pick_list();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'])
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05320';
					$this->												view($_SESSION['transaction_info']->profile_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05330';
					$this->												view($_SESSION['transaction_info']->profile_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->profile_name)
			OR 	empty($_SESSION['transaction_info']->profile_description)
			OR 	empty($_SESSION['transaction_info']->profile_fidelity)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect														("customer_profiles");
		}

		// check profile code duplicate only if new or changed
		if ($_SESSION['new'] == 1 OR $_SESSION['transaction_info']->profile_name != $_SESSION['original_profile_name'])
		{
			$count														=	$this->Customer_profile->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']									=	'05300';
				redirect													("customer_profiles");
			}
		}
		
		// check discount is numeric if entered
		if (!empty($_SESSION['transaction_info']->profile_discount))
		{
			if (!is_numeric($_SESSION['transaction_info']->profile_discount))
			{
				// set message
				$_SESSION['error_code']									=	'05310';
				redirect													("customer_profiles");
			}
		}
	}
}
?>
