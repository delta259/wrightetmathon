<?php
class Items extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"15";
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);

		// initialise
		$data															=	array();

		// set list title if undelete
		switch ($_SESSION['undel'])
		{
			case	1:
					$data['title']										=	$this->lang->line('common_undelete');
					$_SESSION['controller_name']=$_SESSION['controller_name']. '_desactives';
			break;

			default:
					$data['title']										=	'';
					$_SESSION['controller_name']=$_SESSION['controller_name']. '_actifs';
					unset($_SESSION['reactivation']);
			break;
		}

		// set up the pagination - phase 1
		$config															=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 											= 	site_url('/items/index');

		// get items
		$data['items']													=	$this->Item->get_all($config['per_page'], $this->uri->segment($config['uri_segment']));

		// set up the pagination - phase 2
		$config['total_rows'] 											= 	$this->Item->count_all();
		$this															->	pagination->initialize($config);
		$data['links']													=	$this->pagination->create_links();

		// Set route
		$this															->	session->set_userdata('route', 'IZ');

		// show data
		$this															->	load->view('items/manage', $data);
	}

	function search()
	{
		// initialise
		$data															=	array();
		$item_id														=	$this->input->post("search"); // supplied by javascript in manage.php

		// set list title if undelete
		switch ($_SESSION['undel'])
		{
			case	1:
					$data['title']										=	$this->lang->line('common_undelete');
			break;

			default:
					$data['title']										=	'';
			break;
		}

		// set up the pagination - phase 1
		$config															=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 											= 	site_url('/items/index');

		// get items
		$data['items']													=	$this->Item->search($item_id);

		// set up the pagination - phase 2
		$config['total_rows'] 											= 	count($data['items']);
		$this															->	pagination->initialize($config);
		$data['links']													=	$this->pagination->create_links();

		// Set route
		$this															->	session->set_userdata('route', 'IZ');

		// show data
		$this															->	load->view('items/manage', $data);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Item->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function item_search()												// used in receivings and in sales controllers
	{
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	// this function is called when updating an item, ie when clicking on the item number or for new items
	function view($item_id=-1, $origin='0')
	{

		$_SESSION['redirection']=$_SESSION['controller_name'];
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
		$_SESSION['show_dialog']			=	1;

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
								$_SESSION['selected_export_to_franchise']		=	'Y';
								$_SESSION['selected_export_to_integrated']		=	'Y';
								$_SESSION['selected_export_to_other']			=	'Y';
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
					switch ($_SESSION['undel'])
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

	// this function is called when updating an item, ie when clicking on the item number or for new items
	function view_suppliers()
	{
		// intialise
		$_SESSION['transaction_supplier_info']							=	new stdClass();
		$_SESSION['transaction_add_supplier_info']						=	new stdClass();

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
					$_SESSION['origin']		=	$origin;
					$_SESSION['sel_item_id']=	$_SESSION['transaction_info']->item_id;
			break;
		}

		// manage session for dialog
		$_SESSION['show_dialog']			=	9;

		// load suppliers pick list
		$supplier_pick_list					=	array();
		$suppliers							=	array();
		$suppliers							=	$this->Supplier->get_all()->result_array();
		foreach($suppliers as $row)
		{
			$supplier_pick_list[$row['person_id']] 	=	strtoupper($row['company_name']);
		}

		// load pick list output data
		$_SESSION['supplier_pick_list']		=	$supplier_pick_list;

		// get current data
		$_SESSION['transaction_supplier_info']	=	$this->Item->get_info_suppliers();

		// get supplier names
		foreach ($_SESSION['transaction_supplier_info'] as $key => $row)
		{
			// get supplier name
			$supplier_info					=	$this->Supplier->get_info($row->supplier_id);
			$_SESSION['transaction_supplier_info'][$key]->supplier_name	=	$supplier_info->company_name;
		}

		// set output variables
		$_SESSION['transaction_add_supplier_info']->supplier_reorder_policy = 'Y';
		$_SESSION['transaction_add_supplier_info']->supplier_id = $this->config->item('default_supplier_id');
		$_SESSION['transaction_add_supplier_info']->supplier_preferred = 'N';

		// check preferred supplier flag
		$_SESSION['transaction_add_supplier_info']->item_id = $_SESSION['transaction_info']->item_id;
		$count								=	$this->Item->item_supplier_preferred_y();
		if ($count == 0)
		{
			// set message
			$_SESSION['error_code']			=	'01830';
		}

		// show data entry
		redirect("items");
	}

	// this function is called when updating an item, ie when clicking on the item number or for new items
	function view_warehouses()
	{
		// intialise
		$_SESSION['transaction_warehouse_info']			=	new stdClass();
		$_SESSION['transaction_add_warehouse_info']		=	new stdClass();

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
					$_SESSION['origin']		=	$origin;
					$_SESSION['sel_item_id']=	$_SESSION['transaction_info']->item_id;
			break;
		}

		// manage session for dialog
		$_SESSION['show_dialog']			=	10;

		// load warehouses pick list
		$warehouses_pick_list				=	array();
		$warehouses							=	array();
		$warehouses							=	$this->Warehouse->get_all()->result_array();

		foreach($warehouses as $row)
		{
			$warehouses_pick_list[$row['warehouse_code']] 	=	strtoupper($row['warehouse_description']);
		}

		// load pick list output data
		$_SESSION['warehouse_pick_list']		=	$warehouses_pick_list;

		// get current data
		$_SESSION['transaction_warehouse_info']	=	$this->Item->get_info_warehouses();

		// get warehouse descriptions
		foreach ($_SESSION['transaction_warehouse_info'] as $key => $row)
		{
			// get supplier name
			$warehouse_info					=	$this->Warehouse->get_info($row->warehouse_code);
			$_SESSION['transaction_warehouse_info'][$key]->warehouse_description	=	$warehouse_info->warehouse_description;
		}

		// set output variables
		// $_SESSION['transaction_add_supplier_info']->supplier_reorder_policy = 'Y';
		$_SESSION['transaction_add_warehouse_info']->warehouse_code = $this->config->item('default_warehouse_code');
		$_SESSION['transaction_add_warehouse_info']->location_preferred = 'N';

		// check preferred warehouse flag
		$_SESSION['transaction_add_warehouse_info']->item_id = $_SESSION['transaction_info']->item_id;
		$count								=	$this->Item->item_warehouse_preferred_y();
		if ($count == 0)
		{
			// set message
			$_SESSION['error_code']			=	'01890';
		}

		// show data entry
		redirect("items");
	}

	//
	// Manage item/Pricelists
	//
	// Prepare view
	function view_pricelists()
	{
		// intialise
		$_SESSION['transaction_pricelist_info']							=	new stdClass();
		$_SESSION['transaction_add_pricelist_info']						=	new stdClass();

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
					$_SESSION['origin']									=	$origin;
					$_SESSION['sel_item_id']							=	$_SESSION['transaction_info']->item_id;
			break;
		}

		// manage session for dialog
		$_SESSION['show_dialog']										=	11;

		// get current data
		$_SESSION['transaction_pricelist_info']							=	$this->Item->get_info_pricelists();

		// get pricelist names
		foreach ($_SESSION['transaction_pricelist_info'] as $key => $row)
		{
			// get pricelist name
			$pricelist_info												=	$this->Pricelist->get_info($row->pricelist_id);
			$_SESSION['transaction_pricelist_info'][$key]->pricelist_name	=	$pricelist_info->pricelist_name;
			$_SESSION['transaction_pricelist_info'][$key]->pricelist_description	=	$pricelist_info->pricelist_description;
		}

		// set output variables
		$_SESSION['transaction_add_pricelist_info']->valid_from_day 	=	'00';
		$_SESSION['transaction_add_pricelist_info']->valid_from_month 	=	'00';
		$_SESSION['transaction_add_pricelist_info']->valid_from_year 	=	'0000';

		$_SESSION['transaction_add_pricelist_info']->valid_to_day 		=	'00';
		$_SESSION['transaction_add_pricelist_info']->valid_to_month 	=	'00';
		$_SESSION['transaction_add_pricelist_info']->valid_to_year 		=	'0000';

		// show data entry
		redirect("items");
	}

	// delete pricelist
	function item_pricelist_delete($item_id, $pricelist_id)
	{
		if ($this->Item->pricelist_delete($item_id, $pricelist_id))
		{
			$_SESSION['error_code']		=	'05130';
		}
		else
		{
			$_SESSION['error_code']		=	'05140';
		}

		// re-route
		$this	->	view_pricelists();
	}

	// add pricelist
	function item_pricelist_add()
	{
		// store user entries
		$_SESSION['transaction_add_pricelist_info']->item_id			=	$_SESSION['transaction_info']->item_id;
		$_SESSION['transaction_add_pricelist_info']->pricelist_id		=	$this->input->post('pricelist_id');
		$_SESSION['transaction_add_pricelist_info']->unit_price_with_tax=	$this->input->post('unit_price_with_tax');

		// explode and load valid_from
		$pieces 														=	explode("/", $this->input->post('valid_from'));
		$_SESSION['transaction_add_pricelist_info']->valid_from_day		=	$pieces[0];
		$_SESSION['transaction_add_pricelist_info']->valid_from_month	=	$pieces[1];
		$_SESSION['transaction_add_pricelist_info']->valid_from_year	=	$pieces[2];

		// explode and load valid_to
		$pieces 														=	explode("/", $this->input->post('valid_to'));
		$_SESSION['transaction_add_pricelist_info']->valid_to_day		=	$pieces[0];
		$_SESSION['transaction_add_pricelist_info']->valid_to_month		=	$pieces[1];
		$_SESSION['transaction_add_pricelist_info']->valid_to_year		=	$pieces[2];

		// load standard fields
		$_SESSION['transaction_add_pricelist_info']->deleted			=	0;
		$_SESSION['transaction_add_pricelist_info']->branch_code		=	$this->config->item('branch_code');

		// verify data entry
		$this->verify_pricelist();

		// calculate price without tax
			// get default tax rate for this item
			$item_tax_info												=	$this->Item_taxes->get_info($_SESSION['transaction_info']->item_id);

			// if not found, get default tax rate
			if (!$item_tax_info)
			{
				$tax_rate												=	$this->config->item('default_tax_1_rate');
			}
			else
			{
				$tax_rate												=	$item_tax_info[0]['percent'];
			}

			$tax_rate													=	(100 + $tax_rate) / 100;
			$_SESSION['transaction_add_pricelist_info']->unit_price		=	$_SESSION['transaction_add_pricelist_info']->unit_price_with_tax / $tax_rate;

		// so add record
		$this->Item->save_pricelist();

		// set up success message
		$_SESSION['error_code']	=	'05190';

		// re-route
		$this	->	view_pricelists();
	}

	function verify_pricelist()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_add_pricelist_info']->unit_price_with_tax)
			)
		{
			// set message

			$_SESSION['error_code']			=	'00030';
			redirect("items");
		}

		// verify item/pricelist does not exit already
		$count								=	$this->Item->item_pricelist_combo_exists();
		if ($count > 0)
		{
			// set message
			$_SESSION['error_code']			=	'05150';
			redirect("items");
		}

		// check unit_price is numeric
		if (!is_numeric($_SESSION['transaction_add_pricelist_info']->unit_price_with_tax))
		{
			// set message
			$_SESSION['error_code']			=	'01420';
			redirect("items");
		}

		// if pricelist is = to default pricelist then no validity can be entered.
		if ($_SESSION['transaction_add_pricelist_info']->pricelist_id == $this->config->item('pricelist_id')
			AND ($this->input->post('valid_from') != '00/00/0000' OR $this->input->post('valid_to') != '00/00/0000'))
		{
			// set message
			$_SESSION['error_code']										=	'05200';
			redirect($_SESSION['G']->modules[$_SESSION['module_id']]['module_name']);
		}

		// if valid_from date is entered it must be a date if entered
		if ($this->input->post('valid_from') != '00/00/0000')
		{
			if (!checkdate($_SESSION['transaction_add_pricelist_info']->valid_from_month, $_SESSION['transaction_add_pricelist_info']->valid_from_day, $_SESSION['transaction_add_pricelist_info']->valid_from_year))
			{
				// set message
				$_SESSION['error_code']									=	'05160';
				redirect($_SESSION['G']->modules[$_SESSION['module_id']]['module_name']);
			}
		}

		// valid_to date checks
		if ($this->input->post('valid_to') != '00/00/0000')
		{
			// is it a real date?
			if (!checkdate($_SESSION['transaction_add_pricelist_info']->valid_to_month, $_SESSION['transaction_add_pricelist_info']->valid_to_day, $_SESSION['transaction_add_pricelist_info']->valid_to_year))
			{
				// set message
				$_SESSION['error_code']									=	'05170';
				redirect($_SESSION['G']->modules[$_SESSION['module_id']]['module_name']);
			}

			// transform dates to real dates
			$valid_from_str												=	str_replace('/', '-', $this->input->post('valid_from'));
			$vfrom														=	strtotime($valid_from_str);
			$valid_to_str												=	str_replace('/', '-', $this->input->post('valid_to'));
			$vto														=	strtotime($valid_to_str);

			// verify valid to < valid from
			if ($vfrom > $vto)
			{
				// set message
				$_SESSION['error_code']									=	'05180';
				redirect($_SESSION['G']->modules[$_SESSION['module_id']]['module_name']);
			}
		}
	}

	// this function is called to get the remote stock for an item
	function remote_stock($item_id=-1, $origin='0')
	{
		// intialise
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['transaction_info_original']							=	$_SESSION['transaction_info'];

		// get transaction info
		$_SESSION['transaction_info']									=	$this->Item->get_info($item_id);

		// set origin
		switch ($origin)
		{
			case	'0':
					unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
					$_SESSION['origin']									=	$origin;
					$_SESSION['sel_item_id']							=	$item_id;
			break;
		}

		// manage session
		$_SESSION['show_dialog']										=	8;

		// get remote quantities
		$this->Item->get_remote_quantities();

		// set titles
		$_SESSION['$title']												=	$this->lang->line('items_remote_stock').'  '.$_SESSION['transaction_info']->name.'  '.$_SESSION['transaction_info']->item_number;

		redirect("items");
	}

	//Ramel Inventory Tracking
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
		$_SESSION['show_dialog']			=	3;

		// show the data entry
		redirect("items");
	}

	function count_details($item_id=-1)
	{
		// intialise
		$_SESSION['transaction_info']		=	new stdClass();
		$_SESSION['transaction_info_dluo']	=	new stdClass();

		// set session data
		unset($_SESSION['clone_from_id']);
		unset($_SESSION['clone_to_id']);
		unset($_SESSION['new']);
		unset($_SESSION['clone']);
		$_SESSION['$title']					=	$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_details_count');
		$_SESSION['transaction_info']		=	$this->Item->get_info($item_id);
		$_SESSION['inventory_info']			=	$this->Inventory->get_inventory_data_for_item($item_id)->result_array();

		// set dialog switch
		$_SESSION['show_dialog']			=	4;

		// show the data entry
		redirect("items");
	}

	function generate_barcode($item_id)
	{
		// get item info
		$item_info						=	$this->Item->get_info($item_id);

		// set user data
		$values							=	array	(
													'item_id'		=>	$item_info->item_id,
													'item_number'	=>	$item_info->item_number,
													'name'			=>	$item_info->name,
													'barcode'		=>	'*'.$item_info->item_number.'*'
													);
		$this							->	session->set_userdata($values);

		// set fonts
		$barcode_font				=	'/var/www/html/wrightetmathon/application/fonts/fre3of9x.ttf';
		$plain_font					=	$this->config->item('default_label_font');

		// create the image
		$img						=	imagecreatefrompng($this->config->item('default_label_image'));

		// First call to imagecolorallocate is the background color
		$white						=	imagecolorallocate($img, 255, 255, 255);
		$black						=	imagecolorallocate($img, 0, 0, 0);

		//	Create the barcode
		//	imagettftext			($img,	$fontsize,	$angle,	$xpos,	$ypos,	$color,	$fontfile,		$text);

			imagettftext			($img,	30,			0,		10,		40,		$black,	$barcode_font,	$values['barcode']);

		// create the text
			imagettftext			($img,	10,			0,		10,		50,		$black,	$plain_font,	$values['barcode']);
			imagettftext			($img,	10,			0,		10,		65,		$black,	$plain_font,	$values['item_number']);
			imagettftext			($img,	10,			0,		10,		75,		$black,	$plain_font,	word_wrap($values['name'], 18));

		// save image
		$image_path					=	'/var/www/html/wrightetmathon/barcodes/'.$values['item_number'].'.jpeg';
		imagejpeg					($img, $image_path, 100, NULL);

		// save memory
		imagedestroy				($img);

		// test if image produced
		if (!file_exists($image_path))
		{
			$_SESSION['error_code']										=	'';//07220
			$_SESSION['substitution_parms']								=	array($values['item_number'], $values['name'], $image_path);
		}
		else
		{
			$_SESSION['error_code']										=	'';//07230
			$_SESSION['substitution_parms']								=	array($values['item_number'], $values['name'], $image_path);
		}

		// set user data
		$values							=	array	(
													'image_path'		=>	$image_path
													);
		$this							->	session->set_userdata($values);

		// call view to display barcode
		$this						->	load->view('items/barcode');
	}

	// show bulk action selection screen. Each bulk action has a unique id.
	// bulk actions are loaded at system initialisation in common_routines->initialise
	function bulk_action_1()
	{
		// set show dialog code, title and redirect
		$_SESSION['show_dialog']										=	12;
		$_SESSION['$title']												=	$this->lang->line('items_bulk_edit');
		redirect("items");
	}

	// determine which action the user has selected
	function bulk_action_2()
	{
		// load the bulk action id
		$_SESSION['bulk_action_id']										=	$this->input->post('bulk_action_id');

		// get the db connection parameters
		// initialise
		$redirect														=	'items';
		unset($_SESSION['$conn_parms']);

		// get the db connection parameters
		$_SESSION['$conn_parms']										=	$this->Common_routines->get_conn_parms($redirect);
		if (!$_SESSION['$conn_parms'])
		{
			$_SESSION['error_code']										=	'05630';
			redirect("items");
		}

		// set the database to use to get the fields
		$shop_database													=	$_SESSION['$conn_parms']['database'];
		$_SESSION['$conn_parms']['database']							=	'information_schema';

		// open the DB
		$conn															=	$this->Common_routines->open_db($_SESSION['$conn_parms']);
		if (!$conn)
		{
			$_SESSION['error_code']										=	'05640';
			$_SESSION['substitution_parms']								=	array($_SESSION['$conn_parms']['database']);
			redirect("items");
		}

		// construct the sql to get the table columns from the tables we are interested in
		$sql 															=	"SELECT `INNODB_SYS_COLUMNS`.`NAME` as col_name, `INNODB_SYS_TABLES`.`NAME` as tab_name FROM `INNODB_SYS_COLUMNS` JOIN `INNODB_SYS_TABLES` ON `INNODB_SYS_COLUMNS`.`TABLE_ID` = `INNODB_SYS_TABLES`.`TABLE_ID` WHERE `INNODB_SYS_TABLES`.`NAME` = '$shop_database/ospos_items' OR `INNODB_SYS_TABLES`.`NAME` = '$shop_database/ospos_categories' OR `INNODB_SYS_TABLES`.`NAME` = '$shop_database/ospos_suppliers'";
		$this															->	Item->items_table_column_pick_list($sql, $conn);

		// construct the and/or picklist
		$_SESSION['M']->and_or_pick_list								=	array('AND'=>$this->lang->line('common_and'), 'OR'=>$this->lang->line('common_or'));

		// construct the test pick list
		$_SESSION['M']->test_pick_list									=	array	(
																					'='				=>	$this->lang->line('common_equal_to'),
																					'>'				=>	$this->lang->line('common_greater_than'),
																					'>='			=>	$this->lang->line('common_greater_than_or_equal'),
																					'<'				=>	$this->lang->line('common_less_than'),
																					'<='			=>	$this->lang->line('common_less_than_or_equal'),
																					'!='			=>	$this->lang->line('common_not_equal'),
																					'LIKE'			=>	$this->lang->line('common_like'),
																					'NOT LIKE'		=>	$this->lang->line('common_not_like'),
																					'IS NULL'		=>	$this->lang->line('common_is_null'),
																					'IS NOT NULL'	=>	$this->lang->line('common_is_not_null')
																					);

		// set show dialog
		$_SESSION['show_dialog']										=	13;

		// set title and initial defaults
		switch ($_SESSION['bulk_action_id'])
		{
			// de-activate
			case	10:
				$_SESSION['$title']										=	$this->lang->line('items_bulk_deactivate');
				break;

			// modify sales price TTC
			case	20:
				// title
				$_SESSION['$title']										=	$this->lang->line('items_bulk_salespriceTTC');

				// initial defaults
				$_SESSION['transaction_update_pricelist_info']			=	new stdClass();
				$_SESSION['transaction_update_pricelist_info']->pricelist_id 				=	$this->config->item('pricelist_id');
				$_SESSION['transaction_update_pricelist_info']->unit_price_with_tax			=	0;
				break;

			// modify reorder policy
			case	30:
				// title
				$_SESSION['$title']										=	$this->lang->line('items_bulk_reorderpolicy');

				// initial defaults
				$_SESSION['transaction_update_supplier_info']			= 	new stdClass();
				$_SESSION['transaction_update_supplier_info']->supplier_id					=	$this->config->item('default_supplier_id');
				$_SESSION['transaction_update_supplier_info']->supplier_reorder_policy		=	'Y';
				$_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size	=	0;
				$_SESSION['transaction_update_supplier_info']->supplier_min_order_qty		=	0;
				$_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty		=	0;
				break;

			// de-activate
			case	40:
				$_SESSION['$title']										=	$this->lang->line('items_bulk_reactivate');
				break;

			// modify DLUO indicator
			case	50:
				// title
				$_SESSION['$title']										=	$this->lang->line('items_bulk_DLUO');

				// initial defaults
				$_SESSION['transaction_info']							=	new stdClass();
				$_SESSION['transaction_info']->dluo_indicator			=	'Y';
				break;
			
			// Modification du prix d'achat Fournisseur HT
			case    60:
			    // title
				$_SESSION['$title']										=	$this->lang->line('items_bulk_prixachat');
				
				// initial 
				$_SESSION['transaction_update_pricelist_info']			=	new stdClass();
				$_SESSION['transaction_update_pricelist_info']->pricelist_id 				=	$this->config->item('pricelist_id');
				$_SESSION['transaction_update_pricelist_info']->unit_price_without_tax			=	0;
				$_SESSION['transaction_update_supplier_info']			= 	new stdClass();
				$_SESSION['transaction_update_supplier_info']->supplier_id					=	$this->config->item('default_supplier_id');	
				break;
		}

		// close the db connection
		$conn->close();

		// redirect
		redirect("items");
	}

	// process the data the user has entered and create the sql
	function bulk_action_3()
	{
		// initialise
		unset($_SESSION['bulk_data']);
		unset($_SESSION['bulk_selection']);
		unset($_SESSION['sql_where_1']);
		unset($_SESSION['sql_where_2']);
		unset($_SESSION['sql_branch']);
		unset($_SESSION['sql_deleted']);
		unset($_SESSION['sql_pricelist']);
		unset($set_where_1);
		unset($sql_where_1);

		// trim and capture SQL input for all bulk_action_ids
		$_SESSION['bulk_data']['0']										=	trim($this->input->post('column_id_1'));
		$_SESSION['bulk_data']['1']										=	trim($this->input->post('test_id_1'));
		$_SESSION['bulk_data']['2']										=	trim($this->input->post('value_1'));
		$_SESSION['bulk_data']['3']										=	trim($this->input->post('and_or_2'));
		$_SESSION['bulk_data']['4']										=	trim($this->input->post('column_id_2'));
		$_SESSION['bulk_data']['5']										=	trim($this->input->post('test_id_2'));
		$_SESSION['bulk_data']['6']										=	trim($this->input->post('value_2'));

		// trim and capture SQL input for specific bulk_action_ids
		switch ($_SESSION['bulk_action_id'])
		{
			case 20:
				$_SESSION['transaction_update_pricelist_info']->pricelist_id 				=	trim($this->input->post('pricelist_id'));
				$_SESSION['transaction_update_pricelist_info']->unit_price_with_tax			=	trim($this->input->post('unit_price_with_tax'));
				break;
			case 30:
				$_SESSION['transaction_update_supplier_info']->supplier_id					=	trim($this->input->post('supplier_id'));
				$_SESSION['transaction_update_supplier_info']->supplier_reorder_policy		=	trim($this->input->post('supplier_reorder_policy'));
				$_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size	=	trim($this->input->post('supplier_reorder_pack_size'));
				$_SESSION['transaction_update_supplier_info']->supplier_min_order_qty		=	trim($this->input->post('supplier_min_order_qty'));
				$_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty		=	trim($this->input->post('supplier_min_stock_qty'));
				break;
			case 50:
				$_SESSION['transaction_info']->dluo_indicator	 		=	trim($this->input->post('dluo_indicator'));
				break;
		
			case 60:
				$_SESSION['transaction_update_pricelist_info']->pricelist_id 				=	trim($this->input->post('pricelist_id'));
				$_SESSION['transaction_update_pricelist_info']->unit_price_without_tax			=	trim($this->input->post('unit_price_without_tax'));
				$_SESSION['transaction_update_supplier_info']->supplier_id					=	$_POST['pricelist_id'];    //Fonctionne qu'avec $_POST['']
				break;
			default:
				break;
		}

		// test input - there must be at least one value entered in the where
		if (empty($_SESSION['bulk_data']['2']) AND empty($_SESSION['bulk_data']['6']))
		{
			$_SESSION['error_code']										=	'05650';
			redirect("items");
		}

		// test specific bulk_action_id entries
		switch ($_SESSION['bulk_action_id'])
		{
			case 20:
				// price must be numeric - it can be zero
				if (!is_numeric($_SESSION['transaction_update_pricelist_info']->unit_price_with_tax))
				{
					$_SESSION['error_code']								=	'05660';
					$_SESSION['substitution_parms']						=	array($_SESSION['transaction_update_pricelist_info']->unit_price_with_tax);
					redirect("items");
				}
				break;
			case 30:
				// numeric values must be numeric
				if (!is_numeric($_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size))
				{
					$_SESSION['error_code']								=	'01460';
					$_SESSION['substitution_parms']						=	array($_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size);
				}
				if (!is_numeric($_SESSION['transaction_update_supplier_info']->supplier_min_order_qty))
				{
					$_SESSION['error_code']								=	'01463';
					$_SESSION['substitution_parms']						=	array($_SESSION['transaction_update_supplier_info']->supplier_min_order_qty);
					redirect("items");
				}
				if (!is_numeric($_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty))
				{
					$_SESSION['error_code']								=	'01465';
					$_SESSION['substitution_parms']						=	array($_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty);
					redirect("items");
				}
				// values cannot be less than or equal to zero if reorder policy = Y
				if ($_SESSION['transaction_update_supplier_info']->supplier_reorder_policy == 'Y')
				{
					if ($_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size <= 0)
					{
						// set message
						$_SESSION['error_code']							=	'00190';
						redirect("items");
					}

					if ($_SESSION['transaction_update_supplier_info']->supplier_min_order_qty <= 0)
					{
						// set message
						$_SESSION['error_code']							=	'00190';
						redirect("items");
					}

					if ($_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty <= 0)
					{
						// set message
						$_SESSION['error_code']							=	'00190';
						redirect("items");
					}
				}
				else  // they have to be zero if reorder policy is N
				{
					$_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size = 0;
					$_SESSION['transaction_update_supplier_info']->supplier_min_order_qty = 0;
					$_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty = 0;
				}
				break;
			
			// Vérification du type de l'entré dans la case associée à "Prix d'achat Public HT" 
			case 60:
				 if (!is_numeric($_SESSION['transaction_update_pricelist_info']->unit_price_without_tax))
				{
					// Message dans /var/www/html/wrightetmathon/application/messages/messages.def
					// Utilise /var/www/html/wrightetmathon/application/views/partial/show_messages.php					
					$_SESSION['error_code']								=	'27280';
					$_SESSION['substitution_parms']						=	array($_SESSION['transaction_update_pricelist_info']->unit_price_without_tax);
					redirect("items");
				}
				break;
			default:
				break;
		}

		// create the WHERE SQL statement for the first value if entered and load to session
		if ($_SESSION['bulk_data']['2'] != NULL)
		{
			$_SESSION['sql_where_1']									=	$this->bulk_create_where($_SESSION['bulk_data']['0'], $_SESSION['bulk_data']['1'], $_SESSION['bulk_data']['2']);
		}

		// create the WHERE SQL statement for the second value if entered
		if ($_SESSION['bulk_data']['6'] != NULL)
		{
			$_SESSION['sql_where_2']									=	$this->bulk_create_where($_SESSION['bulk_data']['4'], $_SESSION['bulk_data']['5'], $_SESSION['bulk_data']['6']);
		}

		// create the WHERE SQL statement for the branch code
		$_SESSION['sql_branch']											=	'items.branch_code = "'.$this->config->item('branch_code').'"';

		// create the WHERE SQL statement for the deleted - depends on bulk-action-id
		switch ($_SESSION['bulk_action_id'])
		{
			case 40:
				// for reactivating an item the deleted flag must = deactivated
				$_SESSION['sql_deleted']								=	'items.deleted = "1"';
				break;
			default:
				// for the rest the deleted flag must = active
				$_SESSION['sql_deleted']								=	'items.deleted = "0"';
				break;
		}

		// create the WHERE SQL statement for the specific bulk_action_id
		switch ($_SESSION['bulk_action_id'])
		{
			case 20:
				$_SESSION['sql_pricelist']								=	'items_pricelists.pricelist_id = "'.$_SESSION['transaction_update_pricelist_info']->pricelist_id.'"';
				break;
			case 30:
				$_SESSION['sql_reorderpolicy']							=	'items_suppliers.supplier_id = "'.$_SESSION['transaction_update_supplier_info']->supplier_id.'"';
				break;
			
			case 60:
			    $_SESSION['sql_reorderpolicy']							=	'items_suppliers.supplier_id = "'.$_POST['pricelist_id'].'"';    // fonctionne qu'avec $_POST
				break;
			default:
				break;
		}

		// now run the select to see how many items will be affected and present first 10 lines to user for confirmation
		$result															=	$this->Item->bulk_select();

		// set number of rows
		$_SESSION['bulk_num_rows']										=	$result->num_rows;

		// load the selection to array
		foreach	($result->result_array() as $row)
		{
			// for bulk_selection_ids
			switch ($_SESSION['bulk_action_id'])
			{
				case 20:
					$_SESSION['bulk_selection'][]							=	array 	(
																						'item_id'		=>	$row['item_id'],
																						'item_number'	=>	$row['item_number'],
																						'item_name'		=>	$row['name'],
																						'category_name'	=>	$row['category_name'],
																						'supplier_name'	=>	$row['company_name'],
																						'pricelist_id'	=>	$row['pricelist_id']
																						);
					break;
				case 30:
					$_SESSION['bulk_selection'][]							=	array 	(
																						'item_id'		=>	$row['item_id'],
																						'item_number'	=>	$row['item_number'],
																						'item_name'		=>	$row['name'],
																						'category_name'	=>	$row['category_name'],
																						'supplier_name'	=>	$row['company_name'],
																						'supplier_id'	=>	$row['supplier_id']
																						);
					break;
			
				case 60:
					$_SESSION['bulk_selection'][]							=	array 	(
																						'item_id'		=>	$row['item_id'],
																						'item_number'	=>	$row['item_number'],
																						'item_name'		=>	$row['name'],
																						'category_name'	=>	$row['category_name'],
																						'supplier_name'	=>	$row['company_name'],
																						'pricelist_id'	=>	$row['pricelist_id'],
																						'supplier_id'	=>	$row['supplier_id']
																						);
					break;
				default:
					$_SESSION['bulk_selection'][]							=	array 	(
																						'item_id'		=>	$row['item_id'],
																						'item_number'	=>	$row['item_number'],
																						'item_name'		=>	$row['name'],
																						'category_name'	=>	$row['category_name'],
																						'supplier_name'	=>	$row['company_name']
																						);
					break;
			}
		}

		// I now have the complete sql; so ask for confirmation
		// set show dialog
		$_SESSION['show_dialog']										=	14;

		// redirect
		redirect("items");
	}

	// user has confirmed the SQL, so do it
	function bulk_action_4()
	{
		// initialise
		unset($_SESSION['show_dialog']);
		unset($_SESSION['new']);
		$count_true														=	0;
		$count_false													=	0;

		// read the selected data and update accordingly
		foreach ($_SESSION['bulk_selection'] as $row)
		{
			// take action depending on the bulk_action_id
			switch ($_SESSION['bulk_action_id'])
			{
				// de-activate
				case	10:
					// de-activate the item
					$result												=	$this->Item->delete($row['item_id']);
					break;

				// modify sales price TTC
				case	20:
					// set price with tax
					// calculate price without tax
					// get default tax rate for this item
					$item_tax_info										=	$this->Item_taxes->get_info($row['item_id']);

					// if not found, get default tax rate
					if (!$item_tax_info)
					{
						$tax_rate										=	$this->config->item('default_tax_1_rate');
					}
					else
					{
						$tax_rate										=	$item_tax_info[0]['percent'];
					}

					$tax_rate											=	(100 + $tax_rate) / 100;
					$_SESSION['transaction_update_pricelist_info']->unit_price	=	$_SESSION['transaction_update_pricelist_info']->unit_price_with_tax / $tax_rate;

					// update the pricelist
					$result												=	$this->Item->update_pricelist($row['item_id'], $row['pricelist_id']);
					break;

				// modify reorder policy
				case	30:
					// update the items supplier info
					$result												=	$this->Item->update_supplier($row['item_id'], $row['supplier_id']);
					break;

				// Re-activate
				case	40:
					// Re-activate the item
					$_SESSION['transaction_info']->item_id				=	$row['item_id'];
					$result												=	$this->Item->undelete();
					break;

				// DLUO
				case	50:
					// set the DLUO indicator
					$_SESSION['transaction_info']->item_id				=	$row['item_id'];
					$result												=	$this->Item->save();
					break;

                // modification du prix d'achat Fournisseur HT
				case	60:
                    // set price with tax
					// calculate price without tax
					// get default tax rate for this item
					$item_tax_info										=	$this->Item_taxes->get_info($row['item_id']);

					// if not found, get default tax rate
					if (!$item_tax_info)
					{
						//$tax_rate										=	$this->config->item('default_tax_1_rate');
					}
					else
					{
						//$tax_rate										=	$item_tax_info[0]['percent'];
                    }
					//$tax_rate											=	(100 + $tax_rate) / 100;
					//$_SESSION['transaction_update_pricelist_info']->unit_price	=	$_SESSION['transaction_update_pricelist_info']->unit_price_without_tax / $tax_rate;
					// update the pricelist
					//$result												=	$this->Item->update_pricelist($row['item_id'], $row['pricelist_id']);
					//$result = "UPDATE `ospos_items_supplier` SET `ospos_items_supplier`.`supplier_cost_price`='' WHERE `ospos_items_supplier`.`item_id`='" . $row['item_id'] . "' AND `ospos_items_suppliers`.`branch_code`='" . $this->CI_Config->config->item('branch_code')."'";
					//$this						->	db->where('items_suppliers.supplier_id', $supplier_id);
					
					$this->db->where('items_suppliers.item_id', $row['item_id']);
					$this->db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
					$this->db->where('items_suppliers.supplier_preferred', 'Y');
		            $result=$this->db->update('items_suppliers', array	('supplier_cost_price'=>$_SESSION['transaction_update_pricelist_info']->unit_price_without_tax));
					break;
			}

			// count the results
			if ($result)
			{
				$count_true												=	$count_true + 1;
			}
			else
			{
				$count_false											=	$count_false + 1;
			}
		}

		// redirect
		$_SESSION['error_code']											=	'05670';
		$_SESSION['substitution_parms']									=	array($_SESSION['bulk_num_rows'], $count_true, $count_false);

		redirect("items");
	}

	// contruct the where clause
	function bulk_create_where($column, $operator, $value)
	{
		// initialse
		unset($where_clause);

		// inspect the operator for where formatting
		switch ($operator)
		{
			case "LIKE":
				$where_clause											=	$column.' '.$operator.' "%'.$value.'%"';
				break;
			case "NOT LIKE":
				$where_clause											=	$column.' '.$operator.' "%'.$value.'%"';
				break;
			default:
				$where_clause											=	$column.' '.$operator.' "'.$value.'"';
				break;
		}

		// return constructed where clause
		return															$where_clause;
	}


	function save($item_id=-1, $origin='')
	{
		// get category name
		$category_info													=	$this->Category->get_info($this->input->post('category_id'));

		// set selected supplier and category
		$_SESSION['selected_category']									=	$this->input->post('category_id');
		$_SESSION['selected_warehouse']									=	$this->input->post('warehouse_code');
		$_SESSION['selected_dluo_indicator']							=	$this->input->post('dluo_indicator');
		$_SESSION['selected_reorder_policy']							=	$this->input->post('reorder_policy');
		$_SESSION['selected_giftcard_indicator']						=	$this->input->post('giftcard_indicator');
		$_SESSION['selected_offer_indicator']							=	$this->input->post('offer_indicator');
		$_SESSION['selected_DynamicKit']								=	$this->input->post('DynamicKit');
		$_SESSION['selected_export_to_franchise']						=	$this->input->post('export_to_franchise');
		$_SESSION['selected_export_to_integrated']						=	$this->input->post('export_to_integrated');
		$_SESSION['selected_export_to_other']							=	$this->input->post('export_to_other');

		// manage session
		switch ($_SESSION['new'] ?? 0)
		{
			// add item
			case	1:
					$_SESSION['transaction_info']->item_id				=	NULL;

			break;

			// update item
			default:
					$_SESSION['transaction_info']->item_id				=	$item_id;
			break;
		}

		$_SESSION['transaction_info']->name								=	$this->input->post('name');
		$_SESSION['transaction_info']->description						=	$this->input->post('description');
		$_SESSION['transaction_info']->volume							=	$this->input->post('volume');
		$_SESSION['transaction_info']->nicotine							=	$this->input->post('nicotine');
		$_SESSION['transaction_info']->category_id						=	$this->input->post('category_id');
		$_SESSION['transaction_info']->category							=	$category_info->category_name;
		$_SESSION['transaction_info']->item_number						=	$this->input->post('item_number');
		$_SESSION['transaction_info']->DynamicKit						=	$this->input->post('DynamicKit');
		$_SESSION['transaction_info']->dluo_indicator					=	$this->input->post('dluo_indicator');
		$_SESSION['transaction_info']->giftcard_indicator				=	$this->input->post('giftcard_indicator');
		$_SESSION['transaction_info']->offer_indicator					=	$this->input->post('offer_indicator');
		$_SESSION['transaction_info']->kit_reference					=	$this->input->post('kit_reference');
		$_SESSION['transaction_info']->barcode							=	$this->input->post('barcode');
		$_SESSION['transaction_info']->export_to_franchise				=	$this->input->post('export_to_franchise');
		$_SESSION['transaction_info']->export_to_integrated				=	$this->input->post('export_to_integrated');
		$_SESSION['transaction_info']->export_to_other					=	$this->input->post('export_to_other');
		$_SESSION['transaction_info']->image_file_name					=	$this->input->post('image_file_name');
		$_SESSION['transaction_info']->branch_code						=	$this->config->item('branch_code');

		$_SESSION['transaction_tax_info']->tax_name_1					=	$this->input->post('tax_name_1');
		$_SESSION['transaction_tax_info']->tax_percent_1				=	$this->input->post('tax_percent_1');

		// do data verifications
		$this															->	verify();

		// if here then all checks succeeded so do the update
		$this															->	Item->save();

		// add inventory record
		$employee_id													=	$this->Employee->get_logged_in_employee_info()->person_id;

		// set correct field values depending on new or not for inventory record file
		switch ($_SESSION['new'] ?? 0)
		{
			// add item
			case	1:
					$trans_comment				=	$this->lang->line('items_item_new');
					$trans_stock_before			=	0;
					$trans_stock_after			=	0;
			break;

			default:
					$trans_comment				=	$this->lang->line('items_item_edit');
					$trans_stock_before			=	$_SESSION['transaction_info']->quantity;
					$trans_stock_after			=	$_SESSION['transaction_info']->quantity;
			break;
		}

		// create data for inventory table
		$inv_data = array	(
							'trans_date'		=>	date('Y-m-d H:i:s'),
							'trans_items'		=>	$_SESSION['transaction_info']->item_id,
							'trans_user'		=>	$employee_id,
							'trans_comment'		=>	$trans_comment,
							'trans_stock_before'=>	$trans_stock_before,
							'trans_inventory'	=>	0,
							'trans_stock_after'	=>	$trans_stock_after,
							'branch_code'		=>	$this->config->item('branch_code')
							);
		$this->Inventory->insert($inv_data);

		// add tax records
		$items_taxes_data	 = array	(
										'item_id'		=>	$_SESSION['transaction_info']->item_id,
										'name'			=>	$_SESSION['transaction_tax_info']->tax_name_1,
										'percent'		=>	$_SESSION['transaction_tax_info']->tax_percent_1,
										'branch_code'	=>	$this->config->item('branch_code')
										);
		$this->Item_taxes->save($items_taxes_data, $_SESSION['transaction_info']->item_id);

		// all done
		switch ($_SESSION['new'] ?? 0)
		{
			// add item
			case	1:
					$_SESSION['error_code']		=	'00390';
			break;

			default:
					$_SESSION['error_code']		=	'00400';
			break;
		}

		// manage session - this controls the flow of the data input
		switch ($_SESSION['show_dialog'])
		{
			case 1:
				// coming from std item entry, so show supplier entry
				$_SESSION['next_action']				=	'S';
			break;

			case 9:
				//
				// coming from supplier entry, so show warehouse entry
				$_SESSION['next_action']				=	'W';
			break;

			case 10:
				// coming from warehouse entry, so show std entry next
				$_SESSION['next_action']				=	NULL;
			break;

			default:
				// Don't know where coming from, so show std entry
				$_SESSION['next_action']				=	NULL;
			break;
		}

		// reload the data
		$this->view($_SESSION['transaction_info']->item_id, $_SESSION['origin']);
	}

	// set the flash data
	function setflash($success_or_failure, $message, $origin)
	{
		$this						->	session->set_flashdata('success_or_failure', $success_or_failure);
		$this						->	session->set_flashdata('message', $message);
		$this						->	session->set_flashdata('origin', $origin);

		// redirect to correct calling prog
		// determine route depending on route_code
		$route_info					=	$this->Common_routines->determine_route($origin);

		if(!empty($route_info))
		{
			echo br(1);

			switch ($route_info->route_code)
			{
				case "DL":
					redirect('reports/'.$route_info->route_path);
					break;
				case "DD":
					redirect('reports/'.$route_info->route_path);
					break;
				case "DF":
					redirect('reports/'.$route_info->route_path);
					break;
				default:
					redirect('reports/excel_export');
			}
		}
		else
		{
			redirect('items');
			return;
		}
	}

	// Inventory Tracking
	function save_inventory($item_id=-1)
	{
		// save item data
		$cur_item_info													=	$_SESSION['transaction_info'];

		// set up data for inventory
		$trans_stock_after												=	$cur_item_info->quantity + $this->input->post('newquantity');
		$inv_data 					= 	array	(
												'trans_date'			=>	date('Y-m-d H:i:s'), //Wright modified 18/01/2014
												'trans_items'			=>	$item_id,
												'trans_user'			=>	$_SESSION['G']->login_employee_id,
												'trans_comment'			=>	$this->input->post('trans_comment'),
												'trans_stock_before'	=>	$cur_item_info->quantity,
												'trans_inventory'		=>	$this->input->post('newquantity'),
												'trans_stock_after'		=>	$trans_stock_after,
												'branch_code'			=>	$this->config->item('branch_code')
												);
		$this						->	Inventory->insert($inv_data);

		//Update stock quantity and rolling inventory indicator
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['new']												=	0;
		$_SESSION['transaction_info']->item_id							=	$item_id;
		$_SESSION['transaction_info']->quantity							=	$trans_stock_after;
		$_SESSION['transaction_info']->rolling_inventory_indicator 		=	1;

		$report															=	$this->Item->save();

		// update stock valuation records
		// get item supplier data for item cost using default supplier
		$item_supplier_data 											=	$this->Item->item_supplier_get_cost($item_id);//, $this->config->item('default_supplier_id')

		// set cost price
		if ($item_supplier_data)
		{
			$cost_price													=	$item_supplier_data->supplier_cost_price;
		}
		else
		{
			$cost_price 												=	0;
		}

		// multiply by -1 to change sign so that logic here is same as in sales.php
		if ($trans_stock_after < 0)
		{
			$this														->	Item->value_delete_item_id($item_id);
			$data					=	array	(
												'value_item_id'			=>	$item_id,
												'value_cost_price'		=>	$cost_price,
												'value_qty'				=>	$trans_stock_after,
												'value_trans_id'		=>	0,
												'branch_code'			=>	$this->config->item('branch_code')
												);

			$this														->	Item->value_write($data);
		}
		else
		{
			$value_remaining_qty										=	-1 * $this->input->post('newquantity');
			$value_trans_id												=	0;
			$this														->	Item->value_update($value_remaining_qty, $item_id, $cost_price, $value_trans_id);
		}

		if (empty($report))
		{
			// set success indicator
			$success_or_failure											=	'S';
			$message													=	$this->lang->line('items_inv_successful_updating').' -> '.$cur_item_info->item_number.' '.$cur_item_info->name;

			// test for DLUO
			if ($cur_item_info->dluo_indicator == 'Y')
			{
				// get the data
				$item_info_dluo											=	array();
				$cur_item_info 											= 	$this->Item->get_info($item_id);
				$item_info_dluo											=	$this->Item->get_info_dluo($item_id)->result_array();

				// get total dluo qty
				$dluo_total_qty											=	0;

				foreach ($item_info_dluo as $row)
				{
					$dluo_total_qty										=	$dluo_total_qty + $row['dluo_qty'];
				}

				// check total dluo against stock qty and show the DLUO screen only if different
				$item_qty												=	0 + $cur_item_info->quantity;
				if ($dluo_total_qty != $cur_item_info->quantity)
				{
					$line												=	0;
					$this												->	dluo_form($item_id, $_SESSION['origin'], $line);
					return;
				}
			}
		}
		else
		{
			// set error indicator
			$success_or_failure											=	'F';
			$message													=	$this->lang->line('items_inv_unsuccessful_updating').' -> '.$cur_item_info->item_number.' '.$cur_item_info->name;
		}

		// set dialog switch
		unset($_SESSION['show_dialog']);

		// exit gracefully
		redirect("/common_controller/common_exit");
	}

