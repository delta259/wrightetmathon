<?php
class Currencies extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"7";
		
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
		$config['base_url'] 											= 	site_url('/currencies/index');
		$config['total_rows'] 											= 	$this->Currency->count_all();
		$this															->	pagination->initialize($config);
			
		// setup output data
		$data['links']													=	$this->pagination->create_links();	
		$data['controller_name']										=	strtolower(get_class($this));
		$data['form_width']												=	$this->Common_routines->set_form_width();
		$create_headers													=	1;
		$data['manage_table']											=	get_currencies_manage_table($this->Currency->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);

		$this															->	load->view('currencies/manage',$data);
	}

	function search()
	{
		$search															=	$this->input->post('search');
		$create_headers													=	0;
		$data_rows														=	get_currencies_manage_table($this->Currency->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions 													=	$this->Currency->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($currency_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_id']										=	$currency_id;
		
		// set up currency side dropdown
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
		switch ($currency_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']									=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']									=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']						=	$this->Currency->get_info($currency_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->currency_name;
						break;
						
						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->currency_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect("currencies");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_currency_name']);
			$_SESSION['original_currency_name']							=	$_SESSION['transaction_info']->currency_name;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->currency_name					=	$this->input->post('currency_name');
		$_SESSION['transaction_info']->currency_description				=	$this->input->post('currency_description');												
		$_SESSION['transaction_info']->currency_code					=	$this->input->post('currency_code');
		$_SESSION['transaction_info']->currency_sign					=	$this->input->post('currency_sign');
		$_SESSION['transaction_info']->currency_side					=	$this->input->post('currency_side');
		$_SESSION['transaction_info']->currency_tax						=	$this->input->post('currency_tax');
		$_SESSION['transaction_info']->currency_display_order			=	$this->input->post('currency_display_order');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');		
		
		// strip spaces from currency name
		//$_SESSION['transaction_info']->currency_name					=	preg_replace('/\s+/', '', $_SESSION['transaction_info']->currency_name);
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Currency->save();
		
		// reload the global currencies
		$_SESSION['G']->currency_details								=	$this->Currency->get_info($this->config->item('currency'));
		
		// and pick list
		$this															->	Currency->load_pick_list();
		
		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05040';
					$this												->	view($_SESSION['transaction_info']->currency_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05050';
					$this												->	view($_SESSION['transaction_info']->currency_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->currency_name)
			OR 	empty($_SESSION['transaction_info']->currency_description)
			OR 	empty($_SESSION['transaction_info']->currency_code)
			OR 	empty($_SESSION['transaction_info']->currency_display_order)
			)
		{
			// set message
			$_SESSION['error_code']										=	'00030';
			redirect("currencies");
		}

		// check Currency code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->currency_name != $_SESSION['original_currency_name'])
		{
			$count														=	$this->Currency->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']									=	'5030';
				redirect("currencies");
			}
		}
		
		// verify display order is numeric
		if (!is_numeric($_SESSION['transaction_info']->currency_display_order))
		{
			// set message
			$_SESSION['error_code']										=	'02030';
			redirect("currencies");
		}
	}

	function delete($currency_id)
	{
		if ($this->Currency->delete($currency_id))
		{
			$_SESSION['error_code']											=	'01660';
		}
		else
		{
			$_SESSION['error_code']											=	'00350';
		}
		redirect("currencies");
	}
}
?>
