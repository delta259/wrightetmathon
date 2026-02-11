<?php
class Paymethods extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"18";
		
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
		$config['base_url'] 		= 	site_url('/paymethods/index');
		$config['total_rows'] 		= 	$this->Paymethod->count_all();
		$this						->	pagination->initialize($config);
		
		// setup output data
		$data['links']				=	$this->pagination->create_links();	
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->Common_routines->set_form_width();
		$create_headers				=	1;
		$data['manage_table']		=	get_paymethods_manage_table($this->Paymethod->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);
		
		$this->load->view('paymethods/manage',$data);
	}

	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_paymethods_manage_table($this->Paymethod->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Paymethod->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($paymethod_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_id']								=	$paymethod_id;
		
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
		
		// set data
		switch ($paymethod_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']							=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']							=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Paymethod->get_info($paymethod_id);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->payment_method_description;
						break;
						
						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->payment_method_description;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("paymethods");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_payment_method_code']);
			$_SESSION['original_payment_method_code']					=	$_SESSION['transaction_info']->payment_method_code;
			$_SESSION['first_time']										=	1;
		}
		
		// load input data
		$_SESSION['transaction_info']->payment_method_code				=	$this->input->post('payment_method_code');
		$_SESSION['transaction_info']->payment_method_description		=	$this->input->post('payment_method_description');												
		$_SESSION['transaction_info']->payment_method_include			=	$this->input->post('payment_method_include');
		$_SESSION['transaction_info']->payment_method_display_order		=	$this->input->post('payment_method_display_order');
		$_SESSION['transaction_info']->payment_method_fidelity_flag		=	$this->input->post('payment_method_fidelity_flag');
		$_SESSION['transaction_info']->payment_method_giftcard_flag		=	$this->input->post('payment_method_giftcard_flag');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');
		
		// do data verifications
		$this->															verify();
		
		// if here then all checks succeeded so do the update
		$this->															Paymethod->save();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'01980';
					$this->												view($_SESSION['transaction_info']->payment_method_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'01990';
					$this->												view($_SESSION['transaction_info']->payment_method_id, $_SESSION['origin']);
			break;	
		}
	}
	
	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->payment_method_code)
			OR 	empty($_SESSION['transaction_info']->payment_method_description)
			)
		{
			// set message
			$_SESSION['error_code']			=	'00030';
			redirect("paymethods");
		}

		// check paymethod code duplicate only if new or changed
		if 	(	($_SESSION['new'] ?? 0) == 1
			OR 	$_SESSION['transaction_info']->payment_method_code != $_SESSION['original_payment_method_code']
			)
		{
			$count							=	$this->Paymethod->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']		=	'01950';
				redirect("paymethods");
			}
		}
		
		// if include = Y test entries
		if ($_SESSION['transaction_info']->payment_method_include == 'Y')
		{
			// check required fields
			if 	(	empty($_SESSION['transaction_info']->payment_method_display_order)
				)
			{
				// set message
				$_SESSION['error_code']			=	'01960';
				redirect("paymethods");
			}
			
			// check paymethod display order is numeric
			if (!is_numeric($_SESSION['transaction_info']->payment_method_display_order))
			{
				// set message
				$_SESSION['error_code']			=	'01970';
				redirect("paymethods");
			}
		}
		
		// if include is N, blank fields
		if ($_SESSION['transaction_info']->payment_method_include == 'N')
		{
			$_SESSION['transaction_info']->payment_method_display_order		=	0;
		}
	}

}
?>
