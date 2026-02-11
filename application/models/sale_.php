<?php
class Sale extends CI_Model
{
	public function	get_info		($sale_id)
	{
		$this						->	db->from('sales');
		$this						->	db->where('sale_id', $sale_id);
		$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
		return						$this->db->get();
	}
	
	function get_all()
	{
		$this						->	db->from('sales_items');		
		$this						->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}
	
	public function	get_info_by_customer($customer_id)
	{
		$this						->	db->from('sales');
		$this						->	db->where('customer_id', $customer_id);
		$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}

	function	exists				($sale_id)
	{
		$this						->	db->from('sales');
		$this						->	db->where('sale_id',$sale_id);
		$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();

		return						($query->num_rows()==1);
	}
	
	function	CN_already_applied	($serialnumber)
	{
		$this						->	db->from('sales_items');
		$this						->	db->where('serialnumber',$serialnumber);
		$this						->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();

		return						($query->num_rows()==1);
	}
	
	function	update				($transaction_data, $transaction_id)
	{
		$this						->	db->where('sale_id', $transaction_id);
		$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
		$success					=	$this->db->update('sales',$transaction_data);
		
		return						$success;
	}
	
	function	update_line			($transaction_data, $sale_id, $item_id, $line)
	{
		$this						->	db->where('sale_id', $sale_id);
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('line', $line);
		$this						->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
		$success					=	$this->db->update('sales_items', $transaction_data);
		
		return						$success;
	}
	
	function	save_sales_header	($sales_data)
	{
		$this															->	db->insert('sales', $sales_data);
		$sale_id														=	$this->db->insert_id();
		return															$sale_id;
	}
	
	function	save_sales_payment	($sales_payment_data)
	{
		$this															->	db->insert('sales_payments', $sales_payment_data);
		return;
	}
	
	function	save_sales_item		($sales_item_data)
	{
		$this															->	db->insert('sales_items', $sales_item_data);
		return;
	}
	
	function	save_sales_item_tax	($sales_item_tax_data)
	{
		$this															->	db->insert('sales_items_taxes', $sales_item_tax_data);
		return;
	}			

	
	function	delete				($sale_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this						->	db->trans_start();
		
		// Get sales header data
		$sales_header_data			=	$this->get_info($sale_id)->row_array();

		// Get customer record and reduce counts
		$trans_data					=	$this->Customer->get_info($sales_header_data['customer_id']);

		$new_total					=	$trans_data->sales_ht - $sales_header_data['subtotal_after_discount'];
		$new_total_number_of		=	$trans_data->sales_number_of - 1;

		$customer_data				=	array	(
												'sales_ht'			=>	$new_total,
												'sales_number_of'	=>	$new_total_number_of
												);
																
		$this						->	db->where('person_id', $sales_header_data['customer_id']);
		$this						->	db->where('customers.branch_code', $this->config->item('branch_code'));
		$this						->	db->update('customers', $customer_data);

		// Get employee record and reduce counts
		$trans_data					=	$this->Employee->get_info($sales_header_data['employee_id']);
		$new_total					=	$trans_data->sales_ht - $sales_header_data['subtotal_after_discount'];
		$new_total_number_of		=	$trans_data->sales_number_of - 1;
		
		$employee_data				=	array	(
												'sales_ht'			=>	$new_total,
												'sales_number_of'	=>	$new_total_number_of
												);
												
		$this						->	db->where('person_id', $sales_header_data['employee_id']);
		$this						->	db->where('employees.branch_code', $this->config->item('branch_code'));
		$this						->	db->update('employees', $employee_data);
		
		// Get sales items data
		$sales_item_data			=	$this->get_sale_items($sale_id)->result_array();

		// for each item reduce item counts and return stock to item
		foreach ($sales_item_data as $item)
		{
			// reduce counts and return stock
			$cur_item_info 			= 	$this->Item->get_info($item['item_id']);
			
			$new_total				=	$cur_item_info->sales_ht - $item['line_sales_after_discount'];
			$new_total_qty			=	$cur_item_info->sales_qty - $item['quantity_purchased'];
			$new_quantity			=	$cur_item_info->quantity + $item['quantity_purchased'];
			
			$_SESSION['transaction_info']							=	new stdClass();
			$_SESSION['transaction_info']->item_id					=	$item['item_id'];
			$_SESSION['transaction_info']->quantity					=	$new_quantity;
			$_SESSION['transaction_info']->sales_ht					=	$new_total;
			$_SESSION['transaction_info']->sales_qty				=	$new_total_qty;
			$this->Item->save();
			
			// now add an inventory line
			$sale_remarks			=	$this->lang->line('sales_delete_entire_sale').' => '.date('Y-m-d H:i:s');
			
			$inv_data 				= 	array	(
												'trans_date'		=>	date('Y-m-d H:i:s'),
												'trans_items'		=>	$item['item_id'],
												'trans_user'		=>	$sales_header_data['employee_id'],
												'trans_comment'		=>	$sale_remarks,
												'trans_stock_before'=>	$cur_item_info->quantity,
												'trans_inventory'	=>	$item['quantity_purchased'],
												'trans_stock_after'	=>	$new_quantity,
												'branch_code'		=>	$this->config->item('branch_code')
												);
			$this					->	Inventory->insert($inv_data);
		}
		
		// now delete the sale
		$this						->	db->delete	('sales_payments',		array	(
																					'sale_id' => $sale_id,
																					'branch_code' => $this->config->item('branch_code')
																					)
													); 
		$this						->	db->delete	('sales_items_taxes',	array	(
																					'sale_id' => $sale_id,
																					'branch_code' => $this->config->item('branch_code')
																					)
													); 
		$this						->	db->delete	('sales_items',			array	(
																					'sale_id' => $sale_id,
																					'branch_code' => $this->config->item('branch_code')
																					)
													); 
		$this						->	db->delete	('sales',				array	(
																					'sale_id' => $sale_id,
																					'branch_code' => $this->config->item('branch_code')
																					)
													); 
		
		// and commit
		$this						->	db->trans_complete();
				
		// return the status of the commit
		return 						$this->db->trans_status();
	}

