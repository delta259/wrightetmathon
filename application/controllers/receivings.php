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
	}


  //Fonction pour bloquer la recherche 
	function filtre()
	{
		//si le filtre est inactif, alors il s'active et vis verca
		switch($_SESSION['filtre_receivings'])
		{
			case 1: 
				$_SESSION['filtre_receivings']=0;
			break;

			default:
				$_SESSION['filtre_receivings']=1;
			break;
		}
		redirect("receivings");
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
		$_SESSION['supplier_id'] = $supplier_id;
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
					$barcode                            = $this->Item->get_item_search_suggestions($item_id_or_number_or_item_kit_or_receipt );
					$brcd          = explode( "|" ,$barcode[0] );
	
					$item_info													=	$this->Item->get_info($brcd[0]);
					$item_id_or_number_or_item_kit_or_receipt = $brcd[0];
		
		
					if(empty($barcode[0]))
					{

				$_SESSION['error_code']									=	'05280';
				$this->_reload();
				return;
			}
		}
		
			// get supplier code for this receiving
			$supplier_id												=	$this->receiving_lib->get_supplier();
			
			// get item_supplier info
			$item_supplier_info											=	$this->Item->item_supplier_get($item_id_or_number_or_item_kit_or_receipt, $supplier_id);
			if ($item_supplier_info == NULL && $_SESSION['receiving_title'] != "Mouvement de stock divers")
			{
				$_SESSION['error_code']									=	'05270';
				$this->_reload();
				return;
			}
			elseif ($item_supplier_info == NULL && $_SESSION['receiving_title'] == "Mouvement de stock divers")
			{
				$item_supplier_info											=	$this->Item->item_supplier_get_preferred($item_id_or_number_or_item_kit_or_receipt);
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

			$somme_ventes = $this->Item->get_all_between_2_date_for_item($item_id_or_number_or_item_kit_or_receipt);
			$_SESSION['ventes_for_approv_qty'] = $somme_ventes[0]['somme'];

			$_SESSION['supplier_id']=$this->Item->get_supplier_id($item_id_or_number_or_item_kit_or_receipt);
			$_SESSION['supplier_id']=$supplier_id;
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

	function count_all_items($array)
	{
		$compteur_line_item = 0;
		$compteur_line_kit = 0;
		$compteur_kit_items_all = 0;
		$compteur_items_all = 0;
		$compte = 0;

		foreach($array as $line => $item)
		{
			$compte = 0;
			$data_item = $this->Item->get_info($item['item_id']);
			if($data_item->DynamicKit == 'Y')
			{
				$data_kit = $this->Item_kit->get_item_kit_items($item['item_id']);
				foreach($data_kit as $key => $kit_item)
				{
            $compte = $compte + intval($item['quantity']) * intval($kit_item['quantity']);
				}
				$compteur_line_kit += intval($item['quantity']);
			}
			if($data_item->DynamicKit == 'N')
			{
					$compte = intval($item['quantity']);
			    
			}
			$compteur_items_all = $compteur_items_all + $compte;
		}
		return $compteur_items_all;
	}

	function complete()
	{
		if($_SESSION['unsuspend_reception']['0'] = '1')
		{
				$_SESSION['unsuspend_reception']['0'] = '0';
				$this->Receiving->delete($_SESSION['unsuspend_reception']['id']);
		}
		if($_SESSION['attach_reception_boolean'][0]==1)
		{
			$_SESSION['attach_reception_boolean'][0]=0;
			$do_update = $this->Receiving->update_purchaseorder($_SESSION['attach_reception_boolean'][1]);
		}
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
		$data['cart'] 													= 	$this->array_msort($arr1, array('category'=>SORT_ASC, 'name'=>SORT_ASC));  //PDF bon de commande
		$data['count_all_items']                =   $this->count_all_items($data['cart']);
		
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
			//$html 														=	$this->load->view('receivings/receipt', $data, true);
			$html 														=	$this->load->view('receivings/receipt_pdf', $data, true);
			
			// load the PDF (mPDF) library
			$this														->	load->library('pdf');
			$pdf														=	$this->pdf->load();
			 
			//$pdf														->	SetFooter('SARL Wright et Mathon'.'|{PAGENO}|'.$data['transaction_time']);
			$pdf														->	SetFooter('Groupe Yes Store'.'|{PAGENO}|'.$data['transaction_time']);
			$pdf														->	WriteHTML($html);
			$pdf														->	Output($pdf_data_file, 'F');
	
			// Create the CSV...
			$filename_csv 												=	$this->config->item('branch_code')."_".$data['receiving_id'].".txt";
			$csv_data_file												=	$this->config->item('POsavepath').$filename_csv;
			
			// read the cart and load output file
			foreach($data['cart'] as $line=>$item)
			{
				$csv_quantity 											=	number_format($item['quantity'],0);
				$csv_price 											=	number_format(floatval($item['price']),2,',','');
				
				if ( (strpos($item['item_number'], "SO") === 0 || strpos($item['item_number'], "PK") === 0))
				{	$csv_item											= $item['item_number'];
					$csv_bool_price										="O";
				} 
				else
				{	
					$csv_item											= 'SO009998';
					$csv_bool_price										="N";
				}			
				file_put_contents($csv_data_file,						"V7\t",						FILE_APPEND);
				file_put_contents($csv_data_file,						"LIG\t", 					FILE_APPEND);
				file_put_contents($csv_data_file,						$csv_item,		FILE_APPEND);
				file_put_contents($csv_data_file,						"\t\t\t\t",					FILE_APPEND);
				file_put_contents($csv_data_file,						$item['name'],				FILE_APPEND);
				file_put_contents($csv_data_file,						"\t",						FILE_APPEND);
				file_put_contents($csv_data_file,						$csv_quantity,				FILE_APPEND);
				file_put_contents($csv_data_file,						"\t\t",						FILE_APPEND);
				file_put_contents($csv_data_file,						$csv_price,					FILE_APPEND);
				file_put_contents($csv_data_file,						"\t",						FILE_APPEND);
				file_put_contents($csv_data_file,						$csv_price,					FILE_APPEND);
				file_put_contents($csv_data_file,						"\t\t\t\t\t\t\t\t\t",		FILE_APPEND);
				file_put_contents($csv_data_file,						$csv_bool_price."\t",		FILE_APPEND);
				file_put_contents($csv_data_file,						"\r\n",						FILE_APPEND);
			}
			
			// Send to supplier
			//									'smtp_host' 						=>	'ssl://ns10.monarobase.net',
			$mail_config =	array	(
									'protocol'							=>	'smtp',
									'smtp_host' 						=>	'ssl://mail.sonrisa-smile.com',
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
		$data['count_all_items'] = $this->count_all_items($data['cart']);
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
		$data['cart']													=	$this->receiving_lib->get_cart();    //récupére les informations sur les articles à approvisionner
		$data['count_all_items'] = $this->count_all_items($data['cart']);

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
		
	    unset($_SESSION['nbre_jour_prevision_stock_correct']);
	    unset($_SESSION['historique_correct']);
				
		// show transaction selection
    	redirect("receivings");
    }
    
    function suspend_CMDE()
    {
    	// set mode
			$this															->	receiving_lib->set_mode("suspended");

			unset($_SESSION['nbre_jour_prevision_stock_correct']);
	    unset($_SESSION['historique_correct']);
		
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
		redirect('receivings');
	}
	
	
	// Process stock action selection and redirect
	function stock_actions_2()
	{
		$_SESSION['stock_action_id'] = $this->input->post('stock_action_id');
		$_SESSION['stock_action_id_stock_choix_liste'] = $_SESSION['stock_action_id'];

		// Ensure attach transaction type exists
		$input['transaction_ID'] = '9';
		$data_transaction_type = $this->Receiving->get_info_transaction_type($input);
		if (!isset($data_transaction_type[0]['transaction_ID'])) {
			$this->db->insert('transaction_type', array(
				'transaction_type'        => 'SM',
				'transaction_subtype'     => 'attach',
				'transaction_code'        => 'ATCH-',
				'transaction_updatestock' => 'Y',
			));
		}

		// Helper: set default supplier if none selected
		$this->_ensure_default_supplier();

		switch ($_SESSION['stock_action_id'])
		{
			// Purchase Order
			case 10:
				$_SESSION['title']        = $this->lang->line('receivings_stock_create');
				$_SESSION['show_dialog']  = 0;
				$this->receiving_lib->set_mode('purchaseorder');
				$this->session->set_userdata('comment', NULL);
				redirect('receivings');
				break;

			// Receive (attach to existing PO)
			case 20:
				$_SESSION['title']            = $this->lang->line('receivings_stock_receive');
				$_SESSION['show_dialog']      = 7;
				$_SESSION['attach_receives']  = $this->Receiving->get_all_by_mode('purchaseorder')->result_array();
				$this->receiving_lib->set_mode('receive');
				redirect('receivings');
				break;

			// Adhoc stock movement
			case 30:
				$_SESSION['title']        = $this->lang->line('receivings_stock_adhoc');
				$_SESSION['show_dialog']  = 0;
				$this->receiving_lib->set_mode('stockadhoc');
				redirect('receivings');
				break;

			// Manage suspended POs
			case 40:
				unset($_SESSION['merge_from']);
				$_SESSION['title']               = $this->lang->line('receivings_stock_suspended');
				$_SESSION['show_dialog']         = 3;
				$_SESSION['suspended_receives']  = $this->Receiving->get_all_by_mode('suspended')->result_array();
				if (empty($_SESSION['suspended_receives'])) {
					$this->receiving_lib->clear_all();
					$_SESSION['error_code'] = '05690';
				}
				redirect('receivings');
				break;

			// Auto PO (reorder)
			case 50:
				$_SESSION['title']        = $this->lang->line('receivings_stock_reorder');
				$_SESSION['show_dialog']  = 6;
				redirect('receivings');
				break;

			// Manage suspended receptions
			case 60:
				$_SESSION['title']               = $this->lang->line('receivings_stock_suspended_reception');
				$_SESSION['show_dialog']         = 4;
				$_SESSION['suspended_receives']  = $this->Receiving->get_all_by_mode('suspendedreception')->result_array();
				if (empty($_SESSION['suspended_receives'])) {
					$this->receiving_lib->clear_all();
					$_SESSION['error_code'] = '05700';
				}
				redirect('receivings');
				break;

			// Stock inventory import
			case 70:
				$_SESSION['show_dialog'] = 8;
				redirect('receivings');
				break;
		}
	}

	// Helper: ensure default supplier is set
	private function _ensure_default_supplier()
	{
		$supplier_id = $this->receiving_lib->get_supplier();
		if ($supplier_id == -1) {
			$this->receiving_lib->set_supplier($this->config->item('default_supplier_id'));
		}
	}
	
	function unsuspend($receiving_id)
	{
		unset($_SESSION['nbre_jour_prevision_stock_correct']);
		unset($_SESSION['historique_correct']);
		unset($_SESSION['nbre_jour_prevision_stock_correct_stay']);
		unset($_SESSION['historique_correct_stay']);

		// clear the virtual shopping cart
		$this->receiving_lib->clear_all();
		
		// set the stock action ID
		$_SESSION['stock_action_id']									=	10;
		
		// set mode
		$this															->	receiving_lib->set_mode("purchaseorder");
		
		// set title
		$_SESSION['title']												=	$this->lang->line('receivings_stock_create');
		
		$_SESSION['marqeur_fonction'] = 'receivings_unsuspend';

		// copy entire suspended receiving to shopping cart
		$this->receiving_lib->copy_entire_receiving($receiving_id);
		
		// set comments to blank
		$this->session->set_userdata('comment', NULL);
		
		// delete suspended ID from file.
		$this->Receiving->delete($receiving_id);

		unset($_SESSION['marqeur_fonction']);

    	$this->_reload();
	}
	
	//attach pour la reception 
	function attach_reception($receiving_id)
	{
		// clear the virtual shopping cart
		$this->receiving_lib->clear_all();
		$_SESSION['attach_reception_boolean'] = array();
		$_SESSION['attach_reception_boolean'][0]=1;
    $_SESSION['attach_reception_boolean'][1]=$receiving_id;
    
    $this->receiving_lib->return_entire_receiving($receiving_id);



		// set the stock action ID
		$_SESSION['stock_action_id']									=	20;
		
		// set mode
		$this															->	receiving_lib->set_mode("receive");
		
		// set title
//		$_SESSION['title']												=	$this->lang->line('receivings_stock_receive');
//		$_SESSION['receiving_id_SESSION']=$receiving_id;
/*
		$tab_update=array();
		$tab_update=array("mode"=> 'purchaseorder');
    $this->db->select("receiving_id");
    $this->db->where("receiving_id", $receiving_id);
		$this->db->update('receivings', $tab_update);    //*/
		/*
		//Changement du mode de la commande 
		//Récupération de la ligne de rattachement

		$redirect = 'receivings';
		$conn_parms =	array();

		//initialisation des parametres pour la connexion
		$conn_parms = $this->Common_routines->get_conn_parms($redirect);
		$conn = $this->Common_routines->open_db($conn_parms);

		//Requête SQL
		$sql_ajout_ligne =	"SELECT * FROM `ospos_transaction_type` WHERE `transaction_ID` = '9'";						
		$result_ajout_ligne	=	$conn->query($sql_ajout_ligne);
		
		//récuperation de la ligne
		$request_ajout_ligne = $result_ajout_ligne->fetch_assoc();
//*/
		// copy entire suspended receiving to shopping cart
		//$this->receiving_lib->copy_entire_receiving($receiving_id);
		
		// delete suspended ID from file.
		//$this->Receiving->delete($receiving_id);
		
    	$this->_reload();
	}

	function unsuspend_reception($receiving_id)
	{
		//if($_SESSION['unsuspend_reception'])
		//{
			$_SESSION['unsuspend_reception']['0'] = '1';
			$_SESSION['unsuspend_reception']['id'] = $receiving_id;
			
		//}

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
		//$this->Receiving->delete($receiving_id);
		
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

    //ajout de la fonction inventory de /var/www/html/wrightetmathon/application/controllers/items.php ligne 671
	function inventory($item_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']		=	new stdClass();
		$_SESSION['transaction_info_dluo']	=	new stdClass();

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
					$_SESSION['origin']		=	$origin;
					$_SESSION['sel_item_id']=	$item_id;
			break;
		}

		// set session data
		unset($_SESSION['clone_from_id']);
		unset($_SESSION['clone_to_id']);
		unset($_SESSION['new']);
		unset($_SESSION['clone']);
		$_SESSION['$title']					=	$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_inventory');
		$_SESSION['transaction_info']		=	$this->Item->get_info($item_id);
		$_SESSION['transaction_info_dluo']	=	$this->Item->get_info_dluo($item_id);

		// set dialog switch
		$_SESSION['show_dialog']			=	17;
		
		$_SESSION['origin']           ='RR';


		// show the data entry
		redirect("items");
	}

	//ajout la fonction view de /var/www/html/wrightetmathon/application/controllers/items.php ligne 97
	// this function is called when updating an item, ie when clicking on the item number or for new items
	function view($item_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']			=	new stdClass();
		$_SESSION['transaction_tax_info']		=	new stdClass();
		$_SESSION['transaction_warehouse_info']	=	new stdClass();
		$_SESSION['transaction_supplier_info']	=	new stdClass();

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
					$_SESSION['origin']		=	$origin;
					$_SESSION['sel_item_id']=	$item_id;
			break;
		}

		// manage session for dialog
		$_SESSION['show_dialog']			=	17;
		$_SESSION['origin']           ='RR';

		// load suppliers pick list
		$supplier_pick_list					=	array();
		$suppliers							=	array();
		$suppliers							=	$this->Supplier->get_all()->result_array();
		foreach($suppliers as $row)
		{
			$supplier_pick_list[$row['person_id']] 	=	strtoupper($row['company_name']);
		}

		// load categories pick list
		$category_pick_list					=	array();
		$categories							=	array();
		$categories							=	$this->Category->get_all();
		foreach($categories as $row)
		{
			$category_pick_list[$row['category_id']] =	$row['category_name'];
		}

		// load warehouses pick list
		$warehouse_pick_list				=	array();
		$warehouses							=	array();
		$warehouses							=	$this->Warehouse->get_all();
		foreach($warehouses->result() as $row)
		{
			$warehouse_pick_list[$row->warehouse_code] =	$row->warehouse_description;
		}

		// load pick list output data
		$_SESSION['category_pick_list']		=	$category_pick_list;
		$_SESSION['supplier_pick_list']		=	$supplier_pick_list;
		$_SESSION['warehouse_pick_list']	=	$warehouse_pick_list;

		// manage session
		switch ($item_id)
		{
			// create new
			case	-1:

					// if clone get info from clone to and set item_number to clone to
					switch ($_SESSION['clone'])
					{
						case	1:

								// manage session
								$_SESSION['transaction_info']->clone_from_id=	$this->input->post('clone_from_id');
								$_SESSION['transaction_info']->clone_to_id	=	$this->input->post('clone_to_id');

								// test clone from exists
								$this->verify_clone();

								// load session data
								$_SESSION['$title']						=	$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_clone');
								$_SESSION['new']						=	1;
								$_SESSION['item_id']					=	-1;
								$_SESSION['transaction_info']			=	$this->Item->get_info($this->Item->get_item_id($_SESSION['transaction_info']->clone_from_id));
								$_SESSION['transaction_info']->item_number=	$this->input->post('clone_to_id');
								$_SESSION['transaction_info']->deleted	=	0;
								$_SESSION['item_tax_info']				=	$this->Item_taxes->get_info($_SESSION['transaction_info']->item_id);
								$_SESSION['selected_supplier']			=	$_SESSION['transaction_info']->supplier_id;
								$_SESSION['selected_category']			=	$_SESSION['transaction_info']->category_id;
								$_SESSION['selected_dluo_indicator']	=	$_SESSION['transaction_info']->dluo_indicator;
								$_SESSION['selected_reorder_policy']	=	$_SESSION['transaction_info']->reorder_policy;
								$_SESSION['selected_giftcard_indicator']=	$_SESSION['transaction_info']->giftcard_indicator;
								$_SESSION['selected_offer_indicator']	=	$_SESSION['transaction_info']->offer_indicator;
								$_SESSION['selected_DynamicKit']		=	$_SESSION['transaction_info']->DynamicKit;
								$_SESSION['selected_export_to_franchise']		=	$_SESSION['transaction_info']->export_to_franchise;
								$_SESSION['selected_export_to_integrated']		=	$_SESSION['transaction_info']->export_to_integrated;
								$_SESSION['selected_export_to_other']			=	$_SESSION['transaction_info']->export_to_other;
								unset($_SESSION['clone']);
						break;

						default:
								$_SESSION['$title']						=	$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_new');
								$_SESSION['new']						=	1;
								$_SESSION['item_id']					=	-1;
								$_SESSION['selected_supplier']			=	$this->config->item('default_supplier_id');
								$_SESSION['selected_category']			=	'';
								$_SESSION['selected_dluo_indicator']	=	'N';
								$_SESSION['selected_reorder_policy']	=	'N';
								$_SESSION['selected_giftcard_indicator']=	'N';
								$_SESSION['selected_offer_indicator']	=	'N';
								$_SESSION['selected_DynamicKit']		=	'N';
								$_SESSION['selected_export_to_franchise']		=	'N';
								$_SESSION['selected_export_to_integrated']		=	'N';
								$_SESSION['selected_export_to_other']			=	'N';
								$_SESSION['transaction_info']->cost_price=	0;
								$_SESSION['transaction_info']->unit_price=	0;
								$_SESSION['transaction_info']->reorder_pack_size=	0;
						break;
					}
			break;

			// update existing item so set up the data
			default:
					// get current data
					$_SESSION['transaction_info']						=	$this->Item->get_info($item_id);
					// and load display data
					$_SESSION['item_tax_info']							=	$this->Item_taxes->get_info($item_id);
					$_SESSION['transaction_warehouse_info']				=	$this->Item->get_info_warehouses($item_id);
					$_SESSION['transaction_supplier_info']				=	$this->Item->get_info_suppliers($item_id);
					$_SESSION['selected_supplier']						=	$_SESSION['transaction_info']->supplier_id;
					$_SESSION['selected_category']						=	$_SESSION['transaction_info']->category_id;
					$_SESSION['selected_warehouse']						=	$_SESSION['transaction_warehouse_info']->warehouse_code;
					$_SESSION['selected_dluo_indicator']				=	$_SESSION['transaction_info']->dluo_indicator;
					$_SESSION['selected_reorder_policy']				=	$_SESSION['transaction_info']->reorder_policy;
					$_SESSION['selected_giftcard_indicator']			=	$_SESSION['transaction_info']->giftcard_indicator;
					$_SESSION['selected_offer_indicator']				=	$_SESSION['transaction_info']->offer_indicator;
					$_SESSION['selected_DynamicKit']					=	$_SESSION['transaction_info']->DynamicKit;
					$_SESSION['selected_export_to_franchise']			=	$_SESSION['transaction_info']->export_to_franchise;
					$_SESSION['selected_export_to_integrated']			=	$_SESSION['transaction_info']->export_to_integrated;
					$_SESSION['selected_export_to_other']				=	$_SESSION['transaction_info']->export_to_other;

					// calculate margin
					// get default price list unit_price for this item
					$default_price_list_info							=	new stdClass();
					$default_price_list_info							=	$this->Item->get_default_pricelist()->result_object();

					if (count($default_price_list_info) != 1)
					{
						$_SESSION['transaction_info']->unit_price		=	0;
					}
					else
					{
						foreach ($default_price_list_info as $row)
						{
							$_SESSION['transaction_info']->unit_price	=	$row->unit_price;
						}
					}

					// get preferred supplier record for this item
					$item_supplier_info									=	$this->Item->item_supplier_get_cost($item_id ); //$this->config->item('default_supplier_id')
					if ($item_supplier_info == NULL)
					{
						$cost_price										=	'ERR'; //lllll
					}
					else
					{
						$cost_price										=	$item_supplier_info->supplier_cost_price;
					}

					// save cost price
					$_SESSION['preferred_supplier_cost_price'] 			= $cost_price;

					// set percentage profit
					if ($_SESSION['transaction_info']->unit_price == 0 OR $cost_price == 0)
					{
						If ($_SESSION['transaction_info']->unit_price == 0)
						{
							$_SESSION['percentage_profit'] 				= 0;
						}
						If ($cost_price == 0)
						{
							$_SESSION['percentage_profit'] 				= 100;
						}
						if ($_SESSION['transaction_info']->unit_price == 0 AND $cost_price == 0)
						{
							$_SESSION['percentage_profit'] 				= 0;
						}
					}
					else
					{
						$_SESSION['percentage_profit']					=	round(((($_SESSION['transaction_info']->unit_price - $cost_price) / $_SESSION['transaction_info']->unit_price) * 100), 2);
					}

					// set titles
					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title']						=	$this->lang->line('common_undelete').'  '.$_SESSION['transaction_info']->name;
						break;

						default:
								$_SESSION['$title']						=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->name;
						break;
					}
					unset($_SESSION['new']);
			break;
		}

		redirect("items");
	}
	



	function load_stock_dosponible_centrale()
	{
		//load file from drive to 127.0.0.1/wrightetmathon
		require_once "/var/www/html/wrightetmathon/application/controllers/hidrive_remote_stock_logistique.php";
	 
		//load /var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv for remote_stock
		$this->db->query("truncate table `ospos_remote_stock`;");
		$this->db->query("load data LOW_PRIORITY infile '/var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv' into table ospos_remote_stock FIELDS TERMINATED BY ';' IGNORE 1 LINES;");
		$this->db->query('UPDATE `ospos_items` SET `ospos_items`.`quantity_central` = 0');
		$this->db->query('UPDATE  `ospos_items` ,`ospos_remote_stock` SET `ospos_items`.`quantity_central` = `ospos_remote_stock`.`STOCK_DISPONIBLE` WHERE `ospos_items`.`item_number` = `ospos_remote_stock`.`CODE_PRODUIT` AND `ospos_remote_stock`.`STOCK_DISPONIBLE` > 0');
		
		// set error code
		$_SESSION['error_code'] = '07430';
		
		//redirect to receivings
		//redirect("receivings");
		
		//redirect to receivings
		redirect("reports/inventory_rolling");
	}

	// --- Partial Receive ---

	function confirm_partial_receive()
	{
		$partial_lines = $this->input->post('partial_lines');
		if (empty($partial_lines) || !is_array($partial_lines)) {
			$_SESSION['error_code'] = '05280';
			$this->_reload();
			return;
		}

		// Store selected lines in session
		$_SESSION['partial_receive_lines'] = $partial_lines;

		// Count totals for confirmation display
		$cart = $this->receiving_lib->get_cart();
		$_SESSION['partial_receive_checked'] = count($partial_lines);
		$_SESSION['partial_receive_total'] = count($cart);

		// Show confirmation dialog
		$_SESSION['show_dialog'] = 2;
		$_SESSION['confirm_what'] = 'partialreceive';
		$_SESSION['origin'] = 'DR';

		$this->_reload();
	}

	function partial_receive()
	{
		// Get selected lines from session
		$partial_lines = $_SESSION['partial_receive_lines'] ?? array();
		if (empty($partial_lines)) {
			$_SESSION['error_code'] = '05280';
			$this->_reload();
			return;
		}

		// Handle unsuspend like complete() does
		if (($_SESSION['unsuspend_reception']['0'] ?? '0') == '1') {
			$_SESSION['unsuspend_reception']['0'] = '0';
			$this->Receiving->delete($_SESSION['unsuspend_reception']['id']);
		}
		if (($_SESSION['attach_reception_boolean'][0] ?? 0) == 1) {
			$_SESSION['attach_reception_boolean'][0] = 0;
			$this->Receiving->update_purchaseorder($_SESSION['attach_reception_boolean'][1]);
		}

		unset($_SESSION['new']);

		// Check supplier
		if ($this->session->userdata('supplier') == -1) {
			$_SESSION['error_code'] = '05260';
			$this->_reload();
			return;
		}

		// Split cart into received and reliquat
		$full_cart = $this->receiving_lib->get_cart();
		$receive_cart = array();
		$reliquat_cart = array();

		foreach ($full_cart as $line => $item) {
			if (in_array((string)$line, $partial_lines)) {
				$receive_cart[$line] = $item;
			} else {
				$reliquat_cart[$line] = $item;
			}
		}

		// --- Step 1: Save received items ---
		$transaction_mode = $this->receiving_lib->get_mode();
		$transaction_code = $this->Transaction->get_transaction_code($transaction_mode);

		switch ($transaction_mode) {
			case "purchaseorder": $_SESSION['title'] = $this->lang->line('receivings_stock_create'); break;
			case "receive": $_SESSION['title'] = $this->lang->line('receivings_stock_receive'); break;
			case "stockadhoc": $_SESSION['title'] = $this->lang->line('receivings_stock_adhoc'); break;
			case "suspended": $_SESSION['title'] = $this->lang->line('receivings_suspended'); break;
			case "suspendedreception": $_SESSION['title'] = $this->lang->line('receivings_suspended_reception'); break;
		}

		$data = array();
		$data['cart'] = $this->array_msort($receive_cart, array('category' => SORT_ASC, 'name' => SORT_ASC));
		$data['count_all_items'] = $this->count_all_items($data['cart']);

		$total = 0;
		foreach ($receive_cart as $item) {
			$total += ($item['price'] * $item['quantity'] - $item['price'] * $item['quantity'] * $item['discount'] / 100);
		}
		$data['total'] = $total;
		$data['transaction_title'] = $_SESSION['title'];
		$data['transaction_time'] = date('d/m/Y H:i:s');
		$comment = $this->receiving_lib->get_comment();
		$data['comment'] = $comment;
		$data['payment_type'] = NULL;
		$data['transaction_subtype'] = $transaction_mode;

		$supplier_id = $this->receiving_lib->get_supplier();
		if ($supplier_id != -1) {
			$person_info = $this->Supplier->get_info($supplier_id);
			$data['supplier'] = strtoupper($person_info->company_name) . ' - ' . $this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);
		}

		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$person_info = $this->Employee->get_info($employee_id);
		$data['employee'] = $this->Common_routines->format_full_name($person_info->last_name, $person_info->first_name);

		// Save received items
		$data['receiving_id'] = $transaction_code . $this->Receiving->save($receive_cart, $supplier_id, $employee_id, $comment, NULL);

		if ($data['receiving_id'] == ($transaction_code . '-1')) {
			$data['error_message'] = $this->lang->line('receivings_transaction_failed');
		}

		$data['image_path'] = $this->Common_routines->generate_barcode($data['receiving_id']);

		// --- Step 2: Save reliquat as suspended ---
		$reliquat_receiving_id = '';
		$reliquat_count = count($reliquat_cart);

		if ($reliquat_count > 0) {
			// Temporarily switch mode to save reliquat as suspended
			$original_mode = $this->receiving_lib->get_mode();

			// Choose suspended mode based on original mode
			if ($original_mode == 'receive' || $original_mode == 'suspendedreception') {
				$this->receiving_lib->set_mode('suspendedreception');
			} else {
				$this->receiving_lib->set_mode('suspended');
			}

			$reliquat_comment = 'Reliquat de réception partielle ' . $data['receiving_id'];
			$reliquat_id = $this->Receiving->save($reliquat_cart, $supplier_id, $employee_id, $reliquat_comment, NULL);

			$suspended_mode = $this->receiving_lib->get_mode();
			$reliquat_code = $this->Transaction->get_transaction_code($suspended_mode);
			$reliquat_receiving_id = $reliquat_code . $reliquat_id;

			// Restore original mode
			$this->receiving_lib->set_mode($original_mode);
		}

		// Pass reliquat info to receipt view
		$data['is_partial_receive'] = true;
		$data['reliquat_receiving_id'] = $reliquat_receiving_id;
		$data['reliquat_count'] = $reliquat_count;

		// Clean up
		unset($_SESSION['partial_receive_lines']);
		unset($_SESSION['partial_receive_checked']);
		unset($_SESSION['partial_receive_total']);

		$this->receiving_lib->clear_all();
		$this->load->view("receivings/receipt", $data);
	}

	//désactive l'article définitivement
	function desactive($tout)
	{
		//Création d'une variable tampon pour savoir si l'oeil est utilisé dans la page Accueil->Stock Tournant ou dans Boutique->Stock
		//Cette variable est ajouté à la fin de la chaîne de caractére

    //$tout est une suite de 2 nombres séparés par le séparateur ":" et cast en chaine de caractère
    //explode Scinde une chaîne de caractères en segments, retourne un tableau de chaînes de caractères, chacune d'elle étant une sous-chaîne du paramètre string extraite en utilisant le séparateur delimiter.
    list($item_id, $line, $direction)=explode(":", $tout);

		if($direction=='reports')
		{
			$direction='reports/inventory_rolling';
			//Pour faire un focus sur l'article où l'on a cliqué sur l'oeil dans /var/www/html/wrightetmathon/application/views/reports/tabular.php
			foreach($_SESSION['report_data'] as $index => $row)
			{
				if($row['item_id'] == $item_id)
				{
					$_SESSION['report_data'][$index]['focus']=1;
				}
				else
				{
          $_SESSION['report_data'][$index]['focus']=0;
				}
			}	

		}
		$_SESSION['line_focus']=$line;
		if($direction=='items')
		{
			$direction='items';
		}

    //$tout est une suite de 2 nombres séparés par le séparateur ":" et cast en chaine de caractère
    //explode Scinde une chaîne de caractères en segments, retourne un tableau de chaînes de caractères, chacune d'elle étant une sous-chaîne du paramètre string extraite en utilisant le séparateur delimiter. 
    //list($item_id, $line)=explode(":", $tout);
		
		$data_items = $this->Item->get_info($item_id);
		$request['quantity'] = $data_items->quantity;
		$request_pour_deleted['deleted'] = $data_items->deleted;

/*		//initialisation des parametres pour la connexion
		$conn_parms														=	$this->Common_routines->get_conn_parms($redirect);
		$conn															=	$this->Common_routines->open_db($conn_parms);

    //Requête SQL
		$sql =	"SELECT `quantity` FROM `ospos_items` WHERE `item_id` = '".$item_id."'";						
		$result	=	$conn->query($sql);
		
		//récuperation de la ligne
		$request	=	$result->fetch_assoc();



//Requête SQL
		$sql_1 =	"SELECT `deleted` FROM `ospos_items` WHERE `item_id` = '".$item_id."'";						
		$result_1	=	$conn->query($sql_1);
		
		//récuperation de la ligne
		$request_pour_deleted	=	$result_1->fetch_assoc();
		//*/
		//Si l'article est déjà désactivé alors il faut le réactiver
		if($request_pour_deleted['deleted']=="1")
		{
			//alors il faut réactiver l'article
			$this->db->from('items');	
	  	$this->db->where('item_id', $item_id);
		  $this->db->update('items', array('deleted' => 0));
			$this->receiving_lib->delete_item($line);

		  $_SESSION['origin']='RR';
			$_SESSION['module_id']                              =    "15";
			
			// Ajout de la ligne correspondant à l'article dans l'Inventory
			// add inventory record
		  $inv_data = array	(
			                  'trans_date'		=>	date('Y-m-d H:i:s'),
			                  'trans_items'		=>	$item_id,
												'trans_user'		=>	$this->Employee->get_logged_in_employee_info()->person_id,
			                  'trans_comment'		=>	$this->lang->line('items_undeleted'),
												'trans_stock_before'=>	$request['quantity'],
												'trans_inventory'	=>	0,
												'trans_stock_after'=>	$request['quantity'],
												'branch_code'		=>	$this->config->item('branch_code')
											  );
			$this->Inventory->insert($inv_data);
			unset($_SESSION['title']);
			unset($_SESSION['origin']);
	
		
		}
//*/


	if($request_pour_deleted['deleted']=="0")
	{


		// check actual quantity on hand is zero
		//Condition pour vérifier si le stock est bien vide même chose que la fonction delete() dans /wrightetmathon/application/controllers/items.php (ligne 2211)
		//if (($request['quantity'] != 0) //|| ($request['quantity'] < 0))
		if ($request['quantity'] > 0)    //Maintenant les articles ayant une quantité négative peuvent aussi être supprimé
		{
			// set error message
			$_SESSION['error_code']			=	'01480';
			//$_SESSION['error_code']			=	'27280';
			$_SESSION['del']				=	1;
		}
		else
		{
			//désactive l'article définitivement dans la base de donnée
		  $this->db->from('items');	
	  	$this->db->where('item_id', $item_id);
		  $this->db->update('items', array('deleted' => 1));

		  //supprime la ligne correspondant à l'article dans le tableau
		  $this->receiving_lib->delete_item($line);

		  $_SESSION['origin']='RR';
			$_SESSION['module_id']                              =    "15";
			
			// Ajout de la ligne correspondant à l'article dans l'Inventory
			// add inventory record
		  $inv_data = array	(
			                  'trans_date'		=>	date('Y-m-d H:i:s'),
			                  'trans_items'		=>	$item_id,
												'trans_user'		=>	$this->Employee->get_logged_in_employee_info()->person_id,
			                  'trans_comment'		=>	$this->lang->line('items_deleted'),
												'trans_stock_before'=>	$request['quantity'],
												'trans_inventory'	=>	0,
												'trans_stock_after'=>	$request['quantity'],
												'branch_code'		=>	$this->config->item('branch_code')
											  );
			$this->Inventory->insert($inv_data);
		}

		unset($_SESSION['title']);
		unset($_SESSION['origin']);

		if($direction!='receivings')
		{
			unset($line);
      $this->_reload();
		}
	}
		//redirection vers la page qui a appelé la fonction de l'oeil
		redirect($direction);
		
	}
