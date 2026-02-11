<?php
class Giftcards extends CI_Controller
{
	function index()
	{
		// get flash data
		$data['success_or_failure']	=	$this->session->flashdata('success_or_failure');
		$data['message']			=	$this->session->flashdata('message');
		
		$config['base_url'] = site_url('/giftcards/index');
		$config['total_rows'] = $this->Giftcard->count_all();
		$config['per_page'] = '20';
		$config['uri_segment'] = 3;
		$this->pagination->initialize($config);
		
		$data['controller_name']=strtolower(get_class($this));
		$data['form_width']=$this->get_form_width();
		$data['manage_table']=get_giftcards_manage_table( $this->Giftcard->get_all( $config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this );
		$this->load->view('giftcards/manage',$data);
	}

	function search()
	{
		$search=$this->input->post('search');
		$data_rows=get_giftcards_manage_table_data_rows($this->Giftcard->search($search),$this);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Giftcard->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
/** GARRISON ADDED 5/3/2013 **/
	/*
	 Gives search suggestions for person_id based on what is being searched for
	*/
	function suggest_person()
	{
		$suggestions = $this->Giftcard->get_person_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}
/** END GARRISON ADDED **/
	function get_row()
	{
		$giftcard_id = $this->input->post('row_id');
		$data_row=get_giftcard_data_row($this->Giftcard->get_info($giftcard_id),$this);
		echo $data_row;
	}

	function view($giftcard_id=-1)
	{
		$data['giftcard_info']=$this->Giftcard->get_info($giftcard_id);

		$this->load->view("giftcards/form",$data);
	}
	
	function save($giftcard_id=-1)
	{
		// check valid date entered
		$date_invalid									=	'N';
		$pieces											=	explode('/', $this->input->post('sale_date'));
		
		// $pieces 0 = day, 1 = month, 2 = year
		if ($pieces[0] < 1 OR $pieces[0] > 31)
		{
			$date_invalid								=	'Y';
		}
		if ($pieces[1] < 1 OR $pieces[1] > 12)
		{
			$date_invalid								=	'Y';
		}
		if ($pieces[2] < 2000)
		{
			$date_invalid								=	'Y';
		}
		
		if ($date_invalid == 'Y')
		{
			// send error message
			$success_or_failure							=	'F';
			$message									=	$this->lang->line('common_date_invalid').' => '.$this->input->post('sale_date');
			$this										->	setflash($success_or_failure, $message, $origin);
		}		
		
		// format sale date;	
		$sale_date										=	$pieces[2].'/'.$pieces[1].'/'.$pieces[0];
		
		// test card date not too old (less than six months)
		$six_months_ago									=	date('Y/m/d', mktime(0,0,0,date("m")-6,1,date("Y")));
		if ($sale_date < $six_months_ago)
		{
			// send error message
			$success_or_failure							=	'F';
			$message									=	$this->lang->line('giftcards_sale_date_too_old').' => '.$this->input->post('sale_date');
			$this										->	setflash($success_or_failure, $message, $origin);
		}
		
		// test card date not in future)
		$today											=	date('Y/m/d');
		if ($sale_date > $today)
		{
			// send error message
			$success_or_failure							=	'F';
			$message									=	$this->lang->line('giftcards_sale_date_future').' => '.$this->input->post('sale_date');
			$this										->	setflash($success_or_failure, $message, $origin);
		}
								
		// test card exists - must not already exist in the DB
		$giftcard_exists								=	$this->Giftcard->exists_by_number($this->input->post('giftcard_number'));

		if ($giftcard_exists == 1)
		{
			// send error message
			$success_or_failure							=	'F';
			$message									=	$this->lang->line('sales_giftcard_already_exists').' => '.$this->input->post('giftcard_number');
			$this										->	setflash($success_or_failure, $message, $origin);
		}

		// test card value - must greater than 20
		if ($this->input->post('value') < 20)
		{
			// send error message
			$success_or_failure							=	'F';
			$message									=	$this->lang->line('sales_giftcard_value').' => '.$this->input->post('giftcard_number');
			$this										->	setflash($success_or_failure, $message, $origin);
		}
		
		// set up output data
		$giftcard_data 	= 	array	(
									'giftcard_number'	=>	$this->input->post('giftcard_number'),
									'value		'		=>	$this->input->post('value'),
									'value_used'		=>	0,
									'deleted'			=>	0,
									'customer_id'		=>	$this->config->item('default_client_id'),
									'branch_code'		=>	$this->config->item('branch_code'),
									'sale_id'			=>	0,
									'sale_date'			=>	$sale_date
									);

		// add card to DB
		if (!$this->Giftcard->save($giftcard_data, $giftcard_id))
		{
			// set success indicator and message
			$success_or_failure							=	'F';
			$message									=	$this->lang->line('giftcards_error_adding_updating').' -> '.$this->input->post('giftcard_number');
			$this										->	setflash($success_or_failure, $message, $origin);
		}
		else
		{
			// set success indicator and message
			$success_or_failure							=	'S';
			$message									=	$this->lang->line('giftcards_successful_adding').' -> '.$this->input->post('giftcard_number');
			$this										->	setflash($success_or_failure, $message, $origin);
		}
	}

	function delete()
	{
		$giftcards_to_delete=$this->input->post('ids');

		if($this->Giftcard->delete_list($giftcards_to_delete))
		{
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('giftcards_successful_deleted').' '.
			count($giftcards_to_delete).' '.$this->lang->line('giftcards_one_or_multiple')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('giftcards_cannot_be_deleted')));
		}
	}
		
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 360;
	}
	
	// set the flash data
	function setflash($success_or_failure, $message, $origin)
	{
		$this						->	session->set_flashdata('success_or_failure', $success_or_failure);
		$this						->	session->set_flashdata('message', $message);
		$this						->	session->set_flashdata('origin', $origin);
		redirect('giftcards');
		return;
	}
}
?>
