<?php
class Branches extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"1";
		
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
		$config['base_url'] 		= 	site_url('/branches/index');
		$config['total_rows'] 		= 	$this->Branch->count_all();
		$this						->	pagination->initialize($config);
		
		// setup output data
		$data['links']				=	$this->pagination->create_links();	
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->Common_routines->set_form_width();
		$create_headers				=	1;
		$data['manage_table']		=	get_branches_manage_table($this->Branch->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);
		
		$this->load->view('branches/manage',$data);
	}

	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_branches_manage_table($this->Branch->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Branch->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($branch_code=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_id']								=	$branch_code;
		
		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
			break;
			
			default:
					$_SESSION['origin']							=	$origin;
			break;
		}
		
		// manage session
		$_SESSION['show_dialog']								=	1;
		
		// load branch type pick list
		$_SESSION['branch_type_pick_list']						=	array('I'=>$this->lang->line('branches_branch_type_I'), 'F'=>$this->lang->line('branches_branch_type_F'));
		
		// set data
		switch ($branch_code) 
		{
			// create new
			case	-1:
					$_SESSION['$title']							=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']							=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Branch->get_info($branch_code);
					if (!$_SESSION['transaction_info']) { $_SESSION['transaction_info'] = new stdClass(); $_SESSION['transaction_info']->branch_description = ''; }

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->branch_description;
						break;
						
						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->branch_description;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("branches");
	}
	
	function save()
	{		
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_branch_code']);
			$_SESSION['original_branch_code']							=	$_SESSION['transaction_info']->branch_code;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->branch_code						=	$this->input->post('branch_code');
		$_SESSION['transaction_info']->branch_description				=	$this->input->post('branch_description');												
		$_SESSION['transaction_info']->branch_type						=	$this->input->post('branch_type');
		$_SESSION['transaction_info']->branch_allows_check				=	$this->input->post('branch_allows_check');
		$_SESSION['transaction_info']->branch_ip						=	$this->input->post('branch_ip');
		$_SESSION['transaction_info']->branch_user						=	$this->input->post('branch_user');
		$_SESSION['transaction_info']->branch_password					=	$this->input->post('branch_password');
		$_SESSION['transaction_info']->branch_database					=	$this->input->post('branch_database');
		$_SESSION['transaction_info']->deleted							=	0;
		
		// do data verifications
		$this->															verify();
		
		// if here then all checks succeeded so do the update
		$this->															Branch->save();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'01640';
					$this->												view($_SESSION['transaction_info']->branch_code, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'01650';
					$this->												view($_SESSION['transaction_info']->branch_code, $_SESSION['origin']);
			break;	
		}
	}

	function delete()
	{
		if($this->Category->delete($_SESSION['transaction_info']->category_id))
		{
			// set success message
			$_SESSION['error_code']										=	'00470';
			$_SESSION['del']											=	1;
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}
		
		redirect("categories");
	}
	
	function list_deleted()
	{
		// set flag to select deleted categories
		$_SESSION['undel']					=	1;
		redirect("categories");
	}
	
	function undelete()
	{
		if($this->Category->undelete($_SESSION['transaction_info']->category_id))
		{
			// set success message
			$_SESSION['error_code']										=	'00510';
			unset($_SESSION['undel']);
		}
		else
		{
			$_SESSION['error_code']										=	'00350';
		}
		
		$_SESSION['$title']												=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->category_name;
		$this->															view($_SESSION['transaction_info']->category_id);
	}
	
	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->branch_code)
			OR 	empty($_SESSION['transaction_info']->branch_description)
			OR 	empty($_SESSION['transaction_info']->branch_type)
			OR 	empty($_SESSION['transaction_info']->branch_allows_check)
			)
		{
			// set message
			$_SESSION['error_code']			=	'00030';
			redirect("branches");
		}

		// check branch code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->branch_code != $_SESSION['original_branch_code'])
		{
			$count							=	$this->Branch->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']		=	'01620';
				redirect("branches");
			}
		}
		
		// if allow-check = Y test entries
		if ($_SESSION['transaction_info']->branch_allows_check == 'Y')
		{
			// check required fields
			if 	(	empty($_SESSION['transaction_info']->branch_ip)
				OR 	empty($_SESSION['transaction_info']->branch_user)
				OR 	empty($_SESSION['transaction_info']->branch_password)
				OR 	empty($_SESSION['transaction_info']->branch_database)
				)
			{
				// set message
				$_SESSION['error_code']			=	'00030';
				redirect("branches");
			}
			
			// check branch ip duplicate
			if (($_SESSION['new'] ?? 0) == 1 OR (isset($_SESSION['transaction_info_original']) && $_SESSION['transaction_info']->branch_ip != $_SESSION['transaction_info_original']->branch_ip))
			{
				if (!$this	->	Branch->check_duplicate_ip())
				{
					// set message
					$_SESSION['error_code']			=	'01630';
					redirect("branches");
				}
			}	
		}
		
		// if allows_check is N, blank fields
		if ($_SESSION['transaction_info']->branch_allows_check == 'N')
		{
			$_SESSION['transaction_info']->branch_ip		=	NULL;
			$_SESSION['transaction_info']->branch_user		=	NULL;
			$_SESSION['transaction_info']->branch_password	=	NULL;
			$_SESSION['transaction_info']->branch_database	=	NULL;
		}
	}

}
?>
