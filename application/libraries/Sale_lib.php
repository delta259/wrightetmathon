<?php
class Sale_lib
{
	var $CI;

  	function __construct()
	{
		$this->CI =& get_instance();
	}

	function get_cart()
	{
		if (!$_SESSION['cart'])
		{
			$this->set_cart(array());
		}
		
		return $_SESSION['cart'];
		
		
		//if(!$this->CI->session->userdata('cart'))
			//$this->set_cart(array());

		//return $this->CI->session->userdata('cart');
	}

	function set_cart($cart_data)
	{
		$_SESSION['cart']	=	$cart_data;
		//$this->CI->session->set_userdata('cart',$cart_data);
	}
	
// process comments
	function get_comment() 
	{
		return $this->CI->session->userdata('comment');
	}
	
	function set_comment($comment) 
	{
		$this->CI->session->set_userdata('comment', $comment);
	}

	function clear_comment() 	
	{
		$this->CI->session->unset_userdata('comment');
	}

// process discounts
	function get_overall_discount_percentage() 
	{
		return $this->CI->session->userdata('overall_discount_percentage');
	}
	
	function get_overall_discount_amount() 
	{
		return $this->CI->session->userdata('overall_discount_amount');
	}
	
	function set_overall_discount_percentage($overall_discount_percentage) 
	{
		$this->CI->session->set_userdata('overall_discount_percentage', $overall_discount_percentage);
	}
	
	function set_overall_discount_amount($overall_discount_amount) 
	{
		$this->CI->session->set_userdata('overall_discount_amount', $overall_discount_amount);
	}
	
	function apply_overall_discounts($subtotal) 
	{
		$overall_discount_percentage	=	$this->get_overall_discount_percentage();
		$subtotal						=	($subtotal * (100 - $overall_discount_percentage)) / 100;
		$overall_discount_amount		=	$this->get_overall_discount_amount();
		$subtotal						=	$subtotal - $overall_discount_amount;
		return	$subtotal;
	}
		
	function clear_discounts() 	
	{
		$this->CI->session->unset_userdata('overall_discount_percentage');
		$this->CI->session->unset_userdata('overall_discount_amount');
	}

// process payments
	function get_payments()
	{
		if(!$this->CI->session->userdata('payments'))
			$this->set_payments(array());

		return $this->CI->session->userdata('payments');
	}

	//Alain Multiple Payments
	function set_payments($payments_data)
	{
		$this->CI->session->set_userdata('payments',$payments_data);
	}


	function add_payment($paymethod_array)
	{
		// get existing payments
		$payments = $this->get_payments();
		
		// prepare this payment
		$payment = array($paymethod_array['payment_method_id']	=>	array	(
																			'payment_method_code' 		=> $paymethod_array['payment_method_code'],
																			'payment_type'				=> $paymethod_array['payment_type'],
																			'payment_amount' 			=> $paymethod_array['payment_amount'],
																			'payment_method_id' 		=> $paymethod_array['payment_method_id'],
																			'payment_method_fidelity' 	=> $paymethod_array['payment_method_fidelity'],
																			'payment_method_giftcard'	=> $paymethod_array['payment_method_giftcard'],
																			'payment_giftcard_number'	=> $paymethod_array['payment_giftcard_number']
																			)
		);

		//payment_method already exists, add to payment_amount
		if( isset( $payments[$paymethod_array['payment_method_id']] ) )
		{
			$payments[$paymethod_array['payment_method_id']]['payment_amount'] += $payment_amount;
		}
		else
		{
			//add to existing array
			$payments += $payment;
		}

		$this->set_payments($payments);
		return true;
	}

	//Alain Multiple Payments
	function edit_payment($payment_id,$payment_amount)
	{
		$payments = $this->get_payments();
		if(isset($payments[$payment_id]))
		{
			$payments[$payment_id]['payment_type'] = $payment_id;
			$payments[$payment_id]['payment_amount'] = $payment_amount;
			$this->set_payments($payment_id);
		}

		return false;
	}

	//Alain Multiple Payments
	function delete_payment($pmi)
	{
		unset($_SESSION['CSI']['PD'][$pmi]);
		$this															->	payment_values();
		return;
	}

	//Alain Multiple Payments
	function empty_payments()
	{
		$this->CI->session->unset_userdata('payments');
	}

