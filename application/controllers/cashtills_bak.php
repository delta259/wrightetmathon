<?php
class Cashtills extends CI_Controller
{
	function index()
	{				
		// set module id
		$_SESSION['module_id']											=	"2";

		// manage session
		$_SESSION['controller_name']				=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);
		
		// set list title if undelete
		switch ($_SESSION['undel'])
		{
			case	1:
					$data['title']					=	$this->lang->line('common_undelete');
			break;
				
			default:
					$data['title']					=	'';
			break;
		}
		
		// set up the base url
		$config['base_url'] 						= 	site_url('/cashtill/index');
		
		// is the cashtill open for this date?
			// set cash code
			$cash_code								=	'OPEN';
			
			// test to see if open record exists for this date, if so then don't allow access to open cash till
			$count									=	$this->Cashtill->exists(date("Y"), date("m"), date("d"), $cash_code);
			if ($count	!=	0)
			{
				$_SESSION['cash_till_open']			=	1;
			}
			else
			{
				$_SESSION['cash_till_open']			=	0;
			}
			
		// is the cashtill closed for this date?			
			// set cash code
			$cash_code								=	'CLOSE_BEFORE_DEPOSIT';
			
			// test to see if open record exists for this date, if so then don't allow access to open cash till
			$count									=	$this->Cashtill->exists(date("Y"), date("m"), date("d"), $cash_code);
			if ($count	!=	0)
			{
				$_SESSION['cash_till_closed']		=	1;
			}
			else
			{
				$_SESSION['cash_till_closed']		=	0;
			}
			
		// is the cashtill closed for this date?			
			// set cash code
			$cash_code								=	'CLOSE_FINAL';
			
			// test to see if open record exists for this date, if so then don't allow access to open cash till
			$count									=	$this->Cashtill->exists(date("Y"), date("m"), date("d"), $cash_code);
			if ($count	!=	0)
			{
				$_SESSION['cash_till_final']		=	1;
			}
			else
			{
				$_SESSION['cash_till_final']		=	0;
			}
				