//	------------------------------------------------------------------------------------------
// 	Manage DLUO
//	------------------------------------------------------------------------------------------

	// show the DLUO screen
	function dluo_form($item_id=-1, $origin='0', $line=0)
	{
		// set session data
		$_SESSION['transaction_info']				=	new stdClass();
		$_SESSION['item_info_dluo']					=	array();
		$_SESSION['$title']							=	$this->lang->line('common_manage').' '.$this->lang->line('items_dluo');
		$_SESSION['show_dialog']					=	6;
		$_SESSION['line']							=	$line;

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

		// get the data
		$_SESSION['transaction_info']				=	$this->Item->get_info($item_id);
		$_SESSION['item_info_dluo']					=	$this->Item->get_info_dluo($item_id)->result_array();

		// get total dluo qty
		$_SESSION['dluo_total_qty']					=	0;

		// read each dluo record
		foreach ($_SESSION['item_info_dluo'] as $row)
		{
			// total qty DLUO
			$_SESSION['dluo_total_qty']				=	$_SESSION['dluo_total_qty'] + $row['dluo_qty'];

			// delete records with 0 qty
			if ($row['dluo_qty'] == 0)
			{
				$this->Item->dluo_delete($row['year'], $row['month']);
			}
		}

		// get dluo records again, in case some were deleted
		$_SESSION['item_info_dluo']					=	array();
		$_SESSION['item_info_dluo']					=	$this->Item->get_info_dluo($item_id)->result_array();

		// check total dluo against stock qty and issue message if not same
		if ($_SESSION['dluo_total_qty'] 			!= 	$_SESSION['transaction_info']->quantity)
		{
			$_SESSION['error_code']					=	'01490';
		}

		// show the data entry
		redirect("items");
	}

	// edit the detail
	function dluo_edit($year, $month)
	{
		// store user entries
		$_SESSION['transaction_info']->dluo_qty1	=	$this->input->post('new_dluo_qty');

		// verify entry
		$this->verify_dluo_edit();

		// if here tests OK
		// create dluo data set
		$dluo_data 						= 	array	(
													'dluo_qty'	=>	$_SESSION['transaction_info']->dluo_qty1
													);
		if ($this->Item->dluo_edit($year, $month, $dluo_data))
		{
			$_SESSION['error_code']		=	'01500';
		}
		else
		{
			$_SESSION['error_code']		=	'01510';
		}

		$this							->	dluo_form($_SESSION['transaction_info']->item_id, $_SESSION['origin'], $_SESSION['line']);
	}

	function dluo_add()
	{
		// store user entries
		$_SESSION['transaction_info']->dluo_year1			=	$this->input->post('new1_add_year');
		$_SESSION['transaction_info']->dluo_month1			=	$this->input->post('new1_add_month');
		$_SESSION['transaction_info']->dluo_qty1			=	$this->input->post('new1_add_qty');

		// verify dluo entries
		$this->verify_dluo_add();

		// if here all test passed
		// create dluo data set
		$dluo_data 						= 	array	(
													'item_id'		=>	$_SESSION['transaction_info']->item_id,
													'year'			=>	$_SESSION['transaction_info']->dluo_year1,
													'month'			=>	$_SESSION['transaction_info']->dluo_month1,
													'dluo_qty'		=>	$_SESSION['transaction_info']->dluo_qty1,
													'branch_code' 	=>	$this->config->item('branch_code')
													);

		// to avoid duplicate entries, test to see if this record already exists, if so update existing record, else add record
		$record_exists					=	$this->Item->get_dluo_record($dluo_data);
		if (!empty($record_exists))
		{
			// add new qty to existing qty and update record
			$dluo_data['dluo_qty']		=	$dluo_data['dluo_qty'] + $record_exists['dluo_qty'];

			if ($this->Item->dluo_edit($dluo_data['year'], $dluo_data['month'], $dluo_data))
			{
				$_SESSION['error_code']	=	'01500';
			}
			else
			{
				$_SESSION['error_code']	=	'01510';
			}
		}
		else
		{
			if ($this->Item->dluo_add($dluo_data))
			{
				$_SESSION['error_code']	=	'01500';
			}
			else
			{
				$_SESSION['error_code']	=	'01510';
			}
		}

		$this							->	dluo_form($_SESSION['transaction_info']->item_id, $_SESSION['origin'], $_SESSION['line']);
	}


	// delete a DLUO record
	function dluo_delete($year, $month)
	{
		if ($this->Item->dluo_delete($year, $month))
		{
			$_SESSION['error_code']		=	'01520';
		}
		else
		{
			$_SESSION['error_code']		=	'01510';
		}

		$this							->	dluo_form($_SESSION['transaction_info']->item_id, $_SESSION['origin'], $_SESSION['line']);
	}

