<?php
class Item extends CI_Model
{
	/*
	Determines if a given item_id is an item
	*/
	function	exists				($item_id)
	{
		$this						->	db->from('items');
		$this						->	db->where('item_id',$item_id);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by('items.category');
		$this						->	db->order_by('items.name', 'desc' );
		$query						=	$this->db->get();

		return 						($query->num_rows()==1);
	}

	/*
	Returns all the items
	*/
	function	get_all				($limit=100000, $offset=0)
	{
		$this															->	db->from('items');
		//$this															->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id', 'LEFT');
		$this															->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		
		// select records based on whether normal or undelete mode
		switch ($_SESSION['undel'])
		{
			case	1:
					$this												->	db->where('items.deleted', 1);
			break;
			
			case	2:
					$this												->	db->where('items.deleted', 0);
					$this												->	db->where('items_suppliers.supplier_reorder_policy', 'Y');
					$this												->	db->where('items_suppliers.supplier_id', $_SESSION['supplier_id']);
			break;
			
			case	3:
					$this												->	db->where('items.deleted', 0);
					$this												->	db->where('dluo_indicator', 'Y');
			break;
			
			default:
					$this												->	db->where('items.deleted', 0);
			break;
		}
		
		// set order by if set
		if (isset($_SESSION['order_by']) AND isset($_SESSION['sequence']))
		{
			$this														->	db->order_by($_SESSION['order_by'], $_SESSION['sequence']);
			unset($_SESSION['order_by']);
			unset($_SESSION['sequence']);
		}	
		else
		{
			$this														->	db->order_by("name", "asc");
		}
			
		$this															->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this															->	db->limit($limit);
		$this															->	db->offset($offset);
		return 															$this->db->get();
	}

/*    //Fonctionne bien

	//Pour récupérer tous les item_id et les quantités des articles vendus entre 2 dates:
	function get_all_between_2_date($inputs)
	{
		//
		$this->db->select('
			sales.sale_id,
    sales_items.item_id,
    sales_items.quantity_purchased
		');
		
		$this->db->from("sales_items");
		$this->db->join("sales", "sales_items.sale_id = sales.sale_id");
		$this->db->where("date(sale_time) BETWEEN '". $inputs['date_start']. "' and '" . $inputs['date_end']."'" );

//        $data=$this->db->get_result();
		//return $this->db->get()->result(); //row_array();	  //$data;
		$data = $this->db->get()->result_array();
		return $data;
	}//*/

