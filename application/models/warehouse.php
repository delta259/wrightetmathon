<?php
class Warehouse extends CI_Model 
{	
	function	exists($warehouse_code)
	{
		$this						->	db->from('warehouses');	
		$this						->	db->where('warehouse_code', $warehouse_code);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('warehouses.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('warehouses');
		$this						->	db->where('deleted', 0);	
		$this						->	db->where('warehouses.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("warehouse_description", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('warehouses');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('warehouses.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on suppliers
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('warehouses');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										warehouse_code LIKE '%".$this->db->escape_like_str($search)."%' or
										warehouse_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('warehouses.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("warehouse_description", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find suppliers
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('warehouses');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										warehouse_code LIKE '%".$this->db->escape_like_str($search)."%' or
										warehouse_description LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('warehouses.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("warehouse_description", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->warehouse_description;
		}

		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Gets information about a particular warehouse
	*/
	function	get_info			($warehouse_code)
	{
		$this						->	db->from('warehouses');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('warehouses.warehouse_code',$warehouse_code);
		$this						->	db->where('warehouses.branch_code', $this->config->item('branch_code'));
		$query 						= 	$this->db->get();
		
		if($query->num_rows()	==	1)
		{
			return $query->row();
		}
		else
		{			
			return 					NULL;
		}
	}
	
	// common check duplicate
	function 	check_duplicate		()
	{		
		$this						->	db->from('warehouses');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('warehouse_code', $_SESSION['transaction_info']->warehouse_code);
		$this						->	db->where('warehouses.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	/*
	Inserts or updates a warehouse
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add category
			case	1:
					$this			->	db->insert('warehouses', $_SESSION['transaction_info']);
			break;
			
			// update category
			default:
					$this			->	db->where('warehouse_code', $_SESSION['transaction_id']);
					$this			->	db->where('warehouses.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('warehouses', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
}
?>