//	------------------------------------------------------------------------------------------
// 	Manage suppliers
//	------------------------------------------------------------------------------------------

	// add supplier
	function item_supplier_add()
	{
		// store user entries
		$_SESSION['transaction_add_supplier_info']->item_id						=	$_SESSION['transaction_info']->item_id;
		$_SESSION['transaction_add_supplier_info']->supplier_id					=	$this->input->post('supplier_id');
		$_SESSION['transaction_add_supplier_info']->supplier_preferred			=	$this->input->post('supplier_preferred');
		$_SESSION['transaction_add_supplier_info']->supplier_item_number		=	$this->input->post('supplier_item_number');
		$_SESSION['transaction_add_supplier_info']->supplier_cost_price			=	$this->input->post('supplier_cost_price');
		$_SESSION['transaction_add_supplier_info']->supplier_reorder_policy		=	$this->input->post('supplier_reorder_policy');
		$_SESSION['transaction_add_supplier_info']->supplier_reorder_pack_size	=	$this->input->post('supplier_reorder_pack_size');
		$_SESSION['transaction_add_supplier_info']->supplier_min_order_qty		=	$this->input->post('supplier_min_order_qty');
		$_SESSION['transaction_add_supplier_info']->supplier_min_stock_qty		=	$this->input->post('supplier_min_stock_qty');
		$_SESSION['transaction_add_supplier_info']->supplier_bar_code			=	$this->input->post('supplier_bar_code');
		$_SESSION['transaction_add_supplier_info']->supplier_reorder_level		=	0;
		$_SESSION['transaction_add_supplier_info']->supplier_reorder_quantity	=	0;
		$_SESSION['transaction_add_supplier_info']->deleted						=	0;
		$_SESSION['transaction_add_supplier_info']->branch_code					=	$this->config->item('branch_code');

		// verify supplier entries
		$this->verify_supplier();

		// so add record
		$this->Item->save_supplier();

		// set up success message
		$_SESSION['error_code']	=	'01840';

		// re-route
		$this	->	view_suppliers();
	}


	// delete supplier
	function item_supplier_delete($item_id, $supplier_id)
	{
		if ($this->Item->supplier_delete($item_id, $supplier_id))
		{
			$_SESSION['error_code']		=	'01870';
		}
		else
		{
			$_SESSION['error_code']		=	'01880';
		}

		// check preferred supplier flag
		$_SESSION['transaction_add_supplier_info']->item_id = $item_id;
		$count							=	$this->Item->item_supplier_preferred_y();
		if ($count == 0)
		{
			// set message
			$_SESSION['error_code']		=	'01830';
			redirect("items");
		}

		// re-route
		$this	->	view_suppliers();
	}

