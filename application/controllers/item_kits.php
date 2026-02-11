<?php
class Item_kits extends CI_Controller
{
	function index()
	{
		// set module id
		$_SESSION['module_id']											=	"16";
		
		// manage session
		$data 															=	array();
		$_SESSION['controller_name']									=	strtolower(get_class($this));
		unset($_SESSION['report_controller']);
		
		// set list title if undelete
		switch ($_SESSION['undel'] ?? 0)
		{
			case	1:
					$data['title']										=	$this->lang->line('common_undelete');
			break;
				
			default:
					$data['title']										=	'';
			break;
		}
		
		// set up the pagination
		$config															=	$this->Common_routines->set_up_pagination();
		$config['base_url'] 											= 	site_url('/customers/index');
		$config['total_rows']											=	$this->Item_kit->count_all();
		$this															->	pagination->initialize($config);
		
		// setup output data
		$data['links']													=	$this->pagination->create_links();	
		$data['form_width']												=	$this->get_form_width();
		$create_headers													=	1;
//		$data['manage_table']=get_item_kits_manage_table( $this->Item_kit->get_all( $config['per_page'], $this->uri->segment( $config['uri_segment'] ) ), $this );
	//	$data['manage_table']=get_item_kits_manage_table( $this->Item_kit->get_all( ), $this );
		$data['manage_table']=get_item_kits_manage_table( $this->Item_kit->get_all_with_cost_price( ), $this );
//		$this->calcul_cost_price_kits();
		// show data
		$this						->	load->view('item_kits/manage', $data);
	}

	function search()
	{
		$search=$this->input->post('search');
		//$data_rows=get_item_kits_manage_table_data_rows($this->Item_kit->search($search),$this);
		//$data_rows=get_item_kits_manage_table($this->Item_kit->search($search),$this);
		$data_rows=get_item_kits_manage_table($this->Item_kit->search_with_cost_price($search),$this);
		$data['manage_table'] = $data_rows;
		//$this->load->view('item_kits/manage', $data['manage_table']);
		//echo $data_rows;
		$this						->	load->view('item_kits/manage', $data);
        //$this->index();
	}

