<?php
class Pricelists extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"19";
		
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
		$config['base_url'] 		= 	site_url('/pricelists/index');
		$config['total_rows'] 		= 	$this->Pricelist->count_all();
		$this						->	pagination->initialize($config);
		
		// setup output data
		$data['links']				=	$this->pagination->create_links();	
		$data['controller_name']	=	strtolower(get_class($this));
		$data['form_width']			=	$this->Common_routines->set_form_width();
		$create_headers				=	1;
		$data['manage_table']		=	get_pricelists_manage_table($this->Pricelist->get_all($config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this, $create_headers);

		$this->load->view('pricelists/manage',$data);
	}

	function search()
	{
		$search						=	$this->input->post('search');
		$create_headers				=	0;
		$data_rows					=	get_pricelists_manage_table($this->Pricelist->search($search), $this, $create_headers);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Pricelist->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function view($pricelist_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_id']								=	$pricelist_id;
		
		// load currencies pick list
		$currency_pick_list					=	array();
		$currencies							=	array();
		foreach($this->Currency->get_all()->result() as $row)
		{
			$currency_pick_list[$row->currency_id] =	$row->currency_name;
		}
		$_SESSION['currency_pick_list']		=	$currency_pick_list;
		
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
		switch ($pricelist_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title']							=	$this->lang->line($_SESSION['controller_name'].'_new');
					$_SESSION['new']							=	1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info']				=	$this->Pricelist->get_info($_SESSION['transaction_id']);

					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']				=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->pricelist_name;
						break;
						
						default:
								$_SESSION['$title']				=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->pricelist_name;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}
		
		redirect("pricelists");
	}
	
	function save()
	{						
		// save orignal data but only first time through
		// be aware that if there is an error in the data input you will loop through this many times
		// this is essentially done for dumplicate checking
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			unset($_SESSION['original_pricelist_name']);
			$_SESSION['original_pricelist_name']						=	$_SESSION['transaction_info']->pricelist_name;
			
			unset($_SESSION['original_pricelist_default']);
			$_SESSION['original_pricelist_default']						=	$_SESSION['transaction_info']->pricelist_default;
			
			$_SESSION['first_time']										=	1;
		}
		
		
		// load input data
		$_SESSION['transaction_info']->pricelist_name					=	$this->input->post('pricelist_name');
		$_SESSION['transaction_info']->pricelist_description			=	$this->input->post('pricelist_description');												
		$_SESSION['transaction_info']->pricelist_currency				=	$this->input->post('pricelist_currency');
		$_SESSION['transaction_info']->pricelist_default				=	$this->input->post('pricelist_default');
		$_SESSION['transaction_info']->deleted							=	0;
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');		
		
		// do data verifications
		$this															->	verify();
		
		// if here then all checks succeeded so do the update
		$this															->	Pricelist->save();
		
		// reload pick list
		$this															->	Pricelist->load_pick_list();

		// test for added or updated and set appropriate message
		switch ($_SESSION['new'] ?? 0)
		{
			case	1:
					// set message
					$_SESSION['error_code']								=	'05080';
					$this->												view($_SESSION['transaction_info']->pricelist_id, $_SESSION['origin']);
			break;
					
			default:
					// set message
					unset($_SESSION['new']);
					$_SESSION['error_code']								=	'05090';
					$this->												view($_SESSION['transaction_info']->pricelist_id, $_SESSION['origin']);
			break;	
		}
	}

	function verify()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->pricelist_name)
			OR 	empty($_SESSION['transaction_info']->pricelist_description)
			OR 	empty($_SESSION['transaction_info']->pricelist_currency)
			OR 	empty($_SESSION['transaction_info']->pricelist_default)
			)
		{
			// set message
			$_SESSION['error_code']			=	'00030';
			redirect("pricelists");
		}

		// check pricelist code duplicate only if new or changed
		if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->pricelist_name != $_SESSION['original_pricelist_name'])
		{
			$count							=	$this->Pricelist->check_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']		=	'05060';
				redirect("pricelists");
			}
		}
		
		// check pricelist default duplicate only if new or changed AND = y
		if ($_SESSION['transaction_info']->pricelist_default == 'Y')
		{
			if (($_SESSION['new'] ?? 0) == 1 OR $_SESSION['transaction_info']->pricelist_default != $_SESSION['original_pricelist_default'])
			{
				$count							=	$this->Pricelist->check_duplicate_default();
				if ($count > 0)
				{
					// set message
					$_SESSION['error_code']		=	'05070';
					redirect("pricelists");
				}
			}
		}
	}
}
?>