//	------------------------------------------------------------------------------------------
// 	Manage warehouses
//	------------------------------------------------------------------------------------------

	// add warehouse
	function item_warehouse_add()
	{
		// store user entries
		$_SESSION['transaction_add_warehouse_info']->item_id						=	$_SESSION['transaction_info']->item_id;
		$_SESSION['transaction_add_warehouse_info']->location_preferred				=	$this->input->post('location_preferred');
		$_SESSION['transaction_add_warehouse_info']->warehouse_code					=	$this->input->post('warehouse_code');
		$_SESSION['transaction_add_warehouse_info']->warehouse_row					=	$this->input->post('warehouse_row');
		$_SESSION['transaction_add_warehouse_info']->warehouse_section				=	$this->input->post('warehouse_section');
		$_SESSION['transaction_add_warehouse_info']->warehouse_shelf				=	$this->input->post('warehouse_shelf');
		$_SESSION['transaction_add_warehouse_info']->warehouse_bin					=	$this->input->post('warehouse_bin');
		$_SESSION['transaction_add_warehouse_info']->quantity						=	$this->input->post('quantity');
		$_SESSION['transaction_add_warehouse_info']->deleted						=	0;
		$_SESSION['transaction_add_warehouse_info']->branch_code					=	$this->config->item('branch_code');

		// verify warehouse entries
		$this->verify_warehouse();

		// so add record
		$this->Item->save_warehouse();

		// set up success message
		$_SESSION['error_code']	=	'01840';

		// re-route
		$this	->	view_warehouses();
	}


	// delete warehouse
	function item_warehouse_delete($item_id, $warehouse_code, $warehouse_row, $warehouse_section, $warehouse_shelf, $warehouse_bin)
	{
		if ($this->Item->warehouse_delete($item_id, $warehouse_code, $warehouse_row, $warehouse_section, $warehouse_shelf, $warehouse_bin))
		{
			$_SESSION['error_code']		=	'01920';
		}
		else
		{
			$_SESSION['error_code']		=	'01930';
		}

		// check preferred warehouse flag
		$_SESSION['transaction_add_warehouse_info']->item_id = $item_id;

		$count							=	$this->Item->item_warehouse_preferred_y();

		if ($count == 0)
		{
			// set message
			$_SESSION['error_code']		=	'01890';
		}

		// re-route
		$this	->	view_warehouses();
	}

