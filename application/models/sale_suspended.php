<?php
class Sale_suspended extends CI_Model
{
	function	get_all				()
	{
		$this						->	db->from('sales_suspended');
		$this						->	db->where('sales_suspended.branch_code', $this->config->item('branch_code'));
		$this						->	db->where('sales_suspended.cancel_indicator', 'N');
		$this						->	db->order_by('sale_id');
		return 						$this->db->get();
	}
	
	public function	get_info		($sale_id)
	{
		$this						->	db->from('sales_suspended');
		$this						->	db->where('sale_id',$sale_id);
		$this						->	db->where('sales_suspended.branch_code', $this->config->item('branch_code'));
		return						$this->db->get();
	}

	function	exists				($sale_id)
	{
		$this						->	db->from('sales_suspended');
		$this						->	db->where('sale_id',$sale_id);
		$this						->	db->where('sales_suspended.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();

		return 						($query->num_rows()==1);
	}
	
	function	update				($sale_data, $sale_id)
	{
		$this						->	db->where('sale_id', $sale_id);
		$this						->	db->where('sales_suspended.branch_code', $this->config->item('branch_code'));
		$success					=	$this->db->update('sales_suspended', $sale_data);
		
		return 						$success;
	}
	
	function	save				()
	{
		if (count($_SESSION['CSI']['CT']) == 0)
		{
			return -1;
		}
		
		// set overall discount in the payment types since this field is not used for suspended sales
		if (isset($_SESSION['CSI']['SHV']->overall_discount))
		{
			$payment_type												=	$_SESSION['CSI']['SHV']->overall_discount;
		}
		else
		{
			$payment_type												=	NULL;
		}
		
		if(isset($_SESSION['cancel_indicator']))
		{
			$cancel_indicator = $_SESSION['cancel_indicator'];
		}
		if(!isset($_SESSION['cancel_indicator']))
		{
			$cancel_indicator = 'N';
		}
		// construct save array
		$sales_data					=	array	(
												'sale_time'		=>	date('Y-m-d H:i:s'),
												'customer_id'	=>	$_SESSION['CSI']['SHV']->customer_id,
												'employee_id'	=>	$_SESSION['CSI']['SHV']->employee_id,
												'payment_type'	=>	$payment_type,
												'comment'		=>	$_SESSION['CSI']['SHV']->comment,
												'cancel_indicator' => $cancel_indicator,
												'branch_code'	=>	$this->config->item('branch_code')
												);

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this						->	db->trans_start();

		$this						->	db->insert('sales_suspended', $sales_data);
		$sale_id 					=	$this->db->insert_id();

		foreach($_SESSION['CSI']['CT'] as $line => $cart_line)
		{
			$sales_items_data		=	array	(
												'sale_id'				=>	$sale_id,
												'item_id'				=>	$cart_line->item_id,
												'line'					=>	$line,
												'description'			=>	$cart_line->name,
												'quantity_purchased'	=>	$cart_line->line_quantity,
												'discount_percent'		=>	$cart_line->line_discount,
												'item_cost_price' 		=>	$cart_line->supplier_cost_price,
												'item_unit_price'		=>	$cart_line->line_priceTTC,
												'branch_code'			=>	$this->config->item('branch_code')
												);

			$this					->	db->insert('sales_suspended_items', $sales_items_data);
		}
		$this						->	db->trans_complete();
		
		if ($this->db->trans_status() === FALSE)
		{
			$sale_id 													-1;
		}
		
		return $sale_id;
	}
	
	function	delete				($sale_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this						->	db->trans_start();
		
		$this						->	db->delete	('sales_suspended_payments',	array	(
																							'sale_id' => $sale_id,
																							'branch_code' => $this->config->item('branch_code')
																							)
													); 
		$this						->	db->delete	('sales_suspended_items_taxes',	array	(
																							'sale_id' => $sale_id,
																							'branch_code' => $this->config->item('branch_code')
																							)
													); 
		$this						->	db->delete	('sales_suspended_items',		array	(
																							'sale_id' => $sale_id,
																							'branch_code' => $this->config->item('branch_code')
																							)
													); 
		$this						->	db->delete	('sales_suspended',				array	(
																							'sale_id' => $sale_id,
																							'branch_code' => $this->config->item('branch_code')
																							)
													); 
		
		$this						->	db->trans_complete();
				
		return 						$this->db->trans_status();
	}

	function	get_sale_items		($sale_id)
	{
		$this						->	db->from('sales_suspended_items');
		$this						->	db->where('sale_id',$sale_id);
		$this						->	db->where('sales_suspended_items.branch_code', $this->config->item('branch_code'));
		return						$this->db->get();
	}

	function	get_sale_payments	($sale_id)
	{
		$this						->	db->from('sales_suspended_payments');
		$this						->	db->where('sale_id',$sale_id);
		$this						->	db->where('sales_suspended_payments.branch_code', $this->config->item('branch_code'));
		return 						$this->db->get();
	}

	function	get_customer		($sale_id)
	{
		$this						->	db->from('sales_suspended');
		$this						->	db->where('sale_id',$sale_id);
		$this						->	db->where('sales_suspended.branch_code', $this->config->item('branch_code'));
		$row = $this->db->get()->row();
		return 						$row ? $this->Customer->get_info($row->customer_id) : false;
	}
	
	function	get_comment			($sale_id)
	{
		$this						->	db->from('sales_suspended');
		$this						->	db->where('sale_id',$sale_id);
		$this						->	db->where('sales_suspended.branch_code', $this->config->item('branch_code'));
		$row = $this->db->get()->row();
		return 						$row ? $row->comment : '';
	}
	
	function	merge_customer		($merge_from_client, $update_data)
	{
		$this						->	db->where('sales_suspended.customer_id', $merge_from_client);
		$this						->	db->where('sales_suspended.branch_code', $this->config->item('branch_code'));
		$success					=	$this->db->update('sales_suspended', $update_data);
		
		return $success;
	}
	
}
?>