    //Test pour obtenir les item_id distinct et la somme des quantity_purchased d'un même article
	function get_all_between_2_date($inputs)
	{
		$this->db->select('
			sales.sale_id,
			sales_items.item_id,
    sum(`ospos_sales_items`.`quantity_purchased`) AS somme
	');

		$this->db->from("sales_items");

		$this->db->join("sales", "sales_items.sale_id = sales.sale_id");
		$this ->db->join('items_suppliers', 'sales_items.item_id = items_suppliers.item_id');

		$this->db->where("date(sale_time) BETWEEN '". $inputs['date_start']. "' and '" . $inputs['date_end']."'" );
		$this ->db->where('items_suppliers.supplier_preferred', 'Y');
		$this ->db->where('items_suppliers.supplier_reorder_policy', 'Y');
		$this ->db->where('items_suppliers.supplier_id', $_SESSION['supplier_id']);

		$this->db->group_by('sales.sale_id');

		$this->db->order_by('sales_items.line_category', "DESC");
		$this->db->order_by('sales_items.description', "DESC");
		
		$data = $this->db->get()->result_array();
		return $data;
	}

	function get_info_by_date($item_id)
	{
		$this ->db->from('items');
		$this ->db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');

		$this ->db->where('items.item_id',$item_id);
		$this ->db->where('items.deleted', 0);
		$this ->db->where('items_suppliers.supplier_reorder_policy', 'Y');
		$this ->db->where('items_suppliers.supplier_preferred', 'Y');
		$this ->db->where('items_suppliers.supplier_id', $_SESSION['supplier_id']);
		$this ->db->where('items.branch_code', $this->config->item('branch_code'));

		$this ->db->order_by('items.category', 'desc');
		$this ->db->order_by('items.name', 'desc' );

		$data = $this->db->get()->result_array();
		return $data;
	}

	function get_info_supplier_cost_price_by_date($item_id)
	{
		$this ->db->select('supplier_cost_price');
		$this ->db->from('items_suppliers');

		$this ->db->where('items_suppliers.item_id',$item_id);
		$this ->db->where('items_suppliers.supplier_reorder_policy', 'Y');
		$this ->db->where('items_suppliers.supplier_id', $_SESSION['supplier_id']);

		$data = $this->db->get()->result_array();
		return $data;
	}

	/*function	get_all_by_date				($limit=100000, $offset=0)
	{
		$this															->	db->from('items');
		$this															->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		

		// select records based on whether normal or undelete mode
		switch ($_SESSION['undel'])
		{
			case	1:
					$this												->	db->where('items.deleted', 1);
			break;
			
			case	2:
					$this												->	db->where('items.deleted', 0);
					$this												->	db->where('items_suppliers.supplier_reorder_policy', 'Y');
					$this												->	db->where('items_suppliers.supplier_id', $_SESSION['supplier_id']);
			break;
			
			case	3:
					$this												->	db->where('items.deleted', 0);
					$this												->	db->where('dluo_indicator', 'Y');
			break;
			
			default:
					$this												->	db->where('items.deleted', 0);
			break;
		}
		
		// set order by if set
		if (isset($_SESSION['order_by']) AND isset($_SESSION['sequence']))
		{
			$this														->	db->order_by($_SESSION['order_by'], $_SESSION['sequence']);
			unset($_SESSION['order_by']);
			unset($_SESSION['sequence']);
		}	
		else
		{
			$this														->	db->order_by("name", "asc");
		}
			
		$this															->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this															->	db->limit($limit);
		$this															->	db->offset($offset);
		return 															$this->db->get();
	}
	//*/
	/*
	Returns all the items
	*/
	function	get_remote_quantities($limit=10000, $offset=0)
	{
		// initialise
		$port															=	3306;
		$timeout														=	6;
		$wrightetmathon['dbdriver'] 									= 	'mysqli';
		$wrightetmathon['dbprefix']										= 	'ospos_';
		$wrightetmathon['pconnect']										= 	TRUE;
		$wrightetmathon['db_debug']										= 	TRUE;
		$wrightetmathon['cache_on']										= 	FALSE;
		$wrightetmathon['cachedir']										=	'';
		$wrightetmathon['char_set']										= 	'utf8';
		$wrightetmathon['dbcollat']										=	'utf8_general_ci';
		$wrightetmathon['swap_pre']										=	'';
		$wrightetmathon['autoinit']										=	TRUE;
		$wrightetmathon['stricton']										=	FALSE;
		
		// get the branch codes and database connections
		$branches														=	new stdClass();
		$branches														=	$this->Branch->get_all()->result_array();

		// read the branches
		foreach ($branches as $branch)
		{
			// test for self and ignore if so
			if ($branch['branch_code'] != $this->config->item('branch_code'))
			{				
				// Check stock only for branches which allow check
				if ($branch['branch_allows_check'] == 'Y')
				{
					// ignore where branch_ip is null AND where branch_ip is local machine
					if (!empty($branch['branch_ip']) AND $branch['branch_ip'] != '127.0.0.1')
					{
						// ping remote machine box. Mostly the IP in branches is the IP of the box. 
						// If a box it must be configured to port forward 3306 to POS machine = redirection
						// if return_var = 0 = box/machine is available
						$ping_command									=	"ping -c2 -n -W1 ".$branch['branch_ip'];
						unset($output);
						unset($return_var);
						exec($ping_command, $output, $return_var);
						if ($return_var == 0) 
						{
							// set up remote db connection parameters
							$wrightetmathon['hostname'] =	trim($branch['branch_ip']);
							$wrightetmathon['username'] =	trim($branch['branch_user']);
							$wrightetmathon['password'] =	trim($branch['branch_password']);
							$wrightetmathon['database'] =	trim($branch['branch_database']);
						
							// check machine is available using mysqli because load->database will throw an error if machine not available which cannot be caught
							$link = mysqli_init();
							mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 2);

							if (mysqli_real_connect($link, $wrightetmathon['hostname'], $wrightetmathon['username'], $wrightetmathon['password'], $wrightetmathon['database']))
							{
								// close link because load->database will open another one
								mysqli_close($link);
								
								// connect to database
								$DB1										=	$this->load->database($wrightetmathon, TRUE);

								// run the query
								$DB1										->	select('quantity');
								$DB1										->	from('items');
								$DB1										->	where('item_number', $_SESSION['transaction_info']->item_number);
								$DB1										->	where('deleted', 0);
								$DB1										->	where('items.branch_code', $branch['branch_code']);
                                $DB1                                        ->  order_by('items.category');
		                        $DB1										->	db->order_by('items.name', 'desc' );
								$query										=	$DB1->get();
								
								// close the DB
								$DB1->close();

								// Check success
								if($query->num_rows()==1)
								{				
									$qty = $query->row();
									$_SESSION['remote_quantities'][$branch['branch_description']]['qty'] = $qty->quantity;
									$_SESSION['remote_quantities'][$branch['branch_description']]['des'] = '';
								}
								else
								{
									$_SESSION['remote_quantities'][$branch['branch_description']]['qty'] = 0;
									$_SESSION['remote_quantities'][$branch['branch_description']]['des'] = $this->lang->line('branches_not_stocked');
								}
							}
							else
							{
								$_SESSION['remote_quantities'][$branch['branch_description']]['qty'] = 0;
								$_SESSION['remote_quantities'][$branch['branch_description']]['des'] = $this->lang->line('branches_offline');
							}
						}
					}
				}
			}
		}

		return;
	}
	
	function	count_all			()
	{
		$this						->	db->from('items');
		
		// select records based on whether normal or undelete mode
		switch ($_SESSION['undel'])
		{
			case	1:
					$this			->	db->where('deleted', 1);
			break;
			
			default:
					$this			->	db->where('deleted', 0);
			break;
		}

		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
        $this                       ->  db->order_by('items.description', 'desc');
		$this					->	db->order_by('items.name', 'desc' );

		return 						$this->db->count_all_results();
	}
	
	function	get_info			($item_id)
	{
		$this						->	db->from('items');
		$this						->	db->where('item_id',$item_id);
		//$this						->	db->where('deleted',0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
        $this                       ->  db->order_by('items.category', 'desc');
		$this					->	db->order_by('items.name', 'desc' );
		$query 					=	$this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = $this->db->list_fields('items');

			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}
			
