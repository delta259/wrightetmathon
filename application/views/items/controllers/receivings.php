<?php
class Receivings extends CI_Controller
{
	function index()
	{
		$this->load->library('receiving_lib');
		
		// set session data
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['line']);
		unset($_SESSION['confirm_what']);
		
		// set to show initial stock action selection only if stock action not already selected
		if (!isset($_SESSION['stock_action_id']))
		{
			$_SESSION['show_dialog']									=	1;
			$_SESSION['title']											=	$this->lang->line('receivings_stock_actions');
		}
		
		// show stock action selection dialog
		$this															->	reload();
		//$this->load->view("receivings/receiving", $data);
	}

	function item_search()
	{
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		$suggestions = array_merge($suggestions, $this->Item_kit->get_item_kit_search_suggestions($this->input->post('q'),$this->input->post('limit')));
		echo implode("\n",$suggestions);
	}

	function supplier_search()
	{
		$suggestions = $this->Supplier->get_suppliers_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function select_supplier()
	{
		$supplier_id = $this->input->post("supplier_id");
		$this->receiving_lib->set_supplier($supplier_id);
		$this->_reload();
	}

	function change_mode()
	{
		$mode = $this->input->post("mode");
		$this->receiving_lib->set_mode($mode);
		$this->_reload();
	}

	function add()
	{
		// initialise
		$data															=	array();
		
		// get the mode
		$mode 															=	$this->receiving_lib->get_mode();
		
		// If PO, check supplier entered - supplier must be selected before any items can be added 
		if ($mode == 'purchaseorder' AND $this->session->userdata('supplier') == -1)
		{
			// set message
			$_SESSION['error_code']										=	'05260';
			$this->_reload();
			return;
		}
			
		// set the user entry
		$item_id_or_number_or_item_kit_or_receipt 						=	$this->input->post("item");
			
		// set comments to blank
		//$this->session->set_userdata('comment', NULL);
		
		// set entire_receipt flag - used to control order in which the cart lines are displayed
		$data['entire_receipt']		=	'N';
		
		// call appropriate add routine	
		//if($this->receiving_lib->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt) && $mode == 'return')
		if($this->receiving_lib->is_valid_receipt($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->receiving_lib->return_entire_receiving($item_id_or_number_or_item_kit_or_receipt);
			$data['entire_receipt']	=	'Y';
		}
		elseif($this->receiving_lib->is_valid_item_kit($item_id_or_number_or_item_kit_or_receipt))
		{
			$this->receiving_lib->add_item_kit($item_id_or_number_or_item_kit_or_receipt);
		}
		else
		{			
			// get item info 
			$item_info													=	$this->Item->get_info($item_id_or_number_or_item_kit_or_receipt);
			if (empty($item_info->item_id))
			{
				$_SESSION['error_code']									=	'05280';
				$this->_reload();
				return;
			}
			
			// get supplier code for this receiving
			$supplier_id												=	$this->receiving_lib->get_supplier();
			
			// get item_supplier info
			$item_supplier_info											=	$this->Item->item_supplier_get($item_id_or_number_or_item_kit_or_receipt, $supplier_id);
			if ($item_supplier_info == NULL)
			{
				$_SESSION['error_code']									=	'05270';
				$this->_reload();
				return;
			}	
		
			// set default quantity
			$quantity = 1;
			
			// set price
			$price														=	$item_supplier_info->supplier_cost_price;		
		
			// set qty to the reorder quantity if purchase order
			if ($mode == 'purchaseorder')
			{	
				// test if the item can be ordered
				if ($item_supplier_info->supplier_reorder_policy == 'N')
				{
					$_SESSION['error_code']								=	'05290';
					$this->_reload();
					return;
				}
			
				// get reorder quantity
				$quantity 												=	$item_supplier_info->supplier_reorder_quantity;
				if ($quantity == 0)
				{
					$quantity = 1;
				}
			}
			
			// set qty to negative if return
			if ($mode == 'returns' OR $mode == 'stockreturns')
			{
				$quantity = $quantity * -1;
			}
			
			$this->receiving_lib->add_item($item_id_or_number_or_item_kit_or_receipt, $quantity, $price);
		}
		
		// show data
		$this->reload();		
	}

	function edit_item($line)
	{
		$_SESSION['line']	=	$line;	
		
		$data= array();

		$this->form_validation->set_rules('price', 'lang:items_price', 'required|numeric');
		$this->form_validation->set_rules('quantity', 'lang:items_quantity', 'required|numeric');
		$this->form_validation->set_rules('discount', 'lang:items_discount', 'required|integer');

    	$description = $this->input->post("description");
    	$serialnumber = $this->input->post("serialnumber");
		$price = $this->input->post("price");
		$quantity = $this->input->post("quantity");
		$discount = $this->input->post("discount");
		if ($this->form_validation->run() != FALSE)
		{
			$this->receiving_lib->edit_item($line,$description,$serialnumber,$quantity,$discount,$price);
		}
		else
		{
			$data['error']=$this->lang->line('recvs_error_editing_item');
		}

		$this->_reload($data);
	}

	function delete_item($item_number)
	{
		$this->receiving_lib->delete_item($item_number);
		$this->_reload();
	}

	function delete_supplier()
	{
		$this->receiving_lib->delete_supplier();
		$this->_reload();
	}

	function array_msort($array, $cols)
	{
		$colarr = array();
		foreach ($cols as $col => $order) {
			$colarr[$col] = array();
			foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
		}
		$eval = 'array_multisort(';
		foreach ($cols as $col => $order) {
			$eval .= '$colarr[\''.$col.'\'],'.$order.',';
		}
		$eval = substr($eval,0,-1).');';
		eval($eval);
		$ret = array();
		foreach ($colarr as $col => $arr) {
			foreach ($arr as $k => $v) {
				$k = substr($k,1);
				if (!isset($ret[$k])) $ret[$k] = $array[$k];
				$ret[$k][$col] = $array[$k][$col];
			}
		}
		return $ret;
	}

	function complete()
	{
		// set session
		unset($_SESSION['new']);
		
		// check supplier entered
		if ($this->session->userdata('supplier') == -1)
		{
			// set message
			$_SESSION['error_code']										=	'05260';
			$this->_reload();
			return;
		}
		
		// get the transaction info
		$transaction_mode 												= 	$this->receiving_lib->get_mode();
		$transaction_code 												= 	$this->Transaction->get_transaction_code($transaction_mode);
		
		// load title
		switch ($transaction_mode)
		{
			// purchase order
			case	"purchaseorder":
				$_SESSION['title']										=	$this->lang->line('receivings_stock_create');
				break;
				
			// Receive
			case	"receive":
				$_SESSION['title']										=	$this->lang->line('receivings_stock_receive');
				break;
				
			// adhoc 
			case	"stockadhoc":	
				// title
				$_SESSION['title']										=	$this->lang->line('receivings_stock_adhoc');
				break;
			
			// suspended
			case	"suspended":
				// title
				$_SESSION['title']										=	$this->lang->line('receivings_suspended');
				break;
			
			// suspended
			case	"suspendedreception":
				// title
				$_SESSION['title']										=	$this->lang->line('receivings_suspended_reception');
				break;
		}
						
		// load cart contents from userdata cartRecv
		$arr1															=	array();
		$arr1															=	$this->receiving_lib->get_cart();
				
		// sort the array in category/name alpha order
		$data['cart'] 													= 	$this->array_msort($arr1, array('category'=>SORT_ASC, 'name'=>SORT_ASC));

		// then load rest of data			
		$data['total']													=	$this->receiving_lib->get_total();
		$data['transaction_title']										=	$_SESSION['title'];
		$data['transaction_time']										= 	date('d/m/Y H:i:s');
		$comment 														= 	$this->receiving_lib->get_comment();
		$data['comment'] 												= 	$comment;
		$data['payment_type']											=	NULL;					// payment type is a field in the header
		$data['transaction_subtype']									= 	$transaction_mode;
		
		// get and format supplier name
		$supplier_id													=	$this->receiving_lib->get_supplier();
		if($supplier_id != -1)
		{
			$person_info 												= 	$this->Supplier->get_info($supplier_id);
			$data['supplier'] 											= 	strtoupper($person_info->company_name).' - '.$this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
			$supplier_poemail											= 	$person_info->email;
		}
		
		// test supplier email blank, if so send error message
		if (empty($supplier_poemail))
		{
			$data['error_message']										=	$this->lang->line('receivings_supplier_email_invalid');
		}
		
		// get and format employee name
		$employee_id													=	$this->Employee->get_logged_in_employee_info()->person_id;
		$person_info													=	$this->Employee->get_info($employee_id);
		$data['employee'] 												= 	$this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);

		//SAVE receiving to database
		$data['receiving_id']											=	$transaction_code.$this->Receiving->save($data['cart'], $supplier_id, $employee_id, $comment, $payment_type);
		
		if ($data['receiving_id'] == ($transaction_code.'-1'))
		{
			$data['error_message'] 										=	$this->lang->line('receivings_transaction_failed');
		}
		
		// generate barcode
		$data['image_path']												=	$this->Common_routines->generate_barcode($data['receiving_id']);
		
		// If this is a PO, 
		// 1) create a PDF of the PO
		// 2) create a CSV of the PO
		// 3) send these docs by email to supplier.
		
		if ($data['transaction_subtype'] == 'purchaseorder')
		{
			// Create the PDF...
			$filename_pdf 												=	$this->config->item('branch_code')."_".$data['receiving_id'].".pdf";
			$pdf_data_file												=	$this->config->item('POsavepath').$filename_pdf;
			
			// create the html view
			$html 														=	$this->load->view('receivings/receipt', $data, true);
			
			// load the PDF (mPDF) library
			$this														->	load->library('pdf');
			$pdf														=	$this->pdf->load();
			 
			$pdf														->	SetFooter('SARL Wright et Mathon'.'|{PAGENO}|'.$data['transaction_time']);
			$pdf														->	WriteHTML($html);
			$pdf														->	Output($pdf_data_file, 'F');
	
			// Create the CSV...
			$filename_csv 												=	$this->config->item('branch_code')."_".$data['receiving_id'].".txt";
			$csv_data_file												=	$this->config->item('POsavepath').$filename_csv;
			
			// read the cart and load output file
			foreach($data['cart'] as $line=>$item)
			{
				$csv_quantity 											=	number_format($item['quantity'],0);
				file_put_contents($csv_data_file,						"V7\t",						FILE_APPEND);
				file_put_contents($csv_data_file,						"LIG\t", 					FILE_APPEND);
				file_put_contents($csv_data_file,						$item['item_number'],		FILE_APPEND);
				file_put_contents($csv_data_file,						"\t",						FILE_APPEND);
				file_put_contents($csv_data_file,						"\t\t\t\t",					FILE_APPEND);
				file_put_contents($csv_data_file,						$csv_quantity,				FILE_APPEND);
				file_put_contents($csv_data_file,						"\t",						FILE_APPEND);
				file_put_contents($csv_data_file,						"\t\t\t\t\t\t\t\t\t\t\t",	FILE_APPEND);
				file_put_contents($csv_data_file,						"O\t",						FILE_APPEND);
				file_put_contents($csv_data_file,						"\r\n",						FILE_APPEND);
			}
			
			// Send to supplier
			$mail_config =	array	(
									'protocol'							=>	'smtp',
									'smtp_host' 						=>	'ssl://ns10.monarobase.net',
									'smtp_port' 						=>	'465',
									'smtp_user' 						=> 	$this->config->item('POemail'),
									'smtp_pass' 						=> 	$this->config->item('POemailpwd'),
									//'smtp_user' 						=>	'envoie-commande@sonrisa-smile.com',
									//'smtp_pass' 						=>	'J~?mbk+HhJ)W',
									'mailtype'  						=>	'text',
									'starttls'  						=>	FALSE,
									'wordwrap'							=>	TRUE,
									'smtp_timeout'						=>	60,
									'newline'   						=>	"\r\n"
									);
									
			$this														->	load->library('email', $mail_config);

			$this->email												->	from($this->config->item('email'), $this->config->item('company')); 
			$this->email												->	to($supplier_poemail); 
			$this->email												->	cc($this->config->item('email'));  

			$this->email												->	subject($data['transaction_time']." - CY_".$data['receiving_id'].' - Commande Fournisseur');
			$this->email												->	message($comment."\r\n"."\r\n".$this->config->item('POemailmsg'));
			$this->email												->	attach($csv_data_file);
			$this->email												->	attach($pdf_data_file);	

			$this->email												->	send();

			//echo $this->email->print_debugger();
			//exit;
		}
		
		// load the view
		$this															->	receiving_lib->clear_all();
		$this															->	load->view("receivings/receipt",$data);
	}
	
	function reprint()
	{	
		// set values if first time through
		$values															=	array	(
																					'reprint_code	'				=>	'',
																					'success_or_failure'			=>	'',
																					'message'						=>	'',
																					);
		
		// set cookie data
		$this->session													->	set_userdata($values);

		// show transaction code data entry
		$this															->	load->view("receivings/reprint");
	}
	
	function reprint_check()
	{	
		// get data
		$values															=	array	();
		$values															=	array	(
																					'reprint_code'					=>	$this->input->post('reprint_code'),
																					'success_or_failure'			=>	'',
																					'message'						=>	''
																					);
		$this->session													->	set_userdata($values);
		
		// check code exists
		if(!$this->receiving_lib->is_valid_receipt($values['reprint_code']))
		{
			// set success indicator and message
			$values														=	array	(
																					'success_or_failure'=>	'F',
																					'message'			=>	$this->lang->line('recvs_invalid_transaction_code')
																					);
			$this->session												->	set_userdata($values);
			$this->load->view("receivings/reprint");
		}
		else
		{
			$pieces 													=	explode('-', $values['reprint_code']);
			$this														->	receipt($pieces[1]);
		}
	}
		
	function receipt($receiving_id)
	{	
		// get the transaction info
		$transaction_info 												= 	$this->Receiving->get_receiving_header($receiving_id)->row_array();

		// get the transaction code from the transaction mode
		$tcode 															= 	$this->Transaction->get_transaction_code($transaction_info['mode']);
		
		// load cart contents from userdata cartRecv
		$this															->	receiving_lib->copy_entire_receiving($receiving_id);
		$arr1															=	array();
		$arr1															=	$this->receiving_lib->get_cart();
						
		// sort the array in category/item_number alpha order
		$data['cart'] 													= 	$this->array_msort($arr1, array('category'=>SORT_ASC, 'item_number'=>SORT_ASC));
		$data['total']													=	$this->receiving_lib->get_total();
		$data['transaction_title']										=	$_SESSION['title'];
		$data['transaction_time']										= 	date('d/m/Y H:i:s', strtotime($transaction_info['receiving_time'])); // Wright modified 04/02/2014
		$data['payment_type']											=	NULL;	
		$data['comment'] 												= 	$transaction_info['comment'];

		// get and format supplier name
		$supplier_id													=	$this->receiving_lib->get_supplier();
		if($supplier_id != -1)
		{
			$person_info 												= 	$this->Supplier->get_info($supplier_id);
			$data['supplier'] 											=	strtoupper($person_info->company_name).' - '.$this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);			
		}
		
		// get and format employee name
		$person_info													=	$this->Employee->get_info($transaction_info['employee_id']);
		$data['employee'] 												= 	$this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
		
		// transaction info
		$data['receiving_id']											=	$tcode.$receiving_id;
		$data['transaction_subtype']									= 	$transaction_info['mode'];
		
		// barcode
		$data['image_path']												=	$this->Common_routines->generate_barcode($data['receiving_id']);
		
		// clear all
		$this															->	receiving_lib->clear_all();
		
		// load view
		$this															->	load->view("receivings/receipt", $data);
	}

	
	function reload()
	{		
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		$this															->	_reload();
	}
	
	function _reload($data=array())
	{				
		// get the employee info
		$person_info													=	$this->Employee->get_logged_in_employee_info();
		
		// get the cart
		$data['cart']													=	$this->receiving_lib->get_cart();
					
		// setup the drop down for mode			
		$data['mode']													=	$this->receiving_lib->get_mode();
		$data['total']													=	$this->receiving_lib->get_total();
		$data['items_module_allowed']									=	$this->Employee->has_permission('items', $person_info->person_id);

		// get and format supplier name
		$supplier_id													=	$this->receiving_lib->get_supplier();
		if($supplier_id != -1)
		{
			$person_info												=	$this->Supplier->get_info($supplier_id);
			$data['supplier']											=	strtoupper($person_info->company_name).' - '.$this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);			
		}
		
		// set comments from user data
		$data['comment']												=	$this->session->userdata('comment');

		// show data entry
		$this->load->view('receivings/receiving', $data);
	}

    function cancel_receiving()
    {
    	// cancel the order -ie empty virtual shopping cart
    	$this->receiving_lib->clear_all();
		
		// show transaction selection
    	redirect("receivings");
    }
    
    function suspend_CMDE()
    {
    	// set mode
    	$this															->	receiving_lib->set_mode("suspended");
		// complete
		$this															->	complete();
    }
    
    function suspend_RCPT()
    {
    	// set mode
    	$this															->	receiving_lib->set_mode("suspendedreception");
		// complete
		$this															->	complete();
    }
    
    function edit($transaction_code)
	{
		$pieces = explode("-", $transaction_code);
		$code = $pieces[0];
		$transaction_id = $pieces[1];
		
		$data = array();

		// load suppliers
		$data['suppliers'] = array('' => $this->lang->line('sales_no_supplier'));
		foreach ($this->Supplier->get_all()->result() as $person_info)
		{
			$data['suppliers'][$person_info->person_id] = $this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
		}
		
		// load employees
		$data['employees'] = array();
		foreach ($this->Employee->get_all()->result() as $person_info)
		{
			$data['employees'][$person_info->person_id] = $this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
		}
		
		$data['transaction_info'] = $this->Receiving->get_info($transaction_id)->row_array();
		
		//$data['code'] = $code;
		$data['code'] = $transaction_code;				
		
		$this->load->view('receivings/edit', $data);
	}
	
	function save_trans($transaction_id)
	{		
		$transaction_data = array	(
									//'receiving_time' => date('Y-m-d', strtotime($this->input->post('date'))),
									'supplier_id'	=>	$this->input->post('supplier_id') ? $this->input->post('supplier_id') : null,
									'employee_id'	=>	$this->input->post('employee_id'),
									'comment'		=>	$this->input->post('comment'),
									'branch_code'	=>	$this->config->item('branch_code')
									);
		
		if ($this->Receiving->update($transaction_data, $transaction_id))
		{
			
			echo json_encode(array('success'=>true,'message'=>$this->lang->line('sales_successfully_updated')));
		}
		else
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('sales_unsuccessfully_updated')));
		}
	}
	
	function delete($transaction_id)
	{
		// delete it
		if ($this->Receiving->delete($transaction_id))
		{
			$data['success'] = true;
		}
		else
		{
			$data['success'] = false;
		}
		
		// clear the virtual shopping cart
		$this															->	receiving_lib->clear_all();
		
		// show selection screen
		$this															->	index();
	}
	
	function confirm($confirm_what)
	{
		$_SESSION['show_dialog']										=	2;
		$_SESSION['confirm_what']										=	$confirm_what;
		
		// set origin
		$_SESSION['origin']												=	'DR';
					
    	$this->_reload();
	}
	
	function set_comment() 
	{
		$this->receiving_lib->set_comment($this->input->post('comment'));
	}
	
	// show the stock actions dialog
	function stock_actions_1() 
	{
		unset($_SESSION['stock_action_id']);
		$this															->	index();
	}
	
	
	// test which stock action was selected and take appropriate measures!
	function stock_actions_2() 
	{
		// load the stock action id
		$_SESSION['stock_action_id']									=	$this->input->post('stock_action_id');
		
		// set title and initial defaults
		switch ($_SESSION['stock_action_id'])
		{
			// purchase order
			case	10:
				// set title
				$_SESSION['title']										=	$this->lang->line('receivings_stock_create');
				// set mode
				$this													->	receiving_lib->set_mode("purchaseorder");
				// set comments to blank
				$this->session->set_userdata('comment', NULL);
				// set supplier to default if not already set
				$supplier_id											=	$this->receiving_lib->get_supplier();
				if ($supplier_id == -1)
				{
					$this												->	receiving_lib->set_supplier($this->config->item('default_supplier_id'));
				}
				// reset show_dialog
				$_SESSION['show_dialog']								=	0;
				// reload
				$this													->	_reload();
				break;
				
			// Receive
			case	20:
				// title
				$_SESSION['title']										=	$this->lang->line('receivings_stock_receive');
				// set mode
				$this													->	receiving_lib->set_mode("receive");
				// set supplier to default if not already set
				$supplier_id											=	$this->receiving_lib->get_supplier();
				if ($supplier_id == -1)
				{
					$this												->	receiving_lib->set_supplier($this->config->item('default_supplier_id'));
				}
				// reset show_dialog
				$_SESSION['show_dialog']								=	0;
				// reload
				$this													->	_reload();
				break;
				
			// adhoc 
			case	30:	
				// title
				$_SESSION['title']										=	$this->lang->line('receivings_stock_adhoc');
				// set mode
				$this													->	receiving_lib->set_mode("stockadhoc");
				// set supplier to default if not already set
				$supplier_id											=	$this->receiving_lib->get_supplier();
				if ($supplier_id == -1)
				{
					$this												->	receiving_lib->set_supplier($this->config->item('default_supplier_id'));
				}
				// reset show_dialog
				$_SESSION['show_dialog']								=	0;
				// reload
				$this													->	_reload();
				break;
			
			// manage suspended
			case	40:
				// initialise
				unset($_SESSION['merge_from']);
				
				// title
				$_SESSION['title']										=	$this->lang->line('receivings_stock_suspended');
				
				// turn on modal dialog for suspended receivings
				$_SESSION['show_dialog']								=	3;
		
				// get the suspended sales
				$_SESSION['suspended_receives']							=	array();
				$_SESSION['suspended_receives']							=	$this->Receiving->get_all_by_mode('suspended')->result_array();
				
				// don't show suspended if there aren't any
				if (!$_SESSION['suspended_receives'])
				{
					// clear all and reset session parameters
					$this->receiving_lib->clear_all();
					// set error code
					$_SESSION['error_code']								=	'05690';
				}
				
				// show suspended orders
				$this													->	index();
				break;
				
			// create auto PO
			case	50:
				// title
				$_SESSION['title']										=	$this->lang->line('receivings_stock_reorder');
				// turn on modal dialog for suspended receivings
				$_SESSION['show_dialog']								=	6;
				// show selection screen
				$this													->	index();
				break;
				
			// manage suspended receptions
			case	60:
				// title
				$_SESSION['title']										=	$this->lang->line('receivings_stock_suspended_reception');
				
				// turn on modal dialog for suspended receivings
				$_SESSION['show_dialog']								=	4;
		
				// get the suspended sales
				$_SESSION['suspended_receives']							=	array();
				$_SESSION['suspended_receives']							=	$this->Receiving->get_all_by_mode('suspendedreception')->result_array();
				
				// don't show suspended if there aren't any
				if (!$_SESSION['suspended_receives'])
				{
					// clear all and reset session parameters
					$this->receiving_lib->clear_all();
					// set error code
					$_SESSION['error_code']								=	'05700';
				}
				
				// show suspended receptions
				$this													->	index();
				break;			
		}
	}
	
	function unsuspend($receiving_id)
	{
		// clear the virtual shopping cart
		$this->receiving_lib->clear_all();
		
		// set the stock action ID
		$_SESSION['stock_action_id']									=	10;
		
		// set mode
		$this															->	receiving_lib->set_mode("purchaseorder");
		
		// set title
		$_SESSION['title']												=	$this->lang->line('receivings_stock_create');
		
		// copy entire suspended receiving to shopping cart
		$this->receiving_lib->copy_entire_receiving($receiving_id);
		
		// set comments to blank
		$this->session->set_userdata('comment', NULL);
		
		// delete suspended ID from file.
		$this->Receiving->delete($receiving_id);
		
    	$this->_reload();
	}
	
	function unsuspend_reception($receiving_id)
	{
		// clear the virtual shopping cart
		$this->receiving_lib->clear_all();
		
		// set the stock action ID
		$_SESSION['stock_action_id']									=	20;
		
		// set mode
		$this															->	receiving_lib->set_mode("receive");
		
		// set title
		$_SESSION['title']												=	$this->lang->line('receivings_stock_receive');
		
		// copy entire suspended receiving to shopping cart
		$this->receiving_lib->copy_entire_receiving($receiving_id);
		
		// delete suspended ID from file.
		$this->Receiving->delete($receiving_id);
		
    	$this->_reload();
	}
	
	function merge_1($receiving_id)
	{				
		// save ID
		$_SESSION['merge_from']											=	$receiving_id;
		
		// title
		$_SESSION['title']												=	$this->lang->line('receivings_stock_select_merge');
		
		// turn on modal dialog for suspended receivings
		$_SESSION['show_dialog']										=	5;

		// get the suspended sales
		$_SESSION['suspended_receives']									=	array();
		$_SESSION['suspended_receives']									=	$this->Receiving->get_all_by_mode('suspended')->result_array();
		
		// don't show suspended if there aren't any
		if (!$_SESSION['suspended_receives'])
		{
			// clear all and reset session parameters
			$this->receiving_lib->clear_all();
			// set error code
			$_SESSION['error_code']										=	'05690';
		}
		
		// show suspended orders
		$this															->	index();
	}
	
	function merge_2($receiving_id)
	{
		// save ID to
		$_SESSION['merge_to']											=	$receiving_id;
		
		// clear the virtual shopping cart
		$this															->	receiving_lib->clear_all();
		
		// test merging to itself
		if ($_SESSION['merge_to'] == $_SESSION['merge_from'])
		{
			// set error code
			$_SESSION['error_code']										=	'05710';
			// redirect
			$this														->	index();
			return;
		}
		
		// set the stock action ID
		$_SESSION['stock_action_id']									=	10;
		
		// set mode
		$this															->	receiving_lib->set_mode("purchaseorder");
		
		// set title
		$_SESSION['title']												=	$this->lang->line('receivings_stock_create');
		
		// OK merge the suspended orders
		$this->receiving_lib->merge_receiving();
		
		// set comments to blank
		$this->session->set_userdata('comment', NULL);
		
		// delete suspended ID from file.
		$this->Receiving->delete($_SESSION['merge_to']);
		$this->Receiving->delete($_SESSION['merge_from']);
		
		// show cart
		$this->_reload();
	}
}	
?>
