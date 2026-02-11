<?php
class countries extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"6";

		// set data array
		$data 															=	array();

		// manage session
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);

		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
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
		$config['base_url'] 											= 	site_url("/".$_SESSION['controller_name']."/index");
		$config['total_rows'] 											= 	$this->Country->count_all();
		$this															->	pagination->initialize($config);

		// setup output data
		$data['links']													=	$this->pagination->create_links();
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['countries']												=	$this->Country->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );
		$data['count']													=	$this->Country->count_all();

		$this															->	load->view('countries/manage',$data);
	}

	function search()
	{
		$data															=	array();
		$search															=	$this->input->post('search');
		$_SESSION['recherche']											=	1;
		$_SESSION['filtre_recherche']									=	$search;

		// set list title if undelete
		$data['title']													=	($_SESSION['undel'] == 1) ? $this->lang->line('common_undelete') : '';

		// set up the pagination
		$config															=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 											= 	site_url("/".$_SESSION['controller_name']."/index");
		$data['countries']												=	$this->Country->search($search);
		$config['total_rows'] 											= 	$data['countries']->num_rows();
		$this															->	pagination->initialize($config);

		// setup output data
		$data['links']													=	$this->pagination->create_links();
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['count']													=	$data['countries']->num_rows();

		$this															->	load->view('countries/manage', $data);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													=	$this->Country->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($country_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$country_id;
		
		// set up Country side dropdown
		$_SESSION['LorR_pick_list']										=	array('R'=>$this->lang->line('common_right'), 'L'=>$this->lang->line('common_left'));
		
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
		switch ($country_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Country->get_info($country_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->country_name;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->country_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect("countries");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_country_name']);
			$_SESSION['original_country_name']							=	$_SESSION['transaction_info']->country_name;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->country_name						=	$this->input->post('country_name');
		$_SESSION['transaction_info']->country_description				=	$this->input->post('country_description');												
		$_SESSION['transaction_info']->country_display_order			=	$this->input->post('country_display_order');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');		
		
		// strip spaces from Country name
		//$_SESSION['transaction_info']->Country_name					=	preg_replace('/\s+/', '', $_SESSION['transaction_info']->Country_name);
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Country->save();
		
		// reload the global countries
		//$_SESSION['G']->country_details									=	$this->Country->get_info($this->config->item('country'));
		
		// load pick list
		$this															->	Country->load_pick_list();
		
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05380';
					$this												->	view($_SESSION['transaction_info']->country_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05390';
					$this												->	view($_SESSION['transaction_info']->country_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->country_name)
			OR 	empty($_SESSION['transaction_info']->country_description)
			OR 	empty($_SESSION['transaction_info']->country_display_order)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("countries");
		}

		// check Country code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->country_name != $_SESSION['original_country_name'])
		{
			$count														=	$this->Country->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']									=	'05370';
				redirect("countries");
			}
		}
		
		// verify display order is numeric
		if (!is_numeric($_SESSION['transaction_info']->country_display_order))
		{
			// set message
			$_SESSION['error_code']										=	'02030';
			redirect("countries");
		}
	}
}
?>