	//Alain Multiple Payments
	function get_payments_total()
	{
		$subtotal = 0;
		foreach($this->get_payments() as $payments)
		{
		    $subtotal+=$payments['payment_amount'];
		}
		return to_currency_no_money($subtotal);
	}

	// Get amount due
	function get_amount_due()
	{
		$amount_due														=	0;
		$payment_total 													=	$this->get_payments_total();
		$sales_total													=	$this->get_total();
		$amount_due														=	$sales_total - $payment_total;
		return $amount_due;
	}

// process customer
	function get_customer()
	{
		if(!$this->CI->session->userdata('customer'))
			$this->set_customer(-1);

		return $this->CI->session->userdata('customer');
	}

	function set_customer($customer_id)
	{
		$this->CI->session->set_userdata('customer',$customer_id);
	}

// process mode
	function get_mode()
	{
		if(!$this->CI->session->userdata('sale_mode'))
			$this->set_mode('sales');
		return $this->CI->session->userdata('sale_mode');
	}

	function set_mode($mode)
	{
		$this->CI->session->set_userdata('sale_mode', $mode);
	}

	// add item to cart
	function add_item($item_id, $quantity=1, $discount=0, $price=null, $description=null, $serialnumber=null, $kit_item='N', $kit_option_type='O', $kit_reference=NULL, $kit_option=NULL, $kit_cart_line=0)
	{
		// cart items are stored in,
		// $_SESSION['CSI']['CT']										=	CarT Info by item ID
		
		// make sure item exists
		if(!$this->CI->Item->exists($item_id))
		{
			//try to get item id given an item_number
			$item_id = $this->CI->Item->get_item_id($item_id);

			if(!$item_id)
			{
				return 0;
			}	
		}
		
		// does this item already exist in cart?
		if (isset($_SESSION['CSI']['CT'][$item_id]))
		{
			// if so update the quantity
			$_SESSION['CSI']['CT'][$item_id]->line_quantity				+=	$quantity;
		}
		else
		{
			// else add it
			// get item info
			$_SESSION['CSI']['CT'][$item_id]							=	$this->CI->Item->get_info($item_id);
			// is this a kit item?
			if ($kit_item != 'Y')
			{
				// if not get prices for customer price list
				$prices													=	$this->get_price($item_id, $_SESSION['CSI']['SHV']->pricelist_id);
				$_SESSION['CSI']['CT'][$item_id]->line_priceTTC			=	$prices['price_with_tax'];
				$_SESSION['CSI']['CT'][$item_id]->line_priceHT			=	$prices['price_no_tax'];
				$_SESSION['CSI']['CT'][$item_id]->kit_item				=	$kit_item;
				$_SESSION['CSI']['CT'][$item_id]->kit_option_type		=	NULL;
				$_SESSION['CSI']['CT'][$item_id]->kit_reference			=	NULL;
				$_SESSION['CSI']['CT'][$item_id]->kit_option			=	NULL;
				$_SESSION['CSI']['CT'][$item_id]->kit_cart_line			=	NULL;
			}
			else
			{
				// set kit related stuff
				$_SESSION['CSI']['CT'][$item_id]->line_priceTTC			=	0;
				$_SESSION['CSI']['CT'][$item_id]->line_priceHT			=	0;
				$_SESSION['CSI']['CT'][$item_id]->category				=	$kit_option;
				$_SESSION['CSI']['CT'][$item_id]->kit_item				=	$kit_item;
				$_SESSION['CSI']['CT'][$item_id]->kit_option_type		=	$kit_option_type;
				$_SESSION['CSI']['CT'][$item_id]->kit_reference			=	$kit_reference;
				$_SESSION['CSI']['CT'][$item_id]->kit_option			=	$kit_option;
				$_SESSION['CSI']['CT'][$item_id]->kit_cart_line			=	$kit_cart_line;
			}
			
			// set other stuff
			$_SESSION['CSI']['CT'][$item_id]->line_quantity				=	$quantity;
			$_SESSION['CSI']['CT'][$item_id]->line_discount				=	$discount;
			// set line offered flag
			if ($_SESSION['CSI']['CT'][$item_id]->line_discount == 100)
			{
				$_SESSION['CSI']['CT'][$item_id]->line_offered			=	'Y';
			}
			else
			{
				$_SESSION['CSI']['CT'][$item_id]->line_offered			=	'N';
			}
			// set force price flag
			if ($_SESSION['CSI']['CT'][$item_id]->line_discount == 0)
			{
				$_SESSION['CSI']['CT'][$item_id]->force_price			=	0;	// this is used at line level to show that a price was forced
			}
			else
			{
				$_SESSION['CSI']['CT'][$item_id]->force_price			=	1;
			}
			
			// get supplier info and append to array
			$_SESSION['transaction_info']->item_id						=	$item_id;
			$preferred_supplier_data									=	array();
			$preferred_supplier_data									=	$this->CI->Item->get_preferred_supplier()->result_array();
			// test returned array
			if (count($preferred_supplier_data) > 0)
			{
				$_SESSION['CSI']['CT'][$item_id]->supplier_cost_price	=	$preferred_supplier_data[0]['supplier_cost_price'];
			}
			else
			{
				$_SESSION['CSI']['CT'][$item_id]->supplier_cost_price	=	0;
			}
		}
			
		// calculate values
		$this															->	line_values($item_id);
		$this															->	header_values();
		$this															->	payment_values();
		
		// set out of stock indicator
		if ($_SESSION['CSI']['CT'][$item_id]->quantity < $_SESSION['CSI']['CT'][$item_id]->line_quantity)
		{
			$_SESSION['CSI']['CT'][$item_id]->out_of_stock				=	'Y';
		}
		else
		{
			$_SESSION['CSI']['CT'][$item_id]->out_of_stock				=	'N';
		}			
		
		// return
		return															($item_id);
	}
	
