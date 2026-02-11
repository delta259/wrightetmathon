	<?php
class Receiving_lib
{
	var $CI;

  	function __construct()
	{
		$this->CI =& get_instance();
	}

	function get_cart()
	{
		if (!$_SESSION['cartRecv'])
		{
			$this->set_cart(array());
		}
		
		return $_SESSION['cartRecv'];
		
		//if(!$this->CI->session->userdata('cartRecv'))
			//$this->set_cart(array());

		//return $this->CI->session->userdata('cartRecv');
	}

	function set_cart($cart_data)
	{
		$_SESSION['cartRecv']	=	$cart_data;
		//$this->CI->session->set_userdata('cartRecv',$cart_data);
	}

	function get_supplier()
	{
		if(!$this->CI->session->userdata('supplier'))
		{
			$this->set_supplier(-1);
			//$this->set_supplier($this->CI->config->item('default_supplier_id'));
		}

		return $this->CI->session->userdata('supplier');
	}

	function set_supplier($supplier_id)
	{
		$this->CI->session->set_userdata('supplier', $supplier_id);
	}

	function get_mode()
	{
		if(!$this->CI->session->userdata('recv_mode'))
			$this->set_mode('purchaseorder');

		return $this->CI->session->userdata('recv_mode');
	}

	function set_mode($mode)
	{
		$this->CI->session->set_userdata('recv_mode',$mode);
	}

	function add_item($item_id, $quantity=1, $price=null, $discount=0, $description=null, $serialnumber=null)
	{		
		//Get items in the receiving so far.
		$items = $this->get_cart();

        //We need to loop through all items in the cart.
        //If the item is already there, get it's key($updatekey).
        //We also need to get the next key that we are going to use in case we need to add the
        //item to the list. Since items can be deleted, we can't use a count. we use the highest key + 1.

        $maxkey=0;                       //Highest key so far
        $itemalreadyinsale=FALSE;        //We did not find the item yet.
		$insertkey=0;                    //Key to use for new entry.
		$updatekey=0;                    //Key to use to update(quantity)

		foreach ($items as $item)
		{            
            //We primed the loop so maxkey is 0 the first time.
            //Also, we have stored the key in the element itself so we can compare.
            //There is an array function to get the associated key for an element, but I like it better
            //like that!

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id']==$item_id)
			{
				$itemalreadyinsale=TRUE;
				$updatekey=$item['line'];
			}
		}

		// get the mode
		$mode = $this->get_mode();
		
		// get the item info
		$item_info														=	$this->CI->Item->get_info($item_id);
		//$item_info=$this->CI->Item->get_info_fils_rouge_1($item_id);
		$insertkey=$maxkey+1;

		//array records are identified by $insertkey and item_id is just another field.
		
		$item = array(($insertkey)=>
		array	(
				'item_id'												=>	$item_id,
				'line'													=>	$insertkey,
				'category'												=>	$item_info->category,
				'item_number'											=>	$item_info->item_number,
				'name'													=>	$item_info->name,
				'serialnumber'											=>	$serialnumber!=null ? $serialnumber: '',
				'allow_alt_description'									=>	$item_info->allow_alt_description,
				'is_serialized'											=>	$item_info->is_serialized,
				'ventes' => intval($_SESSION['ventes_for_approv_qty']),
				'quantity'												=>	$quantity,
				'discount'												=>	$discount,
				'price'													=>	$price
				)
		);
		
		//Item already exists
		if($itemalreadyinsale)
		{
			$items[$updatekey]['quantity']+=$quantity;
		}
		else
		{
			//add to existing array
			$items+=$item;
		}

		$this->set_cart($items);
		return true;

	}

