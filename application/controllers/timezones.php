<?php
class timezones extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"26";

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
		$config['total_rows'] 											= 	$this->Timezone->count_all();
		$this															->	pagination->initialize($config);

		// setup output data
		$data['links']													=	$this->pagination->create_links();
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['timezones']												=	$this->Timezone->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) );
		$data['count']													=	$this->Timezone->count_all();

		$this															->	load->view('timezones/manage',$data);
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
		$data['timezones']												=	$this->Timezone->search($search);
		$config['total_rows'] 											= 	$data['timezones']->num_rows();
		$this															->	pagination->initialize($config);

		// setup output data
		$data['links']													=	$this->pagination->create_links();
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$data['count']													=	$data['timezones']->num_rows();

		$this															->	load->view('timezones/manage', $data);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													=	$this->Timezone->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($timezone_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$timezone_id;
		
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
		switch ($timezone_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Timezone->get_info($timezone_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->timezone_name;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->timezone_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect("timezones");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_timezone_name']);
			$_SESSION['original_timezone_name']							=	$_SESSION['transaction_info']->timezone_name;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->timezone_name					=	$this->input->post('timezone_name');
		$_SESSION['transaction_info']->timezone_description				=	$this->input->post('timezone_description');												
		$_SESSION['transaction_info']->timezone_continent				=	$this->input->post('timezone_continent');
		$_SESSION['transaction_info']->timezone_city					=	$this->input->post('timezone_city');
		$_SESSION['transaction_info']->timezone_offset					=	$this->input->post('timezone_offset');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');		
		
		// strip spaces from timezone name
		//$_SESSION['transaction_info']->timezone_name					=	preg_replace('/\s+/', '', $_SESSION['transaction_info']->timezone_name);
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Timezone->save();

		// reload pick list	
		$this															->	Timezone->load_pick_list();
		
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05410';
					$this												->	view($_SESSION['transaction_info']->timezone_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05420';
					$this												->	view($_SESSION['transaction_info']->timezone_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->timezone_name)
			OR 	empty($_SESSION['transaction_info']->timezone_description)
			OR 	empty($_SESSION['transaction_info']->timezone_continent)
			OR 	empty($_SESSION['transaction_info']->timezone_city)
			OR 	empty($_SESSION['transaction_info']->timezone_offset)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("timezones");
		}

		// check timezone code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->timezone_name != $_SESSION['original_timezone_name'])
		{
			$count														=	$this->Timezone->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']									=	'5400';
				redirect("timezones");
			}
		}
	}
}
?>