	function line_values($item_id)
	{
		$_SESSION['CSI']['CT'][$item_id]->line_valueBD_TTC				=	round($_SESSION['CSI']['CT'][$item_id]->line_priceTTC * $_SESSION['CSI']['CT'][$item_id]->line_quantity, 2);
		$_SESSION['CSI']['CT'][$item_id]->line_valueBD_HT				=	round($_SESSION['CSI']['CT'][$item_id]->line_priceHT * $_SESSION['CSI']['CT'][$item_id]->line_quantity, 2);
		$_SESSION['CSI']['CT'][$item_id]->line_taxBD					=	round($_SESSION['CSI']['CT'][$item_id]->line_valueBD_TTC - $_SESSION['CSI']['CT'][$item_id]->line_valueBD_HT, 2);

		$_SESSION['CSI']['CT'][$item_id]->line_valueAD_TTC				=	round(($_SESSION['CSI']['CT'][$item_id]->line_valueBD_TTC * (100 - $_SESSION['CSI']['CT'][$item_id]->line_discount)) / 100, 2);
		$_SESSION['CSI']['CT'][$item_id]->line_valueAD_HT				=	round(($_SESSION['CSI']['CT'][$item_id]->line_valueBD_HT * (100 - $_SESSION['CSI']['CT'][$item_id]->line_discount)) / 100, 2);
		$_SESSION['CSI']['CT'][$item_id]->line_taxAD					=	round($_SESSION['CSI']['CT'][$item_id]->line_valueAD_TTC - $_SESSION['CSI']['CT'][$item_id]->line_valueAD_HT, 2);

		$_SESSION['CSI']['CT'][$item_id]->line_cost_HT					=	round($_SESSION['CSI']['CT'][$item_id]->supplier_cost_price * $_SESSION['CSI']['CT'][$item_id]->line_quantity, 2);
		$_SESSION['CSI']['CT'][$item_id]->line_profit_HT				=	round($_SESSION['CSI']['CT'][$item_id]->line_valueAD_HT - $_SESSION['CSI']['CT'][$item_id]->line_cost_HT, 2);
		
		return;
	}
	
	function payment_values()
	{
		// initialise
		$_SESSION['CSI']['SHV']->header_payments_TTC					=	0;
		
		// calculate the totals
		foreach ($_SESSION['CSI']['PD'] as $pmi => $payment)
		{
			$_SESSION['CSI']['SHV']->header_payments_TTC				+=	$payment->payment_amount_TTC;
		}
		
		// set amount_due
		$_SESSION['CSI']['SHV']->header_amount_due_TTC					=	$_SESSION['CSI']['SHV']->header_valueAD_TTC - $_SESSION['CSI']['SHV']->header_payments_TTC;									

		// return
		return;
	}
	