		// load the view
		$this->load->view('cashtills/manage');
	}

	function open()
	{
		// initialise
		$_SESSION['transaction_info']									=	new stdClass();
		
		// set dialog
		$_SESSION['show_dialog']										=1;

		//
		if(!isset($_SESSION['second_time']))
		{
			$_SESSION['second_time'] = 0;
		}

		// setup transaction data firstime through
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			// get currency definition
			$_SESSION['transaction_info']								=	$this->Currency_definition->get_all_cashtill()->result();
		
			// set quantities to zero by adding the column to the objectarray
			foreach ($_SESSION['transaction_info'] as $key => $row)
			{

				$row->quantity											=0;
				$row->total												=0;
				$_SESSION['transaction_info'][$key] 					=$row;

			}

			// set output
			$_SESSION['$title']											=	$this->lang->line('cashtills_open').'  '.date("d").'/'.date("m").'/'.date("Y");
			$_SESSION['submit']											=	$this->lang->line('common_submit');
			$_SESSION['total_caisse_open']								=	0;
			$_SESSION['confirm']										=	'N';
		}
		// setup transaction data second_time
		if ($_SESSION['second_time'] != 0)
			{
			$_SESSION['transaction_info']								=	$this->Currency_definition->get_all_cashtill()->result();
		
			// set quantities to zero by adding the column to the objectarray
			foreach ($_SESSION['transaction_info'] as $key => $row)
			{
		
				$row->quantity											= intval($_SESSION['transaction_info_save'][$key] );
				$row->total												= 0;
				$_SESSION['transaction_info'][$key] 					= $row;
		
			}
		}
		$_SESSION['cash_trans_today']									=	$this->Cashtill->get_all_lastday($_SESSION['cash_year'], $_SESSION['cash_month'], $_SESSION['cash_day'])->result();
		$_SESSION['second_time'] = 1;
		// redirect
		redirect("cashtills");	
	}
	
	function change()
	{
		// initialise
		$_SESSION['show_dialog']										=	6;
		$_SESSION['submit']												=	$this->lang->line('common_submit');
		$_SESSION['$title']												=	$this->lang->line('cashtills_change').'  '.date("d").'/'.date("m").'/'.date("Y");
		$_SESSION['total_caisse_change']								=	0;
		$_SESSION['confirm']											=	'N';
		$_SESSION['transaction_info']									=	new stdClass();
		
		//
		if(!isset($_SESSION['second_time']))
		{
			$_SESSION['second_time']=0;
		}

		// setup transaction data firstime through
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			// get currency definition
			$_SESSION['transaction_info']								=	$this->Currency_definition->get_all_cashtill()->result();
		
			// set quantities to zero by adding the column to the objectarray
			foreach ($_SESSION['transaction_info'] as $key => $row)
			{
				$row->quantity											=	0;
				$row->total												=	0;
				$_SESSION['transaction_info'][$key] 					= 	$row;
			}
			// setup transaction data second_time
		 if ($_SESSION['second_time'] != 0)
		 {
		$_SESSION['transaction_info']								=	$this->Currency_definition->get_all_cashtill()->result();
		
		    // set quantities to zero by adding the column to the objectarray
		    foreach ($_SESSION['transaction_info'] as $key => $row)
		    {
	
			$row->quantity											= intval ($_SESSION['transaction_info_save'][$key]) ;
			$row->total												= 0;
			$_SESSION['transaction_info'][$key] 					= $row;
	
		    }
		 }
	}
		$_SESSION['second_time'] = 1;
		// redirect
		redirect("cashtills");	
	}
	
	function bank()
	{		
		// initialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['show_dialog']										=	5;
		$_SESSION['$title']												=	$this->lang->line('cashtills_open').'  '.date("d").'/'.date("m").'/'.date("Y");
		$_SESSION['submit']												=	$this->lang->line('common_submit');
		$_SESSION['total_set_aside_year']								=	0;
		$_SESSION['total_set_aside_month']								=	0;
		$_SESSION['total_bank_year']									=	0;
		$_SESSION['total_bank_month']									=	0;
		$_SESSION['reference']											=	'';
		$_SESSION['deposit_amount']										=	0;
		$_SESSION['cash_year']											=	date("Y");
		$_SESSION['cash_month']											=	date("m");
		$_SESSION['cash_day']											=	date("d");	
		
		// get set_aside year to date
		$cash_code														=	'SET_ASIDE';
		$_SESSION['transaction_info']									=	$this->Cashtill->get_total_year(date("Y"), $cash_code)->result();
		foreach ($_SESSION['transaction_info']	as	$row)
		{
			$_SESSION['total_set_aside_year']							=	$_SESSION['total_set_aside_year']	+	$row->cash_amount;	
		}
		
		// get set_aside month to date
		$cash_code														=	'SET_ASIDE';
		$_SESSION['transaction_info']									=	$this->Cashtill->get_total_month(date("Y"), date("m"), $cash_code)->result();
		foreach ($_SESSION['transaction_info']	as	$row)
		{
			$_SESSION['total_set_aside_month']							=	$_SESSION['total_set_aside_month']	+	$row->cash_amount;	
		}
		
		// get bank year to date
		$cash_code														=	'BANK_DEPOSIT';
		$_SESSION['transaction_info']									=	$this->Cashtill->get_total_year(date("Y"), $cash_code)->result();
		foreach ($_SESSION['transaction_info']	as	$row)
		{
			$_SESSION['total_bank_year']								=	$_SESSION['total_bank_year']	+	$row->cash_amount;	
		}
		
		// get bank month to date
		$cash_code														=	'BANK_DEPOSIT';
		$_SESSION['transaction_info']									=	$this->Cashtill->get_total_month(date("Y"), date("m"), $cash_code)->result();
		foreach ($_SESSION['transaction_info']	as	$row)
		{
			$_SESSION['total_bank_month']								=	$_SESSION['total_bank_month']	+	$row->cash_amount;	
		}
		
		// calculate differences
		$_SESSION['balance_year']										=	$_SESSION['total_set_aside_year']	-	$_SESSION['total_bank_year'];
		$_SESSION['balance_month']										=	$_SESSION['total_set_aside_month']	-	$_SESSION['total_bank_month'];
		
		// redirect
		redirect("cashtills");	
	}
	
	function save_open()
	{		
		// initialse
		$_SESSION['total_caisse_open']									=	0;
		$_SESSION['open_error']											=	0;
		$first_use														=	0;
		
		// read through input quantity array and update transaction_info with quantity found
		foreach (($this->input->post("quantity") ?: []) as $key => $quantity)
		{
			$_SESSION['transaction_info'][$key]->quantity				=$quantity;
			$_SESSION['transaction_info_save'][$key]                   	= $quantity;
		}
				
		// validate input data
		$this															->	validate();
	
		// read through all denominations and calculate the denomination total, update array after, calculate total
		foreach ($_SESSION['transaction_info'] as $key => $row)
		{
			$row->total													=	$row->quantity * $row->multiplier;
			$_SESSION['transaction_info'][$key]							=	$row;
			
			$_SESSION['total_caisse_open']								=	$_SESSION['total_caisse_open']	+	$row->total;
		}
	
		// get last close. Might be either CLOSE_FINAL or CLOSE_BEFORE_DEPOSIT. 
		// test for CLOSE_FINAL first, if it doesn't exist test for CLOSE_BEFORE_DEPOSIT - this MUST exist. Except for first use.
		$cash_code														=	'CLOSE_FINAL';
		$_SESSION['cash_last_close_data']								=	$this->Cashtill->get_last_close($cash_code)->row();
		
		if (empty($_SESSION['cash_last_close_data']->cash_amount))
		{
			$cash_code													=	'CLOSE_BEFORE_DEPOSIT';
			$_SESSION['cash_last_close_data']							=	$this->Cashtill->get_last_close($cash_code)->row();
			
			// if not found = first use
			if (empty($_SESSION['cash_last_close_data']->cash_amount))
			{
				$first_use												=	1;
			}
		}
		
		// test open today = close latest - only if not first_use
		if ($first_use == 0)
		{
			$epsilon 													=	0.001;
			$diff														=	$_SESSION['total_caisse_open'] - $_SESSION['cash_last_close_data']->cash_amount;	

			if (abs($diff) > $epsilon)
			{
				// set message
				$_SESSION['error_code']									=	'03050';
				
				// sent email
				$_SESSION['open_error']									=	1;
				$this->send_email();
				
				// redirect
				redirect("cashtills");
			}
		}	

		// has user confirmed? If so write the data
		if ($this->input->post("confirm") == 'Y')
		{
			$this														->	write_open();
		}
		
		// set session parameters
		$_SESSION['first_time']											=	1;
		$_SESSION['submit']												=	$this->lang->line('common_confirm');
		
		// redirect
		redirect("cashtills");
	}
	
	function save_change()
	{		
		// initialse
		$_SESSION['total_caisse_change']								=	0;
		$_SESSION['change_error']										=	0;
		$_SESSION['correction'] 										=	0;
		$today															=	date("Y-m-d");
		
		// read through input quantity array and update transaction_info with quantity found
		foreach (($this->input->post("quantity") ?: []) as $key => $quantity)
		{
			$_SESSION['transaction_info'][$key]->quantity				=	$quantity;
			$_SESSION['transaction_info_save'][$key]                   	= $quantity;
		}
				
		// validate input data
		$this															->	validate();
		
		// read through all denominations and calculate the denomination total, update array after, calculate total
		foreach ($_SESSION['transaction_info'] as $key => $row)
		{
			$row->total													=	$row->quantity * $row->multiplier;
			$_SESSION['transaction_info'][$key]							=	$row;
			
			$_SESSION['total_caisse_change']							=	$_SESSION['total_caisse_change']	+	$row->total;
		}
		
		// get sales for today
		$_SESSION['cash_total_today'] 									=	$this->Sale->get_cash_sales_by_date(array('start_date'=>$today, 'end_date'=>$today))->row();

		// get cash till open for today
		$cash_code														=	'OPEN';
		$_SESSION['cash_transaction_open']								=	$this->Cashtill->get(date("Y"), date("m"), date("d"), $cash_code)->row();

		// total open + cash sales
		$cash_in_theory													=	$_SESSION['cash_transaction_open']->cash_amount + $_SESSION['cash_total_today']->cash_total_today;

		// test that cash open + cash sale = cash close
		$epsilon 													=	0.001;
		$diff														= $cash_in_theory - $_SESSION['total_caisse_change'];
		if (abs($diff) > $epsilon)
		{
			// set message
			$_SESSION['error_code']										=	'03000';
			redirect("cashtills");
		}
	
		// has user confirmed? If so write the data
		if ($this->input->post("confirm") == 'Y')
		{
			$this														->	write_change();
		}
		
		// set session parameters
		$_SESSION['first_time']											=	1;
		$_SESSION['submit']												=	$this->lang->line('common_confirm');
		
		// redirect
		redirect("cashtills");
	}
	
	function	write_open()
	{
		// save to DB
		$cash_till_data = array	(
								'cash_year'								=>	date("Y"),
								'cash_month'							=>	date("m"),
								'cash_day'								=>	date("d"),
								'cash_sequence'							=>	10,
								'cash_action'							=>	'=',
								'cash_code'								=>	'OPEN',
								'cash_transaction'						=>	$this->lang->line('cashtills_open'),
								'cash_bank_deposit_reference'			=>	NULL,
								'cash_amount'							=>	$_SESSION['total_caisse_open'],
								'person_id'								=>	$_SESSION['G']->login_employee_id,
								'branch_code'							=>	$this->config->item('branch_code')
								);
		$this															->	Cashtill->insert($cash_till_data);
		
		// set message
		$_SESSION['error_code']											=	'02070';
		
		// set sessions controls
		$_SESSION['first_time']											=	0;
		$_SESSION['show_dialog']										=	0;
		$_SESSION['submit']												=	$this->lang->line('common_confirm');
		
		// redirect
		redirect("cashtills");	
	}
	
		function	write_change()
	{
		// save to DB
		$cash_till_data = array	(
								'cash_year'								=>	date("Y"),
								'cash_month'							=>	date("m"),
								'cash_day'								=>	date("d"),
								'cash_sequence'							=>	15,
								'cash_action'							=>	'=',
								'cash_code'								=>	'CHANGE_USER',
								'cash_transaction'						=>	$this->lang->line('cashtills_change'),
								'cash_bank_deposit_reference'			=>	NULL,
								'cash_amount'							=>	$_SESSION['total_caisse_change'],
								'person_id'								=>	$_SESSION['G']->login_employee_id,
								'branch_code'							=>	$this->config->item('branch_code')
								);
		$this															->	Cashtill->insert($cash_till_data);
		
		// set message
		$_SESSION['error_code']											=	'04050';
		
		// set sessions controls
		$_SESSION['first_time']											=	0;
		$_SESSION['show_dialog']										=	0;
		$_SESSION['submit']												=	$this->lang->line('common_confirm');
		
		// redirect
		redirect("cashtills");	
	}
	
	function close()
	{
		// is the cashtill open for this date? If not then we can't close it.
		// initialise
		$cash_code														=	'OPEN';
		$count															=	$this->Cashtill->exists(date("Y"), date("m"), date("d"), $cash_code);
		if ($count	==	0)
		{
			// set message
			$_SESSION['error_code']										=	'03010';
			redirect("cashtills");
		}
		
		// initialise
		$_SESSION['transaction_info']									=	new stdClass();
		
		// set dialog
		$_SESSION['show_dialog']										=	2;
		
		if(!isset($_SESSION['second_time']))
		{
			$_SESSION['second_time']=0;
		}

		// setup transaction data firstime through
		if (($_SESSION['first_time'] ?? 0) != 1)
		{
			// get currency definition
			$_SESSION['transaction_info']								=	$this->Currency_definition->get_all_cashtill()->result();
		
			// set quantities to zero by adding the column to the objectarray
			foreach ($_SESSION['transaction_info'] as $key => $row)
			{
				$row->quantity											=	0;
				$row->total												=	0;
				$_SESSION['transaction_info'][$key] 					=	$row;
			}

			// set output
			$_SESSION['$title']											=	$this->lang->line('cashtills_close').'  '.date("d").'/'.date("m").'/'.date("Y");
			$_SESSION['submit']											=	$this->lang->line('common_submit');
			$_SESSION['total_caisse_close']								=	0;
			$_SESSION['confirm']										=	'N';
		}
		// setup transaction data second_time
		if ($_SESSION['second_time'] != 0)
			{
			$_SESSION['transaction_info']								=	$this->Currency_definition->get_all_cashtill()->result();
			
			// set quantities to zero by adding the column to the objectarray
			foreach ($_SESSION['transaction_info'] as $key => $row)
			{
		
				$row->quantity											= intval($_SESSION['transaction_info_save'][$key] );
				$row->total												= 0;
				$_SESSION['transaction_info'][$key] 					= $row;
		
			}
		}
		    $_SESSION['cash_year']										=	date("Y");
			$_SESSION['cash_month']										=	date("m");
			$_SESSION['cash_day']										=	date("d");	
		    $_SESSION['cash_trans_today']								=	$this->Cashtill->get_all_day($_SESSION['cash_year'], $_SESSION['cash_month'], $_SESSION['cash_day'])->result();
			$_SESSION['second_time'] = 1;		
		redirect("cashtills");	
	}
	
	function save_close()
	{		
		// initialise
		$_SESSION['total_caisse_close']									=	0;
		if ($_SESSION['correction'] != 1)
		{
			$_SESSION['correction_amount']								=	0;
		}
		
		// read through input quantity array and update transaction_info with quantity found
		foreach (($this->input->post("quantity") ?: []) as $key => $quantity)
		{
			$_SESSION['transaction_info'][$key]->quantity				=	$quantity;
			$_SESSION['transaction_info_save'][$key]                   	= $quantity;
		}
		
		// save correction_amount
		if ($_SESSION['correction'] == 1)
		{
			$_SESSION['correction_amount']								=	$this->input->post("correction_amount");
		}
				
		// validate input data
		$this															->	validate();
		
		// read through all denominations and calculate the denomination total, update array after, calculate total
		foreach ($_SESSION['transaction_info'] as $key => $row)
		{
			$row->total													=	$row->quantity * $row->multiplier;
			$_SESSION['transaction_info'][$key]							=	$row;
			
			$_SESSION['total_caisse_close']								=	$_SESSION['total_caisse_close']	+	$row->total;
		}
		
		// has user confirmed? If so write the data
		if ($this->input->post("confirm") == 'Y')
		{
			$this														->	write_close();
		}
		
		// set session parameters
		$_SESSION['first_time']											=	1;
		$_SESSION['submit']												=	$this->lang->line('common_confirm');
		
		// redirect
		redirect("cashtills");
	}
	
	function	write_close()
	{
		// initialise
		$today															=	date("Y-m-d");
		
		// delete any CASH record for today
		$cash_code														=	'CASH_SALES';
		$this->Cashtill													->	delete(date("Y"), date("m"), date("d"), $cash_code);
		
		// delete any CORR = correction record for today
		$cash_code														=	'CORRECTION';
		$this->Cashtill													->	delete(date("Y"), date("m"), date("d"), $cash_code);
		
		// get sales for today
		$_SESSION['cash_total_today'] 									=	$this->Sale->get_cash_sales_by_date(array('start_date'=>$today, 'end_date'=>$today))->row();

		// get cash till open for today
		$cash_code														=	'OPEN';
		$_SESSION['cash_transaction_open']								=	$this->Cashtill->get(date("Y"), date("m"), date("d"), $cash_code)->row();
		
		// total open + cash sales
		$cash_in_theory													=	$_SESSION['cash_transaction_open']->cash_amount + $_SESSION['cash_total_today']->cash_total_today;

		// test that cash open + cash sale = cash close
		// calculate difference and then test to epsilon. 
		// This is required because I am comparing two floats. See PHP manual - floating point numbers
		$epsilon 														=	0.00001;
		$diff															=	$cash_in_theory - ($_SESSION['total_caisse_close'] + $_SESSION['correction_amount']);
		
		// do the test		
		if(abs($diff) > $epsilon)
		{
			// set message
			$_SESSION['error_code']										=	'03000';
			// allow correction if allowed in configuration
			if ($this->config->item('cashtill_allow_correction') == 'Y')
			{
				$_SESSION['correction']									=	1;
			}
			redirect("cashtills");
		}
		
		// if here its OK, so write DB entries
		// save sales for today to DB
		$cash_till_data = array	(
								'cash_year'								=>	date("Y"),
								'cash_month'							=>	date("m"),
								'cash_day'								=>	date("d"),
								'cash_sequence'							=>	20,
								'cash_action'							=>	'+',
								'cash_code'								=>	'CASH_SALES',
								'cash_transaction'						=>	$this->lang->line('cashtills_cash_sale_today'),
								'cash_bank_deposit_reference'			=>	NULL,
								'cash_amount'							=>	$_SESSION['cash_total_today']->cash_total_today,
								'person_id'								=>	$_SESSION['G']->login_employee_id,
								'branch_code'							=>	$this->config->item('branch_code')
								);
		$this->Cashtill													->	insert($cash_till_data);
		
		// save correction amount to DB if allowed
		if ($_SESSION['correction'] == 1 AND $_SESSION['correction_amount'] != 0)
		{
			$_SESSION['correction_amount']								=	$this->input->post("correction_amount");
			$cash_till_data = array	(
								'cash_year'								=>	date("Y"),
								'cash_month'							=>	date("m"),
								'cash_day'								=>	date("d"),
								'cash_sequence'							=>	30,
								'cash_action'							=>	'-',
								'cash_code'								=>	'CORRECTION',
								'cash_transaction'						=>	$this->lang->line('cashtills_correction_amount'),
								'cash_bank_deposit_reference'			=>	NULL,
								'cash_amount'							=>	$_SESSION['correction_amount'],
								'person_id'								=>	$_SESSION['G']->login_employee_id,
								'branch_code'							=>	$this->config->item('branch_code')
								);
			$this->Cashtill												->	insert($cash_till_data);
			
			// send notification email to manager because a correction has been put through
			$this														->	send_email();
			
			// reset
			$_SESSION['correction']										=	0;
		}

		// save close amount to DB 
		$cash_till_data = array		(
									'cash_year'							=>	date("Y"),
									'cash_month'						=>	date("m"),
									'cash_day'							=>	date("d"),
									'cash_sequence'						=>	40,
									'cash_action'						=>	'=',
									'cash_code'							=>	'CLOSE_BEFORE_DEPOSIT',
									'cash_transaction'					=>	$this->lang->line('cashtills_close_before'),
									'cash_bank_deposit_reference'		=>	NULL,
									'cash_amount'						=>	$_SESSION['total_caisse_close'],
									'person_id'							=>	$_SESSION['G']->login_employee_id,
									'branch_code'						=>	$this->config->item('branch_code')
									);
									
		$this->Cashtill													->	insert($cash_till_data);
		
		// set message
		$_SESSION['error_code']											=	'02090';
		
		// set sessions controls
		$_SESSION['first_time']											=	0;
		$_SESSION['show_dialog']										=	0;
		$_SESSION['correction'] 										=	0;
		$_SESSION['submit']												=	$this->lang->line('common_confirm');
		
		// redirect to set_aside.
		// this is to ensure that a set-aside and a final close record are written whatever.
		redirect("cashtills/set_aside");
	}
	
	function	set_aside()
	{
		// is the cashtill closed before set aside for this date? If not then we can't close it.
		// initialise
		$cash_code														=	'CLOSE_BEFORE_DEPOSIT';
		$count															=	$this->Cashtill->exists(date("Y"), date("m"), date("d"), $cash_code);
		if ($count	==	0)
		{
			// set message
			$_SESSION['error_code']										=	'04040';
			redirect("cashtills");
		}
		
		// initialise
		$_SESSION['show_dialog']										=	3;
		$_SESSION['submit']												=	$this->lang->line('common_submit');
		$_SESSION['$title']												=	$this->lang->line('cashtills_set_aside').'  '.date("d").'/'.date("m").'/'.date("Y");
		$_SESSION['set_aside_amount']									=	0;
		
		// get today's details
		$_SESSION['cash_trans_today']									=	$this->Cashtill->get_all_today(date("Y"), date("m"), date("d"))->result();	

		// redirect
		redirect("cashtills");
	}
	
	function	status()
	{
		// initialise
		$_SESSION['show_dialog']									=	4;
		$_SESSION['submit']												=	$this->lang->line('common_submit');
		
		// set date
		if ($_SESSION['first_time'] == 1)
		{

            // explode and load date
			$pieces 														=	explode("/", $this->input->post('date'));
            $_SESSION['cash_day']											=	$pieces[0];
            $_SESSION['cash_month']											=	$pieces[1];
            $_SESSION['cash_year']											=	$pieces[2];

			/*$_SESSION['cash_year']										=	$this->input->post("year");
			$_SESSION['cash_month']										=	$this->input->post("month");
			$_SESSION['cash_day']										=	$this->input->post("day");*/
		}
		else
		{
			$_SESSION['cash_year']										=	date("Y");
			$_SESSION['cash_month']										=	date("m");
			$_SESSION['cash_day']										=	date("d");	
		}
		
		// set title
		$_SESSION['$title']												=	$this->lang->line('cashtills_status').' '.$_SESSION['cash_day'].'/'.$_SESSION['cash_month'].'/'.$_SESSION['cash_year'];
		
		// get today's details
		$_SESSION['cash_trans_today']									=	$this->Cashtill->get_all_today($_SESSION['cash_year'], $_SESSION['cash_month'], $_SESSION['cash_day'])->result();	
		
		// set user name
		foreach ($_SESSION['cash_trans_today'] as $key => $row)
		{
			// get user name
			$user_info													=	$this->Person->get_info($row->person_id);
			$_SESSION['cash_trans_today'][$key]->user_name				=	$user_info->last_name.', '.$user_info->first_name;	
		}
		
		
		// set first time
		$_SESSION['first_time']											=	1;
		
		// redirect
		redirect("cashtills");
	}
	function send_report_mail()
	{
		$mail_config = array	(
			'protocol'								=> 'smtp',
			'smtp_host' 							=> 'ssl://mail.sonrisa-smile.com',
			'smtp_port' 							=> '465',
			'smtp_user' 							=> $this->config->item('POemail'),
			'smtp_pass' 							=> $this->config->item('POemailpwd'),
			'mailtype'  							=> 'text',
			'starttls'  							=> TRUE,
			'wordwrap'								=> TRUE,
			'smtp_timeout'							=> 60,
			'newline'   							=> "\r\n"
			);

		$this->load->library('email', $mail_config);
		$this->load->library("../controllers/reports");
		
		$date_now = date("Y-m-d");
		$this->reports->summary_transations_ticket_z($date_now,$date_now,"sales","payment","0",0);
	
		$this->email	->	from($this->config->item('POemail'), $this->config->item('company')); 
		$this->email	->	to($this->config->item('cashtill_notification_email')); 
		//$this->email	->	to($this->config->item('cashtill_notification_email')); 
		//$this->email->cc($this->config->item('POemail'));  

		// set message for correction amount
           $_SESSION['cash_transaction_open']								=	$this->Cashtill->get(date("Y"), date("m"), date("d"), "OPEN")->result_array();
           $_SESSION['cash_transaction_close']								=	$this->Cashtill->get(date("Y"), date("m"), date("d"), "CLOSE_BEFORE_DEPOSIT")->result_array();
			
		$this->email	->	subject	(	
					date($this->config->item('dateformat').' G:i:s')
					." : " . $_SESSION['data']['title']
					);
					
		
		$msg_sent = $this->lang->line('employees_username')	." :\t".$_SESSION['G']->login_employee_info->last_name .' '.$_SESSION['G']->login_employee_info->first_name ."\r\n"
					.$this->lang->line('cashtills_open')	." :\t".$_SESSION['cash_transaction_open'][0]['timestamp']." : ".$_SESSION['cash_transaction_open'][0]['cash_amount'] ."\r\n"
					.$this->lang->line('cashtills_closed')	." :\t\t".$_SESSION['cash_transaction_close'][0]['timestamp']." : ".$_SESSION['cash_transaction_close'][0]['cash_amount'] ."\r\n" ;
		$msg_sent_line="\r\n";
		foreach ($_SESSION['data']['data'] as  $row)
			{	
				foreach (  $row as $valcol)
				{		
					$msg_sent_line = $msg_sent_line. $valcol ."\t\t\t" ;
				}
				$msg_sent_line = $msg_sent_line. "\r\n";
			}			
			$msg_sent = $msg_sent . $msg_sent_line. "\r\n"	
					.$this->lang->line('reports_total')	." : \t\t".round($_SESSION['data']['summary_data']['total'],2) . "\r\n"
					.$this->lang->line('reports_subtotal')	." : \t".round($_SESSION['data']['summary_data']['subtotal'],2) . "\r\n"
					.$this->lang->line('reports_tax')	." : \t\t\t".round($_SESSION['data']['summary_data']['tax'],2) . "\r\n"
					.$this->lang->line('reports_cost')	." : \t\t".round($_SESSION['data']['summary_data']['cost'],2) . "\r\n"
					.$this->lang->line('reports_profit')	." : \t\t".round($_SESSION['data']['summary_data']['profit'],2) . "\r\n"
					.$this->lang->line('reports_invoice_count')	." : \t".$_SESSION['data']['summary_data']['invoice_count'] . "\r\n"
					.$this->lang->line('reports_offered_count')	." : ".$_SESSION['data']['summary_data']['offered_count'] . "\r\n"
					.$this->lang->line('reports_average_basket')	." : \t".round($_SESSION['data']['summary_data']['average_basket'],2) . "\r\n"
					;
		
		$this->email	->	message	($msg_sent);
		
		

		$this->email	->	send();


	}
	function	save_set_aside()
	{
		// initialise
		$_SESSION['show_dialog']										=	3;
		$_SESSION['submit']												=	$this->lang->line('common_confirm');
		$_SESSION['set_aside_amount']									=	0;
		
		//delete any SET_ASIDE record for today
		//$cash_code														=	'SET_ASIDE';
		//$this->Cashtill													->	delete(date("Y"), date("m"), date("d"), $cash_code);
		
		// get set aside amount
		$_SESSION['set_aside_amount']									=	$this->input->post("set_aside_amount");
		
		// validate
		$this															->	validate_set_aside();
		
		// save transaction
		$cash_till_data = array	(
								'cash_year'								=>	date("Y"),
								'cash_month'							=>	date("m"),
								'cash_day'								=>	date("d"),
								'cash_sequence'							=>	50,
								'cash_action'							=>	'-',
								'cash_code'								=>	'SET_ASIDE',
								'cash_transaction'						=>	$this->lang->line('cashtills_set_aside'),
								'cash_bank_deposit_reference'			=>	NULL,
								'cash_amount'							=>	$_SESSION['set_aside_amount'],
								'person_id'								=>	$_SESSION['G']->login_employee_id,
								'branch_code'							=>	$this->config->item('branch_code')
								);
								
		$this->Cashtill													->	insert($cash_till_data);
		

        //Récupération de la valeur de versement
        $input['cash_year'] = $cash_till_data['cash_year'];
        $input['cash_month'] = $cash_till_data['cash_month'];
        $input['cash_day'] = $cash_till_data['cash_day'];
        $input['cash_sequence'] = '50';
        
        $data_row = $this->Cashtill->get_cash_amount_sum($input);
        
        $row = $data_row[0];

	    //	calculate final close amount
	    //	get close before deposit amount
        $cash_code														=	'CLOSE_BEFORE_DEPOSIT';
        $_SESSION['cash_transaction_set_aside']							=	$this->Cashtill->get(date("Y"), date("m"), date("d"), $cash_code)->row();
        
        // final close amount	
        $final_close_amount												=	$_SESSION['cash_transaction_set_aside']->cash_amount	-	$row['total'];
        
        //si la somme des versements est superieur à la Fermeture Caisse avant versement
        if(	$row['total'] >  $_SESSION['cash_transaction_set_aside']->cash_amount)
        {
        	$_SESSION['error_code']											=	'07280';
	    
	        $result = $this->Cashtill->delete_line($input);
	        redirect("cashtills");
	    
        }	
        $cash_code														=	'CLOSE_FINAL';
	    $this->Cashtill													->	delete(date("Y"), date("m"), date("d"), $cash_code);	 
        // save final cash till amount for today 		
/*
//Récupération de la valeur de versement
$redirect = 'cashtills';
$conn_parms														=	array();
//initialisation des parametres pour la connexion
$conn_parms														=	$this->Common_routines->get_conn_parms($redirect);
$conn															=	$this->Common_routines->open_db($conn_parms);

//Requête SQL
$sql =	"SELECT SUM(cash_amount) as total FROM `ospos_cash_till` WHERE `cash_year`='".$cash_till_data['cash_year']."' AND `cash_month`='".$cash_till_data['cash_month']."' AND `cash_day`='".$cash_till_data['cash_day']."' AND `cash_sequence`='50' ORDER BY `timestamp` ASC";		
$result	=	$conn->query($sql);
 
//récuperation de la ligne
$row = mysqli_fetch_assoc($result);

   

	//	calculate final close amount
	//	get close before deposit amount
$cash_code														=	'CLOSE_BEFORE_DEPOSIT';
$_SESSION['cash_transaction_set_aside']							=	$this->Cashtill->get(date("Y"), date("m"), date("d"), $cash_code)->row();

// final close amount	
$final_close_amount												=	$_SESSION['cash_transaction_set_aside']->cash_amount	-	$row['total'];

//si la somme des versements est superieur à la Fermeture Caisse avant versement
if(	$row['total'] >  $_SESSION['cash_transaction_set_aside']->cash_amount)
{
	$_SESSION['error_code']											=	'07280';
	
	$sql = "DELETE FROM `ospos_cash_till` WHERE `cash_year`='".$cash_till_data['cash_year']."' AND `cash_month`='".$cash_till_data['cash_month']."' AND `cash_day`='".$cash_till_data['cash_day']."' AND `cash_sequence`='50' ORDER BY `timestamp` DESC LIMIT 1";	
	$result	=	$conn->query($sql);

	redirect("cashtills");
	
}
	
$cash_code														=	'CLOSE_FINAL';
	$this->Cashtill													->	delete(date("Y"), date("m"), date("d"), $cash_code);	 
// save final cash till amount for today 
//*/
		$cash_till_data = array	(
								'cash_year'								=>	date("Y"),
								'cash_month'							=>	date("m"),
								'cash_day'								=>	date("d"),
								'cash_sequence'							=>	60,
								'cash_action'							=>	'=',
								'cash_code'								=>	'CLOSE_FINAL',
								'cash_transaction'						=>	$this->lang->line('cashtills_close_final'),
								'cash_bank_deposit_reference'			=>	NULL,
								'cash_amount'							=>	$final_close_amount,
								'person_id'								=>	$_SESSION['G']->login_employee_id,
								'branch_code'							=>	$this->config->item('branch_code')
								);
								
		$this->Cashtill													->	insert($cash_till_data);

		// set message
		$_SESSION['error_code']											=	'03040';
	
		// set sessions controls
		$_SESSION['first_time']											=	0;
		$_SESSION['show_dialog']										=	0;
		$_SESSION['set_aside_amount'] 									=	0;
		
		$_SESSION['submit']												=	$this->lang->line('common_submit');
		// redirect

		if ( $this->config->item('POemail')!= "" && $this->config->item('POemail')!= "Mettre-a-jour@sonrisa-smile.com" )
		{
			$this->send_report_mail();
		}
		$logout															=	'yes';
        if ($_SESSION['hostname'] == "") {
			require_once "/var/www/html/wrightetmathon/application/views/items/controllers/home.php";
			$HP = new Home();
            $HP->backup($logout);
        }
		// redirect("cashtills");
		return;
	}
	
	
	
	function	save_bank()
	{
		// initialise
		$_SESSION['show_dialog']										=	5;

		// get inputs
		$_SESSION['cash_year']											=	$this->input->post("year");
		$_SESSION['cash_month']											=	$this->input->post("month");
		$_SESSION['cash_day']											=	$this->input->post("day");
		$_SESSION['reference']											=	$this->input->post("reference");
		$_SESSION['deposit_amount']										=	$this->input->post("deposit_amount");
		
		//	validate
		$this->validate_bank();
		
		// save transaction
		$cash_till_data = array	(
								'cash_year'								=>	$_SESSION['cash_year'],
								'cash_month'							=>	$_SESSION['cash_month'],
								'cash_day'								=>	$_SESSION['cash_day'],
								'cash_sequence'							=>	70,
								'cash_action'							=>	'=',
								'cash_code'								=>	'BANK_DEPOSIT',
								'cash_transaction'						=>	$this->lang->line('cashtills_bank_deposit'),
								'cash_bank_deposit_reference'			=>	$_SESSION['reference'],
								'cash_amount'							=>	$_SESSION['deposit_amount'],
								'person_id'								=>	$_SESSION['G']->login_employee_id,
								'branch_code'							=>	$this->config->item('branch_code')
								);
		$this->Cashtill->insert($cash_till_data);

		// set message
		$_SESSION['error_code']											=	'04020';
		
		// set sessions controls
		$_SESSION['show_dialog']										=	0;
		$_SESSION['deposit_amount'] 									=	0;
		
		// redirect
		redirect("cashtills");
	}

	function	validate()
	{
		// test if all entries are numeric
		foreach ($_SESSION['transaction_info'] as $key => $row)
		{
			if (!is_numeric($row->quantity))
			{
				// set message
				$_SESSION['error_code']									=	'02080';
				redirect("cashtills");
			}
		}
		
		if ($_SESSION['correction'] == 1)
		{
			if (!is_numeric($_SESSION['correction_amount']))
			{
				// set message
				$_SESSION['error_code']									=	'03020';
				redirect("cashtills");
			}
		}
	}
	
	function	validate_set_aside()
	{
		if (!is_numeric($_SESSION['set_aside_amount']))
		{
			// set message
			$_SESSION['error_code']										=	'03030';
			redirect("cashtills");
		}
	}
	
	function	validate_bank()
	{		
		// test if year is valid
		if (!is_numeric($_SESSION['cash_year']) OR $_SESSION['cash_year'] < date("Y"))
		{
			// set message
			$_SESSION['error_code']										=	'03060';
			redirect("cashtills");
		}
		
		// test if month is valid
		if (!is_numeric($_SESSION['cash_month']) OR $_SESSION['cash_month'] < 1 OR  $_SESSION['cash_month'] > 12)
		{
			// set message
			$_SESSION['error_code']										=	'03070';
			redirect("cashtills");
		}
		
		// test if day is valid
		if (!is_numeric($_SESSION['cash_day']) OR $_SESSION['cash_day'] < 1 OR  $_SESSION['cash_day'] > 31)
		{
			// set message
			$_SESSION['error_code']										=	'03080';
			redirect("cashtills");
		}
		
		// test reference is not blank
		if (empty($_SESSION['reference']))
		{
			// set message
			$_SESSION['error_code']										=	'03090';
			redirect("cashtills");
		}
		
		// test reference is unique
		// set cash code
		$cash_code														=	'BANK_DEPOSIT';
		$count															=	$this->Cashtill->reference_exists($_SESSION['reference'], $cash_code);
		if ($count	!=	0)
		{
			// set message
			$_SESSION['error_code']										=	'04010';
			redirect("cashtills");
		}
		
		// test amount numeric
		if (!is_numeric($_SESSION['deposit_amount']))
		{
			// set message
			$_SESSION['error_code']										=	'04000';
			redirect("cashtills");
		}
		
		// test amountzero
		if ($_SESSION['deposit_amount'] == 0)
		{
			// set message
			$_SESSION['error_code']										=	'04030';
			redirect("cashtills");
		}
	}
	
	function	send_email()
	{
		$mail_config = array	(
								'protocol'								=> 'smtp',
								'smtp_host' 							=> 'ssl://mail.yesstore.fr',
								'smtp_port' 							=> '465',
								'smtp_user' 							=> $this->config->item('POemail'),
								'smtp_pass' 							=> $this->config->item('POemailpwd'),
								'mailtype'  							=> 'text',
								'starttls'  							=> TRUE,
								'wordwrap'								=> TRUE,
								'smtp_timeout'							=> 60,
								'newline'   							=> "\r\n"
								);
								
		$this->load														->	library('email', $mail_config);

		$this->email													->	from($this->config->item('POemail'), $this->config->item('company')); 
		$this->email													->	to($this->config->item('cashtill_notification_email')); 
		//$this->email->cc($this->config->item('POemail'));  

		// set message for correction amount
		if ($_SESSION['correction']	==	1)
		{
			$this->email												->	subject	(	
																					date('Y/m/d')
								." - "
																					.$this->config->item('branch_code')
								." - "
																					.$this->lang->line('cashtills_basic_information')
								." - "
																					.$this->lang->line('cashtills_correction_amount')
																					);
								
			$this->email												->	message	(	
																					$this->lang->line('branches_branch_code')
								.":\t"
																					.$this->config->item('branch_code')
																					."\r\n"
																					.$this->lang->line('employees_username')
								.":\t "
																					.$_SESSION['G']->login_employee_info->username
																					.', '
																					.$_SESSION['G']->login_employee_info->last_name
																					.', '
																					.$_SESSION['G']->login_employee_info->first_name
																					."\r\n"
																					.$this->lang->line('cashtills_correction_amount')
																					." => "
																					.$_SESSION['correction_amount']
																					);
		}
		
		// set message for open error amount
		if ($_SESSION['open_error']	==	1)
		{
			$this->email												->	subject	(
																					date('Y/m/d')
																					." => "
																					.$this->config->item('branch_code')
																					." => "
																					.$this->lang->line('cashtills_basic_information')
																					." => "
																					.$this->lang->line('cashtills_open_error')
																					);
								
			$this->email												->	message	(
																					$this->lang->line('branches_branch_code')
																					." => "
																					.$this->config->item('branch_code')
																					."\r\n"
																					.$this->lang->line('employees_username')
																					." => "
																					.$_SESSION['G']->login_employee_info->username
																					.', '
																					.$_SESSION['G']->login_employee_info->last_name
																					.', '
																					.$_SESSION['G']->login_employee_info->first_name
																					."\r\n"
																					.$this->lang->line('cashtills_open_error')
																					." => "
																					.$this->lang->line('cashtills_open_error_detail')
																					);
		}				

		$this->email													->	send();
	}
}
?>
