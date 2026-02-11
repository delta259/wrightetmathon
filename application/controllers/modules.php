<?php
class modules extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"28";
		
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
		$config['total_rows'] 											= 	$this->Module->count_all();
		$this															->	pagination->initialize($config);	
	
		// setup output data
		$data['links']													=	$this->pagination->create_links();	
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$create_headers													=	1;
		$data['manage_table']											=	get_modules_manage_table($this->Module->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);

		$this															->	load->view('modules/manage',$data);
	}

	function search()
	{
		$search															=	$this->input->post('search');
		$create_headers													=	0;
		$data_rows														=	get_modules_manage_table($this->Module->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													=	$this->Module->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($module_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$module_id;
		
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
		switch ($module_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Module->get_info($module_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->module_name;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->module_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect("modules");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_module_name']);
			$_SESSION['original_module_name']							=	$_SESSION['transaction_info']->module_name;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->module_name						=	$this->input->post('module_name');
		$_SESSION['transaction_info']->name_lang_key					=	$this->input->post('name_lang_key');
		$_SESSION['transaction_info']->desc_lang_key					=	$this->input->post('desc_lang_key');												
		$_SESSION['transaction_info']->sort								=	$this->input->post('sort');
		$_SESSION['transaction_info']->show_in_header					=	$this->input->post('show_in_header');
		$_SESSION['transaction_info']->show_new_button					=	$this->input->post('show_new_button');
		$_SESSION['transaction_info']->show_clone_button				=	$this->input->post('show_clone_button');
		$_SESSION['transaction_info']->show_merge_button				=	$this->input->post('show_merge_button');
		$_SESSION['transaction_info']->show_undel_button				=	$this->input->post('show_undel_button');
		$_SESSION['transaction_info']->show_exit_button					=	$this->input->post('show_exit_button');
		$_SESSION['transaction_info']->user_menu						=	$this->input->post('user_menu');
		$_SESSION['transaction_info']->admin_menu						=	$this->input->post('admin_menu');
		$_SESSION['transaction_info']->sys_admin_menu					=	$this->input->post('sys_admin_menu');
		
		// strip spaces from Module name
		//$_SESSION['transaction_info']->Module_name					=	preg_replace('/\s+/', '', $_SESSION['transaction_info']->Module_name);
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Module->save();
		
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05440';
					$this												->	view($_SESSION['transaction_info']->module_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05450';
					$this												->	view($_SESSION['transaction_info']->module_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->module_name)
			OR 	empty($_SESSION['transaction_info']->name_lang_key)
			OR 	empty($_SESSION['transaction_info']->desc_lang_key)
			OR 	empty($_SESSION['transaction_info']->sort)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("modules");
		}

		// check module name duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->module_name != $_SESSION['original_module_name'])
		{
			$count														=	$this->Module->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']									=	'05430';
				redirect("modules");
			}
		}
		
		// verify display order is numeric
		if (!is_numeric($_SESSION['transaction_info']->sort))
		{
			// set message
			$_SESSION['error_code']										=	'02030';
			redirect("modules");
		}
	}
}
?>