	function out_of_stock($item_id)
	{
		//make sure item exists
		if(!$this->CI->Item->exists($item_id))
		{
			//try to get item id given an item_number
			$item_id = $this->CI->Item->get_item_id($item_id);

			if(!$item_id)
				return false;
		}
		
		$item = $this->CI->Item->get_info($item_id);
		$quanity_added = $this->get_quantity_already_added($item_id);
		
		if ($item->quantity - $quanity_added < 0)
		{
			return true;
		}
		
		return false;
	}
	
	function process_kit($item_id, $kit_cart_line)
	{
		//make sure item exists (item_id can contain either the item ID or a item number)
		if(!$this->CI->Item->exists($item_id))
		{
			//try to get item id given an item_number
			$item_id = $this->CI->Item->get_item_id($item_id);

			if(!$item_id)
				return false;
		}
		
		// get the item_info
		$item_info						=	$this->CI->Item->get_info($item_id);
		
		// test if kit
		if ($item_info->DynamicKit == '1')
		{
			// get kit structure
			$kit_structure				=	array();
			$kit_structure				=	$this->CI->Item->get_kit_structure($item_info->kit_reference)->result_array();
			
			// and read it
			$kit_row					=	array();
			foreach ($kit_structure as $kit_row)
			{
				// get the detail
				$db_detail_result		=	$this->CI->Item->get_kit_detail_option($item_info->kit_reference, $kit_row['kit_option'])->result_array();
				
				// and read the detail
				foreach ($db_detail_result as $kit_row_detail)
				{
					// get the item ID
					$item_id			=	$this->CI->Item->get_item_id($kit_row_detail['item_number']);
					
					// test for fixed reference
					if ($kit_row['kit_option_type'] == 'F')
					{
						$quantity		=	$kit_row['kit_option_qty'];
					}
					else
					{
						$quantity		=	0;
					}
					
					// set price = 0
					$price				=	0;
					
					// set kit_item indicator
					$kit_item			=	'Y';
										
					// and add the item to the cart
					$this->add_item($item_id, $quantity, $discount=0, $price, $description=null, $serialnumber=null, $kit_item, $kit_row['kit_option_type'], $item_info->kit_reference, $kit_row['kit_option'], $kit_cart_line);
				}
			}
		}
		return;
	}
		
	function get_quantity_already_added($item_id)
	{
		$items = $this->get_cart();
		$quanity_already_added = 0;
		foreach ($items as $item)
		{
			if($item['item_id']==$item_id)
			{
				$quanity_already_added+=$item['quantity'];
			}
		}
		
		return $quanity_already_added;
	}
	
	function get_item_id($line_to_get)
	{
		$items = $this->get_cart();

		foreach ($items as $line=>$item)
		{
			if($line==$line_to_get)
			{
				return $item['item_id'];
			}
		}
		
		return -1;
	}

	function edit_item($item_id, $new_cart_data)
	{		
		// set data
		$_SESSION['CSI']['CT'][$item_id]->line_quantity					=	$new_cart_data['line_quantity'];
		$_SESSION['CSI']['CT'][$item_id]->line_offered					=	$new_cart_data['line_offered'];
					
		// set discount if line_offered
		if ($_SESSION['CSI']['CT'][$item_id]->line_offered == 'Y')
		{
			$new_cart_data['line_discount']								=	100;
		}

		// set discount entered
		if ($new_cart_data['line_discount'] != 0)
		{
			// set force price indicator
			$_SESSION['CSI']['CT'][$item_id]->force_price				=	1;	// this is used at line level to show that a price was forced
			
			// set discount in cart
			$_SESSION['CSI']['CT'][$item_id]->line_discount				=	$new_cart_data['line_discount'];
		}
		else
		{
			// set force price indicator
			$_SESSION['CSI']['CT'][$item_id]->force_price				=	0;
			
			// set discount in cart
			$_SESSION['CSI']['CT'][$item_id]->line_discount				=	$new_cart_data['line_discount'];
		}
		
		// calculate values
		$this															->	line_values($item_id);
		$this															->	header_values();
		$this															->	payment_values();
		
		// set out of stock indicator
		if ($_SESSION['CSI']['CT'][$item_id]->quantity < $_SESSION['CSI']['CT'][$item_id]->line_quantity)
		{
			$_SESSION['CSI']['CT'][$item_id]->out_of_stock				=	'Y';
		}
		else
		{
			$_SESSION['CSI']['CT'][$item_id]->out_of_stock				=	'N';
		}
		
		return;
	}