//	------------------------------------------------------------------------------------------
// 	Manage KITS
//	------------------------------------------------------------------------------------------

	// show the KIT screen
	function kit($item_id)
	{
		// initialise
		unset($_SESSION['kit_info']);
		$_SESSION['kit_info']->option_type_pick_list					=	array(	'F'=>$this->lang->line('items_kit_option_type_F'),
																					'O'=>$this->lang->line('items_kit_option_type_O'));
		$_SESSION['kit_info']->kit_option_type							=	'F';

		// top level item info
		$_SESSION['kit_info']->item_info								=	$this->Item->get_info($item_id);

		// get kit data
		$_SESSION['kit_info']->kit_structure							=	$this->Item->get_kit_structure($_SESSION['kit_info']->item_info->kit_reference)->result_array();

		// set dialog switch
		$_SESSION['show_dialog']										=	15;

		// show the data entry
		redirect("items");
	}

	// add a kit structure option
	function kit_structure_add($item_id, $kit_reference)
	{
		// get data inputs
		$_SESSION['kit_info']->kit_option								=	$this->input->post('new1_kit_option');
		$_SESSION['kit_info']->kit_option_type							=	$this->input->post('new1_kit_option_type');
		$_SESSION['kit_info']->kit_option_qty							=	$this->input->post('new1_kit_option_qty');


		// test that this kit reference exists. It should always exist if I am in this method
		if (empty($kit_reference))
		{
			// set message
			$_SESSION['error_code']										=	'07000';
			redirect("items");
		}

		// data verification
		// kit option cannot be blank
		if (empty($_SESSION['kit_info']->kit_option))
		{
			// set message
			$_SESSION['error_code']										=	'07010';
			redirect("items");
		}

		// kit_option already exists?
		$db_result						=	$this->Item->get_kit_structure_option($kit_reference, $_SESSION['kit_info']->kit_option)->row_array();
		if (!empty($db_result))
		{
			// set message
			$_SESSION['error_code']										=	'07020';
			redirect("items");
		}

		// kit option cannot contain blanks
		if (preg_match('/\s/', $_SESSION['kit_info']->kit_option))
		{
			// set message
			$_SESSION['error_code']										=	'07100';
			redirect("items");
		}

		// qty must be numeric and > 0
		if (!is_numeric($_SESSION['kit_info']->kit_option_qty) OR $_SESSION['kit_info']->kit_option_qty <= 0)
		{
			// set message
			$_SESSION['error_code']										=	'07030';
			redirect("items");
		}

		// tests passed so add record
		$kit_structure_data 		= 	array	(
												'kit_reference'			=>	$kit_reference,
												'kit_option'			=>	$_SESSION['kit_info']->kit_option,
												'kit_option_qty'		=>	$_SESSION['kit_info']->kit_option_qty,
												'kit_option_type'		=>	$_SESSION['kit_info']->kit_option_type,
												'branch_code' 			=>	$this->config->item('branch_code')
												);

		if ($this->	Item->add_kit_structure($kit_structure_data))
		{
			// set message
			$_SESSION['error_code']										=	'07040';
		}
		else
		{
			// set message
			$_SESSION['error_code']										=	'07050';
		}

		// redirect
		redirect("items/kit/$item_id");
	}

	// delete a KIT structure
	function kit_structure_delete($item_id, $kit_reference, $kit_option)
	{
		if ($this->Item->delete_kit_structure($kit_reference, $kit_option))
		{
			// set message
			$_SESSION['error_code']										=	'07070';
		}
		else
		{
			// set message
			$_SESSION['error_code']										=	'07060';
		}

		// redirect
		redirect("items/kit/$item_id");
	}

	function kit_detail($kit_reference, $kit_option, $kit_option_type)
	{
		// initialise
		unset($_SESSION['kit_info']->kit_detail);
		$_SESSION['kit_info']->kit_reference							=	$kit_reference;
		$_SESSION['kit_info']->kit_option								=	$kit_option;
		$_SESSION['kit_info']->kit_option_type							=	$kit_option_type;

		// get kit detail data
		$_SESSION['kit_info']->kit_detail								=	$this->Item->get_kit_detail_option($kit_reference, $kit_option)->result_array();

		// get kit detail item description
		foreach ($_SESSION['kit_info']->kit_detail as $key => $row)
		{
			$item_id													=	$this->Item->get_item_id($row['item_number']);
			$kit_item_info												=	$this->Item->get_info($item_id);
			$_SESSION['kit_info']->kit_detail[$key]['name']				=	$kit_item_info->name;
		}

		// set dialog switch
		$_SESSION['show_dialog']										=	16;
		$_SESSION['origin']												=	'KD';

		// show the data entry
		redirect("items");
	}

	// add a kit structure option
	function kit_detail_add($kit_reference, $kit_option, $kit_option_type)
	{
		// get data inputs
		$_SESSION['kit_info']->kit_item_number							=	$this->input->post('new1_item_number');

		// kit option type = F, then only one entry in detail file allowed.
		if ($kit_option_type == 'F')
		{
			// get kit option detail
			$db_detail_result											=	$this->Item->get_kit_detail_option($kit_reference, $kit_option)->result_array();

			// number of detail rows cannot be >1 for a fixed item
			if (count($db_detail_result) >= 1)
			{
				// set message
				$_SESSION['error_code']									=	'07130';
				redirect("items");
			}
		}

		// test for valid item number
		$kit_item_info					=	array();
		$kit_item_id					=	$this->Item->get_item_id($_SESSION['kit_info']->kit_item_number);
		$kit_item_info					=	$this->Item->get_info($kit_item_id);

		if (empty($kit_item_info->item_id))
		{
			// set message
			$_SESSION['error_code']										=	'07140';
			redirect("items");
		}

		// test item number not already in structure
		$kit_item_result				=	array();
		$kit_item_result				=	$this->Item->get_kit_item($kit_reference, $kit_item_number)->result_array();

		if (!empty($kit_item_result))
		{
			// set message
			$_SESSION['error_code']										=	'07150';
			redirect("items");
		}

		// tests passed so add record
		$kit_detail_data 				= 	array	(
													'kit_reference'		=>	$kit_reference,
													'kit_option'		=>	$kit_option,
													'item_number'		=>	$_SESSION['kit_info']->kit_item_number,
													'branch_code' 		=>	$this->config->item('branch_code')
													);

		if ($this->	Item->add_kit_detail($kit_detail_data))
		{
			$_SESSION['error_code']										=	'07160';
		}
		else
		{
			$_SESSION['error_code']										=	'07170';
		}

		// redirect
		redirect("items/kit_detail/$kit_reference/$kit_option/$kit_option_type");
	}

	// delete a KIT detail
	function kit_detail_delete($kit_reference, $kit_option, $item_number, $kit_option_type)
	{
		if ($this->Item->delete_kit_detail($kit_reference, $kit_option, $item_number))
		{
			// set message
			$_SESSION['error_code']										=	'07180';
		}
		else
		{
			// set message
			$_SESSION['error_code']										=	'07190';
		}

		// redirect
		redirect("items/kit_detail/$kit_reference/$kit_option/$kit_option_type");
	}

