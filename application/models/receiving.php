<?php
class Receiving extends CI_Model
{
	function	get_info			($receiving_id)
	{
		$this						->	db->from('receivings');
		$this						->	db->where('receiving_id',$receiving_id);
		$this						->	db->where('receivings.branch_code', $this->config->item('branch_code'));
		return						$this->db->get();
	}
		
	function	exists				($receiving_id)
	{
		$this						->	db->from('receivings');
		$this						->	db->where('receiving_id',$receiving_id);
		$this						->	db->where('receivings.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();

		return						($query->num_rows()==1);
	}
	
	function	update				($transaction_data, $transaction_id)
	{
		$this						->	db->where('receiving_id', $transaction_id);
		$this						->	db->where('receivings.branch_code', $this->config->item('branch_code'));
		$success					=	$this->db->update('receivings',$transaction_data);
		
		return						$success;
	}

	function insert($transaction_data, $transaction_id =-1)
	{
		if(!$this->exists($transaction_id))
		{
			$sucess = $this->db->insert('receivings', $transaction_data);
			$sucess = $this->db->insert_id();
		}
		else
		{
			$sucess = 0;
		}
		return $sucess;
	}

	function update_items($transaction_data, $id)
	{
		if($id != -1)
		{
	    	$this						->	db->where('receiving_id', $id['receiving_id']);
	    	$this						->	db->where('item_id', $id['item_id']);
    
	    	$this						->	db->where('receivings_items.branch_code', $this->config->item('branch_code'));
	    	$success					=	$this->db->update('receivings_items',$transaction_data);
	    }
		if($id == -1)
		{
			$success = $this->db->insert('receivings_items', $transaction_data);
			$sucess = $this->db->insert_id();
		}

		return						$success;
	}

	function insert_items($transaction_data)
	{
		$sucess = $this->db->insert('receivings_items', $transaction_data);
		return $sucess;
	}

	function check_id_with_mode($mode)
	{
		$this->db->select('receivings.receiving_id');
		$this->db->from('receivings');
		$this->db->join('receivings_items', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->db->where('receivings.mode', $mode);
		$this->db->where('receivings_items.quantity_purchased < 0');
		$this->db->group_by('receivings.receiving_id');
		$this->db->order_by('receivings.receiving_id', 'DESC');
		$this->db->limit('1');

		return $this->db->get()->row();
	}



	//function	save				($items, $supplier_id, $employee_id, $comment, $payment_type, $receiving_id=false)
	//{
	//	// get the mode
	//	$transaction_subtype											=	$this->receiving_lib->get_mode();
//
	//	// check there is something to do!
	//	if(count($items)==0)
	//		return -1;
//
	//	// store data
	//	$receivings_data 			= array	(
	//										'supplier_id'				=>	$supplier_id,
	//										'employee_id'				=>	$employee_id,
	//										'payment_type'				=>	$payment_type,
	//										'comment'					=>	$comment,
	//										'mode' 						=> 	$transaction_subtype,
	//										'branch_code'				=>	$this->config->item('branch_code')
	//										);
//
	//	//Run these queries as a transaction, we want to make sure we do all or nothing
	//	$this															->	db->trans_start();
	//	$this															->	db->insert('receivings', $receivings_data);
	//	$receiving_id													=	$this->db->insert_id();
//
	//	foreach($items as $line=>$item)
	//	{
	//		// get item info at current state before update
	//		$cur_item_info												=	$this->Item->get_info($item['item_id']);
//
	//		// load output data
	//		$receivings_items_data 	=	array	(
	//											'receiving_id'			=>	$receiving_id,
	//											'item_id'				=>	$item['item_id'],
	//											'line'					=>	$item['line'],
	//											'description'			=>	$item['description'],
	//											'serialnumber'			=>	$item['serialnumber'],
	//											'quantity_purchased'	=>	$item['quantity'],
	//											'discount_percent'		=>	$item['discount'],
	//											'item_cost_price' 		=>	$item['price'],
	//											'item_unit_price'		=>	0,
	//											'branch_code'			=>	$this->config->item('branch_code')
	//											);
//
	//		// write output to file
	//		$this														->	db->insert('receivings_items', $receivings_items_data);	
	//		// get transaction updatestock indicator
	//		$transaction_updatestock 									=	$this->Transaction->get_transaction_updatestock($transaction_subtype);
	//		
	//		// update stock record if transaction allows update
	//		if ($transaction_updatestock == 'Y')
	//		{
	//			// update to item with receivings data - load update data
	//			$_SESSION['transaction_info']							=	new stdClass();
	//			$_SESSION['transaction_info']->item_id					=	$item['item_id'];
	//			$_SESSION['transaction_info']->quantity					=	$cur_item_info->quantity + $item['quantity'];
	//			$_SESSION['transaction_info']->rolling_inventory_indicator		=	0;
	//			// update the item record
	//			$this													->	Item->save();
	//			
	//			// update DLUO totals if DLUO is used on this item and this is a stock return
	//			if ($cur_item_info->dluo_indicator == 'Y' AND $transaction_subtype == 'stockreturns')
	//			{
	//				// get DLUO records
	//				$item_info_dluo										=	array();
	//				$item_info_dluo										=	$this->Item->get_info_dluo($item['item_id'])->result_array();
	//				
	//				// dluo remaining quantity is a counter which when zero stops the loop
	//				$dluo_remaining_qty									=	$item['quantity'];
//
	//				// read records
	//				foreach ($item_info_dluo as $row)
	//				{
	//					// test for qty left
	//					if ($dluo_remaining_qty != 0)
	//					{
	//						// test item qty against dluo qty this line
	//						if ($row['dluo_qty'] >= $dluo_remaining_qty)
	//						{
	//							// add item qty from dluo qty and update record (for  stock returns qty is -ve so need to add it)
	//							$new_dluo_qty							=	$row['dluo_qty'] + $dluo_remaining_qty;
	//							
	//							// update or delete record
	//							if ($new_dluo_qty > 0)
	//							{
	//								$dluo_data 							= 	array	(
	//																				'dluo_qty'	=>	$new_dluo_qty
	//																				);
	//								$this->Item							->	dluo_edit($row['year'], $row['month'], $dluo_data);
	//							}
	//							else
	//							{
	//								$this->Item							->	dluo_delete($row['year'], $row['month']);	
	//							}
	//							
	//							// zero the qty remaining
	//							$dluo_remaining_qty	=	0;	
	//						}
	//						else
	//						{
	//							// if here dluo qty is < qty remaining
	//							// calculate the new remaining qty
	//							$dluo_remaining_qty						=	$dluo_remaining_qty - $row['dluo_qty'];
	//							
	//							// now delete this dluo record
	//							$this->Item								->	delete_dluo($row['item_id'], $row['year'], $row['month']);
	//						}
	//					}
	//				}
	//			}
	//			
	//			// write stock valuation record - load output data
	//			$stock_value_data	=	array	(
	//											'value_item_id'			=>	$item['item_id'],
	//											'value_cost_price'		=>	$item['price'],
	//											'value_qty'				=>	$item['quantity'],
	//											'value_trans_id'		=>	$receiving_id,
	//											'branch_code'			=>	$this->config->item('branch_code')
	//											);
	//			// write record
	//			$this->Item												->	value_write($stock_value_data);
	//			
	//			// set stock after for history record
	//			$trans_stock_after										=	$_SESSION['transaction_info']->quantity;
	//		}
	//		else
	//		{
	//			$trans_stock_after										=	$cur_item_info->quantity;
	//		}
	//		
	//		// get the transaction code
	//		$transaction_code											=	$this->Transaction->get_transaction_code($transaction_subtype);
	//		
	//		// write inventory record - setup output data
	//		$recv_remarks												=	$transaction_code.$receiving_id.' - '.$_SESSION['title'];
	//		$inv_data 				=	array	(
	//											'trans_date'			=>	date('Y-m-d H:i:s'),
	//											'trans_items'			=>	$item['item_id'],
	//											'trans_user'			=>	$employee_id,
	//											'trans_comment'			=>	$recv_remarks,
	//											'trans_stock_before'	=>	$cur_item_info->quantity,
	//											'trans_inventory'		=>	$item['quantity'],
	//											'trans_stock_after'		=>	$trans_stock_after,
	//											'branch_code'			=>	$this->config->item('branch_code')
	//											);
	//		// write the inventor record	
	//		$this->Inventory											->	insert($inv_data);
	//	}
	//	
	//	$this															->	db->trans_complete();
	//	
	//	if ($this->db->trans_status() === FALSE)
	//	{
	//		return 															-1;
	//	}
//
	//	return 																$receiving_id;
	//}







//	function	save				($items, $supplier_id, $employee_id, $comment, $payment_type, $receiving_id=false)
//	{
//		// get the mode
//		$transaction_subtype											=	$this->receiving_lib->get_mode();
//
//		// check there is something to do!
//		if(count($items)==0)
//			return -1;
//
//		// store data
//		$receivings_data 			= array	(
//											'supplier_id'				=>	$supplier_id,
//											'employee_id'				=>	$employee_id,
//											'payment_type'				=>	$payment_type,
//											'comment'					=>	$comment,
//											'mode' 						=> 	$transaction_subtype,
//											'branch_code'				=>	$this->config->item('branch_code')
//											);
//
//		//Run these queries as a transaction, we want to make sure we do all or nothing
//		$this															->	db->trans_start();
//		$this															->	db->insert('receivings', $receivings_data);
//		$receiving_id													=	$this->db->insert_id();
//
//		foreach($items as $line=>$item)
//		{
//			// get item info at current state before update
//			$cur_item_info												=	$this->Item->get_info($item['item_id']);
//
//
//
//			// load output data
//			$receivings_items_data 	=	array	(
//												'receiving_id'			=>	$receiving_id,
//												'item_id'				=>	$item['item_id'],
//												'line'					=>	$item['line'],
//												'description'			=>	$item['description'],
//												'serialnumber'			=>	$item['serialnumber'],
//												'quantity_purchased'	=>	$item['quantity'],
//												'discount_percent'		=>	$item['discount'],
//												'item_cost_price' 		=>	$item['price'],
//												'item_unit_price'		=>	0,
//												'branch_code'			=>	$this->config->item('branch_code')
//												);
//
//			// write output to file
//			$this														->	db->insert('receivings_items', $receivings_items_data);
//			// get transaction updatestock indicator
//			$transaction_updatestock 									=	$this->Transaction->get_transaction_updatestock($transaction_subtype);
//			
//			// update stock record if transaction allows update
//			if ($transaction_updatestock == 'Y')
//			{
//				// update to item with receivings data - load update data
//				$_SESSION['transaction_info']							=	new stdClass();
//				$_SESSION['transaction_info']->item_id					=	$item['item_id'];
//				$_SESSION['transaction_info']->quantity					=	$cur_item_info->quantity + $item['quantity'];
//				$_SESSION['transaction_info']->rolling_inventory_indicator		=	0;
//				// update the item record
//				$this													->	Item->save();
//				
//				// update DLUO totals if DLUO is used on this item and this is a stock return
//				if ($cur_item_info->dluo_indicator == 'Y' AND $transaction_subtype == 'stockreturns')
//				{
//					// get DLUO records
//					$item_info_dluo										=	array();
//					$item_info_dluo										=	$this->Item->get_info_dluo($item['item_id'])->result_array();
//					
//					// dluo remaining quantity is a counter which when zero stops the loop
//					$dluo_remaining_qty									=	$item['quantity'];
//
//					// read records
//					foreach ($item_info_dluo as $row)
//					{
//						// test for qty left
//						if ($dluo_remaining_qty != 0)
//						{
//							// test item qty against dluo qty this line
//							if ($row['dluo_qty'] >= $dluo_remaining_qty)
//							{
//								// add item qty from dluo qty and update record (for  stock returns qty is -ve so need to add it)
//								$new_dluo_qty							=	$row['dluo_qty'] + $dluo_remaining_qty;
//								
//								// update or delete record
//								if ($new_dluo_qty > 0)
//								{
//									$dluo_data 							= 	array	(
//																					'dluo_qty'	=>	$new_dluo_qty
//																					);
//									$this->Item							->	dluo_edit($row['year'], $row['month'], $dluo_data);
//								}
//								else
//								{
//									$this->Item							->	dluo_delete($row['year'], $row['month']);	
//								}
//								
//								// zero the qty remaining
//								$dluo_remaining_qty	=	0;	
//							}
//							else
//							{
//								// if here dluo qty is < qty remaining
//								// calculate the new remaining qty
//								$dluo_remaining_qty						=	$dluo_remaining_qty - $row['dluo_qty'];
//								
//								// now delete this dluo record
//								$this->Item								->	delete_dluo($row['item_id'], $row['year'], $row['month']);
//							}
//						}
//					}
//				}
//				
//				if($cur_item_info->DynamicKit == 'Y')
//				{
//					$item_kit_items_info = $this->Item_kit->get_item_kit_items($item['item_id']);
//					
//					foreach($item_kit_items_info as $key => $value_item_kit_item)
//					{
//						$item_kit_item = $this->Item->get_info($value_item_kit_item['item_id']);
//						$_SESSION['transaction_info_item_kit_items'][$key]->item_id = $value_item_kit_item['item_id'];
//						$item_kit_item_price = $this->Item->get_all_info_item($value_item_kit_item['item_id']);
//						$_SESSION['transaction_info_item_kit_items'][$key]->price = $item_kit_item_price->supplier_cost_price;
//						$_SESSION['transaction_info_item_kit_items'][$key]->quantity = $item_kit_item->quantity + $value_item_kit_item['quantity'];
//						
//						// write stock valuation record - load output data
//				        $stock_value_data	=	array	(
//				        								'value_item_id'			=>	$value_item_kit_item['item_id'],
//				        								'value_cost_price'		=>	$item_kit_item_price->supplier_cost_price,
//				        								'value_qty'				=>	$_SESSION['transaction_info_item_kit_items'][$key]->quantity,
//				        								'value_trans_id'		=>	$receiving_id,
//				        								'branch_code'			=>	$this->config->item('branch_code')
//				        								);
//				        // write record
//				        $this->Item												->	value_write($stock_value_data);
//				        
//				        // set stock after for history record
//				        $_SESSION['transaction_info_item_kit_items'][$key]->quantitytrans_stock_after =	$_SESSION['transaction_info_item_kit_items'][$key]->quantity;
//					}
//					//$_SESSION['transaction_info_item_kit_items']->quantity = $item_kit_items_info->quantity + $item['quantity'];
//				}
//				else
//				{
//				    // write stock valuation record - load output data
//				    $stock_value_data	=	array	(
//				    								'value_item_id'			=>	$item['item_id'],
//				    								'value_cost_price'		=>	$item['price'],
//				    								'value_qty'				=>	$item['quantity'],
//				    								'value_trans_id'		=>	$receiving_id,
//				    								'branch_code'			=>	$this->config->item('branch_code')
//				    								);
//				    // write record
//				    $this->Item												->	value_write($stock_value_data);
//				    
//				    // set stock after for history record
//				    $trans_stock_after										=	$_SESSION['transaction_info']->quantity;
//			    }
//			}
//			else
//			{
//				$trans_stock_after										=	$cur_item_info->quantity;
//			}
//			
//			// get the transaction code
//			$transaction_code											=	$this->Transaction->get_transaction_code($transaction_subtype);
//			
//			// write inventory record - setup output data
//			$recv_remarks												=	$transaction_code.$receiving_id.' - '.$_SESSION['title'];
//			
//			if($cur_item_info->DynamicKit == 'Y')
//			{
//				foreach($_SESSION['transaction_info_item_kit_items'] as $key => $valu)
//				{
//					$item_kit_item = $this->Item->get_info($_SESSION['transaction_info_item_kit_items'][$key]->item_id);
//				
//			        $inv_data = array(
//			        					'trans_date'			=>	date('Y-m-d H:i:s'),
//			        					'trans_items'			=>	$_SESSION['transaction_info_item_kit_items'][$key]->item_id,
//			        					'trans_user'			=>	$employee_id,
//			        					'trans_comment'			=>	$recv_remarks,
//			        					'trans_stock_before'	=>	$cur_item_info->quantity,
//			        					'trans_inventory'		=>	$item['quantity'],
//			        					'trans_stock_after'		=>	$trans_stock_after,
//			        					'branch_code'			=>	$this->config->item('branch_code')
//			        					);
//			        // write the inventor record	
//			        $this->Inventory											->	insert($inv_data);
//			    }
//			}
//			else
//			{
//				$inv_data 				=	array	(
//					'trans_date'			=>	date('Y-m-d H:i:s'),
//					'trans_items'			=>	$item['item_id'],
//					'trans_user'			=>	$employee_id,
//					'trans_comment'			=>	$recv_remarks,
//					'trans_stock_before'	=>	$cur_item_info->quantity,
//					'trans_inventory'		=>	$item['quantity'],
//					'trans_stock_after'		=>	$trans_stock_after,
//					'branch_code'			=>	$this->config->item('branch_code')
//                	);
//                // write the inventor record	
//                $this->Inventory											->	insert($inv_data);
////			}
//		}
//		
//		$this															->	db->trans_complete();
//		
//		if ($this->db->trans_status() === FALSE)
//		{
//			return 															-1;
//		}
//
//		return 																$receiving_id;
//	}










   // function save_sav($items, )







	function	save				($items, $supplier_id, $employee_id, $comment, $payment_type, $receiving_id=false)
	{
		// get the mode
		$transaction_subtype											=	$this->receiving_lib->get_mode();

		// check there is something to do!
		if(count($items)==0)
			return -1;

		// store data
		$receivings_data 			= array	(
											'supplier_id'				=>	$supplier_id,
											'employee_id'				=>	$employee_id,
											'payment_type'				=>	$payment_type,
											'comment'					=>	$comment,
											'mode' 						=> 	$transaction_subtype,
											'branch_code'				=>	$this->config->item('branch_code')
											);
											
		if($transaction_subtype == 'suspended')
		{
			$receivings_data['number_day_sale'] = intval($_SESSION['historique_correct_stay']);
			$receivings_data['number_day_prevision_stock'] = intval($_SESSION['nbre_jour_prevision_stock_correct_stay']);
		}

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this															->	db->trans_start();
		$this															->	db->insert('receivings', $receivings_data);
		$receiving_id													=	$this->db->insert_id();

		foreach($items as $line=>$item)
		{
			// get item info at current state before update
			$cur_item_info												=	$this->Item->get_info($item['item_id']);

			if($cur_item_info->DynamicKit == 'Y')
			{
			    // load output data
			    $receivings_items_data 	=	array	(
			    	'receiving_id'			=>	$receiving_id,
			    	'item_id'				=>	$item['item_id'],
			    	'line'					=>	$item['line'],
			    	'description'			=>	$item['description'],
			    	'serialnumber'			=>	$item['serialnumber'],
			    	'quantity_purchased'	=>	$item['quantity'],
			    	'discount_percent'		=>	$item['discount'],
			    	'item_cost_price' 		=>	$item['price'],
			    	'item_unit_price'		=>	0,
			    	'branch_code'			=>	$this->config->item('branch_code')
			    	);

                // write output to file
                $this														->	db->insert('receivings_items', $receivings_items_data);
                // get transaction updatestock indicator
                $transaction_updatestock 									=	$this->Transaction->get_transaction_updatestock($transaction_subtype);
                
                // update stock record if transaction allows update
                if ($transaction_updatestock == 'Y')
                {
                    // update to item with receivings data - load update data
                    $_SESSION['transaction_info']							=	new stdClass();
                    $_SESSION['transaction_info']->item_id					=	$item['item_id'];
                    $_SESSION['transaction_info']->quantity					=	$cur_item_info->quantity + $item['quantity'];
                    $_SESSION['transaction_info']->rolling_inventory_indicator		=	0;
                    // update the item record
					$this													->	Item->save();
					$_SESSION['transaction_info_kit']->quantity = $_SESSION['transaction_info']->quantity;
			
					$item_kit_items_info = $this->Item_kit->get_item_kit_items($item['item_id']);
					foreach($item_kit_items_info as $key => $value)
					{
						$cur_item_kit_items_info								= $this->Item->get_info($value['item_id']);
						
						
						// update to item with receivings data - load update data
						$_SESSION['transaction_info']							=	new stdClass();
						$_SESSION['transaction_info']->item_id					=	$cur_item_kit_items_info->item_id;
						$_SESSION['transaction_info']->quantity					=	$item['quantity'] * intval($value['quantity']) + intval($cur_item_kit_items_info->quantity);
						$_SESSION['transaction_info']->rolling_inventory_indicator		=	0;
						// update the item record

						$_SESSION['transaction_info_kit_items'][$key] = new stdClass();
						$_SESSION['transaction_info_kit_items'][$key]->item_id = $cur_item_kit_items_info->item_id;
						$_SESSION['transaction_info_kit_items'][$key]->quantity_before = intval($cur_item_kit_items_info->quantity);
						$_SESSION['transaction_info_kit_items'][$key]->quantity_trans  = $item['quantity'] * intval($value['quantity']);
						$_SESSION['transaction_info_kit_items'][$key]->quantity_after  = $_SESSION['transaction_info']->quantity;
						
						$this													->	Item->save();
					}
                          // update DLUO totals if DLUO is used on this item and this is a stock return
                          if ($cur_item_info->dluo_indicator == 'Y' AND $transaction_subtype == 'stockreturns')
                          {
                               // get DLUO records
                               $item_info_dluo										=	array();
                               $item_info_dluo										=	$this->Item->get_info_dluo($item['item_id'])->result_array();
                               
                               // dluo remaining quantity is a counter which when zero stops the loop
                               $dluo_remaining_qty									=	$item['quantity'];
                         
                               // read records
                               foreach ($item_info_dluo as $row)
                               {
                               // test for qty left
                               if ($dluo_remaining_qty != 0)
                               {
                                    // test item qty against dluo qty this line
                                    if ($row['dluo_qty'] >= $dluo_remaining_qty)
                                    {
                                         // add item qty from dluo qty and update record (for  stock returns qty is -ve so need to add it)
                                         $new_dluo_qty							=	$row['dluo_qty'] + $dluo_remaining_qty;
                                         
                                         // update or delete record
                                         if ($new_dluo_qty > 0)
                                         {
                                        	    $dluo_data 							= 	array	(
                                        	    												'dluo_qty'	=>	$new_dluo_qty
                                        	    												);
                                        	    $this->Item							->	dluo_edit($row['year'], $row['month'], $dluo_data);
                                         }
                                         else    
                                         {    
                                           	$this->Item							->	dluo_delete($row['year'], $row['month']);	
                                         }
                 
                                        // zero the qty remaining
                                        $dluo_remaining_qty	=	0;	
                                    }
                                    else
                                    {
                                        // if here dluo qty is < qty remaining
                                        // calculate the new remaining qty
                                        $dluo_remaining_qty						=	$dluo_remaining_qty - $row['dluo_qty'];
                                        
                                        // now delete this dluo record
                                        $this->Item								->	delete_dluo($row['item_id'], $row['year'], $row['month']);
                                    }
                                }
                                }
                            }

            //// write stock valuation record - load output data
            //$stock_value_data	=	array	(
            //					'value_item_id'			=>	$item['item_id'],
            //					'value_cost_price'		=>	$item['price'],
            //					'value_qty'				=>	$item['quantity'],
            //					'value_trans_id'		=>	$receiving_id,
            //					'branch_code'			=>	$this->config->item('branch_code')
            //					);
            //// write record
            //$this->Item												->	value_write($stock_value_data);
			
			$item_kit_items_info = $this->Item_kit->get_item_kit_items($item['item_id']);
			foreach($item_kit_items_info as $key => $value)
			{
				$cur_item_kit_items_info				= $this->Item->get_all_info_item($value['item_id']);

                // write stock valuation record - load output data
                $stock_value_data	=	array	(
                					'value_item_id'			=>	$cur_item_kit_items_info->item_id,
                					'value_cost_price'		=>	$cur_item_kit_items_info->supplier_cost_price,
                					'value_qty'				=>	$cur_item_kit_items_info->quantity,
                					'value_trans_id'		=>	$receiving_id,
                					'branch_code'			=>	$this->config->item('branch_code')
                					);
                // write record
                $this->Item												->	value_write($stock_value_data);
			}

            // set stock after for history record
            $trans_stock_after										=	$_SESSION['transaction_info_kit']->quantity;

            }
            else
            {
            $trans_stock_after										=	$cur_item_info->quantity;
            }
            
            // get the transaction code
            $transaction_code											=	$this->Transaction->get_transaction_code($transaction_subtype);
            
            // write inventory record - setup output data
            $recv_remarks												=	$transaction_code.$receiving_id.' - '.$_SESSION['title'];
            

            $inv_data 				=	array	(
            'trans_date'			=>	date('Y-m-d H:i:s'),
            'trans_items'			=>	$item['item_id'],
            'trans_user'			=>	$employee_id,
            'trans_comment'			=>	$recv_remarks,
            'trans_stock_before'	=>	$cur_item_info->quantity,
            'trans_inventory'		=>	$item['quantity'],
            'trans_stock_after'		=>	$trans_stock_after,
            'branch_code'			=>	$this->config->item('branch_code')
            );
            // write the inventor record	
            $this->Inventory											->	insert($inv_data);
			
			$item_kit_items_info = $this->Item_kit->get_item_kit_items($item['item_id']);
			foreach($_SESSION['transaction_info_kit_items'] as $key => $value)
			{
				$cur_item_kit_items_info				= $this->Item->get_info($value->item_id);

				
				$inv_data 				=	array	(
					'trans_date'			=>	date('Y-m-d H:i:s'),
					'trans_items'			=>	$value->item_id,
					'trans_user'			=>	$employee_id,
					'trans_comment'			=>	$recv_remarks,
					'trans_stock_before'	=>	$value->quantity_before,
					'trans_inventory'		=>	$value->quantity_trans,
					'trans_stock_after'		=>	$value->quantity_after,
					'branch_code'			=>	$this->config->item('branch_code')
					);
					// write the inventor record	
					$this->Inventory											->	insert($inv_data);
			}
            }
			else
			{
            // load output data
			$receivings_items_data 	=	array	(
												'receiving_id'			=>	$receiving_id,
												'item_id'				=>	$item['item_id'],
												'line'					=>	$item['line'],
												'description'			=>	$item['description'],
												'serialnumber'			=>	$item['serialnumber'],
												'quantity_purchased'	=>	$item['quantity'],
												'discount_percent'		=>	$item['discount'],
												'item_cost_price' 		=>	$item['price'],
												'item_unit_price'		=>	0,
												'branch_code'			=>	$this->config->item('branch_code')
												);

			// write output to file
			$this														->	db->insert('receivings_items', $receivings_items_data);
			// get transaction updatestock indicator
			$transaction_updatestock 									=	$this->Transaction->get_transaction_updatestock($transaction_subtype);
			
			// update stock record if transaction allows update
			if ($transaction_updatestock == 'Y')
			{
				// update to item with receivings data - load update data
				$_SESSION['transaction_info']							=	new stdClass();
				$_SESSION['transaction_info']->item_id					=	$item['item_id'];
				$_SESSION['transaction_info']->quantity					=	$cur_item_info->quantity + $item['quantity'];
				$_SESSION['transaction_info']->rolling_inventory_indicator		=	0;
				// update the item record
				$this													->	Item->save();

				// update DLUO totals if DLUO is used on this item and this is a stock return
				if ($cur_item_info->dluo_indicator == 'Y' AND $transaction_subtype == 'stockreturns')
				{
					// get DLUO records
					$item_info_dluo										=	array();
					$item_info_dluo										=	$this->Item->get_info_dluo($item['item_id'])->result_array();
					
					// dluo remaining quantity is a counter which when zero stops the loop
					$dluo_remaining_qty									=	$item['quantity'];

					// read records
					foreach ($item_info_dluo as $row)
					{
						// test for qty left
						if ($dluo_remaining_qty != 0)
						{
							// test item qty against dluo qty this line
							if ($row['dluo_qty'] >= $dluo_remaining_qty)
							{
								// add item qty from dluo qty and update record (for  stock returns qty is -ve so need to add it)
								$new_dluo_qty							=	$row['dluo_qty'] + $dluo_remaining_qty;
								
								// update or delete record
								if ($new_dluo_qty > 0)
								{
									$dluo_data 							= 	array	(
																					'dluo_qty'	=>	$new_dluo_qty
																					);
									$this->Item							->	dluo_edit($row['year'], $row['month'], $dluo_data);
								}
								else
								{
									$this->Item							->	dluo_delete($row['year'], $row['month']);	
								}
								
								// zero the qty remaining
								$dluo_remaining_qty	=	0;	
							}
							else
							{
								// if here dluo qty is < qty remaining
								// calculate the new remaining qty
								$dluo_remaining_qty						=	$dluo_remaining_qty - $row['dluo_qty'];
								
								// now delete this dluo record
								$this->Item								->	delete_dluo($row['item_id'], $row['year'], $row['month']);
							}
						}
					}
				}
			/*	
				if($cur_item_info->DynamicKit == 'Y')
				{
					$item_kit_items_info = $this->Item_kit->get_item_kit_items($item['item_id']);
					
					foreach($item_kit_items_info as $key => $value_item_kit_item)
					{
						$item_kit_item = $this->Item->get_info($value_item_kit_item['item_id']);
						$_SESSION['transaction_info_item_kit_items'][$key]->item_id = $value_item_kit_item['item_id'];
						$item_kit_item_price = $this->Item->get_all_info_item($value_item_kit_item['item_id']);
						$_SESSION['transaction_info_item_kit_items'][$key]->price = $item_kit_item_price->supplier_cost_price;
						$_SESSION['transaction_info_item_kit_items'][$key]->quantity = $item_kit_item->quantity + $value_item_kit_item['quantity'];
						
						// write stock valuation record - load output data
				        $stock_value_data	=	array	(
				        								'value_item_id'			=>	$value_item_kit_item['item_id'],
				        								'value_cost_price'		=>	$item_kit_item_price->supplier_cost_price,
				        								'value_qty'				=>	$_SESSION['transaction_info_item_kit_items'][$key]->quantity,
				        								'value_trans_id'		=>	$receiving_id,
				        								'branch_code'			=>	$this->config->item('branch_code')
				        								);
				        // write record
				        $this->Item												->	value_write($stock_value_data);
				        
				        // set stock after for history record
				        $_SESSION['transaction_info_item_kit_items'][$key]->quantitytrans_stock_after =	$_SESSION['transaction_info_item_kit_items'][$key]->quantity;
					}
					//$_SESSION['transaction_info_item_kit_items']->quantity = $item_kit_items_info->quantity + $item['quantity'];
				}
				else
				{*/
				    // write stock valuation record - load output data
				    $stock_value_data	=	array	(
				    								'value_item_id'			=>	$item['item_id'],
				    								'value_cost_price'		=>	$item['price'],
				    								'value_qty'				=>	$item['quantity'],
				    								'value_trans_id'		=>	$receiving_id,
				    								'branch_code'			=>	$this->config->item('branch_code')
				    								);
				    // write record
				    $this->Item												->	value_write($stock_value_data);
				    
				    // set stock after for history record
				    $trans_stock_after										=	$_SESSION['transaction_info']->quantity;
			  //  }
			}
			else
			{
				$trans_stock_after										=	$cur_item_info->quantity;
			}
			
			// get the transaction code
			$transaction_code											=	$this->Transaction->get_transaction_code($transaction_subtype);
			
			// write inventory record - setup output data
			$recv_remarks												=	$transaction_code.$receiving_id.' - '.$_SESSION['title'];
			
			/*if($cur_item_info->DynamicKit == 'Y')
			{
				foreach($_SESSION['transaction_info_item_kit_items'] as $key => $valu)
				{
					$item_kit_item = $this->Item->get_info($_SESSION['transaction_info_item_kit_items'][$key]->item_id);
				
			        $inv_data = array(
			        					'trans_date'			=>	date('Y-m-d H:i:s'),
			        					'trans_items'			=>	$_SESSION['transaction_info_item_kit_items'][$key]->item_id,
			        					'trans_user'			=>	$employee_id,
			        					'trans_comment'			=>	$recv_remarks,
			        					'trans_stock_before'	=>	$cur_item_info->quantity,
			        					'trans_inventory'		=>	$item['quantity'],
			        					'trans_stock_after'		=>	$trans_stock_after,
			        					'branch_code'			=>	$this->config->item('branch_code')
			        					);
			        // write the inventor record	
			        $this->Inventory											->	insert($inv_data);
			    }
*/
				$inv_data 				=	array	(
					'trans_date'			=>	date('Y-m-d H:i:s'),
					'trans_items'			=>	$item['item_id'],
					'trans_user'			=>	$employee_id,
					'trans_comment'			=>	$recv_remarks,
					'trans_stock_before'	=>	$cur_item_info->quantity,
					'trans_inventory'		=>	$item['quantity'],
					'trans_stock_after'		=>	strval($trans_stock_after),
					'branch_code'			=>	$this->config->item('branch_code')
                	);
                // write the inventor record	
                $this->Inventory											->	insert($inv_data);
            }
		}
		
		$this															->	db->trans_complete();
		
		if ($this->db->trans_status() === FALSE)
		{
			return 															-1;
		}

		return 																$receiving_id;
	}


























	
	function	delete				($transaction_id)
	{
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this					->	db->trans_start();
		
		$this					->	db->delete	('receivings_items',	array	(
																				'receiving_id'	=>	$transaction_id,
																				'branch_code'	=>	$this->config->item('branch_code')
																				)
												); 
		$this					->	db->delete	('receivings',			array	(
																				'receiving_id'	=>	$transaction_id,
																				'branch_code'	=>	$this->config->item('branch_code')
																				)
												); 
		
		$this					->	db->trans_complete();
				
		return 					$this->db->trans_status();
	}

	function	get_receiving_items		($receiving_id)
	{
		$this					->	db->from('receivings_items');
        $this                   ->  db->join('items', "receivings_items.item_id=items.item_id",'INNER');
		$this					->	db->where('receiving_id',$receiving_id);
		$this					->	db->where('receivings_items.branch_code', $this->config->item('branch_code'));
		$this                   ->  db->order_by('items.category', 'desc');
		$this                   ->  db->order_by('items.name', 'desc');
		
		return 					$this->db->get();
	}

	function	get_receiving_header	($receiving_id)
	{
		$this					->	db->from('receivings');
		$this					->	db->where('receiving_id',$receiving_id);
		$this					->	db->where('receivings.branch_code', $this->config->item('branch_code'));
		
		return 					$this->db->get();
	}
	
	function	get_supplier	($receiving_id)
	{
		$this					->	db->from('receivings');
		$this					->	db->where('receiving_id',$receiving_id);
		$this					->	db->where('receivings.branch_code', $this->config->item('branch_code'));
		$row = $this->db->get()->row();
		return 					$row ? $this->Supplier->get_info($row->supplier_id) : false;
	}

	function	get_all_by_mode	($mode)
	{
		$this					->	db->from('receivings');
		$this					->	db->where('mode',$mode);
		$this					->	db->where('receivings.branch_code', $this->config->item('branch_code'));
		return 					$this->db->get();
	}
	
	function	update_mode		($transaction_data, $mode)
	{
		$this						->	db->where('mode', $mode);
		$this						->	db->where('receivings.branch_code', $this->config->item('branch_code'));
		$success					=	$this->db->update('receivings', $transaction_data);
		
		return						$success;
	}
	
	function update_purchaseorder($id)
	{
		$this->db->where('receiving_id', $id);
        $this->db->update('receivings', array('mode' => 'attach'));	
	}

	function load_remote_stock()
	{
		//load /var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv for remote_stock
		$this->db->query("truncate table `ospos_remote_stock`;");
		$this->db->query("load data LOW_PRIORITY infile '/var/www/html/wrightetmathon/STOCK/STOCK_LOGISTIQUE.csv' into table ospos_remote_stock FIELDS TERMINATED BY ';' IGNORE 1 LINES;");
		$this->db->query('UPDATE `ospos_items` SET `ospos_items`.`quantity_central` = 0');
		$this->db->query('UPDATE  `ospos_items` ,`ospos_remote_stock` SET `ospos_items`.`quantity_central` = `ospos_remote_stock`.`STOCK_DISPONIBLE` WHERE `ospos_items`.`item_number` = `ospos_remote_stock`.`CODE_PRODUIT` AND `ospos_remote_stock`.`STOCK_DISPONIBLE` > 0');
	}

	function get_info_transaction_type($inputs)
	{
		$this->db->from('transaction_type');

		foreach($inputs as $key => $input)
		{
			$this->db->where($key, $input);
		}
		
		$data = $this->db->get()->result_array();
		return $data;
	}

	function get_info_with_where($inputs)
	{
		if($this->exists($inputs['receiving_id']))
		{		
		    $this->db->from('receivings');
		    $this->db->join('receivings_items', 'receivings.receiving_id = receivings_items.receiving_id');
		    $this->db->where($inputs['where']);
    
		    return $this->db->get()->result_array();
	    }
	}

	function get_max_line_for_receivings_items($id)
	{
		$this->db->select('MAX(`ospos_receivings_items`.`line`) as max');
		$this->db->from('receivings');
		$this->db->join('receivings_items', 'receivings.receiving_id = receivings_items.receiving_id');
		$this->db->where('receivings.receiving_id', $id);

		return $this->db->get()->row();
	}
}
?>
