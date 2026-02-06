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
		switch ($_SESSION['undel'] ?? 0)
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

		// override per_page from session if set
		if (isset($_SESSION['items_per_page'])) {
			$config['per_page'] = ($_SESSION['items_per_page'] == 0) ? 100000 : $_SESSION['items_per_page'];
		}

		// Vérifier d'abord si le filtre nouveautés est actif (prioritaire)
		if (isset($_SESSION['filtre_nouveautes']) && $_SESSION['filtre_nouveautes'] == 1) {
			$_SESSION['controller_name'] = strtolower(get_class($this)) . '_news';
			$data['items'] = $this->Item->get_new_after_date(15);
		}
		else {
			switch($_SESSION['filtre'] ?? 0)
			{
				case 1:
					//Si le filtre est actif, alors la recherche (si elle existe) est conservé
					if (isset($_SESSION['filtre_recherche'])) {
						// get items
						$data['items']													=	$this->Item->search($_SESSION['filtre_recherche']);
						if (is_numeric($_SESSION['filtre_recherche'])) {
							$data_item = $this->Item->get_info($_SESSION['filtre_recherche']);
							$item_id = $data_item->name;
						} else {
							$item_id = $_SESSION['filtre_recherche'];
						}
						// get items
						$data['items']													=	$this->Item->search_desactive($item_id);
					}
					else
					{
						// get items
						//unset($_SESSION['undel']);
						$data['items']													=	$this->Item->get_all($config['per_page'], $this->uri->segment($config['uri_segment']));
					}
				break;

				default:
						//Si il n'y a pas de filtre alors il ne faut rien faire
						//comme avant avec la suppression de la recherche
						unset($_SESSION['filtre_recherche']);
						// get items
						$data['items']													=	$this->Item->get_all($config['per_page'], $this->uri->segment($config['uri_segment']));

				break;
			}
		}

		// set up the pagination - phase 2
		$config['total_rows'] 											= 	$this->Item->count_all();
		$this															->	pagination->initialize($config);
		$data['links']													=	$this->pagination->create_links();

		// Set route
		$this															->	session->set_userdata('route', 'IZ');

		switch($_SESSION['global'] ?? 0)
		{
			case 1:
				//cas 1:
				unset($_SESSION['global']);
			break;
		}

        $_SESSION['data_items'] = $data['items']->result();

		// show data
		$this															->	load->view('items/manage', $data);
	}

	// Server-side sort: set sort column and direction, then reload list
	function sort($col = 'name', $dir = 'asc')
	{
		// whitelist of allowed sort columns
		$allowed = array(
			'item_number'      => 'items.item_number',
			'name'             => 'items.name',
			'category'         => 'category',
			'volume'           => 'items.volume',
			'nicotine'         => 'items.nicotine',
			'cost_price'       => 'items_suppliers.supplier_cost_price',
			'margin'           => 'margin',
			'quantity'         => 'items.quantity',
			'quantity_central' => 'items.quantity_central',
			'sales_qty'        => 'items.sales_qty',
			'sales_ht'         => 'items.sales_ht',
		);

		$dir = (strtolower($dir) === 'desc') ? 'desc' : 'asc';

		if (isset($allowed[$col])) {
			$_SESSION['items_sort_col'] = $col;
			$_SESSION['items_sort_dir'] = $dir;
		}

		redirect('items');
	}

	// Set number of items displayed per page
	function per_page($n = 20)
	{
		$allowed = array(20, 50, 100, 500, 0);
		$n = (int)$n;
		if (in_array($n, $allowed)) {
			$_SESSION['items_per_page'] = $n;
		}
		redirect('items');
	}

	function filter_category($id = 0)
	{
		$id = (int)$id;
		if ($id > 0) {
			$_SESSION['filter_category_id'] = $id;
		} else {
			unset($_SESSION['filter_category_id']);
		}
		redirect('items');
	}

	function filter_supplier($id = 0)
	{
		$id = (int)$id;
		if ($id > 0) {
			$_SESSION['filter_supplier_id'] = $id;
		} else {
			unset($_SESSION['filter_supplier_id']);
		}
		redirect('items');
	}

    //Fonction pour bloquer la recherche
	function filtre()
	{
		//si le filtre est inactif, alors il s'active et vis verca
		switch($_SESSION['filtre'])
		{
			case 1:
				$_SESSION['filtre']=0;
				// Nettoyer les filtres quand on désactive le verrou
				unset($_SESSION['filtre_recherche']);
				unset($_SESSION['filtre_nouveautes']);
			break;

			default:
				$_SESSION['filtre']=1;
			break;
		}
		redirect("items");
	}
	function items_list_new()
	{
		// Si le verrou est actif, conserver le filtre nouveautés
		if (($_SESSION['filtre'] ?? 0) == 1) {
			$_SESSION['filtre_nouveautes'] = 1;
			unset($_SESSION['filtre_recherche']);
		} else {
			unset($_SESSION['filtre_recherche']);
			unset($_SESSION['filtre_nouveautes']);
		}

		// get items
		$data['title']										=	'';
		$_SESSION['controller_name']=$_SESSION['controller_name']. '_news';

		$data['items']													=	$this->Item->get_new_after_date(15);
		//$_SESSION['undel']												= 2;
		$_SESSION['data_items'] = $data['items']->result();

		$this															->	load->view('items/manage', $data);
	}

	// Toggle pour activer/désactiver le filtre nouveautés
	function toggle_nouveautes()
	{
		if (($_SESSION['filtre_nouveautes'] ?? 0) == 1) {
			// Désactiver le filtre nouveautés
			unset($_SESSION['filtre_nouveautes']);
		} else {
			// Activer le filtre nouveautés
			$_SESSION['filtre_nouveautes'] = 1;
			unset($_SESSION['filtre_recherche']);
			// Désactiver le mode inactifs (mutuellement exclusif)
			unset($_SESSION['undel']);
			unset($_SESSION['reactivation']);
		}
		redirect("items");
	}

	function items_avanced_search()
	{
		//si la recherche avancée est inactive, alors il s'active et vis verca
		switch($_SESSION['items_avanced_search'])
		{
			case 1:
				$_SESSION['items_avanced_search'] = 0;    //recherche non acancée
			//	$_SESSION['filtre'] = 1;
			break;

			default:
				$_SESSION['items_avanced_search'] = 1;    //recherche anvancée
			//	$_SESSION['filtre'] = 0;
			break;
		}
		redirect("items");
	}

	function search()
	{
		// set module id (same as index)
		$_SESSION['module_id']											=	"15";
		$_SESSION['controller_name']									=	strtolower(get_class($this)) . '_actifs';

		// initialise
		$data															=	array();
		$item_id														=	$this->input->post("search"); // supplied by javascript in manage.php
		$_SESSION['recherche']=1;
		$_SESSION['filtre_recherche']=$item_id;

		// Clear category and supplier filters for search (search should find all matching items)
		unset($_SESSION['filter_category_id']);
		unset($_SESSION['filter_supplier_id']);
		// Effacer le filtre nouveautés quand on fait une nouvelle recherche
		unset($_SESSION['filtre_nouveautes']);

		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']										=	$this->lang->line('common_undelete');
					$_SESSION['controller_name']						=	strtolower(get_class($this)) . '_desactives';
			break;
			default:
					$data['title']										=	'';
			break;
		}

		// set up the pagination - phase 1
		$config															=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 											= 	site_url('/items/index');

		switch($_SESSION['items_avanced_search'] ?? 0)
		{
			case 0:
				if(is_numeric($item_id))
				{
					$data_item = $this->Item->get_info($item_id);
					$item_id = $data_item->name;
				}
			    // get items
			    $data['items']													=	$this->Item->search_desactive($item_id);
			break;

			case 1:
        		// get items
		        $data['items']													=	$this->Item->search_desactive($item_id);
			break;

			default:
			if(is_numeric($item_id))
			{
				$data_item = $this->Item->get_info($item_id);
				$item_id = $data_item->name;
			}
			    // get items
			    $data['items']													=	$this->Item->search_desactive($item_id);
			break;
		}

		// get items
		//$data['items']													=	$this->Item->search($item_id);

		// set up the pagination - phase 2
		$config['total_rows'] 											= 	$data['items']->num_rows();
		$this															->	pagination->initialize($config);
		$data['links']													=	$this->pagination->create_links();

		// Set route
		$this															->	session->set_userdata('route', 'IZ');
		$_SESSION['data_items'] = $data['items']->result();
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
	

	function vs_quantity($item_id)
	{
		$data_GetQuantite = array();
		$data_GetQuantite['ID'] = intval($item_id);
		$data_GetQuantite['Emplacement'] = 'Machine';

        //chargement de la class vapeself 
		$this->load->library("../controllers/vapeself");

		//demande de token
		$token = $this->vapeself->get_token();
		
		//récupération de la quantité en stock dans le distributeur
		$return_MajProduit = $this->vapeself->get_GetQuantite($token, $data_GetQuantite);
		$_SESSION['vs_quantity'][$item_id] = $return_MajProduit;
	}

	// this function is called when updating an item, ie when clicking on the item number or for new items
	function view($item_id=-1, $origin='0')
	{
		if($this->config->item("distributeur_vapeself") == 'Y')
		{
			$this->vs_quantity($item_id);
			$data_item = $this->Item->get_info($item_id);
			$_SESSION['emplacement'][$item_id] = $data_item->emplacement;
		}


		switch($_SESSION['redirection'])
		{
			case 'receivings':

			break;
			default:
			    $_SESSION['redirection']=$_SESSION['controller_name'];
			break;
		}
		//$_SESSION['redirection']=$_SESSION['controller_name'];
		// intialise
		$_SESSION['transaction_info']			=	new stdClass();
		$_SESSION['transaction_tax_info']		=	new stdClass();
		$_SESSION['transaction_warehouse_info']	=	new stdClass();
		$_SESSION['transaction_supplier_info']	=	new stdClass();

		// set origin
		switch ($origin)
		{
			case	'0':
			//		unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			case 'SS':
			    $_SESSION['origin'] = 'SS';
			break;

			case 'IR':
			    $_SESSION['origin'] = 'IR';
			break;

			case 'RR':
			    $_SESSION['origin'] = 'RR';
			break;
			
			case 'IA':
				$_SESSION['origin'] = 'IA';
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
					
					$_SESSION['offer_value'] = $_SESSION['transaction_info']->offer_value;
                    
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
		}/*
		switch($_SESSION['global'])
		{
			case 1:
			    //cas 1: 
			break;
		}//*/
		switch($_SESSION['global'])
		{
			case 1:
				$_SESSION['autofocus_avec_item_id_tabular'] = $item_id;
				unset($_SESSION['global']);
			break;
		
			default:
				$_SESSION['autofocus_avec_item_id_manage'] = $item_id;
				unset($_SESSION['global']);
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
			//		unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
			//		$_SESSION['origin']		=	$origin;
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
////Récupération de la valeur
//$redirect = 'items';
//$conn_parms														=	array();
//
//initialisation des parametres pour la connexion
//$conn_parms														=	$this->Common_routines->get_conn_parms($redirect);
//$conn															=	$this->Common_routines->open_db($conn_parms);
//
//Requête SQL
//$sql =	"DELETE FROM `ospos_items_suppliers` WHERE `supplier_reorder_policy` = '".N ."'";						
//$result	=	$conn->query($sql);
//
//
//$sql =	"INSERT *FROM `ospos_items_suppliers` WHERE `supplier_reorder_policy` = '".Y ."'";						
//$result	=	$conn->query($sql);
//$row = mysqli_fetch_assoc($result);
//		// show data entry
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
			//		unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
			//		$_SESSION['origin']		=	$origin;
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
			//		unset($_SESSION['origin']);
					unset($_SESSION['sel_item_id']);
			break;

			default:
			//		$_SESSION['origin']									=	$origin;
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

	//
	// AJAX Tab Methods - Load tab content only (body content for single modal)
	//

	/**
	 * AJAX: Load Article tab content (body-only)
	 * Returns only the tab content partial, not the full modal
	 */
	/**
	 * AJAX: Toggle item active/inactive status
	 * Returns JSON with new state
	 */
	function ajax_toggle_active($item_id)
	{
		$data_items = $this->Item->get_info($item_id);
		$quantity = $data_items->quantity;
		$is_deleted = ($data_items->deleted == '1');

		if ($is_deleted) {
			// Réactiver
			$this->db->where('item_id', $item_id);
			$this->db->update('items', array('deleted' => 0));

			$inv_data = array(
				'trans_date'       => date('Y-m-d H:i:s'),
				'trans_items'      => $item_id,
				'trans_user'       => $this->Employee->get_logged_in_employee_info()->person_id,
				'trans_comment'    => $this->lang->line('items_undeleted'),
				'trans_stock_before' => $quantity,
				'trans_inventory'  => 0,
				'trans_stock_after' => $quantity,
				'branch_code'      => $this->config->item('branch_code')
			);
			$this->Inventory->insert($inv_data);

			echo json_encode(array('success' => true, 'deleted' => 0));
		} else {
			// Désactiver — vérifier stock
			if ($quantity > 0) {
				echo json_encode(array('success' => false, 'error' => 'Stock non vide (' . $quantity . ')'));
				return;
			}

			$this->db->where('item_id', $item_id);
			$this->db->update('items', array('deleted' => 1));

			$inv_data = array(
				'trans_date'       => date('Y-m-d H:i:s'),
				'trans_items'      => $item_id,
				'trans_user'       => $this->Employee->get_logged_in_employee_info()->person_id,
				'trans_comment'    => $this->lang->line('items_deleted'),
				'trans_stock_before' => $quantity,
				'trans_inventory'  => 0,
				'trans_stock_after' => $quantity,
				'branch_code'      => $this->config->item('branch_code')
			);
			$this->Inventory->insert($inv_data);

			echo json_encode(array('success' => true, 'deleted' => 1));
		}
	}

	function ajax_tab_article()
	{
		$_SESSION['show_dialog'] = 1;
		$this->load->view('items/partials/tab_content_article');
	}

	/**
	 * AJAX: Load Suppliers tab content (body-only)
	 * Prepares supplier data and returns the tab content partial
	 */
	function ajax_tab_suppliers()
	{
		// Initialize
		$_SESSION['transaction_supplier_info'] = new stdClass();
		$_SESSION['transaction_add_supplier_info'] = new stdClass();
		$_SESSION['show_dialog'] = 9;

		// Load suppliers pick list
		$supplier_pick_list = array();
		$suppliers = $this->Supplier->get_all()->result_array();
		foreach ($suppliers as $row) {
			$supplier_pick_list[$row['person_id']] = strtoupper($row['company_name']);
		}
		$_SESSION['supplier_pick_list'] = $supplier_pick_list;

		// Get current data
		$_SESSION['transaction_supplier_info'] = $this->Item->get_info_suppliers();

		// Get supplier names
		foreach ($_SESSION['transaction_supplier_info'] as $key => $row) {
			$supplier_info = $this->Supplier->get_info($row->supplier_id);
			$_SESSION['transaction_supplier_info'][$key]->supplier_name = $supplier_info->company_name;
		}

		// Set output variables
		$_SESSION['transaction_add_supplier_info']->supplier_reorder_policy = 'Y';
		$_SESSION['transaction_add_supplier_info']->supplier_id = $this->config->item('default_supplier_id');
		$_SESSION['transaction_add_supplier_info']->supplier_preferred = 'N';
		$_SESSION['transaction_add_supplier_info']->item_id = $_SESSION['transaction_info']->item_id;

		// Check preferred supplier flag
		$count = $this->Item->item_supplier_preferred_y();
		if ($count == 0) {
			$_SESSION['error_code'] = '01830';
		}

		// Return only the tab content partial
		$this->load->view('items/partials/tab_content_suppliers');
	}

	/**
	 * AJAX: Load Pricelists tab content (body-only)
	 * Prepares pricelist data and returns the tab content partial
	 */
	function ajax_tab_pricelists()
	{
		// Initialize
		$_SESSION['transaction_pricelist_info'] = new stdClass();
		$_SESSION['transaction_add_pricelist_info'] = new stdClass();
		$_SESSION['show_dialog'] = 11;

		// Get current data
		$_SESSION['transaction_pricelist_info'] = $this->Item->get_info_pricelists();

		// Get pricelist names
		foreach ($_SESSION['transaction_pricelist_info'] as $key => $row) {
			$pricelist_info = $this->Pricelist->get_info($row->pricelist_id);
			$_SESSION['transaction_pricelist_info'][$key]->pricelist_name = $pricelist_info->pricelist_name;
			$_SESSION['transaction_pricelist_info'][$key]->pricelist_description = $pricelist_info->pricelist_description;
		}

		// Set output variables
		$_SESSION['transaction_add_pricelist_info']->valid_from_day = '00';
		$_SESSION['transaction_add_pricelist_info']->valid_from_month = '00';
		$_SESSION['transaction_add_pricelist_info']->valid_from_year = '0000';
		$_SESSION['transaction_add_pricelist_info']->valid_to_day = '00';
		$_SESSION['transaction_add_pricelist_info']->valid_to_month = '00';
		$_SESSION['transaction_add_pricelist_info']->valid_to_year = '0000';

		// Return only the tab content partial
		$this->load->view('items/partials/tab_content_pricelists');
	}

	/**
	 * AJAX: Load Stock tab content (body-only)
	 * Prepares inventory data and returns the tab content partial
	 */
	function ajax_tab_stock()
	{
		$item_id = $_SESSION['transaction_info']->item_id;
		$_SESSION['show_dialog'] = 3;

		// Refresh item info for current quantity
		$_SESSION['transaction_info'] = $this->Item->get_info($item_id);

		// Load inventory history
		$_SESSION['inventory_info'] = $this->Inventory->get_inventory_data_for_item($item_id)->result_array();

		// Return only the tab content partial
		$this->load->view('items/partials/tab_content_stock');
	}

	/**
	 * AJAX: Load DLUO tab content (body-only)
	 * Prepares DLUO data and returns the tab content partial
	 */
	function ajax_tab_dluo()
	{
		$item_id = $_SESSION['transaction_info']->item_id;
		$_SESSION['show_dialog'] = 6;

		// Refresh item info
		$_SESSION['transaction_info'] = $this->Item->get_info($item_id);

		// Get DLUO records
		$_SESSION['item_info_dluo'] = $this->Item->get_info_dluo($item_id)->result_array();

		// Calculate total DLUO qty and clean up zero entries
		$_SESSION['dluo_total_qty'] = 0;
		foreach ($_SESSION['item_info_dluo'] as $row) {
			$_SESSION['dluo_total_qty'] += $row['dluo_qty'];
			if ($row['dluo_qty'] == 0) {
				$this->Item->dluo_delete($row['year'], $row['month']);
			}
		}

		// Refresh after cleanup
		$_SESSION['item_info_dluo'] = $this->Item->get_info_dluo($item_id)->result_array();

		// Check total dluo against stock qty
		if ($_SESSION['dluo_total_qty'] != $_SESSION['transaction_info']->quantity) {
			$_SESSION['error_code'] = '01490';
		}

		// Return only the tab content partial
		$this->load->view('items/partials/tab_content_dluo');
	}

	/**
	 * AJAX: Load Kit tab content (body-only)
	 * Prepares kit structure data and returns the tab content partial
	 */
	function ajax_tab_kit()
	{
		$item_id = $_SESSION['transaction_info']->item_id;
		$_SESSION['show_dialog'] = 15;

		// Initialize kit info
		unset($_SESSION['kit_info']);
		$_SESSION['kit_info'] = new stdClass();
		$_SESSION['kit_info']->option_type_pick_list = array(
			'F' => $this->lang->line('items_kit_option_type_F'),
			'O' => $this->lang->line('items_kit_option_type_O')
		);
		$_SESSION['kit_info']->kit_option_type = 'F';

		// Top level item info
		$_SESSION['kit_info']->item_info = $this->Item->get_info($item_id);

		// Get kit structure
		$_SESSION['kit_info']->kit_structure = $this->Item->get_kit_structure($_SESSION['kit_info']->item_info->kit_reference)->result_array();

		// Return only the tab content partial
		$this->load->view('items/partials/tab_content_kit');
	}

	/**
	 * AJAX: Load Sales tab content (body-only)
	 * Prepares sales statistics and chart data
	 */
	function ajax_tab_sales()
	{
		$item_id = $_SESSION['transaction_info']->item_id;
		$_SESSION['show_dialog'] = 19;

		// Refresh item info
		$_SESSION['transaction_info'] = $this->Item->get_info($item_id);

		// Get sales data for the last 3 months
		$_SESSION['sales_stats'] = $this->Item->get_sales_stats($item_id, 3);

		// Return only the tab content partial
		$this->load->view('items/partials/tab_content_sales');
	}

	/**
	 * View sales tab (non-AJAX fallback)
	 */
	function view_sales($item_id)
	{
		$_SESSION['show_dialog'] = 19;
		$_SESSION['transaction_info'] = $this->Item->get_info($item_id);
		$_SESSION['sales_stats'] = $this->Item->get_sales_stats($item_id, 3);
		$this->load->view('items/modal_wrapper');
	}

	// ---- Fuzzy Search & Merge with disabled article ----

	function ajax_fuzzy_search_merge()
	{
		$name = isset($_SESSION['transaction_info']->name) ? $_SESSION['transaction_info']->name : '';
		$item_id = isset($_SESSION['transaction_info']->item_id) ? $_SESSION['transaction_info']->item_id : 0;
		$item_number = isset($_SESSION['transaction_info']->item_number) ? $_SESSION['transaction_info']->item_number : '';

		// Check if filtering active items only (default: false = show deleted items)
		$active_only = isset($_GET['active_only']) && $_GET['active_only'] == '1';

		if (empty($name))
		{
			echo json_encode(array('success' => false, 'error' => 'Nom article vide'));
			return;
		}

		// SQL pre-filter
		$sql_results = $this->Item->fuzzy_search_deleted($name, 50, $active_only);

		if (empty($sql_results))
		{
			echo json_encode(array(
				'success' => true,
				'current_item' => array('item_id' => $item_id, 'item_number' => $item_number, 'name' => $name),
				'results' => array(),
				'active_only' => $active_only
			));
			return;
		}

		// PHP scoring
		$search_lower = strtolower($name);
		$search_words = array();
		$raw = preg_split('/[\s\-\_\/\.\,\(\)]+/', $search_lower);
		for ($i = 0; $i < count($raw); $i++)
		{
			$w = trim($raw[$i]);
			if (strlen($w) >= 2) { $search_words[] = $w; }
		}
		$n_search_words = count($search_words);

		// Pre-compute search word soundex
		$search_soundex = array();
		for ($i = 0; $i < $n_search_words; $i++)
		{
			$search_soundex[] = soundex($search_words[$i]);
		}

		$scored = array();
		for ($r = 0; $r < count($sql_results); $r++)
		{
			$row = $sql_results[$r];
			$candidate = strtolower($row['name']);

			// 1. similar_text percentage (25%)
			similar_text($search_lower, $candidate, $sim_pct);
			$score_similar = $sim_pct;

			// 2. Exact words in common (25%)
			$cand_words = array();
			$craw = preg_split('/[\s\-\_\/\.\,\(\)]+/', $candidate);
			for ($j = 0; $j < count($craw); $j++)
			{
				$cw = trim($craw[$j]);
				if (strlen($cw) >= 2) { $cand_words[] = $cw; }
			}
			$common = 0;
			for ($j = 0; $j < $n_search_words; $j++)
			{
				for ($k = 0; $k < count($cand_words); $k++)
				{
					if ($search_words[$j] === $cand_words[$k])
					{
						$common++;
						break;
					}
				}
			}
			$score_words = ($n_search_words > 0) ? ($common / $n_search_words) * 100 : 0;

			// 3. Levenshtein distance (35%)
			$s1 = substr($search_lower, 0, 255);
			$s2 = substr($candidate, 0, 255);
			$max_len = max(strlen($s1), strlen($s2));
			if ($max_len > 0)
			{
				$score_lev = (1 - (levenshtein($s1, $s2) / $max_len)) * 100;
			}
			else
			{
				$score_lev = 0;
			}
			if ($score_lev < 0) { $score_lev = 0; }

			// 4. Phonetic (15%)
			$n_cand_words = count($cand_words);
			$phonetic_match = 0;
			for ($j = 0; $j < $n_search_words; $j++)
			{
				for ($k = 0; $k < $n_cand_words; $k++)
				{
					if ($search_soundex[$j] === soundex($cand_words[$k]))
					{
						$phonetic_match++;
						break;
					}
				}
			}
			$score_phonetic = ($n_search_words > 0) ? ($phonetic_match / $n_search_words) * 100 : 0;

			// Composite score
			$composite = ($score_similar * 0.25) + ($score_words * 0.25) + ($score_lev * 0.35) + ($score_phonetic * 0.15);
			$row['composite_score'] = round($composite, 1);
			$row['sql_score'] = (int)$row['fuzzy_score'];
			unset($row['fuzzy_score']);
			$scored[] = $row;
		}

		// Sort by composite score desc
		for ($i = 0; $i < count($scored) - 1; $i++)
		{
			for ($j = $i + 1; $j < count($scored); $j++)
			{
				if ($scored[$j]['composite_score'] > $scored[$i]['composite_score'])
				{
					$tmp = $scored[$i];
					$scored[$i] = $scored[$j];
					$scored[$j] = $tmp;
				}
			}
		}

		// Top 20
		$top = array();
		$max = min(20, count($scored));
		for ($i = 0; $i < $max; $i++)
		{
			$top[] = $scored[$i];
		}

		echo json_encode(array(
			'success' => true,
			'current_item' => array('item_id' => $item_id, 'item_number' => $item_number, 'name' => $name),
			'results' => $top,
			'active_only' => $active_only
		));
	}

	function ajax_fuzzy_merge_confirm($to_item_id)
	{
		$to_item_id = (int)$to_item_id;
		$from_item_id = isset($_SESSION['transaction_info']->item_id) ? (int)$_SESSION['transaction_info']->item_id : 0;

		if ($from_item_id <= 0 || $to_item_id <= 0)
		{
			echo json_encode(array('success' => false, 'error' => 'IDs invalides'));
			return;
		}

		// Fetch FROM (current item)
		$from_info = $this->Item->get_info($from_item_id);
		if (empty($from_info->item_id))
		{
			echo json_encode(array('success' => false, 'error' => 'Article courant introuvable'));
			return;
		}

		// Fetch TO item (peut être actif ou inactif)
		$to_info = $this->Item->get_info($to_item_id);
		if (empty($to_info->item_id))
		{
			echo json_encode(array('success' => false, 'error' => 'Article cible introuvable'));
			return;
		}

		// Store in session for execute step
		$_SESSION['fuzzy_merge_from'] = $from_info;
		$_SESSION['fuzzy_merge_to'] = $to_info;

		echo json_encode(array(
			'success' => true,
			'from' => array(
				'item_id' => $from_info->item_id,
				'item_number' => $from_info->item_number,
				'name' => $from_info->name,
				'category' => $from_info->category,
				'quantity' => $from_info->quantity,
				'sales_ht' => $from_info->sales_ht,
				'sales_qty' => $from_info->sales_qty,
				'dluo_indicator' => $from_info->dluo_indicator,
				'reorder_pack_size' => $from_info->reorder_pack_size
			),
			'to' => array(
				'item_id' => $to_info->item_id,
				'item_number' => $to_info->item_number,
				'name' => $to_info->name,
				'category' => $to_info->category,
				'quantity' => $to_info->quantity,
				'sales_ht' => $to_info->sales_ht,
				'sales_qty' => $to_info->sales_qty,
				'dluo_indicator' => $to_info->dluo_indicator,
				'reorder_pack_size' => $to_info->reorder_pack_size
			)
		));
	}

	function ajax_fuzzy_merge_execute()
	{
		header('Content-Type: application/json');

		try {
			if ($_SERVER['REQUEST_METHOD'] !== 'POST')
			{
				echo json_encode(array('success' => false, 'error' => 'POST requis'));
				return;
			}

			if (!isset($_SESSION['fuzzy_merge_from']) || !isset($_SESSION['fuzzy_merge_to']))
			{
				echo json_encode(array('success' => false, 'error' => 'Session de fusion expirée'));
				return;
			}

			$from_session = $_SESSION['fuzzy_merge_from'];
			$to_session = $_SESSION['fuzzy_merge_to'];

			// Vérifier que les IDs existent dans la session
			if (!isset($from_session->item_id) || !isset($to_session->item_id))
			{
				echo json_encode(array('success' => false, 'error' => 'IDs articles manquants dans la session'));
				return;
			}

			// Vérifier que l'article source existe toujours
			$from = $this->Item->get_info($from_session->item_id);
			if (empty($from) || !isset($from->item_id))
			{
				echo json_encode(array('success' => false, 'error' => "L'article source n'existe plus"));
				return;
			}

			// Vérifier que l'article cible existe toujours
			$to = $this->Item->get_info($to_session->item_id);
			if (empty($to) || !isset($to->item_id))
			{
				echo json_encode(array('success' => false, 'error' => "L'article cible n'existe plus"));
				return;
			}

			$employee_info = $this->Employee->get_logged_in_employee_info();
			$employee_id = ($employee_info && isset($employee_info->person_id)) ? $employee_info->person_id : 0;

		// 1. Undelete TO seulement s'il est désactivé
		if (isset($to->deleted) && $to->deleted == 1)
		{
			$_SESSION['transaction_info'] = new stdClass();
			$_SESSION['transaction_info']->item_id = $to->item_id;
			$this->Item->undelete();

			// Inventory record: reactivation
			$inv_data = array(
				'trans_date'        => date('Y-m-d H:i:s'),
				'trans_items'       => $to->item_id,
				'trans_user'        => $employee_id,
				'trans_comment'     => ($to->item_number ?? '') . ' ' . $this->lang->line('items_undeleted') . ' (fusion)',
				'trans_stock_before' => ($to->quantity ?? 0),
				'trans_inventory'   => 0,
				'trans_stock_after' => ($to->quantity ?? 0),
				'branch_code'       => $this->config->item('branch_code')
			);
			$this->Inventory->insert($inv_data);
		}

		// 2. Update TO with merged data
		$new_qty = (float)($from->quantity ?? 0) + (float)($to->quantity ?? 0);
		$new_sales_ht = (float)($from->sales_ht ?? 0) + (float)($to->sales_ht ?? 0);
		$new_sales_qty = (float)($from->sales_qty ?? 0) + (float)($to->sales_qty ?? 0);
		$new_reorder = max((float)($from->reorder_pack_size ?? 0), (float)($to->reorder_pack_size ?? 0));

		$_SESSION['new'] = 0;
		$_SESSION['transaction_info'] = new stdClass();
		$_SESSION['transaction_info']->item_id = $to->item_id;
		$_SESSION['transaction_info']->quantity = $new_qty;
		$_SESSION['transaction_info']->reorder_pack_size = $new_reorder;
		$_SESSION['transaction_info']->sales_ht = $new_sales_ht;
		$_SESSION['transaction_info']->sales_qty = $new_sales_qty;
		$this->Item->save();

		// 3. Zero out FROM and delete
		$_SESSION['new'] = 0;
		$_SESSION['transaction_info'] = new stdClass();
		$_SESSION['transaction_info']->item_id = $from->item_id;
		$_SESSION['transaction_info']->quantity = 0;
		$_SESSION['transaction_info']->sales_ht = 0;
		$_SESSION['transaction_info']->sales_qty = 0;
		$_SESSION['transaction_info']->deleted = 1;
		$this->Item->save();

		// 4. DLUO transfer if both have dluo_indicator = Y
		if (($from->dluo_indicator ?? 'N') == 'Y' && ($to->dluo_indicator ?? 'N') == 'Y')
		{
			$merge_from_dluo = $this->Item->get_info_dluo($from->item_id)->result_array();
			$_SESSION['transaction_info'] = new stdClass();
			$_SESSION['transaction_info']->item_id = $from->item_id;
			for ($d = 0; $d < count($merge_from_dluo); $d++)
			{
				$update_data = array('item_id' => $to->item_id);
				$this->Item->dluo_edit($merge_from_dluo[$d]['year'], $merge_from_dluo[$d]['month'], $update_data);
			}
		}

		// 5. Inventory records (same pattern as merge_do)
		$from_qty = (float)($from->quantity ?? 0);
		$to_qty = (float)($to->quantity ?? 0);
		$from_item_number = $from->item_number ?? '';
		$to_item_number = $to->item_number ?? '';

		// FROM item - merged away
		$inv_data = array(
			'trans_date'        => date('Y-m-d H:i:s'),
			'trans_items'       => $from->item_id,
			'trans_user'        => $employee_id,
			'trans_comment'     => $from_item_number . ' => ' . $this->lang->line('items_merge_with') . ' => ' . $to_item_number,
			'trans_stock_before' => $from_qty,
			'trans_inventory'   => $from_qty * -1,
			'trans_stock_after' => 0,
			'branch_code'       => $this->config->item('branch_code')
		);
		$this->Inventory->insert($inv_data);
		sleep(2);

		// FROM item - deleted record
		$inv_data = array(
			'trans_date'        => date('Y-m-d H:i:s'),
			'trans_items'       => $from->item_id,
			'trans_user'        => $employee_id,
			'trans_comment'     => $this->lang->line('items_merge_deleted') . ' => ' . $to_item_number,
			'trans_stock_before' => 0,
			'trans_inventory'   => 0,
			'trans_stock_after' => 0,
			'branch_code'       => $this->config->item('branch_code')
		);
		$this->Inventory->insert($inv_data);

		// TO item - received merged stock
		$inv_data = array(
			'trans_date'        => date('Y-m-d H:i:s'),
			'trans_items'       => $to->item_id,
			'trans_user'        => $employee_id,
			'trans_comment'     => $to_item_number . ' => ' . $this->lang->line('items_merge_from') . ' => ' . $from_item_number,
			'trans_stock_before' => $to_qty,
			'trans_inventory'   => $from_qty,
			'trans_stock_after' => $to_qty + $from_qty,
			'branch_code'       => $this->config->item('branch_code')
		);
		$this->Inventory->insert($inv_data);

		// 6. Transfer sales_items and sales_taxes
		$sales_items = $this->Sale->get_sales_items_by_item_id($from->item_id)->result_array();
		for ($s = 0; $s < count($sales_items); $s++)
		{
			$update_data = array(
				'item_id'     => $to->item_id,
				'description' => $this->lang->line('items_merge_sales_desc') . $to->item_id
			);
			$this->Sale->update_line($update_data, $sales_items[$s]['sale_id'], $from->item_id, $sales_items[$s]['line']);

			$update_data = array('item_id' => $to->item_id);
			$this->Sale->update_sales_taxes_by_item_id($update_data, $sales_items[$s]['sale_id'], $from->item_id, $sales_items[$s]['line']);
		}

		// 7. Transfer suppliers from FROM to TO (if not already present)
		// Get suppliers from both items
		$from_suppliers = $this->Item->get_suppliers_by_item_id($from->item_id);
		$to_suppliers = $this->Item->get_suppliers_by_item_id($to->item_id);

		// Build array of supplier IDs already on TO item
		$to_supplier_ids = array();
		foreach ($to_suppliers as $sup) {
			$to_supplier_ids[] = $sup->supplier_id;
		}

		// Transfer any supplier from FROM that isn't already on TO
		foreach ($from_suppliers as $from_sup) {
			if (!in_array($from_sup->supplier_id, $to_supplier_ids)) {
				// Transfer this supplier to TO item with preferred = 'N'
				$this->Item->transfer_supplier_to_item($from->item_id, $to->item_id, $from_sup->supplier_id);
			}
		}

		// 8. Fix supplier reorder values: if policy=Y and values=0, set to 1
		$this->db->from('items_suppliers');
		$this->db->where('item_id', $to->item_id);
		$this->db->where('supplier_reorder_policy', 'Y');
		$this->db->where('supplier_reorder_pack_size', 0);
		$this->db->where('supplier_min_order_qty', 0);
		$this->db->where('supplier_min_stock_qty', 0);
		$this->db->where('branch_code', $this->config->item('branch_code'));
		$suppliers_to_fix = $this->db->get()->result();

		foreach ($suppliers_to_fix as $sup) {
			$this->db->where('item_id', $to->item_id);
			$this->db->where('supplier_id', $sup->supplier_id);
			$this->db->where('branch_code', $this->config->item('branch_code'));
			$this->db->update('items_suppliers', array(
				'supplier_reorder_pack_size' => 1,
				'supplier_min_order_qty'     => 1,
				'supplier_min_stock_qty'     => 1
			));
		}

		// Cleanup session
		unset($_SESSION['fuzzy_merge_from']);
		unset($_SESSION['fuzzy_merge_to']);

		echo json_encode(array(
			'success' => true,
			'message' => $this->lang->line('items_merge_successfull') . ' => ' . $from_item_number . ' => ' . $to_item_number,
			'redirect' => site_url('items/view/' . $to->item_id)
		));

		} catch (Exception $e) {
			echo json_encode(array('success' => false, 'error' => 'Erreur: ' . $e->getMessage()));
		}
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

		// re-route - use AJAX tab reload to stay on pricelist tab
		$this->ajax_tab_pricelists();
	}

	// add pricelist
	function item_pricelist_add()
	{
		// store user entries
		$_SESSION['transaction_add_pricelist_info']->item_id			=	$_SESSION['transaction_info']->item_id;
		$_SESSION['transaction_add_pricelist_info']->pricelist_id		=	$this->input->post('pricelist_id');
		$_SESSION['transaction_add_pricelist_info']->unit_price_with_tax=	$this->input->post('unit_price_with_tax');
		$_SESSION['transaction_add_pricelist_info']->unit_price_with_tax = str_replace($_SESSION['G']->number['virgule'], $_SESSION['G']->number['point'], $_SESSION['transaction_add_pricelist_info']->unit_price_with_tax);

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

		// Update session unit_price from default pricelist for Article tab display
		$default_price_list_info = $this->Item->get_default_pricelist()->result_object();
		if (count($default_price_list_info) == 1)
		{
			foreach ($default_price_list_info as $row)
			{
				$_SESSION['transaction_info']->unit_price = $row->unit_price;
			}
		}

		// set up success message
		$_SESSION['error_code']	=	'05190';

		// re-route - use AJAX tab reload to stay on pricelist tab
		$this->ajax_tab_pricelists();
	}
	// update pricelist
	function item_pricelist_update()
	{
		$_SESSION['transaction_add_pricelist_info']= array();
		foreach($_SESSION['pricelists_id'] as $key=>$pricelist)
		{

			// store user entries
			$_SESSION['transaction_add_pricelist_info'][$pricelist] 						= 	array();
			$_SESSION['transaction_add_pricelist_info'][$pricelist]['unit_price_with_tax']	=	$this->input->post('unit_price_with_tax_'.$pricelist);
			$_SESSION['transaction_add_pricelist_info'][$pricelist]['unit_price_with_tax'] 	= 	str_replace($_SESSION['G']->number['virgule'], $_SESSION['G']->number['point'], $_SESSION['transaction_add_pricelist_info'][$pricelist]['unit_price_with_tax']);

			if($pricelist != 1)
			{
				// explode and load valid_from
				$pieces_from 																		=	explode("-", $this->input->post('valid_from_'.$pricelist));
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_from_day']		=	$pieces_from[2];
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_from_month']		=	$pieces_from[1];
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_from_year']		=	$pieces_from[0];

				// explode and load valid_to
				$pieces_to 																		=	explode("-", $this->input->post('valid_to_'.$pricelist));
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_day']			=	$pieces_to[2];
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_month']		=	$pieces_to[1];
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_year']		=	$pieces_to[0];
			}
			if($this->input->post('valid_from_'.$pricelist) == NULL && $pricelist != 1)
			{
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_from_day']		=	0;
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_from_month']		=	0;
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_from_year']		=	0;
			}
			if($this->input->post('valid_to_'.$pricelist) == NULL && $pricelist != 1)
			{
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_day']			=	0;
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_month']		=	0;
				$_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_year']		=	0;
			}

			// verify data entry

			// verify required fields are entered
			if(empty($_SESSION['transaction_add_pricelist_info'][$pricelist]['unit_price_with_tax']))
			{
				// set message
				$_SESSION['error_code'] = '00030';
				redirect("items");
			}

			// check unit_price is numeric
			if (!is_numeric($_SESSION['transaction_add_pricelist_info'][$pricelist]['unit_price_with_tax']))
			{
				// set message
				$_SESSION['error_code'] = '01420';
				redirect("items");
			}
			
			// if valid_from date is entered it must be a date if entered
			if($this->input->post('valid_from_'.$pricelist) != NULL)
			{
				if(!checkdate($_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_month'], $_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_day'], $_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_year']))
				{
					// set message
					$_SESSION['error_code'] = '05160';
					redirect($_SESSION['G']->modules[$_SESSION['module_id']]['module_name']);
				}
			}

			// valid_to date checks
			if($this->input->post('valid_from_'.$pricelist) != NULL)
			{
				// is it a real date?
				if(!checkdate($_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_month'], $_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_day'], $_SESSION['transaction_add_pricelist_info'][$pricelist]['valid_to_year']))
				{
					// set message
					$_SESSION['error_code'] = '05170';
					redirect($_SESSION['G']->modules[$_SESSION['module_id']]['module_name']);
				}

				// transform dates to real dates
				$valid_from_str												=	str_replace('/', '-', $this->input->post('valid_from_'.$pricelist));
				$vfrom														=	strtotime($valid_from_str);
				$valid_to_str												=	str_replace('/', '-', $this->input->post('valid_to_'.$pricelist));
				$vto														=	strtotime($valid_to_str);

				// verify valid to < valid from
				if ($vfrom > $vto)
				{
					// set message
					$_SESSION['error_code']									=	'05180';
					redirect($_SESSION['G']->modules[$_SESSION['module_id']]['module_name']);
				}
			}

	
			// calculate price without tax
			// get default tax rate for this item
			$item_tax_info = $this->Item_taxes->get_info($_SESSION['transaction_info']->item_id);

			// if not found, get default tax rate
			if (!$item_tax_info)
			{
				$tax_rate = $this->config->item('default_tax_1_rate');
			}
			else
			{
				$tax_rate = $item_tax_info[0]['percent'];
			}

			$tax_rate = (100 + $tax_rate) / 100;
			$_SESSION['transaction_add_pricelist_info'][$pricelist]['unit_price'] = $_SESSION['transaction_add_pricelist_info'][$pricelist]['unit_price_with_tax'] / $tax_rate;


			// so add record
			$this->db->from('items_pricelists');
			$this->db->where('item_id', $_SESSION['sel_item_id']);
			$this->db->where('pricelist_id', $pricelist);
			$this->db->update('items_pricelists', $_SESSION['transaction_add_pricelist_info'][$pricelist]);
		}

		// Update session unit_price from default pricelist for Article tab display
		$default_price_list_info = $this->Item->get_default_pricelist()->result_object();
		if (count($default_price_list_info) == 1)
		{
			foreach ($default_price_list_info as $row)
			{
				$_SESSION['transaction_info']->unit_price = $row->unit_price;
			}
		}

		// set up success message
		$_SESSION['error_code']	=	'05190';

		// re-route - use AJAX tab reload to stay on pricelist tab
		$this->ajax_tab_pricelists();
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

		// Load inventory history for stock tab
		$_SESSION['inventory_info']			=	$this->Inventory->get_inventory_data_for_item($item_id)->result_array();

        //Pour l'autofocus en utilisant la valeur de item_id
/*        $_SESSION['autofocus_avec_item_id'] = $item_id;
		$_SESSION['autofocus_avec_item_id_manage'] = $item_id;
//*/
        switch($_SESSION['global'])
        {
        	case 1:
        		$_SESSION['autofocus_avec_item_id_tabular'] = $item_id;
        		unset($_SESSION['global']);
        	break;
        
        	default:
				$_SESSION['autofocus_avec_item_id_manage'] = $item_id;
				unset($_SESSION['global']);
        	break;
        }


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
		//redirect("items");

		$data['items'] = $this->Item->search_desactive('azertyuiopqsdfghjklmwxcvbn');
        $this->load->view('items/manage', $data);
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
		// capture selected item IDs from checkbox selection (if any)
		$selected = $this->input->post('selected_ids');
		if ($selected) {
			$_SESSION['selected_item_ids'] = explode(',', $selected);
		} else {
			unset($_SESSION['selected_item_ids']);
		}

		// if a bulk_action_id was passed directly from the dropdown, skip the action selection dialog
		$bulk_action_id = $this->input->post('bulk_action_id');
		if ($bulk_action_id) {
			$_SESSION['bulk_action_id'] = $bulk_action_id;
			$this->bulk_action_2();
			return;
		}

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

			case 70:
				// title
				$_SESSION['$title']										=	$this->lang->line('items_bulk_suppliers');

				// initial defaults
				$_SESSION['transaction_update_supplier_info']			                  = new stdClass();
				$_SESSION['transaction_update_supplier_info']->supplier_id		          = $this->config->item('default_supplier_id');
				$_SESSION['transaction_update_supplier_info']->supplier_preferred         = "Y";    
				$_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size = 0;
                $_SESSION['transaction_update_supplier_info']->supplier_min_order_qty     = 0;
                $_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty     = 0;
				
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
		unset($_SESSION['bulk_select']);
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
		$_SESSION['bulk_data']['7']										=	trim($this->input->post('and_or_3'));
		$_SESSION['bulk_data']['8']										=	trim($this->input->post('column_id_3'));
		$_SESSION['bulk_data']['9']										=	trim($this->input->post('test_id_3'));
		$_SESSION['bulk_data']['10']									=	trim($this->input->post('value_3'));
		$_SESSION['bulk_select']										=	$this->input->post('metho');
		
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

		    case 70:
				
			//    $_SESSION['transaction_update_supplier_info']->supplier_id			      = trim($this->input->post('supplier_id'));
			    $_SESSION['transaction_update_supplier_info']->supplier_id                = $this->config->item('default_supplier_id');
				$_SESSION['transaction_update_supplier_info']->supplier_id_new            = trim($this->input->post('supplier_id_new'));
				$_SESSION['transaction_update_supplier_info']->supplier_reorder_policy	  = trim($this->input->post('supplier_reorder_policy'));
				$_SESSION['transaction_update_supplier_info']->supplier_preferred         = trim($this->input->post('supplier_preferred'));
				$_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size = intval(trim($this->input->post('supplier_reorder_pack_size')));
                $_SESSION['transaction_update_supplier_info']->supplier_min_order_qty     = intval(trim($this->input->post('supplier_min_order_qty')));
                $_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty     = intval(trim($this->input->post('supplier_min_stock_qty')));
				break;

			default:
				break;
		}

		// test input - there must be at least one value entered in the where
		if (empty($_SESSION['bulk_data']['2']) AND empty($_SESSION['bulk_data']['6'])AND $_SESSION['bulk_select']!='metho_by_select')
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
			
			case 70:
				// numeric values must be numeric
				if (!is_numeric($_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size))
				{
					$_SESSION['error_code']			= '01460';
					$_SESSION['substitution_parms']	= array($_SESSION['transaction_update_supplier_info']->supplier_reorder_pack_size);
				}
				if (!is_numeric($_SESSION['transaction_update_supplier_info']->supplier_min_order_qty))
				{
					$_SESSION['error_code']			= '01463';
					$_SESSION['substitution_parms']	= array($_SESSION['transaction_update_supplier_info']->supplier_min_order_qty);
					redirect("items");
				}
				if (!is_numeric($_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty))
				{
					$_SESSION['error_code']			= '01465';
					$_SESSION['substitution_parms']	= array($_SESSION['transaction_update_supplier_info']->supplier_min_stock_qty);
					redirect("items");
				}

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

		// create the WHERE SQL statement for the second value if entered
		if ($_SESSION['bulk_data']['10'] != NULL)
		{
			$_SESSION['sql_where_3']									=	$this->bulk_create_where($_SESSION['bulk_data']['8'], $_SESSION['bulk_data']['9'], $_SESSION['bulk_data']['10']);
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
			
			case 10:
				$_SESSION['sql_quantity']								=	'items.quantity <= 0';
				break;
			
			case 20:
				$_SESSION['sql_pricelist']								=	'items_pricelists.pricelist_id = "'.$_SESSION['transaction_update_pricelist_info']->pricelist_id.'"';
				break;
			case 30:
				$_SESSION['sql_reorderpolicy']							=	'items_suppliers.supplier_id = "'.$_SESSION['transaction_update_supplier_info']->supplier_id.'"';
				break;
			
			case 60:
			    $_SESSION['sql_reorderpolicy']							=	'items_suppliers.supplier_id = "'.$_POST['pricelist_id'].'"';    // fonctionne qu'avec $_POST
				break;

			case 70:
				//$_SESSION['sql_attacht_supplier']						=	'items_suppliers.supplier_id = "'.$_SESSION['transaction_update_supplier_info']->supplier_id.'"';
				//$_SESSION['sql_attacht_supplier']						=	'items_suppliers.supplier_id = "'.$_SESSION['transaction_update_supplier_info']->supplier_id.'"';
				$_SESSION['sql_supplier_preferred'] = 'items_suppliers.supplier_preferred = "Y"';
				
				break;
			
			default:
				break;
		}
		if ($_SESSION['bulk_select']=='metho_by_select')
		{
			$result = 	$_SESSION['data_items'];
			if (!empty($_SESSION['selected_item_ids'])) {
				$selected_ids = $_SESSION['selected_item_ids'];
				$result = array_filter($result, function($item) use ($selected_ids) {
					return in_array($item->item_id, $selected_ids);
				});
				$result = array_values($result);
			}
		}
		else
		{
			// now run the select to see how many items will be affected and present first 10 lines to user for confirmation
			$data_result															=	$this->Item->bulk_select();
			$result =	$data_result->result();
		}
		
		// set number of rows
		$_SESSION['bulk_num_rows']										=	count($result);
		
		// load the selection to array
		foreach	($result as $line=>$row)
		{
			// for bulk_selection_ids
			switch ($_SESSION['bulk_action_id'])
			{
				case 20:
					$_SESSION['bulk_selection'][]							=	array 	(
																						'item_id'		=>	$row->item_id,
																						'item_number'	=>	$row->item_number,
																						'item_name'		=>	$row->name,
																						'category_name'	=>	$row->category_name,
																						'supplier_name'	=>	$row->company_name,
																						'pricelist_id'	=>	$row->pricelist_id
																						);
					break;
				case 30:
					$_SESSION['bulk_selection'][]							=	array 	(
																						'item_id'		=>	$row->item_id,
																						'item_number'	=>	$row->item_number,
																						'item_name'		=>	$row->name,
																						'category_name'	=>	$row->category_name,
																						'supplier_name'	=>	$row->company_name,
																						'supplier_id'	=>	$row->supplier_id
																						);
					break;
			
				case 60:
					$_SESSION['bulk_selection'][]							=	array 	(
																						'item_id'		=>	$row->item_id,
																						'item_number'	=>	$row->item_number,
																						'item_name'		=>	$row->name,
																						'category_name'	=>	$row->category_name,
																						'supplier_name'	=>	$row->company_name,
																						'pricelist_id'	=>	$row->pricelist_id,
																						'supplier_id'	=>	$row->supplier_id
																						);
					break;

				case 70:
					$_SESSION['bulk_selection'][]							    = array (
																						'item_id'		=>	$row->item_id,
																						'item_number'	=>	$row->item_number,
																						'item_name'		=>	$row->name,
																						'category_name'	=>	$row->category_name,
																						'supplier_name'	=>	$row->company_name,
																						'supplier_id'	=>	$row->supplier_id
																						);
					break;
				default:
					$_SESSION['bulk_selection'][]							=	array 	(
																						'item_id'		=>	$row->item_id,
																						'item_number'	=>	$row->item_number,
																						'item_name'		=>	$row->name,
																						'category_name'	=>	$row->category_name,
																						'supplier_name'	=>	$row->company_name
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

				// modify supplier
				case 70:
					
				    //récupération du supplier_id pour le fournisseur ALFALIQUID
				    $list = array();
					$list = explode(" => ", $_SESSION['G']->supplier_pick_list[$_SESSION['transaction_update_supplier_info']->supplier_id_new]); 
					$list = explode(",", $list[1]);

					//récupération de tous les paramètres de l'article du fournisseur SONRISA
					$_SESSION['supplier_id'] = $_SESSION['transaction_update_supplier_info']->supplier_id;
					$transaction_update_supplier_info = $this->Item->get_info_supplier_cost_price_by_date($row['item_id']);
					$_SESSION['transaction_update_supplier_info']->supplier_item_number = $transaction_update_supplier_info[0]['supplier_item_number'];
					$_SESSION['transaction_update_supplier_info']->supplier_cost_price = $transaction_update_supplier_info[0]['supplier_cost_price'];
					$_SESSION['transaction_update_supplier_info']->supplier_bar_code = $transaction_update_supplier_info[0]['supplier_bar_code'];
					
					// update the items supplier info				
					//if(strtoupper($list[0]) == 'ALFALIQUID')
					//{
						//check if supplier a déjà été créé
						$id['supplier_id'] = $_SESSION['transaction_update_supplier_info']->supplier_id_new;
						$id['item_id'] = $row['item_id'];
						$data_item_supplier = $this->Item->get_info_with_item_id_and_supplier_id($id);
						if(!isset($data_item_supplier[0]))
						{
							$result = $this->Item->insert_supplier_by_supplier($row['item_id'], $row['supplier_id']);
						}
						else
						{
							$result = $this->Item->update_supplier_by_supplier_new($row['item_id'], $_SESSION['transaction_update_supplier_info']->supplier_id_new);
						}
					//}
					//else
					//{
                    //    $result = $this->Item->update_supplier_by_supplier($row['item_id'], $row['supplier_id']);
					//}
				//	$this->Item->update_supplier_by_supplier_into_items($row['item_id']);
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
		$_SESSION['transaction_info']				=	new stdClass();
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
					$_SESSION['previous_id_item'] = $item_id;
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
		if(isset($_POST['offer_value']))
        {
			$_SESSION['transaction_info']->offer_value						=	$this->input->post('offer_value');
		}
		$_SESSION['transaction_tax_info']->tax_name_1					=	$this->input->post('tax_name_1');
		$_SESSION['transaction_tax_info']->tax_percent_1				=	$this->input->post('tax_percent_1');

		if(!isset($_SESSION['transaction_info']->cost_price))
		{
			//valeur à 0.0
			$_SESSION['transaction_info']->cost_price = 0.0;
		}		
		if(!isset($_SESSION['transaction_info']->unit_price))
		{
			//valeur à 0.0
			$_SESSION['transaction_info']->unit_price = 0.0;
		}
		if($this->config->item('distributeur_vapeself') == 'Y')
		{
			$_SESSION['transaction_info']->emplacement = $this->input->post('emplacement');
			
			$_SESSION['transaction_info']->vs_nom = $this->input->post('vs_nom');
			$_SESSION['transaction_info']->vs_marque = $this->input->post('vs_marque');
			$_SESSION['transaction_info']->vs_category = $this->input->post('vs_category');
			$_SESSION['transaction_info']->vs_param_1 = $this->input->post('vs_param_1');
			$_SESSION['transaction_info']->vs_param_2 = $this->input->post('vs_param_2');
			$_SESSION['transaction_info']->vs_param_3 = $this->input->post('vs_param_3');
			$_SESSION['transaction_info']->vs_nom_image = $this->input->post('vs_nom_image');
			
			
		}
		else
		{
			$_SESSION['transaction_info']->vs_nom = '';
			$_SESSION['transaction_info']->vs_marque = '';
			$_SESSION['transaction_info']->vs_category = '';
			$_SESSION['transaction_info']->vs_param_1 = '';
			$_SESSION['transaction_info']->vs_param_2 = '';
			$_SESSION['transaction_info']->vs_param_3 = '';
			$_SESSION['transaction_info']->vs_nom_image = '';
		}

		// do data verifications
		$this															->	verify();

		// if here then all checks succeeded so do the update
		$this															->	Item->save();


		if($_SESSION['$title'] == "Cloner")
		{
			switch ($_SESSION['new'] ?? 0)
			{
				// add item
				case	1:

					//duplicate pricelists
					$data 							=	array();
					$this							->	db->select();
					$this							->	db->from('items_pricelists');
					$this							->	db->where('items_pricelists.branch_code', $this->config->item('branch_code'));
					$this							->	db->where('items_pricelists.item_id', $_SESSION['previous_id_item']);
					$data 							= 	$this->db->get()->result_array();
					
					$_SESSION['clone_info'] = array();
					foreach($data as $pricelist)
					{
						$_SESSION['clone_info']['pricelist_id'] = $pricelist['pricelist_id'];
						$_SESSION['clone_info']['item_id'] = $_SESSION['transaction_info']->item_id;
						$_SESSION['clone_info']['unit_price'] = $pricelist['unit_price'];
						$_SESSION['clone_info']['unit_price_with_tax'] = $pricelist['unit_price_with_tax'];
						$_SESSION['clone_info']['valid_from_year'] = $pricelist['valid_from_year'];
						$_SESSION['clone_info']['valid_from_month'] = $pricelist['valid_from_month'];
						$_SESSION['clone_info']['valid_from_day'] = $pricelist['valid_from_day'];
						$_SESSION['clone_info']['valid_to_year'] = $pricelist['valid_to_year'];
						$_SESSION['clone_info']['valid_to_month'] = $pricelist['valid_to_month'];
						$_SESSION['clone_info']['valid_to_day'] = $pricelist['valid_to_day'];
						$_SESSION['clone_info']['deleted'] = 0;
						$_SESSION['clone_info']['creation_date'] = date("Y-m-d H:i:s");
						$_SESSION['clone_info']['change_date'] = date("Y-m-d H:i:s");
						$_SESSION['clone_info']['branch_code'] = $this->config->item('branch_code');
						$this			->	db->insert('items_pricelists', $_SESSION['clone_info']);
						unset($_SESSION['clone_info']);
					}


					//duplicate suppliers
					$data 							=	array();
					$this							->	db->select();
					$this							->	db->from('items_suppliers');
					$this							->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
					$this							->	db->where('items_suppliers.item_id', $_SESSION['previous_id_item']);
					$data 							= 	$this->db->get()->result_array();
					
					$_SESSION['clone_info'] = array();
					foreach($data as $supplier)
					{
						$_SESSION['clone_info']['supplier_id'] = $supplier['supplier_id'];
						$_SESSION['clone_info']['item_id'] = $_SESSION['transaction_info']->item_id;
						$_SESSION['clone_info']['supplier_preferred'] = $supplier['supplier_preferred'];
						$_SESSION['clone_info']['supplier_item_number'] = 0;
						$_SESSION['clone_info']['supplier_cost_price'] = $supplier['supplier_cost_price'];
						$_SESSION['clone_info']['supplier_reorder_policy'] = $supplier['supplier_reorder_policy'];
						$_SESSION['clone_info']['supplier_reorder_pack_size'] = $supplier['supplier_reorder_pack_size'];
						$_SESSION['clone_info']['supplier_min_order_qty'] = $supplier['supplier_min_order_qty'];
						$_SESSION['clone_info']['supplier_min_stock_qty'] = $supplier['supplier_min_stock_qty'];
						$_SESSION['clone_info']['supplier_bar_code'] = 0;
						$_SESSION['clone_info']['supplier_reorder_level'] = $supplier['supplier_reorder_level'];
						$_SESSION['clone_info']['supplier_reorder_quantity'] = $supplier['supplier_reorder_quantity'];
						$_SESSION['clone_info']['deleted'] = 0;
						$_SESSION['clone_info']['branch_code'] = $this->config->item('branch_code');
						$this			->	db->insert('items_suppliers', $_SESSION['clone_info']);
						unset($_SESSION['clone_info']);
					}

				break;

				// update item
				default:
				break;
			}
		}
		

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
					$quantity_item = $this->Item->get_info($_SESSION['transaction_info']->item_id);
					$_SESSION['transaction_info']->quantity = $quantity_item->quantity; 
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

	function integration_item_to($item_id)
	{
		//integration item
		$data_item = $this->Item->get_info($item_id);
		if($data_item->DynamicKit == 'Y')
		{
            $code_bar = $data_item->barcode;
		}
		if($data_item->DynamicKit != 'Y')
		{
			$data_item_supplier = $this->Item->get_supplier_id($item_id);
			$code_bar = $data_item_supplier[0]['supplier_bar_code'];
		}

		$input['codebarre'] = $code_bar;
		$input['id'] = $item_id;

		$data_vs_item = $this->remonter_info_article_vapeself($input);
		
		//item ever exist
		unset($_SESSION['new']);

        //$_SESSION['transaction_info']-> = $data_vs_item['ID_PRODUIT']:21;
        $_SESSION['transaction_info']->vs_nom = $data_vs_item['NOM']; //"CLASSIQUE FR-4";
        $_SESSION['transaction_info']->barcode = $data_vs_item['CODEBARRE'];    //:"3662572100662";
        //$_SESSION['transaction_info']-> = $data_vs_item['CARAC1'];    //:"Nicotine";
        //$_SESSION['transaction_info']-> = $data_vs_item['CARAC2'];    //:"Volume";
        //$_SESSION['transaction_info']-> = $data_vs_item['CARAC3'];    //:"";
        $_SESSION['transaction_info']->vs_param_1 = $data_vs_item['VALEUR1'];    //:"6";
        $_SESSION['transaction_info']->vs_param_2 = $data_vs_item['VALEUR2'];    //:"10";
        $_SESSION['transaction_info']->vs_param_3 = $data_vs_item['VALEUR3'];    //:"";
        $_SESSION['transaction_info']->vs_nom_image = $data_vs_item['NOMIMAGE'];    //:"SO00539.jpg";
        $_SESSION['transaction_info']->vs_marque = $data_vs_item['MARQUE'];    //:"ALFALIQUID";
        //$_SESSION['transaction_info']-> = $data_vs_item['PRIXACHAT'];    //:0;
        //$_SESSION['transaction_info']-> = $data_vs_item['PRIXVENTE'];    //:5.9;
        //$_SESSION['transaction_info']-> = $data_vs_item['RECREDIT'];    //:0;
        //$_SESSION['transaction_info']-> = $data_vs_item['TAUXTVA'];    //:1.2;
        $_SESSION['transaction_info']->emplacement = $data_vs_item['TYPECASIER'];    //:1;
        $_SESSION['transaction_info']->item_id = $data_vs_item['VOTREID'];    //:"545";
//        $_SESSION['transaction_info']-> = $data_vs_item['MONID'];    //:232;
        
      //  $_SESSION['transaction_info']-> = $data_vs_item['MODIFIE'];    //:0;
       
        
       // $_SESSION['transaction_info']-> = $data_vs_item['VOTREIDGLOBAL'];    //:;
		$_SESSION['transaction_info']->item_id = $item_id;
		//$_SESSION['transaction_info']-> = ;
//*/
/*
$_SESSION['transaction_info']-> = $data_vs_item['TYPE'];    //:1;
$_SESSION['transaction_info']-> = $data_vs_item['DESCRIPTION'];    //:"";
$_SESSION['transaction_info']-> = $data_vs_item['GAMME'];    //:"25";//*/

		$_SESSION['transaction_info']->name					= $this->input->post('name');
		$_SESSION['transaction_info']->description			= $this->input->post('description');
		$_SESSION['transaction_info']->volume				= $this->input->post('volume');
		$_SESSION['transaction_info']->nicotine				= $this->input->post('nicotine');
		$_SESSION['transaction_info']->category_id			= $this->input->post('category_id');
		//$_SESSION['transaction_info']->category				= $category_info->category_name;
		$_SESSION['transaction_info']->item_number			= $this->input->post('item_number');
		$_SESSION['transaction_info']->DynamicKit			= $this->input->post('DynamicKit');
		$_SESSION['transaction_info']->dluo_indicator		= $this->input->post('dluo_indicator');
		$_SESSION['transaction_info']->giftcard_indicator	= $this->input->post('giftcard_indicator');
		$_SESSION['transaction_info']->offer_indicator		= $this->input->post('offer_indicator');
		$_SESSION['transaction_info']->kit_reference		= $this->input->post('kit_reference');
		$_SESSION['transaction_info']->barcode				= $this->input->post('barcode');
		$_SESSION['transaction_info']->export_to_franchise	= $this->input->post('export_to_franchise');
		$_SESSION['transaction_info']->export_to_integrated	= $this->input->post('export_to_integrated');
		$_SESSION['transaction_info']->export_to_other		= $this->input->post('export_to_other');
		$_SESSION['transaction_info']->image_file_name		= $this->input->post('image_file_name');
		$_SESSION['transaction_info']->branch_code			= $this->config->item('branch_code');

		$_SESSION['transaction_info']->emplacement = $this->input->post('emplacement');
		$_SESSION['transaction_info']->vs_nom = $this->input->post('vs_nom');
		$_SESSION['transaction_info']->vs_marque = $this->input->post('vs_marque');
		$_SESSION['transaction_info']->vs_category = $this->input->post('vs_category');
		$_SESSION['transaction_info']->vs_param_1 = $this->input->post('vs_param_1');
		$_SESSION['transaction_info']->vs_param_2 = $this->input->post('vs_param_2');
		$_SESSION['transaction_info']->vs_param_3 = $this->input->post('vs_param_3');
		$_SESSION['transaction_info']->vs_nom_image = $this->input->post('vs_nom_image');
	}

	function remonter_info_article_vapeself($input)
	{
		//envoie d'un mail d'alerte
		$mail_config = array(
			'protocol'		=>	'smtp',
			'smtp_host' 	=>	'ssl://mail.sonrisa-smile.com',
			'smtp_port' 	=>	'465',
			'smtp_user' 	=> 	$this->config->item('POemail'),
			'smtp_pass' 	=> 	$this->config->item('POemailpwd'),
			'mailtype'  	=>	'html',
			'starttls'  	=>	FALSE,
			'wordwrap'		=>	TRUE,    // Dans le cas où nos lignes comportent plus de 70 caractères, nous les coupons en utilisant wordwrap() $message = wordwrap($message, 70, "\r\n");
			'smtp_timeout'	=>	60,
			'newline'   	=>	"\r\n"
			);
		$this->load->library('email', $mail_config);
		//chargement de la class vapeself
		$this->load->library("../controllers/vapeself");

		//demande de token
		$token = $this->vapeself->get_token();

		//get info item vapeself
		$return = $this->vapeself->get_GetInfo($token, $input);
		
        return $return;
	}

	function verify_info_article_vapeself($item_id)
	{
		$data_item = $this->Item->get_info($item_id);
		if($data_item->DynamicKit == 'Y')
		{
            $code_bar = $data_item->barcode;
		}
		if($data_item->DynamicKit != 'Y')
		{
			$data_item_supplier = $this->Item->get_supplier_id($item_id);
			$code_bar = $data_item_supplier[0]['supplier_bar_code'];
		}

		$input['codebarre'] = $code_bar;
		$input['id'] = $item_id;

		if(!isset($input['codebarre']) || ($input['codebarre'] == ''))
		{
			//Il manque le code barre
		}
		
		$data_vs_item = $this->remonter_info_article_vapeself($input);

		if(is_object($data_vs_item))
		{
			//
			$search = $data_vs_item->Message;
			echo "$search";
			$research = strstr($search, "Aucune ressource HTTP correspondant à l’URI de demande");
//			$research = stripos("ucune ressource HTTP correspondant à l’URI de demande", $search);
//			$research = strpos("ucune ressource HTTP correspondant à l’URI de demande", $search);
			
			if($research !== FALSE)
			{
			    $_SESSION['substitution_parms'][1] = "Produit inexistant dans le distributeur";
			    //$_SESSION['substitution_parms'][1] = $data_vs_item->Message;
				$_SESSION['error_code'] = '07490';
		    }
		}

		$_SESSION['transaction_info'] = new stdClass();
		$_SESSION['transaction_info'] = $this->Item->get_all_info_item($item_id);
		if(!isset($_SESSION['transaction_info']->vs_category))
		{
			$_SESSION['transaction_info'] = $this->Item->get_all_info_item_for_kit($item_id);
		}
		$data_MajProduit = array();
		// attention même ordre que dans produit.csv
		$replace_nom_item = array("10 ML","10ML","0MG","0 MG","3MG","3 MG","6MG","6 MG","11MG","11 MG","12MG","12 MG","16MG","16 MG","19.6MG","19.6 MG","50ML","50 MG","50/50");

		switch($_SESSION['transaction_info']->vs_category)
		{
			//pour les ELiquides
			case '1':
			    $carac1 = 'Nicotine';
			    $carac2 = 'Volume';
			    $carac3 = 'PG / VG';
			break;
			//pour le matériel
			case '2':
				$carac1 = 'Couleur';
		    	$carac2 = '';
		    	$carac3 = '';
			break;
			default:
			break;
		}
		$prix_vente = $this->Item->get_items_pricelists_item_id($item_id);
		$data_MajProduit['NOM'] = $_SESSION['transaction_info']->vs_nom;
		if(!isset($_SESSION['transaction_info']->supplier_bar_code))
		{
			$_SESSION['transaction_info']->supplier_bar_code = $_SESSION['transaction_info']->barcode;
		}
		$data_MajProduit['CODEBARRE'] = $_SESSION['transaction_info']->supplier_bar_code;
        $data_MajProduit['CARAC1'] = $carac1;
        $data_MajProduit['CARAC2'] = $carac2;
        $data_MajProduit['CARAC3'] = $carac3;
        $data_MajProduit['VALEUR1'] = $_SESSION['transaction_info']->vs_param_1;
        $data_MajProduit['VALEUR2'] = $_SESSION['transaction_info']->vs_param_2;
        $data_MajProduit['VALEUR3'] = $_SESSION['transaction_info']->vs_param_3;
        $data_MajProduit['PRIXACHAT'] = 0;
        $data_MajProduit['PRIXVENTE'] = strval(floatval($prix_vente->unit_price_with_tax));
        $data_MajProduit['RECREDIT'] = 0;
		$data_MajProduit['TAUXTVA'] = floatval(((100 + floatval($this->config->item('default_tax_1_rate')))/100));
		if(isset($_SESSION['transaction_info']->vs_nom_image))
		{
			$vs_image = $_SESSION['transaction_info']->vs_nom_image;
		}
		if(!isset($_SESSION['transaction_info']->vs_nom_image))
		{
			$vs_image = $_SESSION['transaction_info']->item_number . '.jpg';
		}
        $data_MajProduit['NOMIMAGE'] = $vs_image;
        $data_MajProduit['MARQUE'] = $_SESSION['transaction_info']->vs_marque;
        $data_MajProduit['TYPECASSIER'] = $_SESSION['transaction_info']->emplacement;
		$data_MajProduit['VOTREID'] = $_SESSION['transaction_info']->item_id;
		$votreidglobal = $data_MajProduit['NOM'] . $_SESSION['transaction_info']->category_id . $data_MajProduit['MARQUE'];
        $data_MajProduit['VOTREIDGLOBAL'] = md5($votreidglobal);    //MD5(nom_abrege + category + marque [+ ML ]) exemple: Md5('CLASSIQUE FR-47ALFALIQUID')
        $data_MajProduit['GAMME'] = $_SESSION['transaction_info']->category_id;
        $data_MajProduit['TYPE'] = $_SESSION['transaction_info']->vs_category;
        $data_MajProduit['DESCRIPTION'] = $_SESSION['transaction_info']->description;

		$diff = 0;
        foreach($data_MajProduit as $line => $data)
        {
			if(is_array($data_vs_item))
			{
	    		if($data != $data_vs_item[0]->$line)
            	{
                    $diff = 1;
	    			//mail
	    		}
	    	}
		}
		
		if($diff == 1)
		{
			$_SESSION['substitution_parms'][1] = "Erreur détectée dans le paramétrage  du produit";
			$_SESSION['error_code'] = '07490';
		}

		if($diff == 1)
		{
			//envoie d'un mail d'alerte
			$mail_config = array(
				'protocol'		=>	'smtp',
				'smtp_host' 	=>	'ssl://mail.sonrisa-smile.com',
				'smtp_port' 	=>	'465',
				'smtp_user' 	=> 	$this->config->item('POemail'),
				'smtp_pass' 	=> 	$this->config->item('POemailpwd'),
				'mailtype'  	=>	'html',
				'starttls'  	=>	FALSE,
				'wordwrap'		=>	TRUE,    // Dans le cas où nos lignes comportent plus de 70 caractères, nous les coupons en utilisant wordwrap() $message = wordwrap($message, 70, "\r\n");
				'smtp_timeout'	=>	60,
				'newline'   	=>	"\r\n"
				);
				
				//Chargement
				$this->load->library('email', $mail_config);
				$this->email->from($this->config->item('email'), $this->config->item('company'));    //Fonction avec l'email de Troyes -> changement pour ne pas causer de problèmes
	
				//Envoie du mail à la personne consernée
				$this->email->to('david@sonrisa-smile.com, guillaume@yesstore.fr');
				$this->email->cc($this->config->item('email'));
				
				$this->email->subject('Problème de cohérence entre les paramètres du POS et du distributeur vapeself');
				$message_mail = '';
				$message_mail .= $data_MajProduit['NOM'];
				$message_mail .= '<br>';
				$message_mail .= $data_MajProduit['CODEBARRE'];
				$message_mail .= '<table>';
				$message_mail .= '<tr>';
				$message_mail .= '<td>LIBELLE</td><td>POS</td><td>Distributeur Vapeself</td>';
				$message_mail .= '</tr>';
				
				foreach($data_MajProduit as $line => $data)
				{
					if($data != $data_vs_item[0]->$line)
					{
						$message_mail .= '<tr>';
						$message_mail .= '<td>';
						$message_mail .= $line . ': </td><td>[' . $data . ']</td> / <td>[' . $data_vs_item[0]->$line . ']';
						$message_mail .= '</td>';
						$message_mail .= '</tr>';
					}
				}
				$message_mail .= '</table>';

				$this->email->message($message_mail);
				$this->email->send();
		}
		if(($diff != 1) && is_array($data_vs_item))
		{
			$_SESSION['substitution_parms'][1] = "Informations valide";
			$_SESSION['error_code'] = '07480';
		}

		redirect("items");
	}

	function update_VS_item($item_id=-1)
	{
		$_SESSION['transaction_info']				=	new stdClass();


		$_SESSION['transaction_info']						=	$this->Item->get_all_info_item($item_id);
		if(!isset($_SESSION['transaction_info']->vs_category))
		{
			$_SESSION['transaction_info'] = $this->Item->get_all_info_item_for_kit($item_id);
		}
		
		$data_MajProduit = array();

		// attention même ordre que dans produit.csv
		$replace_nom_item = array("10 ML",
                                   "10ML",
                                   "0MG",
                                   "0 MG",
                                   "3MG",
                                   "3 MG",
                                   "6MG",
                                   "6 MG",
                                   "11MG",
                                   "11 MG",
                                   "12MG",
                                   "12 MG",
                                   "16MG",
                                   "16 MG",
                                   "19.6MG",
                                   "19.6 MG",
                                   "50ML",
								   "50 MG",
								   "50/50" 
						        );

		switch($_SESSION['transaction_info']->vs_category)
		{
			//pour les ELiquides
			case '1':
			    $carac1 = 'Nicotine';
			    $carac2 = 'Volume';
			    $carac3 = 'PG / VG';
			break;

			//pour le matériel
			case '2':
				$carac1 = 'Couleur';
		    	$carac2 = '';
		    	$carac3 = '';
			break;
			
			default:
			    $_SESSION['error_code'] = '07400';
			    redirect("items");
			break;
		}

		$prix_vente = $this->Item->get_items_pricelists_item_id($item_id);

		$data_MajProduit['NOM'] = $_SESSION['transaction_info']->vs_nom;
		if(!isset($_SESSION['transaction_info']->supplier_bar_code))
		{
			$_SESSION['transaction_info']->supplier_bar_code = $_SESSION['transaction_info']->barcode;
		}
		$data_MajProduit['CODEBARRE'] = $_SESSION['transaction_info']->supplier_bar_code;
        $data_MajProduit['CARAC1'] = $carac1;
        $data_MajProduit['CARAC2'] = $carac2;
        $data_MajProduit['CARAC3'] = $carac3;
        $data_MajProduit['VALEUR1'] = $_SESSION['transaction_info']->vs_param_1;
        $data_MajProduit['VALEUR2'] = $_SESSION['transaction_info']->vs_param_2;
        $data_MajProduit['VALEUR3'] = $_SESSION['transaction_info']->vs_param_3;
        $data_MajProduit['PRIXACHAT'] = 0;
        $data_MajProduit['PRIXVENTE'] = strval(floatval($prix_vente->unit_price_with_tax));
        $data_MajProduit['RECREDIT'] = 0;
		$data_MajProduit['TAUXTVA'] = floatval(((100 + floatval($this->config->item('default_tax_1_rate')))/100));
		if(isset($_SESSION['transaction_info']->vs_nom_image))
		{
			$_SESSION['transaction_info']->item_number = $_SESSION['transaction_info']->vs_nom_image;
		}

        $data_MajProduit['NOMIMAGE'] = $_SESSION['transaction_info']->item_number . '.jpg';
        $data_MajProduit['MARQUE'] = $_SESSION['transaction_info']->vs_marque;
        $data_MajProduit['TYPECASSIER'] = $_SESSION['transaction_info']->emplacement;
		$data_MajProduit['VOTREID'] = $_SESSION['transaction_info']->item_id;
		$votreidglobal = $data_MajProduit['NOM'] . $_SESSION['transaction_info']->category_id . $data_MajProduit['MARQUE'];
        $data_MajProduit['VOTREIDGLOBAL'] = md5($votreidglobal);    //MD5(nom_abrege + category + marque [+ ML ]) exemple: Md5('CLASSIQUE FR-47ALFALIQUID')
        $data_MajProduit['GAMME'] = $_SESSION['transaction_info']->category_id;
        $data_MajProduit['TYPE'] = $_SESSION['transaction_info']->vs_category;
        $data_MajProduit['DESCRIPTION'] = $_SESSION['transaction_info']->description;

		//chargement de la class vapeself
		$this->load->library("../controllers/vapeself");

		//demande de token
		$token = $this->vapeself->get_token();
		
		//mise à jour ou inséretion du client
		$return_MajProduit = $this->vapeself->post_MajProduit($token, $data_MajProduit);
    	// set data
		switch($return_MajProduit)
		{
			case "Ok":
				$_SESSION['error_code']		=	'07370';
				
            break;
			
			default:

			break;
		}

		redirect("items");
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
		foreach($_SESSION['report_data'] as $index => $row)
		{
			//$_SESSION['report_data'][$index]['check_modif']=1;
			if($row['item_id'] == $item_id)
			{
				$_SESSION['report_data'][$index]['check_modif']=1;
			}
		}
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
// 	Inline inventory AJAX (rolling inventory)
//	------------------------------------------------------------------------------------------

	function save_inventory_inline()
	{
		// read POST data
		$item_id			=	intval($this->input->post('item_id'));
		$real_quantity		=	floatval($this->input->post('real_quantity'));
		$newquantity		=	floatval($this->input->post('newquantity'));
		$dluo_indicator		=	$this->input->post('dluo_indicator');
		$trans_comment		=	$this->input->post('trans_comment');
		if (empty($trans_comment))
		{
			$trans_comment	=	'Inventaire tournant inline';
		}

		// mark item as counted in session report data
		if (isset($_SESSION['report_data']) && is_array($_SESSION['report_data']))
		{
			foreach ($_SESSION['report_data'] as $index => $row)
			{
				if ($row['item_id'] == $item_id)
				{
					$_SESSION['report_data'][$index]['check_modif'] = 1;
				}
			}
		}

		// get current item info
		$cur_item_info		=	$this->Item->get_info($item_id);

		if (empty($cur_item_info->item_id))
		{
			header('Content-Type: application/json');
			echo json_encode(array('success' => false, 'message' => 'Article introuvable'));
			return;
		}

		// calculate stock after adjustment
		$trans_stock_after	=	$cur_item_info->quantity + $newquantity;

		// INSERT into ospos_inventory
		$inv_data			=	array(
									'trans_date'			=>	date('Y-m-d H:i:s'),
									'trans_items'			=>	$item_id,
									'trans_user'			=>	$_SESSION['G']->login_employee_id,
									'trans_comment'			=>	$trans_comment,
									'trans_stock_before'	=>	$cur_item_info->quantity,
									'trans_inventory'		=>	$newquantity,
									'trans_stock_after'		=>	$trans_stock_after,
									'branch_code'			=>	$this->config->item('branch_code')
								);
		$this->Inventory->insert($inv_data);

		// UPDATE ospos_items: quantity + rolling_inventory_indicator
		$_SESSION['transaction_info']									=	new stdClass();
		$_SESSION['new']												=	0;
		$_SESSION['transaction_info']->item_id							=	$item_id;
		$_SESSION['transaction_info']->quantity							=	$trans_stock_after;
		$_SESSION['transaction_info']->rolling_inventory_indicator		=	1;

		$report		=	$this->Item->save();

		// UPDATE ospos_stock_valuation (same logic as save_inventory)
		$item_supplier_data		=	$this->Item->item_supplier_get_cost($item_id);

		if ($item_supplier_data)
		{
			$cost_price		=	$item_supplier_data->supplier_cost_price;
		}
		else
		{
			$cost_price		=	0;
		}

		if ($trans_stock_after < 0)
		{
			$this->Item->value_delete_item_id($item_id);
			$data		=	array(
								'value_item_id'			=>	$item_id,
								'value_cost_price'		=>	$cost_price,
								'value_qty'				=>	$trans_stock_after,
								'value_trans_id'		=>	0,
								'branch_code'			=>	$this->config->item('branch_code')
							);
			$this->Item->value_write($data);
		}
		else
		{
			$value_remaining_qty	=	-1 * $newquantity;
			$value_trans_id			=	0;
			$this->Item->value_update($value_remaining_qty, $item_id, $cost_price, $value_trans_id);
		}

		// build JSON response
		$response	=	array(
							'success'		=>	true,
							'message'		=>	'Stock mis a jour: '.$cur_item_info->item_number.' '.$cur_item_info->name,
							'new_quantity'	=>	$trans_stock_after,
							'adjustment'	=>	$newquantity
						);

		// check DLUO if applicable
		if ($dluo_indicator == 'Y')
		{
			$cur_item_info_fresh	=	$this->Item->get_info($item_id);
			$item_info_dluo			=	$this->Item->get_info_dluo($item_id)->result_array();

			$dluo_total_qty			=	0;
			foreach ($item_info_dluo as $dluo_row)
			{
				$dluo_total_qty		=	$dluo_total_qty + $dluo_row['dluo_qty'];
			}

			if ($dluo_total_qty != $cur_item_info_fresh->quantity)
			{
				$response['dluo_redirect']	=	site_url('items/dluo_form/'.$item_id.'/IR/0');
			}
		}

		header('Content-Type: application/json');
		echo json_encode($response);
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
		// Initialize session objects for PHP 8.3 compatibility
		if (!isset($_SESSION['transaction_add_supplier_info']) || !is_object($_SESSION['transaction_add_supplier_info'])) {
			$_SESSION['transaction_add_supplier_info'] = new stdClass();
		}
		if (!isset($_SESSION['add_supplier_info_statut_offre']) || !is_object($_SESSION['add_supplier_info_statut_offre'])) {
			$_SESSION['add_supplier_info_statut_offre'] = new stdClass();
		}
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

        $statut_offre = $this->Item->get_info($_SESSION['transaction_add_supplier_info']->item_id);
		$_SESSION['add_supplier_info_statut_offre']->statut_offre = $statut_offre->offer_indicator;
		if($_SESSION['add_supplier_info_statut_offre']->statut_offre == 'Y')
		{
		    if($_SESSION['transaction_add_supplier_info']->supplier_cost_price == "0")
		    {
		    	$_SESSION['transaction_add_supplier_info']->supplier_cost_price = "0.0";
		    }
		}

		//$search = array(',');
		//$replace = array('.');
		$_SESSION['transaction_add_supplier_info']->supplier_cost_price = str_replace($_SESSION['G']->number['virgule'], $_SESSION['G']->number['point'], $_SESSION['transaction_add_supplier_info']->supplier_cost_price);

		// verify supplier entries
		$this->verify_supplier();

		// so add record
		$this->Item->save_supplier();

		// Update session cost_price from preferred supplier for Article tab display
		$item_supplier_info = $this->Item->item_supplier_get_cost($_SESSION['transaction_info']->item_id);
		if ($item_supplier_info != NULL)
		{
			$_SESSION['preferred_supplier_cost_price'] = $item_supplier_info->supplier_cost_price;
		}

		// set up success message
		$_SESSION['error_code']	=	'01840';

		//update kits
		$array = $this->Item_kit->get_item_kits_item($_SESSION['transaction_info']->item_id);

		foreach($array as $key => $value)
		{
			$cost_ = $this->Item_kit->select_all_with_cost_price($value['item_kit_id']);
            $inputs = array('item_kit_id' => $value['item_kit_id'], 'supplier_cost_price' => $cost_[0]['cost_kit']);
			$this->Item_kit->update_all_with_cost_price($inputs);
		}

		// re-route - use AJAX tab reload to stay on supplier tab
		$this->ajax_tab_suppliers();
	}

	// update supplier
	function item_supplier_update()
	{
		$_SESSION['transaction_add_supplier_info'] = array();
		// Initialize session object for PHP 8.3 compatibility
		if (!isset($_SESSION['add_supplier_info_statut_offre']) || !is_object($_SESSION['add_supplier_info_statut_offre'])) {
			$_SESSION['add_supplier_info_statut_offre'] = new stdClass();
		}
		$supplier_preferred_count = 0;
		$last_preferred_supplier = null;
		foreach($_SESSION['suppliers_id'] as $key => $supplier)
		{
			// store user entries
			$_SESSION['transaction_add_supplier_info'][$supplier] 								= 	array();
			$_SESSION['supplier_id']															=	$supplier;
			$_SESSION['transaction_add_supplier_info'][$supplier]['item_id']					=	$_SESSION['transaction_info']->item_id;
			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_preferred'] 		= 	$this->input->post('supplier_preferred_'.$supplier);
			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_item_number'] 		= 	$this->input->post('supplier_item_number_'.$supplier);
			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_cost_price'] 		= 	$this->input->post('supplier_cost_price_'.$supplier);
			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_reorder_policy'] 	= 	$this->input->post('supplier_reorder_policy_'.$supplier);
			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_reorder_pack_size'] = 	$this->input->post('supplier_reorder_pack_size_'.$supplier);
			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_min_order_qty'] 	= 	$this->input->post('supplier_min_order_qty_'.$supplier);
			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_min_stock_qty'] 	= 	$this->input->post('supplier_min_stock_qty_'.$supplier);
			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_bar_code'] 			= 	$this->input->post('supplier_bar_code_'.$supplier);
			$_SESSION['supplier_bar_code'] 														= 	$this->input->post('supplier_bar_code_'.$supplier);
			$_SESSION['supplier_item_number']													=	$this->input->post('supplier_item_number_'.$supplier);

			
			$statut_offre = $this->Item->get_info($_SESSION['transaction_add_supplier_info'][$supplier]['item_id']);
			$_SESSION['add_supplier_info_statut_offre']->statut_offre = $statut_offre->offer_indicator;
			if($_SESSION['add_supplier_info_statut_offre']->statut_offre == 'Y')
			{
		   		if($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_cost_price'] == "0")
		    	{
		    		$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_cost_price'] = "0.0";
		    	}
			}

			$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_cost_price'] = str_replace($_SESSION['G']->number['virgule'], $_SESSION['G']->number['point'], $_SESSION['transaction_add_supplier_info'][$supplier]['supplier_cost_price']);
			
			
			// verify supplier entries

			// verify required fields are entered
			if 	((empty($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_preferred'])
			OR	empty($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_cost_price'])
			OR 	empty($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_reorder_policy'])
			&&  ($_SESSION['add_supplier_info_statut_offre']->statut_offre == 'N')))
			{
			// set message

				$_SESSION['error_code']			=	'00030';
				redirect("items");
			}

			// check item_number duplicate
			$this						->	db->from('items_suppliers');
			$this						->	db->where('supplier_id', $_SESSION['supplier_id']);
			$this						->	db->where('supplier_item_number', $_SESSION['supplier_item_number']);
			$this						->	db->where('item_id', $_SESSION['transaction_add_supplier_info'][$supplier]['item_id']);
			$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
			$report_data = $this->db->get()->result_array();
			if (!empty($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_item_number']) && $report_data[0]['supplier_item_number'] =! $_SESSION['transaction_add_supplier_info'][$supplier]['supplier_item_number'] || $report_data[0]['supplier_item_number'] == NULL)
			{
				$count								=	$this->Item->check_supplier_item_number_duplicate_update();
				if ($count > 0)
				{
					// set message
					$_SESSION['error_code']			=	'01800';
					redirect("items");
				}
			}

			// check cost_price is numeric
			if (!is_numeric($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_cost_price']))
			{
				// set message
				$_SESSION['error_code']			=	'01410';
				redirect("items");
			}

			// If reorder_policy is Y, reorder data must be entered
			if ($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_reorder_policy'] == 'Y')
			{
				if ($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_reorder_pack_size'] <= 0)
				{
					// set message
					$_SESSION['error_code']		=	'00190';
					redirect("items");
					
				}

				if ($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_min_order_qty'] <= 0)
				{
					// set message
					$_SESSION['error_code']		=	'00190';
					redirect("items");
					
				}

				if ($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_min_stock_qty'] <= 0)
				{
					// set message
					$_SESSION['error_code']		=	'00190';
					redirect("items");
				}

				// check reorder_pack_size is numeric
				if (!is_numeric($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_reorder_pack_size']))
				{
					// set message
					$_SESSION['error_code']			=	'01460';
					redirect("items");
				}

				// check min_order_qty is numeric
				if (!is_numeric($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_min_order_qty']))
				{
					// set message
					$_SESSION['error_code']			=	'01463';
					redirect("items");
				}

				// check min_stock_qty is numeric
				if (!is_numeric($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_min_stock_qty']))
				{
					// set message
					$_SESSION['error_code']			=	'01465';
					redirect("items");
				}
			}

			// If reorder_policy is N, force reorder data zero
			if ($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_reorder_policy'] == 'N')
			{
				$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_reorder_pack_size'] = 0;
				$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_min_order_qty'] = 0;
				$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_min_stock_qty'] = 0;
			}

			// check bar_code duplicate on this supplier
			$this						->	db->from('items_suppliers');
			$this						->	db->where('supplier_id', $_SESSION['supplier_id']);
			$this						->	db->where('supplier_bar_code', $_SESSION['supplier_bar_code']);
			$this						->	db->where('item_id', $_SESSION['transaction_add_supplier_info'][$supplier]['item_id']);
			$this						->	db->where('items_suppliers.branch_code', $this->config->item('branch_code'));
			$report_data = $this->db->get()->result_array();
			if (!empty($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_bar_code']) && $report_data[0]['supplier_bar_code'] =! $_SESSION['transaction_add_supplier_info'][$supplier]['supplier_bar_code'] || $report_data[0]['supplier_bar_code'] == NULL)
			{
				$count								=	$this->Item->check_supplier_bar_code_duplicate_update();
				if ($count > 0)
				{
					// set message
					$_SESSION['error_code']			=	'01860';
					redirect("items");
				}
			}
			if($_SESSION['transaction_add_supplier_info'][$supplier]['supplier_preferred']=='Y')
			{
				$supplier_preferred_count += 1;
				$last_preferred_supplier = $supplier;
			}
		}

		// If more than one supplier is marked as preferred, keep only the last one
		if($supplier_preferred_count > 1)
		{
			foreach($_SESSION['suppliers_id'] as $key => $supplier)
			{
				if($supplier != $last_preferred_supplier && $_SESSION['transaction_add_supplier_info'][$supplier]['supplier_preferred'] == 'Y')
				{
					$_SESSION['transaction_add_supplier_info'][$supplier]['supplier_preferred'] = 'N';
				}
			}
		}

		// Update all suppliers
		foreach($_SESSION['suppliers_id'] as $key => $supplier)
		{
			// so add record
			$this->db->from('items_suppliers');
			$this->db->where('item_id', $_SESSION['sel_item_id']);
			$this->db->where('supplier_id', $supplier);
			$this->db->update('items_suppliers', $_SESSION['transaction_add_supplier_info'][$supplier]);
		}

		// Update session cost_price from preferred supplier for Article tab display
		$item_supplier_info = $this->Item->item_supplier_get_cost($_SESSION['transaction_info']->item_id);
		if ($item_supplier_info != NULL)
		{
			$_SESSION['preferred_supplier_cost_price'] = $item_supplier_info->supplier_cost_price;
		}

		// set up success message
		$_SESSION['error_code']	=	'01840';

		//update kits
		$array = $this->Item_kit->get_item_kits_item($_SESSION['transaction_info']->item_id);

		foreach($array as $key => $value)
		{
			$cost_ = $this->Item_kit->select_all_with_cost_price($value['item_kit_id']);
            $inputs = array('item_kit_id' => $value['item_kit_id'], 'supplier_cost_price' => $cost_[0]['cost_kit']);
			$this->Item_kit->update_all_with_cost_price($inputs);
		}

		// re-route - use AJAX tab reload to stay on supplier tab
		$this->ajax_tab_suppliers();
	}

	// delete supplier
	function item_supplier_delete($item_id, $supplier_id)
	{
		$this->db->select();
		$this->db->from('items_suppliers');
		$this->db->where('item_id', $item_id);
		$this->db->where('supplier_id', $supplier_id);
		$report_data = $this->db->get()->result_array();
		if($report_data[0]['supplier_preferred'] == "N")
		{
			if ($this->Item->supplier_delete($item_id, $supplier_id))
			{
				$_SESSION['error_code']		=	'01870';
			}
			else
			{
				$_SESSION['error_code']		=	'01880';
			}
		}
		else
		{
			// set message - cannot delete preferred supplier
			$_SESSION['error_code']		=	'01830';
		}

		// re-route - use AJAX tab reload to stay on supplier tab
		$this->ajax_tab_suppliers();
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

		// If source has reorder_indicator = 'Y' and target has 'N', set target to 'Y'
		$new_reorder_indicator = $_SESSION['transaction_to']->reorder_indicator ?? 'N';
		if (isset($_SESSION['transaction_from']->reorder_indicator) && $_SESSION['transaction_from']->reorder_indicator == 'Y' &&
		    isset($_SESSION['transaction_to']->reorder_indicator) && $_SESSION['transaction_to']->reorder_indicator == 'N') {
			$new_reorder_indicator = 'Y';
		}

		// update to item with merged data
		$_SESSION['transaction_info']							=	new stdClass();
		$_SESSION['transaction_info']->item_id					=	$_SESSION['transaction_to']->item_id;
		$_SESSION['transaction_info']->quantity					=	$_SESSION['transaction_from']->quantity + $_SESSION['transaction_to']->quantity;
		$_SESSION['transaction_info']->reorder_pack_size		=	$_SESSION['transaction_to']->reorder_pack_size;
		$_SESSION['transaction_info']->reorder_indicator		=	$new_reorder_indicator;
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

		// Transfer suppliers from FROM to TO (if not already present)
		$from_suppliers = $this->Item->get_suppliers_by_item_id($_SESSION['transaction_from']->item_id);
		$to_suppliers = $this->Item->get_suppliers_by_item_id($_SESSION['transaction_to']->item_id);

		// Build array of supplier IDs already on TO item
		$to_supplier_ids = array();
		foreach ($to_suppliers as $sup) {
			$to_supplier_ids[] = $sup->supplier_id;
		}

		// Transfer any supplier from FROM that isn't already on TO
		foreach ($from_suppliers as $from_sup) {
			if (!in_array($from_sup->supplier_id, $to_supplier_ids)) {
				// Transfer this supplier to TO item with preferred = 'N'
				$this->Item->transfer_supplier_to_item($_SESSION['transaction_from']->item_id, $_SESSION['transaction_to']->item_id, $from_sup->supplier_id);
			}
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
		//verify offer_value
		/*if(($_SESSION['transaction_info']->offer_value<0) || !is_numeric($_SESSION['transaction_info']->offer_value))
        {
			//if offer_value est négatif ou n'est pas numérique
			$_SESSION['error_code']										=	'07330';
			redirect("items");
		}//*/

		// verify name entered if add
		if 	(($_SESSION['new'] ?? 0) == 1 AND $_SESSION['transaction_info']->item_number == NULL)
		{
			// set message
			$_SESSION['error_code']										=	'01400';
			redirect("items");
		}

		// verify item_number not duplicate if add
		if 	(($_SESSION['new'] ?? 0) == 1)
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
		//	OR 	empty($_SESSION['transaction_tax_info']->tax_name_1)
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
		if 	((	empty($_SESSION['transaction_add_supplier_info']->supplier_id)
			OR 	empty($_SESSION['transaction_add_supplier_info']->supplier_preferred)
			OR	empty($_SESSION['transaction_add_supplier_info']->supplier_cost_price)
			OR 	empty($_SESSION['transaction_add_supplier_info']->supplier_reorder_policy)
			&&  ($_SESSION['add_supplier_info_statut_offre']->statut_offre == 'N')))
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

		// if supplier preferred indicator is Y, reset existing preferred supplier to N
		if ($_SESSION['transaction_add_supplier_info']->supplier_preferred == 'Y')
		{
			$count								=	$this->Item->item_supplier_preferred_unique();
			if ($count > 0)
			{
				// Reset existing preferred supplier(s) to 'N' instead of showing error
				$this->Item->item_supplier_reset_preferred($_SESSION['transaction_add_supplier_info']->item_id);
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
		unset($_SESSION['origin']);
		redirect("items");
	}

	// Toggle pour basculer entre articles actifs et inactifs
	function toggle_deleted()
	{
		if (($_SESSION['undel'] ?? 0) == 1) {
			// Actuellement on voit les inactifs, revenir aux actifs
			unset($_SESSION['undel']);
			unset($_SESSION['reactivation']);
		} else {
			// Actuellement on voit les actifs, montrer les inactifs
			$_SESSION['undel'] = 1;
			$_SESSION['reactivation'] = 1;
			// Désactiver le filtre nouveautés (mutuellement exclusif)
			unset($_SESSION['filtre_nouveautes']);
		}
		unset($_SESSION['origin']);
		redirect("items");
	}

	// AJAX: Toggle active/inactive status for an item
	function ajax_toggle_status($item_id = null)
	{
		header('Content-Type: application/json');

		if (!$item_id) {
			echo json_encode(array('success' => false, 'message' => 'ID article manquant'));
			return;
		}

		// Get item info
		$item = $this->Item->get_info($item_id);
		if (!$item || !isset($item->item_id)) {
			echo json_encode(array('success' => false, 'message' => 'Article non trouvé'));
			return;
		}

		$current_deleted = $item->deleted;
		$quantity = (float)($item->quantity ?? 0);

		// If trying to deactivate, check quantity
		if ($current_deleted == 0 && $quantity > 0) {
			echo json_encode(array('success' => false, 'message' => 'Stock non vide (' . $quantity . ')'));
			return;
		}

		// Toggle the deleted status
		$new_deleted = ($current_deleted == 0) ? 1 : 0;
		$this->db->where('item_id', $item_id);
		$this->db->update('items', array('deleted' => $new_deleted));

		// Add inventory record
		$trans_comment = ($new_deleted == 1) ? $this->lang->line('items_deleted') : $this->lang->line('items_undeleted');
		$employee_info = $this->Employee->get_logged_in_employee_info();
		$user_id = ($employee_info && isset($employee_info->person_id)) ? $employee_info->person_id : 0;

		$inv_data = array(
			'trans_date'        => date('Y-m-d H:i:s'),
			'trans_items'       => $item_id,
			'trans_user'        => $user_id,
			'trans_comment'     => $trans_comment,
			'trans_stock_before'=> $quantity,
			'trans_inventory'   => 0,
			'trans_stock_after' => $quantity,
			'branch_code'       => $this->config->item('branch_code')
		);
		$this->Inventory->insert($inv_data);

		echo json_encode(array(
			'success' => true,
			'new_status' => $new_deleted,
			'message' => ($new_deleted == 1) ? 'Article désactivé' : 'Article activé'
		));
	}

	// AJAX: Save image URL for an item
	function ajax_save_image_url()
	{
		header('Content-Type: application/json');

		$item_id = $this->input->post('item_id');
		$image_url = $this->input->post('image_url');

		if (!$item_id || $item_id == '' || $item_id == '-1') {
			echo json_encode(array('success' => false, 'error' => 'ID article invalide'));
			return;
		}

		// Vérifier que l'article existe
		$item = $this->Item->get_info($item_id);
		if (empty($item) || !isset($item->item_id)) {
			echo json_encode(array('success' => false, 'error' => 'Article non trouvé'));
			return;
		}

		// Mettre à jour l'URL de l'image
		$this->db->where('item_id', $item_id);
		$this->db->update('items', array('image_file_name' => $image_url));

		if ($this->db->affected_rows() >= 0) {
			echo json_encode(array('success' => true, 'message' => 'Image mise à jour'));
		} else {
			echo json_encode(array('success' => false, 'error' => 'Erreur lors de la mise à jour'));
		}
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
		/*
		if (substr($_SESSION['transaction_info']->clone_to_id, 0, 2) != "SO")
		{
			// set message
			$_SESSION['error_code']		=	'00830';
			redirect("items");
		}
		*/
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

	function codebarre($tout)
	{
		list($item_id, $line, $direction)=explode(":", $tout);
	//	$_SESSION['line_focus']=$line;
	    $_SESSION['autofocus_avec_item_id_manage'] = $item_id;
		$_SESSION['transaction_info']->item_id = $item_id;
		$this->view_suppliers();
	}

	function exportation()
	{
        // Create the CSV...
		$filename_csv = "export_" . date("dmY_His") . ".csv";
		$chemin = "/home/wrightetmathon/export/";
		

		// test upload dir exists
		if (!file_exists($chemin))
		{
			// make it if not found
			mkdir($chemin);
		}
		exec('sudo chmod -R 777 ' . $chemin);
		
		$csv_data_file = $chemin.$filename_csv;
        $compteur = 0;

		file_put_contents($csv_data_file, "CODE ARTICLE" . ",", FILE_APPEND);
		file_put_contents($csv_data_file, "DESCRIPTION" . ",", FILE_APPEND);
		file_put_contents($csv_data_file, "QUANTITE" . ",", FILE_APPEND);
		file_put_contents($csv_data_file, "\r\n", FILE_APPEND);
		// read the data_item and load output file
		foreach($_SESSION['data_items'] as $line=>$item)
		{
			$compteur += 1;
			$csv_quantity = round($item->quantity,0);    //number_format($item->quantity,0);
			file_put_contents($csv_data_file, $item->item_number . ",", FILE_APPEND);
			file_put_contents($csv_data_file, $item->name . ",", FILE_APPEND);
			file_put_contents($csv_data_file, $csv_quantity . ",", FILE_APPEND);
			file_put_contents($csv_data_file, "\r\n", FILE_APPEND);
		}

		//give all right
		exec('sudo chmod 777 ' . $csv_data_file);

		switch($compteur)
		{
			case 0:
				$_SESSION['substitution_parms'][0] = "0 article";
			break;
			
			case 1:
				$_SESSION['substitution_parms'][0] = "1 article";
			break;
			
			default:
				$_SESSION['substitution_parms'][0] = $compteur . " articles";
			break;
		}
        $_SESSION['substitution_parms'][1] = "dans le dossier " . $chemin;
        $_SESSION['error_code'] = '07470';

		//redirect("items");
		redirect("reports/inventory_rolling");
	}


	function verification_for_importation()
	{
		$quantity_remove = $this->input->post('qauntity_remove');

        $name = $_FILES['userfile']['name'];
        $type = $_FILES['userfile']['type'];
        $tmp_name = $_FILES['userfile']['tmp_name'];
        $size = $_FILES['userfile']['size'];
        $error = $_FILES['userfile']['error'];

		// initialise
	    $dir															=	"/var/www/html/wrightetmathon_uploads/";
		$upload_file_name												=	$name; //"BASE_ARTICLES_MANUAL.xls";

		// test upload dir exists
		if (!file_exists($dir))
		{
			// make it if not found
			mkdir($dir);
		}
		
		// test if upload dir is not empty
		if ((new \FilesystemIterator($dir))->valid() == TRUE)
		{
			// if it is not empty, delete any files in it
			if (!array_map('unlink', glob($dir."*")))
			{
				$_SESSION['error_code']									=	'05490';
				redirect($_SESSION['controller_name']);
			}
		}

		// config upload library for first upload
		$config = array	(
						'upload_path' 	=>	$dir,
						'allowed_types' =>	'*',
						'overwrite' 	=>	TRUE
						);
		$this->upload->initialize($config);
		
		$_SESSION['config_1'] = $config;

		// test file selected
		if(!$this->upload->do_upload('userfile'))
		{
			// set message depends on what is in the error string
			if (strpos($this->upload->display_errors(), 'select'))
			{
				$_SESSION['error_code']									=	'05470';
			//	redirect($_SESSION['controller_name']);
			}
		}
		
		// check file extension
		$data['upload_data']											=	array();
		$data['upload_data']											=	$this->upload->data();
        

		if(!in_array($data['upload_data']['file_ext'], array('.csv', '.xls', '.xlsx', '.ods')))
		{
			$_SESSION['error_code']										=	'05480';
			redirect($_SESSION['controller_name']);
		}

		// clear upload path
		//array_map('unlink', glob($dir."*"));
		
		// If here then I am reasonably sure I have a valid and authentic file so initialise the upload library to
		// convert file name to standard
		$config = array	(
						'file_name'		=>	$upload_file_name,
						'upload_path' 	=>	$dir,
						'allowed_types' =>	'*',
						'overwrite' 	=>	TRUE
						);
		$this->upload->initialize($config);
		
		// now upload the file again
		if(!$this->upload->do_upload('userfile'))
		{
			// set message depends on what is in the error string
			if (strpos($this->upload->display_errors(), 'select'))
			{
				$_SESSION['error_code']									=	'05470';
				redirect($_SESSION['controller_name']);
			}
		}
		
		// set session parms
		$_SESSION['upload_path']										=	$dir;
		$_SESSION['upload_file']										=	$upload_file_name;
		$_SESSION['upload_quantity']                                    =   $quantity_remove;
		$_SESSION['import_mode']										=	'manual';

		// begin verify document
		$this->load->library('../controllers/updates');
        $this->updates->manage_items_stock_inventory_xls_verify();


		$_SESSION['show_dialog'] = 9;
		redirect("receivings");
	}


	function importation()
	{
		// initialise
	    $dir				= $_SESSION['upload_path']; //"/var/www/html/wrightetmathon_uploads/";
		$upload_file_name	= $_SESSION['upload_file']; //$name; //"BASE_ARTICLES_MANUAL.xls";

		$this->load->library('../controllers/updates');
		
		// now do update
	    $this->updates->manage_items_stock_inventory_xls();
	    return;
	}
	
	function competitive_intelligence($inputs)
    {
		$item_id = $inputs;
		$data_item = $this->Item->get_info($item_id);
		$data_item_supllier = $this->Item->get_supplier_id($item_id);
        $_SESSION['origin'] = 'RR';

		$_SESSION['transaction_info_competitive_intelligence'] = new stdClass();
		
		$_SESSION['transaction_info_competitive_intelligence'] = $data_item;
		
		$_SESSION['transaction_info_competitive_intelligence']->price_pos = $data_item_supllier[0]['supplier_cost_price'];
		
		$_SESSION['show_dialog'] = 10;
		redirect("receivings");
	}

	function competitive_intelligence_send($inputs)
    {
		$items_id = $inputs;
		
		$comment = $this->input->post('competitive_intelligence_price_concurrent');

		$message = 'POS :';
		$message .= '<br>';
		$message .= 'INFO :'.$_SESSION['G']->login_employee_info->first_name;
		$message .= '<table>';
		$message .= '<tr>';
		
		$message .= '<td>';
		$message .= 'Code Article :';
		$message .= '</td>';
		$message .= '<td>';
		$message .= $_SESSION['transaction_info_competitive_intelligence']->item_number;
		$message .= '</td>';

		$message .= '</tr>';
		$message .= '<tr>';

		$message .= '<td>';
		$message .= $this->lang->line('items_libelle').' :';
		$message .= '</td>';
		$message .= '<td>';
		$message .= $_SESSION['transaction_info_competitive_intelligence']->name;
		$message .= '</td>';

		$message .= '</tr>';
		$message .= '<tr>';

		$message .= '<td>';
		$message .= $this->lang->line('common_competitive_intelligence_price_pos');
		$message .= '</td>';
		$message .= '<td>';
		$message .= $_SESSION['transaction_info_competitive_intelligence']->price_pos;
		$message .= '</td>';

		$message .= '</tr>';
		$message .= '</table>';
		$message .= '<br>';
		
		$message .= 'Commentaire sur la concurrence: ';
		$message .= '<br>';
		$message .= $comment;
		
		// Send to supplier
		$mail_config = array(
			'protocol'							=>	'smtp',
			'smtp_host' 						=>	'ssl://mail.sonrisa-smile.com',
			'smtp_port' 						=>	'465',
			'smtp_user' 						=> 	$this->config->item('POemail'),
			'smtp_pass' 						=> 	$this->config->item('POemailpwd'),
			'mailtype'  						=>	'html',
			'starttls'  						=>	FALSE,
			'wordwrap'							=>	TRUE,
			'smtp_timeout'						=>	60,
			'newline'   						=>	"\r\n"
			);
		
        $this->load->library('email', $mail_config);
        
		$this->email->from($this->config->item('email'), $this->config->item('company'));
		$this->email->to('david@sonrisa-smile.com');
//		$this->email->to('guillaume@yesstore.fr');

		$this->email->cc('guillaume@yesstore.fr');
        
		$this->email->subject('test: Veille Concurentielle');
		$this->email->message($message);

		$this->email->send();
		
		$_SESSION['show_dialog'] = 0;
		redirect("receivings");
	}

	

}
?>
