<?php
class Item_kit extends CI_Model
{
	/*
	Determines if a given item_id is an item kit
	*/
	function	exists				($item_kit_id)
	{
		$this						->	db->from('item_kits');
		$this						->	db->where('item_kit_id',$item_kit_id);
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();

		return 						($query->num_rows()==1);
	}

	/*
	Returns all the item kits
	*/
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('item_kits');
		$this						->	db->join('items_pricelists', 'items_pricelists.item_id=item_kits.item_kit_id');
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("name", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		return 						$this->db->get();
	}
//	SELECT `ospos_item_kit_items`.`item_kit_id`, `ospos_item_kit_items`.`item_id`, `ospos_item_kit_items`.`quantity`, `ospos_items_suppliers`.`supplier_cost_price`, (CAST(`ospos_item_kit_items`.`quantity` as DECIMAL(7,2)) * CAST(`ospos_items_suppliers`.`supplier_cost_price` as DECIMAL(7,2))) as cost_kit FROM `ospos_item_kit_items`, `ospos_items_suppliers` WHERE `ospos_item_kit_items`.`item_id` = `ospos_items_suppliers`.`item_id`
	

	function get_all_with_cost_price()
	{
		$sql = '
            SELECT
                `ospos_item_kit_items`.`item_kit_id`,
                `ospos_item_kits`.`name`,
                `ospos_item_kits`.`description`,
                `ospos_item_kits`.`item_kit_id`,
                SUM(CAST(`ospos_item_kit_items`.`quantity` as DECIMAL(7,2)) * CAST(`ospos_items_suppliers`.`supplier_cost_price` as DECIMAL(7,2))) as cost_kit,
                `ospos_items_pricelists`.`unit_price_with_tax`,
				`ospos_item_kits`.`barcode`,
				`ospos_item_kits`.`deleted`
            FROM
                `ospos_item_kits`,
                `ospos_item_kit_items`,
                `ospos_items_suppliers`,
                `ospos_items_pricelists`
            WHERE
				`ospos_item_kit_items`.`item_id` = `ospos_items_suppliers`.`item_id` AND
				`ospos_items_suppliers`.`supplier_preferred` = "Y" AND
                `ospos_item_kit_items`.`item_kit_id` = `ospos_items_pricelists`.`item_id`AND
                `ospos_item_kits`.`item_kit_id` = `ospos_item_kit_items`.`item_kit_id`
            GROUP BY 
            `ospos_item_kit_items`.`item_kit_id`
            ';
        $query = $this->db->query($sql);
    //    $result = $query->result_array();
	//    return $result;
	    return $query;
		/*
		$this->db->select('
		    item_kit_items.item_kit_id,
		    item_kits.name,
		    item_kits.description,
		    item_kits.item_kit_id,
		    SUM(
				CAST(`ospos_item_kit_items`.`quantity` as FLOAT(7,2)) * CAST(`ospos_items_suppliers`.`supplier_cost_price` as FLOAT(7,2))
				) as cost_kit,
		    items_pricelists.unit_price_with_tax,
		    item_kits.barcode
		');
		$this->db->from('    
		    item_kits,
		    item_kit_items,
		    items_suppliers,
		    items_pricelists
		');
		$this->db->where('item_kit_items.item_id = ospos_items_suppliers.item_id');
		$this->db->where('item_kit_items.item_kit_id = ospos_items_pricelists.item_id');
		$this->db->where('item_kits.item_kit_id = ospos_item_kit_items.item_kit_id');
		$this->db->group_by('item_kit_items.item_kit_id');
		$this->db->order_by("item_kits.name", "asc");
		return $this->db->get();//*/
	}