//	------------------------------------------------------------------------------------------
//
//	------------------------------------------------------------------------------------------


	//
	function reset_rolling()
	{
		// set where_select
		$where_select					=	'deleted = 0';

		// set rolling indicator
		$rolling_inventory_indicator	=	0;

		// set item_data
		$item_data 						= 	array	(
													'rolling_inventory_indicator'	=>	$rolling_inventory_indicator
													);

		// update the database
		$this->Item->update_all($item_data, $where_select);
	}

	// create price label
	function label_form($item_id)
	{
		// set session
		$_SESSION['transaction_info']				=	new stdClass();
		$_SESSION['$title']							=	$this->lang->line('items_label');
		$_SESSION['show_dialog']					=	7;
		$_SESSION['original_item_id']				=	$item_id;
		unset($_SESSION['label_show']);
		unset($_SESSION['origin']);

		// get the part details
		$_SESSION['transaction_info']				= 	$this->Item->get_info($item_id);

		// get the unit price from default price list

		// calculate sales price
		$tax_percentage								=	$this->config->item('default_tax_1_rate');
		$tax										=	1 + ($tax_percentage / 100);
		$pieces 									=	explode("/", $this->config->item('numberformat'));
		$_SESSION['transaction_info']->sales_price	=	number_format(($_SESSION['transaction_info']->unit_price * $tax), 2, $pieces[1], $pieces[2]);

		// show the data entry
		redirect("items");
	}

	function label_do()
	{
		// get data entry
		$_SESSION['transaction_info']->item_number	=	$this->input->post('item_number');
		$_SESSION['transaction_info']->name			=	$this->input->post('name');
		$_SESSION['transaction_info']->category		=	$this->input->post('category');

		// Set Path to Font File
		$font_path						=	$this->config->item('default_label_font');

		// load image from template
		$base_image 					=	imagecreatefrompng($this->config->item('default_label_image'));
		if (!$base_image)
		{
			// set message
			$_SESSION['error_code']		=	'01570';
			redirect("items");
		}

		// Allocate A Color For The Text
		$colour							=	imagecolorallocate($base_image, 0, 0, 0);

		// Print item number On Image
		if (!empty($this->input->post('item_number')))
		{
			imagettftext($base_image, 10, 0, 2, 15, $colour, $font_path, $this->input->post('item_number'));
		}

		// Print description On Image
		if (!empty($this->input->post('name')))
		{
			$this->load->helper('text');
			imagettftext($base_image, 10, 0, 65, 15, $colour, $font_path, word_wrap($this->input->post('name'), 18));
		}

		// Print category On Image
		if (!empty($this->input->post('category')))
		{
			imagettftext($base_image, 7, 0, 2, 40, $colour, $font_path, $this->input->post('category'));
		}

		// Print price and currency symbol On Image
		imagettftext($base_image, 30, 0, 60, 105, $colour, $font_path, $_SESSION['transaction_info']->sales_price.' '.$_SESSION['G']->currency_details->currency_sign);

		// Send Image to file NOTE - VERY IMPORTANT : DISABLE SELINUX for this to work
		//$_SESSION['transaction_info']->image_path	=	$this->config->item('default_label_store').$_SESSION['original_item_id'].'.png';
        $_SESSION['transaction_info']->image_path	=	'/var/www/html/wrightetmathon/label/'.$_SESSION['original_item_id'].'.png';


		imagepng($base_image, $_SESSION['transaction_info']->image_path, 0, NULL);

		// Clear Memory
		imagedestroy($base_image);

		// test if image produced
		if (!file_exists($_SESSION['transaction_info']->image_path))
		{
			// set message
			$_SESSION['error_code']		=	'01580';
			redirect("items");
		}
		else
		{
			// set message
			$_SESSION['label_show']		=	1;
			$_SESSION['transaction_info']->server_image	=	$_SESSION['original_item_id'].'.png';
			$_SESSION['error_code']		=	'01590';
			unset($_SESSION['original_item_id']);
			redirect("items");
		}
	}

	function bulk_update()
	{
		$items_to_update				=	$this->input->post('item_ids');
		$item_data 						=	array();
		foreach($_POST as $key=>$value)
		{
			if($value != '' AND !(in_array($key, array('submit', 'item_ids', 'tax_names', 'tax_percents'))))
			{
				$item_data["$key"]		=	$value;
			}
		}

		// now set the branch code
		$key							=	'branch_code';
		$value							=	$this->config->item('branch_code');
		$item_data["$key"] 				=	$value;

		//Item data could be empty if tax information is being updated
		if(empty($item_data) || $this->Item->update_multiple($item_data, $items_to_update))
		{
			$items_taxes_data 			=	array();
			$tax_names 					=	$this->input->post('tax_names');
			$tax_percents				=	$this->input->post('tax_percents');
			for($k=0;$k<count($tax_percents);$k++)
			{
				if (is_numeric($tax_percents[$k]))
				{
					$items_taxes_data[] = array	(
												'name'			=>	$tax_names[$k],
												'percent'		=>	$tax_percents[$k],
												'branch_code'	=>	$this->config->item('branch_code')
												);
				}
			}

			$this->Item_taxes->save_multiple($items_taxes_data, $items_to_update);

			// set success indicator
			$success_or_failure			=	'S';
			$message					=	$this->lang->line('items_successful_bulk_edit');
		}
		else
		{
			// set error indicator
			$success_or_failure			=	'F';
			$message					=	$this->lang->line('items_error_updating_multiple');
		}

		// set flash data and return to controller
		$this							->	setflash($success_or_failure, $message);
	}


	function delete()
	{
		// check actual quantity on hand is zero
		if ($_SESSION['transaction_info']->quantity != 0)
		{
			// set error message
			$_SESSION['error_code']			=	'01480';
			$_SESSION['del']				=	1;
			redirect("items");
		}

		// now do delete
		if($this->Item->delete($_SESSION['transaction_info']->item_id))    ///var/www/html/wrightetmathon/application/models/item.php ligne 543
		{
			// set success message
			$_SESSION['error_code']			=	'00420';
			$_SESSION['del']				=	1;
		}
		else
		{
			$_SESSION['error_code']			=	'00410';
		}

		// add inventory record
		$inv_data = array	(
							'trans_date'		=>	date('Y-m-d H:i:s'), //Wright modified 18/01/2014
							'trans_items'		=>	$_SESSION['transaction_info']->item_id,
							'trans_user'		=>	$this->Employee->get_logged_in_employee_info()->person_id,
							'trans_comment'		=>	$this->lang->line('items_deleted'),
							'trans_stock_before'=>	$_SESSION['transaction_info']->quantity,
							'trans_inventory'	=>	0,
							'trans_stock_after'	=>	$_SESSION['transaction_info']->quantity,
							'branch_code'		=>	$this->config->item('branch_code')
							);
		$this->Inventory->insert($inv_data);

		// return to controller
		redirect("items");
	}

	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 600; //Wright modified 18/01/2014
	}

	function clone_form()
	{
		// set session data
		unset($_SESSION['clone_from_id']);
		unset($_SESSION['clone_to_id']);
		$_SESSION['new'] 					=	1;
		$_SESSION['clone'] 					=	1;
		$_SESSION['$title']					=	$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_clone');

		// set dialog switch
		$_SESSION['show_dialog']			=	2;

		// show the data entry
		redirect("items");
	}

	function merge_form($merge_step=0)
	{
		// set session data
		unset($_SESSION['merge_from_id']);
		unset($_SESSION['merge_to_id']);
		unset($_SESSION['new']);
		unset($_SESSION['clone']);
		unset($_SESSION['merge_ok']);
		$_SESSION['merge']					=	1;
		$_SESSION['$title']					=	$this->lang->line($_SESSION['G']->modules[$_SESSION['module_id']]['module_name'].'_merge');

		// set dialog switch
		$_SESSION['show_dialog']			=	5;

		// show the data entry
		redirect("items");
	}

	function merge_do()
	{
		// test confirm
		if ($_SESSION['merge_ok'] != 2)
		{
			// intialise
			$_SESSION['transaction_info']		=	new stdClass();
			$_SESSION['transaction_from']		=	new stdClass();
			$_SESSION['transaction_to']			=	new stdClass();

			// get data
			$_SESSION['transaction_info']->merge_from_id	=	$this->input->post('merge_from_id');
			$_SESSION['transaction_info']->merge_to_id		=	$this->input->post('merge_to_id');

			// verify input
			$this->verify_merge();

			// verifications are ok, so ask for confirmation
			$_SESSION['merge_ok']				=	1;
			redirect("items");
		}

		// 	if here merge is confirmed so do updates
		//	calculate reorder pack size
		if ($_SESSION['transaction_from']->reorder_pack_size 	>	$_SESSION['transaction_to']->reorder_pack_size)
		{
			$_SESSION['transaction_to']->reorder_pack_size		=	$_SESSION['transaction_from']->reorder_pack_size;
		}

		// update to item with merged data
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_info']->item_id					=	$_SESSION['transaction_to']->item_id;
		$_SESSION['transaction_info']->quantity					=	$_SESSION['transaction_from']->quantity + $_SESSION['transaction_to']->quantity;
		$_SESSION['transaction_info']->reorder_pack_size		=	$_SESSION['transaction_to']->reorder_pack_size;
		$_SESSION['transaction_info']->sales_ht					=	$_SESSION['transaction_from']->sales_ht + $_SESSION['transaction_to']->sales_ht;
		$_SESSION['transaction_info']->sales_qty				=	$_SESSION['transaction_from']->sales_qty + $_SESSION['transaction_to']->sales_qty;
		$this->Item->save();

		// now reduce from article quantity to 0 and delete
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_info']->item_id					=	$_SESSION['transaction_from']->item_id;
		$_SESSION['transaction_info']->quantity					=	0;
		$_SESSION['transaction_info']->sales_ht					=	0;
		$_SESSION['transaction_info']->sales_qty				=	0;
		$_SESSION['transaction_info']->deleted					=	1;
		$this->Item->save();

		// check if both items are subject to DLUO
		if ($_SESSION['transaction_from']->dluo_indicator == 'Y' AND $_SESSION['transaction_to']->dluo_indicator == 'Y')
		{
			// get from DLUO info
			$merge_from_dluo_info	=	array();
			$merge_from_dluo_info	=	$this->Item->get_info_dluo($_SESSION['transaction_from']->item_id)->result_array();
			foreach ($merge_from_dluo_info as $dluo_record)
			{
				// change the item id from DLUO data to the TO item id
				$update_data		=	array	(
												'item_id'	=>	$_SESSION['transaction_to']->item_id
												);
				if (!$this->Item->dluo_edit($dluo_record['year'], $dluo_record['month'], $update_data))
				{
						// set message
						$_SESSION['error_code']		=	'00680';
						redirect("items");
				}
			}
		}

		// now write an inventory record to each item
		$employee_id							=	$this->Employee->get_logged_in_employee_info()->person_id;

		// from item
		$inv_data = array	(
							'trans_date'		=>	date('Y-m-d H:i:s'),
							'trans_items'		=>	$_SESSION['transaction_from']->item_id,
							'trans_user'		=>	$employee_id,
							'trans_comment'		=>	$_SESSION['transaction_from']->item_number.' => '.$this->lang->line('items_merge_with').' => '.$_SESSION['transaction_to']->item_number,
							'trans_stock_before'=>	$_SESSION['transaction_from']->quantity,
							'trans_inventory'	=>	$_SESSION['transaction_from']->quantity * -1,
							'trans_stock_after'	=>	0,
							'branch_code'		=>	$this->config->item('branch_code')
							);
		$this->Inventory->insert($inv_data);
		sleep(2);
		$inv_data = array	(
							'trans_date'		=>	date('Y-m-d H:i:s'),
							'trans_items'		=>	$_SESSION['transaction_from']->item_id,
							'trans_user'		=>	$employee_id,
							'trans_comment'		=>	$this->lang->line('items_merge_deleted').' => '.$_SESSION['transaction_to']->item_number,
							'trans_stock_before'=>	0,
							'trans_inventory'	=>	0,
							'trans_stock_after'	=>	0,
							'branch_code'		=>	$this->config->item('branch_code')
							);
		$this->Inventory->insert($inv_data);

		// to item
		$inv_data = array	(
							'trans_date'		=>	date('Y-m-d H:i:s'),
							'trans_items'		=>	$_SESSION['transaction_to']->item_id,
							'trans_user'		=>	$employee_id,
							'trans_comment'		=>	$_SESSION['transaction_to']->item_number.' => '.$this->lang->line('items_merge_from').' => '.$_SESSION['transaction_from']->item_number,
							'trans_stock_before'=>	$_SESSION['transaction_to']->quantity,
							'trans_inventory'	=>	$_SESSION['transaction_from']->quantity,
							'trans_stock_after'	=>	$_SESSION['transaction_to']->quantity + $_SESSION['transaction_from']->quantity,
							'branch_code'		=>	$this->config->item('branch_code')
							);
		$this->Inventory->insert($inv_data);

		// now update sales history for the from item - this is done to preserve history for the reorder process.
		$sales_items				=	array();
		$sales_items				=	$this->Sale->get_sales_items_by_item_id($_SESSION['transaction_from']->item_id)->result_array();
		foreach ($sales_items as $sales_item)
		{
			// now update the line changing the item_id to the merge to item id
			$update_data	= array	(
									'item_id'		=>	$_SESSION['transaction_to']->item_id,
									'description'	=>	$this->lang->line('items_merge_sales_desc').$_SESSION['transaction_to']->item_id
									);
			$this->Sale->update_line($update_data, $sales_item['sale_id'], $_SESSION['transaction_from']->item_id, $sales_item['line']);

			// now update the sales taxes info.
			$update_data	= array	(
									'item_id'		=>	$_SESSION['transaction_to']->item_id
									);
			$this->Sale->update_sales_taxes_by_item_id($update_data, $sales_item['sale_id'], $_SESSION['transaction_from']->item_id, $sales_item['line']);
		}

		// success
		// now show DLUO screen to allow user to correct any errors if dluo applies
		if ($_SESSION['transaction_to']->dluo_indicator == 'Y')
		{
			$origin					=	' ';
			$line					=	0;
			$success_or_failure		=	'S';
			$message				=	$this->lang->line('items_merge_successfull').' => '.$_SESSION['transaction_from']->item_number.' => '.$_SESSION['transaction_to']->item_number;
			$this					->	dluo_form($_SESSION['transaction_to']->item_id, $origin, $line);
		}
		else
		{
			// set message
			$_SESSION['transaction_info']							=	new stdClass();
			$_SESSION['transaction_info']->merge_from_id			=	$_SESSION['transaction_from']->item_number;
			$_SESSION['transaction_info']->merge_to_id				=	$_SESSION['transaction_to']->item_number;
			$_SESSION['error_code']									=	'00650';
			redirect("items");
		}
	}

	function verify()
	{
		// verify name entered if add
		if 	(($_SESSION['new'] ?? 0) == 1 AND $_SESSION['transaction_info']->item_number == NULL)
		{
			// set message
			$_SESSION['error_code']										=	'01400';
			redirect("items");
		}

		// verify item_number not duplicate if add
		if 	($_SESSION['new'] == 1)
		{
			$success													=	$this->Item->check_duplicate($_SESSION['transaction_info']->item_id, $_SESSION['transaction_info']->item_number);
			if (!$success)
			{
				// set message
				$_SESSION['error_code']									=	'00280';
				redirect("items");
			}
		}

		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_info']->item_number)
			OR 	empty($_SESSION['transaction_info']->name)
			OR 	empty($_SESSION['transaction_info']->category_id)
			OR 	empty($_SESSION['transaction_tax_info']->tax_name_1)
			OR 	empty($_SESSION['transaction_tax_info']->tax_percent_1)
			OR 	empty($_SESSION['transaction_info']->dluo_indicator)
			OR 	empty($_SESSION['transaction_info']->giftcard_indicator)
			)
		{
			// set message

			$_SESSION['error_code']			=	'00030';
			redirect("items");
		}

		// check volume is numeric
		if (!is_numeric($_SESSION['transaction_info']->volume))
		{
			// set message
			$_SESSION['error_code']			=	'01660';
			redirect("items");
		}

		// check nicotine is numeric
		if (!is_numeric($_SESSION['transaction_info']->nicotine))
		{
			// set message
			$_SESSION['error_code']			=	'01670';
			redirect("items");
		}

		// check unit_price not less than cost_price = cannot sell at a loss
		//if ($_SESSION['transaction_info']->unit_price < $_SESSION['transaction_info']->cost_price)
		//{
			// set message
			//$_SESSION['error_code']			=	'01470';
			//redirect("items");
		//}

		// check tax_percent_1 is numeric
		if (!is_numeric($_SESSION['transaction_tax_info']->tax_percent_1))
		{
			// set message
			$_SESSION['error_code']			=	'01430';
			redirect("items");
		}

		// test kit reference if 'Y'
		if ($_SESSION['transaction_info']->DynamicKit == 'Y')
		{
			// kit cannot be blank
			if ($_SESSION['transaction_info']->kit_reference == null)
			{
				// set message
				$_SESSION['error_code']		=	'00240';
				redirect("items");
			}

			// kit cannot already exist on another item
			$kit_info					=	array();
			$kit_info					=	$this->Item->get_kit_reference($_SESSION['transaction_info']->kit_reference)->result_array();

			if (count($kit_info) > 1)
			{
				// set message
				$_SESSION['error_code']			=	'00250';
				redirect("items");
			}
		}

		// if DynamicKit is N, force kit reference blank
		if ($_SESSION['transaction_info']->DynamicKit == 'N')
		{
			$_SESSION['transaction_info']->kit_reference = NULL;
		}

		// if image file name is not blank test it exists - NOTE for now this doesn't work, need to look at this when setting up central.
		// test for central only
		//if ()
		//{
		//}
	}

	function verify_warehouse()
	{
		// verify warehouse code
		if (!$this->Warehouse->exists($_SESSION['transaction_add_warehouse_info']->warehouse_code))
		{
			// set message
			$_SESSION['error_code']				=	'01730';
			redirect("items");
		}

		// get warehouse defintion
		$warehouse	=	$this->Warehouse->get_info($_SESSION['transaction_add_warehouse_info']->warehouse_code);

		// verify row is in range
		if (!empty($_SESSION['transaction_add_warehouse_info']->warehouse_row))
		{
			if ($_SESSION['transaction_add_warehouse_info']->warehouse_row < $warehouse->warehouse_row_start OR
				$_SESSION['transaction_add_warehouse_info']->warehouse_row > $warehouse->warehouse_row_end)
			{
				// set message
				$_SESSION['error_code']			=	'01740';
				redirect("items");
			}
		}

		// verify section is in range
		if (!empty($_SESSION['transaction_add_warehouse_info']->warehouse_section))
		{
			if ($_SESSION['transaction_add_warehouse_info']->warehouse_section < $warehouse->warehouse_section_start OR
				$_SESSION['transaction_add_warehouse_info']->warehouse_section > $warehouse->warehouse_section_end)
			{
				// set message
				$_SESSION['error_code']			=	'01750';
				redirect("items");
			}
		}

		// verify shelf is in range
		if (!empty($_SESSION['transaction_add_warehouse_info']->warehouse_shelf))
		{
			if ($_SESSION['transaction_add_warehouse_info']->warehouse_shelf < $warehouse->warehouse_shelf_start OR
				$_SESSION['transaction_add_warehouse_info']->warehouse_shelf > $warehouse->warehouse_shelf_end)
			{
				// set message
				$_SESSION['error_code']			=	'01760';
				redirect("items");
			}
		}

		// verify bin is in range
		if (!empty($_SESSION['transaction_add_warehouse_info']->warehouse_bin))
		{
			if ($_SESSION['transaction_add_warehouse_info']->warehouse_bin < $warehouse->warehouse_bin_start OR
				$_SESSION['transaction_add_warehouse_info']->warehouse_bin > $warehouse->warehouse_bin_end)
			{
				// set message
				$_SESSION['error_code']			=	'01770';
				redirect("items");
			}
		}

		// if warehouse preferred indicator is Y, check its the only Y
		if ($_SESSION['transaction_add_warehouse_info']->location_preferred == 'Y')
		{
			$count								=	$this->Item->item_warehouse_preferred_unique();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']			=	'01900';
				redirect("items");
			}
		}

		// if warehouse preferred indicator is N, check that there is a Y
		if ($_SESSION['transaction_add_warehouse_info']->location_preferred == 'N')
		{
			$count								=	$this->Item->item_warehouse_preferred_y();
			if ($count == 0)
			{
				// set message
				$_SESSION['error_code']			=	'01910';
				redirect("items");
			}
		}
	}

	function verify_supplier()
	{
		// verify required fields are entered
		if 	(	empty($_SESSION['transaction_add_supplier_info']->supplier_id)
			OR 	empty($_SESSION['transaction_add_supplier_info']->supplier_preferred)
			OR	empty($_SESSION['transaction_add_supplier_info']->supplier_cost_price)
			OR 	empty($_SESSION['transaction_add_supplier_info']->supplier_reorder_policy)
			)
		{
			// set message

			$_SESSION['error_code']			=	'00030';
			redirect("items");
		}

		// verify supplier is valid code
		if (!$this->Supplier->exists($_SESSION['transaction_add_supplier_info']->supplier_id))
		{
			// set message
			$_SESSION['error_code']			=	'01790';
			redirect("items");
		}

		// verify item/supplier does not exit already
		$count								=	$this->Item->item_supplier_combo_exists();
		if ($count > 0)
		{
			// set message
			$_SESSION['error_code']			=	'01810';
			redirect("items");
		}

		// if supplier preferred indicator is Y, check its the only Y
		if ($_SESSION['transaction_add_supplier_info']->supplier_preferred == 'Y')
		{
			$count								=	$this->Item->item_supplier_preferred_unique();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']			=	'01820';
				redirect("items");
			}
		}

		// if supplier preferred indicator is N, check that there is a Y
		if ($_SESSION['transaction_add_supplier_info']->supplier_preferred == 'N')
		{
			$count								=	$this->Item->item_supplier_preferred_y();
			if ($count == 0)
			{
				// set message
				$_SESSION['error_code']			=	'01830';
				redirect("items");
			}
		}

		// check item_number duplicate
		if (!empty($_SESSION['transaction_add_supplier_info']->supplier_item_number))
		{
			$count								=	$this->Item->check_supplier_item_number_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']			=	'01800';
				redirect("items");
			}
		}

		// check cost_price is numeric
		if (!is_numeric($_SESSION['transaction_add_supplier_info']->supplier_cost_price))
		{
			// set message
			$_SESSION['error_code']			=	'01410';
			redirect("items");
		}

		// If reorder_policy is Y, reorder data must be entered
		if ($_SESSION['transaction_add_supplier_info']->supplier_reorder_policy == 'Y')
		{
			if ($_SESSION['transaction_add_supplier_info']->supplier_reorder_pack_size <= 0)
			{
				// set message
				$_SESSION['error_code']		=	'00190';
				redirect("items");
			}

			if ($_SESSION['transaction_add_supplier_info']->supplier_min_order_qty <= 0)
			{
				// set message
				$_SESSION['error_code']		=	'00190';
				redirect("items");
			}

			if ($_SESSION['transaction_add_supplier_info']->supplier_min_stock_qty <= 0)
			{
				// set message
				$_SESSION['error_code']		=	'00190';
				redirect("items");
			}

			// check reorder_pack_size is numeric
			if (!is_numeric($_SESSION['transaction_add_supplier_info']->supplier_reorder_pack_size))
			{
				// set message
				$_SESSION['error_code']			=	'01460';
				redirect("items");
			}

			// check min_order_qty is numeric
			if (!is_numeric($_SESSION['transaction_add_supplier_info']->supplier_min_order_qty))
			{
				// set message
				$_SESSION['error_code']			=	'01463';
				redirect("items");
			}

			// check min_stock_qty is numeric
			if (!is_numeric($_SESSION['transaction_add_supplier_info']->supplier_min_stock_qty))
			{
				// set message
				$_SESSION['error_code']			=	'01465';
				redirect("items");
			}
		}

		// If reorder_policy is N, force reorder data zero
		if ($_SESSION['transaction_add_supplier_info']->supplier_reorder_policy == 'N')
		{
			$_SESSION['transaction_add_supplier_info']->supplier_reorder_pack_size = 0;
			$_SESSION['transaction_add_supplier_info']->supplier_min_order_qty = 0;
			$_SESSION['transaction_add_supplier_info']->supplier_min_stock_qty = 0;
		}

		// check bar_code duplicate on this supplier
		if (!empty($_SESSION['transaction_add_supplier_info']->supplier_bar_code))
		{
			$count								=	$this->Item->check_supplier_bar_code_duplicate();
			if ($count > 0)
			{
				// set message
				$_SESSION['error_code']			=	'01860';
				redirect("items");
			}
		}
	}

	function list_deleted()
	{
		// set flag to select deleted items
		$_SESSION['undel']					=	1;
		$_SESSION['reactivation']=1;
		redirect("items");
	}

	function undelete()
	{
		if ($this->Item->undelete())
		{
			// set success message
			$_SESSION['error_code']			=	'00520';

			// add inventory record
			$inv_data = array	(
							'trans_date'		=>	date('Y-m-d H:i:s'), //Wright modified 18/01/2014
							'trans_items'		=>	$_SESSION['transaction_info']->item_id,
							'trans_user'		=>	$this->Employee->get_logged_in_employee_info()->person_id,
							'trans_comment'		=>	$this->lang->line('items_undeleted'),
							'trans_stock_before'=>	$_SESSION['transaction_info']->quantity,
							'trans_inventory'	=>	0,
							'trans_stock_after'	=>	$_SESSION['transaction_info']->quantity,
							'branch_code'		=>	$this->config->item('branch_code')
							);
			$this->Inventory->insert($inv_data);

			// update session data
			unset($_SESSION['undel']);
			$_SESSION['transaction_info']->deleted =	0;
			$_SESSION['$title']		=	$this->lang->line('common_edit').'  '.$_SESSION['transaction_info']->name;

			// redirect
			$this->															view($_SESSION['transaction_info']->item_id);
		}
		else
		{
			$_SESSION['error_code']			=	'00410';
			redirect("items");
		}
	}

	function verify_clone()
	{
		// check input not blank
		if (empty($_SESSION['transaction_info']->clone_from_id) OR empty($_SESSION['transaction_info']->clone_to_id))
		{
			// set message
			$_SESSION['error_code']		=	'00030';
			redirect("items");
		}

		// check input not same
		if ($_SESSION['transaction_info']->clone_from_id == $_SESSION['transaction_info']->clone_to_id)
		{
			// set message
			$_SESSION['error_code']		=	'00790';
			redirect("items");
		}

		// check clone_from_item valid
		if (!$this->Item->get_item_id($_SESSION['transaction_info']->clone_from_id))
		{
			// set message
			$_SESSION['error_code']		=	'00800';
			redirect("items");
		}

		// check clone_from_item not deleted
		// save entered data
		$clone_from_id					=	$_SESSION['transaction_info']->clone_from_id;
		$clone_to_id					=	$_SESSION['transaction_info']->clone_to_id;

		// get item info
		$_SESSION['transaction_info']	=	$this->Item->get_info($this->Item->get_item_id($_SESSION['transaction_info']->clone_from_id));

		// restore data
		$_SESSION['transaction_info']->clone_from_id	=	$clone_from_id;
		$_SESSION['transaction_info']->clone_to_id		=	$clone_to_id;

		// test deleted
		if ($_SESSION['transaction_info']->deleted == 1)
		{
			// set message
			$_SESSION['error_code']		=	'00810';
			redirect("items");
		}

		// check clone_to_item does not exist
		if ($this->Item->get_item_id($_SESSION['transaction_info']->clone_to_id))
		{
			// set message
			$_SESSION['error_code']		=	'00820';
			redirect("items");
		}

		// check clone_to good format
		if (substr($_SESSION['transaction_info']->clone_to_id, 0, 2) != "SO")
		{
			// set message
			$_SESSION['error_code']		=	'00830';
			redirect("items");
		}
	}

	function verify_merge()
	{
		// check input not blank
		if (empty($_SESSION['transaction_info']->merge_from_id) OR empty($_SESSION['transaction_info']->merge_to_id))
		{
			// set message
			$_SESSION['error_code']		=	'00030';
			redirect("items");
		}

		// check input not same
		if ($_SESSION['transaction_info']->merge_from_id == $_SESSION['transaction_info']->merge_to_id)
		{
			// set message
			$_SESSION['error_code']		=	'00555';
			redirect("items");
		}

		// check merge_from_item valid
		if (!$this->Item->get_item_id($_SESSION['transaction_info']->merge_from_id))
		{
			// set message
			$_SESSION['error_code']		=	'00560';
			redirect("items");
		}

		// check merge_from_item not deleted
		// get item info
		$_SESSION['transaction_from']	=	$this->Item->get_info($this->Item->get_item_id($_SESSION['transaction_info']->merge_from_id));

		// test deleted
		if ($_SESSION['transaction_from']->deleted == 1)
		{
			// set message
			$_SESSION['error_code']		=	'00565';
			redirect("items");
		}

		// check merge_to_item valid
		if (!$this->Item->get_item_id($_SESSION['transaction_info']->merge_to_id))
		{
			// set message
			$_SESSION['error_code']		=	'00570';
			redirect("items");
		}

		// check merge_to_item not deleted
		// get item info
		$_SESSION['transaction_to']		=	$this->Item->get_info($this->Item->get_item_id($_SESSION['transaction_info']->merge_to_id));

		// test deleted
		if ($_SESSION['transaction_to']->deleted == 1)
		{
			// set message
			$_SESSION['error_code']		=	'00575';
			redirect("items");
		}
	}

	function verify_dluo_add()
	{
		// check input not blank
		if (empty($_SESSION['transaction_info']->dluo_year1) OR empty($_SESSION['transaction_info']->dluo_month1) OR empty($_SESSION['transaction_info']->dluo_qty1))
		{
			// set message
			$_SESSION['error_code']		=	'00030';
			redirect("items");
		}

		// check input numeric
		if (!is_numeric($_SESSION['transaction_info']->dluo_year1) OR !is_numeric($_SESSION['transaction_info']->dluo_month1) OR !is_numeric($_SESSION['transaction_info']->dluo_qty1))
		{
			// set message
			$_SESSION['error_code']		=	'00940';
			redirect("items");
		}

		// check year is not in past
		if ($_SESSION['transaction_info']->dluo_year1 < date('Y'))
		{
			// set message
			$_SESSION['error_code']		=	'01530';
			redirect("items");
		}

		// check month is not in past
		if ($_SESSION['transaction_info']->dluo_year1 == date('Y') AND $_SESSION['transaction_info']->dluo_month1 < date('m'))
		{
			// set message
			$_SESSION['error_code']		=	'01540';
			redirect("items");
		}

		// check month is in range
		if ($_SESSION['transaction_info']->dluo_month1 < 01 OR $_SESSION['transaction_info']->dluo_month1 > 12)
		{
			// set message
			$_SESSION['error_code']		=	'01550';
			redirect("items");
		}

		// check month is in range
		if ($_SESSION['transaction_info']->dluo_qty1 <= 0)
		{
			// set message
			$_SESSION['error_code']		=	'01560';
			redirect("items");
		}
	}

	function verify_dluo_edit()
	{
		// check input not blank
		if (empty($_SESSION['transaction_info']->dluo_qty1))
		{
			// set message
			$_SESSION['error_code']		=	'00030';
			redirect("items");
		}

		// check input numeric
		if (!is_numeric($_SESSION['transaction_info']->dluo_qty1))
		{
			// set message
			$_SESSION['error_code']		=	'00940';
			redirect("items");
		}

		// check dluo qty is ok
		if ($_SESSION['transaction_info']->dluo_qty1 <= 0)
		{
			// set message
			$_SESSION['error_code']		=	'01560';
			redirect("items");
		}
	}
}
?>