	function	get_sale_items		($sale_id)
	{
		$this						->	db->from('sales_items');
		$this						->	db->where('sale_id', $sale_id);
		$this						->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}
	
	function	get_sale_item		($sale_id, $item_id)
	{
		$this						->	db->from('sales_items');
		$this						->	db->where('sale_id', $sale_id);
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}

	function	get_sale_payments	($sale_id)
	{
		$this						->	db->from('sales_payments');
		$this						->	db->where('sale_id', $sale_id);
		$this						->	db->where('sales_payments.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}

	function	get_customer		($sale_id)
	{
		$this						->	db->from('sales');
		$this						->	db->where('sale_id',$sale_id);
		$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
		$row = $this->db->get()->row();
		return 						$row ? $this->Customer->get_info($row->customer_id) : false;
	}
	
	public function	get_giftcard_value($giftcardNumber)
	{
		if ( !$this->Giftcard->exists( $this->Giftcard->get_giftcard_id($giftcardNumber)))
			return 0;
		
		$this						->	db->from('giftcards');
		$this						->	db->where('giftcard_number',$giftcardNumber);
		$this						->	db->where('giftcards.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get()->row()->value;
	}
	
	public function	get_sales_data	(array $inputs)
	{				
		$this						->	db->select	('
													date(sale_time) as sale_date, 
													sum(subtotal_after_discount) as subtotal, 
													sum(overall_total) as total, 
													sum(overall_tax) as tax, 
													sum(overall_profit) as profit
													');
							
		$this						->	db->from('sales');
		
		$this						->	db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
		
		$this						->	db->group_by('date(sale_time)');
		$this						->	db->order_by('date(sale_time)');
		
		return 						$this->db->get()->result_array();
	}


    public function	get_sales_data_notfide	(array $inputs) // not fidelite
    {
        $this						->	db->select	('
        											date(sale_time) as sale_date, 
        											sum(payment_amount) as subtotal,
													sum(overall_total) as total, 
													
													');

        $this						->	db->from('sales');
        $this						->	db->join('sales_payments', 'sales_payments.sale_id = sales.sale_id');

        $this						->	db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
        $this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
        $this						->	db->where('sales_payments.branch_code', $this->config->item('branch_code'));
        $this						->	db->where('sales_payments.payment_method_code != "FIDE"');

		$this						->	db->group_by('sale_date');
        $this						->	db->order_by('sale_date');

        return 						$this->db->get()->result_array();
    }


    function	get_cash_sales_by_date	(array $inputs)
	{				
		$this						->	db->select	('
													sum(payment_amount) as cash_total_today
													');
							
		$this						->	db->from('sales');
		$this						->	db->join('sales_payments', 'sales_payments.sale_id = sales.sale_id');
		
		$this						->	db->where('date(sale_time) BETWEEN "'. $inputs['start_date']. '" and "'. $inputs['end_date'].'"');
		$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('sales_payments.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('sales_payments.payment_method_code', 'CASH');	
		
		return 						$this->db->get();
	}
	
	function	merge_customer		($merge_from_client, $update_data)
	{
		$this						->	db->where('sales.customer_id', $merge_from_client);
		$this						->	db->where('sales.branch_code', $this->config->item('branch_code'));
		$success					=	$this->db->update('sales', $update_data);
		
		return $success;
	}
	
	function	get_payment_methods	()
	{
		$this						->	db->from('payment_methods');
		$this						->	db->where('payment_method_include', 'Y');
		$this						->	db->where('payment_methods.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('payment_method_display_order');
		return 						$this->db->get()->result_array();
	}
	
	function	get_sales_items_by_item_id		($item_id)
	{
		$this						->	db->from('sales_items');
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('sales_items.branch_code', $this->config->item('branch_code'));
		return						$this->db->get();
	}
	
	function	get_sales_items_by_item_id_and_date	($start_date, $end_date, $item_id)
	{
		$this						->	db->select	('sum(quantity_purchased) as total_sold_in_period');
		$this						->	db->from	('sales_items');
		$this						->	db->join	('sales', 'sales_items.sale_id = sales.sale_id');
		$this						->	db->where	('sales_items.item_id', $item_id);
		$this						->	db->where	('date(sale_time) BETWEEN "'. $start_date. '" and "'. $end_date. '"');
		$this						->	db->where	('sales_items.branch_code', $this->config->item('branch_code'));
		return						$this->db->get();
	}
	
	function	update_sales_taxes_by_item_id	($transaction_data, $sale_id, $item_id, $line)
	{
		$this						->	db->where('sale_id', $sale_id);
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('line', $line);
		$this						->	db->where('sales_items_taxes.branch_code', $this->config->item('branch_code'));
		$success					=	$this->db->update('sales_items_taxes', $transaction_data);
		
		return						$success;
	}
	
	function	write_line($ph, $ESC, $title, $value, $type)
	{
		switch ($type)
		{
			case	1:
					$format												=	"%12.2f";
					break;
					
			case	2:
					$format												=	"%12s";
					break;
			default:
					break;	
		}
		
		$a																=	sprintf("%10s", ' ');
		$b																=	sprintf("%-20s", $title);
		$c																=	sprintf($format, $value);
		$line															=	$a.$b.$c;
		fwrite($ph, $line);

		fwrite($ph,	$ESC."d".chr(1));
		
		if($value==$_SESSION['CSI']['SHV']->fidelity_value) // 5 pour les 10
		{
			//ne fait rien
			$c="<br>" . $c; // points de fidélité à la ligne
			$line															=	$a.$b.$c;
		}

		$title_2 = str_replace("\n", "<br>", $line);
		$title_2 = str_replace("{", "é", $title_2);
		$title_2 = str_replace("@", "à", $title_2);
		$title_2 = str_replace("}", "è", $title_2);
		$title_2 = str_replace("|", "ù", $title_2);
		$title_2 = str_replace("\\", "ç", $title_2);

		//Pour les tests
		//$title_2 = str_replace(" ", "&#144", $title_2);
		//$title_2 = str_replace(" ", ".", $title_2);
		
		//Pour forcer à faire l'espace
        $title_2 = str_replace(" ", "&nbsp;", $title_2);		
	
		//Savoir si la ligne est une ligne de: "-" ou pas
        $attention=$c[5];

	    //fwrite($ph, "\r\n");
		
		$_SESSION['message_mail']=$_SESSION['message_mail'] . $title_2;
		if($attention=="-") // 5 pour les 10 // 33
		{
			//ne fait rien
		}
		else
		{
			// Affiche le symbole euro si la ligne contient un prix
			$_SESSION['message_mail']=$_SESSION['message_mail'] . "€";
		}
		$_SESSION['message_mail']=$_SESSION['message_mail'] . "<br>";
	}
	
	function	write_title($subject, $ph, $ESC, $search, $replace, $formfeed)
	{
		$title_1 =$subject;
		$title															=	str_replace($search, $replace, $subject);
		fwrite($ph, $title);
		
		$replace_mail																=	array('&#35', '&#36', '&#224', '&#176', '&#231', '&#167', '&#136', '&#8171', '&#233', '&#249', '&#232', '&#34', '&#244');
		$replace_mail																=	array('#', '$', 'à', '°', 'ç', '§', '^', '`', 'é', 'ù', 'è', '"', 'ô');

		//Remplacement des caractéres spéciaux par leurs expressions adaptés
		$title_2              = str_replace($search, $replace_mail, $subject);
		//fwrite($ph, "\r\n");
        //rajouter str_replace pour les \n
		$title_2 = str_replace("\n", "<br>", $title_2);
		$title_2 = str_replace("{", "é", $title_2);
		$title_2 = str_replace("@", "à", $title_2);
		$title_2 = str_replace("}", "è", $title_2);
		$title_2 = str_replace("|", "ù", $title_2);
		$title_2 = str_replace("\\", "ç", $title_2);
		//$title_2 = str_replace(" ", "&#144", $title_2);
        //$title_2 = str_replace(" ", ".", $title_2);
		$title_2 = str_replace(" ", "&nbsp;", $title_2);

		fwrite($ph,	$ESC."d".chr($formfeed));

		//Il faut trouver une police correct et non proportionnelle
		$_SESSION['message_mail']=$_SESSION['message_mail'] . $title_2;
		if($formfeed==1)
		{
			$_SESSION['message_mail']=$_SESSION['message_mail'] . "<br>";
		}
		else {
			$_SESSION['message_mail']=$_SESSION['message_mail'] . "<br>";
		
		}
	}
}
?>