//*/

/*
	//désactive l'article définitivement
	function desactive($tout)
	{
		//Création d'une variable tampon pour savoir si l'oeil est utilisé dans la page Accueil->Stock Tournant ou dans Boutique->Stock
		//Cette variable est ajouté à la fin de la chaîne de caractére

    //$tout est une suite de 2 nombres séparés par le séparateur ":" et cast en chaine de caractère
    //explode Scinde une chaîne de caractères en segments, retourne un tableau de chaînes de caractères, chacune d'elle étant une sous-chaîne du paramètre string extraite en utilisant le séparateur delimiter. 
    list($item_id, $line, $direction)=explode(":", $tout);
		
		if($direction=='reports') 
		{
			$direction='reports/inventory_rolling';
		}
		if($direction=='items')
		{
			$direction='items';
		}

    //$tout est une suite de 2 nombres séparés par le séparateur ":" et cast en chaine de caractère
    //explode Scinde une chaîne de caractères en segments, retourne un tableau de chaînes de caractères, chacune d'elle étant une sous-chaîne du paramètre string extraite en utilisant le séparateur delimiter. 
    //list($item_id, $line)=explode(":", $tout);
		
		//Récupération de la valeur de la quantité
    $redirect = 'receivings';
		$conn_parms														=	array();

		//initialisation des parametres pour la connexion
		$conn_parms														=	$this->Common_routines->get_conn_parms($redirect);
		$conn															=	$this->Common_routines->open_db($conn_parms);

    //Requête SQL
		$sql =	"SELECT `quantity` FROM `ospos_items` WHERE `item_id` = '".$item_id."'";						
		$result	=	$conn->query($sql);
		
		//récuperation de la ligne
		$request	=	$result->fetch_assoc();
 
/*
//Requête SQL
		$sql_1 =	"SELECT `deleted` FROM `ospos_items` WHERE `item_id` = '".$item_id."'";						
		$result_1	=	$conn->query($sql_1);
		
		//récuperation de la ligne
		$request_pour_deleted	=	$result->fetch_assoc();
		
		Si l'article est déjà désactivé alors il faut le réactiver
		if($request_pour_deleted=="1")
		{
			//
			
		}
//*/
/*
		// check actual quantity on hand is zero
		//Condition pour vérifier si le stock est bien vide même chose que la fonction delete() dans /wrightetmathon/application/controllers/items.php (ligne 2211)
		//if (($request['quantity'] != 0) //|| ($request['quantity'] < 0))
		if ($request['quantity'] > 0)    //Maintenant les articles ayant une quantité négative peuvent aussi être supprimé
		{
			// set error message
			$_SESSION['error_code']			=	'01480';
			//$_SESSION['error_code']			=	'27280';
			$_SESSION['del']				=	1;
		}
		else
		{
			//désactive l'article définitivement dans la base de donnée
		  $this->db->from('items');	
	  	$this->db->where('item_id', $item_id);
		  $this->db->update('items', array('deleted' => 1));

		  //supprime la ligne correspondant à l'article dans le tableau
		  $this->receiving_lib->delete_item($line);

		  $_SESSION['origin']='RR';
			$_SESSION['module_id']                              =    "15";
			
			// Ajout de la ligne correspondant à l'article dans l'Inventory
			// add inventory record
		  $inv_data = array	(
			                  'trans_date'		=>	date('Y-m-d H:i:s'),
			                  'trans_items'		=>	$item_id,
												'trans_user'		=>	$this->Employee->get_logged_in_employee_info()->person_id,
			                  'trans_comment'		=>	$this->lang->line('items_deleted'),
												'trans_stock_before'=>	$request['quantity'],
												'trans_inventory'	=>	0,
												'trans_stock_after'=>	$request['quantity'],
												'branch_code'		=>	$this->config->item('branch_code')
											  );
			$this->Inventory->insert($inv_data);
		}

		unset($_SESSION['title']);
		unset($_SESSION['origin']);

		if($direction!='receivings')
		{
			unset($line);
      $this->_reload();
		}
		
		//redirection vers la page qui a appelé la fonction de l'oeil
		redirect($direction);
		
	}

*/




}
?>
