<?php
class Targets extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"25";
		
		// set data array
		$data = array();
		
		// manage session
		$_SESSION['controller_name']=	strtolower(get_class($this));
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
		$config['base_url'] 		= 	site_url('/targets/index');
		$config['total_rows'] 		= 	$this->Target->count_all();
		$this						->	pagination->initialize($config);
		
		// setup output data
		$data['links']				=	$this->pagination->create_links();	
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->Common_routines->set_form_width();
		$create_headers				=	1;
		$data['manage_table']		=	get_targets_manage_table($this->Target->get_all(date("Y"), $config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);
		
		$this->load->view('targets/manage',$data);
	}

	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_targets_manage_table($this->Target->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Target->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($target_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_id']								=	$target_id;
		
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
		switch ($target_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
					$_SESSION['transaction_info']->target_year			=	date('Y');
					$_SESSION['transaction_info']->target_month			=	0;
					$_SESSION['transaction_info']->target_shop_open_days=	0;
					$_SESSION['transaction_info']->target_shop_turnover	=	0;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Target->get_info($target_id);

					switch ($_SESSION['undel'])
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->target_year.'/'.$_SESSION['transaction_info']->target_month;
						break;
						
						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').' 
								 
								 '.$_SESSION['transaction_info']->target_year.'/'.$_SESSION['transaction_info']->target_month;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("targets");
	}
	
	function save()
	{		
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if ($_SESSION['first_time'] != 1)
		{
			unset($_SESSION['original_id']);
			$_SESSION['original_id']									=	$_SESSION['transaction_info']->target_id;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		if ($_SESSION['new'] == 1)
		{
			$_SESSION['transaction_info']->target_year						=	$this->input->post('target_year');
			$_SESSION['transaction_info']->target_month						=	$this->input->post('target_month');
		}
		$_SESSION['transaction_info']->target_day						=	0;											
		$_SESSION['transaction_info']->target_shop_open_days			=	$this->input->post('target_shop_open_days');
		$_SESSION['transaction_info']->target_shop_turnover				=	$this->input->post('target_shop_turnover');
		$_SESSION['transaction_info']->person_id						=	$_SESSION['G']->login_employee_id;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');
		
		// do data verifications
		$this->															verify();
		
		// if here then all checks succeeded so do the update
		$this->															Target->save();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'])
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'04080';
					$this->												view($_SESSION['transaction_id'], $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'04090';
					$this->												view($_SESSION['transaction_info']->target_id, $_SESSION['origin']);
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
		
		$_SESSION['$title']												=	$this->lang->line('common_edit').' => '.$_SESSION['transaction_info']->category_name;
		$this->															view($_SESSION['transaction_info']->category_id);
	}
	
	function verify()
	{
		// verify required fields are entered
		if 	(	!is_numeric($_SESSION['transaction_info']->target_year)
			OR 	!is_numeric($_SESSION['transaction_info']->target_month)
			OR 	!is_numeric($_SESSION['transaction_info']->target_shop_open_days)
			OR 	!is_numeric($_SESSION['transaction_info']->target_shop_turnover)
			)
		{
			// set message
			$_SESSION['error_code']			=	'04060';
			redirect("targets");
		}
		
		// test if year is valid
		if ($_SESSION['transaction_info']->target_year < date("Y"))
		{
			// set message
			$_SESSION['error_code']			=	'03060';
			redirect("targets");
		}
		
		// test if month is valid
		if ($_SESSION['transaction_info']->target_month < 1 OR  $_SESSION['transaction_info']->target_month > 12)
		{
			// set message
			$_SESSION['error_code']			=	'03070';
			redirect("targets");
		}
		
		// test if shop open days is valid
		if ($_SESSION['transaction_info']->target_shop_open_days < 0 OR  $_SESSION['transaction_info']->target_shop_open_days > 31)
		{
			// set message
			$_SESSION['error_code']			=	'05000';
			redirect("targets");
		}
		
		// check year/month duplicate only if new or changed
		if ($_SESSION['new'] == 1)
		{
			$count							=	$this->Target->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']		=	'04070';
				redirect("targets");
			}
		}
	}
}
?>
