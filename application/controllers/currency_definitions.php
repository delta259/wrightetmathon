<?php
class Currency_definitions extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"8";
		
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
		$config['base_url'] 		= 	site_url('/currency_definitions/index');
		$config['total_rows'] 		= 	$this->Currency_definition->count_all();
		$this						->	pagination->initialize($config);
		
		// setup output data
		$data['links']				=	$this->pagination->create_links();	
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->Common_routines->set_form_width();
		$create_headers				=	1;
		$data['manage_table']		=	get_currency_definitions_manage_table($this->Currency_definition->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);
		
		$this->load->view('currency_definitions/manage',$data);
	}

	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_currency_definitions_manage_table($this->Currency_definitions->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Currency_definition->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($denomination=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_id']								=	$denomination;
		
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
		
		// load currency type pick list
		$_SESSION['currency_type_pick_list']					=	array('N'=>$this->lang->line('currency_definitions_type_N'), 'C'=>$this->lang->line('currency_definitions_type_C'));
		
		// set data
		switch ($denomination) 
		{
			// create new
			case	-1:
					$_SESSION['$title']							=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']							=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Currency_definition->get_info($denomination);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->display_name;
						break;
						
						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->display_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("currency_definitions");
	}
	
	function save()
	{		
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_denomination']);
			$_SESSION['original_denomination']							=	$_SESSION['transaction_info']->denomination;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->denomination						=	$this->input->post('denomination');
		$_SESSION['transaction_info']->display_name						=	$this->input->post('display_name');												
		$_SESSION['transaction_info']->display_order					=	$this->input->post('display_order');
		$_SESSION['transaction_info']->type								=	$this->input->post('type');
		$_SESSION['transaction_info']->cashtill							=	$this->input->post('cashtill');
		$_SESSION['transaction_info']->multiplier						=	$this->input->post('multiplier');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');
		
		// do data verifications
		$this->															verify();
		
		// if here then all checks succeeded so do the update
		$this->															Currency_definition->save();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'02050';
					$this->												view($_SESSION['transaction_info']->denomination, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'02060';
					$this->												view($_SESSION['transaction_info']->denomination, $_SESSION['origin']);
			break;	
		}
	}
	
	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->denomination)
			OR 	empty($_SESSION['transaction_info']->display_name)
			OR 	empty($_SESSION['transaction_info']->display_order)
			OR 	empty($_SESSION['transaction_info']->type)
			OR 	empty($_SESSION['transaction_info']->cashtill)
			OR 	empty($_SESSION['transaction_info']->multiplier)
			)
		{
			// set message
			$_SESSION['error_code']			=	'00030';
			redirect("currency_definitions");
		}
		
		// verify display order is numeric
		if (!is_numeric($_SESSION['transaction_info']->display_order))
		{
			// set message
			$_SESSION['error_code']			=	'02030';
			redirect("currency_definitions");
		}
		
		// verify multiplier is numeric
		if ($_SESSION['transaction_info']->cashtill == 'Y')
		{
			if (!is_numeric($_SESSION['transaction_info']->multiplier))
			{
				// set message
				$_SESSION['error_code']		=	'02040';
				redirect("currency_definitions");
			}
			
			if ($_SESSION['transaction_info']->multiplier <= 0)
			{
				// set message
				$_SESSION['error_code']		=	'02045';
				redirect("currency_definitions");
			}
		}

		// check denomination duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->denomination != $_SESSION['original_denomination'])
		{
			$count							=	$this->Currency_definition->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']		=	'02020';
				redirect("currency_definitions");
			}
		}
		
		// if cashtill is N, blank fields
		if ($_SESSION['transaction_info']->cashtill == 'N')
		{
			$_SESSION['transaction_info']->multiplier		=	NULL;
		}
	}

}
?>