			return $item_obj;
		}
	}
	
	function get_info_by_date_1($item_id)
	{
		$this						->	db->from('items');

		$this ->db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');

		$this						->	db->where('items.item_id',$item_id);
		$this ->db->where('items_suppliers.supplier_reorder_policy', 'Y');
		$this ->db->where('items_suppliers.supplier_preferred', 'Y');
		$this ->db->where('items_suppliers.supplier_id', $_SESSION['supplier_id']);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));

		$this                       ->  db->order_by('items.category', 'desc');
		$this					->	db->order_by('items.name', 'desc' );

		$query 					=	$this->db->get();

		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//Get empty base parent object, as $item_id is NOT an item
			$item_obj=new stdClass();

			//Get all the fields from items table
			$fields = $this->db->list_fields('items');

			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}
			
			return $item_obj;
		}
	}


	function	get_info_dluo		($item_id)
	{
		$this						->	db->from('items_dluo');
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('items_dluo.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("year", "asc");
		$this						->	db->order_by("month", "asc");
		return 							$this->db->get();
	}
	
	
	function	get_dluo_record		($dluo_data=array())
	{
		$this						->	db->from('items_dluo');
		$this						->	db->where('item_id', $dluo_data['item_id']);
		$this						->	db->where('year', $dluo_data['year']);
		$this						->	db->where('month', $dluo_data['month']);
		$this						->	db->where('items_dluo.branch_code', $this->config->item('branch_code'));
		return 							$this->db->get()->row_array();
	}
	
	function	get_kit_reference	($kit_reference)
	{
		$this						->	db->from('items');
		$this						->	db->where('kit_reference', $kit_reference);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
        $this                       ->  db->order_by('items.category', 'desc');
		$this					->	db->order_by('items.name', 'desc' );

		return 							$this->db->get();
	}
	
	function	dluo_edit			($year, $month, $dluo_data)
	{
		$this						->	db->where('item_id', $_SESSION['transaction_info']->item_id);
		$this						->	db->where('year', $year);
		$this						->	db->where('month', $month);
		$this						->	db->where('items_dluo.branch_code', $this->config->item('branch_code'));
		return 							$this->db->update('items_dluo', $dluo_data);
	}
	
	function	dluo_add			($dluo_data)
	{
		
		return 						$this->db->insert('items_dluo', $dluo_data);
	}
	
	function	dluo_delete			($year, $month)
	{
		$this						->	db->where('item_id', $_SESSION['transaction_info']->item_id);
		$this						->	db->where('year', $year);
		$this						->	db->where('month', $month);
		$this						->	db->where('items_dluo.branch_code', $this->config->item('branch_code'));
		return 							$this->db->delete('items_dluo');
	}
	
	function	supplier_delete		($item_id, $supplier_id)
	{
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('supplier_id', $supplier_id);
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		return 							$this->db->delete('items_suppliers');
	}
	
	function	warehouse_delete	($item_id, $warehouse_code, $warehouse_row, $warehouse_section, $warehouse_shelf, $warehouse_bin)
	{
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('warehouse_code', $warehouse_code);
		$this						->	db->where('warehouse_row', $warehouse_row);
		$this						->	db->where('warehouse_section', $warehouse_section);
		$this						->	db->where('warehouse_shelf', $warehouse_shelf);
		$this						->	db->where('warehouse_bin', $warehouse_bin);
		$this						->	db->where('items_locations.branch_code', $this->config->item('branch_code'));
		return 							$this->db->delete('items_locations');
	}
	
	function	pricelist_delete		($item_id, $pricelist_id)
	{
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('pricelist_id', $pricelist_id);
		$this						->	db->where('items_pricelists.branch_code', $this->config->item('branch_code'));
		return 							$this->db->delete('items_pricelists');
	}				
	
	function	get_kit_structure	($kit_reference)
	{
		$this						->	db->from('kit_structure');
		$this						->	db->where('kit_structure.kit_reference', $kit_reference);
		$this						->	db->where('kit_structure.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("kit_option", "asc");
		return 							$this->db->get();
	}	
	
	function	get_kit_detail		($kit_reference)
	{
		$this						->	db->from('kit_detail');
		$this						->	db->where('kit_detail.kit_reference', $kit_reference);
		$this						->	db->where('kit_detail.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("kit_option", "asc");
		return 							$this->db->get();
	}	

	function	get_kit_structure_option	($kit_reference, $kit_option)
	{
		$this						->	db->from('kit_structure');
		$this						->	db->where('kit_structure.kit_reference', $kit_reference);
		$this						->	db->where('kit_structure.kit_option', $kit_option);
		$this						->	db->where('kit_structure.branch_code', $this->config->item('branch_code'));
		return 							$this->db->get();
	}

	function	get_kit_detail_option	($kit_reference, $kit_option)
	{
		$this						->	db->from('kit_detail');
		$this						->	db->where('kit_detail.kit_reference', $kit_reference);
		$this						->	db->where('kit_detail.kit_option', $kit_option);
		$this						->	db->where('kit_detail.branch_code', $this->config->item('branch_code'));
		return 							$this->db->get();
	}
	
	function	get_kit_item		($kit_reference, $kit_item_number)
	{
		$this						->	db->from('kit_detail');
		$this						->	db->where('kit_detail.kit_reference', $kit_reference);
		$this						->	db->where('kit_detail.item_number', $kit_item_number);
		$this						->	db->where('kit_detail.branch_code', $this->config->item('branch_code'));
		return 							$this->db->get();
	}
	
	function	add_kit_structure	($kit_structure_data)
	{
		return 							$this->db->insert('kit_structure', $kit_structure_data);
	}
	
	function	add_kit_detail		($kit_detail_data)
	{
		return 							$this->db->insert('kit_detail', $kit_detail_data);
	}
	
	function	delete_kit_structure($kit_reference, $kit_option)
	{
		// if top level kit_structure is deleted then delete all associated detail lines also
		// perform as a set
		$this						->	db->trans_start();
		
		// delete from the kit structure file
		$this						->	db->from('kit_structure');
		$this						->	db->where('kit_structure.kit_reference', $kit_reference);
		$this						->	db->where('kit_structure.kit_option', $kit_option);
		$this						->	db->where('kit_structure.branch_code', $this->config->item('branch_code'));
		$this						->	db->delete('kit_structure');
		
		// delete from the kit detail file
		$this						->	db->from('kit_detail');
		$this						->	db->where('kit_detail.kit_reference', $kit_reference);
		$this						->	db->where('kit_detail.kit_option', $kit_option);
		$this						->	db->where('kit_detail.branch_code', $this->config->item('branch_code'));
		$this						->	db->delete('kit_detail');
		
		// and commit
		$this						->	db->trans_complete();
				
		// return the status of the commit
		return 						$this->db->trans_status();
	}
	
	function	delete_kit_detail	($kit_reference, $kit_option, $item_number)
	{	
		// perform as a set
		$this						->	db->trans_start();
		
		// delete from the kit detail file
		$this						->	db->from('kit_detail');
		$this						->	db->where('kit_detail.kit_reference', $kit_reference);
		$this						->	db->where('kit_detail.kit_option', $kit_option);
		$this						->	db->where('kit_detail.item_number', $item_number);
		$this						->	db->where('kit_detail.branch_code', $this->config->item('branch_code'));
		$this						->	db->delete('kit_detail');
		
		// and commit
		$this						->	db->trans_complete();
				
		// return the status of the commit
		return 						$this->db->trans_status();
	}
	
	/*
	Get an item id given an item number
	*/
	function	get_item_id			($item_number)
	{
		$this						->	db->from('items');
		$this						->	db->where('item_number', $item_number);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
        $this                       ->  db->order_by('items.category', 'desc');
		$this					->	db->order_by('items.name', 'desc' );

		$query						=	$this->db->get();

		if($query->num_rows() == 1)
		{
			return 					$query->row()->item_id;
		}

		return 						false;
	}

	/*
	Gets information about multiple items
	*/
	function	get_multiple_info	($item_ids)
	{
		$this						->	db->from('items');
		$this						->	db->where_in('item_id',$item_ids);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
        $this                       ->  db->order_by('items.category', 'desc');
		$this					->	db->order_by('items.name', 'desc' );
		return 						$this->db->get();
	}

	/*
	Inserts or updates a item
	*/
	function	save				()
	{
		// test new item
		switch ($_SESSION['new'])
		{
			// add item
			case	1:
					if (!$this->db->insert('items', $_SESSION['transaction_info']))
					{
						// error inserting = set message
						$_SESSION['error_code']		=	'01000';
						redirect("items");
					}
					$_SESSION['transaction_info']->item_id	=	$this->db->insert_id();
			break;
			
			default:
					$this->db->where('item_id', $_SESSION['transaction_info']->item_id);
					$this->db->where('branch_code', $this->config->item('branch_code'));

					if 	(!$this->db->update('items', $_SESSION['transaction_info']))
					{
						// error updating = set message
						$_SESSION['error_code']		=	'01000';
						redirect("items");
					}
			break;
		}
		return;
	}
	
	function	save_supplier		()
	{
		$this						->	db->insert('items_suppliers', $_SESSION['transaction_add_supplier_info']);
	}
	
	function	update_supplier		($item_id, $supplier_id)
	{
		$this						->	db->where('items_suppliers.supplier_id', $supplier_id);
		$this						->	db->where('items_suppliers.item_id', $item_id);
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update	('items_suppliers', array	(
																					'supplier_reorder_policy'		=>	$_SESSION['transaction_update_supplier_info']->supplier_reorder_policy,
																					'supplier_reorder_pack_size' 	=>	$_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size,
																					'supplier_min_order_qty' 		=>	$_SESSION['transaction_update_supplier_info']->supplier_min_order_qty,
																					'supplier_min_stock_qty' 		=>	$_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty
																					)
														);
	}
	
	function	save_warehouse		()
	{
		$this						->	db->insert('items_locations', $_SESSION['transaction_add_warehouse_info']);
	}
	
	function	save_pricelist		()
	{
		$this						->	db->insert('items_pricelists', $_SESSION['transaction_add_pricelist_info']);
	}

	function	update_pricelist	($item_id, $pricelist_id)
	{
		$this						->	db->where('items_pricelists.pricelist_id', $pricelist_id);
		$this						->	db->where('items_pricelists.item_id', $item_id);
		$this						->	db->where('items_pricelists.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update	('items_pricelists', array	(
																					'unit_price_with_tax'	=>	$_SESSION['transaction_update_pricelist_info']->unit_price_with_tax,
																					'unit_price' 			=>	$_SESSION['transaction_update_pricelist_info']->unit_price
																					)
														);
	}
		
	/*
	Updates multiple items at once
	*/
	function	update_multiple		($item_data, $item_ids)
	{
		$this						->	db->where_in('item_id', $item_ids);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update('items', $item_data);
	}

	/*
	Update all items
	*/
	function	update_all			($item_data, $where_select)
	{
		$this						->	db->where($where_select);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update('items', $item_data);
	}

	/*
	Deletes one item
	*/
	function	delete				($item_id)
	{
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update('items', array('deleted' => 1));
	}

	/*
	Undeletes one item
	*/
	function	undelete			()
	{
		$this						->	db->where('item_id', $_SESSION['transaction_info']->item_id);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update('items', array('deleted' => 0));
	}

	/*
	Deletes a list of items
	*/
	function	delete_list			($item_ids)
	{
		$this						->	db->where_in('item_id',$item_ids);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		return 						$this->db->update('items', array('deleted' => 1));
 	}

 	/*
	Get search suggestions to find items
	*/
	function	get_search_suggestions($search, $limit=50)				// used in items module
	{				
		// Initialise
		$suggestions 													=	array();
		
		// set up search keys
		$search_array													=	explode('&', $search);
		
		// search by category
		$this															->	db->from('items');
		$this															->	db->join('categories', 'items.category_id = categories.category_id');
		$this															->	db->where('items.branch_code', $this->config->item('branch_code'));
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this												->	db->where('items.deleted', 1);
			break;
			
			default:
					$this												->	db->where('items.deleted', 0);
			break;
		}
		foreach ($search_array as $search_element)
		{
			$this														->	db->like('categories.category_name', $search_element);
		}
		$this															->	db->order_by("categories.category_name", "asc");
		$this															->	db->order_by("categories.category_name", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		foreach($by_category->result() as $row)
		{
			// test to see if item is already in suggestions, only add if not
			if(!array_search($row->name, $suggestions)) 
			{
				$suggestions[]											=	$row->name;
			}
		}
		
		// search by item number
		$this						->	db->from('items');
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('item_number', $search_element);
		}
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_item_number				=	$this->db->get();
		foreach($by_item_number->result() as $row)
		{
			// test to see if item is already in suggestions, only add if not
			if(!array_search($row->item_number, $suggestions)) 
			{
				$suggestions[]		=	$row->item_number;
			}
		}
		
		// search by name
		$this						->	db->from('items');
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('name', $search_element);
		}
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_name 					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			// test to see if item is already in suggestions, only add if not
			if(!array_search($row->name, $suggestions)) 
			{
				$suggestions[]		=	$row->name;
			}
		}
		
		// search by barcode
		$this						->	db->from('items');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('items_suppliers.supplier_bar_code', $search_element);
		}
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_name 					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			// test to see if item is already in suggestions, only add if not
			if(!array_search($row->supplier_bar_code, $suggestions)) 
			{
				$suggestions[]		=	$row->supplier_bar_code;
			}
		}
		
		// Search by supplier_item_number
		$this						->	db->from('items');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('items_suppliers.supplier_item_number', $search_element);
		}
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			// test to see if item is already in suggestions, only add if not
			if(!array_search($row->supplier_item_number, $suggestions)) 
			{
				$suggestions[]		=	$row->supplier_item_number;
			}
		}
		
		// only return $limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions			=	array_slice($suggestions, 0, $limit);
		}
		return 										$suggestions;

	}

	function get_item_search_suggestions($search, $limit=50)			// used by sales module
	{
		// initialise
		$suggestions 				=	array();
		
		// set up search keys
		$search_array				=	explode('&', $search);

		// search by name
		$this						->	db->from('items');
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('name', $search_element);
		}
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			if(!array_search($row->item_id.'|'.$row->name, $suggestions)) 
			{
				$suggestions[]		=	$row->item_id.'|'.$row->name;
			}
		}

		// search by item number
		$this						->	db->from('items');
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		$this						->	db->where('category !=', 'DEFECT');
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('item_number', $search_element);
		}
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_item_number				=	$this->db->get();
		foreach($by_item_number->result() as $row)
		{
			if(!array_search($row->item_id.'|'.$row->name, $suggestions)) 
			{
				$suggestions[]		=	$row->item_id.'|'.$row->name;
			}
		}
		
		// search by barcode
		$this						->	db->from('items');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		$this						->	db->where('category !=', 'DEFECT');
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('items_suppliers.supplier_bar_code', $search_element);
		}
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_bar_code				=	$this->db->get();
		foreach($by_bar_code->result() as $row)
		{
			if(!array_search($row->item_id.'|'.$row->name, $suggestions)) 
			{
				$suggestions[]		=	$row->item_id.'|'.$row->name;
			}
		}
		
		// search by supplier item number
		$this						->	db->from('items');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('items_suppliers.supplier_item_number', $search_element);
		}
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_supplier_number			=	$this->db->get();
		foreach($by_supplier_number->result() as $row)
		{
			if(!array_search($row->item_id.'|'.$row->name, $suggestions)) 
			{
				$suggestions[]		=	$row->item_id.'|'.$row->name;
			}
		}
				
		//limit suggestions
		if(count($suggestions > $limit))
		{
			$suggestions 			=	array_slice($suggestions, 0, $limit);
		}

		return 						$suggestions;
	}

	function	get_category_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('category');
		$this						->	db->from('items');
		$this						->	db->like('category', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("categories.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->category;
		}

		return 						$suggestions;
	}