	function add_item_by_date($item_id, $quantity=1, $price=null, $discount=0, $description=null, $serialnumber=null)
    {		
		//Get items in the receiving so far.
		$items = $this->get_cart();
        //We need to loop through all items in the cart.
        //If the item is already there, get it's key($updatekey).
        //We also need to get the next key that we are going to use in case we need to add the
        //item to the list. Since items can be deleted, we can't use a count. we use the highest key + 1.
        $maxkey=0;                       //Highest key so far
        $itemalreadyinsale=FALSE;        //We did not find the item yet.
		$insertkey=0;                    //Key to use for new entry.
		$updatekey=0;                    //Key to use to update(quantity)
		foreach ($items as $item)
		{            
            //We primed the loop so maxkey is 0 the first time.
            //Also, we have stored the key in the element itself so we can compare.
            //There is an array function to get the associated key for an element, but I like it better
            //like that!

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id']==$item_id)
			{
				$itemalreadyinsale=TRUE;
				$updatekey=$item['line'];
			}
		}
		// get the mode
		$mode = $this->get_mode();
		// get the item info
		//$item_info														=	$this->CI->Item->get_info($item_id);
		$item_info=$this->CI->Item->get_info_by_date_1($item_id);
		$insertkey=$maxkey+1;
		//array records are identified by $insertkey and item_id is just another field.
		$item = array(($insertkey)=>
		array	(
				'item_id'				=>	$item_id,
				'line'					=>	$insertkey,
				'category'				=>	$item_info->category,
				'item_number'			=>	$item_info->item_number,
				'name'					=>	$item_info->name,
				'serialnumber'			=>	$serialnumber!=null ? $serialnumber: '',
				'allow_alt_description'	=>	$item_info->allow_alt_description,
				'is_serialized'			=>	$item_info->is_serialized,
				'ventes' => intval($_SESSION['ventes_for_approv_qty']),
				
				//'ventes' => $_SESSION['ventes_for_approv_qty'],
				'quantity'				=>	intval($quantity),
				'discount'				=>	$discount,
        		'price'					=>	$price
		        )
		);
		//Item already exists
		if($itemalreadyinsale)
		{
			$items[$updatekey]['quantity']+=$quantity;
		}
		else
		{
			//add to existing array
			$items+=$item;
		}
		$this->set_cart($items);
		return true;
	}


	function edit_item($line,$description,$serialnumber,$quantity,$discount,$price)
	{
		$items = $this->get_cart();
		if(isset($items[$line]))
		{
			$items[$line]['description'] = $description;
			$items[$line]['serialnumber'] = $serialnumber;
			$items[$line]['quantity'] = $quantity;
			$items[$line]['discount'] = $discount;
			$items[$line]['price'] = $price;
			$this->set_cart($items);
		}

		return false;
	}

	function is_valid_receipt($receipt_receiving_id)
	{		
		//RECV #
		$pieces = explode('-',$receipt_receiving_id);

		if(count($pieces)==2)
		{
			return $this->CI->Receiving->exists($pieces[1]);
		}
		return false;
	}
	
	function is_valid_item_kit($item_kit_id)
	{
		//KIT #
		$pieces = explode('-',$item_kit_id);

		if(count($pieces)==2)
		{
			return $this->CI->Item_kit->exists($pieces[1]);
		}

		return false;
	}