	function is_valid_receipt($receipt_sale_id)
	{
		//POS #
		$pieces = explode('-',$receipt_sale_id);

		if(count($pieces)==2)
		{
			return $this->CI->Sale->exists($pieces[1]);
		}

		return false;
	}
	
	function is_valid_item_kit($item_kit_id)
	{
		//KIT #
		$pieces = explode(' ',$item_kit_id);

		if(count($pieces)==2)
		{
			return $this->CI->Item_kit->exists($pieces[1]);
		}

		return false;
	}

	function return_entire_sale($receipt_sale_id)
	{
		// housekeeping
		$this->empty_cart();
		$this->remove_customer();
		
		// get the parts of the sales id
		$pieces = explode('-',$receipt_sale_id);
		$sale_id = $pieces[1];
		
		// get the mode and the code
		$transaction_subtype = $this->CI->session->userdata('sale_mode');
		$transaction_code = strtoupper($pieces[0]);
		
		// get the multiplier in order to set the sign of the quantity correctly
		$transaction_multiplier = $this->CI->Transaction->get_transaction_multiplier($transaction_subtype, $transaction_code);

		foreach($this->CI->Sale->get_sale_items($sale_id)->result() as $row)
		{
			// Set the quantity depending on the type of document and what the user is trying to do (depends on mode)
			$row_quantity = $row->quantity_purchased * $transaction_multiplier;
			
			// Finally add the item line		
			$this->add_item($row->item_id,$row_quantity,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber);
		}
		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
	}
	
	function add_item_kit($external_item_kit_id)
	{
		//KIT #
		$pieces = explode(' ',$external_item_kit_id);
		$item_kit_id = $pieces[1];
		
		foreach ($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$this->add_item($item_kit_item['item_id'], $item_kit_item['quantity']);
		}
	}