/** GARRISON ADDED 5/18/2013 **/	
	function	get_location_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('location');
		$this						->	db->from('items');
		$this						->	db->like('location', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->location;
		}
	
		return 						$suggestions;
	}

	function	get_custom1_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom1');
		$this						->	db->from('items');
		$this						->	db->like('custom1', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom1;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom2_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom2');
		$this						->	db->from('items');
		$this						->	db->like('custom2', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom2;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom3_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom3');
		$this						->	db->from('items');
		$this						->	db->like('custom3', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom3;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom4_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom4');
		$this						->	db->from('items');
		$this						->	db->like('custom4', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom4;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom5_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom5');
		$this						->	db->from('items');
		$this						->	db->like('custom5', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom5;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom6_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom6');
		$this						->	db->from('items');
		$this						->	db->like('custom6', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom6;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom7_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom7');
		$this						->	db->from('items');
		$this						->	db->like('custom7', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom7;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom8_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom8');
		$this						->	db->from('items');
		$this						->	db->like('custom8', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom8;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom9_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom9');
		$this						->	db->from('items');
		$this						->	db->like('custom9', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom9;
		}
	
		return 						$suggestions;
	}
	
	function	get_custom10_suggestions($search)
	{
		$suggestions				=	array();
		$this						->	db->distinct();
		$this						->	db->select('custom10');
		$this						->	db->from('items');
		$this						->	db->like('custom10', $search);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("items.category", "desc");
		$this					->	db->order_by('items.name', 'desc' );
		$by_category				=	$this->db->get();
		foreach($by_category->result() as $row)
		{
			$suggestions[]			=	$row->custom10;
		}
	
		return 						$suggestions;
	}

	/*
	Perform a search on items
	*/
	function	search				($search)							// used in items module to show table of found items
	{				
		// Please note that all these queries must be of same structure otherwise the UNION at end of this method will not work.
		
		// set up search keys
		$search_array				=	explode('&', $search);		
		
		// search using item id
		$this						->	db->from('items');
		$this						->	db->join('categories', 'items.category_id = categories.category_id');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		$this						->	db->where('items_suppliers.supplier_preferred', 'Y');
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('items.item_id', $search_element);
		}
		
		$query_id					=	$this->db->get();
		$subQuery_id				=	$this->db->last_query();
		
		// search using category
		$this						->	db->from('items');
		$this						->	db->join('categories', 'items.category_id = categories.category_id');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		$this						->	db->where('items_suppliers.supplier_preferred', 'Y');
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
				
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('categories.category_name', $search_element);
		}
		
		$query_cat					=	$this->db->get();
		$subQuery_cat				=	$this->db->last_query();
		
		// search using name
		$this						->	db->from('items');
		$this						->	db->join('categories', 'items.category_id = categories.category_id');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		$this						->	db->where('items_suppliers.supplier_preferred', 'Y');
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('name', $search_element);
		}
		
		$query_nam					=	$this->db->get();
		$subQuery_nam				=	$this->db->last_query();
		
		// search using item number
		$this						->	db->from('items');
		$this						->	db->join('categories', 'items.category_id = categories.category_id');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		$this						->	db->where('items_suppliers.supplier_preferred', 'Y');
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('item_number', $search_element);
		}
		
		$query_ite					=	$this->db->get();
		$subQuery_ite				=	$this->db->last_query();
		
		// search using barcode
		$this						->	db->from('items');
		$this						->	db->join('categories', 'items.category_id = categories.category_id');
		$this						->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		$this						->	db->where('items_suppliers.supplier_preferred', 'Y');
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		
		switch ($_SESSION['undel'])
		{
			case 	1:
					$this			->	db->where('items.deleted', 1);
			break;
			
			default:
					$this			->	db->where('items.deleted', 0);
			break;
		}
		
		foreach ($search_array as $search_element)
		{
			$this					->	db->like('items_suppliers.supplier_bar_code', $search_element);
		}
		
		$query_bar					=	$this->db->get();
		$subQuery_bar				=	$this->db->last_query();
		
		// now run the queries
		$sql						=	"($subQuery_id)".' UNION '."($subQuery_cat)".' UNION '."($subQuery_nam)".' UNION '."($subQuery_ite)".' UNION '."($subQuery_bar)".' ORDER BY `name` ASC, `category` ASC';
		$query						=	$this->db->query($sql);

		return 						$query;	
	}

	function	get_categories		()
	{
		$this						->	db->select('category');
		$this						->	db->from('items');
		$this						->	db->where('deleted',0);
		$this						->	db->where('items.branch_code', $this->config->item('branch_code'));
		$this						->	db->distinct();
		$this						->	db->order_by("items.description", "desc");
		$this					->	db->order_by('items.name', 'desc' );

		return 						$this->db->get();
	}
	
	function	get_preferred_supplier		()
	{
		$this						->	db->from('items_suppliers');
		$this						->	db->where('item_id', $_SESSION['transaction_info']->item_id);
		$this						->	db->where('supplier_preferred', 'Y');
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));

		return 						$this->db->get();
	}
	
	function	get_default_pricelist		()
	{
		$this						->	db->from('items_pricelists');
		$this						->	db->where('item_id', $_SESSION['transaction_info']->item_id);
		$this						->	db->where('pricelist_id', $this->config->item('pricelist_id'));
		$this						->	db->where('items_pricelists.branch_code', $this->config->item('branch_code'));

		return 						$this->db->get();
	}
	
	function	value_write			($data)
	{
		$this->db->set('value_date', 'NOW()', FALSE);
		$this->db->insert('stock_valuation', $data);
	}
	
	function	value_edit			($value_date, $value_item_id, $value_data)
	{
		$this						->	db->where('value_date', $value_date);
		$this						->	db->where('value_item_id', $value_item_id);
		$this						->	db->where('stock_valuation.branch_code', $this->config->item('branch_code'));
		return 							$this->db->update('stock_valuation', $value_data);
	}
	
	function	value_delete		($value_date, $value_item_id)
	{
		$this						->	db->where('value_date', $value_date);
		$this						->	db->where('value_item_id', $value_item_id);
		$this						->	db->where('stock_valuation.branch_code', $this->config->item('branch_code'));
		return 							$this->db->delete('stock_valuation');
	}	
	
	function	value_delete_item_id($value_item_id)
	{
		$this						->	db->where('value_item_id', $value_item_id);
		$this						->	db->where('stock_valuation.branch_code', $this->config->item('branch_code'));
		return 							$this->db->delete('stock_valuation');
	}	
	
	function	value_delete_all	()
	{
		$this->db->empty_table('stock_valuation');
	}
	
	function	value_update		($value_remaining_qty, $item_id, $cost_price, $value_trans_id)
	{
		// test for -ne qty = return = just write a record
		if ($value_remaining_qty < 0)
		{
			$data					=	array	();
			$data					=	array	(
												'value_item_id'		=>	$item_id,
												'value_cost_price'	=>	$cost_price,
												'value_qty'			=>	-1 * $value_remaining_qty,
												'value_trans_id'	=>	$value_trans_id,
												'branch_code'		=>	$this->config->item('branch_code')
												);
			$this					->	value_write($data);
		}
		else
		{
			// get stock valuation records
			$stock_value_data		=	array();
			$stock_value_data		=	$this->Stock_queries->get_stock_value_data($item_id);
					
			// read records
			foreach ($stock_value_data as $row)
			{
				// test for qty left
				if ($value_remaining_qty != 0)
				{
					// test item qty against value qty this line
					if ($row['value_qty'] >= $value_remaining_qty)
					{
						// subtract item qty from dluo qty and update record
						$new_value_qty		=	$row['value_qty'] - $value_remaining_qty;
						
						// update or delete record
						if ($new_value_qty > 0)
						{
							$value_data 	= 	array	(
														'value_qty'	=>	$new_value_qty
														);
							$this->value_edit($row['value_date'], $row['value_item_id'], $value_data);
						}
						else
						{
							$this->value_delete($row['value_date'], $row['value_item_id']);	
						}
			
						// zero the qty remaining
						$value_remaining_qty	=	0;	
					}
					else
					{
						// if here value qty is < qty remaining
						// calculate the new remaining qty
						$value_remaining_qty	=	$value_remaining_qty - $row['value_qty'];
							
						// now delete this value record
						$this->value_delete($row['value_date'], $row['value_item_id']);	
					}
				}
			}
		}
	}
	
	// common check duplicate
	function 	check_duplicate		($item_id, $item_number)
	{		
		$results					=	array();
		
		switch ($_SESSION['new'])
		{
			case	1:
					$this			->	db->from('items');
					$this			->	db->where('item_number', $item_number);
					$this			->	db->where('items.branch_code', $this->config->item('branch_code'));
		            $this						->	db->order_by("items.category", "desc");
		            $this					->	db->order_by('items.name', 'desc' );
					$results		=	$this->db->get()->result_array();
			break;
			
			default:
					$this			->	db->from('items');
					$this			->	db->where('item_id != '.$item_id);
					$this			->	db->where('item_number', $item_number);
					$this			->	db->where('items.branch_code', $this->config->item('branch_code'));
		            $this						->	db->order_by("items.category", "desc");
		            $this					->	db->order_by('items.name', 'desc' );
					$results		=	$this->db->get()->result_array();
			break;
		}

		// if there is a record then category is duplicate
		if (count($results) 					> 0)
		{
			$success							=	FALSE;
		}
		else
		{
			$success							=	TRUE;
		}
			
		return									$success;
	}
	
	function	get_info_warehouses			()
	{
		$this						->	db->from('items_locations');
		$this						->	db->where('item_id', $_SESSION['transaction_info']->item_id);
		$this						->	db->where('items_locations.branch_code', $this->config->item('branch_code'));
		$query 						=	$this->db->get();

		return $query->result();
	}
	
	function	get_info_suppliers			()
	{
		$this						->	db->from('items_suppliers');
		$this						->	db->where('item_id', $_SESSION['transaction_info']->item_id);
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		$query 						=	$this->db->get();

		return $query->result();
	}
	
	function	check_supplier_item_number_duplicate()
	{
		$this						->	db->from('items_suppliers');
		$this						->	db->where('supplier_id', $_SESSION['transaction_add_supplier_info']->supplier_id);
		$this						->	db->where('supplier_item_number', $_SESSION['transaction_add_supplier_info']->supplier_item_number);
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function	check_supplier_bar_code_duplicate()
	{
		$this						->	db->from('items_suppliers');
		$this						->	db->where('supplier_id', $_SESSION['transaction_add_supplier_info']->supplier_id);
		$this						->	db->where('supplier_bar_code', $_SESSION['transaction_add_supplier_info']->supplier_bar_code);
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function	item_supplier_combo_exists()
	{
		$this						->	db->from('items_suppliers');
		$this						->	db->where('item_id', $_SESSION['transaction_add_supplier_info']->item_id);
		$this						->	db->where('supplier_id', $_SESSION['transaction_add_supplier_info']->supplier_id);
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function	item_supplier_preferred_unique()
	{
		$this						->	db->from('items_suppliers');
		$this						->	db->where('item_id', $_SESSION['transaction_add_supplier_info']->item_id);
		$this						->	db->where('supplier_preferred', $_SESSION['transaction_add_supplier_info']->supplier_preferred);
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function	item_get_cost($item_id)
	{
		// get preferred supplier for this item.			
	    $_SESSION['transaction_info']->item_id							=	$item_id;
	    $preferred_supplier_data										=	array();
		$preferred_supplier_data										=	$this->get_preferred_supplier()->result_array();
		// if preferred supplier found
		if (!empty($preferred_supplier_data))
		{
			// set cost
			$item_cost													=	$preferred_supplier_data[0]['supplier_cost_price'];
		}
		// if no preferred supplier get default supplier
		else
		{
			$default_supplier_data										=	array();
			$default_supplier_data										=	$this->item_supplier_get($item_id, $this->config->item('default_supplier_id'));
			// if default supplier found
			if (!empty($default_supplier_data))
			{
				// calculate cost
				$item_cost												=	$default_supplier_data['supplier_cost_price'];
			}
			// else $item_cost = 0
			else
			{
				$item_cost												=	0;
			}
		}
		
		// return
		return															$item_cost;
	}
	
	
	function	item_supplier_preferred_y()
	{
		$this						->	db->from('items_suppliers');
		$this						->	db->where('item_id', $_SESSION['transaction_add_supplier_info']->item_id);
		$this						->	db->where('supplier_preferred', 'Y');
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function	item_supplier_get($item_id, $supplier_id)
	{
		$this						->	db->from('items_suppliers');
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('supplier_id', $supplier_id);
		$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			return NULL;
		}
	}

    function	item_supplier_get_cost($item_id)
    {
        $this						->	db->from('items_suppliers');
        $this						->	db->where('item_id', $item_id);
      //  $this						->	db->where('supplier_id', $supplier_id);
        $this						->	db->where('items_suppliers.supplier_preferred', 'Y');
        $this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
        $query						=	$this->db->get();
        if($query->num_rows()==1)
        {
            return $query->row();
        }
        else
        {
            return NULL;
        }
    }
	function	item_warehouse_preferred_y()
	{
		$this						->	db->from('items_locations');
		$this						->	db->where('item_id', $_SESSION['transaction_add_warehouse_info']->item_id);
		$this						->	db->where('location_preferred', 'Y');
		$this						->	db->where('items_locations.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function	item_warehouse_preferred_unique()
	{
		$this						->	db->from('items_locations');
		$this						->	db->where('item_id', $_SESSION['transaction_add_warehouse_info']->item_id);
		$this						->	db->where('location_preferred', $_SESSION['transaction_add_warehouse_info']->location_preferred);
		$this						->	db->where('items_locations.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function	delete_all_items_suppliers()
	{
		$this						->	db->empty_table('items_suppliers');
	}
	
	function	get_info_pricelists			()
	{
		$this						->	db->from('items_pricelists');
		$this						->	db->where('item_id', $_SESSION['transaction_info']->item_id);
		$this						->	db->where('items_pricelists.branch_code', $this->config->item('branch_code'));
		$query 						=	$this->db->get();

		return $query->result();
	}
	
	function	item_pricelist_combo_exists()
	{
		$this						->	db->from('items_pricelists');
		$this						->	db->where('item_id', $_SESSION['transaction_add_pricelist_info']->item_id);
		$this						->	db->where('pricelist_id', $_SESSION['transaction_add_pricelist_info']->pricelist_id);
		$this						->	db->where('items_pricelists.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function	get_info_item_price($item_id, $pricelist_id)
	{
		$this						->	db->from('items_pricelists');
		$this						->	db->where('item_id', $item_id);
		$this						->	db->where('pricelist_id', $pricelist_id);
		$this						->	db->where('items_pricelists.branch_code', $this->config->item('branch_code'));
		$query 						=	$this->db->get();

		return $query->result();
	}
	
	/*
	Deletes item data from a pricelist
	*/
	function delete_pricelist_data($pricelist_id)
	{
		$this						->	db->where('pricelist_id', $pricelist_id);
		$this						->	db->where('items_pricelists.branch_code', $this->config->item('branch_code'));
		return							$this->db->delete('items_pricelists');
		
	}
	
	// load global bulk actions pick list
	function load_bulk_actions_pick_list()
	{
		// initialise
		unset($_SESSION['G']->bulk_actions_pick_list);
		$bulk_actions_pick_list											=	array();
		
		// create filename
		$file_name														=	"/var/www/html/wrightetmathon/application/definitions/items_bulk_actions.def";
		
		// open definitions file
		$fp																=	fopen($file_name, 'r');
				
		// test if file was opened
		if (!$fp)
		{
			$_SESSION['error_code']										=	'05680';
			$_SESSION['substitution_parms']								=	array($filename);
			redirect("items");
		}
		
		// test EOF
		while(!feof($fp)) 
		{
			// read a line
			$line														=	fgets($fp);
			
			// ignore comment lines
			if (strpos($line, "//") === false) 
			{
				// explode the line to find the fields required
				// $bulk_actions[0] = bulk_action_id
				// $bulk_actions[1] = bulk_action_name
				// $bulk_actions[2] = bulk_action_language_file
				// $bulk_actions[3] = deleted
				// $bulk_actions[4] = unused, should be blank
				$bulk_actions											=	explode("->", $line);
				
				// test active
				if ($bulk_actions[3] == '0')
				{
					// load the bulk actions pick list
					$bulk_actions_pick_list[$bulk_actions[0]] 			=	$this->lang->line($bulk_actions[2].'_'.$bulk_actions[1]);
				}
			}
		}
		
		// at EOF close the file
		fclose($fp);
		
		// load the bulk action pick list to the session
		$_SESSION['G']->bulk_actions_pick_list							=	$bulk_actions_pick_list;
		
		// return
		return;
	}
	
	// load items table columns $_SESSION['M']->items_table_column_pick_list
	function items_table_column_pick_list($sql, $conn)
	{
		// initialise
		$items_table_column_pick_list									=	array();
		unset($_SESSION['M']->items_table_column_pick_list);
		
		// run the query to get table column names
		$result															=	$conn->query($sql);
		
		// test the result
		if ($result->num_rows > 0)
		{
			// load the column pick list
			while($row = mysqli_fetch_assoc($result)) 
			{
				// test each column to see if it is required by looking for its translation. If translation found, then it is required
				$translated_field_name							=	'';
				$translated_field_name							=	$this->lang->line('items_column_'.$row['col_name'].'_'.$_SESSION['bulk_action_id']);
				if (!empty($translated_field_name))
				{
					// get the decription for this column
					$column_description							=	'';
					$column_description							=	$this->lang->line($translated_field_name);
					// strip off the database name in the tab_name - second element will hold the prefix_table name
					$pieces 									=	explode("/", $row['tab_name']);
					// now strip off the table prefix - second element will hold the table name
					$pieces 									=	explode("_", $pieces[1]);
					// set up the associative array with back ticks - I can then use the key directly in the sql
					$items_table_column_pick_list[$pieces[1].'.'.$row['col_name']]	=	$column_description;
				}
			}
		}

		$_SESSION['M']->items_table_column_pick_list					=	$items_table_column_pick_list;

		return;
	}
	
	// select items that will be updated by bulk update
	function bulk_select()
	{
		// construct the query
		$this															->	db->from('items');
		$this															->	db->join('categories', 'items.category_id = categories.category_id');
		$this															->	db->join('items_suppliers', 'items.item_id = items_suppliers.item_id');
		$this															->	db->join('suppliers', 'ospos_items_suppliers.supplier_id = ospos_suppliers.person_id');
		$this															->	db->join('items_pricelists', 'items.item_id = items_pricelists.item_id');
		$this															->	db->join('pricelists', 'items_pricelists.pricelist_id = pricelists.pricelist_id');
		//$this															->	db->order_by("item_number", "RANDOM");
		$this															->	db->order_by("item_number", "DESC");
		
//		$this -> db -> where('items_suppliers.supplier_preferred', 'Y');
        //$this						->	db->where('items_suppliers.supplier_preferred', 'Y');
		// set up first where statement if sql_where_1 clause is not NULL
		if ($_SESSION['sql_where_1'] != NULL)
		{
			$this														->	db->where($_SESSION['sql_where_1']);
		}
		
		// now set up second where statement if both sql_where_1 and sql_where_2 are not NULL 
		if ($_SESSION['sql_where_1'] != NULL AND $_SESSION['sql_where_2'] != NULL)
		{
			// set the where depending on the and_or clause selected by the user
			switch ($_SESSION['bulk_data']['3'])
			{
			// AND
			case	'AND':
				$this													->	db->where($_SESSION['sql_where_2']);
				break;
			// OR
			case	'OR':
				$this													->	db->or_where($_SESSION['sql_where_2']);
				break;
			}
		}
		else
		{
			// else check that sql_where_2 is not NULL and set where clause if not.
			// this is done because the user could enter the second value only and not the first 
			// value.
			if ($_SESSION['sql_where_2'] != NULL)
			{
				$this													->	db->where($_SESSION['sql_where_2']);
			} 
		}
		
		// now add the bulk_action_id specific where
		switch ($_SESSION['bulk_action_id'])
		{
			case 20:
				$this													->	db->where($_SESSION['sql_pricelist']);
				break;
			case 30:
				$this													->	db->where($_SESSION['sql_reorderpolicy']);
				break;
			
			case 60:
			    $this													->	db->where($_SESSION['sql_reorderpolicy']);
			default:
				break;
		}			

		// now add the branch selection and the deleted selection
		$this															->	db->where($_SESSION['sql_branch']);
		$this															->	db->where($_SESSION['sql_deleted']);
		
		// return the query to select rows that will be affected
		return															$this->db->get();
	}
}
?>
