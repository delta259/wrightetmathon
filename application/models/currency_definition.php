<?php
class Currency_definition extends CI_Model 
{	
	function	exists($currency_code)
	{
		$this						->	db->from('currency_definition');	
		$this						->	db->where('currency_code', $currency_code);
		$this						->	db->where('deleted', 0);
		$this						->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));
		$query						=	$this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function	get_all				($limit=10000, $offset=0)
	{
		$this						->	db->from('currency_definition');
		$this						->	db->where('deleted', 0);	
		$this						->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("display_order", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	get_all_cashtill	($limit=10000, $offset=0)
	{
		$this						->	db->from('currency_definition');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('cashtill', 'Y');
		$this						->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));	
		$this						->	db->order_by("display_order", "asc");
		$this						->	db->limit($limit);
		$this						->	db->offset($offset);
		$data						=	$this->db->get();

		return 						$data;
	}
	
	function	count_all			()
	{
		$this						->	db->from('currency_definition');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));
		return 						$this->db->count_all_results();
	}
	
	/*
	Preform a search on currency
	*/
	function	search				($search)
	{		
		// search by everything
		$this						->	db->from('currency_definition');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										denomination LIKE '%".$this->db->escape_like_str($search)."%' or
										display_name LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));		
		$this						->	db->order_by("display_order", "asc");
		
		return 						$this->db->get();	
	}
	
	/*
	Get search suggestions to find currency
	*/
	function	get_search_suggestions($search, $limit=25)
	{
		// initialise
		$suggestions = array();
		
		// search on names
		$this						->	db->from('currency_definition');
		$this						->	db->where('deleted', 0);
		$this						->	db->where("(
										denomination LIKE '%".$this->db->escape_like_str($search)."%' or
										display_name LIKE '%".$this->db->escape_like_str($search)."%'
										)");
		$this						->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));
		$this						->	db->order_by("display_order", "asc");	
		$by_name					=	$this->db->get();

		foreach($by_name->result() as $row)
		{			
			$suggestions[]=$row->display_name;
		}

		// only return $limit suggestions
		if(count($suggestions) > $limit)
		{
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		
		return 						$suggestions;
	}
	
	/*
	Gets information about a particular currency
	*/
	function	get_info			($denomination)
	{
		$this						->	db->from('currency_definition');	
		$this						->	db->where('deleted', 0);
		$this						->	db->where('currency_definition.denomination',$denomination);
		$this						->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));
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
		$this						->	db->from('currency_definition');
		$this						->	db->where('deleted', 0);
		$this						->	db->where('denomination', $_SESSION['transaction_info']->denomination);
		$this						->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));
		return							$this->db->count_all_results();
	}
	
	function delete($denomination)
	{
		$this->db->where('denomination', $denomination);
		$this->db->where('currency_definition.branch_code', $this->config->item('branch_code'));
		return $this->db->update('currency_definition', array('deleted' => 1));
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
					$this			->	db->insert('currency_definition', $_SESSION['transaction_info']);
			break;
			
			// update category
			default:
					$this			->	db->where('denomination', $_SESSION['transaction_id']);
					$this			->	db->where('currency_definition.branch_code', $this->config->item('branch_code'));
					$this			->	db->update('currency_definition', $_SESSION['transaction_info']);
			break;				
		}
		return true;
	}
}
?>