	function return_entire_receiving($receipt_receiving_id)
	{
		// housekeeping
		$this															->	empty_cart();
		$this															->	delete_supplier();
		
		// get the parts of the receving id
		$pieces															=	explode('-',$receipt_receiving_id);
		$receiving_id 													=	$pieces[1];
		
		// get the mode and the code
		$transaction_subtype 											= 	$this->CI->session->userdata('recv_mode');
		$transaction_code 												=	strtoupper($pieces[0]);
		
		// get the multiplier in order to set the sign of the quantity correctly
		$transaction_multiplier											=	$this->CI->Transaction->get_transaction_multiplier($transaction_subtype, $transaction_code);
		
		// get transaction data
		$receiving_header												=	$this->CI->Receiving->get_receiving_header($receiving_id)->row();
		$receiving_items												=	$this->CI->Receiving->get_receiving_items($receiving_id)->result();
		
		// set the supplier
		$this->set_supplier($receiving_header->supplier_id);
		
		foreach($receiving_items as $row)
		{
			// Set the quantity depending on the type of document and what the user is trying to do (depends on mode)
			$row_quantity 												=	$row->quantity_purchased * $transaction_multiplier;
				
			// Finally add the item line				
			$this->add_item($row->item_id, $row_quantity, $row->item_cost_price, $row->discount_percent, $row->description, $row->serialnumber);
		}
		
		// load cart contents from userdata cartRecv in order to sort it
		$arr1															=	array();
		$arr1															=	$this->get_cart();
				
		// sort the array in category/description alpha order
		$cart_data														=	array();
		$cart_data	 													= 	$this->array_msort($arr1, array('category'=>SORT_DESC, 'name'=>SORT_DESC));
		
		// now set the cart
		$this															->	set_cart($cart_data);
		
		// set the comment and set it in the userdata for use in controllers/receivings/_reload
		$comment														=	$transaction_code.'-'.$receiving_id;
		$this->CI->session												->	set_userdata('comment', $comment);
	}
	
	function add_item_kit($external_item_kit_id)
	{
		//KIT #
		$pieces = explode('-',$external_item_kit_id);
		$item_kit_id = $pieces[1];
		
		foreach ($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$this->add_item($item_kit_item['item_id'], $item_kit_item['quantity']);
		}
	}

	function copy_entire_receiving($receiving_id)
	{
		$this															->	empty_cart();
		$this															->	delete_supplier();
		
		// get transaction data
		$receiving_header												=	$this->CI->Receiving->get_receiving_header($receiving_id)->row();
		$receiving_items												=	$this->CI->Receiving->get_receiving_items($receiving_id)->result();
		
		// read receiving items
		foreach($receiving_items as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_cost_price, $row->discount_percent, $row->description, $row->serialnumber);
		}
		$this->set_supplier($receiving_header->supplier_id);
	}
	
	function merge_receiving()
	{
		$this															->	empty_cart();
		$this															->	delete_supplier();
		
		// get transaction data from merge to
		$receiving_header												=	$this->CI->Receiving->get_receiving_header($_SESSION['merge_to'])->row();
		
		// get and load merge from items
		$receiving_items												=	$this->CI->Receiving->get_receiving_items($_SESSION['merge_from'])->result();
		foreach($receiving_items as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_cost_price, $row->discount_percent, $row->description, $row->serialnumber);
		}
		// get and load merge to items
		$receiving_items												=	$this->CI->Receiving->get_receiving_items($_SESSION['merge_to'])->result();
		foreach($receiving_items as $row)
		{
			$this->add_item($row->item_id, $row->quantity_purchased, $row->item_cost_price, $row->discount_percent, $row->description, $row->serialnumber);
		}
		
		// set supplier
		$this->set_supplier($receiving_header->supplier_id);
	}

	function delete_item($line)
	{
		$items															=	$this->get_cart();
		unset($items[$line]);
		$this															->	set_cart($items);
	}

	function empty_cart()
	{
		unset($_SESSION['cartRecv']);
		//$this->CI->session->unset_userdata('cartRecv');
	}

	function delete_supplier()
	{
		$this->CI->session												->	unset_userdata('supplier');
	}

	function clear_mode()
	{
		$this->CI->session												->	unset_userdata('receiving_mode');
	}

	function clear_all()
	{
		$this															->	clear_mode();
		$this															->	empty_cart();
		$this															->	delete_supplier();
		
		// reset show_dialog
		unset($_SESSION['show_dialog']);
		
		// unset stock action id
		unset($_SESSION['stock_action_id']);
		
		// unset confirm what
		unset($_SESSION['confirm_what']);
	}

	function get_total()
	{
		$total 															=	0;
		foreach($this->get_cart() as $item)
		{
            $total														+=	($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100);
		}
		
		return $total;
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
}
?>