	function copy_entire_sale($sale_id)
	{
		// initialise
		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale->get_sale_items($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id,$row->quantity_purchased,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber);
		}
		foreach($this->CI->Sale->get_sale_payments($sale_id)->result() as $row)
		{
			$this->add_payment($row->payment_type,$row->payment_amount);
		}
		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);

	}
	
	function copy_entire_suspended_sale($sale_id)
	{
		// restore customer
		$_SESSION['CSI']['SHV']->customer_id							=	$this->CI->Sale_suspended->get_customer($sale_id)->person_id;		
		
		
		foreach($this->CI->Sale_suspended->get_sale_items($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id,$row->quantity_purchased,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber, $kit_item='N', $kit_option_type='O', $kit_reference=NULL, $kit_option=NULL, $kit_cart_line=0);
		}

		$this->set_customer($this->CI->Sale_suspended->get_customer($sale_id)->person_id);
	}

	function delete_item($item_id)
	{
		// now that we are handling kits in the cart, we need delete all the child lines of the kit as well 
		// as the master kit line
		// so, read the cart to find all kit lines
		foreach($_SESSION['CSI']['CT'] as $item_id => $cart_line)			
		{
			// test if kit_cart_line is equal master line, if so delete it
			if ($cart_line->kit_cart_line == $item_id)
			{
				unset($_SESSION['CSI']['CT'][$item_id]);
			}
		}
		
		// Now delete the master row
		unset($_SESSION['CSI']['CT'][$item_id]);
		
		// return
		return;
	}

	function empty_cart()
	{
		unset($_SESSION['CSI']['CT']);
	}

	function remove_customer()
	{
		$this->CI->session->unset_userdata('customer');
		$this->clear_discounts();
		
		unset($_SESSION['force_price_flag']);
		unset($_SESSION['overall_discount_flag']);
	}

	function clear_mode()
	{
		unset($_SESSION['CSI']['SHV']->mode);
	}

	function clear_all()
	{
		unset($_SESSION['CSI']);
	}

	function get_taxes()
	{
		// initialise
		$customer_id 													= 	$this->get_customer();
		$customer 														= 	$this->CI->Customer->get_info($customer_id);
		$taxes 															= 	array();

		// Do not charge sales tax if we have a customer that is not taxable
		if ($customer->taxable == 'N')
		{
		   $name														=	$this->CI->$this->lang->line('sales_client_not_taxable');
		   $tax_amount													=	0;
		   $taxes[$name]												+= 	$tax_amount;
		}
		// else get taxes
		else
		{
			// get tax name and percentage from configuration file
			$tax_percentage												=	$this->CI->config->item('default_tax_1_rate');
			$tax_name													=	$this->CI->config->item('default_tax_1_name');
			
			// set up output array
			$name 														= 	$tax_percentage.'% ' . $tax_name;
			
			// get total
			$total														=	number_format($this->get_total(), 2);
			
			// get the subtotals
			$subtotal_no_tax											=	number_format($total / (1 + ($tax_percentage/100)), 2);
			$subtotal_with_tax											=	$total;
			
			// calculate the tax amount
			$tax_amount													=	number_format($subtotal_with_tax - $subtotal_no_tax, 2);
			
			// set up output array
			$taxes[$name]												+= 	$tax_amount;
		}

		return $taxes;
	}

	function get_subtotal()
	{
		// initialise
		$subtotal 														= 	0;
		
		// get total
		$total															=	number_format($this->get_total(), 2);
		
		// $taxes is an array with the tax description and the tax amount
		$taxes															=	$this->get_taxes();
		
		// so to calculate the tax amount I have to read over the taxes array
		foreach($taxes as $name=>$value)
		{
			$tax_amount													=	number_format($tax_amount + $value, 2);
		}
		
		// calculate subtotal
		$subtotal 														= 	number_format($total - $tax_amount, 2);
		
		return to_currency_no_money($subtotal);
	}
	
	function get_subtotal_with_tax()
	{
		// initialise
		$subtotal 														= 	0;
		
		// read cart
		foreach($this->get_cart() as $item)
		{
			// calculate price using price_with_tax (discount has already been applied to price with tax, if any)
			// use price with 2 decimal places
			$item['price_with_tax']										=	number_format($item['price_with_tax'], 2);
			$subtotal													+=	$item['price_with_tax'] * $item['quantity'];
		}
		
		// apply overall discounts
		$subtotal														=	$this->apply_overall_discounts($subtotal);
		
		return to_currency_no_money($subtotal);
	}

	function get_cost()
	{
		// initialise
		$cost	 														= 	0;
		
		// read cart
		foreach($this->get_cart() as $item)
		{
		    // get preferred supplier for this item.			
		    $_SESSION['transaction_info']->item_id						=	$item['item_id'];
		    $preferred_supplier_data									=	array();
			$preferred_supplier_data									=	$this->CI->Item->get_preferred_supplier()->result_array();
			// if preferred supplier found
			if (!empty($preferred_supplier_data))
			{
				// calculate cost
				$item_cost												=	number_format($preferred_supplier_data[0]['supplier_cost_price'] * $item['quantity'], 2);
			}
			// if no preferred supplier get default supplier
			else
			{
				$default_supplier_data									=	array();
				$default_supplier_data									=	$this->CI->Item->item_supplier_get($item['item_id'], $this->CI->config->item('default_supplier_id'));
				// if default supplier found
				if (!empty($default_supplier_data))
				{
					// calculate cost
					$item_cost											=	number_format($default_supplier_data['supplier_cost_price'] * $item['quantity'], 2);
				}
				// else $item_cost = 0
				else
				{
					$item_cost											=	0;
				}
			}	
				
			// accumulate cost
		    $cost														=	$cost + $item_cost;
		}

		return to_currency_no_money($cost);
	}

	function get_total()
	{
		// initialise
		$total 															= 	0;
		foreach($this->get_cart() as $item)
		{
            $total 														+= 	(number_format($item['price'], 2) * $item['quantity'] - number_format($item['price'], 2) * $item['quantity'] * $item['discount'] / 100);
		}
		
		// apply overall discounts
		$total															=	$this->apply_overall_discounts($total);
		
		// apply taxes but only if price are shown without tax.
		if ($_SESSION['price_with_tax'] != 'Y')
		{
			foreach($this->get_taxes() as $tax)
			{
				$total													+=	$tax;
			}
		}
		
		return to_currency_no_money($total);
	}
	
	function get_overall_totals()
	{		
		// get total
		$total															=	0; 
		$total															=	number_format($this->get_total(), 2);
		
		// get subtotal
		$subtotal														=	0;
		$subtotal														=	number_format($this->get_subtotal(), 2);
		
		// get taxes
		$tax_amount														= 	0;
		foreach($this->get_taxes() as $tax)
		{
			$tax_amount													+=	$tax;
		}
			
		// subtotal before discounts
		//$subtotal_before_discount						= 	0;
		//foreach($this->get_cart() as $item)
		//{
		 //   $subtotal_before_discount					+=	$item['price_no_tax'] * $item['quantity'];
		//}
		
		// overall discount percentage amount
		//$subtotal_discount_percentage_amount			= 	0;
		//$overall_discount_percentage					=	$this->get_overall_discount_percentage();
		//$subtotal_discount_percentage_amount			=	$subtotal_before_discount * $overall_discount_percentage / 100;
		
		// overall discount amount amount
		//$subtotal_discount_amount_amount				= 	0;
		//$subtotal_discount_amount_amount				=	$this->get_overall_discount_amount();
		
		// subtotal after discounts
		//$subtotal_after_discount						= 	0;
		//$subtotal_after_discount						=	$subtotal_before_discount - ($subtotal_discount_percentage_amount + $subtotal_discount_amount_amount);
		
		// cost
		$cost															=	0;
		$cost															=	$this->get_cost();
		
		// profit
		$profit															=	0;
		$profit															=	$subtotal - $cost;
		
		// amount change
		$amount_change													=	0;
		$amount_change													=	$this->get_amount_due() * -1;
		
		// load output array
		$totals															=	array();
		$totals['subtotal_before_discount']								=	to_currency_no_money($subtotal);
		$totals['subtotal_discount_percentage_amount']					=	0;
		$totals['subtotal_discount_amount_amount']						=	0;
		$totals['subtotal_after_discount']								=	to_currency_no_money($subtotal);
		$totals['tax_amount']											=	to_currency_no_money($tax_amount);
		$totals['total']												=	to_currency_no_money($total);
		$totals['cost']													=	to_currency_no_money($cost);
		$totals['profit']												=	to_currency_no_money($profit);
		$totals['amount_change']										=	to_currency_no_money($amount_change);
		$totals['overall_discount_percentage']							=	0;
		$totals['overall_discount_amount']								=	0;
		$totals['overall_tax_percentage']								=	$this->CI->config->item('default_tax_1_rate');
		$totals['overall_tax_name']										=	$this->CI->config->item('default_tax_1_name');
		
		return $totals;
	}
	
	function get_price($item_id, $pricelist_id)
	{
		// get price info from price list file
		$item_price_info												=	$this->CI->Item->get_info_item_price($item_id, $pricelist_id);
		
		// check got record
		if (count($item_price_info) == 1)
		{
			foreach ($item_price_info as $item_price);
			{
				// check validity
				// check valid to for illimited
				if ($item_price->valid_to_day == 0 AND $item_price->valid_to_month == 0 AND $item_price->valid_to_year == 0)
				{
					$item_price->valid_to_day							=	31;
					$item_price->valid_to_month							=	12;
					$item_price->valid_to_year							=	9999;
				}
				// transform dates to real dates
				$vfrom													=	strtotime($item_price->valid_from_day.'-'.$item_price->valid_from_month.'-'.$item_price->valid_from_year);	
				$vto													=	strtotime($item_price->valid_to_day.'-'.$item_price->valid_to_month.'-'.$item_price->valid_to_year);
				// get today
				$now													=	time();
				// test today within validity
				if ($now < $vfrom OR $now > $vto)
				{
					// outside validity so get default price
					$item_price_info									=	$this->CI->Item->get_info_item_price($item_id, $this->CI->config->item('pricelist_id'));
					
					// test got record
					if (count($item_price_info) == 1)
					{
						foreach ($item_price_info as $item_price);
						{
							$price_no_tax								=	$item_price->unit_price;
							$price_with_tax								=	$item_price->unit_price_with_tax;
						}
					}
					else
					{
						$price_no_tax									=	0;
						$price_with_tax									=	0;
					}
				}
				else
				{
					$price_no_tax										=	$item_price->unit_price;
					$price_with_tax										=	$item_price->unit_price_with_tax;
				}
			}
		}
		else
		{
			$price_no_tax												=	0;
			$price_with_tax												=	0;
		}

		// load return array
		$prices															=	array	(
																					'price_no_tax'	=>	$price_no_tax,
																					'price_with_tax'=>	$price_with_tax
																					);

		return $prices;
	}	
}
?>