    //search item à partir du code article SO.....
	function search_item()
	{
		unset($_SESSION['item_kit_item']);
        unset($_SESSION['transaction_info_item_kit_item']);

		$search=$this->input->post('search_item_kit');
		$_SESSION['item_kit_item']['item_number'] = $search;
		//$_SESSION['transaction_info_item_kit_item'] = $this->Item->get_info_with_item_number($search);
		$_SESSION['transaction_info_item_kit_item'] = $this->Item->get_info_with_item_id($search);
		$_SESSION['item_kit_item']['name'] = $_SESSION['transaction_info_item_kit_item'][0]['name'];
		$_SESSION['item_kit_item']['item_number'] = $_SESSION['transaction_info_item_kit_item'][0]['item_number'];
		redirect("item_kits");
	}
	
	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Item_kit->get_search_suggestions($this->input->post('q'),$this->input->post('limit'));
		echo implode("\n",$suggestions);
	}

	function get_row()
	{
		$item_kit_id = $this->input->post('row_id');
		$data_row=get_item_kit_data_row($this->Item_kit->get_info($item_kit_id),$this);
		echo $data_row;
	}

	function view($item_kit_id)
	{
		// Set origin for redirect on exit
		$_SESSION['origin'] = 'IK';

		unset($_SESSION['new']);
		unset($_SESSION['transaction_items_info_item_id']);
		unset($_SESSION['transaction_items_info_items']);
		unset($_SESSION['transaction_item_kits_in_items']); //= new stdClass();
		unset($_SESSION['transaction_info_pricelist']);    // = new stdClass();



		// intialise
		$_SESSION['transaction_info']	= new stdClass();
		$_SESSION['category_pick_list']	= new stdClass();
		$_SESSION['transaction_items_info'] = new stdClass();
		$_SESSION['transaction_item_kits_in_items'] = new stdClass();

		unset($_SESSION['transaction_info_pricelist']);    // = new stdClass();

		$_SESSION['transaction_id']		= $item_kit_id;
		$_SESSION['show_dialog']		= 1;
		
		// load categories pick list
		$category_pick_list	    = array();
		$categories			    = array();
		$categories			    = $this->Category->get_all();
		foreach($categories as $row)
		{
			$category_pick_list[$row['category_id']] =	$row['category_name'];
		}
		
		// load pick list output data
		$_SESSION['category_pick_list'] = $category_pick_list;


		// set data
		switch ($item_kit_id) 
		{
			// create new
			case	-1:
					$_SESSION['$title'] = $this->lang->line($_SESSION['controller_name'].'_new');

					$_SESSION['new'] = 1;
			break;
			
			// update existing
			default:
					$_SESSION['transaction_info'] = $this->Item_kit->get_info($item_kit_id);

					$_SESSION['transaction_items_info'] = $this->Item_kit->get_item_kit_items($item_kit_id);
					
					foreach($_SESSION['transaction_items_info'] as $key => $line)
					{
						$_SESSION['transaction_items_info_item_id'][] = $line['item_id'];
						$_SESSION['transaction_items_info_items'][] = $this->Item->get_info($line['item_id']);
					}
			
					$_SESSION['transaction_info_pricelist'] = $this->Item_kit->get_item_kit_id_pricelist($item_kit_id);
					
                    $_SESSION['transaction_item_kits_in_items'] = $this->Item->get_info($item_kit_id);

					$_SESSION['selected_category'] = $_SESSION['transaction_item_kits_in_items']->category_id;
					
					switch ($_SESSION['undel'] ?? 0)
					{
						case	1:
								$_SESSION['$title'] = $this->lang->line('common_undelete').' => '.$_SESSION['transaction_info']->description;
						break;
						
						default:
								$_SESSION['$title'] = $this->lang->line('common_edit').' => '.$_SESSION['transaction_info']->description;
						break;	
					}
					unset($_SESSION['new']);
			break;
		}

		//show
		$this->load->view("item_kits/manage");
	}
	
	function save_item_kit_into_items($item_kit_data)
	{
		$_SESSION['new'] = 1;
		$_SESSION['transaction_info'] = new stdClass();
		$_SESSION['transaction_info']->item_id = NULL;
		$_SESSION['transaction_info']->name = $item_kit_data['description'];    //$item_kit_data['name'];
		$_SESSION['transaction_info']->description = $item_kit_data['description'];
		$_SESSION['transaction_info']->volume = 0;
		$_SESSION['transaction_info']->nicotine = 0;
		$_SESSION['transaction_info']->category_id = $item_kit_data['category_id'];
		$category = $this->Category->get_info($item_kit_data['category_id']);

		$_SESSION['transaction_info']->category = $category->category_name;
		$_SESSION['transaction_info']->item_number = $item_kit_data['name'];
		$_SESSION['transaction_info']->DynamicKit = 'Y';
		$_SESSION['transaction_info']->dluo_indicator = '';
		$_SESSION['transaction_info']->giftcard_indicator = '';
		$_SESSION['transaction_info']->offer_indicator = '';
		$_SESSION['transaction_info']->kit_reference = '';
		$_SESSION['transaction_info']->kit = 2;    //valeur de transition
		$_SESSION['transaction_info']->barcode = $item_kit_data['barcode'];
		$_SESSION['transaction_info']->export_to_franchise = '';
		$_SESSION['transaction_info']->export_to_integrated = '';
		$_SESSION['transaction_info']->export_to_other = '';
		$_SESSION['transaction_info']->image_file_name = '';
		$_SESSION['transaction_info']->branch_code = $this->config->item('branch_code');
		$_SESSION['transaction_info']->offer_value = 0.0;
		$_SESSION['transaction_info']->cost_price = 0.0;
		$_SESSION['transaction_info']->unit_price = 0.0;
		$_SESSION['transaction_info']->vs_nom = '';
		$_SESSION['transaction_info']->vs_marque = '';
		$_SESSION['transaction_info']->vs_category = '';
		$_SESSION['transaction_info']->vs_param_1 = '';
		$_SESSION['transaction_info']->vs_param_2 = '';
		$_SESSION['transaction_info']->vs_param_3 = '';
		$this->Item->save();
		$item_id = $this->Item_kit->get_item_id_kit();
		$this->Item_kit->update_items_kit();
        return $item_id;
	}

	function save_item_kit($item_kit_id=-1)
	{
    	$item_kit_data = array(
    		'name'			=>	$this->input->post('name'),
    		'description'	=>	$this->input->post('description'),
    		'unit_price_with_tax' => $this->input->post('unit_price_with_tax'),
			'category_id' => $this->input->post('category_id'),
			'barcode' => $this->input->post('code_bar'),
    		'branch_code'	=>	$this->config->item('branch_code')
			);

		$this->verify($item_kit_data, $item_kit_id);

		$category = $this->Category->get_info($item_kit_data['category_id']);

		//insert or update items 
		if($item_kit_id == -1)
		{   		
			$return_item_id = $this->save_item_kit_into_items($item_kit_data);
		}
		if($item_kit_id != -1)    //update item_kits
		{
			$this->Item_kit->update_insert_item_kits_into_ospos_items(array('item_id' => $item_kit_id, 'item_number' => $item_kit_data['name'], 'name' => $item_kit_data['description'], 'description' => $item_kit_data['description'], 'category_id' => $item_kit_data['category_id'], 'barcode' => $item_kit_data['barcode'], 'branch_code' => $item_kit_data['branch_code']));
		}

        if(($_SESSION['new'] ?? 0) == 1)
		{
			$id = intval($return_item_id[0]['item_id']);
		}

		if(($_SESSION['new'] ?? 0) != 1)
		{
            $id = intval($item_kit_id);
		}

		$item_kit_info = array(
			'item_kit_id'   =>  $id,
			'name'			=>	$this->input->post('name'),
			'description'	=>	$this->input->post('description'),
			'code_bar'      => $this->input->post('code_bar'),
		);
    
    	//save info into ospos_item_kit
    	$return = $this->Item_kit->save_item_kit($item_kit_info);

    	$item_kit_info_pricelist = array(
			'item_id' => $id,
			'pricelist_id' => 1,
			'unit_price' => $item_kit_data['unit_price_with_tax'] / ((100 + $this->config->item('default_tax_1_rate')) / 100),
    		'unit_price_with_tax' => $item_kit_data['unit_price_with_tax'],
    		'branch_code' => $this->config->item('branch_code')
		);
		
		$return_item_kit_info_pricelist = $this->Item_kit->get_item_kit_id_pricelist($id);
		if(isset($return_item_kit_info_pricelist[0]))
		{
			$new = 0;    //déjà existant
		}
		if(!isset(($return_item_kit_info_pricelist[0])))
		{
			$new = 1;    //nouveau
		}

		//update or insert pricelist kit
		$this->Item_kit->update_insert_items_pricelist($item_kit_info_pricelist, $new);

		//success message
		$_SESSION['error_code'] = '07410';
		//show
        $this->view(intval($id));
	}

	function view_add_item($item_kit_id = -1)
	{
		if($item_kit_id != 1)
		{
			unset($_SESSION['item_kit_item']['item_number']);
			unset($_SESSION['item_kit_item']['name']);
			unset($_SESSION['item_kit_item']['item_number']);
            $_SESSION['show_dialog'] = 2;
		}
		redirect("item_kits");
	}

    function add_item($item_kit_id = -1)
    {
    	if($item_kit_id != 1)
    	{
			//
			$_SESSION['transaction_info_item_kit_item'] = $this->Item->get_info_with_item_number($_SESSION['item_kit_item']['item_number']);
			
			$item_kit_add_item = array(
				"item_kit_id" => $item_kit_id,
                "item_id" => $_SESSION['transaction_info_item_kit_item'][0]['item_id']
			);
		    
			$add_new_item = $this->Item_kit->get_item_kit_item($item_kit_add_item);
			$item_kit_add_item['quantity'] = $this->input->post('quantity'); 
			if(isset($add_new_item[0]))
			{
				$new = 0;    //article déjà existant dans le kit
				$item_kit_add_item['quantity'] += $add_new_item[0]['quantity'];
			}
			if(!isset(($add_new_item[0])))
            {
				$new = 1;    //nouvel article dans le kit
			}
			
			
            $item_kit_add_item['branch_code'] = $this->config->item('branch_code'); 
            

			if(intval($item_kit_add_item['quantity']) > 0)
			{
				$this->Item_kit->update_insert_item_kit_item($item_kit_add_item, $new);
			}
			
			$_SESSION['transaction_info'] = $this->Item_kit->get_info($item_kit_id);

			$_SESSION['transaction_items_info'] = $this->Item_kit->get_item_kit_items($item_kit_id);
			
			
			foreach($_SESSION['transaction_items_info'] as $key => $line)
			{
				$_SESSION['transaction_items_info_item_id'][] = $line['item_id'];
				$_SESSION['transaction_items_info_items'][] = $this->Item->get_info($line['item_id']);
				
			}
		}
				
		//success message
		$_SESSION['error_code'] = '07410';
		//show
        $this->view(intval($item_kit_id));
    }

	function save($item_kit_id=-1)
	{
		$item_kit_data = array	(
								'name'			=>	$this->input->post('name'),
								'description'	=>	$this->input->post('description'),
								'unit_price_with_tax' => $this->post('unit_price_with_tax'),
								'branch_code'	=>	$this->config->item('branch_code')
								);

		if($this->Item_kit->save($item_kit_data, $item_kit_id))
		{
			//New item kit
			if($item_kit_id==-1)
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('item_kits_successful_adding').' '.
				$item_kit_data['name'],'item_kit_id'=>$item_kit_data['item_kit_id']));
				$item_kit_id = $item_kit_data['item_kit_id'];
			}
			else //previous item
			{
				echo json_encode(array('success'=>true,'message'=>$this->lang->line('item_kits_successful_updating').' '.
				$item_kit_data['name'],'item_kit_id'=>$item_kit_id));
			}
			if ($this->input->post('item_kit_item'))
			{
				$item_kit_items = array();
				foreach($this->input->post('item_kit_item') as $item_id => $quantity)
				{
					$item_kit_items[] = array	(
												'item_id' 		=>	$item_id,
												'quantity' 		=>	$quantity,
												'branch_code'	=>	$this->config->item('branch_code')
												);
				}	
				$this->Item_kit_items->save($item_kit_items, $item_kit_id);
			}
		}
		else//failure
		{
			echo json_encode(array('success'=>false,'message'=>$this->lang->line('item_kits_error_adding_updating').' '.
			$item_kit_data['name'],'item_kit_id'=>-1));
		}
		redirect("item_kits");
	}
	

	//delete un item dans un item_kit
	function delete($item_kit_id = -1, $item_id = -1, $line = 0)
    {
		if(($item_kit_id != -1) && ($item_id != -1))// && ($line != 0))
		{
			$this->Item_kit->delete_item(
				array(
					"item_kit_id" => $item_kit_id,
					"item_id" => $item_id
				)
			);
			unset($_SESSION['transaction_items_info'][$line]);
            unset($_SESSION['transaction_items_info_item_id'][$line]);
		}
		redirect("item_kits");
	}
	
	function generate_barcodes($item_kit_ids)
	{
		$result = array();

		$item_kit_ids = explode(':', $item_kit_ids);
		foreach ($item_kit_ids as $item_kid_id)
		{
			$item_kit_info = $this->Item_kit->get_info($item_kid_id);

			$result[] = array('name' =>$item_kit_info->name, 'id'=> 'KIT '.$item_kid_id);
		}

		$data['items'] = $result;
		$this->load->view("barcode_sheet", $data);
	}
	
	
	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 360;
	}

	function verify($data, $new)
	{
		if($new == -1)
		{
            //$doublon = $this->Item_kit->get_info_with_array(array('name' => $data['name'], 'barcode' => $data['barcode']));
			//$data_name = $this->Item_kit->get_info_with_array(array('name' => $data['name']));
			//if(isset($data_name[0]))
			//{
			//	$_SESSION['error_code'] = '07460'; //La combinaison du code kit et du code 
			//	redirect('item_kits');
			//}
			$data_barcode = $this->Item_kit->get_info_with_array(array('barcode' => $data['barcode']));
			if(isset($data_barcode[0]))
			{
				$_SESSION['substitution_parms'][0] = $data_barcode[0]['name'];
				$_SESSION['error_code'] = '07460'; //Le code bar est déjà attribué au produit:  
				redirect('item_kits');
			}
			if($data['barcode'] != '')
			{
				$data_barcode_items = $this->Item->get_info_with_array(array('barcode' => $data['barcode']));
				if(isset($data_barcode_items[0]))
				{

				    $_SESSION['substitution_parms'][0] = $data_barcode_items[0]['name'];
				    $_SESSION['error_code'] = '07460';
				}
			}
		}
		if($new != -1)
		{
			$doublon = $this->Item_kit->get_info_with_array(array('barcode' => $data['barcode']));
			
			foreach($doublon as $value)
			{
				if($new != $value['item_kit_id'])
				{
					$_SESSION['substitution_parms'][0] = $value['name'];
					$_SESSION['error_code'] = '07460'; //Le code bar est déjà attribué au produit: 
					redirect('item_kits');
				}
			}
		}
	}

	function desactive($tout)
	{
		//synchronisation deleted in ospos_items and ospos_item_kits
		//if(! $this->Item_kit->synchronise_deleted())
        //{
        //	$this->desactive($tout);
		//}
		
		list($id, $line, $direction)=explode(":", $tout);
		
		$data_items = $this->Item->get_info($id);
	//	$request['quantity'] = $data_items->quantity;
		$request_pour_deleted['deleted'] = $data_items->deleted;

		//if(conditition)
		//{
		//	// set error message
		//	$_SESSION['error_code']			=	'01480';
		//    redirect("item_kits");
		//}

		switch($request_pour_deleted['deleted'])
		{
			case 0:
				$data_deleted['deleted'] = 1;
				$trans_comment = $this->lang->line('items_deleted');
			break;

			case 1:
				$data_deleted['deleted'] = 0;
				$trans_comment = $this->lang->line('items_undeleted');
			break;

			default:
			break;
		}
		$this->Item_kit->desactive($id, $data_deleted);

//		$this->load->library('../controllers/receivings');
//        $this->Receivings->desactive($id . ':-1:' . "item_kits");
			// add inventory record
			$inv_data = array	(
				'trans_date'		=>	date('Y-m-d H:i:s'),
				'trans_items'		=>	$id,
								  'trans_user'		=>	$this->Employee->get_logged_in_employee_info()->person_id,
				                  'trans_comment'		=>	$trans_comment,
								  'trans_stock_before'=>	$data_items->quantity,
								  'trans_inventory'	=>	0,
								  'trans_stock_after'=>	$data_items->quantity,
								  'branch_code'		=>	$this->config->item('branch_code')
								);
        $this->Inventory->insert($inv_data);
        redirect("item_kits");
	}
}

?>