	//select supplier cost price for kits in $array
	function select_all_with_cost_price($item_kit_id)
	{
		$sql = '
            SELECT
                SUM(CAST(`ospos_item_kit_items`.`quantity` as DECIMAL(7,2)) * CAST(`ospos_items_suppliers`.`supplier_cost_price` as DECIMAL(7,2))) as cost_kit
            FROM
                `ospos_item_kits`,
                `ospos_item_kit_items`,
                `ospos_items_suppliers`,
                `ospos_items_pricelists`
            WHERE
				`ospos_item_kit_items`.`item_id` = `ospos_items_suppliers`.`item_id` AND
				`ospos_items_suppliers`.`supplier_preferred` = "Y" AND
                `ospos_item_kit_items`.`item_kit_id` = `ospos_items_pricelists`.`item_id`AND
				`ospos_item_kits`.`item_kit_id` = `ospos_item_kit_items`.`item_kit_id` AND
				`ospos_item_kits`.`item_kit_id` = ' . $item_kit_id . '
            GROUP BY 
			`ospos_item_kit_items`.`item_kit_id`
            ';
        $query = $this->db->query($sql);
        $result = $query->result_array();
	//    return $result;
	    return $result;
	}

	//update supplier cost price for kits in $array
	function update_all_with_cost_price($array)
	{
		$this->db->where('item_id', $array['item_kit_id']);
		$this->db->update('items_suppliers', array('supplier_cost_price' => $array['supplier_cost_price']));
	}


	function	count_all			()
	{
		$this						->	db->from('item_kits');
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}

	/*
	Gets information about a particular item kit
	*/
	function	get_info			($item_kit_id)
	{
		$this						->	db->from('item_kits');
		$this						->	db->where('item_kit_id',$item_kit_id);
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();

		if($query->num_rows()==1)
		{
			return 					$query->row();
		}
		else
		{
			//Get empty base parent object, as $item_kit_id is NOT an item kit
			$item_obj				=	new stdClass();

			//Get all the fields from items table
			$fields					=	$this->db->list_fields('item_kits');

			foreach ($fields as $field)
			{
				$item_obj->$field='';
			}

			return 					$item_obj;
		}
	}

	function get_info_with_array($inputs)
	{
		$this ->db->from('item_kits');
		
		foreach($inputs as $key => $input)
		{
			$this->db->where($key, $input);
		}

		$this ->db->where('item_kits.branch_code', $this->config->item('branch_code'));

		$data = $this->db->get()->result_array();
		return $data;
    }
	/*
	Gets information about multiple item kits
	*/
	function	get_multiple_info	($item_kit_ids)
	{
		$this						->	db->from('item_kits');
		$this						->	db->where_in('item_kit_id',$item_kit_ids);
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("name", "asc");
		return						$this->db->get();
	}

	/*
	Inserts or updates an item kit
	*/
	function	save				(&$item_kit_data, $item_kit_id=false)
	{
		if (!$item_kit_id or !$this->exists($item_kit_id))
		{
			if($this->db->insert('item_kits', $item_kit_data))
			{
				$item_kit_data['item_kit_id']=$this->db->insert_id();
				return true;
			}
			return false;
		}

		$this						->	db->where('item_kit_id', $item_kit_id);
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		return						$this->db->update('item_kits', $item_kit_data);
	}

	/*
	Deletes one item kit
	*/
	function	delete				($item_kit_id)
	{
		return 						$this->db->delete	('item_kits',	array	(
																				'item_kit_id' => $id,
																				'branch_code' => $this->config->item('branch_code')
																				)
														); 	
	}

	/*
	Deletes a list of item kits
	*/
	function	delete_list			($item_kit_ids)
	{
		$this						->	db->where_in('item_kit_id',$item_kit_ids);
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		return						$this->db->delete('item_kits');		
 	}


	function delete_item($array_id)
	{
		$this->db->where('item_kit_id', $array_id['item_kit_id']);
		$this->db->where('item_id', $array_id['item_id']);
		return $this->db->delete('item_kit_items');
	}

