<?php
class Import extends CI_Model 
{	
	function	exists($column_id)
	{
		$this						->	db->from('items_imports');	
		$this						->	db->where('column_id', $column_id);
		$this						->	db->where('items_imports.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('items_imports');	
		$this						->	db->where('items_imports.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("column_number", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('items_imports');
		$this						->	db->where('items_imports.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on imports
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('items_imports');
		$this						->	db->where("(
										column_label LIKE '%".$this->db->escape_like_str($search)."%' or
										column_database_field_name LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('items_imports.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("column_letter", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find imports
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('items_imports');
		$this						->	db->where("(
										column_label LIKE '%".$this->db->escape_like_str($search)."%' or
										column_database_field_name LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('items_imports.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("column_letter", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->column_label;
		}

		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Gets information about a particular pricelist
	*/
	function	get_info			($column_id)
	{
		$this						->	db->from('items_imports');	
		$this						->	db->where('column_id',$column_id);
		$this						->	db->where('items_imports.branch_code', $this->config->item('branch_code'));
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
		$this						->	db->from('items_imports');
		$this						->	db->where('column_letter', $_SESSION['transaction_info']->column_letter);
		$this						->	db->where('items_imports.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	/*
	Inserts or updates
	*/
	function save()
	{
		switch ($_SESSION['new'])
		{
			// add category
			case	1:
					$this			->	db->insert('items_imports', $_SESSION['transaction_info']);
					$_SESSION['transaction_info']->column_id	=	$this->db->insert_id();
			break;
			
			// update category
			default:
					$this			->	db->where('column_id', $_SESSION['transaction_id']);
					$this			->	db->where('items_imports.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('items_imports', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
	
	function	delete($column_id)
	{
		$this															->	db->where('column_id', $column_id);
		$this															->	db->where('items_imports.branch_code', $this->config->item('branch_code'));
		$this															->	db->delete('items_imports');
		return;
	}
	
	function	load_pick_list()
	{
		// create dropdown for database field names
		$_SESSION['C']->column_database_field_name_pick_list			=	array_merge($this->db->list_fields('items'), $this->db->list_fields('items_suppliers'), $this->db->list_fields('items_pricelists'));
		
		// create dropdown for data type
		$_SESSION['C']->data_type_pick_list								=	array('N'=>$this->lang->line('imports_column_numeric'), 'A'=>$this->lang->line('imports_column_alpha'));
	
		return;
	}
}
?>
