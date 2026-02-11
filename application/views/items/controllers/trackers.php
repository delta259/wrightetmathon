<?php
class trackers extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"61";
		
		// set data array
		$data 															=	array();
		
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
		$config['base_url'] 											= 	site_url("/".$_SESSION['controller_name']."/index");
		$config['total_rows'] 											= 	$this->Tracker->count_all();
		$this															->	pagination->initialize($config);	
	
		// setup output data
		$data['links']													=	$this->pagination->create_links();	
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$create_headers													=	1;
		$data['manage_table']											=	get_trackers_manage_table($this->Tracker->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);

		$this															->	load->view('trackers/manage',$data);
	}

	function search()
	{
		$search															=	$this->input->post('search');
		$create_headers													=	0;
		$data_rows														=	get_trackers_manage_table($this->Tracker->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													=	$this->Tracker->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($tracker_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$tracker_id;
		
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
		switch ($tracker_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Tracker->get_info($tracker_id);

					switch ($_SESSION['undel'])
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->tracker_subject;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->tracker_subject;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect("trackers");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if ($_SESSION['first_time'] != 1)
		{
			unset($_SESSION['original_tracker_subject']);
			$_SESSION['original_tracker_subject']						=	$_SESSION['transaction_info']->tracker_subject;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->tracker_subject					=	$this->input->post('tracker_subject');
		$_SESSION['transaction_info']->tracker_description				=	$this->input->post('tracker_description');												
		$_SESSION['transaction_info']->tracker_status					=	$this->input->post('tracker_status');
		$_SESSION['transaction_info']->tracker_commit_summary			=	$this->input->post('tracker_commit_summary');
		$_SESSION['transaction_info']->tracker_changed					=	date('Y-m-d H:i:s');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');		
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Tracker->save();
		
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'])
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05600';
					$this												->	view($_SESSION['transaction_info']->tracker_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05610';
					$this												->	view($_SESSION['transaction_info']->tracker_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->tracker_subject)
			OR 	empty($_SESSION['transaction_info']->tracker_description)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("trackers");
		}
		
		// verify commit summary is entered if commit status
		if ($_SESSION['transaction_info']->tracker_status == 5 AND empty($_SESSION['transaction_info']->tracker_commit_summary))
		{
			// set message
			$_SESSION['error_code']										=	'05590';
			redirect("trackers");
		}
	}
}
?>