 	/*
	Get search suggestions to find kits
	*/
	function	get_search_suggestions($search,$limit=25)
	{
		$suggestions				=	array();

		$this						->	db->from('item_kits');
		$this						->	db->like('name', $search);
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("name", "asc");
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]			=	$row->name;
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions			=	array_slice($suggestions, 0,$limit);
		}
		return 						$suggestions;

	}
	
	function	get_item_kit_search_suggestions($search, $limit=25)
	{
		$suggestions				=	array();

		$this						->	db->from('item_kits');
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		$this						->	db->like('name', $search);
		$this						->	db->order_by("name", "asc");
		$by_name					=	$this->db->get();
		foreach($by_name->result() as $row)
		{
			$suggestions[]			=	'KIT '.$row->item_kit_id.'|'.$row->name;
		}

		//only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions			=	array_slice($suggestions, 0,$limit);
		}
		return $suggestions;
		
	}

	/*
	Preform a search on items
	*/
	function	search				($search)
	{
		$this						->	db->from('item_kits');
		$this						->	db->where("name LIKE '%".$this->db->escape_like_str($search)."%' or 
										description LIKE '%".$this->db->escape_like_str($search)."%'");
		$this						->	db->where('item_kits.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("name", "asc");
		return 						$this->db->get();	
	}

	function search_with_cost_price($search)
	{

//		return 						$this->db->get();	





		$sql = '
            SELECT
                `ospos_item_kit_items`.`item_kit_id`,
                `ospos_item_kits`.`name`,
                `ospos_item_kits`.`description`,
                `ospos_item_kits`.`item_kit_id`,
                SUM(CAST(`ospos_item_kit_items`.`quantity` as DECIMAL(7,2)) * CAST(`ospos_items_suppliers`.`supplier_cost_price` as DECIMAL(7,2))) as cost_kit,
                `ospos_items_pricelists`.`unit_price_with_tax`,
                `ospos_item_kits`.`barcode`
            FROM
                `ospos_item_kits`,
                `ospos_item_kit_items`,
                `ospos_items_suppliers`,
                `ospos_items_pricelists`
			WHERE
			    (`ospos_item_kits`.`name` LIKE "%' . $this->db->escape_like_str($search) . '%" OR
			    `ospos_item_kits`.`description` LIKE "%' . $this->db->escape_like_str($search) . '%" ) AND
				`ospos_items_suppliers`.`supplier_preferred` = "Y" AND
                `ospos_item_kit_items`.`item_id` = `ospos_items_suppliers`.`item_id` AND
                `ospos_item_kit_items`.`item_kit_id` = `ospos_items_pricelists`.`item_id`AND
                `ospos_item_kits`.`item_kit_id` = `ospos_item_kit_items`.`item_kit_id`
			GROUP BY 
			`ospos_item_kits`.`name`,
            `ospos_item_kit_items`.`item_kit_id`
            ';
        $query = $this->db->query($sql);
        $result = $query->result_array();
	//    return $result;
	    return $result;






		
	}

	//load all items for kit
	function get_item_kit_items($item_kit_id)
	{
		$this->db->from('item_kit_items');
		$this->db->where('item_kit_id', $item_kit_id);
		$query = $this->db->get();
        return $query->result_array();
	}

    //load all items for kit
    function get_item_kit_items_with_item_cost($item_kit_id)
    {
		$this->db->select('
		item_kit_items.item_id as id,
		item_kit_items.quantity,
		supplier_cost_price
		 ');
		$this->db->from('item_kit_items');
		$this->db->join('items_suppliers', 'items_suppliers.item_id=item_kit_items.item_id');
		
		$this->db->where('item_kit_id', $item_kit_id);
		$this->db->where('supplier_preferred', 'Y');
		
    	$query = $this->db->get();
    	return $query->result_array();
    }


	function get_item_kit_item($item_kit_id_item_id)
	{
		$this->db->from('item_kit_items');
		$this->db->where('item_kit_id', $item_kit_id_item_id['item_kit_id']);
		$this->db->where('item_id', $item_kit_id_item_id['item_id']);
		$query = $this->db->get();
        return $query->result_array();
	}

	function update_insert_item_kit_item($item_kit_item, $new)
	{
		switch($new)
		{
			case '0':
				//update
				$this->db->where('item_kit_id', $item_kit_item['item_kit_id']);
				$this->db->where('item_id', $item_kit_item['item_id']);
				$this->db->update('item_kit_items', $item_kit_item);
			break;

			case '1':
				//insert
				$this->db->insert('item_kit_items', $item_kit_item);
			break;

			default:
			break;
		}
	}

	function save_item_kit($item_kit_info)
	{
			// test new item
			switch ($_SESSION['new'])
			{
				// add item
				case	1:
						if (!$this->db->insert('item_kits', array("item_kit_id" =>$item_kit_info['item_kit_id'], "name" => $item_kit_info['name'], "description" => $item_kit_info['description'], "barcode" => $item_kit_info['code_bar'], "branch_code" => $this->config->item('branch_code'))))
						{
							// error inserting = set message
							$_SESSION['error_code']		=	'01000';
							redirect("item_kits");
						}
						unset($_SESSION['new']);
						unset($_SESSION['show_dialog']);
				break;
				
				default:
						$this->db->where('item_kit_id', $item_kit_info['item_kit_id']);
						$this->db->where('branch_code', $this->config->item('branch_code'));
	
						if 	(!$this->db->update('item_kits', array( "name" => $item_kit_info['name'], "description" => $item_kit_info['description'], "barcode" => $item_kit_info['code_bar'])))
						{
							// error updating = set message
							$_SESSION['error_code']		=	'01000';
							redirect("item_kits");
						}
				break;
			}
			return;
	}
	
	//récupération de l'item_id du kit
	function get_item_id_kit()
	{
		$this->db->select('item_id');
		$this->db->from('items');
		$this->db->where('kit', 2);
		$query = $this->db->get();
        return $query->result_array();
	}

	//passage de la valeur kit à 1
	function update_items_kit()
	{
		$this->db->where('kit', 2);
		$this->db->update('items', array("kit" => 1));
	}
	
    //get line in ospos_items_pricelists
	function get_item_kit_id_pricelist($id)
	{
		$this->db->from('items_pricelists');
		$this->db->where('item_id', $id);
		$query = $this->db->get();
        return $query->result_array();
	}

	function update_insert_items_pricelist($item_kit_info_pricelist, $new)
	{
		switch ($new)
		{
			case	1:
					if (!$this->db->insert('items_pricelists', $item_kit_info_pricelist))
					{
						redirect("item_kits");
					}
					unset($_SESSION['new']);
					unset($_SESSION['show_dialog']);
			break;
			
			default:
					$this->db->where('item_id', $item_kit_info_pricelist['item_id']);
					$this->db->where('branch_code', $this->config->item('branch_code'));

					if 	(!$this->db->update('items_pricelists', $item_kit_info_pricelist))
					{
						redirect("item_kits");
					}
			break;
		}
	}

	function update_insert_item_kits_into_ospos_items($item_kit_data)
	{
		$this->db->where('item_id', $item_kit_data['item_id']);
		$this->db->update('items', $item_kit_data);
	}

	function desactive($id, $data_deleted)
	{
		//desactive in ospos_items
		$this->db->where('item_id', $id);
		$this->db->update('items', $data_deleted);

		//desactive in ospos_item_kits
		$this->db->where('item_kit_id', $id);
		$this->db->update('item_kits', $data_deleted);
	}

	//get all kits with item
	function get_item_kits_item($item_id)
    {
		$this->db->from('item_kit_items');
		$this->db->where('item_id', $item_id);
		$query = $this->db->get();
        return $query->result_array();
    }

	//function synchronise_deleted()
	//{
	//	
	//	$sql = '
	//	pdate
	//	';
	//$query = $this->db->query($sql);
	//return $query;
	//}
}
?>
